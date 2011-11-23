<?php
/**
 * @ package Ui
 */

/**
 * specific class for left menus for anta framework.
 * 
 */
class Ui_Boards_TodaySpecial extends Ui_Board {
	
	public $user;
	
	public $document;
	
	public function init( $properties ){
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		
		// initialize menu items
		$this->items[ "edit.props" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/edit/props/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			I18n_Json::get( 'overview' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		$this->items[ "edit.download" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/edit/download/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'. Anta_Core::getBase() .'/images/tags.png">'. I18n_Json::get( 'download original' ),
			array( 
				'class' => 'alpha-li omega-li' 
			)
		);
		
		/*$this->items[ "tags.add" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/tags/add/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'. Anta_Core::getBase() .'/images/tags.png">'. I18n_Json::get( 'create custom tag' ),
			array( 
				'class' => 'alpha-li omega-li' 
			)
		);*/
		
		$this->items[ "edit.export-tags" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/edit/export-tags/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId ,
			'<img src="'. Anta_Core::getBase() .'/images/document-export.png">'. I18n_Json::get( 'export tags' ),
			array( 
				'class' => 'alpha-li' 
			)
		);
		
		$this->items[ "edit.import-tags" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/edit/import-tags/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'.Anta_Core::getBase() .'/images/document-import.png">'. I18n_Json::get( 'import tags' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		$this->items[ "co-occurrences.match-against" ] = new Ui_Boards_TodaySpecials_Item(
			 Anta_Core::getBase() .'/co-occurrences/match-against/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'.Anta_Core::getBase() .'/images/co-occurrence-match.png">'. I18n_Json::get( 'match against' ),
			array( 
				'class' => 'alpha-li' 
			)
		);
		
		$this->items[ "co-occurrences.list" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/co-occurrences/list/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'.Anta_Core::getBase() .'/images/co-occurrences.png">'. I18n_Json::get( 'compare words' )
			
		);
		
		$this->_autoSelectItem();
	}
	
	public function removeItem( $id ){
		$ids = func_get_args();
		foreach( $ids as $id ){
			unset( $this->items[$id] );
		}
		
	}
	
	protected function _autoSelectItem(){
		// get controller and action
		$front = Zend_Controller_Front::getInstance();
		$selected = $front->getRequest()->getParam( 'controller' ) . "." . $front->getRequest()->getParam( 'action' );
		
		if( isset( $this->items[ $selected ] ) ){
			$this->items[ $selected ] -> select();
		}
	}
	
	public $items = array();
	
	protected function _getCompletion(){
		return '
		<script type="text/javascript" src="'.ANTA_URL.'/js/jquery.ping.js"></script>
		<script type="text/javascript">
			var completion_ping;
			$(window).load( function(){
				completion_ping = new ping({
					url:"'.ANTA_URL.'/api/read-log/user/'.$this->user->cryptoId.'",
					clearTimeoutOnError:false,
					
					success:function(result){
						if( result.status != "ok" ){
							return;	
						} else if( result.routine == "died" ){
							$(".routine-completion .routine-bar").css({"background":"red"})
							
							return;	
						}
						
						var percentage = ( Math.round( result.completion.coeff.indexed * 1000 ) / 10 ) + "%";
						
						$(".routine-completion .routine-bar").css({
							"background":"url( \"'.ANTA_URL.'/images/working.gif\" ) #5ab5de",
							"width": percentage
						});
						
						
						$(".routine-completion .routine-completion-percentage").text( percentage );
					},
					start:function(message){console.log("start",message)}
				});
			});
		</script>
		<style>
			.routine-completion { width:110px; padding: 5px; border-radius:2px; background: url("'.ANTA_URL.'/images/bg_item_grid.png"); height:27px; border:1px solid #bdbdbd; margin-top: 10px; text-align:left}
			.routine-completion p{ margin:3px 0px 7px}
			.routine-completion .routine-status{ width:16px;}
			.routine-completion .routine-bar-box{  float:left;  width:70px;margin-left: 0px;}
			.routine-completion .routine-completion-percentage{  float:left;width: 40px;text-align:right }
			.routine-completion .routine-bar-background{ width:70px; margin-top:3px; background:white; height: 5px; border:1px solid #bdbdbd }
			.routine-completion .routine-bar{ padding:0px; float:none; height:5px; background:#5ab5de; margin-left:0px; }
		</style>
		<div class="routine-completion">
			<p><a href="'.ANTA_URL.'/thread/index/user/'.$this->user->cryptoId.'" title="'.I18n_Json::get("analysis details").'" class="tip-helper">'.I18n_Json::get("analysis").'</a></p>
			<div class="routine-bar-box">
				<div class="routine-bar-background">
					<div class="routine-bar" style="width:5%"></div>
				</div>
			</div>
			<div class="routine-completion-percentage">
				...
			</div>
			
		</div>
		';
	}
		
	public function __toString(){
		
		return '
		<div id="today-special-menu">
			<h2>today specials</h2>
			<ul>
				'.implode( $this->items ).'
				
			</ul>
			'.$this->_getCompletion().'
		</div>
		';
	}

}
