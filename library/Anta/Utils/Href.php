<?php
/**
 * @package Anta_Utils
 */

/**
 * prodide an unique mechanism to link an object
 */
class Anta_Utils_Href{
	
	public $url;
	public $object;
	public $attributes;
	
	public function __construct( $url, $label, $object = null ){
		if( $object != null && $object->$label != null){
			$this->label = $object->$label;
		} else {
			$this->label = $label;
		}
		$this->attributes = array( 'href'=>$url, 'title'=>$label );
		return $this;
	}
	
	protected function init( $attributes ){
		$this->attributes = $attributes;
	}
	
	public function __toString(){
		return '<a '.iatts($this->attributes).'>'.$this->label.'</a>';
	}

}
?>