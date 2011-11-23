<?php
/**
 * @package
 */
 
/**
 * describe a document
 */
class Application_Model_Document{
	
	/** doc crypto id, to hide the real doc idin links */
	public $cryptoId;
	
	/** doc db id */
	public $id;
	
	/** document title */
	public $title;
	
	/** document description */
	public $description;
			
	/** doc mime type, like "text/pdf" */
	public $mimeType;
	
	/** file size, in bytes */
	public $size;
	
	/** doc language, e.g. "en" */
	public $language;
	
	/** document date (if selected by the user, default CURRENT_TIMESTAMP */
	public $date;
	
	/** docuemnt status e.g ready | indexing | indexed | error */
	public $status;
	
	/** the Application_Model_User owner */
	public $owner;
	
	/** tag attached to the documents, instance of Application_Model_Author */
	public $authors = array();
	
	/** all tags attached to the document*/
	public $tags = array();
	
	/** 1-0 value, if 1 it will be ignored during visuqlization */
	public $ignore;
	
	/**
	 * @param id			- int identifier
	 * @param title			- user name
	 * @param description	- user type, "guest" or "admin"
	 * @param mymeType		- document mimeType
	 * @param language		- en, fr, it...
	 * @param date			- mysql date 2011-01-22 18:39:06
	 * @param localUrl		- 
	 */
	public function __construct( $id = -1, $title = "", $description = "", $mimeType = "", $size = 0, $language = "", $date = "", $localUrl = "", $status= "", $owner = null, $ignore=0 ) {
		$this->id = $id;
		$this->cryptoId = Dnst_Crypto_SillyCipher::crypt( $this->id );
		
		$this->title       = $title;
		$this->description = $description;
		$this->mimeType    = $mimeType;
		$this->size        = $size;
		$this->language    = $language;
		$this->date        = $date;
		$this->localUrl    = $localUrl;
		$this->status      = $status;
		$this->owner       =& $owner;
		$this->ignore = $ignore;
		
	}
	
	/**
	 * Extablish if documents match one or more given mimeTypes
	 */
	public function is( $mimeType ){
		
		$args = func_get_args();
		
		$isType = false;
		
		foreach( $args as $_mimeType ){
			if( $this->mimeType == $_mimeType ){
				$isType = true;
				break;
			}
		}
		
		return $isType;
	}
	
}
