<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Ui_Crafts_Items_MatchAgainst extends Ui_Crafts_Item {
	/**
	 * a qury raw object instance to be displayed */
	public $word;
	
	public static $maxFrequency;
	public static $minFrequency;
	
	public static $uniqueId = 0;
	
	/**
	 * Class constructor
	 */
	public function __construct( $word, $maxFrequency, $minFrequency = 1 ){
		
		$this->word = $word;
		
		self::$maxFrequency = $maxFrequency;
		self::$minFrequency = $minFrequency;
		
		parent::__construct( "r".self::$uniqueId++ );
	}
	
	public function __toString(){
		
		$df = self::$maxFrequency - self::$minFrequency;
		$df = $df != 0? $df: 1;
		
		$size = ( 4 * ($this->word->frequency - self::$minFrequency) / $df ) + 1;
		
		return '
			<a class="match-against-item" href="?'.Dnst_Filter::setProperty( 'offset', 0 ).'" title="'.$this->word->term.'">
				<em style="line-height: 1em; font-size: '.$size.'em">'.$this->word->label.'</em> {'.$this->word->frequency.'}
			</a>';
		
	}
	
 }