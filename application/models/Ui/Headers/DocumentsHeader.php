<?php
/**
 * @package Ui_Headers
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Headers_DocumentsHeader extends Application_Model_Ui_Headers_ListHeader{
	
		
	public function __toString(){
		
		
		
		return '
		<div class="grid_24 alpha omega item-header">
		  <div class="grid_2 prefix_1 alpha">'.$this->getEntry( 'mimetype', $this->invertDirections( 'mimetype', 'ASC' ) ).'</div>
		  <div class="grid_2">'.$this->getEntry( 'lang', $this->invertDirections( 'lang', 'ASC' ) ).'</div>
		  <div class="grid_7 item-title">'.$this->getEntry( 'title', $this->invertDirections( 'title', 'ASC' ) ).'</div>
		  <div class="grid_6">'.$this->getEntry( 'description', $this->invertDirections( 'description', 'ASC' ) ).'</div>
		  <div class="grid_3">'.$this->getEntry( 'date', $this->invertDirections( 'date', 'ASC' ) ).'</div>
		  <div class="grid_2">'.$this->getEntry( 'status', $this->invertDirections( 'status', 'ASC' ) ).'</div>
		  <div class="grid_2 omega">&nbsp;</div>
		</div>';
	}
	
	function invertDirections( $orderBy, $defaultOrderDir ){
		if( !isset( $_GET['order'] ) ) return $defaultOrderDir;
		if( $_GET['order'] == $orderBy ){
			if( @$_GET['dir'] == 'ASC' ) return 'DESC';
			return 'ASC';
		}
		return $defaultOrderDir;
	}
}
 
?>
