<?php
/**
 * howto: // check identity in an action 
 *	           $user = $this->_authorizeUser( $this->_getUser() );
 */
class ApiController extends Application_Model_Controller_Api
{
	public function testConnectionAction(){
		$this->_response->setAction( 'test-connection' );
		echo $this->_response;
	}
	
	public function authenticateAction(){
		$this->_response->setAction( 'authenticate' );
		$this->_response->token = session_id();
		# crypt requests? otp to crypt request password
		if( $this->_request->getParam( "use-des-auth") != null ){
			$this->_response->use_des_auth = "not supported";
		}
		
		# already authenticated?
		if( Zend_Auth::getInstance()->hasIdentity() ){
			$this->_response->already_authenticated = true;
			$identity = Zend_Auth::getInstance()->getIdentity();
			$this->_response->authenticated_user = (object) array( "id" => $identity->cryptoId, "username"=>$identity->username);
		} else {
			if( Anta_Core::authenticateUser( $this->_request->getParams() )  === false ){
				$this->_response->throwError( "authentication failed");
			}
			$identity = Zend_Auth::getInstance()->getIdentity();
			$this->_response->authenticated_user = (object) array( "id"=>$identity->cryptoId, "username"=>$identity->username);
		}
		
		echo $this->_response;
	}
	
	/**
	 * return an object full of info
	 * $out = (object) array("indexed"=>0, "ready"=>0, "error"=>0, "coeff"=>(object)array("indexed"=>0, "error"=>0), "total"=>0)
	 */
	protected function _getCompletionCoeff(){
		$out = (object) array("indexed"=>0, "ready"=>0, "error"=>0, "coeff"=>(object)array("indexed"=>0, "error"=>0), "total"=>0);
		$query = "
			SELECT 
				SUM( IF( `status` = 'indexed', 1, 0) ) as indexed, 
				SUM( IF( `status` = 'ready', 1, 0) ) as ready,
				SUM( IF( `status` = 'error', 1, 0) ) as error,
				SUM(1) as total
			FROM `anta_{$this->_user->username}`.`documents` WHERE `ignore` = 0"
		;
		$stmt = Anta_Core::mysqli()->query( $query );
		
		$row = $stmt->fetchObject();
		if( $row == null){
			return $out;
		}
		#computate coeff
		if( $row->total == 0 ) return $out;
		$out->indexed = $row->indexed;
		$out->ready = $row->ready;
		$out->error = $row->error;
		$out->total = $row->total;
		$out->coeff->indexed = $row->indexed / $row->total;
		$out->coeff->error = $row->error / $row->total;
		return $out;
	}
	
	/**
	 * start process for the routine script. Alias for routine controller method.
	 * @todo set the ini path directly into the application.ini file
	 */
	protected function _doRoutine(){
		proc_close( proc_open (
			"php -c /etc/php5/apache2/php.ini ".APPLICATION_PATH."/routines/type-distiller.php -u".$this->_user->cryptoId." &" ,
			array(),
			$foo 
		));
	}
	
	public function stopRoutineAction(){
		$this->_response->setAction( 'stop-routine' );
		Anta_Logging::append( "distillerlog_".$this->_user->username, "received signal to stop routine. ", false, true );
		Application_Model_RoutinesMapper::setStatus( $this->_user->id, 'die' );
		
		// close opened processes
		
		echo $this->_response;
	}
	
	public function startStandardRoutineAction(){
		$this->_response->setAction( 'start-standard-routine' );
		$status = Application_Model_RoutinesMapper::getStatus( $this->_user->id );
		if ("status" == "start" ){
			$this->_response->throwError( "your routine is already working");
		}
		
		
		$this->_closeOpenRoutine();
		Anta_Logging::append( "distillerlog_".$this->_user->username, "starting or restarting analysis routine as requested", false, true );
		Application_Model_RoutinesMapper::setStatus( $this->_user->id, 'start' );
		$this->_doRoutine();
		// close opened processes
		
		echo $this->_response;
	}
	
	/**
	 * Read the last 10 lines of the log file and provide basic info about the analysis
	 */
	public function readLogAction(){
		$this->_response->setAction( 'read-log' );
		$user = $this->_user;
		
		$filename = Anta_Logging::getLogsPath()."/distillerlog_".$user->username;
		$this->_response->logFile = basename( $filename );
		$this->_response->corpus = $user;
		$lines = $this->_request->getParam( "lines" );
		$lines = empty($lines) || !is_numeric($lines)?15:$lines;
		
		$message = Anta_Logging::unixTail( $lines, $filename );
		
		// get getStatus
		$routine = Application_Model_RoutinesMapper::getStatus( $user->id );
		if( $routine == null ){
			$inserted = Application_Model_RoutinesMapper::addRoutine( $user->id );
			$routine = "died";
		}
		$this->_response->routine = $routine;
		
		// get completion
		$this->_response->completion = $this->_getCompletionCoeff();
		
		if( $routine != "died" ){
			// check status status (exit on error )
			$this->_getProcessStatus();
			
		}
		
		$this->_response->tail = $message;
		// get log
		echo $this->_response;
	}
    
	public function cleanLogAction(){
		$this->_response->setAction( 'clean-log' );
		$user = $this->_authorizeUser( $this->_getUser() );
		$filename = Anta_Logging::getLogsPath()."/distillerlog_".$user->username;
		
		if (!@unlink($filename)){
			$this->_response->throwError( "unable to unlink file '".basename($filename)."'");
		}
		echo $this->_response;
	}
	
	/**
	 * force exit the opened routine
	 */
	protected function _closeOpenRoutine( $message = "" ){
		Anta_Logging::append( "distillerlog_".$this->_user->username, "process id closed, routine terminated", false, true );
		if( !empty( $message ) ){
			Anta_Logging::append( "distillerlog_".$this->_user->username, $message, false, true );
		
		}
		Application_Model_RoutinesMapper::kill( $this->_user->id );
		
		
	}
	
	
	
	protected function _getLogFile(){
		
	}
	
	protected function _getProcessStatus(){
		$pidfile = Anta_Logging::getLogsPath()."/distiller_".$this->_user->username.".pid";
		$this->_response->pid_file = basename($pidfile);
		
		if( !file_exists( $pidfile ) ){
			$this->_closeOpenRoutine( "error: '".basename($pidfile)."' not found. Unable to search for a process-id" );
			$this->_response->throwError( "'".basename($pidfile)."' not found. Unable to search for a process-id");
		};
		$pid = $this->_response->pid = file_get_contents( $pidfile );
		
		$temporary = "/tmp/anta_".$this->_user->username."_pid_status";
		
		// search for process id
		exec ( "ps aux | grep type-distiller.php > {$temporary}" );
		
		$contents = file_get_contents( $temporary );
		unlink( $temporary );
		
		preg_match( '/[^\d]\s('.$pid.')\s/', $contents, $matches );
        if( count($matches) != 2 ){
        	$this->_closeOpenRoutine( "error: '".basename($pidfile)."' found, but we're unable to match process-id, check $temporary" );
			$this->_response->throwError( "'".basename($pidfile)."' found, but we're unable to match process-id, check $temporary");
		}
		if( $matches[1] != $pid ){
			$this->_closeOpenRoutine( "error: '".basename($pidfile)."' found, but no matching pid '$pid' found...find[".(implode(',',$matches))."]" );
			unlink( $pidfile );
			$this->_response->throwError( "'".basename($pidfile)."' found, but no matching pid found...");
		}

		
	}
	
    /**
     * return an array of couple(x,y) where x is the number of document sharing an entity
     * 1. count the number of distinct document sharing a certain entity, i.e the distro coeff.
     * 2. count the number of equally distro coeff (a.k.a groups of distro)
     * 
     * and y is the number
     * where:
     * 0 < x < total number of documents
     * and
     * 0 < y <= number of entities DISTRO found for each x documents 
     * 
     * take the prefix of the table as param.
     * 
     * return a json
     * {
	 *	 status:ok,
     *   info:{ max_distro:#, amount_of_documents:# }
     *   data:[ { aod:#, dis:#} ... ]
     * }
     *     
     * 
     */
    public function getEntitiesDistributionAction(){
		
		$this->_response->setAction( 'get-entities-distribution' );
		
		$user = $this->_authorizeUser( $this->_getUser() );
		
		$prefix = $this->_request->getParam( 'prefix' );
		
		$query = "
			SELECT distro, COUNT( distro ) as f_distro FROM 
			(
			SELECT 
			  id_{prefix}_entity as identifier,
			  COUNT( DISTINCT id_document ) as distro
			FROM anta_{$user->username}.{prefix}_entities_documents NATURAL JOIN anta_{$user->username}.{prefix}_entities 
			  WHERE 
				`ignore` = 0 and 
				 pid = 0
			  GROUP BY identifier
			) as distributions
			GROUP BY distro
			order by distro ASC";
		
		
	}
	
	public function getAlchemyDailyRequestAction(){
		$this->_response->setAction( 'get-alchemy-daily-request' );
		$this->_response->daily_request = Application_Model_QuotasMapper::getDailyRequest();
		
		echo $this->_response;
			
	}
	
	public function createCategoryAction(){
		$this->_response->setAction( 'create-category' );
		
		$user = $this->_authorizeUser( $this->_getUser() );
		
		// read the param name
		$category = $this->_request->getParam( 'name' );
		// read the param name
		$category = $this->_request->getParam( 'name' );
		
		if( $category == null ){
			$this->_response->throwError('Bad request: name not found');
		}
		
		// validate the param name, patterns with letters / number only, 255 chars max (cropped)
		$validChunkLength = strspn( strtolower( $category ), "-1234567890 abcdefghijklmnopqrstuvwxyzàèçé");
		
		// validate categories
		if( strlen( $category ) != $validChunkLength ){
			$this->_response->throwError('Bad request: name value does not seem to be valid');
		}
		
		// if there is a type, validate it
		$type = $this->_request->getParam( 'type' );
		
		
		// add category into stuff
		$insertedId = Application_Model_CategoriesMapper::add( $user, $category, $type );
		
		if( $insertedId == 0 ){
			$this->_response->throwError( I18n_Json::get( 'categoryDuplicated', 'errors' ) );
		}
		$this->_response->category   = $category;
		$this->_response->idCategory = $insertedId;
		// output response via json
		
		echo $this->_response;
	}
	
	/**
	 * create a super-entity into superEntities table.
	 *
	 * Required http params: 
	 *   1. user		- crypted id of the user
	 *   2. label		- super entity label
	 *   2. entities	- a json serialized array of entities, where keys are table prefixes and values are array of numeric integer id
	 *
	 * Request Sample:
	 *   /api/entities-merge/user/y9j?entities
	 */
	public function entitiesMergeAction(){
		
		$this->_response->setAction( 'entities-merge' );
		
		// basic params
		$user = $this->_getUser();
		$prefix = $this->_response->prefix = $this->_getPrefix();
		
		// read the label and validate it
		$label = $this->_request->getParam( 'label' );
		
		$labelValidator = new Ui_Forms_Validators_Pattern( array(
			"minLength" => 2,
			"pattern"   => Ui_Forms_Validators_Pattern::$LABEL,
			"patternDescription" => " should contain a-z and special UTF-8 encoded chars."
		));
		
		if( !$labelValidator->isValid( $label ) ){
			$this->_response->throwError( "'label' is not valid: ". implode ( ", ", array_keys( $labelValidator -> getMessages() ) ) );
		}
		
		// read the ids, unjsonize
		$entities = $this->_request->getParam( 'entities' );
		
		// exit if there are any errors
		if( empty( $entities ) || !is_array( $entities ) ){
			$this->_response->throwError(  "'entities' param wasn't found or is not valid" );
		}
		
		// create or get the entity
		try{
			$idSuperEntity = Application_Model_Rws_EntitiesMapper::add( $user, $label, "MN" );
		} catch( Exception $e ){
			$this->_response->throwError( $e->getMessage() );
		}
		if( $idSuperEntity == 0 ){
			$this->_response->throwError( "mmm... unable to save super entity '".$label."' correctly" );
		}
		$entitiesToMerge = array();
		// check for validity
		foreach( $entities as $id => $content ){
			// get components of entity_prefix_id
			if(!isset( $content['id'] ) ){
				$this->_response->throwError( "no a valid content" );
				continue;
			}
			if( $content['prefix'] != $prefix ){
				$this->_response->throwError( "no a valid prefix, given ".$content['prefix'] );
				continue;
			}
			$entitiesToMerge[] =  $content['id'];
		}
		// merge multiple values
		
		try{
			$merged[ $prefix."_". $idSuperEntity ] = Application_Model_SubEntitiesMapper::mergeEntities( 
				$user, $prefix, 
				$entitiesToMerge, 
				$idSuperEntity
			);
		} catch( Exception $e ){
			$this->_response->throwError( $e->getMessage() );
		}
		$this->_response->affected = $entitiesToMerge;
		$this->_response->created = array("label"=>$label);
		echo $this->_response;
	}
	
	
	
	public function createEntityAction(){
		$this->_response->setAction( 'create-entity' );
		
		if( !Zend_Auth::getInstance()->hasIdentity() ){
			$this->_response->throwError( "not authenticated" );
		}
		
		// check if /entity exists and is valid, using a validator
		$entity = $this->_request->getParam( 'entity' );
		
		$entityValidator = new Ui_Forms_Validators_Entity( array(
			'minLength' => 3,
			'maxLength' => 100
		));
		if( $entityValidator->isValid( $entity ) === false ){
			$this->_response->throwError( "entity: " . implode( array_keys( $entityValidator->getMessages() ) ) );
		};
		// check if /entity exists and is valid, using a validator
		$type = $this->_request->getParam( 'type' );
		
		$typeValidator = new Ui_Forms_Validators_Entity( array(
			'minLength'  => 3,
			'maxLength'  => 100
		));
		
		if( $typeValidator->isValid( $type ) === false ){
			$this->_response->throwError( "type: ". implode( array_keys( $typeValidator->getMessages() ) ) );
		};
		
		// check if the document param exists
		$idDocument = $this->_request->getParam( 'document' );
		
		if( empty( $idDocument ) ){
			$this->_response->throwError( "document not found" );
		}
		
		
		// check if /language exists and is valid, using a validator
		$language = $this->_request->getParam( 'language' );
		$language = $language == null? "--":  $language;
		
		
		
		// check if /user/ provided via get param is a valid one
		$user = $this->_getUser();
		
		// check identity
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		// check user id and identity id
		if( ! ( $identity->is ('admin') || $user->id == $identity->id ) ){
			$this->_response->throwError( "not authorized" );
		}
		
		// check if document is valid
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( "document" ) );
		$document = Application_Model_DocumentsMapper::getDocument( $user, $idDocument );
		
		if( $document == null ){
			$this->_response->throwError( "document not valid" );
		}
		
		$idEntity = Application_Model_EntitiesMapper::addEntity( $user, $entity, "", $type, 1, $language, "M" );
		
		if( Application_Model_DocumentsMapper::hasEntity( $user, $document->id, $idEntity ) ){
			$this->_response->throwError( "entity '$entity' is already related to the document. Change its value manually" );
		}
		
		// try to bound to the doc
		$idEntityDocument = Application_Model_DocumentsMapper::addEntity( $user, $document->id, $idEntity, '', 1, "M" );
		
		
		
		
		// successfully created
		$this->_response->type = $type;
		$this->_response->entity = $entity;
		$this->_response->document = $document->id;
		$this->_response->duplicated = $idEntity == 0;
		
		
		
		$this->_response->user = $user->username;
		
		echo $this->_response;
	}
	
	
	/** 
	 * no http-param required
	 * return graphs[{ id:0009, s:status, d:description, e: error ]
	 */
	public function graphsGetAllAction(){
		$this->_response->setAction( 'graphs-get-all' );
		$user = $this->_getUser();
		
		$graphs = Application_Model_GraphsMapper::getGraphs( $user );
		
		$this->_response->graphs = $graphs->results;
		
		echo $this->_response;
	}
	
	public function graphCreateGexfAction(){
		$this->_response->setAction( 'graph-create-gexf' );
		$user = $this->_getUser();
		
		$graphId = Application_Model_GraphsMapper::addGraph( $user, new Application_Model_Graph(
			0, 'tina'
		));
		
		if( $graphId == 0 ){
			$this->_response->throwError( "connection troubles, graph not created" );
		}
		// gexf-creator.php?user=mgu&graph=1
		// try to create the graph via script
		proc_close( proc_open (
			"php -c /etc/php5/apache2/php.ini ".APPLICATION_PATH."/routines/gexf-creator.php -u".$user->cryptoId." -g".$graphId." &" ,
			array(),
			$foo 
		));
		
		// output ok
		echo $this->_response;
	}
	
	public function getCompletionPercentageAction(){
		$this->_response->setAction( 'get-completion-percentage' );
		$user = $this->_getUser();
		
		# number of docs
		$stmt = Anta_Core::mysqli()->query( "SELECT count(*) as number_of_documents FROM anta_{$user->username}.`documents` WHERE `ignore`= 0" );
		$numOfDocuments = $stmt->fetchObject()->number_of_documents;
		
		# number of indexed docs
		$stmt = Anta_Core::mysqli()->query("SELECT count(*) as number_of_indexed_documents FROM anta_{$user->username}.`documents` WHERE `ignore`= 0 AND status = 'indexed'");
		$numOfIndexedDocuments =  $stmt->fetchObject()->number_of_indexed_documents;
		
		# computate percentage
		$percentage = $numOfDocuments == 0? 0: 100* $numOfIndexedDocuments / $numOfDocuments;
		
		$this->_response->totalItems = $numOfDocuments;
		$this->_response->indexedItems = $numOfIndexedDocuments;
		
		$this->_response->percentage = $percentage;
		
		echo $this->_response;
		
	}
	
	public function graphGetGexfAction(){
		$this->_response->setAction( 'graph-get-gexf' );
		$user = $this->_getUser();
		
		
		// get graph
		$graph = Application_Model_GraphsMapper::getGraph( $user,  $this->_request->getParam( "graph" ));
		
		if( $graph == null ){
			$this->_response->throwError( "graph not valid" );
		}
		
		$this->_response->graph = $graph;
		
		if( $graph->localUrl == -1 ){
			$this->_response->throwError( "gexf graph url is not available" );
		}
		
		$graphfile = APPLICATION_PATH ."/../gexf/".$graph->localUrl;
		
		# check for file existance
		if( ! file_exists( $graphfile ) ){
			exit( "file 'gexf/".basename($graphfile)."' not found!" ) ;
		}
		
		# output headers
		Anta_Core::setHttpHeaders( 
			mime_content_type ( $graphfile ),
			basename( $graph->localUrl ),
			true
		);
		
		
		echo file_get_contents( $graphfile );
		
	}
	
	/**
	 * "order an graph, and start python"
	 */
	public function graphOrderAuto(){
		$this->_response->setAction( 'graph-order-auto' );
		$user = $this->_getUser();
		$graphId = Application_Model_GraphsMapper::addGraph( $this->_user, new Application_Model_Graph(
			0, 'tina'
			));
			
			if( $graphId == 0 ){
				Anta_Core::setError( I18n_Json::get('graph not added correctly','errors') );
				return $this->visualizationAction();
			}
			
			// launch the process, in background
			// echo "zendify.py ".$this->_user->id;
				
			$py = new Py_Scriptify( "zendify.py make_graph ".$this->_user->id. " ".$graphId, false );
			$py->silently();
			echo $py->command;
			Anta_Core::setMessage( I18n_Json::get('graph added correctly') );
	}
	
	/**
	 * "order an graph, and start python"
	 */
	public function graphDoAllDocsOrderAuto(){
		$this->_response->setAction( 'graph-do-all-docs-order-auto' );
		$user = $this->_getUser();
		$graphId = Application_Model_GraphsMapper::addGraph( $this->_user, new Application_Model_Graph(
			0, 'tina'
			));
			
			if( $graphId == 0 ){
				Anta_Core::setError( I18n_Json::get('graph not added correctly','errors') );
				return $this->visualizationAction();
			}
			
			// launch the process, in background
			// echo "zendify.py ".$this->_user->id;
				
			$py = new Py_Scriptify( "zendify.py do_all_docs ".$this->_user->id. " ".$graphId, false );
			$py->silently();
			echo $py->command;
			Anta_Core::setMessage( I18n_Json::get('graph added correctly') );
	}
	
	/**
	 * to be used inside inteerface perpare visualization
	 */
	public function graphUpdateStatsAction(){
		$this->_response->setAction( 'graph-update-stats' );
		
		// do not need to be authenticated
		$user = $this->_getUser();
		
		// the three types of graph
		$graphDte = $this->_request->getParam("doc-to-ent");
		$graphDtd = $this->_request->getParam("doc-to-doc");
		$graphEte = $this->_request->getParam("ent-to-ent");
		
		// table prefix @todo check table prefix
		$prefix = $this->_request->getParam("prefix");
		$prefix = empty( $prefix )? "rws": $prefix;
		
		// all tags (document tags ) selected?
		$selectAllDocumentsTags = $this->_request->getParam("all-documents-tags");
		
		$selectAllEntitiesTags = $this->_request->getParam("all-entities-tags");
		
		// the exception
		$entitiesTags  = $this->_request->getParam("exception-entities-tags");
		$documentsTags = $this->_request->getParam("exception-documents-tags");
		
		// setup graph information
		$graphs = array(
			"doc_to_ent" => empty($graphDte)? true: $graphDte==="false"? false: true,
			"doc_to_doc" => empty($graphDtd)? true: $graphDtd==="false"? false: true,
			"ent_to_ent" => empty($graphEte)? true: $graphEte==="false"? false: true
		);
		
		// setup entities info
		$entities = (object) array(
			"tags"		=> (object) array( "select_all" => empty( $selectAllEntitiesTags  )? true: $selectAllEntitiesTags  == "true"? true: false, "exception"=>array()),
			"loaded"	=> 0,
			"unignored" => 0,
			"total"		=> 0
		);
		
		
		// load documents information
		$documents = (object) array(
			"tags"		=> (object) array( "select_all" => empty( $selectAllDocumentsTags  )? true: $selectAllDocumentsTags  == "true"? true: false, "exception" => array()),
			"loaded"	=> 0,
			"unignored"	=> 0,
			"total"		=> 0
		);
		
		# step 1. the easy way. all the docs and all the entities.
		if( $documents->tags->select_all && $entities->tags->select_all && empty( $entitiesTags ) && empty( $entitiesTags ) ){
			$documents->loaded = $documents->unignored = Application_Model_DocumentsMapper::getFilteredNumberOfDocuments( $user, "ignore", 0 );
			$documents->total = Application_Model_DocumentsMapper::getNumberOfDocuments( $user );
			$entities->loaded =  $entities->unignored = Application_Model_SubEntitiesMapper::getFilteredNumberOfEntities( $user, $prefix, "ignore", 0 );
			$entities->total =  Application_Model_SubEntitiesMapper::getNumberOfEntities( $user, $prefix );
			
			// print out information collected
			$this->_response->documents = $documents;
			$this->_response->entities = $entities;
			$this->_response->graphs = $graphs;
			// load total documents
			
			exit( $this->_response );
		}
		
		# step 2. some exceptions...
		
		
		if( $documents->tags->select_all == false ){
			$entities->loaded  = 0;
			$documents->loaded = 0;
		}
		
		// provide the documents tags according to the "selectAll" flag 
		
		// $documents->tags->exception = empty( $this->_request->getParam("all-documents-tags") )?false: true;
		
		// load entities information
		
		// print out information collected
		$this->_response->documents = $documents;
		$this->_response->entities = $entities;
		$this->_response->graphs = $graphs;
		// load total documents
		
		echo $this->_response;
	}
	
	/**
	 * Invert ignore onto entities FOREACH prefix found...
	 * http-param: having-ignore, expolicitely 'false' or 'true'. default is true...
	 */
	public function entitiesToggleIgnoreAction(){
		
		$this->_response->setAction( 'entities-toggle-ignore' );
		
		$havingIgnore =  $this->_request->getParam( 'having-ignore' );
		
		// if prefix matches prefixes
		$prefixes =  Anta_Core::getEntitiesPrefixes();
		
		if( $havingIgnore == null ){
			$this->_response->throwError( "having-ignore param should be set expolicitely 'false' or 'true' and should not be omitted" );
		}
		
		$havingIgnore = $havingIgnore == "false"? 0: 1;
		$updateIgnore = $havingIgnore == 0? 1: 0;
		
		// index are prefixes, values are affected rows per prefix...
		$affecteds = array();
		
		// revert all the entities having ignore = 1 
		foreach( $prefixes as $prefix ){
			$query = "UPDATE anta_{$this->_user->username}.{$prefix}_entities set ignore = ? WHERE ignore = ?";
			$stmt = Anta_Core::mysqli()->query( $query , array( $updateIgnore, $havingIgnore ) );
			$affected[ $prefix ] = $stmt->rowCount();
		}
		
		$this->_response->prefixes = $prefixes;
		$this->_response->updateIgnore;
		$this->_response->havingIgnore = $havingIgnore;
		$this->_response->affected = $affected;
		
		echo $this->_response;
	}
	
	/**
	 * Attach a single tags to a lot of entities.
	 * if the special param all-in exists...
	 */
	public function entitiesAttachTagAction(){
		$this->_response->setAction( 'entities-attach-tag' );
		$user = $this->_authorizeUser( $this->_getUser() );
		
		// read the params
		$entities = $this->_request->getParam( 'entities' );
		$tag = $this->_request->getParam( 'tag' );
		
		// exit if there are errors
		if( empty( $entities ) || !is_array( $entities ) ){
			$this->_response->throwError(  "required 'entities' param was not found or is not valid" );
		}
		
		if( empty( $tag ) ){
			$this->_response->throwError(  "required 'tag' param was not found" );
		}
		
		// save tag
		$idTag = Application_Model_TagsMapper::add( $user, $tag, 'type' );
		
		if( $idTag == 0 ){
			$this->_response->throwError(  "verify your tag. 'tag' param contains a not valid value" );
		}
		
		$affected = array();
		foreach( $entities as $entity ){
			// skip prefix that does not check...
			// if prefix matches prefixes
			if( !in_array( $entity[ 'prefix' ],  Anta_Core::getEntitiesPrefixes()  ) ){
				$this->_response->throwError( "prefix '". $entity[ 'prefix' ]."'does not match!" );
			}
			$affected[ $entity[ 'prefix' ]."_".$entity[ 'id' ] ] = Application_Model_SubEntitiesTagsMapper::addEntityTag( $user, $entity[ 'prefix' ], $entity[ 'id' ], $idTag );
		}
		
		$this->_response->affected = $affected;
		$this->_response->id_tag = $idTag;
		echo $this->_response;
	}
	
	/**
	 * detach a specific tagfrom an entity
	 * @param http id-tag		- numeric identifier
	 * @param http id-entity	- numeric identifier
	 * @param http prefix		- table identifier, e.g rws
	 */
	public function entitiesDetachTagAction(){
		$this->_response->setAction( 'entities-detach-tag' );
		
		$idTag = $this->_request->getParam("id-tag");
		
		if( empty( $idTag ) || !is_numeric(  $idTag ) ){
			$this->_response->throwError( "value 'id-tag' was not found or is not valid" );
		} 
		
		$idEntity = $this->_request->getParam("id-entity");

		if( empty( $idEntity ) || !is_numeric(  $idEntity ) ){
			$this->_response->throwError( "value 'id-entity' was not found or is not valid" );
		} 
		
		$prefix = $this->_request->getParam("prefix");
		
		if( !in_array( $prefix, Anta_Core::getEntitiesPrefixes() ) ){
			$this->_response->throwError( "value 'prefix' was not found or is not valid" );
		}
		
		$affected = $this->_response->affected = Application_Model_SubEntitiesTagsMapper::removeEntityTag( $this->_user, $prefix, $idEntity, $idTag);
		$this->_response->detached = array(
			"id_entity" => $idEntity,
			"prefix"    => $prefix,
			"id_tag"    => $idTag
		);
		
		
		echo $this->_response;
		
	}
	
	/**
	 * unbind entioty from its parent. Simply flag its pid field to 0
	 */
	public function entityUnbindPidAction(){
		$this->_response->setAction( 'entity-unbind-pid' );
		
		$user = $this->_getUser();
		
		// check prefix
		$prefix =  $this->_request->getParam( 'prefix' );
		
		// check entity
		$entity = $this->_request->getParam( 'id' );
		
		// if prefix matches prefixes
		if( !in_array( $prefix,  Anta_Core::getEntitiesPrefixes() ) ){
			$this->_response->throwError( "prefix '".$prefix."'does not match!" );
		}
		
		$this->_response->prefix = $prefix;
		$this->_response->entity = $entity;
		
		// change
		// $this->_response->affected = Application_Model_SubEntitiesMapper::setPid( $user, $prefix, $entity, 0 );
		
		echo $this->_response;
	}
	/**
	 * flag signle entity to ignore =  or false according to http param"undo" presence.
	 * For multiple entities, ref to ignoreEntitiesAction method.
	 * This function requires:
	 * @param user		- http GET param crypted id
	 * @param prefix	- http GET param entity table prefix
	 * @param id		- http GET param uncrypted entity id
	 * 
	 * Note that this function will not check for entity id validity.
	 */
	public function entityIgnoreAction(){
		$this->_response->setAction( 'entity-ignore' );
		
		$entities = $this->_request->getParam( "entities" );
		$ignore = $this->_request->getParam( 'undo' ) == null? true: false;
		
		// check selected all
		$useFilters = $this->_request->getParam( "use-filters" ) == "true"? true: false;
		if( $useFilters ) {
		
			$filters =  $this->_request->getParam("filters") ;
			
			$user = $this->_getUser();
			
			$where = array(); // array of where clauses
			$binds = array(); // array of binds
			
			// build select all query
			if( !empty(  $filters['tags'] ) && is_array(  $filters['tags'] ) ){
				$this->_response->useTags = true;
				$where[] = "id_tag IN(".sbind( $filters['tags'] ).")";
				$binds = array_merge( $binds, $filters['tags'] );
			}
			
			if( !empty(  $filters['query'] ) && strlen(  $filters['query'] ) > 0 ){
				$this->_response->useQuery = true;
				$where[] = "content LIKE ?";
				$binds[] = "%{$filters['query']}%";
			}
			
			$affected = 0;
			
			if( !empty( $where ) ){
				$stmt = Anta_Core::mysqli()->query( "
					SELECT `id_rws_entity`
							FROM `anta_{$user->username}`.`rws_entities`
							LEFT OUTER JOIN `anta_{$user->username}`.`rws_entities_tags` USING( `id_rws_entity` ) 
							WHERE ".implode(" AND ", $where ) ."
					",$binds
				);
				
				while( $row = $stmt->fetchObject() ) {
					$entities[] = $row->id_rws_entity;
				}
				
				// separate into cycles
				$this->_response->cycles = $cycles = ceil( count( $entities ) / 1000 );
				for( $i = 0 ; $i < $cycles; $i++ ){
					$spliced = array_splice ( $entities, max( 0, count( $entities ) - 1000 ) );
					$stmt = Anta_Core::mysqli()->query( "
						UPDATE `anta_{$user->username}`.`rws_entities` SET `ignore` =  ".($ignore?1:0 )." 
							".( empty( $where )? '': "
								WHERE `id_rws_entity` IN(
									".sbind( $spliced  )."
								)
							"),
						$spliced 
					);
					$affected = $stmt->rowCount();
				}
			} else {
				$stmt = Anta_Core::mysqli()->query( "UPDATE `anta_{$user->username}`.`rws_entities` SET `ignore` =  ".($ignore?1:0 ));
				$affected = $stmt->rowCount();
			}
			
			$this->_response->unsingFilters = true;
			$this->_response->affected = $affected;
			$this->_response->query = $query;
			exit( $this->_response );
			
			
		}
		$this->_response->unsingFilters = false;
		
		// check entities array
		if( !is_array( $entities )){
			$this->_response->throwError( "'entities' param should be an array" );
		}
	
		$user = $this->_getUser();
		
		$affected = array();
		
		$prefixes = Anta_Core::getEntitiesPrefixes();
		
		foreach( $entities as $entity ){
			// skip prefix that does not check...
			// if prefix matches prefixes
			if( !in_array( $entity[ 'prefix' ], $prefixes  ) ){
				$this->_response->throwError( "prefix '". $entity[ 'prefix' ]."'does not match!" );
			}
			$affected[ $entity[ 'prefix' ]."_".$entity[ 'id' ] ] = Application_Model_SubEntitiesMapper::setIgnore( $user, $entity[ 'prefix' ], $entity[ 'id' ], $ignore );
		}
		
		$this->_response->affected = $affected;
		$this->_response->modified = array_sum ( $affected );
		$this->_response->ignore = $ignore;
		
		
		echo $this->_response;
	}
	
	/**
	 * flag a lot of entities entity to ignore = true. For multiple entities, ref to ignoreEntities
	 * rquires twwo params
	 */
	public function ignoreEntitiesAction(){
			$this->_response->setAction( 'ignore-entity' );
		
		$user = $this->_getUser();
		
		// extract entities
		$entities =  $this->_request->getParam( 'entities' );
		
		// check documents
		if( !is_array( $entities )){
			$this->_response->throwError( "'entities' param should be an array" );
		}
		
		$ids = array();
		
		$affectedEntities = array();
		$affected = 0;
		foreach( $entities as $id=>$entity ){
			
			if( $id == "length" ) continue;
			
			// split se_rws_0000999 to find prefixes and ids
			$splitted = explode("_",$id);
			
			if( count( $splitted ) != 3 ){
				$this->_response->throwError( "id string '".$id."' does not seem to be valid! should be in the form: 'se_rws_0000999'" );
			}
			
			$prefix = $splitted[1];
			
			if( !in_array( $prefix,  Anta_Core::getEntitiesPrefixes() ) ){
				$this->_response->throwError( "prefix '".$prefix."'does not match!" );
			}
			
			$ignore = $this->_request->getParam( 'undo' ) == null? true: false;
			$affected += Application_Model_SubEntitiesMapper::setIgnore( $user, $prefix, $splitted[2], $ignore );
			
			
			$affectedEntities[] = $id;
			
		}
		
		// put back entities
		$this->_response->entities = $affectedEntities;
		$this->_response->ids = $ids;
		echo $this->_response;
	}
	
	/**
	 * return a list of tags / id tags according to prefix table
	 * @param http prefix		- table identifier, e.g rws
	 */
	public function entitiesGetTagsAction(){
		$this->_response->setAction( 'entities-get-tags' );
		$prefix = $this->_response->prefix = $this->_getPrefix();
		$ignore = $this->_request->getParam("ignore");
		$ignore = ($ignore == "1" || $ignore == "0")? $ignore : -1;
		$tags = $this->_response->tags = Application_Model_SubEntitiesTagsMapper::getTableTags( $this->_user, $prefix , $ignore);
		echo $this->_response;
	}
	
	
	public function entitiesGetContentsAction(){
		$this->_response->setAction( 'entities-get-contents' );
		$prefix = $this->_response->prefix = $this->_getPrefix();
		$entities = $this->_request->getParam("entities");
		$this->_response->sample = http_build_query(array("entities"=>array(123,145,165) ));
		
		if( empty( $entities ) || !is_array( $entities ) ){
			$this->_response->throwError( "'entities' param was not found or is not valid" );
		}
		
		$tags = $this->_response->entities = Application_Model_SubEntitiesMapper::getEntitiesByIds( $this->_user, $prefix, $entities );
		echo $this->_response;
	}
	
	
	
    public function modifyEntityAction(){
		$this->_response->setAction( 'modify-entity' );
		// $this->_response->throwError( "service unavailable" );
		if( !Zend_Auth::getInstance()->hasIdentity() ){
			$this->_response->throwError( "not authenticated" );
		}
		
		// check user
		$user = $this->_getUser();
		
		// check identity
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		// check user id and identity id
		if( ! ( $identity->is ('admin') || $user->id == $identity->id ) ){
			$this->_response->throwError( "not authorized" );
		}
		
		
		// read data message
		
		$data =  $this->_request->getParam( 'entity' );
		
		if( $data == null ){
			$this->_response->throwError( "eroor in your request... entity param was not found" );
		}
		
		$affected = array();
		$merged = array();
		foreach( $data as $id=>$content ){
			// $this->_response->executed->id = Application_Model_EntitiesMapper::modifyEntity( $user, $id, $content );
			
			// get components of entity_prefix_id
			$idComponents = explode("_", $id );
			
			// id must be splitted into 3 components
			if( count( $idComponents ) != 3 ){
				$this->_response->throwError( "error in your request... entity id '".$idComponents."' is malformed" );
			}
			
			// check prefix
			if( !in_array( $idComponents[1], array( "super", "ngr", "rws" ) ) ){
				$this->_response->throwError( "prefix table '".$idComponents[1]."' was not found" );
			}
			
			$prefix = $idComponents[ 1 ];
			$id     = $idComponents[ 2 ];
			
			$entityContent = stripcslashes( trim( $content ) );
			
			$result = Application_Model_SubEntitiesMapper::setContent( $user, $prefix, $id, $entityContent );
			if( $result == 0 ){
				// merge if it has the same name!
				$merged[  $prefix."_". $id] = true;
				// get merged entity
				$mergedEntity = Application_Model_SubEntitiesMapper::getEntityByContent( $user, $prefix, $entityContent );
				
				// this is the same...
				if( $mergedEntity->id ==  $prefix."_". $id ){
					
					unset( $merged[  $prefix."_". $id] );
				
				} else if( $mergedEntity != null ){
					
					// merge entity with already existing ones
					$merged[  $prefix."_". $id] = Application_Model_SubEntitiesMapper::mergeEntities( $user, $prefix, array( $id ), $mergedEntity->table_id );
					
					// reactivate the entity
				} else{
					$merged[  $prefix."_". $id] = "utf8 problem";
				}
			}
			// $affected[ $id ] = array("t"=>$content,"r"=>merged, "pt"=>
		}
		$this->_response->merged = $merged;
		
		
		$this->_response->modifjed = $data;
		$this->_response->user = $user->username;
		
		echo $this->_response;
	}
    
	
	
	/**
	 * change documents ignore setting
	 */
	public function documentSetIgnoreAction(){
		$this->_response->setAction( 'document-set-ignore' );
		
		$documents = $this->_request->getParam( "documents" );
		$ignore = $this->_request->getParam( "ignore" );
		
		// check documents
		if( !is_array( $documents )){
			$this->_response->throwError( "'documents' param should be an array" );
		}
		
		// check setting
		if( $ignore != 'on' && $ignore != 'off' ){
			$this->_response->throwError( "ignore param '".$ignore."' is not a valid on/off value... sorry!" );
		}
		
		$this->_response->ignore = I18n_Json::get( $ignore );
		
		// check user
		$user = $this->_getUser();
		
		$modified = array();
		foreach ($documents as $cryptoId){
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $cryptoId );
			$modified[  $cryptoId ]  = Application_Model_DocumentsMapper::setIgnore( $user, $idDocument, $ignore=='off'?'1':'0' );
		}
		
		$this->_response->modified = $modified;
		
		echo $this->_response;
	}
	
	/**
	 * change documents language
	 */
	public function documentSetLanguageAction(){
		$this->_response->setAction( 'document-set-language' );
		
		$documents = $this->_request->getParam( "documents" );
		$language = $this->_request->getParam( "language" );
		
		// check language
		if( !is_array( $documents )){
			$this->_response->throwError( "'documents' param should be an array" );
		}
		
		// check language
		if( !in_array( $language, Anta_Core::getAvailableLanguages() ) ){
			$this->_response->throwError( "language '".$language."' is not a valid language... sorry!" );
		}
		
		$this->_response->language = I18n_Json::get( $language );
		
		// check user
		$user = $this->_getUser();
		
		$modified = array();
		foreach ($documents as $cryptoId){
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $cryptoId );
			$modified[  $cryptoId ]  = Application_Model_DocumentsMapper::setLanguage( $user, $idDocument, $language );
		}
		
		$this->_response->modified = $modified;
		
		echo $this->_response;
	}
	
	/**
	 * change documents mimetype according to Anta_Core::getAvailableMimetypes()
	 * list of supported mimetypes
	 */
	public function documentSetMimetypeAction(){
		$this->_response->setAction( 'document-set-mimetype' );
		
		$documents = $this->_request->getParam( "documents" );
		$mimetype = $this->_request->getParam( "mimetype" );
		
		// check document
		if( !is_array( $documents )){
			$this->_response->throwError( "'documents' param should be an array" );
		}
		
		// check language
		if( !in_array( $mimetype, Anta_Core::getAvailableMimetypes() ) ){
			$this->_response->throwError( "mimetype '".$mimetype."' is not a valid mimetype... sorry!" );
		}
		
		$this->_response->mimetype = basename( $mimetype );
		
		// check user
		$user = $this->_getUser();
		
		$modified = array();
		foreach ($documents as $cryptoId){
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $cryptoId );
			$modified[  $cryptoId ]  = Application_Model_DocumentsMapper::setMimetype( $user, $idDocument, $mimetype );
		}
		
		$this->_response->modified = $modified;
		
		echo $this->_response;
	}
	
	/**
	 * change documents language
	 */
	public function documentRemoveAction(){
		$this->_response->setAction( 'document-remove' );
		
		$documents = $this->_request->getParam( "documents" );
		
		// check documents array
		if( !is_array( $documents )){
			$this->_response->throwError( "'documents' param should be an array" );
		}
		
		// check user and his session
		$user = $this->_getUser();
		
		$removed = array();
		foreach ($documents as $cryptoId){
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $cryptoId );
			$removed[  $cryptoId ] = Application_Model_DocumentsMapper::removeDocument(  $user, $idDocument );
		}
		
		$this->_response->removed = $removed;
		
		echo $this->_response;
	}
	
	/**
	 * return a list of tags / id tags according to prefix table
	 * @param http prefix		- table identifier, e.g rws
	 */
	public function documentsGetTagsAction(){
		$this->_response->setAction( 'documents-get-tags' );
		$tags = $this->_response->tags = Application_Model_DocumentsTagsMapper::getAvailableTags( $this->_user );
		echo $this->_response;
	}
	
	public function documentsAttachTagAction(){
		$this->_response->setAction( 'documents-attach-tag' );
		$user = $this->_authorizeUser( $this->_getUser() );
		
		// read the params
		$this->_response->documents = $documents = $this->_request->getParam( 'documents' );
		$this->_response->tag = $tag = $this->_request->getParam( 'tag' );
		$this->_response->category = $category = $this->_request->getParam( 'category' );
		$this->_response->useFilters = $useFilters = $this->_request->getParam( 'use-filters' ) == null?false:true;
		
		// exit if there are errors
		if( empty( $documents ) || !is_array( $documents ) ){
			$this->_response->throwError(  "required 'documents' param was not found or is not valid" );
		}
		
		if( empty( $tag ) ){
			$this->_response->throwError(  "required 'tag' param was not found" );
		}
		
		// save tag
		$idTag = Application_Model_TagsMapper::add( $user, $tag, $category );
		
		if( $idTag == 0 ){
			$this->_response->throwError(  "verify your tag. 'tag' param contains a not valid value" );
		}
		$this->_response->id_tag = $idTag;
		$affected = array();
		foreach( $documents as $document ){
			// get doc id
			$idDocument = Dnst_Crypto_SillyCipher::decrypt( $document[ 'id' ] );
			if( !is_numeric( $idDocument ) ) continue;
			$affected[ $document[ 'id' ] ] = Application_Model_DocumentsTagsMapper::add( $user, $idDocument, $idTag );
		}
		
		$this->_response->affected = $affected;
		$this->_response->id_tag = $idTag;
		
		echo $this->_response;
	}
	
	/**
	 * detach a specific tagfrom an entity
	 * @param http id-tag		- numeric identifier
	 * @param http document		- numeric identifier
	 * @param http prefix		- table identifier, e.g rws
	 */
	public function documentsDetachTagAction(){
		$this->_response->setAction( 'documents-detach-tag' );
		
		$idTag = $this->_request->getParam("id-tag");
		
		if( empty( $idTag ) || !is_numeric(  $idTag ) ){
			$this->_response->throwError( "value 'id-tag' was not found or is not valid" );
		} 
		
		$idDocument = $this->_request->getParam("document");

		if( empty( $idDocument ) || !is_numeric(  $idDocument ) ){
			$this->_response->throwError( "value 'document' was not found or is not valid" );
		} 
		
		
		$affected = $this->_response->affected = Application_Model_DocumentsTagsMapper::remove( $this->_user, $idDocument, $idTag);
		$this->_response->detached = array(
			"id_doc" => $idDocument,
			"id_tag" => $idTag
		);
		
		
		echo $this->_response;
		
	}
	
	public function documentsCleanTitleAction(){
		$this->_response->setAction( 'documents-clean-title' );
		$user = $this->_getUser();
		
		$documents = Application_Model_DocumentsMapper::select( $user, (object) array(
			"offset"=>0,
			"limit"=>1000,
			"tags"=>array()
		));
		$affected = 0;
		foreach( $documents->results as $document ){
		
			$this->_response->title = $title = $document->title;
			
			// get date, with requested regex
			$titlePattern = '/^[\d\._]+(.*)\..{3}$/';
			//echo $document->title;
			// apply 
			if( !preg_match( $titlePattern, $document->title, $titleParts) ){
				continue;
			};
			$this->_response->title = $title = array_pop( $titleParts );
			// change title
			// get date format along with date pattern
			$affected =  Application_Model_DocumentsMapper::setTitle( $user, $document->id, $title );
		
		}
		$this->_response->affected = $affected;
		echo $this->_response;
	}
	
	/**
	 * simplify document title and split the information contained
	 */
	public function documentsUseTitleAction(){
		$this->_response->setAction( 'documents-use-title' );
		// check user
		$user = $this->_getUser();
		// check document
		// $document = $this->_getDocument( $user );
		
		$documents = Application_Model_DocumentsMapper::select( $user, (object) array(
			"offset"=>0,
			"limit"=>1000,
			"tags"=>array()
		));
		
		foreach( $documents->results as $document ){
		
		$this->_response->title = $title = $document->title;
		
		// get date, with requested regex
		$datePattern = '/\d{4}\.\d{2}\.\d{2}/';
		
		// apply 
		if( !preg_match( $datePattern, $document->title, $dateParts) ){
			continue;
			//$this->_response->throwError( "date pattern not found" );
		};
		$this->_response->date = $date = array_shift( $dateParts );
		
	
		$this->_response->dateFormat = $dateFormat = "%Y.%m.%d";
		// $this->_response->dateFormats = array( "Y.m.d"=>"%Y%.m.%d", "d.m.Y"=>"%d.%.m.%Y" );
		
		// get date format along with date pattern
		$this->_response->affected = $affected =  Application_Model_DocumentsMapper::setDate( $user, $document->id, $date, $dateFormat );
		
		// update sintax
		#STR_TO_DATE( ?, '%d/%m/%Y' );
		
		}
		echo $this->_response;
	}
	
	
	/**
	 * remove a document_tags relationship
	 */
	public function removeDocumentTagAction(){
		$this->_response->setAction( 'remove-document-tag' );
		
		// check user
		$user = $this->_getUser();
		
		// check document
		$document = $this->_getDocument( $user );
		
		try{
			// check tag
			$idTag = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'tag' ) );
			$this->_response->removed =  Application_Model_DocumentsTagsMapper::remove( $user, $document->id, $idTag );
			if( $this->_response->removed == 0 ){
				// $this->_response->throwError( "unable to remove ".$this->_request->getParam( 'tag' ) );
			}
		} catch( Exception $e ){
			$this->_response->exception = $e->getMessage();
			$this->_response->throwError( "unable to remove ".$this->_request->getParam( 'tag' ) );
			// try to remove tag-document relationship
			
		}
		echo $this->_response;
	}
	
	protected function _authorizeUser( $user ){
		if( !Zend_Auth::getInstance()->hasIdentity() ){
			$this->_response->throwError( "not authenticated" );
		}
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		if( ! ( $identity->is ('admin') || $user->id == $identity->id ) ){
			$this->_response->throwError( "not authorized" );
		}
		return $user;
	}
	
	public function addDocumentTagAction(){
		$this->_response->setAction( 'add-document-tag' );
		
		// check identity
		$user = $this->_authorizeUser( $this->_getUser() );
		
		// check document
		$document = $this->_getDocument( $user );
		
		// check tag
		$category = $this->_request->getParam( 'category' );
		
		$tags =  trim( $this->_request->getParam( 'tags' ) );
		
		if( empty( $category ) ) { 
			$this->_response->throwError("category was not found");
		};
		
		if( empty( $tags ) ) { $this->_response->throwError(  'tags is empty ' ); };
		$tags = explode( ',', $tags );
		
		// check category 
		$categoryValidator = new Ui_Forms_Validators_Pattern( array(
			'minLength' => 3,
			'maxLength' => 255,
			'pattern'   => '/[a-zA-Z]+/',
			'patternDescription' => 'category is not a valid field. only a-z chars allowed'
		));
		
		if( !$categoryValidator->isValid( $category ) ){
			$this->_response->throwError(  implode( array_keys( $categoryValidator->getMessages() ) ) );
		}
		
		// check tags 
		$tagsValidator = new Ui_Forms_Validators_Iterator( array(
			'validator' => new Ui_Forms_Validators_Pattern( array(
				'minLength' => 3,
				'maxLength' => 255,
				'pattern'   => '/[a-z0-9A-Z\s]+/',
				'patternDescription' => 'value contains a not valid field. only a-z chars allowed'
			))
		));
		
		if( !$tagsValidator->isValid( $tags ) ){
			$this->_response->throwError( implode( array_keys( $tagsValidator->getMessages() ) ) );
		}
		
		$this->_response->category = $category;
		
		// save or get category
		$idCategory = Application_Model_CategoriesMapper::add( $user, $category );
		
		$savedTags = array();
		
		//save tags
		foreach( $tags as $tag ){
			$idTag = Application_Model_TagsMapper::add( $user, $tag,  $category );
			$savedTags[ $tag ] = Dnst_Crypto_SillyCipher::crypt( $idTag );
			
			Application_Model_DocumentsTagsMapper::add(  $user, $document->id, $idTag );
		}
		$this->_response->tags = $savedTags;
		
		echo $this->_response;
	}
	
	public function suggestEntityAction(){
		
	}
	
	public function removeTagAction(){
		$this->_response->setAction( 'remove-tag' );
		# check tag
		$idTag = $this->_request->getParam( 'tag' );
		
		if( empty( $idTag ) || !is_numeric( $idTag ) ) { 
			$this->_response->throwError("requested tag param '$idTag' is not valid");
		};
		$tag = Application_Model_TagsMapper::getTag( $this->_user, $idTag );
		if( $tag == null ){
			$this->_response->throwError("given tag was not found, maybe has already been removed");
		}
		
		
		Application_Model_TagsMapper::delete( $this->_user, $idTag );
		$this->_response->message = "<b>".$tag->content."</b> has been removed from your corpus" ;
		echo $this->_response;
	}
	
    public function suggestAction(){
		$startTime = microtime( true );
		$this->_response->setAction( 'suggest' );
		
		$user = $this->_getUser();
		
		$tag = $this->_request->getParam( 'term' ) ;
		
		if( $tag == null ){
			$this->_response->throwError( "term not valid or not found" );
		}
		// check term...
		$tags = Application_Model_TagsMapper::suggest( $user, $tag );
		
		
		$this->_response->terms = $tags;
		
		$this->_response->elapsed = microtime( true ) - $startTime;
		
		echo $this->_response;
	}
	
	public function suggestCategoryAction(){
		$this->_response->setAction( 'suggest-category' );
		$tag  = $this->_request->getParam( 'term' ) ;
		if( $tag == null ){$this->_response->throwError( "term not valid or not found" );}
		$user = $this->_getUser();
		# get category
		$this->_response->categories = $categories = Application_Model_CategoriesMapper::suggest( $user, $tag );
		echo $this->_response;
	}
	
	/**
	 * Restart the analysis 
	 */
	public function documentsReTextifyAction(){
		$startTime = microtime( true );
		$this->_response->setAction( 'documents-re-textify' );
		
		$user = $this->_getUser();
		
		// get documents
		$stmt = Application_Model_DocumentsMapper::dumpDocuments( $user, true );
		
		$errors = array();
		$convertedItems = 0;
		$totalItems = 0;
		
		
		
		while( $row = $stmt->fetchObject() ){
			$document = new Application_Model_Document(
				$row->id_document, stripslashes( $row->title ), stripslashes( $row->description ),
				$row->mimetype, $row->size, $row->language, $row->date, $row->local_url, $row->status, $user
			);
			
			$localUrl = Anta_Core::getUploadPath()."/".$user->username."/".$document->localUrl;
			
			
			if( $document->mimeType == "text/plain" ) {
				// force output encoding in utf-8
				echo "text file $localUrl: ".mb_detect_encoding( file_get_contents( $localUrl ) )."\n";
				// file_put_contents( $localUrl, utf8_encode( file_get_contents( $localUrl ) ) );
				continue;
			
			}
			$totalItems++;
			
			$localTxt = $localUrl.".txt";
			
			// check file existance
			if( !file_exists( $localUrl ) ){
				$errors[ $document->id ] = $localUrl;
				continue;
			}
			
			Anta_Core::convertToText( $document, $localUrl, $localTxt );
			$convertedItems++;
		} 
		
		$elapsed =  microtime( true ) - $startTime;
		$this->_response->elapsed =$elapsed;
		$this->_response->totalItems = $totalItems;
		$this->_response->convertedItems = $convertedItems;
		$this->_response->errors = $errors;
		echo $this->_response;
	}
	
	/**
	 *
	 */
	public function documentStatusAction(){
		$this->_response->setAction( 'document-status' );
		
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		$user = Application_Model_UsersMapper::getUser( $idUser );
		
		if( $user == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not found" );
		}
		
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'document' ) );
		$document = Application_Model_DocumentsMapper::getDocument( $user, $idDocument );
		
		if( $document == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'document' )."' document not found" );
		}
		$this->_response->document = $this->_request->getParam( 'document' );
		$this->_response->document_status = $document->status;
		echo $this->_response;
	}
	
	/**
	 * Get things (group of entities)
	 * it needs some get params
	 * 
	 * 
	 */
	public function thingsAction(){
		$this->_response->setStatus( 'ok' );
		$this->_response->setAction( 'upload' );
		
		$user = $this->_getUser();
		
		
		
	}
	
	public function resetDocumentsStatusAction(){
		$this->_response->setAction( 'reset-document-status' );
		
		// check identity
		$user = $this->_authorizeUser( $this->_getUser() );
		$affected = Application_Model_DocumentsMapper::clearDocuments( $user );
		$this->_response->affected = $affected;
		
		echo $this->_response;
	
	}
	
	/**
	 * tags is an array( "category" => array( "tag", "tag" ) ); 
	 * require a non null _user variable inside this class
	 * @param array tags - douples like array( "category" => array( "tag", "tag" ) );
	 */
	protected function _saveTags( array $tags ){
		$results = array( "categories" => array(), "tags" => array() );
		
		foreach( $tags as $category => $tagss ){
		
			// save / retrieve idcategory
			$idCategory = Application_Model_CategoriesMapper::add( $this->_user, $category );// store categories
			$results["categories"][ $category ] = $idCategory;
			
			foreach( $tagss as $tag ){
				// save/retrieve each tags in the category
				$idTag = Application_Model_TagsMapper::add( $this->_user, $tag, $category );
				$results["tags"][ $category.": ".$tag ] = $idTag;
			}
		
		}
		
		return $results;
	}
	
	protected function _getDocument( Application_Model_User $user ){
		
		$idDocument = $this->_request->getParam( 'document' );
		
		if ( empty( $idDocument ) ){
			$this->_response->throwError( "document not found" );
		}
		
		// load document
		$document = Application_Model_DocumentsMapper::getDocument( $user, Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( "document" ) ) );
		
		if( $document == null ){
			$this->_response->throwError( "document not valid" );
		}
		
		return $document;
	}
	
	
	
	public function itemUploadAction(){
		$this->_response->setAction( 'item-upload' );
		$this->_user = $this->_getUser();
		$this->_response->sample = $sample = array(
			"title"		=> "your document title",
			"language"	=> "english",
			"content"	=> "text content, UTF8",
			"ref_url"	=> "",
			"mimetype"	=> "text/plain",
			"date"		=> "2011-11-24 00:00:00",
			"metadata"	=> array(
				"author"		=> array( "A. Author", "B. Author" ),
				"institution"	=> array( "The Institution" )
			)
			
		);
		
		$this->_response->serialized_info = "base64_encode of php serialize";
		$this->_response->serialized_sample = base64_encode( serialize ( $sample ) );
		
		# get item to store
		$item = $this->_request->getParam( 'item' );
		if( $item == null ){
			$this->_response->throwError( "'item' param, serialized base64encode string, was not found" );	
		}
		
		$this->_response->preview = $preview = $this->_request->getParam( 'preview' ) == "true";
		
		$this->_response->item = $item = (object) unserialize( base64_decode( $item ) );
		
		# test metadata...
		if( $preview ) exit ($this->_response);
		
		# the file which will contain item->contents
		$filename = Anta_Core::getLocalUrl( $this->_user, $item->title, true ).".txt";
		$item->uploaded_filename = basename( $filename );
		
		# check file validity (check fr viruses?)
		if( is_dir( $filename ) ){
			$this->_response->throwError( "filename ´".$filename."´ is not valid" );
		} else if( file_exists( $filename ) ){
			$this->_response->throwError( "filename ´".basename($filename)."´ exists in user's folder" );
		}
		
		# write file
		if( file_put_contents( $filename, $item->content ) === false ){
			$this->_response->throwError( "failed file_put_contents, try to write file ´".basename($filename)."´" );
		}
		
		# store document
		try{
			
			$idDocument = Application_Model_DocumentsMapper::addDocument(
				$this->_user,
				basename( $filename ),
				'',
				filesize( $filename ), 
				"text/plain", 
				basename( $filename ),
				Anta_Core::getDate( $item->date ),
				empty( $item->language )? 'en': $item->language,
				empty( $item->ref_url )? '': $item->ref_url
			);
			
			# check id document integer id 
			if( $idDocument == 0 ){
				unlink( $filename );
				$this->_response->throwError( "error while inserting document into db" );
			}
			
			$item->tags = array();
			
			# store the tags and the categories as well
			if( !empty( $item->metadata ) && is_array( $item->metadata ) ){
				foreach( array_keys( $item->metadata ) as $category ) {
					
					# store categories
					$idCategory = Application_Model_CategoriesMapper::add( $this->_user, $category );// store categories
					if( $idCategory == 0 ){
						continue;
					}
					
					$item->tags[ $category ] = array();
					
					# store tags
					foreach( $item->metadata[ $category ] as $tag ){
						$idTag = Application_Model_TagsMapper::add( $this->_user, $tag, $category );
						if( $idTag == 0 ){
							continue;	
						}
						
						$item->tags[ $category ][ $idTag ] = $tag;
					
						# link tags and document
						Application_Model_DocumentsTagsMapper::add( $this->_user, $idDocument, $idTag );

					}
				}
			} 
			$link = Anta_Core::getBase()."/edit/props/user/".$this->_user->cryptoId."/document/".Dnst_Crypto_SillyCipher::crypt( $idDocument );
			$item->permalink = $link;
		
			
		} catch( Exception $e ){
			unlink( $filename );
			$this->_response->throwError( $e->getMessage() );
		}
		echo $this->_response;
	}
	
    /**
	 * require authenticated session
	 */
	public function uploadAction(){
		
		$this->_response->setStatus( 'ok' );
		$this->_response->setAction( 'upload' );
		
		$this->_user = $this->_getUser();
		
		// store and save tags, if they're updated via tags
		$tags = $this->_request->getParam( 'tags' );
		
		$savedTags = array();
		
		if( $tags != null ){
			$douples = explode( ",", $tags );
			
			foreach( $douples as $douple ){
			
				if( strlen( trim( $douple ) ) == 0 ) continue; // ignore void spaces...
				$splitted = explode(":",$douple,2);
				if( count( $splitted ) != 2 ) continue;
				$category = stripslashes( trim( $splitted[ 0 ] ) );
				// clean stuff?
				if( !isset($savedTags[ $category ] ) ) $savedTags[ $category ] = array();
				$savedTags[ $category ][] = stripslashes( trim( $splitted[ 1 ] ) );
			}
			
		}
		$savedTags = $this->_saveTags( $savedTags );
		$this->_response->savedTags = $savedTags;
		
		// the validator to validate file input
		$validator = null;
		
		// upload via flow
		if ( isset( $_GET['qqfile'] ) ) {
        
			$validator = new Application_Model_Forms_Validators_FileXmlUploadValidator();
			
			if( ! $validator->isValid( 'qqfile' ) ){
				print_r( $validator->getMessages() ); 
				$this->_response->throwError( "qqfile error!".implode( ' ', array_keys( $validator->getMessages() ) ) );
			}
			
			return $this->_xmlUpload( $this->_user, $_GET['qqfile'], $savedTags );
		
		} 
		
		if ( !empty( $_FILES ) ) {
		
            $validator = new Application_Model_Forms_Validators_FileUploadValidator();
			return $this->_upload( $this->_user );
		} 
		
		$this->_response->throwError( "no data flow!" );
		
		
	}
	
	public function __call( $a, $b ){
		
		$action = str_replace( "Action", "", $a );
		$this->_response->setAction( $action );
		
		$classMethods = get_class_methods( "ApiController" );
		$methodsAvailable = array();
		foreach( $classMethods  as $method ){
			preg_match_all('/[A-Z][^A-Z]*/',$method,$results);
			if( isset( $results[0] ) && !empty( $results[0] ) )
			$methodsAvailable[] = strtolower( implode( "-", $results[0] ) );
		}
		
		$this->_response->methodsAvailable = $methodsAvailable;
		$this->_response->throwError( "action '$action' not found" );
		
	}

	protected function _xmlUpload( $user, $filename, $tags = array() ){
		
		$filename = Anta_Core::getLocalUrl( $user, $filename );
		
		if( is_dir( $filename ) ){
			$this->_response->throwError( "filename is not valid" );
		};
		
		if( $filename === false ){
			$this->_response->throwError( "filename $filename exists in user's folder" );
		}
		// open stream		
		$input = fopen("php://input", "r");
        $target = fopen( $filename, "w");        
        
        stream_copy_to_stream($input, $target);
        fclose($target);
		fclose($input);
		try{
			$mimeType = mime_content_type( $filename );
			
			// translate document
			$d = new Application_Model_Document();
			$d->mimeType = $mimeType;
			$conversionResult = Anta_Core::convertToText( $d , $filename );
			
			// unable to convert the given file
			if ( $conversionResult === false ){
				unlink( $filename );
				$this->_response->throwError( "bad encoding or file type" );
			}
			
			$idDocument = Application_Model_DocumentsMapper::addDocument(
				$user,
				basename( $filename ),
				'',
				filesize( $filename ), 
				$mimeType, 
				basename( $filename ),
				"CURRENT_TIMESTAMP",
				$this->_request->getParam("language")==null?'en':$this->_request->getParam("language")
			);
			
			
			
			// add 
			if( $idDocument != 0 && !empty( $tags ) ){
				//print_r( $tags );
				foreach( $tags['tags'] as $label => $idTag ){
					if( $idTag == 0)continue;
					Application_Model_DocumentsTagsMapper::add( $user, $idDocument, $idTag );
				}
				
			}
			$link = Anta_Core::getBase()."/edit/props/user/".$this->_user->cryptoId."/document/".Dnst_Crypto_SillyCipher::crypt( $idDocument );
			
			# some output
			$this->_response->link = $link;
			$this->_response->mimeType = $mimeType;
			$this->_response->result = basename( $filename ) . " (".filesize( $filename )." bytes) added";
			
		} catch( Exception $e ){
			unlink( $filename );
			$this->_response->throwError( $e->getMessage() );
			
		}
		
		
		echo $this->_response;
	}
	
	/**
	 * this upload function is for $_FILES only
	 */
	protected function _upload( $user ){
	}
}





