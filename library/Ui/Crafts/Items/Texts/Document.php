<?php
/**
 * @package Ui_Crafts_Items_Texts
 */
 
/**
 * describe a sentence
 *
 */
class Ui_Crafts_Items_Texts_Document extends Ui_Crafts_Items_Text{
	
	
	public $document;
	
	public $tags;
	
	public $amountOfSentences;
	
	public function __construct( Application_Model_Document $document, $tags, $properties=array() ){
		parent::__construct( "d_".$document->id );
		
		foreach( $properties as $key=>$value){
			$this->$key = $value;
		}
		
		
		$this->document = $document;
		$this->tags = $tags;
	}
 
	public function __toString(){
		
		$documentTags = "";
		
		foreach( $this->tags as $tag ){
			$documentTags .='<span class="is-tag" style="padding-top:1px; padding-right: 3px">'.$tag->content.'</span>';
		}
		
		return '
		<div class="grid_22 prefix_1 alpha omega margin_1" style="text-shadow:1px 1px white">
			<div class="grid_22 alpha omega">
			  <span style="font-size:1.5em"><a href="'.anta_Core::getBase().'/edit/props/document/'.$this->document->cryptoId.'/user/'.$this->document->owner->cryptoId.'">'.$this->document->title.'</a></span> </div>
			<div class="grid_21 alpha omega" style="padding-top:5px;color:#999;"><span style="color:#a0a0a0">'.str_replace("/",".",$this->document->date).'</span></div>
			<div class="grid_21 alpha omega" style="padding:3px;color:#111;">
			  '.$documentTags.'
			</div>
			<div class="grid_22 alpha omega" >'.$this->score.'<!--'.$this->amountOfSentences.' '.I18n_Json::get( $this->amountOfSentences > 1? "sentences": "sentence").'--></div>
		</div>';
		
	}
	
	public function enlighten( $str ){
		return str_ireplace( $this->terms, $this->substitution, $str );
	}
} 
 ?>
