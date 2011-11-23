<?php
/**
 * @package Textopoly
 */
 
/**
 * yahoo term extraction services.
 * 
 * usqge sqmple
 * $texto = new Textopoly_Yahoo( array(
 *			"format"=>"json",
 *		"q"=>'SELECT * FROM search.termextract where context="
 * 	                  Help urban education organizations allocate resources more efficiently: The map"'
 *       ));
 *       
 *  echo $i->get();
 */
class Textopoly_Yahoo extends Textopoly_Plugin{
	
	protected $_url = "http://query.yahooapis.com/v1/public/yql";
	
	
	
	
	protected function _call(){
		// prepare the data to be sent
		$params = '';
		foreach( $this->_params as $k => $p ){
				$params .= $k."=".urlencode($p)."&"; 
		}
		// echo $params;
		
		curl_setopt($this->_ch, CURLOPT_URL, $this->_url."?". $params );
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
	}
	
	
	/**
	 * Error handling
	 */
	protected function _evaluate(){
		
		if( $this->_result ==null ){
			$this->setError( "response null" );
			return; 
		}
		
		// json error
		$this->_result = json_decode( $this->_result );
		
		if( $this->_result ==null ){
			$this->setError( "json error, unkown" );
			return; 
		}
		
		
		
	}
}
