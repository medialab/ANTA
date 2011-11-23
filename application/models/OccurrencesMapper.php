<?php
/**
 * Map the table sentences
 */
class Application_Model_OccurrencesMapper {

	public static function install($username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`occurrences` (
				`id_occurrence` int(11) NOT NULL AUTO_INCREMENT,
				`id_document` int(11) NOT NULL,
				`id_sentence` int(11) NOT NULL,
				`stem` varchar(50) NOT NULL,
				`word` varchar(50) NOT NULL,
				PRIMARY KEY (`id_occurrence`),
				KEY `id_sentence` (`id_sentence`,`stem`,`word`),
				KEY ( `id_document` ),
				FOREIGN KEY ( `id_document` ) REFERENCES anta_".$username.".`documents`( id_document )
                      ON DELETE CASCADE,
				FOREIGN KEY ( `id_sentence` ) REFERENCES anta_".$username.".`sentences`( id_sentence )
                      ON DELETE CASCADE
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
		);
	}
	
	/**
	 * select all the sentences containing all the terms selected
	 * get the tag cloud from these sentences.
	 * return a complex object,as usual, with the results and max frequency ( n of sentences found)
	 * and distro (n. of documents found )
	 */
	public static function getNeighbourhood( Application_Model_User $antaUser, array $terms, $offset=0, $limit=50 ){
		$stmt = Anta_Core::mysqli()->query(
			"SELECT id_sentence, COUNT(id_document) as distribution, COUNT(id_sentence) as frequency, GROUP_CONCAT( DISTINCT word SEPARATOR ', ') as word
			FROM anta_{$antaUser->username}.`occurrences` 
			WHERE id_sentence IN 
			( 
				SELECT id_sentence FROM anta_{$antaUser->username}.sentences
				WHERE content LIKE '%".implode( '%', $terms )."%' 
			)
			GROUP BY stem ORDER BY frequency DESC ".($limit != -1? "LIMIT {$offset}, {$limit}": "") );
			
		$results = array();
		$mf = 1;
		$md = 1;
		while( $row = $stmt->fetchObject() ){
			$mf = max( $mf, $row->frequency );
			$results[] = (object) array( 'ids' => $row->id_sentence, "word" => $row->word, "f"=>$row->frequency, "d" => $row->distribution );
		}
		
		return (object) array( "results"=>$results, "max_frequency"=>$mf, "max_distribution"=>$md);
	}

}
?>
