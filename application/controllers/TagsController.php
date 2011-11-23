<?php
class TagsController extends Zend_Controller_Action
{
	protected $_user;
	protected $_document;
	
	/**
	 * Cannonical url: edit document :id_document of user :id_user
	 * /edit/:id_document/user/:id_user
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
		
		// check the docu,ment into user's docs table
		$idDocument = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'document' ) );
		$this->_document = Application_Model_DocumentsMapper::getDocument( $this->_user, $idDocument );
			
		if( $this->_document == null ){
			throw( new Zend_Exception( I18n_Json::get( 'documentNotFoundException', 'errors' ) ) );
		}
		
		$this->view->user = $this->_user;
		$this->view->document = $this->_document;
		
    }
	
	public function addAction(){
		
		$this->view->dock = new Ui_Dock();
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo(
			"tags",
			I18n_Json::get( 'attach a new tag to ' ). $this->_document->title
		));
		$this->view->dock->tags->setCreateForm( new Ui_Forms_AddTag( 
			'attach_tag',
			I18n_Json::get( "attach tag" ),
			$_SERVER[ 'REDIRECT_URL' ]
		));
		$this->render( 'index' );
		
	}
	
}