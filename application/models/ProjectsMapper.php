<?php

class Application_Model_ProjectsMapper
{

	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query(
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`projects` (
				`id_project` int(11) NOT NULL AUTO_INCREMENT,
				`title` int(11) NOT NULL COMMENT 'project title',
				`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`last_modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY (`id_project`),
				UNIQUE KEY `title` (`title`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);
	}
	
	public static function save( Application_Model_Project $project ){
		
		$stmt = Anta_Core::mysqli()->query("
			INSERT INTO projects( title, description, database ) values( ?, ?, ? )", array(
			$project->title, $project->description,  $project->database 
		));
		$project->id = Anta_Core::mysqli()->lastInsertId( "anta.projects" );		

		return empty( $project->id )? null: $project;
	}	

	public static function fetchAll( Application_Model_User $user, $filters = array() ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT SQL_CALC_FOUND_ROWS *, projects.* FROM projects JOIN users_projects
			USING( id_project ) WHERE id_user = ?", array(
			$user->id
		));
		$results = array() ;
		while( $row = $stmt->fetchObject()  ){
			$results[] = Application_Model_Project::load( $row ); 			
		}
		$stmt = Anta_Core::mysqli()->query(" SELECT FOUND_ROWS() as totalItems" );
		$totalItems = $stmt->fetchObject()->totalItems;

		return (object) array( "results"=> $results, "totalItems" => $totalItems);
	}

	public static function addProject( Application_Model_User $antaUser, $title ){
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO anta_".$antaUser->username.".projects (
				title, last_modification_date, 
			) VALUES (
				?, ?
			)", array( $title, Anta_Core::getCurrentTimestamp() )
		);
		return Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".projects" );
	}

}

