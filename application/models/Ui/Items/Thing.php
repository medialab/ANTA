<?php
/**
 * @package Ui_Items
 */

/**
 * A thing is a group of entities. Use a list of entities
 * to build this thing.
 */
class Application_Model_Ui_Items_Thing extends Application_Model_Ui_Items_Item {
 
	/**
	 * the bound Application_Model_Thing instances to be displayed
	 */
	public $thing;
	public static $uid = 0;
	
	public function  __construct( Application_Model_Thing $thing ){
		$this->thing = $thing;
		parent::__construct( "g".$thing->id );
		
	}
	
	public function __toString(){
		
		return '
		<div class="grid_24 alpha omega item">
		  <div class="grid_1 alpha identifier">'.(self::$uid++).'</div>
		  <div class="grid_8 thing-id">'.$this->thing->id.'...</div>
		  <div class="grid_8">'.$this->thing->getLabel().'</div>
		  <div class="grid_2 thing-spread">'.$this->thing->getSpread().'</div>
		  <div class="grid_2 thing-status">not saved</div>
		  <div class="grid_2 omega thing-relevance">'.number_format($this->thing->getRelevance(), 2, '.','').'</div>
		  <div class="grid_24 alpha omega item-entity"></div>
		</div>';
	}
	

}

?>
