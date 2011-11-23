<?php
/**
 * @package Dnst_Json
 */
 
/**
 * Handle a response tipe, with singleton pattarn
 */
 
class Dnst_Json_Response{
	
	protected $_json;
	
	public function __construct(){
		$this->_json = (object) array();
	}
	
	public function setStatus( $status ){
		$this->_json->status = $status;
	}
	
	public function setAction( $action ){
		$this->_json->action = $action;
	}
	
	public function throwError( $error ){
		$this->_json->status = 'ko';
		$this->_json->error  = $error;
		$this->_json->token = session_id();
		exit( $this );
	}
	
	public function __set( $key, $value ){ 
		$this->_json->$key = $value; 
    } 
	
	public function translit(){
	}
	
	public function __toString(){
		if( isset( $_GET['debug'] ) )print_r(  $this->_json );
		return preg_replace('/(\\\u\w{4})/','',json_encode( $this->_json ));
	}
	
	
}