
	;/**
	 * my ping function, need jquery (ajax plugin and extend plugin as well)
	 * auto timing
	 * set via setting params
	 * 
	 */
	var killPing = false;
	
	var ping = function( options ) {
		
		this.__ping_timer = 0;
		
		var instance = this;
		
		this.settings = {
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
				
		}
		
		/**
		 * the timeout function
		 */
		this.loop = function(){
			if ( instance.settings.url == 'null') {
				return instance.settings.error("ping() settings.url is null. Check your options object");
			} else	if (killPing) {
				return clearTimeout( instance.__ping_timer)
			};
			
			instance.settings.start('ping() called' );
			
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
							console.log("ping() error code received, settings.clearTimeoutOnError = false, then reconnect...");
							instance.__ping_timer = setTimeout( instance.loop, instance.settings.timeOutOnError);
						}
					}
				});
			} catch (exc) {
				settings.error(exc);
			}
		}
		
		this.loop();
	};