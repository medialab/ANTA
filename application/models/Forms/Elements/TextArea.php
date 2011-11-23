<?php
/**
 * Basic class for input element, without boring descriptor
 */
class Application_Model_Forms_Elements_Textarea extends Application_Model_Forms_Elements_Input{
	
	protected $_autoTaggingEnabled = false;
	
	/**
	 * 
	 * @param atts	- atts name=>value array. atts should have at least two params, named 'name' and 'id'
	 */
	public function __construct( $label, $atts ){
		// base class
		$this->name = $atts['name'];
		$this->id   = $atts['id'];
		
		// this class
		$this->type = 'textarea';
		$this->atts = $atts;
		$this->label = $label;
	}
	
	/**
	 * TODO filter request
	 */
	public function __toString(){
		
		if( $this->_autoFill == true ){
			$this->_value = stripslashes( @$_REQUEST[ $this->id ] );
		}
	
		$html = '<textarea ';
		
		foreach( $this->atts as $att=>$value ){
			$html .= $att.'="'.$value.'" ';
		}
		
		$html .= '>'.stripslashes( $this->_value ).'</textarea>';
		if( $this->_autoTaggingEnabled ){ $html .= '
			<br /><div id="'.$this->id.'-extract-tags" class="fg-button width_3 ui-state-default ui-corner-all" title="extract tags">extract tags automatically</div>';
		}
		return  $html;
	}
	
	public function enableAutoTagging(){
		$this->_autoTaggingEnabled = true;
	}

}





?>
