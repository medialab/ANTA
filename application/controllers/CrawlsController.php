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
	
	public function listAction(){
		
		$this->view->dock->addCraft( new Application_Model_Ui_Crafts_Cargo( 
			'google', I18n_Json::get( 'your google crawls' ).": ".$this->_user->username 
		));
		
	}
}