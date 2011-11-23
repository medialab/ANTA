<?php
/**
 * @package Ui_Crafts
 */
 
/**
 * A Cargo is basically a craft that collects items.
 * Every item may be found using its id:
 * 
 * $cargo = new Dnst_Ui_Crafts_Cargo("cargo", "a lot of items");
 * 
 * // add an item
 * $cargo->addItem( new Dnst_Ui_Items_Item( "id_item_1", array( "title" => "some properties") ) );
 * 
 * // output the item using its id
 * print_r( $this->id_item_1 );
 * 
 */
class Application_Model_Ui_Crafts_Texto extends Application_Model_Ui_Crafts_Craft {
	
	/** the preview of the text */
	protected $_text;
	
	/** the entities found into the text, if any... */
	protected $_entities = array();
	
	public function read( Application_Model_User $antaUser, Application_Model_Document $document ){
		$this->_text = Anta_Core::getText( $antaUser, $document );
	}
	
	public function setEntities( array $entities, $totalItems = 0 ){
		$this->_entities =& $entities;
		$this->totalEntities =  $totalItems;
	}
	
	public function __toString(){
		$txt = '';
		foreach( array_keys( $this->_entities ) as $k ){
			$txt .= '
				<div class="grid_7 alpha margin_1"><span style="font-size:12px">'.$this->_entities[ $k ]->content.'</span><p style="font-size:10px;padding:3px;">'.implode( "," , $this->_entities[ $k ]->tags ).'</p></div>
				<div class="grid_2 omega margin_1">'.number_format( $this->_entities[ $k ]->relevance, 2, '.', '').'</p></div>';
		}
		
		// some filter... a dnst filter should already be in place
		$actualOffset = Dnst_Filter::getProperty( "offset" );
		$actualLimit  = Dnst_Filter::getProperty( "limit" );
		
		$firstOffset     = $actualOffset > 0 ? 0: -1;
		$previousOffset  = $actualOffset > 0 ? max( 0, $actualOffset - $actualLimit ): -1;
		
		$nextOffset = $actualOffset + $actualLimit < $this->totalEntities? min( $this->totalEntities, $actualOffset + $actualLimit ): -1;
		$lastOffset = $actualOffset + $actualLimit < $this->totalEntities? $this->totalEntities - $actualLimit: -1;
		
		// $hiddenForm = new Application_Model_
		
		$this->_content = '
			<!-- hidden entity creation form 
			
			<div class="grid_24 alpha omega margin_1" style="display:none" id="sliding-form">
				<form name="form" id="new-entity-form" method="post">
				<div class="grid_12 prefix_1 alpha omega">
					<div class="grid_12 alpha omega" id="new-entity-log"></div>
					<div class="grid_6 alpha">
						<p class="margin_1">add new entity</p>
						<input type="text" name="new-entity-content" id="new-entity-content" class="margin_1 width_3">
					</div>
					<div class="grid_6 omega">
						<p class="margin_1">entity type</p>
						<input type="text" name="new-entity-type" id="new-entity-type" class="margin_1 width_2">
						<input type="button" class="margin_1" id="save-entity" value="save">
					</div>
					
				</div>
				</form>
			</div>
			
			 endof hidden form -->
			<div class="grid_12 prefix_1 suffix_1 alpha margin_1 text-preview">
				<pre class="grid_12  alpha omega">'.$this->_text.'</pre>
			</div>
			
			<div class="grid_9 omega margin_1 text-entities">
			    <div class="grid_1 alpha">'.
			    (
					$firstOffset != -1?
					'<a href="?'.Dnst_Filter::setProperty( "offset", 0 ).'">b</a>':
					'&nbsp;'
				).'</div>
				<div class="grid_1">'.
			    (
					$previousOffset != -1?
					'<a href="?'.Dnst_Filter::setProperty( "offset", $previousOffset).'">p</a>':
					'&nbsp;'
				).'</div>
				<div class="grid_5 centered"><h2 style="border-bottom:1px solid #ebebeb; padding-bottom:10px">'.count($this->_entities) ." ({$actualOffset} - ".max( $this->totalEntities, ($actualOffset + $actualLimit) ).") / ".$this->totalEntities.' entities</h2></div>
				<div class="grid_1 ">'.
			    (
					$nextOffset != -1?
					'<a href="?'.Dnst_Filter::setProperty( "offset", $nextOffset ).'">n</a>':
					'&nbsp;'
				).'</div>
				<div class="grid_1 omega">'.
			    (
					$lastOffset != -1?
					'<a href="?'.Dnst_Filter::setProperty( "offset", $lastOffset ).'">L</a>':
					'&nbsp;'
				).'</div>
				'.$txt.'
			</div>
		';
		
		return parent::__toString();
	}
	
}

?>
