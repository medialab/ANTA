<?php
/**
 * @package Dnst_Filter_Validator
 */

/**
 * Simply validate the value to match at least one value between the values provided.
 */
class Dnst_Filter_Validator_Array extends Dnst_Filter_Validator{
	
	public $validValues;
	
	public function __construct( array $validValues ){
		$this->validValues = $validValues;
	}
	
	public function isValid( $values ){
		
		$values = (array) $values;
		
		if( !is_array( $values ) ){
			$this->_error( "'".$values ."' ".I18n_Json::get('filter-not-valid-array','errors') );
			return false;
		}
		
		foreach( $values as $value ){
		
			if (!in_array( $value, $this->validValues ) ){
				$this->_error( "'".$value ."' ".I18n_Json::get('filter-not-valid-value','errors') );
				return false;
			}
		}
		return true;
	}
	
}
?>
