<?php
/**
 * @package Application_Model
 */

/**
 * anta user.*_entities table api. Return and match generic subEntities instances
 */
class Application_Model_ViewEntitiesDistributionMapper{
	
	
	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE ALGORITHM=UNDEFINED DEFINER=`anta`@`localhost`
			 SQL SECURITY DEFINER VIEW anta_".$username.".`rws_entities_distribution` AS 
				SELECT
					anta_".$username.".`rws_entities_documents`.`id_rws_entity` AS `id_rws_entity`,
					count(distinct anta_".$username.".`rws_entities_documents`.`id_document`) AS `distribution`
				FROM anta_".$username.".`rws_entities_documents`
				GROUP BY `rws_entities_documents`.`id_rws_entity` ORDER BY count(distinct `rws_entities_documents`.`id_document`) desc;

			"
        );
	}
	
}
?>
