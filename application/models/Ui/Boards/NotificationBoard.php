<?php
/**
 * @package Ui
 */
 
/**
 * Shows the identity, otherwise a login form. It Contains a logout button
 */
class Application_Model_Ui_Boards_NotificationBoard {
	
	protected static $_instance;
	
	protected $_errors;
	protected $_messages;
	
	public static function getInstance(){
		if( self::$_instance == null ){
			self::$_instance = new Application_Model_Ui_Boards_NotificationBoard();
		}
		return self::$_instance;
	}
		
	public function __construct(){
		$this->_errors = array();
		$this->_messages = array();
		
	}
	
	public function setMessage( $message ){
		$this->_messages[] = $message;
	}
	
	public function setError( $error ){
		$this->_errors[] = $error;
	}
	
	public function __toString(){
		if( empty( $this->_errors ) && empty( $this->_messages ) ) return '';
		$html = '
		<!-- notification board -->
		<style>
			#notification-board{
				display:none;
				border-bottom:1px solid #dbdbdb;
				padding-bottom:6px;
				margin-bottom:18px;
				background: url("'.Anta_Core::getBase().'/images/bg_notification_grid.png");
				line-height:1.4em;
			}
			.errors{
				
			}
			.messages{
				
			}
			input[type=text]
			.invalid{
				border-top: 1px solid #ff6600;
			}
		</style>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#notification-board").slideToggle( "fast" );
				$("#notifications img").click( function(){
					$("#notification-board").slideToggle( "fast" );
					$(this).css( "display","none");
				});
			});
		</script>
		<noscript>';
			
		if( ! empty( $this->_errors ) ) {
			$html .= '<div class="grid_22 suffix_1 prefix_1 alpha omega errors" id="errors-displayer-noscript">'.end( $this->_errors ).'</div>';
		}
			
		if( ! empty( $this->_messages ) ) {
			$html .= '<div class="grid_22 suffix_1 prefix_1 alpha omega messages" id="messages-displayer-noscript">'.end( $this->_messages ).'</div>';
		}
		
		$html .= '</noscript>';
		
		$html .= '
		<!-- notification board -->
		<div id="notification-board" class="grid_24 alpha omega">
			<div class="grid_23 alpha">';
			// with script
			if( ! empty( $this->_errors ) )
				$html .= '<div class="grid_21 prefix_1 suffix_1 alpha omega errors">'.end( $this->_errors ).'</div>';
			if( ! empty( $this->_messages ) )
				$html .= '<div class="grid_21 prefix_1 suffix_1 alpha omega messages">'.end( $this->_messages ).'</div>';

			$html .= '
			</div>
			<!-- open close notification board (toggle) -->
			<div class="grid_1 omega"><img src="'.Anta_Core::getBase().'/images/cross-small.png" /></div>
			<!-- endof open close notification board (toggle) -->
		</div>
		<!-- endof notification board -->
		
		
		
	    ';
		
		return $html;
		
	}
}
