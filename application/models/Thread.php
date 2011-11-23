<?php
/**
 *@package Anta
 */

/**
 * Describe a Thread ( a separate analysis process, I really don't know if it's a reasl thread)
 * @author Daniele Guido <gui.daniele@gmail.com>
 */
class Application_Model_Thread{
	
	public $id;
	
	/** id of the given routine, thread parent */
	public $routine;
	
	
	/**
	 * type of analysis process, aka AL (alckeny) | OC (openCalais) | GS (genericitè / specificitè, @mathieu jacomy) | IN (indexation, stemming) 
	 * @var string
	 */
	public $type;
	
	/**
	 * cardinal order of the routine
	 */
	public $order;
	
	public $status;
	
	/**
	 * Class constructor
	 */
	public function __construct( $id, $routine, $type, $order, $status ){
		$this->id = $id;
		$this->routine = $routine;
		$this->type = $type;
		$this->order = $order;
		$this->status = $status;
		
	}
	
}
