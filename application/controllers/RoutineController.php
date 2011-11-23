<?php

class RoutineController extends Zend_Controller_Action
{

    /** a Dnst_Json_Response instance */
	protected $_response;
	
	/** a Application_Model_User instance */
	protected $_user;
	
    public function init()
    {
        /* Initialize action controller here */
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
		$this->_response = new Dnst_Json_Response();
		$this->_response->setStatus( 'ok' );
		$this->_response->setAction( $this->_request->getParam( 'action' ) );
		
		
		// chet the identity credentials
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		if( $identity == null  ){
			$this->_response->throwError( "unauthenticated" );
		}
		
		// get the user id
		if ( $this->_request->getParam( 'user' ) == null ){
			$this->_response->throwError( "incomplete: user param not found in request" );
		}
		
		// decrypt request
		$idUser = Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) );
		
		$this->_user = Application_Model_UsersMapper::getUser( $idUser );
		
		if( $this->_user == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not found" );
		}
		
		if( $identity->id == $this->_user->id || $identity->is( 'admin' ) ){
		
		} else {
			$this->_response->throwError( "unauthenticated" );
		}
		
		$this->_response->user = $this->_request->getParam( 'user' );
		$this->_response->userid = $idUser;
		
			
    }

    public function indexAction()
    {
		
		$this->_response->throwError( "action not found" );
		
		echo $this->_response;
        // action body
    }
	
	public function distillerAction(){
		include APPLICATION_PATH."/routines/type-distiller.php";
	}
    
	/**
	 * if a routine status is 'start', then kill it;
	 * if a routine status is 'die', then kill it;
	 * if a routine status is 'died', then restart it;
	 * if a routine status is 'none', then start it;
	 */
	public function cycleAction(){
		$status = Application_Model_RoutinesMapper::getStatus( $this->_user->id );
		
		if( $status == null ) $status = 'none';
		
		switch( $status ){
			case 'none':
				$this->_forward( 'start' );
			break;
			case 'die':
				$this->_forward( 'status' );
			break;
			case 'died':
				$this->_forward( 'restart' );
			break;
			case 'start':
				case 'none':
				$this->_forward( 'kill' );
				break;
			break;
			default:
				$this->_response->throwError( "unable to track routine status" );
			break;
		}
	}
	
    public function startAction()
    {
        // action body
		$this->_doRoutine();
		$this->_forward( 'status' );
	}
	
	public function restartAction(){
		
		Application_Model_RoutinesMapper::setStatus( $this->_user->id, 'start' );
		
		// reset document status
		Application_Model_DocumentsMapper::clearDocuments( $this->_user );
		
		$this->_doRoutine();
			
		$this->_forward( 'status' );
	}
	
	/**
	 * start process for the routine script
	 * @todo set the ini path directly into the application.ini file
	 */
	protected function _doRoutine(){
		proc_close( proc_open (
			"php -c /etc/php5/apache2/php.ini ".APPLICATION_PATH."/routines/type-distiller.php -u".$this->_user->cryptoId." &" ,
			array(),
			$foo 
		));
	}
	
	
    public function killAction()
    {
        // action body
		Application_Model_RoutinesMapper::kill( $this->_user->id );
		$this->_forward( 'status' );
    }

	public function statusAction()
	{
		$status = Application_Model_RoutinesMapper::getStatus( $this->_user->id );
		
		if( $status == null ) $status = 'none';
		
		$this->_response->routine = $status;
		
		echo $this->_response;
	}
	
	
	
	
	/** 
	 * clean completely routine effects on user databases:
	 * 
	 */
	public function clearAction(){
		Application_Model_EntitiesMapper::clearEntities( $this->_user );
		Application_Model_DocumentsMapper::clearDocuments( $this->_user );
		
	}

}





