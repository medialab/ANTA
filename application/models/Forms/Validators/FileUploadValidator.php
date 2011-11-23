<?php
/**
 * Handle the upload of a file according to $_FILES
 * Every param is cool customizable.
 */
class Application_Model_Forms_Validators_FileUploadValidator extends Zend_Validate_Abstract {
	
	
	
	/** an array of accepted mimeTypes. Default: all. Sample: array("application/pdf","text/plain") */
	public $mimeTypes;
	
	public function getValue(){
		return $this->_value;
	}
	
	/**
	 * For debugging purpose only, $_FILES sample: Array ( [file-content] => Array ( [name] => 10.1.1.115.7885.pdf [type] => application/pdf [tmp_name] => C:\Temp\php9C.tmp [error] => 0 [size] => 180539 ) )
	 *
	 * @param value	- will be ignored. The current $_FILES will be evaluated
	 */
	public function isValid( $value="" ){
		$this->_setValue( "" );
		
		
		if( empty( $_FILES ) ){
			$this->_error("No file was provided");
			
			return false;
		}
		
		
		
		/* files to upload */
		$names = array_keys( $_FILES );
		
		
		// check for inner errors
		foreach( $names as $name ){
			
			$this->_setValue( $_FILES[ $name ]['name'] );
			
			if( $_FILES[ $name ] ["error"] != 0 ) {
			
				switch( $_FILES[ $name ]["error"] ){
				
				
					case UPLOAD_ERR_INI_SIZE:
						$this->_error( "".I18n_Json::get( 'uploadErrIniSize' ) );
					break;
					case UPLOAD_ERR_FORM_SIZE:
						$this->_error( "".I18n_Json::get( 'upload_err_form_size' ) );
					break;
					case UPLOAD_ERR_PARTIAL:
						$this->_error( "".I18n_Json::get( 'upload_err_partial' )  );
					break;
					case UPLOAD_ERR_NO_FILE:
						$this->_error( "".I18n_Json::get( 'upload_err_no_file' )  );
					break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$this->_error( "".I18n_Json::get( 'upload_err_no_tmp_dir' )  );
					break;
					case UPLOAD_ERR_CANT_WRITE:
						$this->_error( "".I18n_Json::get( 'upload_err_cant_write' )  );
					break;
					case UPLOAD_ERR_EXTENSION:
						$this->_error( "".I18n_Json::get( 'upload_err_extension' )  );
					break;
					
				}
				
				return false;
			}
		}
		
		// check for mimeTypes, if specified
		if( $this->mimeTypes != null ){
			
		}

        return true;

    }

		
}
?>
