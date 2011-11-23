<?php
/**
 * @package Application_Model
 */

/**
 * pseudo abstract class for entityes management. It will become Application_Model_Entity soon.
 * Eacn Entity can have one or more tag which qualify the textual content
 */
class Application_Model_SubEntity extends Application_Model_Entity{
	
	/**
	 * the table identifier. Note that the table it's identified by the prefix.
	 * @var string
	 */
	public $id;
	
	/**
	 * the "super_" table identifier.
	 * @var string
	 */
	public $pid;
	
	/**
	 * the translitteral representation of the entity id, using SillyCipher algorithm
	 * @var string
	 */
	public $cryptoId;
	
	/**
	 * the prefix denotes the table from which the entity belongs to
	 * @var string
	 */
	public $prefix;
	
	
	/**
	 * the text content of the entity
	 * @var string
	 */
	public $content;
	
	/**
	 * the standard float relevance score
	 * @var float
	 */
	public $relevance;
	
	/**
	 * the number of document sharing the entity
	 */
	public $frequency = 0;
	
	/**
	 * the user identifier, crypted.
	 * @var string
	 */
	public $userCryptoId = "0";
	
	/**
	 * ignore flag 0=do not ignore, 1= ignore
	 */
	public $ignore;
	
	/**
	 * an array of tags
	 */
	public $tags = array();
	
	public static $DO_NOT_IGNORE = 0;
	public static $IGNORE = 1;
	/**
	 * Class constructor
	 * 
	 */
	public function __construct( $id, $table_id, $content, $prefix, $relevance = 0, $frequency = 0, $pid =0, $userCryptoId = 0, $ignore = 0 ){
		$this->id        = $id;
		$this->table_id  = $table_id;
		$this->cryptoId  = Dnst_Crypto_SillyCipher::crypt( $table_id );
		$this->content   = $content;
		$this->prefix    = $prefix;
		$this->relevance = $relevance;
		$this->frequency = $frequency;
		$this->pid       = $pid;
		
		$this->userCryptoId = &$userCryptoId;
		$this->ignore = $ignore;
	}
	
}
