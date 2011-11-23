<?php

class Application_Model_ProjectsTagsMapper
{
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query(
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`projects_tags` (
				`id_project` int(11) NOT NULL,
				`id_tag` int(11) NOT NULL,
				PRIMARY KEY (`id_project`,`id_tag`),
				KEY `id_tag` (`id_tag`),
				FOREIGN KEY ( `id_project` ) REFERENCES anta_".$username.".`projects`( id_project )
                    ON DELETE CASCADE,
				FOREIGN KEY ( `id_tag` ) REFERENCES anta_".$username.".`tags`( id_tag )
                    ON DELETE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);
	}
	
	public static function add( Application_Model_User $antaUser, $idProject, $idTag ){
		
	}

}

