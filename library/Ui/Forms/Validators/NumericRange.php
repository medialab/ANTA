<?php
/**
 *@package Ui_Forms_Validators
 */ 
/**
 * Validate a NUMERIC string using ranges, min and max excluded
 */
 class Ui_Forms_Validators_NumericRange extends Ui_Forms_Validator{
	
	public $min;
	
	public $max = -1;
	
	public function isValid($value){
		$this->_setValue($value);
		
		// common behaviour
		if( !is_numeric( $value ) ){
			$this->_error( "'$value' is not a numeric value" );
			
			return false;
		}
		
		
		
		if(  $value < $this->min || ( $value > $this->max && $this->max != -1 ) ){
			$this->_error( "'$value' is not in range ".$this->min." - ".$this->max );
			return false;
		}
		
        return true;
	}
 
 }
?>