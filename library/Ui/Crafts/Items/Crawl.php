<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an graph Application_Model_Graph
 *
 */
 class Ui_Crafts_Items_Crawl extends Ui_Crafts_Item{
	/**
	 * the bound Application_Model_Entity instance to be displayed */
	public $crawl;
	
	public $user;
	
	/**
	 * Class constructor
	 */
	public function __construct( Application_Model_Crawl $crawl ){
		$this->crawl = $crawl;
		parent::__construct( $crawl->id_crawl );
	}
	
	public function __toString(){
		
		
		
		return '
		 
		<div class="grid_24 alpha omega item" id="gr_'.$this->crawl->id.'">
			<div class="grid_1 alpha centered" >&nbsp <!--<input type="checkbox">--></div>
			<div class="grid_1" id="gr_'.$this->crawl->id.'_id">'.$this->crawl->id_crawl.'</div>
			<div class="grid_6" id="gr_'.$this->crawl->id.'_date">'.$this->crawl->start_words.'</div>
			<div class="grid_4" id="gr_'.$this->crawl->id.'_desc">'.$this->crawl->creation_date.'</div>
			<div class="grid_4" id="gr_'.$this->crawl->id.'_desc">'.$this->_link().'</div>
		</div>';
	}
	
	public function _link(){
		return'
			<a href="'.Anta_Core::getBase().'/api/crawl-download/id/'.$this->crawl->id_crawl.'">
				gexf
			</a>
		';
	}
}
?>