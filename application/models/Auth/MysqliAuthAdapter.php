<?php
/**
 * Adapter auth using oracle db
 * 
 */
 class Application_Model_Auth_MysqliAuthAdapter implements Zend_Auth_Adapter_Interface {
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
	 * // TODO md5 concat in Oracle...
	 */
	public function authenticate() {
		$oci = Anta_Core::mysqli();
		
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "security" );
		
		// $this->username = 'gui.daniele@gmail.com';
		// $this->password = 'diaballein';
		// retrieve salt for the desired identity
		$query = "SELECT id_user, email, name, type, realname FROM users
							WHERE ( email = ? OR name = ? )
							and passwd = MD5( CONCAT( ?, ':', salt, ':', ? ) ) LIMIT 1" ;
		$stmt = $oci->query( $query, array( $this->username, $this->username,  $config->passwd->salt, $this->password ) );
		
		$row = $stmt->fetchObject();
		
		// exit with error if object is null
		if( $row == null ){
			return new Zend_Auth_Result( Zend_Auth_Result::FAILURE, null, array() );
		}
		
		
		// create user using identity
		$code = Zend_Auth_Result::SUCCESS;
		$identity = new Application_Model_User( $row->id_user, $row->name, $row->realname, $row->type, $row->email );
		$messages = array();
		
		return new Zend_Auth_Result($code, $identity, $messages);
		
	}
}
?>
