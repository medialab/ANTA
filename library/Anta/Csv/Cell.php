<?php
/**
 * @package Anta_Csv
 */
 
/**
 * Handle the hader
 */
class Anta_Csv_Cell {
	
	public $value;
	public static $enclosure;
	
	public function __construct( $value, $enclosure='"' ){
		$this->value = str_replace( array( self::$enclosure, "\t", ";" ), '', $value );
		self::$enclosure = $enclosure;
	}
	
	
	public function append( $value ){
		$this->value .= strlen( $this->value ) > 0? ",".$value: $value;
	}
	
	public function __toString(){
		return self::$enclosure.''.$this->value.''.self::$enclosure;
	}
	
	public function getValue(){
		return $this->value;
	}
}
?>