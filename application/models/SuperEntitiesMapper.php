<?php
/**
 * @package Application_Model
 */

/**
 * mapper for super_entities table
 */
class Application_Model_SuperEntitiesMapper{

	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query(
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`super_entities` (
				`id_super_entity` int(11) NOT NULL AUTO_INCREMENT,
				`pid` int(11) NOT NULL COMMENT 'parent id into this table',
				`content` text,
				`sign` varchar(200),
				`ignore` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'flag entities to be ignored',
				PRIMARY KEY (`id_super_entity`),
				UNIQUE KEY `sign` (`sign`),
				KEY `ignore` (`ignore`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
		);
	}
	
	/** returns a super entity i.e a subEntity with a phylogeny
	 * default: no limits at all
	 */
	public static function getEntity( Application_Model_User $antaUser, $id, $orderBy = array(), $offset = 0, $limit = -1 ){
		
		$prefixes =  Anta_Core::getEntitiesPrefixes();
		$unions   =  array();
		$binds    =  array();
		
		// select label and lots of stuff
		$stmt = Anta_Core::mysqli()->query( 
			"SELECT `id_super_entity`, `content`, `pid`, `ignore` FROM anta_{$antaUser->username}.super_entities WHERE id_super_entity = ? LIMIT 1", array( $id )
		);
		
		// the execution
		$row = $stmt->fetchObject();
		// load entity
		$superEntity = new Application_Model_SuperEntity(
				"super_".$row->id_super_entity,
				$row->id_super_entity,
				$row->content,
				$row->prefix,
				-1,
				-1,
				$row->pid ,
				$antaUser->cryptoId,
				$row->ignore
		);
		
		// union between various services
		foreach( $prefixes as $prefix ){
			$binds[] = $id;
			$unions[] = "SELECT '{$prefix}' as prefix, id_{$prefix}_entity as identifier, content, COUNT( id_document ) as frequency, AVG(relevance) as relevance FROM 
				anta_{$antaUser->username}.`{$prefix}_entities` NATURAL JOIN anta_{$antaUser->username}.`{$prefix}_entities_documents` 
				WHERE `pid` = ? AND `ignore` = 0 GROUP BY id_{$prefix}_entity ";
		}
		
		// prepare and check ordrers
		$validOrderBy = array( "content ASC", "content DESC", "relevance ASC", "relevance DESC" );
		$orderBy = array_intersect( $orderBy, $validOrderBy );
		if( empty( $orderBy ) ) $orderBy = array ( "relevance DESC" );
		
		// the query, without SQL_CALC_FOUND_ROWS ...
		$query = "(". implode( ") UNION (", $unions ). ") 
			ORDER BY ".implode( ", ", $orderBy ).
			( $limit != -1? " LIMIT {$offset}, {$limit}": "" );
		
		// the execution
		$stmt = Anta_Core::mysqli()->query( $query, $binds );
		
		while( $row = $stmt->fetchObject() ){
			$superEntity->addChild( new Application_Model_SubEntity(
				$row->prefix."_".$row->identifier,
				$row->identifier,
				$row->content,
				$row->prefix, $row->relevance,
				$row->frequency,
				$row->pid ,
				$antaUser->cryptoId,
				$row->ignore
			));
		}
		/* get tags: all the tags coming from the children+ super_tags (todo) */
		return $superEntity;
		
	}
	
	public static function addEntity( Application_Model_User $antaUser, $content ){
		
		Anta_Core::mysqli()->query(
			"INSERT INTO anta_".$antaUser->username.".`super_entities` (
				`id_super_entity`, `pid`, `content`, `sign`
			) VALUES (
				NULL, 0, ?, LOWER( ? )
			) ON DUPLICATE KEY UPDATE id_super_entity  = LAST_INSERT_ID( id_super_entity );", array( $content, Anta_Core::translit( $content ) )
		);
		
		return  Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".`super_entity`" );
		
	}
	
	public static function setPid(){
		
	}
	
	
}

?>
