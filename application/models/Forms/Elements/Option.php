<?php
/**
 * Select (jquery) pseudo-OPTION tag
 */
class Application_Model_Forms_Elements_Option {

	/** either 'jquery' or 'plain-option' */
	public $type;
	
	public $label;
	
	public $value;
	
	protected $_selected ='';
	
	/**
	 * Class constructor
	 * If type 'jquery' is specified, value must be the href string, cfr. 
	 * <a href="https://github.com/fnagel/jquery-ui">https://github.com/fnagel/jquery-ui</a>, branche 'selectmenu'
	 *
	 * Create an option according to Select type (jquery UI or plain html)
	 * @param label
	 * @param value
	 * @param type	- 'jquery' or 'option'. 'plain-option' is the default
	 */
	public function __construct( $label, $value, $isSelected = false, $type='plain-option' ){
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
		
		if( $this->type == 'plain-option' ){
			return '<option value="'.$this->value.'" '.$this->_selected.'>'.$this->label.'</option>';
		}
	
		return '<li><a href="'.$this->value.'">'.$this->label.'</a></li>';
	}
}

