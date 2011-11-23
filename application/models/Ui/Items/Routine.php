<?php
/**
 * @package Ui_Items
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Items_Routine extends Application_Model_Ui_Items_Item{
	
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
		<style>
		#routine-bar-box{
			float:none;
			display:block;
			padding-top:5px;
			width:700px
		}
		#routine-completion-percentage{
			font-size:12pt;
			text-align:center;
			
		}
		#routine-completion-documents div{
			float:none;
			display:inline;
			padding: 5px;
			
		}
		#routine-completion-documents  span{
			font-size:10pt;
			font-weight:bold
		}
		#routine-bar-background{
			float:none;
			display:block;
			height:5px;
			background:#fefefe;
			width:778px;
			border:1px solid #dedede;
		}
		.routine-bar{
			float:left;display:block;
			height:5px;border:0px;margin:0px;padding:0px;
			border-right:1px solid #fff;
			
		}
		
		</style>
		<div id="routine-engine" class="grid_22 prefix_1 suffix_1 alpha omega margin_1">
			<div class="grid_22 alpha omega">
				<div class="grid_2 alpha">
					<img id="routine-engine-icon" src="'.Anta_Core::getBase().'/images/loading.gif" alt="loading" />
				</div>
				<div class="grid_20 omega" id="routine-description">loading...</div>
			</div>
			<div class="grid_2 alpha margin_1" ><p  id="routine-completion-percentage">0%</p></div>
			<div class="grid_20 omega margin_1" id="routine-completion-documents">
				<div ><span id="routine-completion-indexed">0</span> documents indexed</div>
				<div><span id="routine-completion-error">0</span> errors</div>
				<div><span id="routine-completion-total">0</span> total</div>
			</div>
			<div class="grid_22 alpha omega" id="routine-completion">
				<div id="routine-bar-box">
					<div id="routine-bar-background">
						<div class="routine-bar" id="routine-bar-indexed" style="background:#3F3F3F;width:220px"></div>
						<div class="routine-bar" id="routine-bar-error" style="background:orange;width:20px"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="grid_22 prefix_1 suffix_1 alpha omega margin_1">
			<!-- <input type="text"value="15" id="log-tail-lines" class="width_2" maxlength="4"/> -->
		</div>
		';
		return '
		  <button id="start-engine">'.I18n_Json::get("checking status...").'</button>
		  <div class="grid_7 prefix_1 alpha" style="text-align:center"><div class="ajax-routine" id="'.$this->user->cryptoId.'"><img src="'.Anta_Core::getBase().'/images/loading.gif" alt="loading" /></div></div>
		  <div class="grid_7 prefix_1"  style="text-align:center"><a href="'.Anta_Core::getBase().'/logs/read/user/'.$this->user->cryptoId.'" class="tip-helper" title="'.I18n_Json::get('readUserLog').'"><img src="'.Anta_Core::getBase().'/images/clear-monitor.png"/></a></div>
		  <div class="grid_7 prefix_1 omega"  style="text-align:center"><a href="'.Anta_Core::getBase().'/logs/clear/user/'.$this->user->cryptoId.'" class="tip-helper"  title="'.I18n_Json::get('clearUserLog').'"><img src="'.Anta_Core::getBase().'/images/monitor.png"/></a></div>
		</div>
		';
	}
}
 
?>
