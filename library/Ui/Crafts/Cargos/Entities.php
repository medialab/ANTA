<?php
/**
 * @package Ui_Crafts_Cargos
 */
 
/**
 * Provide the <ol><li> structure to be used with entities
 * 
 */
class Ui_Crafts_Cargos_Entities extends Ui_Crafts_Cargo {
	
	public function __toString(){
		if ( empty( $this->items ) ) return parent::__toString();
		$this->_content .= '<ol id="entities-selectable">';
		foreach( array_keys( $this->items ) as $k ){
			$this->_content .= $this->items[ $k ];
		}
		$this->_content .= '</ol>';
		return parent::__toString();
		
	}
	
}
?>
