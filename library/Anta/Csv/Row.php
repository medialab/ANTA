<?php
/**
 * @package Anta_Csv
 */
 
/**
 * Handle the hader
 */
class Anta_Csv_Row {
	
	/**
	 *@var array of Anta_Csv_Cell
	 */
	public $cells;
	
	/**
	 *@var Anta_Csv_Header
	 */
	public $header;

	public static $delimiter =";";
	
	/**
	 * @param string delimiter	- row delimiter
	 */
	public function __construct( Anta_Csv_Header $header, $cells = array() ){
		
		$this->header =& $header;
		$this->value  = $value;
		$this->cells  = $cells;
		
		$this->id = self::$index++;
		
	}
	
	protected $_messages;
	
	protected $_isValid = true;
	
	protected $_indexInvalid = null;
	
	public function isValid( array $validators ){
		
		// cycle validators
		foreach( array_keys( $validators ) as $index ){
			$result = $validators[ $index ] ->isValid( $this->getCell( $index )->getValue() );
			
			if( $result === false ){
			
				$this->_messages = $validators[ $index ]->getMessages();
				
				$this->_isValid = false;
				$this->_indexInvalid = $index;
				
				return false;
			}
		}
	
		return true;
	}
	
	public function getMessages(){
		return $this->_messages;
	}
	
	/**
	 * a method to create an indexed row from an array of cell values.
	 */
	public static function create( Anta_Csv_Header $header, array $rawCells ){
		
		$row = new Anta_Csv_Row( $header );
		// print_r( $row );
		
		foreach( $header->fields as $k=>$field ){
			// try to merge with headers index, expecially designed for raw import data
			if( !isset( $rawCells[ $field ] ) ){
				$row->addCell( $field , new Anta_Csv_Cell( $rawCells[ $k ] ) );
				continue;
	
			} 
			$row->addCell( $field , new Anta_Csv_Cell( $rawCells[ $field ] ) );
		}
		
		return $row;
	}
	

	public static function setDelimiter( $delimiter ){
		self::$delimiter = $delimiter;
	}
	
	public function addCell( $idCell, $cell ){
		$this->cells[ $idCell ] = $cell;
	}
	
	public function getCell( $idCell ){
		return $this->cells[ $idCell ];
	}
	
	public function append( $idCell, $value ){
		$this->cells[ $idCell ]->append( $value );
	}
	
	
	public function __toString(){
		$row = "";
		foreach ( $this->header->fields as $k ){
			$row .= $this->cells[ $k ].self::$delimiter;
		}
		
		return $row;
	}
	
	public static $index = 0;
	
	public function toHtmlTd(){
		$content = "<tr ".( $this->_isValid === false? 'class="invalid"': '' )."><td>".$this->id."</td>";
		foreach ( $this->header->fields as $k ){
			// if invalid index is set, add class "invalid" to the invalid td
			$content .= '<td '.( $this->_indexInvalid == $k ? 'class="invalid"': '' ).'>' . $this->cells[ $k ]->getValue() . '</td>';
		}
		return $content .= "</tr>";
	}
}
?>
