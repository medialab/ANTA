<?php
/**
 * Basic class for input element, without boring descriptor
 */
class Application_Model_Forms_Elements_Input extends Application_Model_Forms_Elements_FormElement{
	
	public $atts;
	public $type;
	public $validator;
	
	/**
	 * 
	 * @param atts	- atts name=>value array. atts should have at least two params, named 'name' and 'id'
	 */
	public function __construct( $type, $label, $atts ){
		// base class
		$this->name = $atts['name'];
		$this->id   = $atts['id'];
		
		// this class
		$this->type = $type;
		$this->atts = $atts;
		$this->label = $label;
		
		
	}
	
	public function setAttribute( $att, $value ){
		$this->atts[ $att ] = $value;
	}
	
	/**
	 * TODO filter request
	 */
	public function __toString(){
		if( $this->type != "submit" )
		
		if( isset( $_POST[ $this->id ] ) ){
			$this->atts[ 'value' ] = stripslashes( $_POST[ $this->id ] );
			if( $this->type == "checkbox" ){
				$this->atts[ 'checked' ] = 'checked';
			}
		} else if( $this->_value != null ){
			$this->atts[ 'value' ] = stripslashes( $this->_value );
		} else {
			$this->atts[ 'value' ] = "";
		}
		
		
		$html = '<input type="'.$this->type.'" ';
		
		foreach( $this->atts as $att=>$value ){
			$html .= $att.'="'.$value.'" ';
		}
		
		$html .= '/>';
		return  $html;
	}
	
	public function getValue(){
		if( $this->type == "checkbox" ){
			// return its boolean value
			return isset( $_REQUEST[ $this->id ] );
		}
		return $this->_value;
	}
	
	public function isValid(){
		
		$this->_value = @$_REQUEST[ $this->id ];
		if( $this->validator == null ) return true;
		
		if( $this->_evaluated ) return $this->_result;
		
		$this->_evaluated = true;
		
		if(! $this->validator->isValid( @$_REQUEST[ $this->id ] )){
			$this->messages = $this->validator->getMessages();
			$this->_result = false;
			return false;
		}
		$this->_result = true;
		return true;
	}
}

?>
