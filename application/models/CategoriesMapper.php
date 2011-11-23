<?php
class Application_Model_CategoriesMapper{
	
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`categories` (
				`id_category` int(11) NOT NULL AUTO_INCREMENT,
				`content` varchar(255) NOT NULL,
				`type` varchar(4) NOT NULL DEFAULT 'text' COMMENT 'category pseudo mime type (date, txt, img )...',
				PRIMARY KEY (`id_category`),
				UNIQUE KEY `content` (`content`)
			) ENGINE=INNODB  DEFAULT CHARSET=utf8"
		);
	}
	
	
	/**
	 * @return last insert id or 0 if the category exists yet.
	 */
	public static function add( Application_Model_User $antaUser, $content, $type = null ){
		Anta_Core::mysqli()->query("
			INSERT IGNORE INTO anta_".$antaUser->username.".categories (
				content, type
			) VALUES(
				?, ?
			)", array(	$content, $type )
		);
		
		$insertedId =  Anta_Core::mysqli()->lastInsertId( "anta_".$antaUser->username.".`categories`" );
		
		// return the valus
		if( $insertedId == 0 ){
			return self::getId( $antaUser, $content );
		}
		return $insertedId;
	}
	
	public static function getId( Application_Model_User $antaUser, $content ){
		
		$stmt = Anta_Core::mysqli()->query( 
			"SELECT id_category FROM anta_".$antaUser->username.".`categories` cat
				WHERE cat.content = ?
			LIMIT 1
			", array( $content )
		);
		
		$row = $stmt->fetchObject();
		if( $row == null ) return 0;
		return $row->id_category;
	}
	
	public static function suggest( Application_Model_User $antaUser, $content ){
		$stmt = Anta_Core::mysqli()->query( 
			"SELECT id_category, content FROM anta_".$antaUser->username.".`categories` cat
				WHERE cat.content LIKE ?
			", array( "%".$content."%" )
		);
		$results = array();
		while( $row = $stmt->fetchObject() ){
			$results[] =  (object) array( "id"=>$row->id_category, "category" => $row->content );
		}
		return $results;
	}
	
	
	public static function getAll( Application_Model_User $antaUser ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT
				id_category, content, type
			FROM anta_".$antaUser->username.".`categories`"
		);
		
		$results = array();
		while( $row = $stmt->fetchObject() ){
			$results[ "". $row->id_category ] = new Application_Model_Category( $row->id_category, $row->content, $row->type );
		}
		return $results;
	}
}

?>