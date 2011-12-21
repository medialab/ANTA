<?php
class Application_Model_CrawlsMapper{
	
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS `anta_".$username."`.`crawls` (
				`id_crawl` int(11) NOT NULL AUTO_INCREMENT,
				`start_words` text NOT NULL,
				`request_url` text NOT NULL,
				`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`status` enum('alive','error','finished') NOT NULL,
				PRIMARY KEY (`id_crawl`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
		);
	}
	
	public static function get( Application_Model_User $antaUser, $id ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT
				*
			FROM anta_".$antaUser->username.".`crawls` WHERE id_crawl = ?", array(
			$id	
		));
		
		$row = $stmt->fetchObject();
		return $row == null? null: new Application_Model_Crawl( $row );
	}
	public static function select( Application_Model_User $antaUser, $filters=array() ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT
				*
			FROM anta_".$antaUser->username.".`crawls`"
		);
		
		$results = array();
		while( $row = $stmt->fetchObject() ){
			$results[] = new Application_Model_Crawl( $row );
		}
		return $results;
	}
}
?>
