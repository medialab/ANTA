<?php
/**
 * @package Dnst_Filter_Validator
 */

/**
 * Check is numeric, round it? and chose between range.
 */
class Dnst_Filter_Validator_Range extends Dnst_Filter_Validator{
	
	public $min;
	
	public $max;
	
	/**
	 * If it's true, round the value before send it.
	 */
	public $round;
	
	public function __construct( $min, $max, $round = true ){
		$this->min = $min;
		$this->max = $max;
		$this->round = $round;
	}
	
	/**
	 * use the variable as reference
	 */
	public function isValid( $value ){
		
		if( !is_numeric( $value ) ){
			$this->_error( "'".$value ."' ".I18n_Json::get('isNan','errors') );
			return false;
		}
		
		if( $value < $this->min || $value > $this->max ){
			$this->_error( "'".$value ."' ".I18n_Json::get('outOfRange','errors')." [".$this->min." - ".$this->max."]" );
			return false;
		}
		
		if( $this->round ){
			$this->_setValue( round( $value ) );
		}
		
		return true;
	}
	
}
?>
