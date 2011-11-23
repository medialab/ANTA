<?php
/**
 * describe a tag entry in tags table
 */
class Application_Model_Tag{
	public $id;
	public $cryptoId;
	public $content;
	public $category;
	public $pid;
	
	public function __construct( $id, $content, $category, $pid = 0 ){
		$this->id = $id;
		$this->cryptoId = Dnst_Crypto_SillyCipher::crypt( $id );
		$this->content = $content;
		$this->category = $category;
		$this->pid = $pid;
	}
	
	public function __toString(){
		return $this->content;
	}
}
?>
