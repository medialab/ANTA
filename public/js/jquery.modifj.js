;/**
 * modifj plugin
 * 
 * @author Daniele Guido <gui.daniele@gmail.com>
 */
/**
 * textwidth miniplugin, usage $(object).textWidth();
 */
 $.fn.textWidth = function(){
	  var html_org = $(this).html();
	  var html_calc = '<span>' + html_org + '</span>'
	  $(this).html(html_calc);
	  var width = $(this).find('span:first').width();
	  $(this).html(html_org);
	  return width;
 };
 
/**
 * basic usage:
 *   $('jq').modifj({url:'/url/to/send/stuff','varname':'entities'})
 * 
 * Once user clicks 'jq', the jq contents will be replaced by a nice <input />, whose value
 * would be jq text() content. Auto send an ajax request (json) to the given url:
 *   /url/to/send/stuff?paramName:{"eo18":"modified text of #eo18"}
 * 
 * The identifier (in this example "eo18" is the id css attribute of 'jq' object).
 * You can change all the attributes for the input field simply using an 'attributes' onbject
 * via settings:
 * 
 *   $('jq').modifj({
 *       url        : '/url/to/send/stuff',
 *       varname    : 'entities',
 *       timeOut    : 2500,
 *       attributes : {
 *           type  : 'text',
 *           class : 'some custom class' 
 *       } 
 *   }); 
 * 
 * The server script should send an error response like this:
 * 
 *   {"status":"ko","error":"custom error message"}
 * 
 * The ok response:
 * 
 *   {"status":"ok","":{"eo18":"modified text of #eo18","id24":"some text","id36":"..."}}
 * 
 * Note that the ok response *must* contain the modifjed object, in order to provide right info about the div to restore.
 */
(function( $ ){
	  $.fn.modifj = function( options ) {
		
		/**
		 * Default settings
		 */
		var settings = {
			url:"/",
			varname:'',
			undoImgUrl:'',
			timeOut:2500,
			attributes:{
			  type  : 'text',
			  name  : 'nomito',
			  class : 'chico',
			  style : 'font-size:13px; color:#000; background: yellow; border:0px transparent; padding:0px'
			},
			language: "en",
			i18n:{
				 en:{
					 successCallback: "saved modified entity",
					 connectionError: "connection error" 
				 }
			}
		};
		
		/**
		 * This object will be send to the given settings.url, json encoded.
		 * One object for each plugin call...
		 */
		var itemsToModifj = {};
		
		// merge options with settings
		if ( options ) { 
			$.extend( settings, options );
		}
		
		var currentTimeOut = 0;
		
		/**
		 * Function called every settings.timeOut milliseconds,
		 * by using a timeOut.
		 * If 'modified' object contains some object, send through an ajax call 
		 * (default GET) the json-version of the 'modified' object as param. Param name
		 * will be settings.varname
		 */
		var routine = function(){
			clearTimeout( currentTimeOut );
			
			if( busy ){
				// console.log( "*modifj*, user is modifing stuff..." );
				currentTimeOut = setTimeout( function(){ routine(); }, settings.timeOut );
				return;
			}
			var size = 0;
			for (key in itemsToModifj ) {
				if ( itemsToModifj.hasOwnProperty( key ) ) size++;
            }
            // console.log( "*modifj* routine listening..." );
            
            if( size == 0 ){
				currentTimeOut = setTimeout( function(){ routine(); }, settings.timeOut );
				return;
			}
            
            // console.log( "plugin modifj, modified #" + size + " objects: " );
				
			// start ajax call
			$.ajax({
				  url: settings.url,
				  dataType: 'json',
				  data: { entity: itemsToModifj},
				  success: successCallback,
				  error: errorCallback
			});
		}
		
		var busy = false;
		
		
		
		var errorCallback = function( data ){
			$().toastmessage( 'showErrorToast', "connection error" );
		}
		
		/**
		 * successCallback. Read and restore the target using the .html() method
		 */
		var successCallback = function( data ){
			
			console.log( settings.url + " response status: " + data.status );
			console.log( data );
			
			if( data.status == 'ok' ){
				// console.log( "\tajax autosave success!");
				
				for( var key in data.modifjed ){
					// console.log( key + " -> " + data.modifjed[ key ] );
					$( "#"+ key ).html( data.modifjed[ key ] );
					$( "#"+ key ).bind('click', clickCallback );
				}
				$().toastmessage( 'showSuccessToast', settings.i18n[ settings.language ].successCallback + ": "+ data.modifjed[ key ] );
				// stuff
				itemsToModifj = {};
				routine();
				return;
			}
			
			// console.log( "\tajax autosave failed: "+data.error );
			$().toastmessage( 'showErrorToast',  data.error );
			itemsToModifj = {};
			routine();
		}
		
		var reset = function(){
			
		}
		
		// start timing...one for each call to nicelyModified plugin?
		routine();
		
		var enableClickCallback = function( target ){
			target.bind('click', clickCallback );
		}
		
		var clickCallback = function( event ){
			     
				
			// unbound it please
			$(this).unbind('click');
						  
			// get the target
			var target = $(this);
			 
			// check the id,
			var identifier = target.attr('id');
			
			//  or throw an error if the id if is not set!
			if( identifier.length == 0 ){
				console.log("error: could not perform any action if the id attributre is not set!");
				return;
			}
			
			// read target text content
			var text = target.text();
			
			// create the input element and set some attributes, using both settings attributes and overriding with ours.
			var input = $('<input/>', settings.attributes);
			input.val( $.trim( text ) );
			// input.css( 'width', target.textWidth() );
			input.attr( 'id',  identifier);
			
			input.click( function(){
				busy = true;
			});
			
			input.blur( function(){
				busy = false;
			});
			
			input.keypress( function(){
				busy = true;
				input.change();
			});
			// change intput content, prepare stuff to be sent
			input.change( function(){
				
				itemsToModifj[ target.attr( 'id' ) ] = input.val();
				// console.log("changed id " + identifier  + " " + itemsToModifj[ target.attr( 'id' ) ] );
				
			});
			
			// substitute target content
			target.html( input );
			target.append('<img class="modifij-accept" rel="" title="" src="' + settings.acceptImgUrl+ '"/><img class="modifij-delete" src="' + settings.undoImgUrl+ '"/>');
			
			target.children( '.modifij-delete' ).first().css('cursor','pointer').click( function(){
				// get out of my businness
				delete itemsToModifj[ target.attr( 'id' ) ];
				target.html( text );
				target.click( function( e ){
				   e.preventDefault();
				   enableClickCallback( target );
				});
				// reset binding...
				// 
			});
			
			target.children( '.modifij-accept' ).first().css('cursor','pointer').click( function(){
				// send and save
				busy = false;
				itemsToModifj[ target.attr( 'id' ) ] = $.trim( input.val() );
				routine();
				// reset binding...
				// 
			});
			
		}
		
		
		
		// it activates himself with the click
		// return this.bind('click', clickabilly );
		enableClickCallback( this );
		//});
		
	  };
	})( jQuery );
