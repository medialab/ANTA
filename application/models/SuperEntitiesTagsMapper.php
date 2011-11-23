<?php

class Application_Model_SuperEntitiesTagsMapper
{
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query(
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`super_entities_tags` (
				`id_super_entity` int(11) NOT NULL,
				`id_tag` int(11) NOT NULL,
				PRIMARY KEY (`id_super_entity`,`id_tag`),
				KEY `id_tag` (`id_tag`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);
	}

}

?>