<?php
/**
 * @package Ui_Crafts_Headers_Lenses
 */

/**
 * allow to manage a list of documents. add search capabilities.
 */
class Ui_Crafts_Headers_Lenses_Documents extends Ui_Crafts_Headers_Lens{
	
	
	protected function _init(){
		
	}
	
	public function __toString(){
		
		// read current orders
		//print_r( Dnst_Filter::getProperty( "order" ) );
		if( Dnst_Filter::getProperty( "query" ) != null ){
			$queries = array();
			
			foreach( Dnst_Filter::getProperty( "query" ) as $property => $value ){
				
				if( $property == "tag" ){
					//$queries[] = Application_Model_TagsMapper::getTag( $this->user, $value );
					$tag = Application_Model_TagsMapper::getTag( $this->user, $value );
					
					if( $tag == null ){
						$queries[] = "$property: <b>$value</b>";
						continue;
					}
					$queries[] = "<span class='is-untouchable-tag'>{$tag->category} <b>{$tag}</b></span>";
				}
			}
			
			
			$this->query = '<span style="padding-left:5px">filtered by: '.implode(" ",$queries).' <a href="?'.Dnst_Filter::remove( "query" ).'">reset</a></span>';
		}
		
		
		$this->_filters = '
			<div class="grid_1 alpha">
				<input type="checkbox" id="select-all-selectable" style="margin-left:8px" title="'.I18n_Json::get( 'select all' ).'" >
			</div>'.
			$this->prepareSortingChoice( "type", "mimetype ASC", "mimetype DESC", "grid_2" ).
			$this->prepareSortingChoice( "lang", "language ASC", "language DESC", "grid_2" ).
			$this->prepareSortingChoice( "title", "title ASC", "title DESC", "grid_10" ).
			$this->prepareSortingChoice( "vis", "`ignore` ASC", "`ignore` DESC", "grid_2" ).
			$this->prepareSortingChoice( "date", "date ASC", "date DESC", "grid_3" ).
			$this->prepareSortingChoice( "status", "status ASC", "status DESC", "grid_2" );
		
		return parent::__toString();
	}
	
	private function prepareSortingChoice( $label, $ascending, $descending, $grids="grid_1"){
		
		$hasAscending  = Dnst_Filter::hasProperty( "order", array( $ascending ) );
		$hasDescending = $hasAscending == true? false: Dnst_Filter::hasProperty( "order", array( $descending ) );
		
		$hasSorting = $hasAscending || $hasDescending ? "sorting" : "";
		
		return '
			<div class="'.$grids.' '.$hasSorting.'">
				<a href="?'.Dnst_Filter::setProperty( 'order', array( $ascending ), array( $descending ) ).'">'.
					I18n_Json::get( $label ).' '.( $hasAscending? "▲": ( $hasDescending? "▼": "") ).'
				</a>
			</div>';
	}

	
}
