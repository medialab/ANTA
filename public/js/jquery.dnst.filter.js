/**
 * a javascript calss to understand filters
 */	
function DnstFilter(){
	this.url = "";
	this.namespace = "filters";
	
	this.filters = {};
	
	this.read = function(){
		return this.filters;
	}
	
	this.start = function( settings ){
		// default values
		$.extend( this.filters, settings);
		this.url = window.location.toString();
		
		//get the parameters
		var params = this.url.match(/\?(.+)$/);
		
		if( params == null ||  params.length < 2 ){
		// no params found
		return;
		}
		
		var rparams = Url.decode( params[1] );
		rparams = rparams.split("&");
		var params = {};
		
		// merge query param
		
		
		for ( var i in rparams ){
		couple = rparams[i].split("=");
		if( couple.length == 2 ){
			params[ couple[0] ] = couple[1];
		}
		}
		// get couples
		if( params[ this.namespace ] == undefined ) return;
		
		// jsonify
		try{
			
			var _filters = JSON.parse(  params[ this.namespace ] );
			
			if( params[ 'query' ] ){
				// deconstruct filters
				_filters[ 'query' ] = params[ 'query' ];
			}
			console.log( _filters );
			$.extend( this.filters, _filters);
			
		} catch( exception ){
			console.log( exception );
		}
		
		
	}
	
	this.setProperties = function( settings ){
		var __filter = $.extend( this.filters, settings);
		console.log( JSON.stringify( __filter ) );
		return "?" + this.namespace + "=" + Url.encode( JSON.stringify( __filter ) );
	}
};