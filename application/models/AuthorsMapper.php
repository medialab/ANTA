<?php
/**
 * @package Anta
 */
 
/**
 * 
 */
class Application_Model_AuthorsMapper{
	
	/**
	 * Replace all authors
	 */
	public static function addAuthors( $antaUser, $idDocument, array $authors ){
		// delete previous authors
		$removed = Application_Model_DocumentsMapper::removeAuthors($antaUser, $idDocument );
				echo $removed;
	
		
		foreach( $authors as $author ){
			$author = trim( $author );
			if( strlen( $author ) == 0 ) continue;
			Anta_Core::mysqli()->query("
				INSERT IGNORE INTO anta_".$antaUser->username.".authors ( name ) values ( ? )",
				array( $author )
			);
			
			$id_author = Anta_Core::mysqli()->lastInsertId();
			
			if( $id_author == 0 ){
				$instance = self::getAuthor( $antaUser, $author );
				if ($instance == null ) continue;
				$id_author = $instance->id_author;
			}
			
			// create link with the document table
			Anta_Core::mysqli()->query("
				INSERT IGNORE INTO anta_".$antaUser->username.".documents_author ( id_document, id_author ) values ( ?, ? )",
				array( $idDocument, $id_author )
			);
			
		}
		
		
	}
	

	public static function suggest( $antaUser, $name ){
		$stmt = Anta_Core::mysqli()->query("SELECT name FROM anta_".$antaUser->username.".authors WHERE LOWER( name ) LIKE ?", array(
			strtolower( $name )."%"
		));
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = $row->name;
		}
		
		return $results;
	}

	/**
	 * get an authro given the name or the numeric identifier
	 */
	public static function getAuthor( $antaUser, $name ){
		if( is_numeric( $name ) ){
			$stmt = Anta_Core::mysqli()->query("SELECT id_author, name FROM anta_".$antaUser->username.".authors WHERE id_author = ? ", array(
				$name
			));
			return $stmt->fetchObject();
		}
		
		$stmt = Anta_Core::mysqli()->query("SELECT id_author, name FROM anta_".$antaUser->username.".authors WHERE LOWER( name ) = LOWER( ? )", array(
			$name
		));
		
		$row = $stmt->fetchObject();
		
		return $row;
	}
	
	/**
	 * Return all the documents whom the idAuthor is the author, using document_authors table
	 */
	public static function getDocuments( $antaUser, $idAuthor ){
		
	}
	
	
	
	public static function getAuthors( $antaUser, $idDocument ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT id_author as id, name
				FROM anta_".$antaUser->username.".authors
					INNER JOIN anta_".$antaUser->username.".documents_author
				USING (id_author )
			WHERE id_document = ? ", array(
			$idDocument
		));
		
		$authors = array();
		
		while( $row = $stmt->fetchObject() ){
			$authors[] = new Application_Model_Author( $row->id, $row->name );
		}
		
		return  $authors;
	}
	
}
