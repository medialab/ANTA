<?php
/**
 * @package Anta_Distiller
 */
/**
 * index using builtin Zend_Lucene. todo
 */
class Anta_Distiller_Lucene extends Anta_Distiller_ThreadHandler{
	
	public function init(){
		$document =& $this->_target;
		$user =& $this->_distiller->user;
	}
	
}
?>