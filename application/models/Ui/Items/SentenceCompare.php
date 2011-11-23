<?php
/**
 * @package Ui_Items
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Items_SentenceCompare extends Application_Model_Ui_Items_Item{
	/**
	 * a qury raw object instance to be displayed */
	public $sentenceCompare;
	
	public $wordA;
	public $wordB;
	
	public static $uniqueId = 0;
	
	/**
	 * Class constructor
	 */
	public function __construct( $sentenceCompare, $wordA, $wordB ){
		
		$this->sentenceCompare = $sentenceCompare;
		$this->wordA =& $wordA;
		$this->wordB =& $wordB;
		parent::__construct( "r".self::$uniqueId++ );
	}
	
	public function __toString(){
		
		if( $this->sentenceCompare->diff == 0){
			return '
			<div class="grid_24 alpha omega item">
				<div class="grid_1 alpha centered">'.$this->sentenceCompare->diff.'</div>
				<div class="grid_22 omega">'.$this->enlighten( $this->sentenceCompare->c1 ).'</div>
			</div>';
		}
		
		$content = "";
		if( $this->sentenceCompare->diff < 0 ){
			$content = $this->sentenceCompare->c2 . "[...]".$this->sentenceCompare->c1;
		} else {
			$content = $this->sentenceCompare->c1 . "[...]".$this->sentenceCompare->c2;
		}
		
		return '
		<div class="grid_24 alpha omega item">
			<div class="grid_1 alpha centenred ">'.$this->sentenceCompare->diff.'</div>
			<div class="grid_22 omega ">'.
				$this->enlighten( 
					$content 
				). '
			</div>
		</div>';
	}
	
	public function enlighten( $string ){
		return str_ireplace( 
					array( $this->wordA, $this->wordB ), 
					array("<strong>".$this->wordA."</strong>", "<strong>".$this->wordB."</strong>"  ), 
					$string 
		);
	}
 }