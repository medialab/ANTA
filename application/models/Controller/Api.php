<?php
/**
 * Base controller class for api classes.
 * This class Provides basic methods to authentify user or to
 * handle class unavailable methods
 */
class Application_Model_Controller_Api extends Zend_Controller_Action
{
	/** a Dnst_Json_Response instance */
	protected $_response;
	protected $_user;
	
	 public function init()
    {
        /* Initialize action controller here */
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		/** reinitialize headers */
		Anta_Core::setHttpHeaders("text/plain");
		
		// initialize json response
		$this->_response = new Dnst_Json_Response();
		$this->_response->setStatus( 'ok' );
		
		// add verbose information only if it is specified in htttp params
		if( isset( $_REQUEST[ 'verbose' ] ) ){
			$this->_response->params = $this->_request->getParams(); 
		}
		$this->_response->action = $this->_request->action;
		if( $this->_request->action != "authenticate"){
			$this->_user = $this->_getUser( false );
		}
		$this->_response->token = session_id();
		
	}
	/**
	 * action not found handler
	 */
	public function __call( $a, $b ){
		$action = str_replace( "Action", "", $a );
		$this->_response->setAction( $action );
		$this->_response->throwError( "action '$action' not found" );
	}/**
	 * Handle user param error. Return the user if is valid.
	 * If the user is not provided or is not valid, exit with json error
	 */
	protected function _getUser(){
		
		
		# get the current identity
		$identity = Zend_Auth::getInstance()->getIdentity();
			
		if( $identity == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not authenticated, maybe your session has expired" );
		}
			
		
		
		# default: your master stuff
		if ( $this->_request->getParam( 'project' ) != null ){
			# change user username
			
		}

		return $identity;		

				

		// load the user
		$user = Application_Model_UsersMapper::getUser( $idUser );
		
		if( $user == null ){
			$this->_response->throwError( "'".$this->_request->getParam( 'user' )."' user not found!" );
		}
		
		return $user;
	}
	
	
	/**
	 * @return a valid prefix or exit with throwError() method of response class
	 */
	protected function _getPrefix(){
		$prefix = $this->_request->getParam("prefix");
		
		if( !in_array( $prefix, Anta_Core::getEntitiesPrefixes() ) ){
			$this->_response->throwError( "value 'prefix' was not found or is not valid" );
		}
		return $prefix;
	}
}
?>
