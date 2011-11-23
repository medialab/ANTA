<?php

class Anta_Gexf_Edge{
	
	public static $uniqueId = 0;
	
	/** unique id */
	public $id;
	
	/** target */
	public $t;
	
	/** source */
	public $s;
	
	/** weight */
	public $w;
	
	function __construct( $source, $target, $weight = 1, $id = -1  ){
	
		$this->id = "e".( $id == -1? self::$uniqueId++: $id );
		
		$this->s  = $source;
		$this->t  = $target;
		$this->w  = $weight;
		
	}
	
	function __toString(){
		return '<edge id="'.$this->id.'"  source="'.$this->s.'" target="'.$this->t.'" weight="'.$this->w.'"/>';
	}
}
?>