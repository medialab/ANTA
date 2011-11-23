<?php
/**
 * @package Ui_Crafts_Headers_Lenses
 */

/**
 * allow to manage a list of words
 */
class Ui_Crafts_Headers_Lenses_MatchAgainst extends Ui_Crafts_Headers_Lens{
	
	
	protected function _init(){
		
	}
	
	public function __toString(){
		$this->_filters = '
		hello';
		
		return parent::__toString();
	}
}