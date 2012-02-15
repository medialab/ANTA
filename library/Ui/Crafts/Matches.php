<?php
/**
 * @package Ui_Crafts_Cargos
 */
 
/**
 *
 * 
 */
class Ui_Crafts_Matches extends Ui_Craft {
	
	/**
	 * Frog matches object
	 * @var 
	 */
	public $frogMatches;
	
	public $user;
	
	public $docsMatches;
	
	public function __toString(){
		if( $this->docsMatches == null || $this->frogMatches == null ) return "";
		# title
		$this->_content .= '
			<div class="grid_22 prefix_1 suffix_1 alpha omega">
				<strong>'.$this->docsMatches->totalItems.'</strong> document titles and 
				<strong>'. $this->frogMatches->totalItems .'</strong> sentences contain 
				<span class="query">'.Dnst_Filter::getProperty('query').'</span>
			</div>';
			
		$this->_content .= '<div class="grid_23 suffix_1 alpha omega">';
		$this->_content .= '<div id="matches-categories" class="grid_6 alpha"><!--<h3>'.I18n_Json::get("categories' statistics").'</h3>-->';
		
		# set limit to 0
		Dnst_Filter::replaceProperty("limit",20);

		# categories
		$c = -1;
		foreach (array_keys( $this->frogMatches->categories ) as $category ){
			$tags = $this->frogMatches->categories[ $category ];
			$c++;
			if( count( $tags ) == 0 ) continue;
			
			$this->_content .= '<div class="grid_6 alpha omega  '.($c>0?"alpha-border":"").' '.($c<count( $this->frogMatches->categories )-1?"omega-border":"").' category"><h4 class="grid_4 alpha omega prefix_2" style="text-align:center;padding-top:5px">'.$category.'</h4>';
			
			$max = 0;
			// print_r(Dnst_Filter::read());
			foreach( $tags as $tag ){
				
				$max = max( $tag->number_of_documents, $max );
				
				$link = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::setProperty('tags',  array($tag->id_tag)  );
				$addLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::addProperty('tags',  $tag->id_tag  );
				$this->_content .= '
					<div class="grid_6 alpha omega filtered-tag-stats">
						<div class="grid_4 alpha right-aligned" style="margin-right:3px">
							<span >
								<a href="'.$link.'" style="font-size:10px;color:black" title="'.$tag->content.'">'.ucut($tag->content).'</a>
								<a href="'.$addLink.'" >add</a>
							</span><!--'.$tag->id_tag.'--></div>
						<div class="grid_2 omega" >
							<img src="/anta_dev/images/bg_black_grid.png" style="height:6px;display:inline;width:'.round( 40 * $tag->number_of_documents/$max ).'px"> '.$tag->number_of_documents.'
						</div>
					</div>';
			}
			
			
			
			$this->_content .='</div>';
		}
		$this->_content .= '</div><!-- eof matching categories -->'; // of matching categories
		
		$this->_content .='
			<div  class="grid_16 omega" style="padding-left:3px">
				<!-- div class="grid_14 prefix_1 suffix_1 alpha omega omega-border" style="padding-top:6px;padding-bottom:7px">
				</span -->
			</div>';
		
		# show tags filters
		$this->_content .= '<div  class="grid_16 alpha omega filters" >';
		
		// get tags
		$tags = Dnst_Filter::getProperty('tags');
		if (!empty($tags)){
			foreach( $tags as $idTag){
				$tag = Application_Model_TagsMapper::getTag( $this->user, $idTag );
				$delLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::remove('tags', $tag->id );
				$this->_content .= '<span class="is-untouchable-tag">'.$tag->category.'<b>: '.$tag.'</b><a href="'.$delLink.'" style="padding-left:6px"><img class="tag-icon" src="'.Anta_Core::getBase().'/images/cross-small.png"></a></span>';
			}
		
		}
		
		// get docs
		$docs = Dnst_Filter::getProperty('docs');
		if (!empty($docs)){
			foreach( $docs as $idDoc){
				$document = Application_Model_DocumentsMapper::getDocument( $this->user, $idDoc );
				$delLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::remove('docs', $document->id );
				$this->_content .= '
					<span class="is-untouchable-doc">'.$document->title.'<a href="'.$delLink.'" style="padding-left:6px"><img class="tag-icon" src="'.Anta_Core::getBase().'/images/cross-small.png"></a></span>';
			}
		
		}
		
		$this->_content .= '</div>';
		$this->_content .='<div class="grid_16 alpha omega"	id="matches-sentences">';
		
		# load tags for the loaded documents only
		$query = "
			SELECT ta.id_tag, ta.content, dt.id_document, cat.content as category,
			ta.parent_id_tag
			FROM anta_".$this->user->username.".`documents_tags` dt 
			INNER JOIN anta_".$this->user->username.".`tags` ta USING( id_tag )
			INNER JOIN  anta_".$this->user->username.".categories cat USING( id_category )
			WHERE dt.id_document IN( ".implode( ",", array_keys( $this->frogMatches->documents ) )." ) ORDER BY id_document,content";
			
		$stmt = Anta_Core::mysqli()->query( $query );
		$tags = array();
		while( $row = $stmt->fetchObject() ){
			// print_r($row);
			if( !isset( $tags[ $row->id_document ] ) ){
				$tags[ $row->id_document ] = array();
			}
			
			$tags[ $row->id_document ][ $row->id_tag ] = $row;
		}
		
		// echo "<pre>";print_r($tags);echo "</pre>";
		
		# titles
		foreach ( array_keys( $this->docsMatches->results ) as $k ){
			$doc =  $this->docsMatches->results[ $k ];
			$this->_content .= '<div>'.$doc->title.'</div>';
		}
		
		# sentences
		foreach( array_keys( $this->frogMatches->sentences ) as $k ){
		
			$sentence =& $this->frogMatches->sentences[ $k ];
			$document = $this->frogMatches->documents[ $sentence->documentId ];
			
			
			
			$documentLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::setProperty('docs', array(  $sentence->documentId ) );
			$this->_content .= '
				<div class="grid_15 alpha omega tag">
					<div style="padding:1em;"><!-- '.$sentence->id.' '.$sentence->position.' -->'.$sentence->content.'</div>
					<div style="padding:1em;"><span class="is-untouchable-tag"><a href="'.$documentLink.'">'.$document['title'] .'</a></span>';
				// load document tags...
				if( !empty( $tags[$sentence->documentId] ) ){
					foreach( array_keys( $tags[$sentence->documentId] ) as $k ){
					
						$tag =& $tags[$sentence->documentId][$k]; // the tag
						
						$link = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::appendProperties( array( 'tags' => array($tag->id_tag) )  );
						$addLink = $_SERVER['REDIRECT_URL']."?".Dnst_Filter::appendProperties( array( 'tags' => array($tag->id_tag) )   );
				
						$this->_content .= '<span class="is-untouchable-tag"><a title="select only '.$tag->category.': '.$tag->content.'" href="'.$link.'" style="font-size:10px;color:black" title="'.$tag->content.'">'.ucut($tag->content).'</a>
						<a href="'.$addLink.'" title="add '.$tag->category.': '.$tag->content.' to current filters">+</a></span>';
					} 
				}
			$this->_content .= '</div></div>';
		}
		$this->_content .= '</div>';
		
		$this->_content .= '</div></div>';
		
		return parent::__toString();
	}
	
	
	
	
	
	
}