<?php
/**
 * @package Anta
 */

/**
 * Mapper query for table threads in anta main database
 */
class Application_Model_ThreadsMapper{
	
	/**
	 * script to install the table
	 */
	public static function install(){
		
		Anta_Core::mysqli()->query("
			CREATE TABLE  `anta`.`threads` (
				`id_thread` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`id_routine` INT NOT NULL ,
				`type` VARCHAR( 2 ) NOT NULL ,
				`order` INT( 0 ) NOT NULL ,
				`status` VARCHAR( 10 ) NOT NULL DEFAULT  'ready' COMMENT  'ready | done | working',
				INDEX (  `type` ,  `order` ,  `status` )
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci"
		);

	}
	
	public static function getCurrentThreads( Application_Model_User $user ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT `id_thread`, `id_routine`, `id_user`, `type`,  `order`,  `threads`.`status` AS status
				FROM `threads` INNER JOIN `routines`
			USING (`id_routine`)
			WHERE `id_user` = ? AND `threads`.`status` = ?
			ORDER BY `id_thread` ASC", array( $user->id, 'ready' )
		);
		
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = new Application_Model_Thread(
				$row->id_thread,
				$row->id_routine, 
				$row->type,
				$row->order,
				$row->status 
			);
		}
		
		return $results;
	}
	
	public static function getCurrentThread( Application_Model_User $user ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT `id_thread`, `id_routine`, `id_user`, `type`,  `order`,  `threads`.`status` AS status
				FROM `threads` INNER JOIN `routines`
			USING (`id_routine`)
			WHERE `id_user` = ? AND `threads`.`status` = ?
			ORDER BY `id_thread` ASC LIMIT 1", array( $user->id, 'ready' )
		);
	
		$row = $stmt->fetchObject();
		
		return $row == null? null : 
			new Application_Model_Thread(
				$row->id_thread,
				$row->id_routine, 
				$row->type,
				$row->order,
				$row->status 
			);
		
		
	}
	
	/**
	 * declare a thread died: it has finished its analysis cycle.
	 */
	public static function killCurrentThread( $idThread ){
		$stmt = Anta_Core::mysqli()->query("
			UPDATE `threads` SET `status` = 'died' WHERE id_thread = ?", array( $idThread )
		);
	}
	
	/**
	 * rehabilitate every thread for the current user
	 */
	public static function restoreThreads( Application_Model_User $user ){
		$stmt = Anta_Core::mysqli()->query("
			UPDATE `threads` SET `status` = 'ready'
			WHERE id_routine IN ( 
				SELECT id_routine FROM  `routines` WHERE id_user = ?
			)", array( $user->id )
		);
	}
	
	/**
	 * Remove the given thread for the user
	 * @param $user -	Application_Model_User
	 */
	public static function removeThread( $idThread ){
		$stmt = Anta_Core::mysqli()->query("
			DELETE FROM `threads` WHERE id_thread = ?", array( $idThread )
		);
		
		return $stmt->rowCount();
	}
	
	/**
	 * use the table 
	 */
	public static function getThreads( Application_Model_User $user ){
		$stmt = Anta_Core::mysqli()->query("
			SELECT `id_thread`, `id_routine`, `id_user`, `type`,  `order`,  `threads`.`status` AS status
				FROM `threads` INNER JOIN `routines`
			USING (`id_routine`) 
			WHERE `id_user` = ?", array( $user->id)
		);
		
		$results = array();
		
		while( $row = $stmt->fetchObject() ){
			$results[] = new Application_Model_Thread(
				$row->id_thread,
				$row->id_routine, 
				$row->type,
				$row->order,
				$row->status 
			);
		}
		
		return $results;
	}
	
	
	public static function addThread( Application_Model_User $user, $type, $order, $status ){
		
		$routine = Application_Model_RoutinesMapper::getRoutine( $user );
		
		if( $routine == null ){
			$routine = Application_Model_RoutinesMapper::addRoutine( $user->id );
		}
		
		$stmt = Anta_Core::mysqli()->query("
			INSERT IGNORE INTO `anta`.`threads` (
				`id_thread`, `id_routine`, `type`, `order`, `status`
			) VALUES ( 
				NULL, ?, ?, ?, ? 
			)", array( $routine, $type, $order, $status )
		);
		
		return Anta_Core::mysqli()->lastInsertId('threads', 'id_thread');
	}
}
