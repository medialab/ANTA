<?php
/**
 * Describe the common behaviour of a Dock, a collection / list of  Application_Model_Ui_Crafts_Craft instances 
 * @version alpha
 * @package Ui
 */
 
 /**
  * A "dock" is a html object that will contain a lot of "craft" aka Ui_Craft instances.
  * 
  * Note: You don't really need to extends this class.
  *
  * @author Daniele Guido
  */
class Ui_Dock {
	
	/**
	 * an pairs list where index are items-id and values are item-objects
	 * @var array
	 */
	public $crafts;
	
	/** css "id" selector to be used */
	public $id;
	
	/**
	 * class constructor.
	 * create a dock instance
	 * 
	 * @param string id	- css id selector
	 */
	function __construct( $id = "theDock" ){
		$this->id = $id;
		$this->crafts = array();
	}
	
	/**
	 * Output html strings. For each children, output html using their __toString() method
	 */
	public function __toString(){
		
		$html ='';
		
		foreach( array_keys($this->crafts) as $idModule ){
			$html .= $this->crafts[ $idModule ];
		}
		
		return $html;
	}
	
	/**
	 * Add a craft to the dock
	 * @param craft	- the Application_Model_Ui_Crafts_Craft instance
	 * @return the same Application_Model_Ui_Crafts_Craft instance
	 */
	function addCraft( Ui_Craft $craft ){
		$varname = str_replace("-","_",$craft->id);
		$this->crafts[  $varname ] = $craft;
		$this->$varname = $craft;
		return $this->$varname;
	}
	
	/**
	 * Return the Application_Model_Ui_Crafts_Craft instance by the css id selector
	 */
	public function getCraft( $id ){
		$varname = str_replace("-","_",$id);
		return $this->$varname;
	}
}
?>