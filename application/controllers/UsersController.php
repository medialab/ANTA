<?php

class UsersController extends Zend_Controller_Action
{
	protected $_user;

    public function init()
    {
		$this->_user = Anta_Core::getAuthorizedUser(  Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) ) );
		
		
    }

    public function whoisAction()
    {
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		$this->view->dock = new Application_Model_Ui_Docks_Dock();

		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 'users', I18n_Json::get( 'modifyUser' ).' '.$this->_user->username ) );
		
		$this->view->dock->users->setCreateForm( new Application_Model_Forms_ModifyUserForm('create-user', I18n_Json::get( 'applyUserModifications' ), Anta_Core::getBase()."/users/whois/user/".$this->_user->cryptoId ) );
		
		$form = $this->view->dock->users->getCreateForm();
		
		// listen to forms modif
		if( $this->getRequest()->isPost() ){
			$form =  $this->view->dock->users->getCreateForm();
			
			$result = Anta_Core::validateForm( $form, $this->getRequest()->getParams() );
			
			if( $result !== true ){
				Anta_Core::setError( $result );
			} else {
				
			
				// check password value
				Application_Model_UsersMapper::editUser(
					$this->_user->id,
					$form->user_fullname->getValue(),
					$form->user_email->getValue(),
					$form->password->getValue()
				);
						
				Anta_Core::setMessage( I18n_Json::get( 'userModified' ) );
			
				$this->_user = Application_Model_UsersMapper::getUser( $this->_user->id );
			}
		}
		
		
		$form->user_email->setDefaultValue( $this->_user->email );
		$form->user_fullname->setDefaultValue( $this->_user->realname );
		
		$this->render('index');
       
    }

	public function __call( $a, $b){
		// $this->_forward( 'index' );
	}
}

