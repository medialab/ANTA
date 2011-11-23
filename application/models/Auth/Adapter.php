<?php
/**
 * Adapter which may use multiple authentication methods
 * using addAdapter class method with an instance implementing Zend_Auth_Adapter_Interface
 *
 * @author Daniele Guido
 */
 class Application_Model_Auth_Adapter implements Zend_Auth_Adapter_Interface {
	/**
	 * Username
	 *
	 * @var string
	*/
	protected $username = null;

	/**
	  * Password
	  *
	  * @var string
	*/
	protected $password = null;

	protected $adapterChain;
	 
	 /**
	  * Class constructor
	  *
	  * The constructor sets the username and password
	  *
	  * @param string $username
	  * @param string $password
	 */
	 public function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
	 }

	 /**
	  * Authenticate
	  *
	  * Authenticate the username and password
	  *
	  * @return Zend_Auth_Result
	 */
	 public function authenticate() {
		
		// LDAP or DB procedure should be chained using addAdapter()
		if( isset($this->adapterChain) ){
			// execute authenticate method and return result if any
			foreach( array_keys( $this->adapterChain ) as $k ){
				$adapter =& $this->adapterChain[ $k ];
				
				$result = $adapter->authenticate();
				
				if ($result->isValid()) return $result;
			}
			
		}
		
		// Exit as Failed
		$code = Zend_Auth_Result::FAILURE;
		$identity = null;
		$messages = array();
		return new Zend_Auth_Result($code, $identity, $messages);
	}
	
	/**
	 * Chain in our adapter list custom / available adapters.
	 * Use this function before authenticate method
	 */
	public function addAdapter( Zend_Auth_Adapter_Interface $adapter ){
	
		if( !isset($this->adapterChain) ){
			$this->adapterChain = array();
		}
		
		$this->adapterChain[] =  $adapter ;
		
	}
 }
 

