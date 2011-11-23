<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an entity Application_Model_Tag and provide a link toward the controller "TagController"
 * 
 */
 class Ui_Crafts_Items_Tag extends Ui_Crafts_Item {
	
	
	public $user;
	
	public $tag;
	
	public function __construct( $tag, $user ){
		$this->tag  =& $tag;
		$this->user =& $user;
	}
	
	public function __toString(){
		return '<a class="is-tag" href="'.Anta_Core::getBase().'/tag/view/id/'.$this->tag->cryptoId.'/user/'.$this->user->cryptoId.'">'.$this->tag->content.'</a>';
	} 
	 
 }
 ?>
