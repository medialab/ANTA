<?php
/**
 *

 */
class Anta_Ui_Header{

	public $limit;
	public $offset;
	public $loadedItems;
	public $totalItems;
	
	public function __construct( array $properties = array() ){
		
		// reaf the current offset an liit props
		$this->offset = Dnst_Filter::getProperty( "offset" );
		$this->limit = Dnst_Filter::getProperty( "limit" );
		
		// every public fproperty can be override
		foreach( $properties as $key=>$value){
			$this->$key = $value;
		}
		$this->totalItems = empty( $this->totalItems )? 0: $this->totalItems;
		
		// establish if left arrow and / or right arrow should be displayed
		$this->hasLeftOffset  = $this->offset > 0;
		$this->hasRightOffset = $this->offset + $this->limit < $this->totalItems;
		
		
	}
	
	public function __toString(){
		
	}
	
	protected function prepareSortingChoice( $label, $ascending, $descending, $grids="grid_1", $title=""){
			
			$hasAscending  = Dnst_Filter::hasProperty( "order", array( $ascending ) );
			$hasDescending = $hasAscending == true? false: Dnst_Filter::hasProperty( "order", array( $descending ) );
			
			$hasSorting = $hasAscending || $hasDescending ? "sorting" : "";
			
			return '
				<div class="'.$grids.' '.$hasSorting.'" title="'.$title.'" >
					<a href="?'.Dnst_Filter::setProperty( 'order', array( $ascending ), array( $descending ) ).'">'.
						I18n_Json::get( $label ).' '.( $hasAscending? "▲": ( $hasDescending? "▼": "") ).'
					</a>
				</div>';
	} 
}
?>