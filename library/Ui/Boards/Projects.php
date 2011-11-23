<?php
/**
 * @ package Ui
 */

/**
 * specific class for left menus for anta framework.
 * 
 */
class Ui_Boards_Projects extends Ui_Boards_TodaySpecial {

	public function init( $properties ){
		foreach( $properties as $name=>$value){
			$this->$name = $value;
		}
		// initialize menu items
		$this->items[ "projects.create" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/projects/create/user/'.  $this->user->cryptoId ,
			I18n_Json::get( 'start new project' ),
			array( 
				'class' => 'omega-li' 
			)
		);
		
		$this->items[ "projects.list" ] = new Ui_Boards_TodaySpecials_Item(
			Anta_Core::getBase() .'/projects/list/user/'.  $this->user->cryptoId ,
			I18n_Json::get( 'your projects' ),
			array( 
				'class' => 'alpha-li' 
			)
		);
		
		
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
?>