<?php
/**
 * @package Ui
 */

/**
 * zip multiples files, the easy way.
 * Cfr. http://www.php.net/manual/fr/book.zip.php
 * The ZipArchive instance is Ui_Zip->archive variable. use the method add to add multiple files as function args
 * (func_get_args)
 * usage:
 *  $zip = new Ui_Zip( zip file )
 *  $zip->add( "/path/to/file", "file.txt" );
 *  $zip->isValid(); // return true or false
 *  $zip->getMessages(); // return an array of messages collected during the zipping
 *
 */
class Ui_Zip {
	
	public $z;
	protected $_messages = array();
	protected $_filepath;
	public function __construct( $zipFilePath ){
		$this->_filepath = $zipFilePath;
		$this->z = new ZipArchive();
		if( $this->z->open( $zipFilePath, ZIPARCHIVE::CREATE )!== TRUE ) {
    		$this->_error("zip open <$zipFilePath> failed");
    		return;
		}	
	}
	
	/**
	 * add files to the archive
	 */
	public function add( $files ){
		if( !$this->isValid() ) return;
		$files = func_get_args();
		foreach( $files as $file ){
			if( !file_exists( $file ) ){
				$this->_error( "file not found: <$file>" );
				continue;
			}
			$this->z->addFile( $file, basename( $file ) );
		}
		
	}	
	
	public function close(){
		$this->z->close();	
	}
	
	public function isValid(){
		return empty( $this->_messages );	
	}
	
	public function getMessages(){
		return $this->_messages;
	}
	
	/**
	 * @return the file path of the zip file
	 */
	public function __toString(){
		return "".$this->_filepath;
	}
	
	protected function _error( $message ){
		$this->messages[] = $message;	
		
	}
	
}
?>