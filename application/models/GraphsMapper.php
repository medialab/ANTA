<?php
class Application_Model_GraphsMapper{

	public static function install( $username ){
		$stmt = Anta_Core::mysqli()->query( 
			"CREATE TABLE IF NOT EXISTS  `anta_{$username}`.`graphs` (
			  `id_graph` int(11) NOT NULL AUTO_INCREMENT,
			  `engine` varchar(64) NOT NULL COMMENT 'the engine used, e.g tina | simple gexf',
			  `description` varchar(200) NOT NULL,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `localUrl` text NOT NULL,
			  `status` int(11) NOT NULL COMMENT 'ok | ko',
			  `error` text NOT NULL COMMENT 'if status = ko, describe the error',
			  PRIMARY KEY (`id_graph`),
			  KEY `date` (`date`,`status`)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci"
		);
		
	}
	
	public static function addGraph( Application_Model_User $antaUser, Application_Model_Graph $graph ){
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO  anta_{$antaUser->username}.`graphs`(
				`id_graph`, `engine`, `description`, `date`,
				`localUrl`, `status`, `error`
			) VALUES (
				null, ?, ?, CURRENT_TIMESTAMP,
				?, ?, ?
			)", array( $graph->engine, $graph->description, $graph->localUrl, "oo", "")
		);
		
		return Anta_Core::mysqli()->lastInsertId("anta_{$antaUser->username}.`graphs`" ) ;
	}
	
	public static function setDescription( Application_Model_User $antaUser, $idGraph, $description ){
		$stmt = Anta_Core::mysqli()->query( "
			UPDATE anta_{$antaUser->username}.`graphs` SET description = ? WHERE id_graph = ? ", array( $description, $idGraph ) 
		);
		
		return $stmt->rowCount();
	}
	
	public static function setError( Application_Model_User $antaUser, $idGraph, $error ){
		$stmt = Anta_Core::mysqli()->query( "
			UPDATE anta_{$antaUser->username}.`graphs` SET error = ? WHERE id_graph = ? ", array( $error, $idGraph ) 
		);
		
		return $stmt->rowCount();
	}	
	
	public static function setLocalUrl( Application_Model_User $antaUser, $idGraph, $url ){
		$stmt = Anta_Core::mysqli()->query( "
			UPDATE anta_{$antaUser->username}.`graphs` SET localUrl = ? WHERE id_graph = ? ", array( $url, $idGraph ) 
		);
		
		return $stmt->rowCount();
	}	
	
	public static function setStatus( Application_Model_User $antaUser, $idGraph, $description ){
	
	}	
	
	public static function getGraph(Application_Model_User $antaUser, $idGraph ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT
				`id_graph` as id, `engine`, `description`, `date`,
				`localUrl`, `status`, `error`
			FROM anta_{$antaUser->username}.`graphs` WHERE id_graph = ?", array( $idGraph )
		);
		$row = $stmt->fetchObject();
		return $row == null? null: new Application_Model_Graph( $row->id, $row->engine, $row->date, $row->description, $row->localUrl, $row->status, $row->error );
	}
	
	/**
	 * get a list of graph object
	 */
	public static function getGraphs( Application_Model_User $antaUser, Dnst_Helpers_Properties $properties = null ){
		
		if ( $properties == null ) $properties = new Dnst_Helpers_Properties( -1, 0, array( "id DESC" ) );
		
		$stmt = Anta_Core::mysqli()->query( "
			SELECT SQL_CALC_FOUND_ROWS *,
				`id_graph` as id, `engine`, `description`, `date`,
				`localUrl`, `status`, `error`
			FROM anta_{$antaUser->username}.`graphs` ".$properties, $properties->binds
		);
		
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = new Application_Model_Graph( $row->id, $row->engine, $row->date, $row->description, $row->localUrl, $row->status, $row->error );
		}
		
		$stmt = Anta_Core::mysqli()->query(" SELECT  FOUND_ROWS() as totalItems" );
		$totalItems = $stmt->fetchObject()->totalItems ;
		
		return (object) array( "results" => $results, "totalItems" => $totalItems );
	}
}
?>