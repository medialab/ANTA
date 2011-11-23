<?php
/**
 * @package Ui_Items
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Items_User extends Application_Model_Ui_Items_Item{
	
	public $user;
	
	/**
	 * Class constructor
	 */
	public function __construct( Application_Model_User $user ){
		
		$this->user =& $user;
		parent::__construct( $user->id );
	}
	
	/**
	 * gets its routine status, with the ability to kill / start it
	 * 
	 */
	protected function _routine(){
		$status = Application_Model_RoutinesMapper::getStatus( $this->user->id );
		
		// script ajax to read status
		$html = '
		<script type="text/javascript">
			$(document).ready( function(){
				alert("aaaaaaaaargh");
			});
		</script>
		';
		
		$html .= '</div">';
		
		return $html;
	}
	
	public function __toString(){
	
		return '
		<div class="grid_24 alpha omega item">
		  <div class="grid_2 prefix_1 alpha item-type">'.$this->user->type.'</div>
		  <div class="grid_3 item-title"><a href="'.Anta_Core::getBase().'/documents/list/user/'.$this->user->cryptoId.'" title="visit user documents">'.$this->user->username.'</a></div>
		  <div class="grid_6 item-title">'.$this->user->realname.'&nbsp;</a></div>
		  <div class="grid_4 ">'.$this->user->email.'</div>
		  <div class="grid_1 prefix_1" style="text-align:center"><div class="ajax-routine" id="'.$this->user->cryptoId.'"><img src="'.Anta_Core::getBase().'/images/loading.gif" alt="loading" /></div></div>
		  <div class="grid_1 suffix_1 "><a href="/routine/clear/user/'.$this->user->cryptoId.'" title="'.I18n_Json::get('resetUserRoutine').'"><img src="'.Anta_Core::getBase().'/images/reset-user.png"/></a></div>
		  <div class="grid_1"><a href="'.Anta_Core::getBase().'/logs/read/user/'.$this->user->cryptoId.'" title="'.I18n_Json::get('readUserLog').'"><img src="'.Anta_Core::getBase().'/images/clear-monitor.png"/></a></div>
		  <div class="grid_1"><a href="'.Anta_Core::getBase().'/logs/clear/user/'.$this->user->cryptoId.'" title="'.I18n_Json::get('clearUserLog').'"><img src="'.Anta_Core::getBase().'/images/monitor.png"/></a></div>
		  <div class="grid_1"><a href="'.Anta_Core::getBase().'/users/'.$this->user->cryptoId.'" title="'.I18n_Json::get('editUser').'"><img src="'.Anta_Core::getBase().'/images/edit-user.png"/></a></div>
		  <div class="grid_1 omega"><a href="'.Anta_Core::getBase().'/admin/remove/user/'.$this->user->cryptoId.'" title="'.I18n_Json::get('deleteUser').'"><img src="'.Anta_Core::getBase().'/images/cross-small.png"/></a></div>
		</div>';
	}
}
 
?>
