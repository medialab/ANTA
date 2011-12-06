<?php
/**
 * This controller show user folder content. Can be accessed by owner only.
 * 
 */
class CrawlsController extends Zend_Controller_Action
{
    /** the Application_MOdel_User instance. Files will be added to his folder */
	protected $_user;

	
    public function init()
    {
		$this->_user = Anta_Core::authorizeOwner();	
		$this->view->dock = new Ui_Dock();
    }
	public function indexAction(){
		$this->_forward( 'list' );
		
	}
	public function listAction(){
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 
			'google', I18n_Json::get( 'your google crawls' ).": ".$this->_user->username 
		));
		
		$results = Application_Model_CrawlsMapper::select( $this->_user );
		// print_r($results);
		foreach( array_keys( $results ) as $k ){
			$crawl =& $results[ $k ];
			$this->view->dock->google->addItem( 
				new Ui_Crafts_Items_Crawl( $crawl )
			);
		}
	}
	
	
}