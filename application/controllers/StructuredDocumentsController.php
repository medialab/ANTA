<?php
/**
 * a controller for structured document: load configuration settings for documents
 */
class StructuredDocumentsController extends Zend_Controller_Action{
	public function init()
    {
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}
		
		$this->view->user = $this->_user;
	}
	
	public function indexAction(){
		
	}
	
	public function importFromGoogleSpreadsheetAction(){
		$this->view->dock = new Ui_Dock();
		
		// add module "edit property of"
		$this->view->dock->addCraft( new Ui_Crafts_Cargo(
			'tags',
			I18n_Json::get( 'import tags from google docs' )
		));
		
		// brief explaination
		$this->view->dock->tags->setCreateForm( 
			new Ui_Forms_ImportGoogleDocs( 'import-google-docs', I18n_Json::get( "load google document" ) ) 
		);
		
		$this->view->dock->tags->addItem(
			new Ui_Crafts_Items_Void( 'gdoc-some-lines' )
		);
		
		// add previous uploaded graph (auto save google docs in table)
		
		
	}
	
}
?>