<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an graph Application_Model_Graph
 *
 */
 class Ui_Crafts_Items_Graph extends Ui_Crafts_Item{
	/**
	 * the bound Application_Model_Entity instance to be displayed */
	public $graph;
	
	public $user;
	
	/**
	 * Class constructor
	 */
	public function __construct( Application_Model_Graph $graph, Application_Model_User $user ){
		$this->graph = $graph;
		$this->user = $user;
		parent::__construct( $graph->id );
	}
	
	public function __toString(){
		
		
		
		return '
		 
		<div class="grid_24 alpha omega item" id="gr_'.$this->graph->id.'">
			<div class="grid_1 alpha centered" >&nbsp <!--<input type="checkbox">--></div>
			<div class="grid_1" id="gr_'.$this->graph->id.'_id">'.$this->graph->id.'</div>
			<div class="grid_3" id="gr_'.$this->graph->id.'_date">'.$this->graph->date.'</div>
			<div class="grid_4" id="gr_'.$this->graph->id.'_desc">'.$this->graph->description.'</div>
			<div class="grid_6" id="gr_'.$this->graph->id.'_error">'.$this->graph->error.'</div>
			<div class="grid_3" id="gr_'.$this->graph->id.'_localUrl">'.$this->user->cryptoId.'</div>
			
		</div>';
	}
}
?>