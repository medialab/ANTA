<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an entity Application_Model_Entity
 *
 */
 class Ui_Crafts_Items_Distribution extends Ui_Crafts_Item{
	
	
	/**
	 * Class constructor
	 */
	public function __construct(  $id, $title, array $properties ){
		
		foreach( $properties as $key=>$value){
			$this->$key= $value;
		}
		/*
		$this->nameX = 
		$this->namey = 
		 
		$this->minX = $minX;
		$this->minY = $minY;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		
		$this->labelX = $labelX;
		$this->labelY = $labelY;
		*/
		$this->title = $title;
		parent::__construct( $id );
		
	}
	
	public function __toString(){
		
		return '
		
		<div id="'.$this->id.'" class="grid_20 prefix_1 alpha omega margin_1" style="">
		 
		   <div class=" grid_20 alpha omega" style="padding-bottom:7px;border:1px solid #bdbdbd; background: url(\''.Anta_Core::getBase().'/images/bg_light_grid.png\') repeat">
			   
			  <!-- the item 
			  $this->buildDistributionGraph()
			  -->
			  
			  
			  <div class="grid_20 alpha omega">
				   <h3 style="padding:5px">'.$this->title.'</h3>
				  <div class="grid_1 alpha" style="text-align:right;vertical-align:middle;padding-top:6px">'.$this->minX.'</div>
				  
				  <!-- dynamic script inside Ui_Crafts_Items_Distribution class -->
				  <div id="slider-'.$this->id.'" class="margin_1 grid_10 alpha omega"></div>
				  <!-- end of slider -->
				  
				  <div class="grid_1 suffix_1" style="text-align:left;padding-top:6px">'.$this->maxX.'</div>
			  
			      <div class="grid_1" style="text-align:right;padding-top:6px">min</div>
			      <div class="grid_2"><input type="text" id="slider-'.$this->id.'-left" class="grid_2" name="min-'.$this->rangeName.'" style="width:50px;display:inline" value="'.$this->minX.'"></div>
			      
			      <div class="grid_1 prefix_1" style="text-align:right;padding-top:6px">min</div>
			      <div class="grid_2"><input type="text" id="slider-'.$this->id.'-right" class="grid_2" name="max-'.$this->rangeName.'" style="width:50px;display:inline" value="'.$this->maxX.'"></div>
			     
			       
				  <script type="text/javascript">
					
					var $slider_'.$this->id.'_left  = $("#slider-'.$this->id.'-left");
					var $slider_'.$this->id.'_right = $("#slider-'.$this->id.'-right");
					var $slider_'.$this->id.'       = $("#slider-'.$this->id.'");
					
					$(function() {
						$slider_'.$this->id.'.slider({
								range: true,
								slide: function(event, ui) {
									$slider_'.$this->id.'_left.val( ui.values[0] );
									$slider_'.$this->id.'_right.val( ui.values[1] );
								},
								min: '.$this->minX.',
								max: '.$this->maxX.',
								step: 1,
								values: [ '.$this->minX.', '.$this->maxX.' ]
						});
						
						// set automatically the values
						$slider_'.$this->id.'_left.change( function( event, ui ){
							$slider_'.$this->id.'.slider( "option", "values", [$slider_'.$this->id.'_left.val(), $slider_'.$this->id.'_right.val() ] );
						});
						
						$slider_'.$this->id.'_right.change( function( event, ui ){
							$slider_'.$this->id.'.slider( "option", "values", [$slider_'.$this->id.'_left.val(), $slider_'.$this->id.'_right.val() ] );
						});
					});
					
					<!-- check default values -->
					$(document).ready(function(){
						$slider_'.$this->id.'.slider( "option", "values", [$slider_'.$this->id.'_left.val(), $slider_'.$this->id.'_right.val() ] );
					})
				  </script>
				  
			  </div>
			  
			  
			  <!-- the item -->
		   </div>
		 </div>
		 ';
		
	}
	
	private function buildDistributionGraph(){
	return '
	<script type="text/javascript+protovis">

/* Sizing and scales. */
var w = 680,
    h = 50,
    x = pv.Scale.linear(0, 9.9).range(0, w),
    y = pv.Scale.linear(0, 14).range(0, h);

/* The root panel. */
var vis = new pv.Panel()
    .width(w)
    .height(h)
    .bottom(20)
    .left(20)
    .right(10)
    .top(5);

/* X-axis and ticks. 
vis.add(pv.Rule)
    .data(x.ticks())
    .visible(function(d) d)
    .left(x)
    .bottom(-5)
    .height(5)
  .anchor("bottom").add(pv.Label)
    .text(x.tickFormat);
*/
/* The stack layout. */
vis.add(pv.Layout.Stack)
    .layers(data)
    .x(function(d) x(d.x))
    .y(function(d) y(d.y))
  .layer.add(pv.Area);

/* Y-axis and ticks.
vis.add(pv.Rule)
    .data(y.ticks(3))
    .bottom(y)
    .strokeStyle(function(d) d ? "rgba(128,128,128,.2)" : "#000")
  .anchor("left").add(pv.Label)
    .text(y.tickFormat);
 */
vis.render();
	
	function update( ){
		vis.render();
	}

    </script>
	';
	}
 }
?>
