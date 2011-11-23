<?php
/**
 * @package Ui_Crafts_Cargos
 */
 
/**
 *
 * 
 */
class Ui_Crafts_Cargos_Categories extends Ui_Crafts_Cargo {
	
	/**
	 *@param an array of Application_model_Tag instances
	 */
	public function dispatch( array $tags ){
		
		foreach( $tags as $tag ){
			$item = $this->getItem( $tag->category );
			if( $item == null ){
				continue;
			}
			$item->addTag( $tag );
		}
	}
	
	
	
}