<?php
/**
 * @package Anta_Frog
 */
 
class Anta_Frog_Matches{
	
	public $documents;
	public $sentences;
	public $categories;
	public $totalItems = 0;
	
	public function __construct(){
		$this->documents = array();
		$this->sentences = array();
		$this->categories = array();
	}
	
}
?>