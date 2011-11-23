<?php
/**
 *@package Ui_Forms_Validators
 */ 
/**
 * Validate a text string using given pattern
 */
 class Ui_Forms_Validators_Entity extends Ui_Forms_Validator{
	
	public $pattern = '/(^[\w\s\d%@ùèéêçàô\?!\/"\',\.;:-=]+)\z/i';
	
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
		
		//specific validation
        if ( preg_match( $this->pattern, $value, $matches ) === false ) {
			
			$this->_error( "'".$value."' should contain a-z 0-9 and punctuation chars " );
			return false;
		}
		
		
        return true;
	}
 
 }
?>