<?php
/**
 * @package Ui_Crafts
 */
 
/**
 * A Cargo is basically a craft that collects items.
 * Every item may be found using its id:
 * 
 * $cargo = new Ui_Crafts_Cargo("cargo", "a lot of items");
 * 
 * // add an item
 * $cargo->addItem( new Ui_Items_Item( "id_item_1", array( "title" => "some properties") ) );
 * 
 * // output the item using its id
 * print_r( $this->id_item_1 );
 * 
 */
class Ui_Crafts_Cargo extends Ui_Craft {
	
	/** a collection of Dnst_Ui_items_Item instances (or subclasses) */
	public $items;
	
	/**
	 * Add an Item to the list
	 */
	public function addItem( Ui_Crafts_Item &$item, $properties=array() ){
		
		if( $this->items == null ) $this->items = array();
		
		$varname = str_replace("-","_",$item->id);
		$this->items[ $varname ] =& $item;
		
		foreach( $properties as $key => $value )
			$this->items[ $varname ]->$key = $value;
		
		
		$this->$varname =& $this->items[ $varname ];
		return $item;
	}
	
	public function getItem( $varname ){
		return $this->items[ $varname ];
	}
	
	public function __toString(){
		if ( empty( $this->items ) ) return parent::__toString();
		if( !empty( $this->_content ) ) return parent::__toString();
		foreach( array_keys( $this->items ) as $k ){
			$this->_content .= $this->items[ $k ];
		}
		return parent::__toString();
	}
	
}

?>
