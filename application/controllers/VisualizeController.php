<?php

class VisualizeController extends Zend_Controller_Action
{
	protected $_user;
	
    public function init()
    {
		$this->_user = Anta_Core::getAuthorizedUser(  Dnst_Crypto_SillyCipher::decrypt( $this->_request->getParam( 'user' ) ) );
		
    }

    public function gexfAction()
    {
		$this->view->user = $this->_user;
		$this->view->prefix = "rws";
		$this->view->dock = new Ui_Dock();
        // action body
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'graphs', I18n_Json::get( 'exported graphs' ) ) );
		
		
		// load graphs
		$graphs = Application_Model_GraphsMapper::getGraphs( $this->_user);
		
		// add graph
		foreach( array_keys( $graphs->results ) as $k ){
			$this->view->dock->graphs->addItem( new Anta_Ui_Item_Graph(  $graphs->results [ $k ], $this->view->user ) );
		} 
    }
	
	public function sigmaAction(){
		$this->view->user = $this->_user;
		$this->view->prefix = "rws";
		$this->view->dock = new Ui_Dock();
		
		$swf = new Anta_Ui_Craft_Swf( 'sigma-preview', I18n_Json::get( 'preview graphs' ) );
		$swf->url = Anta_Core::getBase() .'/sigma.swf?configPath='.Anta_Core::getBase().'/sigma/config.json';
		
		$this->view->dock->addCraft( $swf );
		
	}

}

