<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		// add login form to identity board
		// Dnst_History_Carnivore::toString();
    }

    public function indexAction()
    {
		// $py = new Py_Scriptify( "dummy.py" );
		// echo $py->getResult();
		
	   // echo  Dnst_History_Carnivore::backBefore( Anta_Core::getBase().'/index/login' );
		
	   /*
	   // load api url
	   // load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "services" );
		
		// get url
		
	   
        // action body
		$alchemy = new Textopoly_Alchemy( $config->alchemy->api->entities, array(
			"outputMode" => "json",
			"text" => "La création d’un climat favorable aux entreprises est une priorité de l’Union européenne. Recueillir l’avis des entreprises sur les politiques européennes et en tenir compte dans l’élaboration du futur cadre législatif européen est donc primordial et indispensable. C’est pourquoi, la Commission européenne a mis en place différents mécanismes – remontée d’informations, consultations, panels –(dont Enterprise Europe Network se fait le relais auprès des entreprises) qui lui permettent de recueillir et d’analyser les difficultés, besoins et attentes des entreprises européennes. L’Europe est à votre écoute, donnez votre avis et influez sur la politique européenne !",
			"apikey" => $config->alchemy->api->key
		));
		
		if( $alchemy->hasError() ){
			echo $alchemy->getError();
			return;
		}
		var_dump( $alchemy->get() );
		$alchemy = new Textopoly_Alchemy( $config->alchemy->api->keywords, array(
			"outputMode" => "json",
			"text" => "La création d’un climat favorable aux entreprises est une priorité de l’Union européenne. Recueillir l’avis des entreprises sur les politiques européennes et en tenir compte dans l’élaboration du futur cadre législatif européen est donc primordial et indispensable. C’est pourquoi, la Commission européenne a mis en place différents mécanismes – remontée d’informations, consultations, panels –(dont Enterprise Europe Network se fait le relais auprès des entreprises) qui lui permettent de recueillir et d’analyser les difficultés, besoins et attentes des entreprises européennes. L’Europe est à votre écoute, donnez votre avis et influez sur la politique européenne !",
			"apikey" => $config->alchemy->api->key
		));
		
		if( $alchemy->hasError() ){
			echo $alchemy->getError();
			return;
		}
		*/
    }

	public function loginAction(){
		if( Zend_Auth::getInstance()->hasIdentity() ){
			Anta_Core::setError( I18n_Json::get( 'loginAlreadyLogged' ) );
			$this->_niceRedirect( Dnst_History_Carnivore::backBefore( Anta_Core::getBase().'/index/login' )	);
			return $this->_forward('index');
		}
		
		if (!$this->_request->isPost()) {
			Anta_Core::setError( I18n_Json::get( 'loginNoPost', 'errors' ) );
			return $this->_forward('index');
        }
		
		// validate form entry before all
		$validationResult = Anta_Core::validateForm(
			Application_Model_Ui_Boards_IdentityBoard::getInstance()->getLoginForm(),
			$this->_request->getParams()
		);
		
		if( $validationResult !== true ){
			// ask for validation
			Anta_Core::setError( $validationResult );
			$this->_forward( 'index' );
			return;
		}
		
		$form = Application_Model_Ui_Boards_IdentityBoard::getInstance()->getLoginForm();
		
		$authenticationResult = Anta_Core::authenticateUser( array(
			'username' => $form->username->getValue(),
			'password' => $form->password->getValue(),
		));
		
		if( $authenticationResult !== true ){ 
			// ask for validation
			Anta_Core::setError( I18n_Json::get( 'loginAuthFailed', 'errors' ) );
			$this->_forward( 'index' );
			return;
		}
		// redirect
		$this->_niceRedirect( Dnst_History_Carnivore::backBefore( Anta_Core::getBase().'/index/login' )	);
		
		$this->_forward( 'index' );
		// $this->_redirect( Dnst_History_Carnivore::backBefore( '/index/login' ) ) ;

	}
	
	protected function _niceRedirect( $redirectUrl ){
		if( $redirectUrl == Anta_Core::getBase()."/" || $redirectUrl == Anta_Core::getBase().'/index/login' || strrpos( $redirectUrl, Anta_Core::getBase()."/index/" ) !== false ){
			// documents page
			$this->_redirect( '/documents/list/user/'.Zend_Auth::getInstance()->getIdentity()->cryptoId );
		} else {
			Anta_Core::redirect( $redirectUrl );
			
		}
	}
	
	public function logoutAction(){
		$_SESSION = array();
        Zend_Auth::getInstance()->clearIdentity();
        $this->_forward('index'); // back to login page
    }
}

