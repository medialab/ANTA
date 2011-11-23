<?php
/**
 * @package Application_Model
 */

/**
 * anta user.*_entities_documents table api. Return generic objects
 */
class Application_Model_SubEntitiesDocumentsMapper{
	
	/**
	 * return a list of Sentence object
	 */
	public static function getSentences( Application_Model_User $antaUser, $filters ){
		$stmt = Anta_Core::mysqli()->query(
			"SELECT SQL_CALC_FOUND_ROWS *, id_sentence as id, s.id_document, s.content, s.position, d.date, d.title
				FROM  anta_{$antaUser->username}.`sentences` s JOIN anta_{$antaUser->username}.documents d USING (id_document)
				WHERE  `content` LIKE ?
			ORDER BY ".implode(",", $filters->order)." LIMIT {$filters->offset}, {$filters->limit}",
			array( "%".$filters->query."%" )
		);
		
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = new Application_Model_Sentence( $row->id, $row->content, $row->id_document, $row->position, $row->date, $row->title );
		}
		
		$stmt = Anta_Core::mysqli()->query(" SELECT  FOUND_ROWS() as totalItems" );
		$totalItems = $stmt->fetchObject()->totalItems ;
		return (object) array( "results"=> $results, "totalItems" => $totalItems) ;
		
	}
	
	
	public static function getEntities( Application_Model_User $antaUser, $idDocument, $orders = array(), $offset=0, $limit=20 ){
		
		$prefixes = Anta_Core::getEntitiesPrefixes();
		
		$unions = array();
		
		foreach( $prefixes as $prefix ){
				
				$unions[] = "
					SELECT	'{$prefix}' as prefix,
						id_{$prefix}_entity as identifier,
						content,
						anta_{$antaUser->username}.{$prefix}_entities_documents.relevance,
						anta_{$antaUser->username}.{$prefix}_entities_documents.frequency
					FROM anta_{$antaUser->username}.{$prefix}_entities JOIN anta_{$antaUser->username}.{$prefix}_entities_documents
						USING ( id_{$prefix}_entity )
					WHERE pid = 0 AND `ignore` = 0 AND id_document = '{$idDocument}'
					GROUP BY id_{$prefix}_entity";
				
			}
			
			// prepare the order values
			$validOrderBy = array( "prefix ASC", "prefix DESC", "frequency ASC", "frequency DESC", "relevance ASC", "relevance DESC", "sign ASC", "sign DESC" );
			
			if( empty( $orders ) ) $orders = array ( "relevance DESC" );
			
			$orderBy = array();
		
			foreach( $orders as $order ){
				if ( in_array( $order, $validOrderBy ) ){
					$orderBy[] = $order;
				}
			}
			
			$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ( (". implode( ") UNION (", $unions ). ") ) as filtered
			ORDER BY ".implode( ", ", $orderBy ).
			( $limit != -1? " LIMIT {$offset}, {$limit}": "" );
			
			// query execution
			$stmt = Anta_Core::mysqli()->query( $query );
		
			$results = array();
			
			while( $row = $stmt->fetchObject() ){
				$results[ $row->prefix."_".$row->identifier ]  = new Application_Model_SubEntity(
					$row->prefix."_".$row->identifier,
					$row->identifier,
					$row->content,
					$row->prefix, $row->relevance,
					$row->frequency ,
					$row->pid ,
					$antaUser->cryptoId,
					$flags['ignore']
				);
				
			};
			
			// load results
			$stmt = Anta_Core::mysqli()->query(" SELECT  FOUND_ROWS() as totalItems" );
			$totalItems = $stmt->fetchObject()->totalItems ;
			
			// get tags
			foreach( array_keys( $results ) as $k ){
				$results[ $k ]->tags = Application_Model_SubEntitiesTagsMapper::getEntityTags( $antaUser, $results[ $k ]->prefix, $results[ $k ]->table_id  );
			}
			
			return (object) array( "results" => $results, "totalItems"=>$totalItems);
			
			
	}
	/**
	 *   return a list of object
	 *   {d, e, r}
	 */
	public static function getLinks(  Application_Model_User $antaUser, $prefixes = array(), $minRelevance = 0, $maxRelevance = 0, $minFrequency = 0, $maxFrequency = 0, $flush=false){
		if( empty( $prefixes ) ){
			$prefixes = array( 'ngr', 'rws' );
		}
		
		// filter by min / max relevance
		$relevance = $maxRelevance > 0? " AND relevance BETWEEN {$minRelevance} AND {$maxRelevance}": "";
		
		// filter by frequency as sub query
		$frequency = $maxFrequency > 0;
		
		// get entities documents. super entities documents 
		foreach( $prefixes as $prefix ){
			$frequencyClause = $frequency? "
			AND id_{$prefix}_entity IN ( SELECT   id_{$prefix}_entity  FROM anta_{$antaUser->username}.`{$prefix}_entities_documents`  GROUP BY id_{$prefix}_entity HAVING COUNT( DISTINCT `id_document` ) BETWEEN {$minFrequency} AND {$maxFrequency} )
			": "";
			
			$unions[] = "
				SELECT '{$prefix}' as prefix, `id_{$prefix}_entity` as id, relevance, id_document, content
				  FROM anta_{$antaUser->username}.{$prefix}_entities_documents JOIN anta_{$antaUser->username}.`{$prefix}_entities`
				USING (id_{$prefix}_entity)
				WHERE
					pid = 0 AND `ignore` = 0 {$relevance} {$frequencyClause}";
		}
		
		
		$query = "(". implode( ") UNION (", $unions ). ")";
		
		// exit ($query);
		
		// filter result set by numberof documents
		$stmt = Anta_Core::mysqli()->query( $query );
		
		if( $flush ) return $stmt;
		
		// echo $query;exit;
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[]  = (object) array(
				"e" => $row->prefix. "_". $row->id,
				"d" => $row->id_document,
				"r" => $row->relevance
			);
			
			
			
		}
		// echo $query;
		
		return $results;
		
		
	}
	
}
