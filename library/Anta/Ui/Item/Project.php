<?php
/**
 * @package Anta_Ui_Item
 */
 
/**
 * prepare and visualize a single Project from anta.projects table in a list of projects
 */
class Anta_Ui_Item_Project extends Ui_Crafts_Item{
	/**
	 * the bound  Anta_Ui_Item_Project instance to be displayed */ 
	public $project;

	protected $_selected = false;	
	
	/**
	 * Class constructor
	 */
	public function __construct( Application_Model_Project $project ){
		$this->project = $project;
		parent::__construct( $project->id );
	}

	public function setSelected( $selected = true ){
		$this->_selected = $selected;
	}

	public function __toString(){
		return '
		<div class="item grid_24 alpha omega" id="doc_'.$this->project->id.'">

			<div class="item-id grid_1 alpha">&nbsp;</div>
			<div class="item-title grid_4">'.( $this->_selected?
				 '<strong>'.$this->project->title.'</strong>'
				:'<a href="'.ANTA_URL.'/projects/use/database/'.$this->project->id.'">'.$this->project->title.'</a>'
						
				).'
			</div>
			<div class="item-title grid_4">'.$this->project->database.'</div>
		</div>
		';
	}

}
