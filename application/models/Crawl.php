<?php
/**
 * a crawl bject
 */
class Application_Model_Crawl{
	
	public $id_crawl;
	public $start_words;
	public $request_urls;
	public $creation_date;
	public $status;
	
	public function __construct( $properties = array() ){
		foreach ( $properties as $k => $v ){
			$this->$k = $v;	
		}
	}
}
?>