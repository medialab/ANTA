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
		
		$idDatabase =  $this->_request->getParam( 'database' );
		
		# database master
		if( $idDatabase == "master" ){

			$database =empty( $alterEgo->origin )? $this->_user->username: $alterEgo->origin;

		} else {

			# is number?		
			if( !is_numeric( $idDatabase ) )
				throw new Zend_Exception( I18n_Json::get("project id should have a numeric value") );

			# check project in user		
			$stmt = Anta_Core::mysqli()->query( "
				SELECT `database` FROM `projects` 
				INNER JOIN `users_projects`
				USING( `id_project` ) 
				WHERE `id_project` = ? AND `id_user` = ?
				", array( $idDatabase, $this->_user->id )
			);
		
			$database = $stmt->fetchObject()->database;		

			if( empty( $database ) )
				throw new Zend_Exception( I18n_Json::get("project not found or the authenticated user is not authorized") );

		}


		
		# clone current identity
		$alterEgo = $this->_user;
		
		# add the origin database
		$alterEgo->origin = $alterEgo->username ;

		# modify project based upon desired project name		
		$alterEgo->username = $database; //"solairemed";
		
		# clear identity
		Zend_Auth::getInstance()->clearIdentity();
		
		# setup new identity		
		Zend_Auth::getInstance()->getStorage()->write( $alterEgo );
		
		# forward to project index page
		$this->_forward( 'list' );
	}	
	
    public function listAction()
    {
        // action body
		// yust a form
		$this->view->dock->addCraft(
			new Ui_Crafts_Cargo( "projects", I18n_Json::get( "your projects" ) ) );
		$results = Application_Model_ProjectsMapper::fetchAll( $this->_user );
		
		$master = $this->view->dock->projects->addItem( new Anta_Ui_Item_Project( new Application_Model_Project( array(
			"id"    => "master",
			"title" => $this->_user->realname,
			"description" => $this->_user->name,
			"database" =>  $this->_user->username
		))));
		
		# is selected?
		// $master->setSelected();

		foreach( array_keys( $results->results ) as $k ){
			$item = $this->view->dock->projects->addItem( new Anta_Ui_Item_Project(
					$results->results[$k]	
			));
			if( !empty( $this->_user->origin ) && ( $this->_user->username == $item->project->database ))
				$item->setSelected();
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
		$form =  $this->view->dock->create_project->setCreateForm( new Ui_Forms_AddProject( 
			"create-project", 
			I18n_Json::get( "create" ), 
			ANTA_URL."/projects/create/" 
		));
		
		if( $this->_request->isPost() ){
			$result = Anta_Core::validateForm( $form );;
			// print_r( $result );
			if ($result === true ){
				// add a project
				
				Anta_Core::addProject( $this->_user, new Application_Model_Project( array(
					"title" =>$form->project_title->getValue(),
					"description" =>$form->project_description->getValue() 
				)));
			}
		}

        // action body
		$this->_forward( 'list' );
    }

}

