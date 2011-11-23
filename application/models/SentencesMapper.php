<?php
/**
 * Map the table sentences
 */
class Application_Model_SentencesMapper {
	
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`sentences` (
				`id_sentence` int(11) NOT NULL AUTO_INCREMENT,
				`id_document` int(11) NOT NULL,
				`position` int(11) NOT NULL,
				`content` text NOT NULL,
				PRIMARY KEY (`id_sentence`),
				KEY( `id_document` ),
				FOREIGN KEY ( `id_document` ) REFERENCES anta_".$username.".`documents`( id_document )
					ON DELETE CASCADE
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
		);
	}
	
	/**
	 * clean the table sentences from every sentence belonged to the given id_document
	 * @antaUser	- db user, db name as well
	 * @idDocument	- id_document doc index
	 */
	public static function cleanSentences( Application_Model_User $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query("
			DELETE FROM anta_".$antaUser->username.".`sentences`
			WHERE id_document = ? ", array(	$idDocument ) 
		);
		return $stmt->rowCount();
	}
	
	public static function cleanCoOccurrences( Application_Model_User $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query("
			DELETE FROM anta_".$antaUser->username.".`co_occurrences`
			WHERE id_document = ? ", array(	$idDocument ) 
		);
		return $stmt->rowCount();
	}
	
	/**
	 * return a list of Sentences object
	 */
	public static function match( $antaUser, $regexp ){

		// clean regexp
		$stmt = Anta_Core::mysqli()->query(
			"SELECT id_sentence as id, s.id_document as doc, s.content, s.position, d.date
				FROM  anta_{$antaUser->username}.`sentences` s JOIN anta_{$antaUser->username}.documents d USING (id_document)
				WHERE `content` REGEXP ?
			ORDER BY date DESC, id_document DESC, position ASC ", array( $regexp )
		);
		
		$results = array( "docs"=>array() );
		while( $row = $stmt->fetchObject() ){
			
			if( !isset( $results["docs"][ $row->doc ] ) ){
				$results["docs"][ $row->doc ] = array();
			}
			$results["docs"][ $row->doc ][] = new Application_Model_Sentence( $row->id, $row->content, $row->doc, $row->position, $row->date );
			
		}
		return $results;
		
	
	
	}
	
	public static function getNumberOfSentences( Application_Model_User $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT COUNT(*) as number_of_sentences
				FROM anta_".$antaUser->username.".`sentences` 
			WHERE id_document = ? ", array( $idDocument )
		);
		$row = $stmt->fetchObject();
		if( $row == null ) return 0;
		return $row->number_of_sentences;
	}
	
	
	public function getSentencesStatement( Application_Model_User $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT `id_sentence`, `id_document`, `position`, `content`
				FROM anta_".$antaUser->username.".`sentences` 
			WHERE id_document = ? ", array( $idDocument )
		);
		return $stmt;
		
	}
	
	public static function getSentences( Application_Model_User $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT `id_sentence`, `id_document`, `position`, `content`
				FROM anta_".$antaUser->username.".`sentences` 
			WHERE id_document = ? ", array( $idDocument )
		);
		$results = array();
		while( $row = $stmt->fetchObject() ){
			$results[] = $row;
		}
		return $results;
		
	}
	
	public static function addSentence( Application_Model_User $antaUser, $idDocument, $position, $content ){
		$stmt = Anta_Core::mysqli()->query("
			INSERT INTO  anta_".$antaUser->username.".`sentences` (
				`id_sentence`, `id_document`, `position`, `content`
			) VALUES (
				NULL, ?, ?, ?
			)", array( $idDocument, $position, $content ) 
		);
		return  Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".`sentences`" );
	}
	
}
?>
