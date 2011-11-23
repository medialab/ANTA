<?php
/**
 * @ package Ui
 */

/**
 * base class for boards  instances, singleton pattern
 */
class Ui_Board {
	
	protected static $_instance;
	
	public static function getInstance( $suffix = "", array $properties=array() ){
		if( self::$_instance == null ){
			$boardSubClass = "Ui_Boards_".$suffix;
			self::$_instance = new $boardSubClass();
			self::$_instance->init( $properties );
		}
		return self::$_instance;
	}
	
	public function __toString(){
		return "ooops! maybe you'd rather prefer an Ui_Board subclass...";
	}
	
}