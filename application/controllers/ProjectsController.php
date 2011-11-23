<?php

class ProjectsController extends Zend_Controller_Action
{
	protected $_user;
	
    public function init()
    {
		/* Initialize action controller here */
		$this->view->user = $this->_user = Anta_Core::authorizeOwner();
		
		/* create a dock */
		$this->view->dock = new Ui_Dock();
		
    }
	
	public function useAction(){
		
		$database =  $this->_request->getParam( 'database' );
		

		# check project in user		

		# clone current identity
		$alterEgo = $this->_user;
		
		# modify project based upon desired project name		
		$alterEgo->username = $database; //"solairemed";

		# clear identity
		Zend_Auth::getInstance()->clearIdentity();
		
		# setup new identity		
		Zend_Auth::getInstance()->getStorage()->write( $alterEgo );
		
		# forward to project index page
		$this->render( 'list' );
	}	
	
    public function listAction()
    {
        // action body
		// yust a form
		$this->view->dock->addCraft(
			new Ui_Crafts_Cargo( "projects", I18n_Json::get( "your projects" ) ) );
		$results = Application_Model_ProjectsMapper::fetchAll( $this->_user );
		
		$this->view->dock->projects->addItem( new Anta_Ui_Item_Project( new Application_Model_Project( array(
			"id"    => "master",
			"title" => $this->_user->realname,
			"description" => $this->_user->name,
			"database" =>  $this->_user->username
		))));
		foreach( array_keys( $results->results ) as $k ){
			$this->view->dock->projects->addItem( new Anta_Ui_Item_Project(
					$results->results[$k]	
			));
		}
		// print_r( $results );
    }

	/**
	 * Handle user param error. Return the user if is valid.
	 * If the user is not provided or is not valid, exit with json error
	 */
	public function createAction()
    {
		// yust a form
		$this->view->dock->addCraft( new Ui_Craft( "create-project", I18n_Json::get( "start a new project" ) ) );
		$this->view->dock->create_project->setCreateForm( new Ui_Forms_AddProject( 
			"create-project", 
			I18n_Json::get( "create" ), 
			ANTA_URL."/projects/create/" 
		));
        // action body
		$this->render( 'list' );
    }

}

