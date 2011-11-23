<?php
/**
 * @package Anta
 */
 
/**
 * Entities explorer
 */
class CategoriesController extends Zend_Controller_Action
{
	/** the user owner of th entitites */
	protected $_user;
	
    public function init()
    {
        $idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		Anta_Core::authorizeOwner( $idUser, array( 'admin' ) );
		
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		if ($this->_user == null ){
			throw( new Zend_Exception( I18n_Json::get( 'userNotFoundException', 'errors' ) ) );
		}

    }
	
	/**
	 * show tags that fall into the selected category (if there is one)
	 */
	public function indexAction(){
		echo "hello";
	}
	
	public function modifyAction(){
		
	}
	
	
	/**
	 * create a new custom field, via ajax.
	 * It uses the Dnst_Response json.
	 */
	public function addAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		/** reinitialize headers */
		header('Content-type: text/plain; charset=UTF-8');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		// initialize json response
		$response = new Dnst_Json_Response();
		$response->setStatus( 'ok' );
		
		// read the param name
		$category = $this->_request->getParam( 'name' );
		
		if( $category == null ){
			$response->throwError('Bad request: name not found');
		}
		
		// validate the param name, patterns with letters / number only, 255 chars max (cropped)
		$validChunkLength = strspn( strtolower( $category ), "-1234567890 abcdefghijklmnopqrstuvwxyzàèçé");
		
		// validate categories
		if( strlen( $category ) != $validChunkLength ){
			$response->throwError('Bad request: name value does not seem to be valid');
		}
		
		// if there is a type, validate it
		$type = $this->_request->getParam( 'type' );
		
		
		// add category into stuff
		$insertedId = Application_Model_CategoriesMapper::addCategory( $this->_user, $category, $type );
		
		if( $insertedId == 0 ){
			$response->throwError( I18n_Json::get( 'categoryDuplicated', 'errors' ) );
		}
		$response->category   = $category;
		$response->idCategory = $insertedId;
		// output response via json
		echo $response;
	}
}
?>