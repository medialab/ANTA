<?php

class UpdateController extends Application_Model_Controller_Api
{
	
	
    
	public function syncViewsAction(){
		$this->_response->setAction( 'sync-views' );
		try{
			Application_Model_ViewsMapper::install( $this->_user->username );
		} catch( Exception $e ){
			$this->_response->throwError( $e->getMessage() );
		}
		echo $this->_response;
	}
	
    /**
     * This action will load sql updates from the file
     * databases.sql
     * and will affect all databases belonging to an anta users.
     * Use spceial string {{user}}
     */
    public function syncDbAction(){
		$this->_response->setAction( 'databases' );
		
		
		// get database.sql queries()
		
		// read files
		$updateFile = APPLICATION_PATH."/../updates/databases.sql";
		if( !file_exists( $updateFile ) ){
			$this->_response->throwError( "file 'updates/".basename( $updateFile)."' not found" );
		}
		
		
		$this->_response->file = "updates/".basename( $updateFile);
		
		// read and execute sql command
		$query = file_get_contents( $updateFile );
		$this->_response->query = $query;
		$executed = array();
		
		$users = Application_Model_UsersMapper::getUsers();
		
		// prepare the queries for each user
		foreach ( $users as $user ){
						
			$executed[ $user->username ] = array(
				"query"=>str_replace( '{{user}}', $user->username, $query ),
				"result"=>"none" 
			);
			// install / reinstall views
			try{
				Application_Model_ViewsMapper::install( $user->username );
			} catch( Exception $e ){
				print_r ($e);
			}
		}
			
		
		foreach( $executed as $username => $query ){
			$queries = explode( ";",$executed[ $username ]["query"] ); 
			foreach( $queries as $query ){
				echo $query."\n";
				try{
					Anta_Core::mysqli()->query(  $query );
					echo "\tok, done.\n";
				} catch( Exception $e ){
					echo "\tnot executed: ".$e->getMessage()."\n";
				}
			}
		}
		
		// execute query for each
		
		
		
		
		$this->_response->executed = $executed;
		
		
		// get anta users
		
		
		echo $this->_response;
	}
    
    public function __call( $a, $b ){
			
		$action = str_replace( "Action", "", $a );
		$this->_response->setAction( $action );
		
		$this->_response->throwError( "action not found" );
		
	}
}?>
