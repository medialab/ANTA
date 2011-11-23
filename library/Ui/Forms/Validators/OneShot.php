<?php
/**
 *@package Ui_Forms_Validators
 */ 
/**
 * Validate a string if and only if is equal to the given one
 */
 class Ui_Forms_Validators_NumericRange extends Ui_Forms_Validator{
	
	
	/**
	 * the value to be checked
	 * @var string
	 */
	public $shot;
	
	/**
	 * The custom error description
	 * @var string
	 */
	public $errorDescription = "";
	
	public function isValid($value){
	
		$this->_setValue($value);
		
		// simpler than ever
		if( $value != $this->shot ){
			$this->_error( $this->errorDescription );
			return false;
		}
		
        return true;
	}
 
 }
?>