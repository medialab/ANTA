<?php
/**
 * @package Anta
 */
 
/**
 * Entities explorer
 */
class EntitiesController extends Zend_Controller_Action
{
	/** the user owner of th entitites */
	protected $_user;
	
    public function init()
    {
        $idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		$this->_user = Zend_Auth::getInstance()->getIdentity();//Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		$this->view->user = $this->_user;
		
    }

    public function indexAction()
    {
        // action body
		// Anta_Utils_Scriptify::load( APPLICATION_PATH."/../public/js/jquery-ui-1.8.11.custom.min.js");
		// echo Anta_Utils_Scriptify::getStaticScript( "jquery-ui-1.8.11.custom.min.js" );
    }

	/**
	 * import the entities list to correct the entities
	 */
	public function csvImportAction(){
	
	}
	
	/**
	 * export the entities list for the user in csv format.
	 * Separator is 
	 */
	public function csvAction(){
	
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		// load ignored only?
		$acceptedOnly = $this->_request->getParam( "accepted-only" ) != null? 1:0;
		$booleanAcceptedOnly = $this->_request->getParam( "accepted-only" ) != null;
		
		
		// csv headers 
		Anta_Core::setHttpHeaders( "text/csv", $this->_user->username."_entities".( $acceptedOnly? "_whitelist": "").".csv", true );
		
		// my csv table
		$table = new Anta_Csv_Table( new Anta_Csv_Header( array( 
			"prefix", "table id", "unique id", "content", "accept", "frequency"
		)));
		
		
		// load accepted entities
		$stmt = Application_Model_SubEntitiesMapper::getEntities( $this->_user, array ( "prefix ASC", "frequency ASC" ), array(), 0, -1, "", false, "en", array( "ignore" => 0 ), true );
		
		while( $entity = $stmt->fetchObject() ){
			
			$table->addRow( Anta_Csv_Row::create( 
				$table->getHeader(), array(
					"prefix"	=> $entity->prefix,
					"table id"	=> $entity->identifier,
					"unique id"	=> $entity->prefix."_".$entity->identifier,
					"content"	=> $entity->content,
					"accept"	=> "w",
					"frequency"	=> $entity->frequency
				)
			));
		}
		
		if( $acceptedOnly ) exit( $table );
		
		// load ignored entities
		$stmt = Application_Model_SubEntitiesMapper::getEntities( $this->_user, array ( "prefix ASC", "frequency ASC" ), array(), 0, -1, "", false, "en", array( "ignore" => 1 ), true );
		
		while( $entity = $stmt->fetchObject() ){
			
			$table->addRow( Anta_Csv_Row::create( 
				$table->getHeader(), array(
					"prefix"	=> $entity->prefix,
					"table id"	=> $entity->identifier,
					"unique id"	=> $entity->prefix."_".$entity->identifier,
					"content"	=> $entity->content,
					"accept"	=> "",
					"frequency"	=> $entity->frequency
				)
			));
		}
		
		echo $table;
	}
	
	/**
	 * Will shoz the list of similar entities
	 */
	public function mergeAction(){
		$this->view->dock = new Ui_Docks_Dock();
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'entities', I18n_Json::get( 'mergeEntities' ).": ".$this->_user->username ) );
		
		$this->render( 'index' );
		
		$idEntity = $this->_request->getParam( 'id' );
		
		if( is_numeric( $idEntity ) ){
			throw( new Zend_Exception( I18n_Json::get( 'entityNotFound', 'errors' ) ) );
		}
		
		$entity = Application_Model_EntitiesMapper::getEntity( $this->_user, $idEntity );
		
		
		if( empty( $entity ) ){
			throw( new Zend_Exception( I18n_Json::get( 'entityNotValid', 'errors' ) ) );
		}
		
		print_r(str_word_count($entity->content, 1));
		
		// nice stemming
		// 	stem();
		
		print_r( $entity );
	}
	
	/**
	 * get the list of similar entities
	 */
	public function similarLookingAction(){
		$this->view->dock = new Ui_Docks_Dock();
		
		$prefix   = $this->_request->getParam( 'prefix' );
		$idEntity = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'id' ) );
		
		// get the entity into various tables
		$entity = Application_Model_EntitiesMapper::find( $this->_user, $prefix, $idEntity );
		
		if( empty( $entity ) ){
			throw( new Zend_Exception( I18n_Json::get( 'entityNotValid', 'errors' ) ) );
		}
		
		// get similar entities using stem approach
		$entities = Application_Model_EntitiesMapper::findSimilar( $this->_user, $entity );
		
		// show similar entities, filtered
		
		
	}
	
	
	
	public function excludedAction(){
		$this->_loadEntities( "excluded entities", 1 );
		$this->render( 'index' );
	}
	
	public function listAction(){
		$this->_loadEntities( "all entities", -1 );
		$this->render( 'index' );
	}
	
	/**
	 * all the entities
	 */
	public function includedAction(){
		
		$this->_loadEntities( "entities", 0 );
		$this->render( 'index' );
	}
	
	protected function _loadEntities( $title, $ignore ){
		$this->view->dock = new Ui_Dock();
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargos_Entities( $title, I18n_Json::get( $title ).": ".$this->_user->username ) );
		$this->view->entity_type = $ignore;
		$header = new Application_Model_Ui_Headers_EntitiesHeader();
		$header->user = $this->_user;
		
		Dnst_Filter::start( array(
				"offset" => 0,
				"limit"  => 100,
				"order"  => array( "distro DESC", "occurrences DESC" ),
				"pid"	 => 0,
				"ignore" => $ignore,
				"tags"	 => array(),
				"query"  => ""
			), array (
				"order"  => new Dnst_Filter_Validator_Array( array(
					"occurrences ASC", "occurrences DESC",
					"distro ASC", "distro DESC",
					"sign ASC", "sign DESC" ) 
				),
				"offset" => new Dnst_Filter_Validator_Range( 0, 10000000 ),
				"limit"  => new Dnst_Filter_Validator_Range( 1, 500 ),
				"query"  => new Dnst_Filter_Validator_Pattern( 0, 100 )
			)
		);
		
		# load n. of included entities (  connected to a visible document )
		$connectedEntities = Application_Model_SubEntitiesMapper::getUnignoredNumberOfEntities( $this->_user, "rws" );
		
		# load n. of included entities
		$includedEntities = Application_Model_SubEntitiesMapper::getFilteredNumberOfEntities( $this->_user, "rws", "ignore", 0 );
		
		# load total entities
		
		
		if( !Dnst_Filter::isValid() ){
			// if you set the filters properly, then these variables MUST be in place
			Anta_Core::setError("uhm..not valid string..".Dnst_Filter::getErrors() );
			return $this->render( 'index' );
		}
		// print_r( Dnst_Filter::read() );
		$entities = Application_Model_SubEntitiesMapper::getEntities(
			$this->_user,
			Dnst_Filter::read()
		);
		
		foreach( array_keys( $entities->results ) as $k ){
			
			$this->view->dock->$title->addItem( new Ui_Crafts_Items_Entity( $entities->results[ $k ] ) );
		}
		$header->connectedEntities = $connectedEntities;
		$header->includedEntities = $includedEntities; // along with unignored documents
		$header->loadedEntities =  count( $entities->results );
		$header->totalEntities  =  $entities->totalItems;
		$header->offset         =  Dnst_Filter::getProperty( "offset" );
		$header->limit          =  Dnst_Filter::getProperty( "limit" );
		$header->searchQuery    =   Dnst_Filter::getProperty( "query" );
		$this->view->dock-> $title->setHeader( $header );
		// $this->view->dock-> $title->setFooter( $header );
	}
	
	
}
?>
