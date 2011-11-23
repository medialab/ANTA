<?php
/**
 * @package ui_Forms_Validators
/**
 * Handle the upload of a file according to $_FILES
 * Every param is cool customizable.
 */
class Ui_Forms_Validators_FileUpload extends Zend_Validate_Abstract {
	
	public $acceptedMimeTypes = array( "text/plain", "text/csv" );
	
	
	
	public function getValue(){
		return $this->_value;
	}
	
	public function __construct( $properties=array() ){
		foreach( $properties as $k=>$v ){
			$this->$k = $v;
		}

	}
	
	/**
	 * For debugging purpose only, $_FILES sample: Array ( [file-content] => Array ( [name] => 10.1.1.115.7885.pdf [type] => application/pdf [tmp_name] => C:\Temp\php9C.tmp [error] => 0 [size] => 180539 ) )
	 *
	 * @param value	- will be ignored. The current $_FILES will be evaluated
	 */
	public function isValid( $value ){
		$this->_setValue( "" );
		
		if( empty( $_FILES ) ){
			$this->_error( I18n_Json::get( "No file was provided" ) );
			
			return false;
		}
		
		/* files to upload */
		$names = array_keys( $_FILES );
		
		
		// check for inner errors
		foreach( $names as $name ){
			
			$this->_setValue( $_FILES[ $name ]['tmp_name'] );
			
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
		
		if(! in_array( mime_content_type (  $_FILES[ $name ] ["tmp_name"] ), $this->acceptedMimeTypes ) ){
			$this->_error( "".I18n_Json::get( 'mime_type_not_accepted' )  );
		}
		

        return true;

    }

		
}
?>
