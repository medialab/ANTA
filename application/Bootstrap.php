<?php
# common function and string utils
include APPLICATION_PATH."/functions.php";

# base path. you can use Anta_Core::getBase() to get the same resut
define( 'ANTA_URL', "/anta_dev" );


// start session here



class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	/**
	 * Initialize storing data into session history
	 */
	protected function _initCarnivore(){
		# start session manually
		
		try{
		if( isset( $_REQUEST['token'] ) ){
			session_id( $_REQUEST['token'] );
			#Zend_Session::start();
		}
			Zend_Session::start();
		} catch( Exception $e ){
			
		}
		Dnst_History_Carnivore::sniff();
	}
}