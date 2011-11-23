<?php
/**
 * @package
 */
 
/**
 * Mapping anta users' routines
 */
class Application_Model_RoutinesMapper{
	
	public static function install(){
		$query = "
			CREATE TABLE IF NOT EXISTS `routines` (
			  `id_routine` int(11) NOT NULL,
			  `id_user` int(11) NOT NULL,
			  `status` varchar(10) NOT NULL DEFAULT 'start' COMMENT 'start|die|died',
			  PRIMARY KEY (`id_routine`),
			  UNIQUE KEY `id_user` (`id_user`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		
	}
	
	
	public static function getStatus( $idUser ){
		
		$stmt = Anta_Core::mysqli()->query( "
			SELECT status
				FROM  anta.`routines`
			WHERE id_user = ?
			ORDER BY id_routine DESC
			LIMIT 1", array( $idUser )
		);
		
		$row = $stmt->fetchObject();
		
		if( $row == null ) return null;
		
		return $row->status;
	}
	
	public static function kill( $idUser ){
		self::setStatus( $idUser, "died" );
	}
	
	public static function setStatus( $idUser, $status ){
		Anta_Core::mysqli()->query( "
			UPDATE anta.`routines` SET status = ? WHERE id_user = ? ",
			array( $status, $idUser )	
		);
	}
	
	public static function getRoutine( Application_Model_User $user ){
	
		$stmt = Anta_Core::mysqli()->query( "
			SELECT id_routine
				FROM  anta.`routines`
			WHERE id_user = ?", array( $user->id )
		);
		
		$row = $stmt->fetchObject();
		
		if( $row == null ) return null;
		
		return $row->id_routine;
	
	}
	
	public static function addRoutine( $idUser ){
	
		$stmt = Anta_Core::mysqli()->query( "
			INSERT IGNORE INTO anta.`routines` ( id_user, status ) VALUES( ?, ? )",
			array( $idUser, "new" )	
		);
		
		
		
		return Anta_Core::mysqli()->lastInsertId( "anta.`routines`" );
	}
	
	
}	
