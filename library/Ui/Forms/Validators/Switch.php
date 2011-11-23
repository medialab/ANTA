<?php
/**
 *@package Ui_Forms_Validators
 */ 
/**
 * Similar to Ui_Forms_Validators_Iterator, but uses a chain of validator. If one of the given validator return a valid value, then it is accepted.
 */
 class Ui_Forms_Validators_Switch extends Ui_Forms_Validator{
	
	/**
	 * an array of validators
	 * @var array
	 */
	public $validators = array();
	
	public function isValid( $value ){
		
		$temporaryResult = false;
		$lastError = "";
		foreach( array_keys( $this->validators ) as $k ){
			if( !$this->validators[ $k ]->isValid( $value ) ) {
				$lastError = implode( array_keys( $this->validators[ $k ]->getMessages() ) );
				continue;
			}
			$temporaryResult = true;
		}
		
		$this->_error( $lastError );
		
		return $temporaryResult;
	}
	
	
}
?>