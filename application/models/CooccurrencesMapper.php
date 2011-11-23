<?php
/**
 * Map the table cooccurrences
 */
class Application_Model_CooccurrencesMapper {

	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS anta_".$username.".`co_occurrences` (
				`id_co_occurrence` int(11) NOT NULL AUTO_INCREMENT,
				`id_document` int(11) NOT NULL,
				`id_sentence` int(11) NOT NULL,
				`stem_A` varchar(50) NOT NULL,
				`stem_B` varchar(50) NOT NULL,
				`word_A` varchar(50) NOT NULL,
				`word_B` varchar(50) NOT NULL,
				`distance` int(11) NOT NULL,
				PRIMARY KEY (`id_co_occurrence`),
				KEY `id_document` (`id_document`,`id_sentence`),
				KEY `stem_A` (`stem_A`,`stem_B`),
				FOREIGN KEY ( `id_document` ) REFERENCES anta_".$username.".`documents`( id_document )
                      ON DELETE CASCADE,
				FOREIGN KEY ( `id_sentence` ) REFERENCES anta_".$username.".`sentences`( id_sentence )
                      ON DELETE CASCADE
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
		);

	}

}
?>