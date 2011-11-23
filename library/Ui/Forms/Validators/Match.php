<?php
/**
 *@package Ui_Forms_Validators
 */ 
/**
 * Validate a text string using given pattern
 */
 class Ui_Forms_Validators_Match extends Ui_Forms_Validator{
	
	public $availables = array();
	
	public $minLength;
	
	public $maxLength;
	
	public function __construct( $properties=array() ){
		foreach( $properties as $k=>$v ){
			$this->$k = $v;
		}
	}
	
	public function isValid($value){
		$this->_setValue($value);
		
		// common behaviour
		if( parent::isValid( $value ) === false ){
			return false;
		}
		
		if( !in_array( $value, $this->availables ) ){
			$this->_error( "'$value' is not a valid value" );
			return false;
		}
		
        return true;
	}
 
 }
?>