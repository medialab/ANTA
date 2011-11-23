<?php
/**
 * @package Forms_Validators
 */
 
/**
 * A Validator extending Zend_Validate_Abstract with a function jsInit and 
 * some variables to be used in javascript validation, so you can always refer to the Zend docs
 * to use protected $_messageTemplates and implements value function.
 */ 
 class Application_Model_Forms_Validators_JsValidator extends Zend_Validate_Abstract {
	
	/** string min length allowed */
	public $minLength;
	
	/** string max length allowed */
	public $maxLength;
	
	/** javascript REGEXP compatible expression */
	public $jsPattern;
	
	/** a description of the error should always be provided */
	public $description = '';
	
	public function __construct( $properties=array() ){
		foreach( $properties as $k=>$v ){
			$this->$k = $v;
		}
	}
	
	/**
	 * initialize js properties properly
	 * @param jsPattern				- a javascript REGEXP compatible expression
	 * @param jsPatternDescription	- code error message
	 * @param minLength				- (optional) min String length
	 * @param maxLength				- (optional) max String length
	 */
	public function setJsValidation( $jsPattern, $description, $minLength=0, $maxLength=-1  ){
		$this->jsPattern   = $jsPattern;
		$this->description = $description;
		$this->minLength   = $minLength;
		$this->maxLength   = $maxLength == -1? 1234567890: $maxLength;
	}
	
	public function isValid($value){
		return true;
	}
	
	public function generateCryptedValue( $value ){
		return str_repeat( "*", strlen( $value ) ); 
	}
 }
?>