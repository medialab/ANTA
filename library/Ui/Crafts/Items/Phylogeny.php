<?php
/**
 * @package Ui_Crafts_Items
 */
 
/**
 * describe an entity Application_Model_SubEntity, its parents and its tags  Application_Model_Tag
 * 
 */
 class Ui_Crafts_Items_Phylogeny extends Ui_Crafts_Item {
	
	/**
	 * the bound Application_Model_Entity instance to be displayed */
	public $entity;
	
	
	/**
	 * the user, by reference
	 */
	public $user;
 
	public function __construct( Application_Model_SubEntity $entity, $user ){
		parent::__construct( "uzieizuieui".$entity->id );
		$this->entity = $entity;
		$this->user =& $user;
		
		
	}
 
	public function __toString(){
		
		?>
		<style>
			ul {
			display: block;
			list-style-type: none;
			-webkit-margin-before: 1em;
			-webkit-margin-after: 1em;
			-webkit-margin-start: 0px;
			-webkit-margin-end: 0px;
			-webkit-padding-start: 40px;
			}
			
			
			ul.listed-tags li {
				float: left;
				width: auto;
				margin: 0px 4px 4px 0px;
				height: 17px;
				list-style: none;
				vertical-align:middle;
			}
			
			ul.listed-tags li img{
				diplay:inline;
			}
			
		</style>
		<?php
		
		// get world list
		$wl = new Anta_Utils_WordList( $this->entity->content );
		$wl->applyStem( "en" );
		
		$tags = array();
		// get tags
		foreach( $this->entity->tags as $tag ){
			$tags[] = new Ui_Crafts_Items_Tag( $tag, $this->user );
		}
		$words = Application_Model_OccurrencesMapper::getNeighbourhood( $this->user, $wl->words, 0, 50 );
		
		$tagCloud = array();
		/*
		foreach( $words->results as $word ){
			
			$fontSize= .8 + $word->f / $words->max_frequency;
			
			$tagCloud[] = '<span class="is-untouchable-tag" style="font-size:'.$fontSize.'em">'.$word->word."</span>";
		}
		*/
		return '
		<div class="grid_23 prefix_1 alpha omega margin_1">
			<ul class="listed-tags">
			  <li>'.implode("</li><li>", $tags).'</li>
			</ul>
		</div>
		<div class="grid_23 prefix_1 alpha omega margin_1" style="border-top: 1px solid #eaeaea">
			
		</div>
		
		';
	}
 }
?>
