<?php

class Application_Model_DocumentsProjectsMapper
{
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`documents_projects` (
				`id_document` int(11) NOT NULL,
				`id_project` int(11) NOT NULL,
				PRIMARY KEY (`id_document`,`id_project`),
				KEY `id_project` (`id_project`),
				FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`)
					ON DELETE CASCADE,
				FOREIGN KEY (`id_document`) REFERENCES `documents` (`id_document`)
					ON DELETE CASCADE
			) ENGINE=InnoDB"
		);
	}
	
}
?>

