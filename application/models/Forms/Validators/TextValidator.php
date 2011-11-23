<?php
 
/**
 * Validate a text string using given pattern
 */
 class Application_Model_Forms_Validators_TextValidator extends Application_Model_Forms_Validators_JsValidator{
	
	protected $_pattern = '/(^[\w\s\d%@ùèéêçàô\?!\/"\',\.;:-=]+)\z/i';
	
 
	
	public function isValid($value){
		$this->_setValue($value);
		
		if (strlen($value) < $this->minLength) {
			$this->_error( "'".$value."' must be at least composed by " .$this->minLength. " characters" );
			
			return false;
		}
		
		if (strlen($value) > $this->maxLength) {
			$this->_error( " must not exteed " .$this->maxLength. " characters in length, given ".strlen($value) );
			
			return false;
		}
		
        if ( preg_match( $this->_pattern, $value, $matches ) === false ) {
			
			$this->_error( "'".$value."' should contain a-z 0-9 and punctuation chars " );
			return false;
		}
		
		
        return true;
	}
 
 }
?>