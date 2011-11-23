<?php

class AuthorsController extends Zend_Controller_Action
{
	protected $_user;
	protected $_author;
	
    public function init()
    {
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		if( $this->_user != null ){
			return;
		}
		
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		// check user
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
       
	    // check author
		$idAuthor =  Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'action' ) );
		
		$this->_author = Application_Model_AuthorsMapper::getAuthor( $this->_user, $idAuthor );
		
		// check user
		if ($this->_author == null ){
			throw( new Zend_Exception( I18n_Json::get( 'authorNotFoundException', 'errors' ) ) );
		}
		
    }

    public function indexAction()
    {
		$this->view->dock = new Application_Model_Ui_Docks_Dock();
		
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'documents', I18n_Json::get( 'documentsList' ).": ".$this->_user->username.", author ".$this->_author->name." " ) );
		
    }

	public function __call( $a, $b ){
		$this->_forward( 'index' );
	}

}

