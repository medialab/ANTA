<?php
/**
 * @ package Ui
 */

/**
 * specific class for left menus for anta framework.
 * controller: threadController
 * 
 */
class Ui_Boards_Analysis extends Ui_Boards_TodaySpecial {

	public function init( $properties ){
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		
		// initialize menu items
		$this->items[ "thread.index" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/thread/index/user/'.  $this->user->cryptoId,
			
			I18n_Json::get( 'analysis panel' ),
			array( 
				'id'    => "thread.index"
			)
		);
		
		$this->items[ "logs.read" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/logs/read/user/'.  $this->user->cryptoId,
			
			'<img src="'. Anta_Core::getBase() .'/images/clear-monitor.png">'. I18n_Json::get( 'read full log' ),
			array( 
				'class' => 'alpha-li',
				'id'    => "logs.read"
			)
		);
		
		$this->items[ "api.read-log" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/api/clean-log/user/'.  $this->user->cryptoId,
			
			'<img src="'. Anta_Core::getBase() .'/images/monitor.png">'. I18n_Json::get( 'clean log' ),
			array( 
				'class' => 'omega-li ajax-href',
				'id'    => "api.read-log",
				'title' => I18n_Json::get( 'clean log' )
			)
		);
		
		$this->items[ "api.reset" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/api/reset-documents-status/user/'.  $this->user->cryptoId,
			I18n_Json::get( 'set all docs ready' ),
			array( 
				'class' => 'alpha-li ajax-href',
				'id' => "api.reset",
				'title' => I18n_Json::get( 'set all docs ready' )
			)
		);
		
		$this->_autoSelectItem();
	}
}
?>