<?php
/**
 * This controller view display a flex object ( Frog )and send some flash params to the view
 * It disables default layout and use view only
 */
class FrogController extends Zend_Controller_Action
{
	protected $_user;

    public function init()
    {
		$this->_helper->layout->setLayout('void');
		
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
}