<?php
/**
 * @package Ui_Items_Item
 */
 
/**
 * describe an entity Application_Model_Entity
 *
 */
 class Application_Model_Ui_Items_Entity extends Application_Model_Ui_Items_Item{
	/**
	 * the bound Application_Model_Entity instance to be displayed */
	public $entity;
	
	/**
	 * Class constructor
	 */
	public function __construct( Application_Model_Entity $entity ){
		
		$this->entity = $entity;
		parent::__construct( $entity->id );
	}
	
	public function __toString(){
		
		return '
		
		<div class="grid_24 alpha omega item">
		  <div class="grid_1 alpha identifier">'.$this->entity->id.'</div>
		  <div class="grid_6 entity-content" id="entity_'.$this->entity->id.'">'.$this->entity->content.'</div>
		  <div class="grid_4">'.$this->entity->type.'</div>
		  <div class="grid_2">'.$this->entity->occurrences.'</div>
		  <div class="grid_2">'.$this->entity->spread.'</div>
		  <div class="grid_2 ">'.number_format($this->entity->relevance, 2, '.','').'</div>
		  <div class="grid_2">'.number_format($this->entity->minRelevance, 2, '.','').'</div>
		  <div class="grid_2">'.number_format($this->entity->maxRelevance, 2, '.','').'</div>
		  <div class="grid_3 omega">
		    <div class="grid_1 alpha">'.$this->entity->pid.'<img src="'.Anta_Core::getBase().'/images/merge.png" class="tip-helper" title="'.I18n_Json::get('find similar looking').'"></div>
		    <div class="grid_1"><img src="'.Anta_Core::getBase().'/images/layers-stack.png" class="tip-helper" title="split into components"></div>
		    <div class="grid_1 omega"><img src="'.Anta_Core::getBase().'/images/cross-small.png" class="tip-helper" title="delete - send to bin"></div>
		  </div>
		</div>';
	}
 }
?>
