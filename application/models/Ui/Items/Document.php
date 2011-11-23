<?php
/**
 * @package Dnst_Ui_Items_Item
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Items_Document extends Application_Model_Ui_Items_Item{
	
	/**
	 * the bound Application_Model_Document instance to be displayed */
	public $document;
	
	/**
	 * Class constructor
	 */
	public function __construct( Application_Model_Document $document ){
		
		$this->document = $document;
		parent::__construct( $document->id );
	}
	
	protected function _formatAuthors(){
		$authors = "";
		foreach (array_keys( $this->document->tags ) as $k ){
			$authors .= '<a href="?'.Dnst_Filter::setProperty( "query", array( "tag" => $this->document->tags[ $k ]->id ) ).'" title="filter results by '.$this->document->tags[ $k ]->category.':'.$this->document->tags[ $k ]->content.'">'.$this->document->tags[ $k ]->content.'</a>';
		}
		return $authors;
	}
	
	public function __toString(){
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		
		return '
		<div class="grid_24 alpha omega item" id="doc_'.$this->document->cryptoId.'">
		  <div class="grid_1 alpha centered" ><input type="checkbox" id="sedo_'.$this->document->cryptoId.'" class="multi-selectable" style="margin-left: 5px"></div>
		  <div class="grid_2 " id="mido_'.$this->document->cryptoId.'">'.basename( $this->document->mimeType).'</div>
		  <div class="grid_2" id="lado_'.$this->document->cryptoId.'">'.I18n_Json::get( $this->document->language ).'</div>
		  <div class="grid_10 item-title '.($identity->id != $this->document->owner->id? 'admin':'').'">
			<a href="'.Anta_Core::getBase().'/edit/props/document/'.$this->document->cryptoId.'/user/'.$this->document->owner->cryptoId.'" title="view and modify file">'.
				wordwrap( $this->document->title, 30, " ", true ).'</a>
			<p><span class="document-tags">'.$this->_formatAuthors().'</span></p>
		  </div>
		   <div class="grid_2 " ><img id="igdo_'.$this->document->cryptoId.'" src="'.Anta_Core::getBase().'/images/'.($this->document->ignore==0? 'tick.png': 'block.png').'"></div>
		  
		  <div class="grid_3">'.$this->document->date.'</div>
		  <div class="grid_3 omega document-status" id="'.$this->document->cryptoId.'">'.$this->document->status.'</div>
		  <!--<div class="grid_1 omega"><a href="'.Anta_Core::getBase().'/documents/remove/user/'.$this->document->owner->cryptoId.'/doc/'.$this->document->cryptoId.'"><img src="'.Anta_Core::getBase().'/images/cross-small.png"/></a></div>-->
		</div>';
	}
}
 
?>
