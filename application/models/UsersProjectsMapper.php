<?php

class Application_Model_UsersProjectsMapper
{
	
	public static function save( Application_Model_User $antaUser, Application_Model_Project $project ){
		$stmt = Anta_Core::mysqli()->query("
			INSERT INTO users_projects( `id_user`, `id_project` ) values( ?, ? )", array(
			$antaUser->id, $project->id
		));
		return $stmt->rowCount( "users_projects" );
	}

}

