<?php
/**
 * @package
 */

/**
 * anta user.entities table api. This table contains all entities found into the user's document.
 */
class Application_Model_EntitiesOccurrencesMapper{

	public static function install( $username ){
	
		$stmt = Anta_Core::mysqli()->query(
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`entities_occurrences` (
				`id_entity_occurrence` int(11) NOT NULL AUTO_INCREMENT,
				`id_entity` int(11) NOT NULL,
				`id_document` int(11) NOT NULL,
				`type` varchar(50),
				`relevance` float NOT NULL,
				`service` varchar(2),
				PRIMARY KEY (`id_entity_occurrence`),
				INDEX `id_entity` (`id_entity`,`id_document`),
				FOREIGN KEY ( `id_document` ) REFERENCES anta_".$username.".`documents`( id_document )
                      ON DELETE CASCADE,
				FOREIGN KEY ( `id_entity` ) REFERENCES anta_".$username.".`entities`( id_entity )
                      ON DELETE CASCADE	
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
		);
	}
	
	/**
	 * add a entity-document relationship between
	 * document and its keyword
	 */
	public static function add( Application_Model_User $antaUser, $idDocument, $idEntity, $type='', $relevance='', $service='' ){
		
		Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO  anta_".$antaUser->username.".`entities_occurrences` (
				`id_document`, `id_entity`, `type`, `relevance`, `service`
			) VALUES ( ?, ?, ?, ?, ? )", array( $idDocument, $idEntity,$type, $relevance, $service ) );
		
		
			
		return Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".`entities_occurrences`" );
	}
	
	public static function getEntities( Application_Model_User $antaUser, $idDocument, $orderBy = "avg_relevance DESC", $offset=0, $limit=20 ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT SQL_CALC_FOUND_ROWS *,
				en.id_entity, en.content,
				occ.type, occ.frequency, occ.relevance as avg_relevance
			FROM anta_".$antaUser->username.".entities en JOIN (
				SELECT COUNT(*) as frequency,
					id_entity, AVG( relevance ) as relevance,
					GROUP_CONCAT( DISTINCT type ORDER BY type ASC SEPARATOR ', ') as type
				FROM anta_".$antaUser->username.".`entities_occurrences`
				WHERE id_document = ?
				GROUP BY (id_entity)
			) as occ
			USING (id_entity)
			ORDER BY  $orderBy LIMIT $offset, $limit", array( $idDocument )
		);
		
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = new Application_Model_Entity( $row->id_entity, $row->content, $row->type, $row->avg_relevance );
		}
		
		$stmt = Anta_Core::mysqli()->query( " SELECT FOUND_ROWS() as count ");
		
		return (object) array( 'info'=>$stmt->fetchObject()->count, 'results'=>$results);
	}
}
?>