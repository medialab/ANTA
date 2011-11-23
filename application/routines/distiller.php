<?php
 header('Content-type: text/plain; charset=UTF-8');
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
 header("Cache-Control: no-store, no-cache, must-revalidate");
 header("Cache-Control: post-check=0, pre-check=0", false);
 header("Pragma: no-cache");	
 
 // Define path to application directory
 $scriptPath = dirname(__FILE__);
 define( 'APPLICATION_PATH', dirname(__FILE__) . '/..' );
 
 echo substr( $scriptPath, 0, strrpos( $scriptPath, "/", -1 ) );
 
 // Ensure library/ is on include_path
 set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
	APPLICATION_PATH . '/models',
    get_include_path(),
 )));
 
 /** Zend_Application */
 require_once 'Zend/Application.php';

 // Create application, bootstrap, and run
 $application = new Zend_Application(
    'development',
    APPLICATION_PATH . '/configs/application.ini'
 );
 
 set_time_limit( 0 );
 
 /**
  * debugger: distiller script may be used directly from the browser.
  */
 $debug = isset( $_GET[ 'debug' ] );
 
 /**
  
  0. Shutdown function and console Log function
 
  */
 
 /**
  * Handle the shudown event, on exit; write the result o,n log.
  * 
  */  
 function routineShutdown(){
		global $user;
		global $log;
		global $debug;
		
		if( $user == null ){
			exit;
		}
		Anta_Logging::append( $log, "shutting down...", false );  
		// set the routine to die
		if( $debug == false ) Application_Model_RoutinesMapper::kill( $user->id );
		
		// actual memory usage
		Anta_Logging::append( $log, "memory: ". memory_get_usage (), false );  
		
		// exit errors
		Anta_Logging::append( $log, "errors: ". @implode( ", ",  error_get_last() ), false );  
		
		$pidFile = Anta_Logging::getLogsPath()."/".$user->username.".pid";
		
		// delete pid file
		if( ! @file_exists ( $pidFile ) ){
			Anta_Logging::append( $log, "pid file '".basename( $pidFile )."' does not exists ", false );  
		} else {
			// check pid
			$pid = file_get_contents( $pidFile );
			Anta_Logging::append( $log, "stored pid: ".$pid.", actual pid: ".getmypid(), false );
			
			if( @unlink( $pidFile ) ){
				Anta_Logging::append( $log, "pid file '".basename( $pidFile )."' removed correctly", false );  
			} else {
				Anta_Logging::append( $log, "pid file '".basename( $pidFile )."' has not been removed!", false );  
			}
		}
		
		// modify the log file
		Anta_Logging::append( 
			"log_".$user->username,
			"exit called, the routine has finished.",
			false
		);
		// echo "oooo";
		// echo Anta_Logging::read(  $log );
	
 }
 
 register_shutdown_function( 'routineShutdown' ); 
 
 /**
  * log function to debug
  */
 function log_ln( ){
	$args = func_get_args();
	foreach( $args as $arg ){
		echo $arg ." ";
	}
	echo "\n";
 }
 
 /**
  
  1. Check / Load user
  
  */
 $idUser = isset( $_REQUEST['user'] )? $_REQUEST['user']: $argv[ 1 ];
 $idUser = Dnst_Crypto_SillyCipher::decrypt(  $idUser );

 log_ln( "received idUser as ".$idUser );
 
 $user = Application_Model_UsersMapper::getUser( $idUser );
 
 
 
 if( $user == null ){
	log_ln( 'user not found' );
	Anta_Logging::append( 
		"routine_error_log",
		"param '?user=".$_REQUEST['user']."':'$idUser' was not found, or is not a valid user",
		true
	);
	exit;
 }
 
 /**
  
  2. Check / Load text File
  
 */ 
 
 /** the log file, Anta_Logging write it in logs folder automatically */
 $log = "log_".$user->username;
 
 /** the starting time var */
 $startTime = microtime( true );

 // info about PID and linux user throwing the script
 Anta_Logging::append(  $log, "user:".get_current_user()."\t"."pid:".getmypid() );

 // create routine
 Application_Model_RoutinesMapper::addRoutine( $user->id );
 
 // create pidfile=
 Anta_Logging::createPidFile( $user->username );
	
 
 /** load the next 'ready' document 
  * Note: will exit on mysql exception! 
  */
 function analyseNextDocument( $log, $user, $startTime ){
	global $debug;
	// check signals, exit if it has not been found
	try{
		$status = Application_Model_RoutinesMapper::getStatus( $user->id );
		if( !$debug ){
		if( $status == null ){
			Anta_Logging::append(  $log, "routine not enabled for user:".$user->username, false );
			exit;
		} else	if ( $status == 'die' ){
			Anta_Logging::append(  $log, "routine for user:".$user->username. " has been killed", false );
			exit;
		} else if( $status == 'died' ){
			Anta_Logging::append(  $log, "routine *died* for user:".$user->username. "... restart it manually", false );
			exit;
		}
		}
			
		
	} catch( Exception $e ){
		
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		exit;
	}
	
	// check document
	try{
		$doc = Application_Model_DocumentsMapper::getNextDocument( $user );
	} catch( Exception $e ){
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		exit;
	}
	
	// waiting if there are no more 'ready' docs
	if( $doc == null ) {
		usleep( 2000000 ); // 20 seconds
		return;
	}
	
	
	
 	Anta_Logging::append( $log, 'id:'.$doc->id.", "."size:".$doc->size, false );
	Anta_Logging::append( $log, 'status:'.$status, false );
	
	// check txt file existance
	$localUrl = Anta_Core::getLocalUrl( $user, $doc->localUrl, true );
	
	if( ! file_exists( $localUrl ) ){
		Anta_Logging::append( $log, "file not found:".$localUrl, false );
		
		try{
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'notfound' );
		} catch( Exception $e ){
			Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
			exit;
		}
		Anta_Logging::append( $log, "status:notfound".", "."changed:".$affected, false );
		return;
	}
		
	// indexing...
	try{
		$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'ready' );
	} catch( Exception $e ){
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		exit;
	}
	Anta_Logging::append( $log, "status:indexing"."\t"."changed:".$affected, false );
	
	// trqsform into text, then the localUrl points to...
	if( $doc->mimeType == "application/pdf" ){
		if( ! file_exists( $localUrl.".txt" ) ){
			exec( "pdftotext -q -nopgbrk -eol unix ".$localUrl." ".$localUrl.".txt ");
		}
		if( ! file_exists( $localUrl.".txt" ) ){
			Anta_Logging::append( $log, "unable to pdftotext:".$localUrl, false );
			try{
				$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->_user, $doc->id, 'nottxt' );
			} catch( Exception $e ){
				Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
			}
			return;
		}
		$localUrl = $localUrl.".txt";
	}
	
	/**
	 *
	 * Sentences and text compression
	 *
	 */
	 
	// compress text
	$text = Anta_Core::compress( $localUrl );
	$textSize = strlen( $text );
	 
	// split into sentences
	$sentences = Textopoly_Alquemy::chunkSentences( $text );

	Anta_Logging::append( $log, "content strlen: ". $textSize.", after chunking: ".Textopoly_Alquemy::$lastLength, false );	
	 
	// text chunking time 
	Anta_Logging::append( $log, "after chunks, elapsed: ". ( microtime( true ) - $startTime ). ', sentences: '.count( $sentences ), false );	
	
	doFlush();
	
	// save sentences for the given document
	try{
		
		$previousSentences = Application_Model_SentencesMapper::cleanSentences( $user, $doc->id );
		
		foreach( array_keys( $sentences ) as $k ){
			$idSentence = Application_Model_SentencesMapper::addSentence( $user, $doc->id, $k, $sentences[ $k ] );
		}
		
	} catch( Exception $e ){
	 
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		return;
		
	}
	 
	// saving sentences time 
	Anta_Logging::append( $log, "after saving chunks, elapsed: ". ( microtime( true ) - $startTime ) , false );	
	 
	// actual memory usage
	Anta_Logging::append( $log, "memory: ". memory_get_usage (), false ); 
	
    // set this document as 'indexed'
	try{
		$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'indexing' );
	} catch( Exception $e ){
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
	}	
	 
	doFlush(); 
	 
	/**
	 *
	 * Services! OpenCalais
	 *
	 */
	 
	 // load configuration (url, api-keys)
	 $config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "services" );

	 // openCalais config log output
	 Anta_Logging::append( $log, "curl OpenCalais: ". $config->opencalais->api->rest, false );	

	 // create chunk
	 $chunk = "";
	 
	 // the value to che
	 $chunksLength = 0;
	 
	 for( $i = 0; $i < count( $sentences ); $i++ ){
		
		if( strlen( $chunk ) + strlen( $sentences[ $i ] )  > 50000 ){
		
			Anta_Logging::append( $log, "chunk: ". strlen( $chunk ), false );	
			$chunksLength += strlen( $chunk );
			
			// call please
			if( openCalaisRoutine( $user, $doc, $chunk, $config, $log ) === false ){
				Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'incomple' );
				return;
			};
			
			
			
			Anta_Logging::append( $log, "after openCalais, elapsed:".( microtime( true ) - $startTime ), false );
			// flush results
			doFlush();
			
			// pause
			usleep( mt_rand ( 4000000 , 6000000 )  );
			
			// unset chunk & json
			$chunk = "";
			$jsonResponse = null;
		}
		
		$chunk .= $sentences[ $i ] ;
		
		
	}
	
	
	
	Anta_Logging::append( $log, "chunk: ". strlen( $chunk ), false );	
	$chunksLength += strlen( $chunk );
	if( openCalaisRoutine( $user, $doc, $chunk, $config, $log ) === false ){
		Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'incomple' );
		return;
	};
	
	
	
	
	
	
	Anta_Logging::append( $log, "chunk length: ". $chunksLength.", text expected length: ".Textopoly_Alquemy::$lastLength, false );
	 
	
	// actual memory usage
	Anta_Logging::append( $log, "memory: ". memory_get_usage (), false );  
	
	doFlush(); 
	
	/** alchemy chunking */
	// reset chunk
	$chunk = "";
	 
	// reset the value
	$chunksLength = 0;
	
	for( $i = 0; $i < count( $sentences ); $i++ ){
		
		if( strlen( $chunk ) + strlen( $sentences[ $i ] )  > 7000 ){
		
			Anta_Logging::append( $log, "chunk: ". strlen( $chunk ), false );	
			$chunksLength += strlen( $chunk );
			
			// call please
			alchemyRoutine( $user, $doc, $chunk, $config, $log );
			Anta_Logging::append( $log, "after alchemy, elapsed:".( microtime( true ) - $startTime ), false );
			// flush results
			doFlush();
			
			// pause
			usleep( mt_rand ( 4000000 , 6000000 )  );
			
			// unset chunk & json
			$chunk = "";
			$jsonResponse = null;
		}
		
		$chunk .= $sentences[ $i ]."." ;
		
		
	}
	Anta_Logging::append( $log, "chunk: ". strlen( $chunk ), false );	
	$chunksLength += strlen( $chunk );
	alchemyRoutine( $user, $doc, $chunk, $config, $log );
	
	
	Anta_Logging::append( $log, "alchemy chunk length: ". $chunksLength.", text expected length: ".Textopoly_Alquemy::$lastLength, false );
	
	
	
	
    // set this document as 'indexed'
	try{
		$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $user, $doc->id, 'indexed' );
	} catch( Exception $e ){
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
	}
	
	Anta_Logging::append( $log, "elapsed:".( microtime( true ) - $startTime ), false );
	
	
 }
 
 /**
  
  Routine!
  
  */
  while( true ){
	analyseNextDocument( $log, $user, $startTime );
	if( $debug ) break;
	
  }
 
/**
 * Do the opencalais routine
 */ 
function openCalaisRoutine( $user, $doc, $chunk, $config, $log ){
	return true;
	$openCalais = new Textopoly_OpenCalais( $config->opencalais->api->rest, array(
			"outputMode"   => "json",
			"text"         => $chunk,
			"content-type" => "text/txt",
			"api-key"       => $config->opencalais->api->key
	));
       
	Application_Model_QuotasMapper::addQuota( 'OC', strlen($chunk), $openCalais->getResponseLength() );
	if( $openCalais->hasError() ){
		Anta_Logging::append( $log, "opencalais api error, error string:".$openCalais->getError(), false );
		return false;
	}
 
	$jsonResponse = $openCalais->get();	

	if( $jsonResponse->meta->messages != null ){
		foreach( $jsonResponse->meta->messages as $message ){
			Anta_Logging::append( $log, "opencalais message received ".json_encode( $message ), false );
			return false; // set document to error
		}
	}
	
	
	// log number of OPENCALAIS entities found
	Anta_Logging::append( $log, "opencalais found ". count( $jsonResponse->entities ). " entities", false );
		
	// save entities
	try{
		
		foreach( $jsonResponse->entities as $entity ){
			$idEntity = Application_Model_EntitiesMapper::addEntity(
				$user,  $entity->text,  $entity->text,  $entity->type, $entity->relevance, 'en', 'OC'
			);
			Application_Model_DocumentsMapper::addEntity( $user, $doc->id, $idEntity, $entity->type, $entity->relevance, 'OC');
		}
		
	} catch( Exception $e ){
		
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		exit;
	
	}
		
		
}


function alchemyRoutine( $user, $doc, $chunk, $config, $log ){
	 // ALCHEMY
	
	// load alchemy entities
	Anta_Logging::append( $log, "curl Alchemy: ". $config->alchemy->api->entities, false );
	
	
	
	$alchemy = new Textopoly_Alchemy( $config->alchemy->api->entities, array(
		"outputMode" => "json",
		"text" => $chunk,
		"apikey" => $config->alchemy->api->key
	));
	Application_Model_QuotasMapper::addQuota( 'AE', strlen($chunk), $alchemy->getResponseLength() );
	if( $alchemy->hasError() ){
		Anta_Logging::append( $log, "alchemy api error, entities:".$alchemy->getError(), false );
		return;
	}
	
	// read alchemy response
	$jsonResponse = $alchemy->get();
	
	// get document language
	$language = substr( $jsonResponse->language, 0, 2 );
	
	// log number of entities found
	Anta_Logging::append( $log, "alchemy found ". count( $jsonResponse->entities ). " entities", false );
	
	// save entities
	try{
	
		foreach( $jsonResponse->entities as $entity ){
			$idEntity = Application_Model_EntitiesMapper::addEntity(
				$user,  $entity->text,  $entity->text,  $entity->type, $entity->relevance, $language, 'AL'
			);
			Application_Model_DocumentsMapper::addEntity( $user, $doc->id, $idEntity, $entity->type, $entity->relevance, 'AL');
	
		}
	} catch( Exception $e ){
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		exit;
	}
	
	// load alchemy keywords
	Anta_Logging::append( $log, "curl Alchemy: ". $config->alchemy->api->keywords, false );
	$alchemy = new Textopoly_Alchemy( $config->alchemy->api->keywords, array(
		"outputMode" => "json",
		"text" => $chunk,
		"apikey" => $config->alchemy->api->key
	));
	
	Application_Model_QuotasMapper::addQuota( 'AK', strlen($chunk), $alchemy->getResponseLength() );
	
	if( $alchemy->hasError() ){
		Anta_Logging::append( $log, "alchemy api error, keywords:".$alchemy->getError(), false );
		return;
	}
	
	$jsonResponse = $alchemy->get();
	
	
	// log number of keywords found
	Anta_Logging::append( $log, "alchemy found ". count( $jsonResponse->keywords ). " keywords", false );
	
	// save entities
	try{
		foreach( $jsonResponse->keywords as $entity ){
			$idEntity = Application_Model_EntitiesMapper::addEntity(
				$user,  $entity->text,  $entity->text,  'keyword', $entity->relevance, $language, 'AL'
			);
			Application_Model_DocumentsMapper::addEntity( $user, $doc->id, $idEntity, 'keyword', $entity->relevance, 'AL' );
		}
	} catch( Exception $e ){
		Anta_Logging::append(  $log, "mysql exception:".$e->getMessage(), false );
		exit;
	}
	
	
}

function doFlush (){
    // echo(str_repeat(' ',256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){            
        @ob_flush();
        @flush();
        @ob_end_flush();
    }    
    @ob_start();
}
?>
