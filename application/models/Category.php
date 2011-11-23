<?php
/**
 * describe a category entry in categories table
 */
class Application_Model_Category{
	public $id;
	public $content;
	public $type;
	
	public function __construct( $id, $content, $type ){
		$this->id = $id;
		$this->content = $content;
		$this->type = $type;
	}
}
?>