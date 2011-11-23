<?php
/**
 *@package Ui_Forms
 */ 
/**
 * Validate a text string using given pattern
 */
class Ui_Forms_Validator extends Zend_Validate_Abstract{
	
	/** 
	 * string min length allowed 
	 * @var int
	 */
	public $minLength;
	
	/** 
	 * string max length allowed. If is -1, then ignore. Default is -1
	 * @var int
	 */
	public $maxLength = -1;
	
	public function __construct( $properties=array() ){
		foreach( $properties as $k=>$v ){
			$this->$k = $v;
		}

	}
	
	public function getMessages(){
		return parent::getMessages();
	}
	
	public function getPlainMessages(){
		return  implode("; ", array_keys( parent::getMessages() ) );
	}
	
	public function isValid($value){
		if (strlen(trim($value)) < $this->minLength) {
			$this->_error( "'".$value."' must be at least composed by " .$this->minLength. " characters" );
			
			return false;
		}
		
		if( $this->maxLength == -1 ){
			return true;
		}
		
		if (strlen($value) > $this->maxLength) {
			$this->_error( " must not exceed " .$this->maxLength. " characters in length, given ".strlen($value) );
			
			return false;
		}
		return true;
	}
}
?>
