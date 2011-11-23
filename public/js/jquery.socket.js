	/**
	 * this function open a remote call similar to getJson to an url and try catch the ajax request
	 * usage sample
	 * sock({
	 *			url: "http://www.google.com",
	 *		success: function(result){
	 *			console.log( result );
	 *			$().toastmessage( 'showSuccessToast', "exporting graph..." );
	 *		},
	 *		error: function(result){
	 *			$().toastmessage( 'showErrorToast', "connection troubles, received: " + result );
	 *		}
	 * });
	 */
	function sock(options){var settings={'url':'null','data':{},'dataType':"json",'start':null,'success':function(message){console.log("success",message)},'error':function(message){console.log(message)}};if(options){$.extend(settings,options);}
		if(settings.url=='null'){return settings.error("ping() settings.url is null. Check your options object");}
		if( typeof( settings.start ) == "function" ){settings.start();}
		try{$.ajax({url:settings.url,dataType:settings.dataType,data:settings.data,success:settings.success,error:settings.error});}catch(exc){settings.error(exc);}}
