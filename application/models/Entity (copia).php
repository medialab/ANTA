<?php
/**
 * @package
 */

/**
 * Describe an Entity
 */
class Application_Model_Entity{

	public $id;
	public $content;
	public $group;
	public $type;
	public $relevance;
	public $language;
	public $service;
	
	/** an array of documents */
	public $documents = array();
	
	protected $_coOccurrences;
	
	protected $_spread;
	
	public function __construct( $id, $content, $group, $type, $relevance, $language = 'en', $service='nd' ){
		$this->id        = $id;
		$this->content   = $content;
		$this->group     = $group; 
		$this->type      =  $type;
		$this->relevance =  $relevance;
		$this->language  =  $language;
		$this->service   = $service;
	}
	
	public function addDocument( $idDocument ){
		if( !isset( $this->documents[ $idDocument ] ) ){
			$this->documents[ $idDocument ] = 1;
		}
		$this->documents[ $idDocument ]++;
	}
	
	public function getOccurrences(){
		if ( $this->_coOccurrences == null ){
			$this->_coOccurrences = array_sum( $this->documents );
		}
		return $this->_coOccurrences;
	}
	
	public function addRelevance( $relevance ){
		$this->relevance += $relevance;
		$this->relevance /= 2;
	}
	
	public function getSpread(){
		if ( $this->_spread == null ){
			$this->_spread = count( $this->documents );
		}
		return $this->_spread;
	}
	
	public function toCsvString(){
		return
			$this->id.               ";" .
			$this->content.          ";" .	
			$this->type.          	 ";" .	
			$this->getSpread().      ";" .
			$this->getOccurrences(). ";" .
			$this->relevance.        ";" ;
	}
	
	public static function getCsvHeaders(){
		return 
			"id".";".
			"content".";".
			"type".";".
			"spread (n. docs)".";".
			"occurrences".";".
			"max relevance".";";
	}
}
