<?php
/**
 * @package Ui_Headers
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Headers_UsersHeader {
	
		
	public function __toString(){
		return '
		<div class="grid_24 alpha omega item-header">
		  <div class="grid_2 prefix_1 alpha">user type</div>
		  <div class="grid_2">nickn.</div>
		  <div class="grid_6">full name</div>
		  <div class="grid_5">email</div>
		  <div class="grid_3 prefix_1">routine</div>
		  <div class="grid_2">logs</div>
		  <div class="grid_2 omega">edit</div>
		</div>';
		
	}
}
 
?>
