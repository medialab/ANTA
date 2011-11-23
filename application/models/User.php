<?php
/**
 * @package
 */
 
/**
 * describe an user
 */
class Application_Model_User{
	
	/** user crypto id, to hide the real user idin links */
	public $cryptoId;
	
	/** user db id */
	public $id;
	
	/** user db name, a.k.a nickname */
	public $username;
			
	/** user real name (firstname chained with last name) */
	public $realname;
	
	/** user type */
	public $type;
	
	/** user email */
	public $email;
	
	/**
	 * @param id		- int identifier
	 * @param username	- user name
	 * @param type		- user type, "guest" or "admin"
	 * @param email		- user email address
	 */
	public function __construct( $id, $username, $realname, $type, $email ) {
		$this->id = $id;
		$this->cryptoId = Dnst_Crypto_SillyCipher::crypt( $this->id );
		
		$this->username = $username;
		$this->realname = $realname;
		
		$this->type  = $type;
		$this->email = $email;
	}
	
	/**
	 * Extablish if user is one of the given type(s)
	 */
	public function is( $type ){
		
		$args = func_get_args();
		
		$isType = false;
		
		foreach( $args as $_type ){
			if( $this->type == $_type ){
				$isType = true;
				break;
			}
		}
		
		return $isType;
	}
	
}
