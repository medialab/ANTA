<?php
/**
 * @ package Ui
 */

/**
 * specific class for left menus for anta framework.
 * 
 */
class Ui_Boards_Documents extends Ui_Boards_TodaySpecial {

	public function init( $properties ){
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		
		// initialize menu items
		$this->items[ "documents.list" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/documents/list/user/'.  $this->user->cryptoId ,
			I18n_Json::get( 'all documents page' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		$this->items[ "add.documents" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/add/documents/user/'.  $this->user->cryptoId ,
			I18n_Json::get( 'upload documents' ),
			array( 
				'class' => 'alpha-li omega-li',
				'id' => "documents-upload"
			)
		);
		
		$this->items[ "documents.export-tags" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/documents/export-tags/user/'.  $this->user->cryptoId ,
			'<img src="'. Anta_Core::getBase() .'/images/document-export.png">'. I18n_Json::get( 'export tags of all' ),
			array( 
				'class' => 'alpha-li',
				
			)
		);
		
		$this->items[ "documents.import-tags" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/documents/import-tags/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'.Anta_Core::getBase() .'/images/document-import.png">'. I18n_Json::get( 'import tags for all from csv' )
		);
		
		$this->items[ "documents.import-from-google-spreadsheet" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/documents/import-from-google-spreadsheet/user/'.  $this->user->cryptoId .'/document/'. $this->document->cryptoId,
			'<img src="'.Anta_Core::getBase() .'/images/document-import.png">'. I18n_Json::get( 'import tags - GoogleDoc' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		$this->items[ "frog.index" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/frog/index/user/'.  $this->user->cryptoId,
			I18n_Json::get( 'visualize in timeline' ),
			array( 
				'class' => 'alpha-li',
				'id' => "frog.index"
			)
		);
		
		/*
		$this->items[ "api.reset" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/api/reset-documents-status/user/'.  $this->user->cryptoId,
			I18n_Json::get( 'set all docs "ready"' ),
			array( 
				'class' => 'alpha-li ajax-href',
				'id' => "api.reset"
			)
		);
		*/
		$this->_autoSelectItem();
	}
	
	/**
	 * Same as its parent, but inject some javascript code into the page to handle ajax-href
	 * behaviour( not ereal link, but api request)
	 */
	public function __toString(){
		?>
		<script type="text/javascript">
		
		// plugin packed, ajaxHref, plugin.ajaxHref.js to get the source code
		(function($){$.fn.ajaxHref=function(options){var executeHandler=function(event){event.preventDefault();settings.target=$(event.currentTarget);settings.previousContent=settings.target.html();settings.ajax.url=settings.target.attr('href');settings.target.html("loading...");$.ajax(settings.ajax);console.log(settings.ajax.url);}
		var resultHandler=function(event){console.log(event);console.log(settings.target);if(event.status=='ko'){return settings.ajax.error(event.error);}
		settings.target.html(settings.previousContent);}
		var errorHandler=function(event){console.log("error"+event);}
		var settings={ajax:{dataType:'json',success:resultHandler,error:errorHandler}};if(options){$.extend(settings,options);}
		return this.each(function(){var $this=$(this);$this.bind('click.ajaxHref',executeHandler);});};})(jQuery);
		
		(function( $ ){

			$.fn.ajaxQueue = function( options ) {  
				
				return this.each(function() {

					var $this = $(this);
					
					console.log( $this );
				});
				
			};
		
		})( jQuery );
		
		$(document).ready( function(){
			
			$(".ajax-href a").ajaxHref();
			
		});
		
		</script>
		<?php
		return parent::__toString();
	}
	
}
