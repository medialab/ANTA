<?php
/**
 *@package Anta
 */

/**
 * Contain some basic static function
 * - auth function
 * - route function
 * - common function ( formatters etc..)
 */
class Anta_Core{

	protected static $_base = "/anta_dev";
	protected static $_prefixes = array( "rws", "ngr" );
	
	protected static $_mysqli;

	/**
	 * @return an array of available prefixes
	 */
	public static function getEntitiesPrefixes(){
		return self::$_prefixes;
	}
	
	public static function getJson( $url, $params=array() ){
		
	}
	
	/**
	 * translit and crop the given text string to the given length.
	 * usefult function to crop stuff
	 * @param length	- optional, trim the string to 200 chars. if -1 is specidied, the function returns the entire string.
	 */
	public static function translit( $string, $length=200, $compress=true, $stripHtmlDelimiters = false  ){
		
		$translitted = trim($string);
		
		// This covers all unicode characters
		setlocale(LC_CTYPE, 'fr_FR.utf8');
		
		// translate directly, avec TRANSLIT
		$translitted = iconv('UTF-8', 'ASCII//TRANSLIT', $translitted);
		
		// compress spaces
		if( $compress === false ){
			$translitted = preg_replace('/[\W\s]+/i', ' ', $string );
		} else {
			$translitted = preg_replace('/[\W\s]+/i', '', $string );
		}
		
		if( $stripHtmlDelimiters === true ){
			$translitted = str_replace( array('<','>'), '', $translitted );
		}
		// return the entire string if length param is -1
		if ($length == -1 ){
			return $translitted;
		}
		
		// echo $translitted;
		return substr( $translitted, 0,  $length );
	}
	
	/**
	 * Use this function to access db via mysqli drivers. It returns the available connection
	 * or try to start a brand new connection. Usually, only the Mappers access this function
	 * so do not call it directly. Cfr Application_Model_UsersMapper.php as a valid implementation example
	 */
	public static function mysqli(){

		if( self::$_mysqli == null ){
			self::$_mysqli = self::getMysqliConnection();
			
		}

		return self::$_mysqli;

	}
	
	public static function getMysqliConnection(){
		// free connection
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "database" );
		// extablish connection
		$mysqli = new Zend_Db_Adapter_Mysqli( array(
			'dbname'   => $config->mysql->dbnm,
			'username' => $config->mysql->user,
			'password' => $config->mysql->pass,
			'host'     => $config->mysql->host
		));
		$mysqli->getConnection()->set_charset('utf8');
		return $mysqli;
	}
	
	/**
	 * create a database 
	 * create a project
	 */
	public static function addProject( Application_Model_User $antaUser, Application_Model_Project $project ){
		$database = ( empty( $antaUser->origin )?  $antaUser->username: $antaUser->origin );
		$database = substr( $database, 0, 14 );
			print_r( $antaUser );

		
		# get number of project belonging to the user
		$stmt = Anta_Core::mysqli()->query("
			SELECT count(*) as amount FROM users_projects WHERE id_user = ?", array( $antaUser->id )
		);		
		$amount = $stmt->fetchObject()->amount;

		# amount of projects per users should have limits	
		if( $amount > 10 ) 
			return false;

		# a collection of letters		
		$letters = "abcdefghijklmnopqrstuvwxyz";

		# cfr Application_Model_Project
		$project->database = $database."_".$letters{$amount};
		print_r( $project);

		# store project
		$project = Application_Model_ProjectsMapper::save( $project );
		
		if( $project == null )
			return false;	
		
		# create project dir
		if( @mkdir( Anta_Core::getUploadPath()."/".$project->database, 0755 ) === false ){
			// user exists, or problems in creating folder...! Anta_Core::getUploadPath() handle is_writable errors.
			Anta_Core::setError( I18n_Json::get( 'userAlreadyExistsFolder' ) );
			return false;
		};		
				
		# store relationships
		Application_Model_UsersProjectsMapper::save( $antaUser, $project );	

		# create database		
		self::mysqli()->query( "CREATE DATABASE IF NOT EXISTS anta_".$project->database."");
		self::mysqli()->query( "GRANT ALL PRIVILEGES ON anta_".$project->database." . * TO anta_".( empty( $antaUser->origin )?  $antaUser->username: $antaUser->origin )."@'localhost'" );
		self::setup( $project->database );
				
	}

	/**
	 * install anta database along with tables for the given user / password
	 * @param string username	- username, max 10 chars
	 * @param string password	- the given password
	 */
	public static function setup( $username, $password="" ){
		if( !empty( $password ) ){
			self::mysqli()->query( "CREATE USER anta_".$username."@localhost IDENTIFIED BY '$password'");
			self::mysqli()->query( "GRANT USAGE ON * . * TO anta_".$username."@localhost IDENTIFIED BY '$password' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ");
			self::mysqli()->query( "CREATE DATABASE IF NOT EXISTS anta_".$username."");
			self::mysqli()->query( "GRANT ALL PRIVILEGES ON anta_".$username." . * TO anta_".$username."@'localhost'" );
		}

		/** install projects table to current user */
		Application_Model_ProjectsMapper::install( $username );
		
		/** install documents table to current user */
		Application_Model_DocumentsMapper::install( $username );
		
		/** install documents_projects table to current user */
		Application_Model_DocumentsProjectsMapper::install( $username );
		
		/** install sentences table to current user */
		Application_Model_SentencesMapper::install( $username );
		
		/** install categories table to current user */
		Application_Model_CategoriesMapper::install( $username );
		
		/** install tags table to current user */
		Application_Model_TagsMapper::install( $username );
		
		/** install documents_tags table */
		Application_Model_DocumentsTagsMapper::install( $username );
		
		/** install projects_tags */
		Application_Model_ProjectsTagsMapper::install( $username );
		
		/** install sentences table */
		Application_Model_SentencesMapper::install( $username );
		
		/** install occurrences table */
		Application_Model_OccurrencesMapper::install( $username );
		
		/** install co_occurrences table */
		Application_Model_CooccurrencesMapper::install( $username );
		
		/**
		 * Entities aka Super entities
		 */
		
		/** install super_entities table */
		Application_Model_SuperEntitiesMapper::install( $username );
		
		/** install super_entities tags table */
		Application_Model_SuperEntitiesTagsMapper::install( $username );
		
		
		/**
		 * RWS module (used with AlchemyAPI, OpenCalais)
		 */
		
		/** install rws_entities table */
		Application_Model_Rws_EntitiesMapper::install( $username );
		
		/** install entities_documents table */
		Application_Model_Rws_EntitiesDocumentsMapper::install( $username );
		
		/** install entities_tags table */
		Application_Model_Rws_EntitiesTagsMapper::install( $username );
		
		
		/**
		 * NGR module (used with AlchemyAPI, OpenCalais)
		 */
		
		/** install rws_entities table */
		Application_Model_Ngr_EntitiesMapper::install( $username );
		
		/** install entities_documents table */
		Application_Model_Ngr_EntitiesDocumentsMapper::install( $username );
		
		/** install entities_tags table */
		Application_Model_Ngr_EntitiesTagsMapper::install( $username );
		
		/** view entities distribution (number of documents sharing the same entity ) */
		Application_Model_ViewsMapper::install( $username );
		
		/** zend lucene */
		
		/** graph tables */
		Application_Model_GraphsMapper::install( $username );
		
		/** add routine entry...? */
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO anta.`routines` ( id_user, status ) SELECT id_user, ? FROM users WHERE name = ?",
			array( "start", $idUser )	
		);
		
		
	}
	
	
	
	public static function getAvailableLanguages(){
		return array( "en", "es", "it", "fr" );
	}
	
	public static function getAvailableMimetypes(){
		return array( ".txt" => "txt/plain", ".pdf"=>"application/pdf" );
	}
	
	/**
	 * given a short two letters language rapresentation, return the stem package to be used.
	 * This function switches between vatrious loaded language
	 */
	public static function getLanguage( $language ){
		switch( $language ){
			case "en":
				return "english";
			case "es":
				return "spanish";
			case "fr":
				return "french";
			case "it":
				return "italian";
			default:
				return "english";
			break;
		}
		
	}
	
	/**
	 * Return the stemmed vesrsion of the given word, using the Pecl stem algorithm
	 * according to the language used and of the available language of course.
	 * @param string word		- the word to be stemmed
	 * @param string language	- two-letters language definition
	 */
	public static function getStem( $word, $language ){
		$stemFunction = "stem_" . self::getLanguage( $language ); 
	
		return call_user_func( $stemFunction, $word );
	}
	
	public static function getBase(){
		return self::$_base;
	}
	
	public static function getUploadPath(){
		// load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );

		if( ! is_writable( $config->uploads->path ) ){
			throw( new Zend_Exception( basename( $config->uploads->path ) ." is not writable" ) );
		}
		return $config->uploads->path;
	}

	/**
	 * set the headers to correctly redirect the server
	 */
	public function redirect( $url ){
		header('Location: '.$url, true);  
		exit;
	}
	
	/**
	 * Return the local url for the given filename, according to the user name
	 * ( the subfolder under /uploads directory.
	 * Note: this function may returns "false", use the === operators to check if file_exists
	 * @return a string or false
	 */
	public static function getLocalUrl( Application_Model_User $user, $filename, $override=false ){
		// clean $filename
		$replace  = "_";
		$pattern  = "/([[:alnum:]_\.-]*)/";
		$filename = str_replace( str_split( preg_replace( $pattern, $replace, $filename ) ), $replace, $filename );

		$filename = basename( $filename );

		// verify file existance
		$localPath = self::getUploadPath()."/".$user->username."/";
		$localUrl = $localPath.$filename;

		$i = 0;
		if( !$override ){
			while( file_exists( $localUrl ) ){
				$localUrl = $localPath."_".$i."_".$filename;
				$i++;
			} 	
		}
		
		return $localUrl;

	}
	
	/**
	 * return the document url. This function uses the mimetype
	 * to return the "textified" version of non text/plain files  
	 */
	public static function getDocumentUrl( Application_Model_User $user, Application_Model_Document $document ){
		
		
		
		$candidate = $document->mimeType == "text/plain"?
			self::getUploadPath()."/".$user->username."/".$document->localUrl:
			self::getUploadPath()."/".$user->username."/".$document->localUrl.".txt";
		
		
		
		if( ( empty( $document->localUrl ) || !file_exists( $candidate ) ) && !empty( $document->description) ){
			
			if( empty( $document->mimeType ) )
				Application_Model_DocumentsMapper::setMimetype( $user, $document->id, "text/plain");
			
			$candidate = self::getLocalUrl( $user, empty( $document->title )?"untitled_".$document->id:$document->title );
			
			# fill document file with description
			$written = file_put_contents( $candidate, $document->description );
			
			if( $written )
				# update document description
				Application_Model_DocumentsMapper::setDescription(  $user, $document->id, "" );
			
			# update document local url
			Application_Model_DocumentsMapper::setLocalUrl(  $user, $document->id, basename( $candidate ) );
			
			// echo "file $candidate not found, built from document description";
			
			
		}
		return $candidate;
	}
	
	/**
	 * Handle variou mimetype to txt conversion.
	 * It doesn't handle wrong command line conversion.
	 * known bugs
	 * "mb_detect_encoding" works by guessing, based on a number of candidates that you pass it. In some encodings,
	 * certain byte-sequences are invalid, an therefore it can distinguish between various candidates. 
	 * Unfortunately, there are a lot of encodings, where the same bytes are valid (but different). 
	 * 
	 * @param Application_Model_Document document - a document, or a null object
	 * @param string localUrl - url into user folder
	 * @param string localTxt - url of converted txt file into user folder
	 * @param boolean forceOverride - if localTxt file exists, force the override
	 * @return false if mimetype format is not supported.
	 * @return the 'localTxt' url converted if succeded. 
	 */
	public static function convertToText( $document, $localUrl, $localTxt="", $forceOverride = false ){
		# file exists and it's readable
		if( !empty( $localTxt ) && file_exists( $localTxt ) && $forceOverride == false ){
			return $localTxt;
		}
		
		# not plain txt files needs a txt version of the file
		$localTxt = empty( $localTxt )? $localUrl.".txt": $localTxt;
	
		# use unix app to convert text
		switch ( $document->mimeType ){
				case "text/plain":
				
					$content = file_get_contents( $localUrl );
					
					# detect encoding. does not work with "unicode" and "utf-16" family
					$encoding = mb_detect_encoding ( $content );
					
					if( empty( $encoding ) ){ echo "encoding not found"; return false;	}
					
					# if encoding is not utf8, try to convert it
					if( $encoding != 'UTF-8' ){	$content = @mb_convert_encoding ( $content , 'UTF-8', $encoding );	}
					
					if( empty( $content ) ) return false;
					# save it
					if( file_put_contents( $localUrl, preg_replace('!\s+!', ' ', $content ) ) === false ){
						return false;
					};
					return $localUrl;
				break;
				case "application/pdf":
					exec( "pdftotext -q -nopgbrk -enc UTF-8 -eol unix ".$localUrl." ".$localTxt );
				break;
				case "application/msword":
					// catdoc
					exec( "catdoc -d utf-8 ".$localUrl." > ".$localTxt );
				break;
				default:
					return false;
				break;
			}
		
		// execute a nice chars replacement around . sign?
		if( !file_exists(  $localTxt ) ){
			return false;
		}
		$content = file_get_contents( $localTxt );
		file_put_contents( $localTxt, preg_replace('!\s+!', ' ', $content ) );
		
		return true;
	}
	
	/**
	 * return the correct Zend_Search_Lucene index or return false.
	 * This function traces "echoes" directly the exception, that is it handles the exceptions itself.
	 * @param  Application_Model_User user - current anta user
	 * @return Zend_Search_Lucene_Index or False
	 */
	public static function getZendLuceneIndex( Application_Model_User $user ){
		
		// load config
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );
		$lucenePath = $config->lucene->path.'/'.$user->username;
		
		$luceneIndexExists = true;
				
		try{
			$index = Zend_Search_Lucene::open( $lucenePath );
		} catch( Exception $e ){
			$luceneIndexExists = false;
		}
		
		if( $luceneIndexExists ) {
			return $index;
		}
		
		// create lucene index at $lucenePath
		try{
			$index = Zend_Search_Lucene::create( $lucenePath );
		} catch( Exception $e ){
			return $e;
		}
		
		return $index;
	}
	
	public static function getText(  Application_Model_User $user, Application_Model_Document $document ){
		$localUrl = self::getDocumentUrl( $user, $document );		
		return self::wrapText( $localUrl, 50 );
	}
	
	/**
	 * return a text preview of the given document.
	 * Throw exceptions type Zend if there are any errors. If returnPreview param is set to true,
	 * the function will try to check the document existance / try to make a txt readable version of the file.
	 *
	 * @param user		- the anta user (document folder)
	 * @param document	- 
	 * @param returnPreview	- if set to false, do not return anything. Default is true.
	 *
	 * @return a text string or null, depending on returnPreview value.
	 */
	public static function getTextPreview(  Application_Model_User $user, Application_Model_Document $document, $returnPreview = true ){
		$localUrl = self::getUploadPath()."/".$user->username."/".$document->localUrl;
		
		if( !file_exists( $localUrl ) ){
			throw( new Zend_Exception( I18n_Json::get( 'documentNotFound', 'errors' ) ) );
		}
		
		if( $document->mimeType == "text/plain" ){
			return self::wrapText( $localUrl, 50 );
		}
		return $localTxt = $localUrl.".txt";
		
		if( $document->mimeType == "text/plain" ){
			
			if( $returnPreview ){
				return self::wrapText( $localUrl, 50 );
			} else return $localUrl;
		}
		// not plain txt files
		$localTxt = $localUrl.".txt";
		
		// try to create the file
		if( !file_exists( $localTxt ) ){
			$conversion = self::convertToText( $document, $localUrl, $localTxt );
			if( $conversion == false ) {
				self::setMessage( I18n_Json::get( 'document fotmat not supported', 'errors' ) );
				return "";
			}
			
		}
		
		// if the file has not been create, ahia!!
		if( !file_exists( $localTxt ) ){
			self::setMessage( I18n_Json::get( 'document txt conversion not found', 'errors' ) );
			return "";
		}
		
		// return a shot clips of the given fil
		if( $returnPreview === true ){
			return self::wrapText( $localTxt );
		}
		
		return $localTxt;
	}

	
	/**
	 * read the first $lines lines of the given file $filename.
	 * Add a wordwrap function with the parameter $wrapAt. The file should exist
	 * and readable as well (check before)
	 */
	public static function wrapText( $filename, $lines=30, $wrapAt=50 ){
		$handle = @fopen( $filename, "r");
		$lineCounter = 0;
		$string = "";
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				if( $lineCounter > $lines && $lines != -1 ) break;
				$string .= $buffer;
				$lineCounter++;
			}
			if (!feof($handle)) {
				$string .= "\n\n[ anta note: fragment preview ends here ]\n";
			}
			fclose($handle);
		}
		return wordwrap($string);
	}
	

	
	public static function compress( $filename ){
		$handle = @fopen( $filename, "r");
		
		$string = "";
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				
				$string .= preg_replace('/\s+/', ' ', $buffer );
			}
			if (!feof($handle)) {
				$string .= "[ fragment ends here ]\n";
			}
			fclose($handle);
		}
		
		// clean definitely
		//$string = preg_replace('/\s+/i', ' ', $string );
		
		
		return $string;
		
	}
	
	/**
	 * return pseudo entities
	 */
	public static function extractEntities(){
		
	}
	
	/*
	 * Note: use strtotime to format dates
	 * @param format the desired date input according to date_create_from_format specs.
	 */
	public static function getDate( $date, $format ="Y-m-d", $outputFormat="Y-m-d H:i:s" ){
		return $date;
	} 
	
	public static function getCurrentTimestamp(){
		date_default_timezone_set('UTC');
		
		Zend_Date::setOptions(array('format_type' => 'php'));
		$parsed_date = new Zend_Date();
		
		return $parsed_date->toString('Y-m-d H:i:s');
		
	}
	
	/**
	 * Generate a SALT unique, randomly
	 * @param length	- desired length for the randomly generated string
	 * @return an unique random string
	 */
	public static function getDynamicSalt( $length=32){
		$salt = "";
		$alphabeth = "abcdefghijklmnopqrstuvwxyz.:,;-_?%&()0123456789*!<>=[]{} ";

		for ($i = 0; $i < $length; $i++) {
			$salt .= $alphabeth{ rand(0, strlen($alphabeth)-1) };
		}
		return $salt;
	}

	/**
	 * Retrieve an md5 hash merging three salts.
	 * @param password		- to store or to check
	 * @param staticSalt	- secret key used
	 * @param dynamicSalt	- unique identifier used as salt (and stored into users table)
	 */
	public static function hashPassword( $password, $staticSalt, $dynamicSalt ){
		return md5( $staticSalt.":".$dynamicSalt.":".$password );
	}



	/**
	 * check if an user has been logged in. Enable multiple authorization, via args.
	 * usage sample:
	 * <code>
	 * Anta_Core::authorizedOnly();
	 * // or, multiple auth with user types
	 * Anta_Core::authorizedOnly( 'admin', 'public');
	 * </code>
	 */
	public static function authorizedOnly(){
		# user is not authenticated. Back to login!
		if (!Zend_Auth::getInstance()->hasIdentity()){
			$r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$r->gotoUrl('/')->redirectAndExit();
			return;
		}

		# if no arguments has been passed to this function, exit successfully: the user should only be "authenticated"
		if( func_num_args() == 0 ) {
			return true;
		}

		# load arguments passed
		$args = func_get_args();

		# the current identity
        $identity = Zend_Auth::getInstance()->getIdentity();

		# the result
		$authorized = false;

		# check the user authorization to view the page
		foreach( $args as $type ){
			if( $identity->is( $type ) ){
				$authorized = true;
			}
		}

		# redirect to the error page "not authorized"
		if( $authorized == false ){
			throw( new Zend_Exception( I18n_Json::get("activity not authorized") ) );
			return;
		}
	}
	
	/**
	 * a. check if an user pâram has been provided
	 * b. check if an user is authenticated
	 * c. compare the given id with the identity id:
	 *		if they match, then return the user instance.
	 *		otherwise, if the user is an admin, return the user instance
	 *		otherwise, exit with Zend_Exception (error page handling )
	 * @param string id	- an user id integer, decrypterd
	 */
	public static function getAuthorizedUser( $idUser, array $authorizedTypes = array( 'admin' ) ){
		if( $idUser == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFound', 'errors' ) ) );
		}
		return self::authorizeOwner( $idUser, $authorizedTypes, true );
		
	}
	
	public static function createProject( Application_Model_User $user, Application_Model_Project $project ){
		# get project id

		# save the new project

		# create the database behind

		# update created project
		

		# create the relationship with the current user
		
		
	}
	/**
	 * All params are optional
 	 * @return Application_Model_User instance of the authentified user
	 */
	public static function authorizeOwner( $idOwner = -1, array $authorizedTypes = array(), $returnAuthenticatedUser = false ){

		if (!Zend_Auth::getInstance()->hasIdentity()){
			$r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$r->gotoUrl('/index/login')->redirectAndExit();
			return;
		}
		
		if( $idOwner == -1 ){
			return Zend_Auth::getInstance()->getIdentity();
		}

		// the current identity
        $identity = Zend_Auth::getInstance()->getIdentity();

		if( $identity->id == $idOwner ) return $returnAuthenticatedUser?  $identity: true;
		
		if ( in_array( $identity->type, $authorizedTypes ) ) return  $returnAuthenticatedUser?  $identity: true;
		
		throw( new Zend_Exception( I18n_Json::get( 'userNotAuthorized', 'errors' ) ) );
	}

	/**
	 * @return true or a loooong list of stuff
	 */
	public static function validateForm( Ui_Form $form, $params=array() ){


		if ( $form->isValid( $params ) ) {
			return true;
		}
		$formErrors = $form->getMessages();
		
		$html = "<br />";
			foreach( $formErrors as $field=>$values){
				$html .= "<strong>".$field."</strong>&nbsp;".implode(", ", $values)."<br />";
			}
		$html .= "<br />";

		return $html;

	}

	/**
	 * Auth
	 * @return true or false
	 */
	public static function authenticateUser( $params ){

		$username =& $params['username'];
		$password =& $params['password'];

		$auth    = Zend_Auth::getInstance();
		// crete main adapter
		$adapter = new Application_Model_Auth_Adapter( $username, $password );

		// chain db adapter(s)
		$adapter->addAdapter( new Application_Model_Auth_MysqliAuthAdapter( $username, $password ) );

		$result  = $auth->authenticate( $adapter );

		if ($result->isValid()) {
			$identity = Zend_Auth::getInstance()->getIdentity();
			return true;
		}

		return false;

	}

	/**
	 * Easy readable version of notification board setError methods
	 */
	public static function setError( $error ){
		Application_Model_Ui_Boards_NotificationBoard::getInstance()->setError( $error );
	}

	/**
	 * Easy readable version of notification board setError methods
	 */
	public static function setMessage( $message ){
		Application_Model_Ui_Boards_NotificationBoard::getInstance()->setMessage( $message );
	}
	
	/**
	 * send http headers
	 */
	public static function setHttpHeaders( $contentType, $filename = "", $forceDownload = false ){
		
		header('Content-type: '.$contentType.'; charset=UTF-8', true);
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", true);
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT", true);
		header("Cache-Control: no-store, no-cache, must-revalidate", true);
		header("Cache-Control: post-check=0, pre-check=0", true);
		
		if( ! empty( $forceDownload ) ){
			header('Content-Disposition: attachment; filename="' . $filename . '"'); 
		}
		
		header("Pragma: no-cache");
	
	}
	


}
?>
