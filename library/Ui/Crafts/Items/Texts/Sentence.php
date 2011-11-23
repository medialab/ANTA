<?php
/**
 * @package Ui_Crafts_Items_Texts
 */
 
/**
 * describe a sentence
 *
 */
class Ui_Crafts_Items_Texts_Sentence extends Ui_Crafts_Items_Text{
	
	private $user;
	
	public function __construct( Application_Model_Sentence $sentence, $terms = array(), $user ){
		parent::__construct( "s_".$sentence->id );
		$this->sentence = $sentence;
		$this->terms = $terms;
		$this->substitution = array();
		$this->user = &$user;
		foreach( $this->terms as $t ){
			$this->substitution[] = '<span style="background:yellow">'.$t.'</span>';
		}
	}
 
	public function __toString(){
		
		$document = Anta_Utils_Href_Document::create( $this->user->cryptoId, $this->sentence->documentCryptoId, $this->clean( $this->sentence->title ) );
		
		$date = new Zend_Date( $this->sentence->date, "yyyy-MM-dd HH:mm:ss");
		
		return '
		<div class="sentence grid_22 prefix_2 alpha omega margin_1" style="border-bottom:1px solid #dbdbdb; padding-bottom:6px;">
			<div class="sentence-date grid_21 alpha omega" style="color:#A0A0A0;">'.$date->toString('dd MMM YYYY').'</div>
			<div class="sentence-content grid_21 alpha omega">'.$this->enlighten( $this->sentence->content ).'</div>
			<div class="sentence-title grid_21 alpha omega" style="color:#A0A0A0; padding-top:3px">'. $document.'</div>
		</div>';
		
	}
	
	public function clean( $str ){
		return str_replace("_"," ", $str );
	}
	
	public function enlighten( $str ){
		return str_ireplace( $this->terms, $this->substitution, $str );
	}
} 
 ?>
