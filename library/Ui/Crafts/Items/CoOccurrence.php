<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Ui_Crafts_Items_CoOccurrence extends Ui_Crafts_Item {
	/**
	 * a qury raw object instance to be displayed */
	public $comparisonResult;
	
	public $termA;
	public $termB;
	
	public static $uniqueId = 0;
	
	/**
	 * Class constructor
	 */
	public function __construct( $comparisonResult, $termA, $termB ){
		$this->comparisonResult =& $comparisonResult;
		$this->termA =& $termA;
		$this->termB =& $termB;
		
		parent::__construct( "c".self::$uniqueId++ );
		
	}
	
	public function __toString(){
		
		if( $this->comparisonResult->diff == 0){
			return '
			<div class="grid_24 alpha omega item">
				<div class="grid_1 alpha centered">'.$this->comparisonResult->diff.'</div>
				<div class="grid_3">'.$this->termA.' + '.$this->termB.'</div>
				<div class="grid_20 omega">'.$this->enlighten( $this->comparisonResult->c1 ).'</div>
			</div>';
		}
		$content =  $this->comparisonResult->diff < 0? 
			$this->comparisonResult->c2 . "<br/>".$this->comparisonResult->c1:
			$this->comparisonResult->c1 . "<br/>".$this->comparisonResult->c2;

		
		return '
		<div class="grid_24 alpha omega item">
			<div class="grid_1 alpha centenred ">'.$this->comparisonResult->diff.'</div>
			<div class="grid_3">'.$this->termA.' + '.$this->termB.'</div>
			<div class="grid_20 omega ">'.
				$this->enlighten( 
					$content 
				). '
			</div>
		</div>';
		
	}
	private function enlighten( $string ){
		return str_ireplace( 
					array( $this->termA, $this->termB ), 
					array("<strong>".$this->termA."</strong>", "<strong>".$this->termB."</strong>"  ), 
					$string 
		);
	}
 }