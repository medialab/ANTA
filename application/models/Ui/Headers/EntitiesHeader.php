<?php
/**
 * @package Ui_Headers
 */
 
/**
 * describe an html object, a generic item to be used in a FlowModule
 *
 */
 class Application_Model_Ui_Headers_EntitiesHeader {
	
	public $limit;
	public $offset;
	public $loadedEntities;
	public $totalEntities;
	
	public $numberOfGroups;

	protected function _getFilters(){
		$content = "";
		
		// get query
		$query = Dnst_Filter::getProperty('query');
		$exact = Dnst_Filter::getProperty('exact');
		if( !empty( $query ) ){
			$delLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::remove('query');
			$content .= '<span class="is-untouchable-tag">contains:<b> '.$query.'</b><a href="'.$delLink.'" style="padding-left:6px"><img class="tag-icon" src="'.Anta_Core::getBase().'/images/cross-small.png"></a></span>';
		}
		if( !empty( $exact ) ){
			$delLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::remove('exact');
			$content .= '<span class="is-untouchable-tag">match type: <b>exactly {'.$query.'}</b><a href="'.$delLink.'" style="padding-left:6px"><img class="tag-icon" src="'.Anta_Core::getBase().'/images/cross-small.png"></a></span>';
		}
		// get tags
		$tags = Dnst_Filter::getProperty('tags');
		if (!empty($tags)){
			foreach( $tags as $idTag){
				$tag = Application_Model_TagsMapper::getTag( $this->user, $idTag );
				$delLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::remove('tags', $tag->id );
				$content .= '<span class="is-untouchable-tag">'.$tag->category.'<b>: '.$tag.'</b><a href="'.$delLink.'" style="padding-left:6px"><img class="tag-icon" src="'.Anta_Core::getBase().'/images/cross-small.png"></a></span>';
			}
		
		}
		
		
		
		return $content;
	}
	
	private function prepareSortingChoice( $label, $ascending, $descending, $grids="grid_1", $title=""){
		
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
	
	
	
	public function __toString(){
		
		
		
		// reaf the current offset an liit props
		$offset =& $this->offset;
		
		$limit  =& $this->limit; Dnst_Filter::getProperty( "limit" );
		
		// establish if left arrow and / or right arrow should be displayed
		$hasLeftOffset  = $offset > 0;
		$hasRightOffset = $offset +$limit < $this->totalEntities;
		
		
		return '
		<div class="grid_24 alpha omega item-header" style="padding-bottom:6px; vertical-align:middle">
		  <div class="grid_1 prefix_1 margin_2 alpha">
		  '.( $hasLeftOffset === false ?'':
			// left fast-forward  offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', 0 ).'"
				title="'.I18n_Json::get('topResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-fast-left.png">
			 </a>'
		  ).'&nbsp;
		  </div>
		  <div class="grid_1 margin_2">
		  '.( $hasLeftOffset === false ?'':
			// left offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', max( 0, $offset - $limit ) ).'"
				title="'.I18n_Json::get('previousResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-left.png">
			 </a>'
		  ).'&nbsp;
		  </div>
		  <div class="grid_18">
		    <div class="grid_12 suffix_1  margin_1 alpha">
		      <b class="black"><span class="included tip-helper" title="'.$this->includedEntities.'included entities and '.$this->connectedEntities.' connected with a visible document">'.$this->includedEntities.'</span>
			  <span class="connected tip-helper" title="connected entities"></span> / '.$this->totalEntities.'</b> entities ('.$offset." - ".min( $offset + $limit, $this->totalEntities ).')
		      '.$this->_getFilters().'
		    </div>
		    <div class="grid_5 omega " style="padding-bottom:6px; vertical-align:middle">
		      <input id="search-field" class="width_2 tip-helper" type="text" value="'.I18n_Json::get('search').'"  title="'.I18n_Json::get('search-in-selected-results').'">
		      <a style="padding:4px;margin-top:6px;" id="search-field-submit" href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', 0 ).'" title="'.I18n_Json::get('search-in-fulltext').'" class="tip-helper"><img src="'.Anta_Core::getBase().'/images/magnifier.png"></a>
		      <a style="padding:4px;margin-top:6px;" id="search-field-exact-submit" href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::prependProperties( array('offset'=>0, 'exact'=>true ) ).'" title="'.I18n_Json::get('match exactly the given string').'" class="tip-helper"><img src="'.Anta_Core::getBase().'/images/magnifier.png"></a>
			</div>
		    
			
		  </div>
		  <div class="grid_1 margin_1">
		  '.( $hasRightOffset === false ?'':
			// left offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', $offset + $limit ).'"
				title="'.I18n_Json::get('nextResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-right.png">
			 </a>'
		  ).'&nbsp;
		  </div>
		  <div class="grid_1 suffix_1 margin_1 omega">
		  '.( $hasRightOffset === false ?'':
			// left offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', $this->totalEntities - $limit ).'"
				title="'.I18n_Json::get('lastResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-fast-right.png">
			 </a>'
		  ).'
		  </div>
		  
		  	  
		</div>
		<!-- filters -->
		
		
		<!-- sosrt -->
		<div class="grid_24 alpha omega item-header" style="padding-bottom: 6px">
				<div class="grid_1 suffix_2 alpha centered"><input type="checkbox" id="select-all-selectable" class="a-button tip-helper" style="margin-left:5px" title="'.I18n_Json::get('select all').'"></div>
				'.$this->prepareSortingChoice( "text content", "sign ASC", "sign DESC", "grid_3 tip-helper", I18n_Json::get('content') ).'
				<div class="grid_15">
					<button id="open-tag-panel">open tag panel</button>
				</div>
				'.
				$this->prepareSortingChoice( "freq", "occurrences ASC", "occurrences DESC", "grid_1 tip-helper", I18n_Json::get('max number of occurrences per document') ).
				$this->prepareSortingChoice( "docs", "distro ASC", "distro DESC", "grid_1 tip-helper omega", I18n_Json::get('number of documents covered') ).
				'
		</div>
		<!-- all items in selection selector... -->
		<div class="grid_22 prefix_1 suffix_1 alpha omega" style="display:none" id="select-all-filtered-items-box">
			<div id="select-all-filtered">
				'.I18n_Json::get( "selected" ).' <b>'.$this->loadedEntities.'</b> '.I18n_Json::get( "entities in this page" ).'
				<span> '.I18n_Json::get( "selected all the" ).' <b>'.$this->totalEntities.'</b> '.I18n_Json::get( "available" ).'</span>
			</div>
			<div style="display: none" id="all-filtered-selected">
				<b>'.$this->totalEntities.'</b> '.I18n_Json::get( "entities have been selected" ).'
				<span>'.I18n_Json::get( "undo selection" ).'</span>
			</div>
		</div>
		';
		
	}
}
 
?>
