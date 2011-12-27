<?php
class Application_Model_DocumentsCrawlsMapper{

	public static function install( $username ){
	
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`documents_crawls` (
				`id_document` int(11) NOT NULL,
				`id_crawl` int(11) NOT NULL,
				UNIQUE KEY `id_document` (`id_document`,`id_crawl`),
				KEY `id_crawl` (`id_crawl`),
				FOREIGN KEY ( `id_crawl` ) REFERENCES anta_".$username.".`crawls`( id_crawl )
                      ON DELETE CASCADE,
				FOREIGN KEY ( `id_document` ) REFERENCES anta_".$username.".`documents`( id_document )
                      ON DELETE CASCADE
			) ENGINE=INNODB"
        );
		
	}

}
