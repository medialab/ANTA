+<?php
/**
 * @package
 */

/**
 * Describe a group of entities, a thing
 */
class Application_Model_Thing{
	public $id;
	
	public $entities;
	
	/** please use getRelevance() to get the actual relevance average */
	protected $_relevance;
	
	public function __construct( $id ){
		$this->entities = array();
		$this->id = $id;
	}
	
	public function addEntity( &$entity ){
		
		$this->entities[] =& $entity;
	}
	
	public function getRelevance(){
		if ( $this->_relevance != 0 ) return $this->_relevance;
		
		foreach( array_keys( $this->entities ) as $k ){
			 $this->_relevance += $this->entities[$k]->relevance;
		}
		$this->_relevance = $this->_relevance / count( $this->entities );
		return  $this->_relevance;
	}
	
	/**
	 * Return the most used label (or the first) between all the entities
	 */
	public function getLabel(){
		$label = reset( $this->entities );
		if( $label === false ) return $id;
		return $label->content;
	}
	
	public function getSpread(){
		$spread = 0;
		foreach( array_keys( $this->entities ) as $k ){
			$spread += $this->entities[$k]->getSpread();
		}
		return $spread;
	}
}
?>
