<?php
/**
 * @package Ui_Crafts
 */
 
/**
 * describe an html object, usually a generic item of search results
 *
 */
 class Ui_Crafts_Item{
	
	/**
	 * the identifier (css id property as well)
	 * @var string
	 */
	public $id;

	/**
	 * the content, a generic object
	 * @var object
	 */
	public $content;

	/**
	 * Class constructor
	 */
	public function __construct( $id = 0 ){
		$this->id = $id;
		
	}
	
	/**
	 * allow to modify the item before the render ( __toString method )
	 * @properties	- an indexed array containing couple variable_name => 'variable value'
	 */
	public function apply( array $properties ){
		foreach( $properties as $key=>$value)
			$this->$key = $value;
		
	}

	
	public function __toString(){
		return get_class().' class needs to be overriden';
	}
}
 
?>
