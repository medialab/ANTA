<?php
/**
 * @package Anta_Distiller
 */
/**
 * Ngram via python script
 * you should chmod chown the pys folder, because Ngram   
 *    sudo chmod 0775 -R /home/daniele/public/anta/trunk/application/pys/tinafiles
 *    sudo chown -hR pj:www-data /home/daniele/public/anta/trunk/application/pys/tinafiles/
 * 
 */
class Anta_Distiller_Ngram extends Anta_Distiller_ThreadHandler{
	
	public function init(){
	
		$document  =& $this->_target;
		$user      =& $this->_distiller->user;
		$command   =  "zendify.py add_doc ".$user->id." 0 ".$document->id ;
		
		// 0. verify file integrity
		$localUrl = $this->_verifyFileIntegrity( $user, $document );
		if( $localUrl === false ) return;
		
		
		// 1. pythonify
		$this->_log( "document url: ".$localUrl, false );
		$this->_log( "ngram: python {$command}", false );
		$py = new Py_Scriptify( $command );
		
		
		// 2. validate output
		if( $py->getResult() == null ){
			$this->_log( "error: crasp! python failed...", false );
		} 
		
		// read pithonify
		$this->_log("result: ". $py->getResult(), false );
		
		echo "\n";
	}
	
}
?>