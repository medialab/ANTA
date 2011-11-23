<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an entity Application_Model_Entity
 *
 */
 class Ui_Crafts_Items_Entity extends Ui_Crafts_Item{
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
		
		$tags = "";
		// load tags
		foreach( array_keys( $this->entity->tags ) as $k ){
			
			$tags .= '<span  class="is-untouchable-tag" id="'.$this->entity->id.'_tag_'.$this->entity->tags[ $k ]->id.'"><a class="tip-helper" title="'.I18n_Json::get('use this tag to filter selection').'" href="?'.
				Dnst_Filter::prependProperties( array( "tags"=>array($this->entity->tags[ $k ]->id), "offset"=>0 ) ).
					'">'.$this->entity->tags[ $k ]->content.'</a><img class="tag-icon tip-helper detach-tag"  src="'.Anta_Core::getBase().'/images/cross-small-grey.png" title="'.I18n_Json::get('remove tag permanently').'"></span>';
		}
		
		return '
		 
		<li class="grid_24 alpha omega item" id="se_'.$this->entity->id.'">
		  <div class="grid_1 alpha centered" ><input type="checkbox" id="seen_'.$this->entity->id.'" class="multi-selectable" style="margin-left: 5px"></div>
		  <!--<div class="grid_1 identifier"> '.$this->entity->id.'</div>-->
		  <div class="grid_2" style="height:16px">
		    <a class="ui-selected-ignore tip-helper"
				title="'.I18n_Json::get( 'view entity presence and statistics' ).'"
				href="'.Anta_Core::getBase().'/entity/'.( $this->entity->prefix == 'super'? 'super-view': 'view').'/user/'.$this->entity->userCryptoId.'/prefix/'.$this->entity->prefix.'/id/'.$this->entity->id.'">
			    <img src="'.Anta_Core::getBase().'/images/page-find.png" class="view-entity" style="display:none"  id="moe_'.$this->entity->id.'"></a>
			<a class="ui-selected-ignore tip-helper view-entity" title="'.I18n_Json::get( 'search similar entities' ).'" style="display:none"
				href="?'.Dnst_Filter::prependProperties( array( "query"=>$this->entity->content, "offset"=>0 )).'">
				<img src="'.Anta_Core::getBase().'/images/wand-small.png">
			</a>
			<a class="ui-selected-ignore view-entity" href="#" style="display:none">
			'.( $this->entity->ignore == 0 ?
				'<img src="'.Anta_Core::getBase().'/images/cross-small.png" class="modify-ignore tip-helper" id="igno_'.$this->entity->id.'" title="'.I18n_Json::get('exclude entity').'">'
				: 
				'<img  src="'.Anta_Core::getBase().'/images/magnet-small.png" id="dese_'.$this->entity->id.'" class="modify-undo-ignore tip-helper view-entity" title="'.I18n_Json::get('include entity').'">'
		    ).'
			</a>
		  </div>
		  <div class="grid_18">
		    <span class="entity-content '.( $this->entity->ignore == 0?'':'excluded tip-helper').'" id="entity_'.$this->entity->id.'" '.( $this->entity->ignore == 0?'':'title="'.I18n_Json::get('this entity is not included into graph views').'"').'>
			'.$this->entity->content.'</span><span class="entity-tags">'.$tags.'</span>
		  </div> 
		  
		  <!--<div class="grid_2">'.$this->entity->prefix.'&nbsp;</div>-->
		  <div class="grid_1 centered">'.$this->entity->frequency.'</div>
		  <div class="grid_1 centered">'.$this->entity->relevance.'</div>
		
		  
		</li>';
		
		/**
		 * <div class="grid_1 alpha"><img src="'.Anta_Core::getBase().'/images/merge.png" class="tip-helper" title="'.I18n_Json::get('find similar looking').'"></div>
		    <div class="grid_1"><img src="'.Anta_Core::getBase().'/images/layers-stack.png" class="tip-helper" title="split into components"></div>
		    * <div class="grid_1 omega"><img src="'.Anta_Core::getBase().'/images/cross-small.png" class="tip-helper" title="'.I18n_Json::get('delete permanently').'"></div>*/
			
	}
 }
?>
