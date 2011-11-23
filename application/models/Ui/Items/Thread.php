<?php
/**
 * @package Ui_Items
 */

/**
 * 
 */
class Application_Model_Ui_Items_Thread extends Application_Model_Ui_Items_Item {
 
	/**
	 * the bound Application_Model_Thing instances to be displayed
	 */
	public $thread;
	
	public $user;
	
	public function  __construct( Application_Model_Thread $thread, Application_Model_User $user ){
		$this->thread = $thread;
		$this->user =& $user;
		parent::__construct( $thread->id );
		
	}
	
	public function __toString(){
		
		return '
		<div class="grid_22 prefix_1 suffix_1 alpha omega item">
		  <div class="grid_1 alpha identifier">'.$this->thread->id.'</div>
		  <div class="grid_13 thread">'.I18n_Json::get( 'thread'.$this->thread->type ) .'</div>
		  <div class="grid_7">'.$this->thread->status.'</div>
		  <div class="grid_1 omega"><a href="'.Anta_Core::getBase().'/thread/remove/id/'.$this->thread->id.'/user/'.$this->user->cryptoId.'"><img src="'.Anta_Core::getBase().'/images/cross-small.png"></a></div>
		</div>';
	}
	

}

?>
