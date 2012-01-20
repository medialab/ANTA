
	;/**
	 * my ping function, need jquery (ajax plugin and extend plugin as well)
	 * auto timing
	 * set via setting params
	 * 
	 */
	var killPing = false;
	
	var ping = function( options ) {
		
		
		
		var instance = this;
		
		this.__ping_timer = 0;
		this.hasBeenKilled = false;
		
		this.settings = {
			'named':'unnamed',
			'url': 'null',
			'data': {},
			'lines':18,
			'dataType': "json",
			'timeOut': 1340,
			'timeOutOnError': 5000,
			'success': function (message) {
				console.log(message)
			},
			'start': function (message, instance) {
				console.log(message)
			},
			'error': function (message) {
				console.log(message)
			},
			clearTimeoutOnError:true
		};
		if (options) {
			$.extend(this.settings, options);
		}
		clearTimeout( this.__ping_timer);
		
		this.kill = function(){
			clearTimeout( this.__ping_timer);
			instance.hasBeenKilled = true;	
		}
		
		this.update = function( updatedSettings ){
			if ( updatedSettings ) {
				$.extend( instance.settings, updatedSettings );
			}
		}
		
		/**
		 * the timeout function
		 */
		this.loop = function(){
			if ( instance.settings.url == 'null') {
				return instance.settings.error("ping() '" +instance.settings.named + "' settings.url is null. Check your options object");
			} else	if (killPing) {
				return clearTimeout( instance.__ping_timer)
			};
			
			if( instance.hasBeenKilled ){
				instance.hasBeenKilled = false;
				clearTimeout( instance.__ping_timer);
				return instance.settings.error("ping() '" +instance.settings.named + "' has been killed. Call update() to restart");	
			}
			
			// instance.settings.start( "ping() '" +instance.settings.named + "' looping..." );
			
			try {
				$.ajax({
					url: instance.settings.url,
					dataType: instance.settings.dataType,
					data: instance.settings.data,
					success: function (result) {
						// console.log("ping.loop.success");
						instance.settings.success(result);
						instance.__ping_timer = setTimeout(instance.loop, instance.settings.timeOut);
					},
					error: function (event) {
						instance.settings.error(event);
						if( instance.settings.clearTimeoutOnError ) {
							clearTimeout( instance.__ping_timer );
						} else {
							console.log("ping() '" +instance.settings.named + "' error code received, settings.clearTimeoutOnError = false, then reconnect...");
							instance.__ping_timer = setTimeout( instance.loop, instance.settings.timeOutOnError);
						}
					}
				});
			} catch (exc) {
				settings.error(exc);
			}
		}
		if ( instance.settings.url == 'null') {
			return instance.settings.error("ping() '" +instance.settings.named + "' INIT settings.url is null. Check your options object");
		} else	this.loop();
	};