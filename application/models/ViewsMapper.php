<?php
/**
 * @package Application_Model
 */

/**
 * Install all the views available for anta user
 */
class Application_Model_ViewsMapper{
	
	
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			 SQL SECURITY DEFINER VIEW anta_".$username.".`rws_entities_distribution` AS 
				SELECT
					`red`.`id_rws_entity` AS `id_rws_entity`,
					count(distinct `red`.`id_document`) AS `distribution`
				FROM anta_".$username.".`rws_entities_documents` `red` JOIN anta_".$username.".`documents` `doc`
				ON (`red`.`id_document` = `doc`.`id_document`)
				WHERE `doc`.`ignore` = 0
				GROUP BY `red`.`id_rws_entity` ORDER BY `distribution` desc;
			"
        );
		
		# documents_metrics view
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			 SQL SECURITY DEFINER VIEW anta_".$username.".`documents_metrics` AS
				SELECT COUNT(*) AS `number_of_documents` from anta_".$username.".`documents` WHERE `ignore` = 0
			"
        );
		
		# documents_metrics view
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			 SQL SECURITY DEFINER VIEW anta_".$username.".`rws_entities_documents_unignored` AS 
				select `ent`.`content` AS `content`,`red`.`id_rws_entity` AS `id_rws_entity`,
				`red`.`id_document` AS `id_document`,
				`red`.`frequency` AS `frequency` 
				FROM anta_".$username.".`rws_entities_documents` `red` 
				JOIN anta_".$username.".`documents` `doc` USING( `id_document` ) 
				JOIN anta_".$username.".`rws_entities` `ent` USING ( `id_rws_entity` )
				WHERE `doc`.`ignore` = 0 and `ent`.`ignore` = 0
			"
		);

		
		# entities_metrics view
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			 SQL SECURITY DEFINER VIEW anta_".$username.".`rws_entities_metrics` AS 
				SELECT COUNT( DISTINCT `id_rws_entity`) AS `number_of_entities` from anta_".$username.".`rws_entities_documents_unignored`
			"
		);

		
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost` 
			 SQL SECURITY DEFINER VIEW anta_".$username.".`rws_entities_per_documents` AS 
			 select `red`.`id_document` AS `id_document`, count(`red`.`id_rws_entity`) AS `entitites_per_document`
			 from anta_".$username.".`rws_entities_documents` `red`
				JOIN anta_".$username.".`documents` `doc`
				ON ( `red`.`id_document` = `doc`.`id_document`)
				JOIN anta_".$username.".`rws_entities` `ent`
				ON ( `red`.`id_rws_entity` = `ent`.`id_rws_entity`)
			 where `doc`.`ignore` = 0 and `ent`.`ignore` = 0
			 group by `red`.`id_document`"
		);

		
		# rws_metrics view
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			 SQL SECURITY DEFINER VIEW anta_".$username.".`rws_metrics_tf` AS
				SELECT `ed`.`id_rws_entity` AS `id_rws_entity`,`ed`.`frequency` AS `frequency`,`ed`.`id_document` AS `id_document`,
					(`ed`.`frequency` / `epd`.`entitites_per_document`) AS `entity_frequency`
				FROM (
					anta_".$username.".`rws_entities_documents` `ed` 
					join anta_".$username.".`documents` `doc` ON `ed`.`id_document` = `doc`.`id_document`
					join anta_".$username.".`rws_entities` `ent` ON `ed`.`id_rws_entity` = `ent`.`id_rws_entity`
					join anta_".$username.".`rws_entities_per_documents` `epd` on((`ed`.`id_document` = `epd`.`id_document`))
				) WHERE `doc`.`ignore` = 0 AND `ent`.`ignore` = 0
			"
        );
		# rws_metrics_tf_idf (full metrics, according to Term frequency – inverse document frequency specs)
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			SQL SECURITY DEFINER VIEW anta_".$username.".`rws_metrics_tf_idf` AS 
			select `red`.`id_rws_entity` AS `id_rws_entity`,(`red`.`distribution` / `dom`.`number_of_documents`) AS `df`,
				`rtf`.`entity_frequency` AS `tf`,
				log((1 / (`red`.`distribution` / `dom`.`number_of_documents`))) AS `idf`,
				(`rtf`.`entity_frequency` * log((1 / (`red`.`distribution` / `dom`.`number_of_documents`)))) AS `tf_idf` 
			from ((anta_".$username.".`rws_entities_distribution` `red` join anta_".$username.".`documents_metrics` `dom`) 
				join anta_".$username.".`rws_metrics_tf` `rtf` on((`red`.`id_rws_entity` = `rtf`.`id_rws_entity`)))"
		);


	}
	
}
?>

