<?php
/**
 * @package
 */

/**
 * Describe an Entity
 */
class Application_Model_Entity{

	public $id;
	
	/** parent id entity */
	public $pid;
	
	/** the text content */
	public $content;
	
	/** the type(s) */
	public $type;
	
	public $relevance;
	public $minRelevance;
	public $maxRelevance;
	
	public $occurrences;
	public $spread;
	
	public $tags;
	
	public function __construct( $id, $content, $type, $relevance, $pid = 0){
		$this->id        = $id;
		$this->content   = $content;
		$this->type      = $type;
		$this->relevance = $relevance;
		$this->pid       = $pid;
	}
	
	public function toCsvString(){
		return
			$this->id.          ";" .
			$this->content.     ";" .	
			$this->type.        ";" .	
			$this->spread.      ";" .
			$this->occurrences. ";" .
			$this->relevance.   ";" .
			$this->minRelevance.   ";" .
			$this->maxRelevance.   ";" ;
	}
	
	public static function getCsvHeaders(){
		return 
			"id".";".
			"content".";".
			"type".";".
			"spread (n. docs)".";".
			"occurrences".";".
			"avg relevance".";".
			"min relevance".";".
			"max relevance".";";
	}
	
}
