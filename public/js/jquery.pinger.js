
var pinger = function( options ) {
		
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
		
		
		/** kill the ping */
		this.kill = function(){
			clearTimeout( this.__ping_timer);
			instance.hasBeenKilled = true;	
		}
		
		/** 
		 * just update settings
		 * @param updatedSettings
		 */
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
				return instance.settings.error("pinger '" +instance.settings.named + "' settings.url is null. Check your options object");
			} else	if (killPing) {
				return clearTimeout( instance.__ping_timer)
			};
			
			if( instance.hasBeenKilled ){
				instance.hasBeenKilled = false;
				clearTimeout( instance.__ping_timer);
				return instance.settings.error("pinger '" +instance.settings.named + "' has been killed. Call update() to restart");	
			}
			
			instance.settings.start( "pinger '" +instance.settings.named + "' looping..." );
			
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
							console.log("pinger '" +instance.settings.named + "' error code received, settings.clearTimeoutOnError = false, then reconnect...");
							instance.__ping_timer = setTimeout( instance.loop, instance.settings.timeOutOnError);
						}
					}
				});
			} catch (exc) {
				settings.error(exc);
			}
		}
		
		/** start engines */
		if (options) {
			$.extend(this.settings, options);
		}
		
		/** clean the ping */
		clearTimeout( this.__ping_timer);
		
		if ( this.settings.url == 'null') {
			return this.settings.error("pinger'" +this.settings.named + "' INIT settings.url is null. Check your options object");
		} else this.loop();
	};