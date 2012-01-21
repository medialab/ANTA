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
		 
		<div class="grid_24 alpha omega item" id="gr_'.$this->crawl->id_crawl.'">
			<!--<div class="grid_1 alpha centered" >&nbsp <input type="checkbox"></div>-->
			<div class="grid_1 prefix_1 alpha centered crawl-action"><img onclick="crawls.remove( '.$this->crawl->id_crawl.')" src="'.ANTA_URL.'/images/cross-small.png" /></div>
			
			<div class="grid_1" class="crawl-id" id="gr_'.$this->crawl->id_crawl.'_id">'.$this->crawl->id_crawl.'</div>
			<div class="grid_6" class="crawl-query" id="gr_'.$this->crawl->id_crawl.'_date">'.$this->crawl->start_words.'</div>
			<div class="grid_4" class="crawl-creation-date" id="gr_'.$this->crawl->id_crawl.'_desc">'.$this->crawl->creation_date.'</div>
			<div class="grid_4" class="crawl-status" id="gr_'.$this->crawl->id_crawl.'_status">'.$this->crawl->status.'</div>
			<div class="grid_4" class="crawl-link" id="gr_'.$this->crawl->id_crawl.'_desc">'.$this->_link().'</div>
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