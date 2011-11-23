<?php

class PrepareController extends Zend_Controller_Action
{
	protected $_user;
	
    public function init()
    {
		$this->_user = Anta_Core::authorizeOwner();	
		
    }

	public function orderAction(){
		
		if( $this->_request->isPost() ) {
			
			//$py = new Py_Scriptify( "zendify.py ".$this->_user->id, false );
			//$py->silently();
			$graphId = Application_Model_GraphsMapper::addGraph( $this->_user, new Application_Model_Graph(
				0, 'tina'
			));
			
			if( $graphId == 0 ){
				Anta_Core::setError( I18n_Json::get('graph not added correctly','errors') );
				return $this->visualizationAction();
			}
			
			// launch the process, in background
			// echo "zendify.py ".$this->_user->id;
				
			$py = new Py_Scriptify( "zendify.py make_graph ".$this->_user->id. " ".$graphId, false );
			$py->silently();
			echo $py->command;
			Anta_Core::setMessage( I18n_Json::get('graph added correctly') );
			
		}
		return $this->visualizationAction();
		
	}
	
	public function standardGraphAction(){
		if( $this->_request->isPost() ) {
			$graphId = Application_Model_GraphsMapper::addGraph( $this->_user, new Application_Model_Graph(
				0, 'ngram'
			));
			
			if( $graphId == 0 ){
				Anta_Core::setError( I18n_Json::get('graph not added correctly','errors') );
				return $this->visualizationAction();
			}
		}
		
		return $this->visualizationAction();
	}
	
    public function visualizationAction()
    {
		// inject user into view
        $this->view->user = $this->_user;
		$this->view->prefix = "rws";
        // action body
		$this->view->dock = new Ui_Dock();
		
		$this->view->dock->addCraft( new Ui_Crafts_Cargo( 'filters', I18n_Json::get( 'filter-entities' ) ) );
		
		
		// the header:lens selector with huge numbers and visualization button
		$this->view->dock->filters->setHeader(
			new Ui_Crafts_Headers_Prepare()
		);
		
		// sample listener
		$this->render( 'index' );
    }


}

