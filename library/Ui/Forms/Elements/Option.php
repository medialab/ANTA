<?php
/**
 * @package Ui_Forms_Elements
 */
/**
 * Select option tag with autoselector
 */
class Ui_Forms_Elements_Option {


	
	public $label;
	
	public $value;
	
	protected $_selected ='';
	
	/**
	 * Class constructor
	 *
	 * Create an option according to Select type (jquery UI or plain html)
	 * @param label
	 * @param value
	 */
	public function __construct( $label, $value, $isSelected = false ){
		$this->label = $label;
		$this->value = $value;
		$this->type  = $type;
		$this->setSelected( $isSelected );
	}
	
	public function setSelected( $selected){
		$this->_selected = $selected? 'selected="selected"':'';
	}
	
	public function isSelected(){
		return $this->_selected !== '';
	}
	
	
	
	public function __toString(){
		
		return '<option value="'.$this->value.'" '.$this->_selected.'>'.$this->label.'</option>';
		
	}
}

