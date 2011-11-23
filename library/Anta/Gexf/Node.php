<?php

 class Anta_Gexf_Node{
 
	public $label;

	public $id;

	
	public $atts;
	
	public $vizs;
	
	function __construct( $id, $label, array $atts = array(), array $vizs = array() ){

		$this->id = $id;
		$this->label = $label;
		$this->atts = $atts;
		$this->vizs = $vizs;
		
	}
	
	
	
	function __toString(){
		if( empty( $this->atts ) && empty( $this->vizs )){
			return '<node id="'.$this->id.'"  label="'.$this->label.'"/>';
		}
		$html = '
		<node id="'.$this->id.'"  label="'.str_replace( array('"','&'), array(' ','&amp;'),   $this->label  ).'"><attvalues>';
		
		foreach( $this->atts as $k => $v )
			$html .= '<attvalue for="'.$k.'" value="'.$v.'"/>';
		
		$html .= '</attvalues>';
		
		foreach( $this->vizs as $k => $v )
			$html .= '<viz:'.$k.' '.$v.'/>';
			
        
        
        $html .= '</node>';
		return $html;
		
	}
 }
?>