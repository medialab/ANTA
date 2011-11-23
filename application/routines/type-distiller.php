<?php
 # Some textual header...
 header('Content-type: text/plain; charset=UTF-8');
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
 header("Cache-Control: no-store, no-cache, must-revalidate");
 header("Cache-Control: post-check=0, pre-check=0", false);
 header("Pragma: no-cache");	
 
 # Define APPLICATION_PATH to application directory
 $scriptPath = dirname(__FILE__);
 define( 'APPLICATION_PATH', substr( $scriptPath, 0, strrpos( $scriptPath, "/", -1 ) ) );
 
 
 # Ensure library/ is on include_path
 set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
	APPLICATION_PATH . '/models',
    get_include_path(),
 )));
 
 # Zend_Application
 require_once 'Zend/Application.php';

 # Create application, bootstrap, and run
 $application = new Zend_Application(
    'development',
    APPLICATION_PATH . '/configs/application.ini'
 );
 
 # Set Infinite looping
 set_time_limit( 0 );
 
 # 1. load anta distiller
 $distiller =  Anta_Distiller::getInstance();

 # 2. coupling thread type with class names to utilize
 $distiller->addThreadHandler( 'IN', 'Anta_Distiller_Indexer' );
 $distiller->addThreadHandler( 'st', 'Anta_Distiller_Indexer' ); 
 $distiller->addThreadHandler( 'al', 'Anta_Distiller_Rws_Alchemy' ); 
 $distiller->addThreadHandler( 'op', 'Anta_Distiller_OpenCalais' ); 
 $distiller->addThreadHandler( 'ng', 'Anta_Distiller_Ngram' ); 
 
 # 3. start loaded distiller
 $distiller->start();
 
?>
