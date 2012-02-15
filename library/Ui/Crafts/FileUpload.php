<?php
/**
 * @package Ui_Crafts
 */
 
/**
 * enable file upload blueimp
 * It requires https://github.com/blueimp/jQuery-File-Upload jquery plugin
 * and <link rel="stylesheet" href="css/jquery.fileupload-ui.css"> as well
 */
class Ui_FileUpload extends Ui_Craft {
	
	
	
	public function init(){
		# create an upload 
		
		$this->content = '
			<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
			<script src="js/jquery.iframe-transport.js"></script>
			<!-- The basic File Upload plugin -->
			<script src="js/jquery.fileupload.js"></script>
			<!-- The File Upload image processing plugin -->
			<script src="js/jquery.fileupload-ip.js"></script>
			<!-- The File Upload user interface plugin -->
			<script src="js/jquery.fileupload-ui.js"></script>
			<!-- The localization script -->
			<script src="js/locale.js"></script>
			<!-- The main application script -->
			<script src="js/main.js"></script>
		';
		
	}
	
	
	
}