<?php
/**
 * @package Anta_Csv
 */
 
/**
 * Handle the hader
 */
class Anta_Csv_Header {
	
	/** an array of identifier string */
	public $fields;
	
	public function __construct( array $fields = array() ){
		$this->fields = array_values( $fields );
	}
	
	
	public function isValid( array $headers ){
		foreach( $headers as $header ){
			if( !in_array( $header, $this->fields ) ) return  $header;
		}
		return true;
	}
	
	public function getCustomFields( array $defaultFields ){
		return array_diff( $this->fields , $defaultFields);
	}
	
	public function addField( $value ){
		$this->fields[ ] = $value;
	}
	
	public function __toString(){
		
		$row = "";
		
		foreach ( $this->fields as $field ){
			$row .= new Anta_Csv_Cell( $field ). Anta_Csv_Row::$delimiter;
		}
		
		return $row;
	}
	public function toHtmlTh(){
		$row = "<tr><th>&nbsp;</th>";
		
		foreach ( $this->fields as $field ){
			$row .= '<th>'. $field .'</th>';
		}
		
		return $row.'</tr>';
	}
} 
?>
