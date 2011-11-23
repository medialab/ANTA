<?php
/**
 * a flexible way to build mysqli related classes.
 * usage:
 * Application_Model_Project extends Application_Model_Configurable
 * new Application_Model_Project( array("property"=>"value") )
 */
class Application_Model_Configurable{
	public function __construct( array $properties = array() ){
		foreach( $properties as $key => $value ){
			$this->$key = $value;
		}
	}

	public static function load( $row ){
		
	}
}
