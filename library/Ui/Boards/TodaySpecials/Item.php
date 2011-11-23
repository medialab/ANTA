<?php
/**
 * @ package Ui_Boards_TodaySpecials
 */

/**
 * specific class for left menus for anta framework.
 * 
 */
class Ui_Boards_TodaySpecials_Item {
	
	public $title;
	
	public $link;
	
	public $attributes;
	
	public $selected = false;
	
	public function __construct( $link, $title, $attributes=array() ){
		
		$this->link = $link;
		$this->title = $title;
		$this->attributes = $attributes;
		
		
	}
	
	public function setAttribute( $name, $value="" ){
		if( $value == "" ) return $this->attributes[ $name ];
		
		if( empty( $this->attributes[ $name ] ) ){
			$this->attributes[ $name ] = $value;
		} else {
			$this->attributes[ $name ].= " ".$value;
		} 
		
	}
	
	public function select() {
		$this->setAttribute( 'class', 'selected' );
		$this->selected = true;
	}
	
	public function __toString(){
		
		$htmlAttributes = "";
		
		foreach( $this->attributes as $attr => $value ){
			$htmlAttributes .= $attr .'="'.$value.'" ';
		}
		
		
		return '
		<li '.$htmlAttributes .'>'. (
			$this->selected?
				"<span>".$this->title."</span>"
			:'
				<a href="'.$this->link.'">
					'.$this->title.'
				</a>'
			).'
		</li>';
	}
	
}