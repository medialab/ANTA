<?php
/**
 * @package Anta
 */
 
/**
 * Handle routine, autologging.
 * Dependences:
 * Application_Model_RoutinesMapper
 * Application_Model_User
 * Anta_Logging
 */
class Anta_Distiller{
	
	/**
	 *
	 */
	public $namespace = "distiller";
	
	/**
	 * A microseconds timeout for sleeping between routine cycles, read-only ( is randomly loaded in start() method! )
	 * @var int
	 */
	public $timeOut;
	
	/**
	 * A microseconds sleeping while waiting for somehting to do (no threads ready)
	 * @var int
	 */
	public $sleepingTime = 10000000;
	
	/**
	 * the current user of anta application
	 * @var Application_Model_User 
	 */
	public $user;
	
	/**
	 * Log filename identifier (e.g "Log_jiminy")
	 * @var string
	 */
	public $log;
	
	/**
	 * If it's true, the debug behaviour is used
	 * @var boolean 
	 */
	public $debug = false;
	
	/**
	 * The process Id
	 * @var int 
	 */
	public $pid;
	
	/**
	 * The statuof the routine: start | die | died
	 * @var string
	 */
	public $status;
	
	/**
	 * microtime( true ) initial time.
	 * @var float
	 */
	protected $_startTime;
	
	/**
	 * A list of threads
	 */
	protected $_threads = array();
	
	/**
	 * start queuing...
	 */
	public function start(){
		
		$this->timeOut = mt_rand ( 1000000 , 2000000 );
		
		// check routine status
       	
		$this->status = Application_Model_RoutinesMapper::getStatus( $this->user->id );
		
		if( ! $this->debug ){
			// check routine status, debug will force it
			if ( $this->status == 'die' ){
				Anta_Logging::append(  $this->log, "status: routine for user '".$this->user->username. "' has been killed", false );
				exit;
			} else if( $this->status == 'died' ){
				Anta_Logging::append(  $this->log, "status: routine *died* for user '".$this->user->username. "'... restart it manually", false );
				exit;
			}
		} else{
			Anta_Logging::append( $this->log, "debugging session start(), elapsed: ".( microtime( true ) - $this->_startTime ), false );
		}
		// Anta_Logging::append(  $this->log, "status: ".$this->status, false );
		
			
		// load next ready document
		$document = Application_Model_DocumentsMapper::getNextDocument( $this->user );
				
		// load current analysis object
		$executionResult = $this->__executeThreads(  $document );
		
		
		
		if( $executionResult === false ){
			$this->_log( "warning: thread execution failed " );
			exit;
		}
		
		// the debug cycle...
		if( $this->debug ){
			exit;
		}
		// sleep...
		usleep( $this->timeOut );
			
		// start again
		$this->start();
	
	}
	
	protected function __executeThreads( $document ){
		$threads = Application_Model_ThreadsMapper::getThreads( $this->user);
		
		#no ready document...
		if( $document == null ) return true;
		
		# changing document status 
		if( !$this->debug ){
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'indexing' );
		}
		$this->_log( "doc #{$document->id}, title:'{$document->title}'");
		
		# no living threads..
		if( empty ( $threads )  ){
			$this->_log( "enable default analysis: search engine indexing + alchemyApi servces");
			// Application_Model_ThreadsMapper::addThread( $this->user, "st",0, 'ready' );
			Application_Model_ThreadsMapper::addThread( $this->user, "al",0, 'ready' );
			
			$threads = Application_Model_ThreadsMapper::getThreads( $this->user);
			
		}
		
		# for each threads, use the timing
		foreach( $threads as $thread ){
			# check if there is a class attached to the given type
			if( !isset( $this->_threads[ $thread->type ] ) ){
				$this->_log( "warning: no threads of type '".$thread->type."' found!" );
				exit;
			}
			$this->_log( "using thread #{$thread->id}: ". $this->_threads[ $thread->type ] );
			// load classname
			$handlerClassName = $this->_threads[ $thread->type ];
		
			// the constructor calls its own analysis automatically
			$handler = new $handlerClassName( $document, $this );
			
			// if handler is not valid, set the document to "error" status
			if( $handler->isValid() ){
				if( !$this->debug ){
					$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'indexed' );
				}
				$this->_log( "status: indexed, changed: ".$affected );
			} else {
				if( !$this->debug ){
					$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'error' );
				}
				$this->_log( "status: error, changed: ".$affected );
			}
			
		}
		
		
		return true;
	}
	
	protected function _log( $message, $spacing=false ){
		Anta_Logging::append(  $this->log, $message, $spacing );
	}
	
	/**
	 * Get current thread.
	 * Rules:
	 * 
	 * a. If there are no thread "ready", the routine won't stop,
	 * 	  but send user a signal that there is nothing to do...
	 * b. If there is at least one thread "ready" and the given document is null,
	 *    the current thread is declared "died", and nothing happen. In the next cycle
	 *    a brand new thread will be loaded.
	 * c. If there is at least one thread "ready" and the given document is not null,
	 *    the function looks for loaded classes matching the thread type.
	 *    We declare custom type->class couple BEFORE starting the routine...
	 * d. IF there is no coupling type->class, the user log is affected.
	 * e. normal operation: current thread "ready" analyses the object.
	 */
	protected function _executeThreads( $document ){
		
		// get current 'ready' thread
		$thread = Application_Model_ThreadsMapper::getCurrentThread( $this->user);
		if( $this->debug ) echo "thread";
		// execute the analysis
		if( $document != null && $thread != null ){
			
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'indexing' );
			
			Anta_Logging::append( $this->log,
				"loaded thread: #".$thread->id." - ".$thread->type ."\n".
				"document: #".$document->id.", title: '".$document->title  ."'\n".
				"status: indexing, changed: ".$affected  ."\n", false 
			);
					
			// check if there is a coupling
			if( !isset( $this->_threads[ $thread->type ] ) ){
				Anta_Logging::append( $this->log, "fatal error! no threads of type '".$thread->type."' found!", false );
				exit;
			}
			
			// load classname
			$handlerClassName = $this->_threads[ $thread->type ];
		
			// the constructor calls its own analysis automatically
			$handler = new $handlerClassName( $document, $this );
		
			if( $this->debug ){
				
				$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'ready' );
				
				if( $handler->isValid() === false ){
					Anta_Logging::append( $this->log, "status: error (debug session only!), unchanged ".$affected , false );
					return;
				}
				
				Anta_Logging::append( $this->log, "status: ready (debug session only!), changed: ".$affected , false );
				return;
			} 
			
			// if handler is not valid, set the document to "error" status
			if( $handler->isValid() ){
				$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'indexed' );
				Anta_Logging::append( $this->log, "status: indexed, changed: ".$affected , false );
				return;
			}
			
			$affected = Application_Model_DocumentsMapper::changeDocumentStatus( $this->user, $document->id, 'error' );
			Anta_Logging::append( $this->log, "status: error, changed: ".$affected , false );
				
			
			return;
		}
		
		// the document is not null, but the thread is null (none ready)
		if( $document != null && $thread == null ){
			// let's try to add a process
			Application_Model_ThreadsMapper::restoreThreads( $this->user );
			if( $this->debug ) echo "document: ".$document->title.", elapsed: ".( microtime( true ) - $this->_startTime )."\n";
			
			return;
		}
		
		// il thread exists, solo che non c'é piu alcun documento 'ready'...
		if( $document == null && $thread != null ){
			// kill the thread, then ask for a new ready one.
			Application_Model_ThreadsMapper::killCurrentThread( $thread->id );
			
			Anta_Logging::append( $this->log,
				"no document ready, killing thread: #".$thread->id." - ".$thread->type,
				false
			);
			
			// get next ready thread
			$thread = Application_Model_ThreadsMapper::getCurrentThread( $this->user);
			
			// is null? then wait for new document
			if(  $thread == null ){
				Anta_Logging::append( $this->log,
					"nothing more to do, waiting for new documents...",
					false
				);
				if( !$this->debug ) usleep( $this->sleepingTime );
				return;
			}
			
			// is not null? try to restore documents!
			// reset ready documents
			// $documentsComeBack = Application_Model_DocumentsMapper::comeBackReady( $this->user );
			//Anta_Logging::append( $this->log,
			//	"documents come back 'ready': ".$documentsComeBack,
			//	false
			//);
			return;
		}
		
		// no docs, no thread. Just waitnig...
		if( !$this->debug ) usleep( $this->sleepingTime );
		return;
		
	}
	
	/**
	 * use this function before calling start()
	 * bind carefully type identifier with Anta_Distiller_Thread classnames
	 */
	public function addThreadHandler( $type, $handler ){
		$this->_threads[ $type ] = $handler;
	}
	
	
	/**
	 * Create a pid file in the same directory of logs
	 */
	protected function _createPidFile( $pid="" ){
		$pid = empty($pid)?getmypid():$pid;
		$filename = Anta_Logging::getLogsPath()."/".$this->namespace."_".$this->user->username.".pid";

		Anta_Logging::append( $this->log, "writing pid=$pid into '". basename($filename)  ."'", false );
		
		if( ! file_exists( $filename ) ){
			Anta_Logging::append( $this->log, "creating file... '". basename($filename)  ."'", false );
			if(! file_put_contents( $filename, $pid ) ){
				Anta_Logging::append( $this->log, "Cannot open file '".basename($filename)."' for writing ", false );
			
				$this->addRoutineError( "Cannot open file '".$filename."'" );
				exit;
			} 
			Anta_Logging::append( $this->log, "success!... '". basename($filename)  ."' created", false );
			return true;
		}
		return false;
	}
	
	protected function _deletePidFile(){
		$pidFile = Anta_Logging::getLogsPath()."/".$this->namespace."_".$this->user->username.".pid";
		
		// delete pid file
		if( ! @file_exists ( $pidFile ) ){
			Anta_Logging::append( $this->log, "warning: pid file '".basename( $pidFile )."' does not exists ", false );  
			return;
		}
		
		// file exists, try to unlink
		$pid = file_get_contents( $pidFile );
		
		
		Anta_Logging::append( $this->log, "saved pid: ".$pid.", actual pid: ".getmypid().", elapsed: ".( microtime( true ) - $this->_startTime) , false );
			
		if( @unlink( $pidFile ) ){
			Anta_Logging::append( $this->log, "remove pid: file '".basename( $pidFile )."' removed correctly", false );  
			return;
		}
		
		Anta_Logging::append( $this->log, "warning: pid file '".basename( $pidFile )."' has not been removed!", false );  
		
	}
	
	protected static $_instance;
	
	public static function getInstance(){
		if( self::$_instance == null ){
			self::$_instance = new Anta_Distiller();
		}
		return self::$_instance;
		
	}
	
	/**
	 * Class Constructor. Do not call it directly, use the getInstance() static method instead.
	 */
	public function __construct() {
		
		// register shutdown function
		register_shutdown_function(array($this, 'shutdown'));
		
		// registerexception handling function
		set_exception_handler( array( $this, 'exceptionHandler' ) );
		
		// save starting time
		$this->_startTime = microtime( true );
		
		// load ebug props
		$this->debug = isset( $_GET[ 'debug' ] );
		
		$options = array( "ehm, cmd line is not in use. You're browsing this page under apache, then you should add a http get param user with a valid user identifier", "u"=>"" );
		
		
		if( $this->debug === false ){
			$options = getopt("u:");
			$iniPath = getopt("c:");
		} 
		
		// load user from request, if any,or via arg if command line php
		$idUser = isset( $_REQUEST['user'] )? $_REQUEST['user']: $options[ 'u' ];
		
		
		
		// decrypt user id
		$idUser = Dnst_Crypto_SillyCipher::decrypt(  $idUser );
		
		// load user
		$this->user = Application_Model_UsersMapper::getUser( $idUser );

		
		if( $this->user == null ){
			$this->addRoutineError( "param '?user='$idUser' was not found, or is not a valid user\nreceived from cmd: ".implode( $options ) );
			// force exit;
			exit;
		}
		
		// the log filename
		$this->log = $this->namespace."log_".$this->user->username;
        
		$pid = getmypid();
		
        // save basic information
        Anta_Logging::append( $this->log, "user: ".get_current_user().", pid: ".$pid.", elapsed: ".( microtime( true ) - $this->_startTime) );
        
		// create pid file
        $this->_createPidFile( $pid );
        
		$this->_init();
	}

	protected function _init(){
		
		$options = array( "ehm, cmd line is not in use. You're browsing this page under apache, then you should add a http get param user with a valid user identifier", "u"=>"" );
		
		
		if( $this->debug === false ){
			$options = getopt("u:");
			$iniPath = getopt("c:");
		} 
		
		
        // check routine status
        $this->status = Application_Model_RoutinesMapper::getStatus( $this->user->id );
		
		if( $this->status == null ){
		
			// force creation
			$affected = Application_Model_RoutinesMapper::addRoutine( $this->user->id );
			Anta_Logging::append(
				$this->log,
				"warning: routine not enabled for user:".$this->user->username."\n".
				"adding routine: ".$affected, 
				false
			);
		}
    }
    
    
    
    
    public function addRoutineError( $message ){
		Anta_Logging::append(
			"routine_error_log",
			$message,
			true
		);
	}
    
    /**
     * Shutdown funcion
     */
    public function shutdown(){
		
		$report = 
			"memory peak: ". memory_get_peak_usage ( true )."\n".
			"errors: [". @implode( ", ",  error_get_last() )."]\n".
			"elapsed: ".( microtime( true ) - $this->_startTime )."\n".
			"--";
		;
		
		
		
		if( $this->user == null ){
			echo "\n".$report;
			return;
		}
		
		// try to kill the stuff
		Application_Model_RoutinesMapper::kill( $this->user->id );
		
		// delete pid file
		$this->_deletePidFile();
		
		// print log errors
		Anta_Logging::append( $this->log, $report, false );  
		
		
		
	}
	
	
	public function exceptionHandler( $exception ){
		Anta_Logging::append(  $this->log, "! exception:".$exception->getMessage(), false );
	}
}
?>
