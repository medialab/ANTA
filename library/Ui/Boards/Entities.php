<?php
/**
 * @ package Ui
 */

/**
 * specific class for left menus for anta framework.
 * 
 */
class Ui_Boards_Entities extends Ui_Boards_TodaySpecial {

	public function init( $properties ){
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		
		$this->items[ "entities.list" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/entities/list/user/'.  $this->user->cryptoId ,
			'<img src="'. Anta_Core::getBase() .'/images/entities.png">'. I18n_Json::get( 'all entities' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		// initialize menu items
		$this->items[ "entities.included" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/entities/included/user/'.  $this->user->cryptoId ,
			'<img src="'. Anta_Core::getBase() .'/images/entities.png">'. I18n_Json::get( 'included entities' ),
			array( 
				'class' => 'alpha-li' 
			)
		);
		
		$this->items[ "entities.excluded" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/entities/excluded/user/'.  $this->user->cryptoId ,
			'<img src="'. Anta_Core::getBase() .'/images/ignored-entities.png">'. I18n_Json::get( 'excluded entities' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		$this->items[ "entities.csv" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/entities/csv/user/'.  $this->user->cryptoId ,
			I18n_Json::get( 'export all entities' ),
			array( 
				'class' => 'alpha-li' 
			)
		);
		/*
		$this->items[ "entities.csv-accepted" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/entities/csv/user/'.  $this->user->cryptoId."?accepted-only=true" ,
			I18n_Json::get( 'export all accepted entities' ),
			array( 
				'class' => 'alpha-li' 
			)
		);
		
		$import = I18n_Json::get( 'import entities' );
		
		$this->items[ "entities.csv-import" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/entities/csv-import/user/'.  $this->user->cryptoId,
			$import
		);
		*/
		$this->_autoSelectItem();
	}
	
	
}