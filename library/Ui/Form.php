<?php
/**
 * @package UI
 */

/**
 * base class for html form ui. Use the è_init protected method to set the html fotm elements
 * and the tostring cast to display it.
 * you can ccess form elenet values directly as variable of the html form. See addElement method for better
 * explaination.
 */
class Ui_Form{
	
	
	/** the id (also css-attribute) */
	public $id;
	
	/** an array of html form elements */
	public $elements;
	
	/**
	 * the html content to be visualized
	 */
	public $content;
	
	/**
	 * build a simple form: id, title, the action url and the method.
	 * use the toString method to display the form in the web page.
	 */
	public function __construct( $id, $title, $action="", $method="post" ){
		$this->id = $id;
		$this->title = $title;
		$this->action = $action;
		$this->method = $method;
		$this->elements = array();
		$this->_init();
	}
	
	/**
	 * use init when extends simpleform
	 */
	protected function _init(){
	
	}
	
	/**
	 * add an element to the elements table. The elment is also available
	 * using the id variable, e.g $element = $this->element_id;
	 */
	public function addElement( Ui_Forms_Element $element ){
		$varname = str_replace("-","_", $element->id );
		$this->elements[ $varname ] =& $element;
		$this->$varname = $this->elements[ $varname ];
	}
	
	
	/**
	 * This function calls recursively the isValid() method of Application_Model_Forms_Elements_FormElement
	 * child instances, like Application_Model_Forms_Elements_Input.
	 * This function doesn't need any params.
	 * @return boolean validation result.
	 */
	public function isValid( ){
		foreach( array_keys( $this->elements ) as $element ){
			if( !$this->$element->isValid() ){
				// print_r( $this->$element );
				$this->$element->addClass( 'invalid' );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * return an array of form values, if needed
	 */
	public function getValues(){
		$values = array();
		foreach( array_keys( $this->elements ) as $element ){
			$values[ $this->$element->id ] = $this->$element->getValue();
		}
		return $values;
	}
	
	/**
	 * Retrieve all (error) messages stored by all Application_Model_Forms_Elements_Input
	 * instances in elements table.
	 * @return array of messsages ( keys are error code labels while values are array of related error messages )
	 */
	public function getMessages(){
		$messages = array();
		
		foreach( array_keys( $this->elements ) as $element ){
			if( !$this->$element->isValid() ){
			
				$messages[ $this->$element->label ] = array_keys($this->$element->messages);
			}
			
		}
		
		return $messages;
	}
	
	
	
	/**
	 * the html representation of the Ui_Fom instance
	 */
	public function __toString(){
		return '
		<div id="'.$this->id.'"  title="'.$this->title.'">
			<div class="validateTips"></div>

			<form id="form-'.$this->id.'" action="'.$this->action.'" method="'.$this->method.'">
				<div><input type="hidden" name="form-action" value="'.$this->id.'"/></div>
				'.$this->content.'
			</form>
		</div>
		
		';
		
	}
	
}