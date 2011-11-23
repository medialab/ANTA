<?php
class Application_Model_DocumentsTagsMapper{

	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`documents_tags` (
				`id_document` int(11) NOT NULL,
				`id_tag` int(11) NOT NULL,
				UNIQUE KEY `id_document` (`id_document`,`id_tag`),
				FOREIGN KEY ( `id_document` ) REFERENCES anta_".$username.".`documents`( id_document )
                      ON DELETE CASCADE,
				FOREIGN KEY ( `id_tag` ) REFERENCES anta_".$username.".`tags`( id_tag )
                      ON DELETE CASCADE	  
			) ENGINE=INNODB"
        );
	}
	
	public static function add( Application_Model_User $antaUser, $idDocument, $idTag ){
		
		$stmt = Anta_Core::mysqli()->query( 
			"INSERT IGNORE INTO anta_".$antaUser->username.".`documents_tags` (
				`id_document`, `id_tag` 
			) VALUES (
				?, ?
			)", array( $idDocument, $idTag )
		);
		
		return $stmt->rowCount();
	}
	
	
	
	public static function remove( Application_Model_User $antaUser, $idDocument, $idTag ){
		
		$stmt = Anta_Core::mysqli()->query( 
			"DELETE FROM anta_".$antaUser->username.".`documents_tags` WHERE id_document = ? AND id_tag = ?",
			array(
				$idDocument, $idTag
			)
		);
		
		return $stmt->rowCount();
	}
	
	
	
	/**
	 * return a list of tags for the given prefix 
	 */
	public static function getAvailableTags( Application_Model_User $antaUser){
		$query = "
			SELECT 
				t.id_tag, t.content as content, t.parent_id_tag as pid,
				c.content as category, 
				count( dt.id_document ) as distro
			FROM 
			anta_{$antaUser->username}.`tags` t, 
			anta_{$antaUser->username}.documents_tags dt,
			anta_{$antaUser->username}.categories c 
			
			WHERE 
			
			dt.id_tag = t.id_tag AND
			c.id_category = t.id_category
			GROUP BY id_tag
			ORDER BY content ASC
			"
		
		;
		
		$stmt = Anta_Core::mysqli()->query( $query);
		
		$results = array();
		
		while(	$row = $stmt->fetchObject() ){
			$tag = new Application_Model_Tag( $row->id_tag, $row->content, $row->category, $row->pid );
			$tag->distro = $row->distro;
			$results[] = $tag;
		}
		
		return $results;
	}
}
?>