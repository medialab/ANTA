<?php
/**
 * @package Ui_Header
 */
 
/**
 * 
 *
 */
class Anta_Ui_Header_Entity extends Anta_Ui_Header {

	protected function _getFilters(){
		$content = "";
		
		// get query
		$query = Dnst_Filter::getProperty('query');
		if( !empty( $query ) ){
			$content .= "<em>{$query}</em>";
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
		
		// get date
		$date_start = Dnst_Filter::getProperty('date_start');
		$date_end = Dnst_Filter::getProperty('date_end');
		if (!empty($date_start)){
			$delLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::remove('date_start');
			$content .= '<span class="is-untouchable-tag"><em>'.(empty($date_end)?"at date":"from date").'</em><b>: '.$date_start.'</b><a href="'.$delLink.'" style="padding-left:6px"><img class="tag-icon" src="'.Anta_Core::getBase().'/images/cross-small.png"></a></span>';
		
		}
		
		return $content;
	}
	
	public function __toString(){
		return '
		<div class="grid_24 alpha omega item-header" style="padding-bottom:6px; vertical-align:middle">
		  <div class="grid_1 prefix_1 margin_2 alpha">
		  '.( $this->hasLeftOffset === false ?'&nbsp;':
			// left fast-forward  offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', 0 ).'"
				title="'.I18n_Json::get('topResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-fast-left.png">
			 </a>'
		  ).'
		  </div>
		  <div class="grid_1 margin_2">
		  '.( $this->hasLeftOffset === false ?'&nbsp;':
			// left offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', max( 0, $this->offset - $this->limit ) ).'"
				title="'.I18n_Json::get('previousResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-left.png">
			 </a>'
		  ).'
		  </div>
		  <div class="grid_18">
		    <div class="grid_18 margin_1 alpha omega">
		      <strong class="black">'.$this->totalItems.'</strong> sentences ('.$this->offset." - ".min( $this->offset + $this->limit, $this->totalItems ).')
		      '.$this->_getFilters().'
		    </div>
		   
		    
			
		  </div>
		  <div class="grid_1 margin_1">
		  '.( $this->hasRightOffset === false ?'':
			// left offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', $this->offset + $this->limit ).'"
				title="'.I18n_Json::get('nextResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-right.png">
			 </a>'
		  ).'&nbsp;
		  </div>
		  <div class="grid_1 suffix_1 margin_1 omega">
		  '.( $this->hasRightOffset === false ?'':
			// left offset
			'<a href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', $this->totalItems - $this->limit ).'"
				title="'.I18n_Json::get('lastResults').'"
				class="tip-helper">
				<img src="'.Anta_Core::getBase().'/images/arrow-fast-right.png">
			 </a>'
		  ).'
		  </div>
		  
		  	  
		</div>
		<!-- filters -->
		<div class="sort grid_22  prefix_2 alpha omega item-header" style="padding-bottom: 6px">
			'.$this->prepareSortingChoice( "date", "date ASC", "date DESC", "grid_2 alpha tip-helper", I18n_Json::get('order by date') ).'
			'.$this->prepareSortingChoice( "position", "position ASC", "position DESC", "grid_2 tip-helper", I18n_Json::get('order by sentence position') ).'
		</div>
		
		
		
		<!-- sosrt
		<div class="sort grid_24 alpha omega item-header" style="padding-bottom: 6px">
				<div class="grid_1 alpha centered"><input type="checkbox" id="select-all-selectable" class="a-button tip-helper" style="margin-left:5px" title="'.I18n_Json::get('select all').'"></div>
				'.
				$this->prepareSortingChoice( "type", "mimetype ASC", "mimetype DESC", "grid_1 tip-helper", I18n_Json::get('document mime-type - doc, pdf') ).
				$this->prepareSortingChoice( "language", "language ASC", "language DESC", "grid_2 tip-helper", I18n_Json::get('document mime-type - doc, pdf') ).
				$this->prepareSortingChoice( "date", "date ASC", "date DESC", "grid_2 tip-helper", I18n_Json::get('explicit document date') ).
				$this->prepareSortingChoice( "title", "title ASC", "title DESC", "grid_1 tip-helper", I18n_Json::get('document title') ).'
				<div class="grid_7 " style="padding-bottom:6px; vertical-align:middle">
				  <input id="search-field" class="width_3 tip-helper" type="text" value=""  title="'.I18n_Json::get('search-in-selected-results').'">
				  <a style="padding:4px;margin-top:6px;" id="search-field-submit" href="'.$_SERVER['REFERRER_URI'].'?'.Dnst_Filter::setProperty( 'offset', 0 ).'" title="'.I18n_Json::get('search-in-fulltext').'" class="tip-helper"><img src="'.Anta_Core::getBase().'/images/magnifier.png"></a>
				</div>
				
				'.
				$this->prepareSortingChoice( "ignore", "`ignore` ASC", "`ignore` DESC", "grid_1 tip-helper omega ", I18n_Json::get('ignored documents will not appear in graphs') ).
				$this->prepareSortingChoice( "status", "status ASC", "status DESC", "grid_2 tip-helper omega centered", I18n_Json::get('analysis status') ).
				'
		</div>
		 -->
		<!-- all items in selection selector... -->
		<div class="grid_22 prefix_1 suffix_1 alpha omega" style="display:none" id="select-all-filtered-items-box">
			<div id="select-all-filtered">
				'.I18n_Json::get( "selected" ).' <b>'.$this->loadedItems.'</b> '.I18n_Json::get( "entities in this page" ).'
				<span> '.I18n_Json::get( "selected all the" ).' <b>'.$this->totalItems.'</b> '.I18n_Json::get( "available" ).'</span>
			</div>
			<div style="display: none" id="all-filtered-selected">
				<b>'.$this->totalItems.'</b> '.I18n_Json::get( "documents have been selected" ).'
				<span>'.I18n_Json::get( "undo selection" ).'</span>
			</div>
		</div>
		';
		
	}
}