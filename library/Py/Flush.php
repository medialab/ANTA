<?php
class Py_Flush{
	public function __construct( $command ){
		# load ini config file
		$config = new Zend_Config_Ini(  APPLICATION_PATH . "/configs/application.ini", "contents" );
		# get path of ptn files
		passthru( 'python '.$config->pys->path.'/'.$command );
		
	}	
}
?>