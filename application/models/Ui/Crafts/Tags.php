<?php
/**
 * @package Ui_Crafts
 */

/**
 * Override the toString function of Craft class, but uses the addItem method of cargo class.
 * It load a export / import csv handler.
 */
class Application_Model_Ui_Crafts_Tags extends Application_Model_Ui_Crafts_Cargo{

	protected $_document;
	protected $_user;
	
	public function init( $user, $document ){
		$this->_user     =& $user;
		$this->_document =& $document;
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
			( $this->_header     != null? '<div class="grid_24 alpha omega craft-header">'.  $this->_header.     '</div>': '' ).'
			<div class="grid_20 alpha craft-content">'. $this->_content. '&nbsp;</div>
			<div class="grid_4 omega tags-board">
				<div class="grid_4 alpha omega centered">
					<a class="a-button" href="'.Anta_Core::getBase().'/documents/export-tags/document/'.$this->_document->cryptoId.'/user/'.$this->_user->cryptoId.'">export csv</a></div>
				<div class="grid_4 alpha omega centered">
					<a class="a-button" href="'.Anta_Core::getBase().'/documents/import-tags/">import csv</a></div>
			</div>
		</div>';
				
		return $html;
	}
}