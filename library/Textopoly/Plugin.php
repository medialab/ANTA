<?php
/**
 * package textopoly
 */

/**
 * base class to be extended. Simply send a curl request.
 */
class Textopoly_Plugin{
	
	protected $_url;
	
	protected $_params;
	
	protected $_ch;
	
	protected $_result;
	
	protected $_lastError;
	
	protected $_responseLength = 0;
	
	static $ERR_URL_NOT_FOUND = "404";
	
	
	
	public function __construct( $params ){
		
		$this->_params = $params;
		
		
		// check for null?
		
		$this->_ch = curl_init();
		
		
		$this->_call();
		$this->_execute();
		$this->_evaluate();
	}
	
	public function setError( $error ){
		$this->_lastError = $error;
	}
	
	public function hasError(){
		return $this->_lastError != null;
	}
	
	public function getError(){
		return $this->_lastError;
	}
	
	public function get(){
		return $this->_result;
	}
	
	public function getResponseLength(){
		return $this->_responseLength;
	}
	
	public function getParam( $key ){
		return $this->_params[ $key ];
	}
	
	protected function _evaluate(){
		// test the request and handle errors
	}
	
	/**
	 * delete redundant spaces, tabs, and newline to reduce the text size
	 */
	public static function compress( $text ){
		return preg_replace( '/\s+/',' ',$text );
	}
	
	
	public static function isUrl ($text ){
	    return strpos( $text, "http:" ) === false? false: true;
	}
	
	/**
	 * Prepare the call.
	 * Do not override if you send only postdata via params var
	 */
	protected function _call(){
		// prepare the data to be sent
		
		curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
	}
	
	/**
	 * call this function when you're ready.
	 */
	protected function _execute(){
		 $this->_result = curl_exec( $this->_ch );
		 // echo "[".$this->_result."]";
		 $this->_responseLength = strlen( $this->_result );
		 curl_close ( $this->_ch );
		 unset( $this->_ch );
	}
	
	

}
?>
