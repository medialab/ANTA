<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an graph Application_Model_Graph
 *
 */
 class Anta_Ui_Item_Graph extends Ui_Crafts_Item{
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
			<div class="item-id grid_1" id="gr_'.$this->graph->id.'_id">'.$this->graph->id.'</div>
			<div class="item-date grid_3" id="gr_'.$this->graph->id.'_date">'.$this->graph->date.'</div>
			<div class="item-description grid_4" id="gr_'.$this->graph->id.'_desc">'.$this->graph->description.'</div>
			<div class="item-error grid_3" id="gr_'.$this->graph->id.'_error">'.$this->graph->error.'</div>
			<div class="item-preview grid_3"><a href="'.Anta_Core::getBase().'/visualize/sigma/user/'.$this->user->cryptoId.'/graph/'.$this->graph->id.'">preview</a></div>
			<div class="grid_3" id="gr_'.$this->graph->id.'_localUrl">'.$this->user->cryptoId.'</div>
			
		</div>';
	}
}
?>