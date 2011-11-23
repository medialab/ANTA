<?php
/**
 * @package Dnst_Filter_Validator
 */

/**
 * Check is numeric, round it? and chose between range.
 */
class Dnst_Filter_Validator_Pattern extends Dnst_Filter_Validator{
	
	public $minLength;
	
	public $maxLength;
	
	/**
	 * If it's not empty, validate the valus with a given pattern. You can specify static values!
	 */
	public $pattern;
	
	
	
	public function __construct( $minLenght, $maxLength, $pattern = "" ){
		$this->minLength = $minLenght;
		$this->maxLength = $maxLength;
		$this->pattern = $pattern;
	}
	
	/**
	 * use the variable as reference
	 */
	public function isValid( $value ){
		
		$value = trim( $value );
		$length = strlen( $value );
		
		if( $length < $this->minLength || $length > $this->maxLength ){
			$this->_error( "'".$value ."' ".I18n_Json::get('outOfTextLength','errors')." [".$this->minLength." - ".$this->maxLength."]" );
			return false;
		}
		
		if( !empty( $this->pattern ) ){
			// validate pattern regexp here...
		}
		
		return true;
	}
	
}
?>
