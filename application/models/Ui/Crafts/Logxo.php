<?php
/**
 * @package Ui_Crafts
 */
 
/**
 * Log or raw text reader
 * 
 */
class Application_Model_Ui_Crafts_Logxo extends Application_Model_Ui_Crafts_Craft {
	
	/** the preview of the text */
	protected $_text;
	
	public function read( $content ){
		$this->_text = $content;
	}
	
	
	public function __toString(){
		
		$this->_content = '
			<div class="grid_22 prefix_1 suffix_1 alpha margin_1 text-preview">
				<pre class="grid_20  alpha omega">'.$this->_text.'</pre>
			</div>
		';
		
		return parent::__toString();
	}
}
?>
