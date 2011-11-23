<?php
/**
 * Handle the upload of a file according to $_GET ( raw data )
 * Every param is cool customizable.
 */
class Application_Model_Forms_Validators_FileXmlUploadValidator extends Zend_Validate_Abstract {
	
	
	
	/** an array of accepted mimeTypes. Default: all. Sample: array("application/pdf","text/plain") */
	public $mimeTypes;
	
	public function getValue(){
		return $this->_value;
	}
	
	/**
	 * For debugging purpose only, $_FILES sample: Array ( [file-content] => Array ( [name] => 10.1.1.115.7885.pdf [type] => application/pdf [tmp_name] => C:\Temp\php9C.tmp [error] => 0 [size] => 180539 ) )
	 *
	 * @param value	- the index of the $_GET data
	 */
	public function isValid( $value ){
		$this->_setValue( "" );
		
		
		if( empty( $_GET[ $value ] ) ){
			$this->_error("$value xml stream: no file was provided");
			
			return false;
		}
		
		// clean value
		

        return true;

    }

		
}
?>
