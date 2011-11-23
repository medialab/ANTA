<?php
class Application_Model_Rws extends Application_Model_Entity {
	
	/**
	 * the service, AL for alchemiApi and OC for openCalais
	 *@var string
	 */
	public $service;
	
	public function __construct( $id, $content, $relevance, $service, $pid ){
		
		parent::__construct( $id, $content, "rws", $relevance, $pid );
		$this->service = $service;
		
	}
	
}
?>