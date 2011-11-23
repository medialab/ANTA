<?php
/**
 * @package
 */
 
/**
 * describe a document
 */
class Application_Model_Author{
	
	/** doc crypto id, to hide the real doc idin links */
	public $cryptoId;
	
	/** doc db id */
	public $id;
	
	/** document title */
	public $name;
	
	/** an array of documents, actor-related */
	public $documents;
	
	/**
	 * @param id			- int identifier
	 * @param name			- author name
	 * 
	 */
	public function __construct( $id, $name ) {
		$this->id = $id;
		$this->cryptoId = Dnst_Crypto_SillyCipher::crypt( $this->id );
		
		$this->name       = $name;
	}
	
	
	
}
?>
