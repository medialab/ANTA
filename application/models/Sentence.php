<?php
/**
 * @package Application_Model
 */

/**
 * 
 */
class Application_Model_Sentence {

	/**
	 * the table identifier. Note that the table it's identified by the prefix.
	 * @var string
	 */
	public $id;
	
	/**
	 * the container document crypted id
	 * @var string
	 */
	public $documentCryptoId;

	/**
	 * the text content of the sentence
	 * @var string
	 */
	public $content;
	
	/**
	 * the position int from the beginning of the document
	 * @var int
	 */
	public $position;
	
	/**
	 * the document date, given by reference
	 */
	public $date;
	
	/**
	 * the document title, given by reference
	 */
	public $title;
	
	
	/**
	 * class constructor
	 */
	public function __construct( $id, $content, $documentId, $position, $date = "12/10/2001", $title="" ){
		
		$this->id = $id;
		$this->content = $content;
		$this->position = $position;
		$this->documentId = $documentId;
		$this->documentCryptoId = Dnst_Crypto_SillyCipher::crypt( $documentId );
		$this->date = $date;
		$this->title = $title;
	}
}
?>