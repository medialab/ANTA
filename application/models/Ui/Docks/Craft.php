<?php
/**
 * @package Ui
 */

/**
 * base class for craft elements.
 */
class Ui_Craft{

	/** a title, displayed just below the icon */
	public $title;
	
	/** the type of module */
	public $id;	
	
	/** 
	 * Describe the content of the craft ( e.g, the list of items, or a description or ... ).
	 * To be used into subclasses __toString() method, cfr. Dnst_Ui_Craft_Cargo, to be used as you wish
	 */
	protected $_content;
	
	/** each craft, just before the content, may have an header (filtering, sorting, displaying the number of items...). use setHeader() method */
	protected $_header;
	
	/** Contains an Application_Model_Forms_SimpleForm instance. use setCreateForm() method. */
	protected $_createForm;
	
	
	protected $_creationLink;
	
	/**
	 * create a craft
	 * @param id	- type identifier AKA icon file name, like "calendar"
	 * @param title	- string naming this module
	 */
	function __construct( $id="default", $title = "new title" ){
		
		$this->title = $title;	
		$this->id	= $id;
			
	}
	
	public function init(){
	
	}
	
	/**
	 * Add an header to the craft.
	 * @todo
	 * @param header	- an instance of Dnst_Ui_Crafts_Header
	 */
	public function setHeader( $header ){
		$this->_header = $header;
	}
	
	public function setContent( $content ){
		$this->_content = $content;
	}
	
	
	/**
	 * Add a form
	 */
	public function setCreateForm( Application_Model_Forms_SimpleForm $form ){
		$this->_createForm = $form;
	}
	
	/**
	 * @return  Application_Model_Forms_SimpleForm instance (or subclasses ), or null
	 */
	public function getCreateForm( ){
		return $this->_createForm;
	}
	
	public function setCreationLink( $link, $title, $attributes=array() ){
		$htmlAttributes = "";
		
		
		
		foreach( $attributes as $k => $v ){
			$htmlAttributes .= $k .'="'.$v.'"';
		}
	
		$this->_creationLink = '<a class="a-button" href="'.$link.'" '.$htmlAttributes.'>'.$title.'</a>';
	}
	
	/**
	 * Output the resulting ready-to-render html string
	 */
	public function __toString(){
		$html ='
		<div class="grid_24 alpha omega craft">
			<div class="grid_24 alpha omega craft-title">
				<div class="grid_1 alpha">
					<img class="flow-icon" src="'.Anta_Core::getBase().'/images/'.$this->id.'.png" alt=" " />
				</div>
				<h2 class="grid_16 suffix_1">'. $this->title .'</h2>
				<div class="grid_4 omega">'.$this->_creationLink.'</div>
			</div>'.
			( $this->_createForm != null? '<div class="grid_24 alpha omega craft-form">'.    $this->_createForm. '</div>': '' ).
			( $this->_header     != null? '<div class="grid_24 alpha omega craft-header">'.  $this->_header.     '</div>': '' ).
			( $this->_content    != null? '<div class="grid_24 alpha omega craft-content">'. $this->_content.    '</div>': '' ).'
		</div>';
				
		return $html;
	}
}
?>