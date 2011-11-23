<?php
/**
 * @package Dnst_Filter
 */

/**
 * 
 */
class Dnst_Filter_Validator extends Zend_Validate_Abstract{
	
	/**
	 * If true, skip the isValid control if it's void or null
	 */
	public $optional = false;
	
	public function getMessages(){
		return array_keys( parent::getMessages() );
	}
	
	public function isValid( $value ){
		return true;
	}
	
}
