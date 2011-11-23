<?php
 /**
  * @package
  * external library
  */
/**
 * $texto = new Textopoly_Alchemy( array(
 *		"outputMode" => "json",
 * 		"text"       => 'uri encoded text',
 * 		"apikey"     => "your api key"
 * ));
 *       
 *  echo $i->get();
 */
class Textopoly_Alchemy extends Textopoly_Plugin {
	
	public function __construct( $url, array $params ){
		$this->_url = $url;
		parent::__construct( $params );
	}
	
	protected function _call(){
		
		// prepare the data to be sent
		$params = '';
		
		foreach( $this->_params as $k => $p ){
			$params .= $k."=".urlencode($p)."&"; 
		}
		
		try{
		
			curl_setopt($this->_ch, CURLOPT_URL, $this->_url."?". $params );
			curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
		} catch( Exception $e ){
			$this->setError( "response exception :". $e->getMessage() );
		}
	}
	
	protected function _evaluate(){
		echo "evaluate";
		// test the request and handle errors
		
		
		if( $this->_result ==null ){
			$this->setError( "response null" );
			return; 
		}
		
		// json error
		$result = json_decode( $this->_result );
		
		if( $result == null ){
			$this->setError( "json error, json null" );
			return;
		}
		
		$this->_result = $result;
		
		// alchemy api error
		if( $this->_result->status == "ERROR" ){
			$this->setError(  $this->_result->statusInfo );
			return; 
		}
		
		
	}
	
}
?>
