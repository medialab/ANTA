<?php
/**
 * @package Ui_D3_Timeline
 */

/**
 * the single istance of time
 */
class Ui_D3_Timeline_Point{
	public $t;
	
	public function __construct( $t, $atts = array()){
		$this->t = $t;
		foreach( $atts as $att=>$value ){
			$this->$att = $value;
		}
	}
	
}
?>