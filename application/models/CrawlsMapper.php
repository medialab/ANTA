<?php
class Application_Model_CategoriesMapper{
	
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS `anta_".$username."`.`crawls` (
			  `id_crawl` int(11) NOT NULL AUTO_INCREMENT,
			  `start_words` text NOT NULL,
			  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `status` enum('alive','error','finished') NOT NULL,
			  PRIMARY KEY (`id_crawl`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
		);
	}
	
	
	public static function selectAll( $filters=array() ){
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