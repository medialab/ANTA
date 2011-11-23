(function( $ ){

			$.fn.ajaxHref = function( options ) {  
				
				/**
				 * function bind to click event on ajaxHref instances
				 */
				var executeHandler = function( event ){
					
					event.preventDefault();
					
					// store the current instance caller
					settings.target = $(event.currentTarget);
					settings.previousContent = settings.target.html();
					
					// get url from href directly, just the first a element
					settings.ajax.url = settings.target.attr( 'href' );
					settings.target.html( "loading...");
					
					// add data params if any, overridable
					$.ajax(	settings.ajax );
					// console.log(  settings.ajax.url );
					
				}
				
				/**
				 * function bind to on success event
				 */
				var resultHandler = function( event ){
					// console.log(  event );
					// console.log( settings.target );
					
					if( event.status == 'ko' ){
						return settings.ajax.error( event.error );
					}
					
					settings.target.html( settings.previousContent );
					
				}
				
				/**
				 * function bind to on fail event
				 */
				var errorHandler = function( event ){
					// console.log( "error" + event );
					settings.target.html( event );
				}
				
				var settings = {
					ajax: {
						dataType: 'json',
						success: resultHandler,
						error: errorHandler
					}
				};
				
				if ( options ) { 
					$.extend( settings, options );
				}
				
				return this.each(function() {

					var $this = $(this);
					
					// bind click
					$this.bind( 'click.ajaxHref', executeHandler );
					
				});

		  };
		})( jQuery );