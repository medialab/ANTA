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
