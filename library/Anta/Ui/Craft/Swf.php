<?php
/**
 * @package Anta_Ui_Craft
 *
 */

/**
 * handle and build a swf div
 * auto load swfobject
 */
class Anta_Ui_Craft_Swf extends Ui_Craft {
	
	/** The swf file url
	 *@var string
	 */
	public $url = "";
	/**
	 * default embed params
	 */
	public $embedParams = array( 
		'width'=>'100%', 
		'height'=>'100%', 
		'version'=>'9.0.0'
	);
	public function setEmbedParam( array $params = array() ){
		$this->embedParams = array_merge( $this->embedParams, $params );
		
	}
		
	public function __toString(){
		
		$this->_content = '
			<script type="text/javascript" src="'.Anta_Core::getBase().'/js/swfobject.js"></script>
			
			<div class="swf-object grid_24 alpha omega" id="swf-'.$this->id.'" style="height:400px">
				<h1>Alternative content</h1>
				<p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
			</div>
			<script type="text/javascript">
				swfobject.embedSWF("'.$this->url.'", "swf-'.$this->id.'", "'.$this->embedParams['width'].'", "'.$this->embedParams['height'].'", "'.$this->embedParams['version'].'", "expressInstall.swf");
			</script>
		';
		
		return parent::__toString();
	}
}
