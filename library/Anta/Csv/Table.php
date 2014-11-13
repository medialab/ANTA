<?php
/**
 * @package Anta_Csv
 */
 
/**
 * Handle the table. 
 * 
 */
class Anta_Csv_Table {
	
	/**
	 *@var Anta_Csv_Header
	 */
	public $header;
	
	/**
	 *@var array Anta_Csv_Row
	 */
	public $rows;
	
	public static $breakline = "\n";
	
	public function __construct( $header, $rows = array() ){
		$this->header =& $header;
		$this->rows = $rows;
		
	}
	
	/**
	 * header field => validator
	 * @var array
	 */
	public $validators;
	
	/**
	 * for each header, a specific validator... :D
	 */
	public function setValidators( array $validators ){
		$this->validators = $validators;
	}
	
	protected $_messages;
	
	/**
	 * validate rows only, not headers! 
	 */
	public function isValid(){
		foreach( array_keys( $this->rows ) as $k ){
			$result = $this->rows[ $k ]->isValid( $this->validators );
			echo "<!-- analyze $k -->";
			if( $result === false ){
				$this->_messages = array_keys( $this->rows[ $k ]->getMessages() );
				
				return false;
			}
		}
		return true;
	}
	
	public function getMessages(){
		return $this->_messages;
	}
	
	public function getHeader(){
		return $this->header;
	}
	
	public function addRow( Anta_Csv_Row $row ){
		$this->rows[] = $row;
	}
	
	public static function setBreakline( $breakline ){
		self::$breakline = $breakline;
	}
	
	public function __toString(){
		$csv = ( string ) $this->header . self::$breakline;
		
		foreach( array_keys( $this->rows ) as $k ){
			$csv .= $this->rows[ $k ] . self::$breakline;
		}
		
		return $csv;
	}
	
	public function toHtmlTable(){
		
		$content = "";
		
		foreach( $this->rows as $row ) {
			$content .= $row->toHtmlTd();
		}
		
		return '
		<style>
			table{
				
			}
			td, th{
				padding:5px;
			}
		</style>
		<table class="grid_23 alpha omega">
			'.$this->header->toHtmlTh().'
			'.$content.'
		</table>
		';
		
	}
	
}
