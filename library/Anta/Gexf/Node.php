<?php

 class Anta_Gexf_Node{
 
	public $label;
	public $id;
	public $atts;
	public $vizs;
	public $start; // start for dynamic node
	public $end; // end for dynamic nodes
	
	function __construct( $id, $label, array $atts = array(), array $vizs = array() ){

		$this->id = $id;
		$label = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $label); //Remove illegal XML char and replace with ' '
		$this->label = str_replace(array("&","<",">", "\""),array("&amp;",""),$label);
		$this->atts = $atts;
		$this->vizs = $vizs;
		
	}
	
	
	
	function __toString(){
		if( empty( $this->atts ) && empty( $this->vizs )){
			return '<node id="'.$this->id.'"  label="'.$this->label.'"/>';
		}
		$html = '
		<node id="'.$this->id.'"  label="'.str_replace( array('"','&'), array(' ','&amp;'),   $this->label  ).'"><attvalues>';
		
		foreach( $this->atts as $k => $v ){
			$v = is_array( $v )? implode(", ", array_unique( $v) ):$v;
			
			$html .= '<attvalue for="'.$k.'" value="'.$v.'"/>';
		}
		
		$html .= '</attvalues>';
		
		foreach( $this->vizs as $k => $v )
			$html .= '<viz:'.$k.' '.$v.'/>';
			
        
        
        $html .= '</node>';
		return $html;
		
	}
 }
?>
