<?php

// do something when script ends or shutdown (e.g shudtdown)
// 

class ThreadController extends Zend_Controller_Action
{
	public $_user;
	
    public function init()
    {
        
		$this->view->user = $this->_user = Anta_Core::authorizeOwner();	
		print_r( $this->_user );
		// create dock
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'routine-status', I18n_Json::get( 'routineStatus' ) ) );
		
		$this->view->dock->routine_status->addItem( new Application_Model_Ui_Items_Routine( $this->_user ) );
		$this->view->dock->routine_status->addItem( new Ui_Crafts_Items_Void( 'log-console' ),array('content'=>'<pre></pre>') );
		
		// $this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'available-threads', I18n_Json::get( 'availableThreads' ) ) );
		//$this->view->dock->available_threads->setContent( new Application_Model_Ui_Items_AvailableThread( $this->_user ) );
		
		//$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'threads', I18n_Json::get( 'threadsList' ) ) );
		
		
    }

	protected $_availableTypes = array(
		"opencalais", "alchemy", "stemming", "ngram"
	);

	public function addAction(){
		
		$type = $this->_request->getParam( 'type' );
		
		if( !in_array( $type, $this->_availableTypes ) ){
			throw (new Zend_Exception( I18n_Json::get( 'threadTypeNotValid', 'errors') ) );
		}
		
		// add the thread to the user
		$idThread = Application_Model_ThreadsMapper::addThread( $this->_user, $type, 0, 'ready' );
		
		if( $idThread != 0 ){
			Anta_Core::setMessage( I18n_Json::get( 'threadAdded' ). ": ".$idThread );
		} else {
			Anta_Core::setMessage( I18n_Json::get( 'threadDuplicated', 'errors' ) );
		}
		
		// print the user's threads
		$threads = Application_Model_ThreadsMapper::getThreads( $this->_user );
		
		foreach (array_keys( $threads ) as $k ){
			$this->view->dock->threads->additem( new Application_Model_Ui_Items_Thread( $threads[ $k ], $this->_user  ) );
		
		}
		
		$this->render('index');
		
	}
	
	public function removeAction(){
		$idThread = $this->_request->getParam( 'id' );
		
		if( $idThread == null || !is_numeric( $idThread ) ){
			throw (new Zend_Exception( I18n_Json::get( 'threadTypeNotValid', 'errors') ) );
		}
		
		
		$affected = Application_Model_ThreadsMapper::removeThread( $idThread );
		
		if( $affected != 0 ){
			Anta_Core::setMessage( I18n_Json::get( 'threadRemoved' ). ": ".$idThread );
		} else {
			Anta_Core::setError( I18n_Json::get( 'threadNotRemoved', 'errors' ) );
		}
		
		
		$this->_forward('index');
	}

	public function indexAction(){
		/*
		$threads = Application_Model_ThreadsMapper::getThreads( $this->_user );
		
		foreach (array_keys( $threads ) as $k ){
			$this->view->dock->threads->additem( new Application_Model_Ui_Items_Thread( $threads[ $k ], $this->_user ) );
		
		}
		*/
		
	}
	
	
	

}



