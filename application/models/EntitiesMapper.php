<?php
/**
 * @package
 */

/**
 * anta user.entities table api. This table contains all entities found into the user's document.
 * This mapper handle entitites table and documents_entitites as well.
 */
class Application_Model_EntitiesMapper{
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query(
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`entities` (
				`id_entity` int(11) NOT NULL AUTO_INCREMENT,
				`content` varchar(100) NOT NULL,
				`type` varchar(100) NOT NULL,
				`language` varchar(2) NOT NULL,
				`pid_entity` int(11) NOT NULL COMMENT 'entity parent id',
				`status` varchar(2) NOT NULL DEFAULT 'zz' COMMENT '[ zz | xx | cc ] ( zz:quiescent, xx:deleted, cc:created )',
				PRIMARY KEY (`id_entity`),
				UNIQUE KEY `content` (`content`),
				KEY `status` (`status`)
			) ENGINE=INNODB DEFAULT CHARSET=utf8"
		);
		
	}
	
	/**
	 * Add an entry into the users's entities table. Entities are ngrams or expression extracted by various services.
	 * The function returns the id_entity inserted id ( an integer )
	 *
	 * @param antaUser	- the Application_Model_User instance of an user (db user)
	 * @param content	- the entity
	 * @param language	- the language used to stem the word ( document language )
	 * @param status	- DEFAULT 'zz' COMMENT '[ zz | xx | cc ] ( zz:quiescent, xx:deleted, cc:manually created )
	 * @return the id_entity inserted into user's terms
	 */
	public static function addEntity( Application_Model_User $antaUser, $content, $language, $status = 'zz' ){
		// echo $content.";";
		
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO  anta_".$antaUser->username.".`entities` (
				`content`, `type`, `language`, `status`
			) VALUES (
				LOWER( ? ), ?, ?, ?
			)",array( $content, '', $language, $status )
		);
		
		$idTerm = Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".`entities`" );
		
		if ( $idTerm != 0 || $duplicateWarning ) return $idTerm;
		
		return self::getIdEntity( $antaUser, $content, $language );
		
	}

	public static function modifyEntity( Application_Model_User $antaUser, $idEntity, $content ){
		$stmt = Anta_Core::mysqli()->query( "
			UPDATE anta_".$antaUser->username.".`entities` set content = LOWER( ? ) WHERE id_entity=?",
			array( $content, $id_entity )
		);
		return $stmt->rowCount();
	} 

	public static function suggest( Application_Model_User $antaUser, $name ){
		$stmt = Anta_Core::mysqli()->query("SELECT content FROM anta_".$antaUser->username.".entities WHERE LOWER( content ) LIKE ? ORDER BY content LIMIT 10", array(
			strtolower( $name )."%"
		));
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = $row->content;
		}
		
		return $results;
	}

	public function getThings( Application_Model_User $antaUser ){
		
		$stmt = Anta_Core::mysqli()->query( "
			SELECT id_entity, content, entities.group, entities_occurrences.type, entities_occurrences.relevance, id_document
				FROM anta_".$antaUser->username.".entities_occurrences
				JOIN anta_".$antaUser->username.".entities 
			USING ( id_entity ) ORDER BY content, relevance ASC" );
			
		$results = array();
		
		// stemming and validation
		/** array of thing object */
		$things = array();
		
		while( $row = $stmt->fetchObject() ){
			
			$words = explode ( " " ,  $row->content);
			
			// stem words by dictionary
			$index = stem( $words[0] );
			
			if( !isset( $things[ $index ] ) ){
				$things[ $index ] = new Application_Model_Thing( $index );
			}
			
			// is a common stem??? like "the"
			
			if( !isset( $results[ $row->id_entity ] ) ){
				$results[ $row->id_entity ] = new Application_Model_Entity( $row->id_entity, $row->content, $row->group, $row->type, $row->relevance );
				// get first word
				//$words = explode ( " " ,  $row->content);
				// echo $index;
				// print_r($words);
				// add the entry, as *reference*
				$things[ $index ]->addEntity( $results [ $row->id_entity ] );
			}
			
			// add entity relevance
			$results[ $row->id_entity ]->addRelevance( $row->relevance );
			
			// add last entity
			$results[ $row->id_entity ]->addDocument( $row->id_document );
		
			
		}
		
		return $things;
	}
	
	public static $validOrderBy = array(
			"occurrences ASC", "occurrences DESC", 
			"content ASC", "content DESC",
			"type ASC", "type DESC",
			"avg_relevance ASC", "avg_relevance DESC",
			"min_relevance ASC", "min_relevance DESC",
			"max_relevance ASC", "max_relevance DESC",
			"spread ASC", "spread DESC"
		);
	
	/**
	 * return a list of entities (where id_entity differs )
	 * every entity presents:
	 * - occurrences, which counts the number of id_entity in the whole corpus
	 * - spread,      the number of distinc id_document where the id_entity appears
	 * - avg_relevance, the average relevance of entity usage into the whole corpus
	 * - min_relevance and max_relevance
	 * 
	 * @param antaUser	- a valid database user with an anta database
	 * @param orders	- array, optional, where values are "field DIR" strings
	 * */
	public function getEntities( Application_Model_User $antaUser, array $orders = array(), $offset=0, $limit=100, $search="" ){
		// filter the order values
		if( empty( $orders ) ) $orders = array ("spread DESC", "avg_relevance ASC" );
		
		$orderBy = array();
		
		foreach( $orders as $order ){
			if ( in_array( $order, self::$validOrderBy ) ){
				$orderBy[] = $order;
			}
		}
		
		// evaluate query
		$searchQuery =  empty( $search )? '': "WHERE content LIKE '%".mysql_escape_string( $search )."%'";
		
		
		
		$query = "
			SELECT id_entity,
				count( id_entity ) AS occurrences,
				count( distinct id_document ) as spread,
				content,
				pid_entity,
				group_concat( distinct anta_".$antaUser->username.".entities.type) as type, 
				AVG( anta_".$antaUser->username.".entities_occurrences.relevance ) as avg_relevance,
				MIN( anta_".$antaUser->username.".entities_occurrences.relevance ) as min_relevance,
				MAX( anta_".$antaUser->username.".entities_occurrences.relevance ) as max_relevance
			FROM anta_".$antaUser->username.".entities_occurrences JOIN anta_".$antaUser->username.".entities 
				USING ( id_entity )
			
			{$searchQuery}
			GROUP BY id_entity
			ORDER BY ".implode( ", ", $orderBy ).
			( $limit != -1? " LIMIT {$offset}, {$limit}": ""
		);
		
		$stmt = Anta_Core::mysqli()->query( $query );
		
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$buffer = new Application_Model_Entity( $row->id_entity, $row->content, $row->type, $row->avg_relevance, $row->pid_entity );
			
			$buffer->minRelevance = $row->min_relevance;
			$buffer->maxRelevance = $row->max_relevance;
			$buffer->spread       = $row->spread;
			$buffer->occurrences  = $row->occurrences;
			
			$results[] = $buffer;
			
		}
		
		return $results;
		
	}
	
	
	public static function countEntities( Application_Model_User $antaUser, $search = ""){
		
		// evaluate query
		$searchQuery =  empty( $search )? '': "WHERE content LIKE '%".mysql_escape_string( $search )."%'";
		
		$stmt = Anta_Core::mysqli()->query( "
			SELECT count( DISTINCT id_entity) as amount
				FROM anta_".$antaUser->username.".entities_occurrences JOIN anta_".$antaUser->username.".entities
			USING ( id_entity )
			{$searchQuery}
			"
		);
		
		$row = $stmt->fetchObject();
		
		return $row->amount;
	}
	
	public static function getEntity( Application_Model_User $antaUser, $idEntity ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT id_entity, content, pid_entity,
			    count( id_entity ) AS occurrences,
				count( distinct id_document ) as spread,
				group_concat( distinct anta_".$antaUser->username.".entities_occurrences.type) as type, 
				AVG( anta_".$antaUser->username.".entities_occurrences.relevance ) as avg_relevance,
				MIN( anta_".$antaUser->username.".entities_occurrences.relevance ) as min_relevance,
				MAX( anta_".$antaUser->username.".entities_occurrences.relevance ) as max_relevance
			FROM anta_".$antaUser->username.".entities_occurrences JOIN anta_".$antaUser->username.".entities 
				USING ( id_entity )
			WHERE id_entity = ?
			GROUP BY id_entity
			", array(
				$idEntity
		));
		
		$row = $stmt->fetchObject();
		
		if( $row == null ) return null;
		$entity =  new Application_Model_Entity( $row->id_entity, $row->content, $row->type, $row->avg_relevance, $row->pid_entity );
			
		$entity->minRelevance = $row->min_relevance;
		$entity->maxRelevance = $row->max_relevance;
		$entity->spread       = $row->spread;
		$entity->occurrences  = $row->occurrences;
			
		
		return $entity;
	}
	
	/**
	 * 
	 */
	public static function getIdEntity(  Application_Model_User $antaUser, $content, $language ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT id_entity
				FROM anta_".$antaUser->username.".`entities`
			WHERE content = LOWER( ? ) and language = ?", array(
				$content, $language
		));
		
		$row = $stmt->fetchObject();
		
		if( $row == null ) return 0;
		
		return $row->id_entity;
		
	}
	
	public static function addCoOccurrence( Application_Model_User $antaUser, $idDocument, $idSentence, $idTermA, $idTermB ){
		$stmt = Anta_Core::mysqli()->query( "
			INSERT INTO  anta_".$antaUser->username.".`co_occurrences` (
				`id_document`, `id_sentence`,
				`id_term_A`, `id_term_B`
			) VALUES (
				?, ?, ?, ?
			)", array( $idDocument, $idSentence, $idTermA, $idTermB )
		);
		return $stmt->rowCount();
	}
	
	public static function clearEntities( Application_Model_User $antaUser ){
		$stmt = Anta_Core::mysqli()->query( "
			TRUNCATE TABLE anta_".$antaUser->username.".`documents_entities`
		");
		
		$stmt = Anta_Core::mysqli()->query( "
			TRUNCATE TABLE anta_".$antaUser->username.".`entities_occurrences`
		");
		
		$stmt = Anta_Core::mysqli()->query( "
			TRUNCATE TABLE  anta_".$antaUser->username.".`entities`
		");
		
		return $stmt->rowCount();
	}
	
}
