<?php
class Application_Model_SuperEntity extends Application_Model_SubEntity {
	
	/**
	 * array of visible, limited children
	 */
	public $children = array();
	
	public function addChild( Application_Model_SubEntity $child ){
		$this->children = array_merge( $this->children, func_get_args() );
	}
	
}

?>
