<?php
/**
 * @package Anta
 */
 
/**
 * Mapping anta users
 */
class Application_Model_UsersMapper{
	
	public static function install(){
		$query = "
			CREATE TABLE `anta`.`users` (
				`id_user` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`name` VARCHAR( 16 ) NOT NULL ,
				`realname` varchar(200) NOT NULL,
				`email` VARCHAR( 255 ) NOT NULL ,
				`salt` VARCHAR( 32 ) NOT NULL ,
				`passwd` VARCHAR( 32 ) NOT NULL ,
				UNIQUE (
					`name` ,
					`email`
				)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	}
	
	/**
	 * add an user to the system
	 * create a database with the same privileges ($username and $password)
	 */
	public static function addUser( $realname, $username, $email, $password, $type="researcher" ){
		
		// try to make dir
		// mkdir
		if( @mkdir( Anta_Core::getUploadPath()."/".$username, 0755 ) === false ){
			// user exists, or problems in creating folder...! Anta_Core::getUploadPath() handle is_writable errors.
			Anta_Core::setError( I18n_Json::get( 'userAlreadyExistsFolder' ) );
			return false;
		};
		
		// load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "security" );
		
		$dynamicSalt = Anta_Core::getDynamicSalt();
		
		$hashedPassword = Anta_Core::hashPassword( $password, $config->passwd->salt, $dynamicSalt );
		
		$stmt = Anta_Core::mysqli()->query("INSERT IGNORE into users( name, email, realname, salt, passwd, type ) VALUES( ?, ?, ?, ?, ?, ?)", array(
			$username, $email, $realname, $dynamicSalt, $hashedPassword, $type
		));
		
		if( $stmt->rowCount() == 0 ){
			Anta_Core::setError( I18n_Json::get( 'userAlreadyExistsDb' ) );
			return false;
		}
		
		// try to create an user db anta_$username
		Anta_Core::setup( $username, $password );
		
		return Anta_Core::mysqli()->lastInsertId();
		
		
		
	}
	
	public static function editUser( $id, $realname, $email, $password='' ){
		
		$setPassword = '';
		$binds = array( $realname, $email );
		
		$password = trim( $password );
		
		if( strlen( $password ) > 0 ){
			$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "security" );
			$setPassword =", salt = ?, passwd = ? ";
			$dynamicSalt = Anta_Core::getDynamicSalt();
			$hashedPassword = Anta_Core::hashPassword( $password, $config->passwd->salt, $dynamicSalt );
			$binds[] = $dynamicSalt;
			$binds[] = $hashedPassword;
		}
		$binds[] = $id;
		
		Anta_Core::mysqli()->query( "UPDATE `anta`.`users` SET realname = ?, email = ? $setPassword WHERE id_user = ?", $binds );
		
	}
	
	
	public static function getUser( $idUser ){
		
		$stmt = Anta_Core::mysqli()->query("
			SELECT id_user as id, name as username, realname, type, email
				FROM anta.users
			WHERE id_user = ?", array( $idUser ) );
		
		$row = $stmt->fetchObject();
		
		if( $row == null ) return null;
		
		return new Application_Model_User( $row->id, $row->username, $row->realname, $row->type, $row->email );
		
	}
	
	public static function getUsers( $orderBy="id_user DESC", $offset = 0, $limit = 50){
		$stmt = Anta_Core::mysqli()->query("
			SELECT id_user as id, name as username, realname, type, email
				FROM anta.users
				ORDER BY $orderBy
			LIMIT ?, ?", array( $offset, $limit) );
		$users = array();
		while( $row = $stmt->fetchObject() ){
			$users[] = new Application_Model_User( $row->id, $row->username, $row->realname, $row->type, $row->email );
		}
		return $users;
	}
	
	public static function removeUser( Application_Model_User $antaUser ){
		
		// copy file in a zip folder
		
		// dump db
		
		
		// unlink its folder
		if( file_exists( Anta_Core::getUploadPath()."/".$antaUser->username ) ){
			if( rrmdir( Anta_Core::getUploadPath()."/".$antaUser->username ) ){
				return I18n_Json::get( 'unable to unlink path', 'errors' );
			}
		}
		
		
		// delete from db users
		$stmt = Anta_Core::mysqli()->query( "DELETE FROM anta.users WHERE id_user = ? ",array( $antaUser->id ) );
		
		try{
			Anta_Core::mysqli()->query( "SET foreign_key_checks = 0" );
			$stmt = Anta_Core::mysqli()->query( "DROP DATABASE IF EXISTS  `anta_".$antaUser->username."`" );
			Anta_Core::mysqli()->query( "SET foreign_key_checks = 1" );
			
		} catch( Exception $e ){
			return I18n_Json::get( 'mysqliException', 'errors' ).":".$e->getMessage();
		}
		
		// delete from mysql users
		try{
			$stmt = Anta_Core::mysqli()->query( "DROP USER anta_".$antaUser->username."@'localhost'" );
		} catch ( Exception $e ){
			return I18n_Json::get( 'mysqliUserException', 'errors' ).":".$e->getMessage();
		}
		return true;
	}
	
	/**
	 * @return an array of Application_Model_DocumentEntity
	 */
	public static function getEntities( Application_Model_User $antaUser ){
		$stmt = Anta_Core::mysqli()->query( "
		
		
			SELECT id_entity, content, type
				FROM  anta_".$antaUser->username.".`entities`");
		
		$entities = array();
		
		while( $row = $stmt->fetchObject() ){
			
			
			$entities[] = new Application_Model_Entity( 
					$row->id_entity, 
					$row->content, 
					$row->type, 0
			);
		}
		
		return $entities;
	}
	
	public static function getDocumentEntityLinks( Application_Model_User $antaUser ){
		$stmt = Anta_Core::mysqli()->query( "
			SELECT id_document, id_entity, entities_occurrences.relevance FROM  anta_".$antaUser->username.".`entities_occurrences` 
			JOIN anta_".$antaUser->username.".entities
			USING ( id_entity ) ");
		$links = array();
		
		while( $row = $stmt->fetchObject() ){
			$index = 'd'.$row->id_document.'e'.$row->id_entity;
			if( !isset( $links[ $index ] )){
				$links[ $index ] = new Application_Model_DocumentEntity( 
						$row->id_document,
						$row->id_entity, 
						$row->relevance
				);
				continue;
			}
			// modify entity adding relevance (max relevance)
			$relevance = ( $row->relevance + $links[ $index ]->relevance ) /2;
			$links[ $index ]->relevance = $relevance; 
			
		}
		
		return $links;
	}
	
}

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
 }

?>
