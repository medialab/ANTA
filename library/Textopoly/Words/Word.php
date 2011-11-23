<?php
/**
 * @package Textopoly_Words
 */

/**
 * describe a word ( the term and its stem )
 */
class Textopoly_Words_Word{
	
	/** if any, the identifier associated */
	public $id;
	
	/** word value */
	protected $_v;
	
	/** word stem */
	protected $_s;
	
	public function __construct( $value, $stem='' ){
		$this->_v = $value;
		$this->_s = $stem;
	}
	
	
	public function getStem(){
		return $this->_s;
	}
	
	
	public function setStem( $stem ){
		return $this->_s = $stem;
	}
	
	/**
	 * return its value
	 */
	public function __toString(){
		return $this->_v;
	}
}