<?php

class AdminController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        Anta_Core::authorizedOnly( 'admin' );
    }

    public function indexAction()
    {
		// verify the writability of uploads folder
		Anta_Core::getUploadPath();
		
		//create users list
		$identity = Zend_Auth::getInstance()->getIdentity();
	
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'users', I18n_Json::get( 'userList' ) ) );
		
		$this->view->dock->users->setCreationLink( Anta_Core::getBase()."/add/user/", I18n_Json::get( 'addUser' ) );
		
		// load user documents
		$this->view->dock->users->setHeader( new Application_Model_Ui_Headers_UsersHeader );
		
		$users = Application_Model_UsersMapper::getUsers();
		
		foreach (array_keys( $users ) as $k ){
			
			$this->view->dock->users->addItem( new Application_Model_Ui_Items_User( $users[ $k ] ) );
	
		}
		
		
		
    }

	protected function createcorpusAction(){
		/*Application_Model_UsersMapper::addUser( 'zaertyriotl', 'trallalla', 'varenne');
		$this->render( 'index' );*/
	}
	
	public function removeAction(){
		
		if ( $this->_request->getParam( 'user' ) == null ){
			throw ( new Zend_Exception("check your request: no 'user' param was found") );
		}
        
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		
		$this->user = Application_Model_UsersMapper::getUser( $idUser );
		
		if( $this->user == null ){
			throw ( new Zend_Exception( "'".$this->_request->getParam( 'user' )."' user not found" ) );
		}
		
		$result = Application_Model_UsersMapper::removeUser( $this->user );
		
		if( $result === true ){
			Anta_Core::setMessage( I18n_Json::get( 'userRemoved' ) );
		} else {
			Anta_Core::setError( $result );
		}
		
		
		
		$this->_forward("index");
	}
	
}

