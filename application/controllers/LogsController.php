<?php

class LogsController extends Zend_Controller_Action
{
	
	protected $_user;
	
	/**
	 * Cannonical url: edit document :id_document of user :id_user
	 * read only: /logs/read/user/:id_user/
	 * clean : /logs/clean/user/:id_user
	 * 
	 */ 
    public function init()
    {
        // check user param
        $idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		// validate ownerships
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		// check that user sists
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		
		// the dock
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		$this->view->user = $this->_user;
		
		$this->identity = Zend_Auth::getInstance()->getIdentity();
	
		
		
		
    }
		
    

    public function indexAction()
    {
        if( $this->identity ->id != $this->_user->id ){
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->addEntry(
				"/documents/".$this->_user->cryptoId,
				I18n_Json::get( 'userDocumentList' ).' user:'.$this->_user->username , array( 
					'class' => 'admin'
			));
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->addEntry(
				"/gexf/entities/user/".$this->_user->cryptoId,
				I18n_Json::get( 'gexf' ).' user:'.$this->_user->username , array( 
					'class' => 'admin'
			));

		}
		
    }

	public function readAction(){
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Logxo(
			'monitor',
			'log: '.
			' <span class="'.( $this->identity ->id != $this->user->id? 'admin': '').'">'."log_".$this->_user->username. "</span>"
		));
		$this->view->dock->monitor->read( @file_get_contents( Anta_Logging::getLogsPath()."/distillerlog_".$this->_user->username ) );
		
		
		
		$this->render( 'index' );
	}
	
	public function clearAction(){
		
		@unlink( Anta_Logging::getLogsPath()."/distillerlog_".$this->_user->username );
		
		Anta_Core::setMessage( I18n_Json::get('logCleaned') );
		
		$this->_forward( 'read' );
	}

}

