<?php
/**
 * @ package Ui_Boards
 */

/**
 * specific class for left menus for anta framework.
 * 
 */
class Ui_Boards_Prepare extends Ui_Boards_TodaySpecial {

	public function init( $properties ){
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		
		// initialize menu items
		$this->items[ "visualize.gexf" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/visualize/gexf/user/'.  $this->user->cryptoId ,
			I18n_Json::get( 'export settings' )
			
		);
		$this->items[ "visualize.export" ] = new Ui_Boards_TodaySpecials_Item(
			"#",
			I18n_Json::get( 'export a gexf' ),
			array(
				"id" => "create-default-gexf"
			)
		);
		
		
		
		$this->_autoSelectItem();
	}
	
	/**
	 * Same as its parent, but inject some javascript code into the page to handle ajax-href
	 * behaviour( not ereal link, but api request)
	 */
	public function __toString(){
		
		return parent::__toString();
	}
	
}
