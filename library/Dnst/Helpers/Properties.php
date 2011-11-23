<?php
class Dnst_Helpers_Properties{
	
	public $LIMIT = -1;
	public $OFFSET = 0;
	public $ORDER_BY = array();
	public $WHERE  = array();
	public $binds  = array();
	
	public function __construct( $LIMIT = 20, $OFFSET = 0, $ORDER_BY = array(), $WHERE = array(), $binds = array() ){
		$this->LIMIT = $LIMIT;
		$this->OFFSET = $OFFSET;
		$this->ORDER_BY = $ORDER_BY;
		$this->WHERE = $WHERE;
		$this->binds = $binds;
	}
	
	public function getOrderByClause(){
		return !empty(  $this->ORDER_BY )? " ORDER BY ".implode( ", ", $this->ORDER_BY ): "";
	}
	
	public function getLimitClause(){
		return $this->LIMIT != -1? " LIMIT {$this->OFFSET}, {$this->LIMIT}": "";
	}
	
	public function getWhereClause(){
		return !empty(  $this->WHERE )? " WHERE ".implode( "AND", $this->WHERE ): "";
	}
	
	public function __toString(){
		return $this->getWhereClause().$this->getOrderByClause().$this->getLimitClause();
	}
}

?>