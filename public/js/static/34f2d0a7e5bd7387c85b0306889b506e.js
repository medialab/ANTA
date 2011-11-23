(function($) {
    $.fn.tipsy = function(options) {

        options = $.extend({}, $.fn.tipsy.defaults, options);
       
        return this.each(function() {
            
            var opts = $.fn.tipsy.elementOptions(this, options);
            
            $(this).mouseenter( function() {

                $.data(this, 'cancel.tipsy', true);

                var tip = $.data(this, 'active.tipsy');
				if (!tip) {
                    tip = $('<div class="tipsy"><div class="tipsy-inner"/></div>');
                    tip.css({position: 'absolute', zIndex: 100000});
                    $.data(this, 'active.tipsy', tip);
					
                }

                if ($(this).attr('title') || typeof($(this).attr('original-title')) != 'string') {
                    $(this).attr('original-title', $(this).attr('title') || '').removeAttr('title');
                }

                var title;
                if (typeof opts.title == 'string') {
					// htm injection
					
                    title = $(this).attr(opts.title == 'title' ? 'original-title' : opts.title);
					title = title.replace("_b__", "</b>" ).replace( "__b_", "<b>");
                } else if (typeof opts.title == 'function') {
                    title = opts.title.call(this);
                }

                tip.find('.tipsy-inner')[opts.html ? 'html' : 'text'](title || opts.fallback);

                var pos = $.extend({}, $(this).offset(), {width: this.offsetWidth, height: this.offsetHeight});
                tip.get(0).className = 'tipsy'; // reset classname in case of dynamic gravity
                tip.remove().css({top: 0, left: 0, visibility: 'hidden', display: 'block'}).appendTo(document.body);
                var actualWidth = tip[0].offsetWidth, actualHeight = tip[0].offsetHeight;
                var gravity = (typeof opts.gravity == 'function') ? opts.gravity.call(this) : opts.gravity;

                switch (gravity.charAt(0)) {
                    case 'n':
                        tip.css({top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2}).addClass('tipsy-north');
                        break;
                    case 's':
                        tip.css({top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2}).addClass('tipsy-south');
                        break;
                    case 'e':
                        tip.css({top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth}).addClass('tipsy-east');
                        break;
                    case 'w':
                        tip.css({top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width}).addClass('tipsy-west');
                        break;
                }

                if (opts.fade) {
                    tip.css({opacity: 0, display: 'block', visibility: 'visible'}).animate({opacity: 0.8});
                } else {
                    tip.css({visibility: 'visible'});
                }

            });
			
			 $(this).mouseleave( function() {
                $.data(this, 'cancel.tipsy', false);
                var self = this;
                setTimeout(function() {
                    if ($.data(this, 'cancel.tipsy')) return;
                    var tip = $.data(self, 'active.tipsy');
					if( tip == null ) return;
                    if (opts.fade) {
                        tip.stop().fadeOut(function() { $(this).remove(); });
                    } else {
                        tip.remove();
                    }
                }, 100);

            });
            
        });
        
    };
    
    // Overwrite this method to provide options on a per-element basis.
    // For example, you could store the gravity in a 'tipsy-gravity' attribute:
    // return $.extend({}, options, {gravity: $(ele).attr('tipsy-gravity') || 'n' });
    // (remember - do not modify 'options' in place!)
    $.fn.tipsy.elementOptions = function(ele, options) {
        return $.metadata ? $.extend({}, options, $(ele).metadata()) : options;
    };
    
    $.fn.tipsy.defaults = {
        fade: false,
        fallback: '',
        gravity: 'n',
        html: false,
        title: 'title'
    };
    
    $.fn.tipsy.autoNS = function() {
        return $(this).offset().top > ($(document).scrollTop() + $(window).height() / 2) ? 's' : 'n';
    };
    
    $.fn.tipsy.autoWE = function() {
        return $(this).offset().left > ($(document).scrollLeft() + $(window).width() / 2) ? 'e' : 'w';
    };
    
})(jQuery);
;/*
 * Copyright 2010 akquinet
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 *  This JQuery Plugin will help you in showing some nice Toast-Message like notification messages. The behavior is
 *  similar to the android Toast class.
 *  You have 4 different toast types you can show. Each type comes with its own icon and colored border. The types are:
 *  - notice
 *  - success
 *  - warning
 *  - error
 *
 *  The following methods will display a toast message:
 *
 *   $().toastmessage('showNoticeToast', 'some message here');
 *   $().toastmessage('showSuccessToast', "some message here");
 *   $().toastmessage('showWarningToast', "some message here");
 *   $().toastmessage('showErrorToast', "some message here");
 *
 *   // user configured toastmessage:
 *   $().toastmessage('showToast', {
 *      text     : 'Hello World',
 *      sticky   : true,
 *      position : 'top-right',
 *      type     : 'success',
 *      close    : function () {console.log("toast is closed ...");}
 *   });
 *
 *   To see some more examples please have a look into the Tests in src/test/javascript/ToastmessageTest.js
 *
 *   For further style configuration please see corresponding css file: jquery-toastmessage.css
 *
 *   This plugin is based on the jquery-notice (http://sandbox.timbenniks.com/projects/jquery-notice/)
 *   but is enhanced in several ways:
 *
 *   configurable positioning
 *   convenience methods for different message types
 *   callback functionality when closing the toast
 *   included some nice free icons
 *   reimplemented to follow jquery plugin good practices rules
 *
 *   Author: Daniel Bremer-Tonn
**/
(function($)
{
	var settings = {
				inEffect: 			{opacity: 'show'},	// in effect
				inEffectDuration: 	600,				// in effect duration in miliseconds
				stayTime: 			3000,				// time in miliseconds before the item has to disappear
				text: 				'',					// content of the item. Might be a string or a jQuery object. Be aware that any jQuery object which is acting as a message will be deleted when the toast is fading away.
				sticky: 			false,				// should the toast item sticky or not?
				type: 				'notice', 			// notice, warning, error, success
                position:           'top-right',        // top-left, top-center, top-right, middle-left, middle-center, middle-right ... Position of the toast container holding different toast. Position can be set only once at the very first call, changing the position after the first call does nothing
                closeText:          '',                 // text which will be shown as close button, set to '' when you want to introduce an image via css
                close:              null                // callback function when the toastmessage is closed
            };

    var methods = {
        init : function(options)
		{
			if (options) {
                $.extend( settings, options );
            }
		},

        showToast : function(options)
		{
			var localSettings = {};
            $.extend(localSettings, settings, options);

			// declare variables
            var toastWrapAll, toastItemOuter, toastItemInner, toastItemClose, toastItemImage;

			toastWrapAll	= (!$('.toast-container').length) ? $('<div></div>').addClass('toast-container').addClass('toast-position-' + localSettings.position).appendTo('body') : $('.toast-container');
			toastItemOuter	= $('<div></div>').addClass('toast-item-wrapper');
			toastItemInner	= $('<div></div>').hide().addClass('toast-item toast-type-' + localSettings.type).appendTo(toastWrapAll).html($('<p>').append (localSettings.text)).animate(localSettings.inEffect, localSettings.inEffectDuration).wrap(toastItemOuter);
			toastItemClose	= $('<div></div>').addClass('toast-item-close').prependTo(toastItemInner).html(localSettings.closeText).click(function() { $().toastmessage('removeToast',toastItemInner, localSettings) });
			toastItemImage  = $('<div></div>').addClass('toast-item-image').addClass('toast-item-image-' + localSettings.type).prependTo(toastItemInner);

            if(navigator.userAgent.match(/MSIE 6/i))
			{
		    	toastWrapAll.css({top: document.documentElement.scrollTop});
		    }

			if(!localSettings.sticky)
			{
				setTimeout(function()
				{
					$().toastmessage('removeToast', toastItemInner, localSettings);
				},
				localSettings.stayTime);
			}
            return toastItemInner;
		},

        showNoticeToast : function (message)
        {
            var options = {text : message, type : 'notice'};
            return $().toastmessage('showToast', options);
        },

        showSuccessToast : function (message)
        {
            var options = {text : message, type : 'success'};
            return $().toastmessage('showToast', options);
        },

        showErrorToast : function (message)
        {
            var options = {text : message, type : 'error'};
            return $().toastmessage('showToast', options);
        },

        showWarningToast : function (message)
        {
            var options = {text : message, type : 'warning'};
            return $().toastmessage('showToast', options);
        },

		removeToast: function(obj, options)
		{
			obj.animate({opacity: '0'}, 600, function()
			{
				obj.parent().animate({height: '0px'}, 300, function()
				{
					obj.parent().remove();
				});
			});
            // callback
            if (options && options.close !== null)
            {
                options.close();
            }
		}
	};

    $.fn.toastmessage = function( method ) {

        // Method calling logic
        if ( methods[method] ) {
          return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
          return methods.init.apply( this, arguments );
        } else {
          $.error( 'Method ' +  method + ' does not exist on jQuery.toastmessage' );
        }
    };

})(jQuery);;/*
 * jQuery Color Animations v@VERSION
 * http://jquery.org/
 *
 * Copyright 2011 John Resig
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jqery.org/license
 *
 * Date: @DATE
 */

(function( jQuery, undefined ){
	var stepHooks = "backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color outlineColor".split(" "),

		// plusequals test for += 100 -= 100
		rplusequals = /^([\-+])=\s*(\d+\.?\d*)/,
		// a set of RE's that can match strings and generate color tuples.
		stringParsers = [{
				re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d+(?:\.\d+)?)\s*)?\)/,
				parse: function( execResult ) {
					return [
						execResult[ 1 ],
						execResult[ 2 ],
						execResult[ 3 ],
						execResult[ 4 ]
					];
				}
			}, {
				re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d+(?:\.\d+)?)\s*)?\)/,
				parse: function( execResult ) {
					return [
						2.55 * execResult[1],
						2.55 * execResult[2],
						2.55 * execResult[3],
						execResult[ 4 ]
					];
				}
			}, {
				re: /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/,
				parse: function( execResult ) {
					return [
						parseInt( execResult[ 1 ], 16 ),
						parseInt( execResult[ 2 ], 16 ),
						parseInt( execResult[ 3 ], 16 )
					];
				}
			}, {
				re: /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/,
				parse: function( execResult ) {
					return [
						parseInt( execResult[ 1 ] + execResult[ 1 ], 16 ),
						parseInt( execResult[ 2 ] + execResult[ 2 ], 16 ),
						parseInt( execResult[ 3 ] + execResult[ 3 ], 16 )
					];
				}
			}, {
				re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d+(?:\.\d+)?)\s*)?\)/,
				space: "hsla",
				parse: function( execResult ) {
					return [
						execResult[1],
						execResult[2] / 100,
						execResult[3] / 100,
						execResult[4]
					];
				}
			}],

		// jQuery.Color( )
		color = jQuery.Color = function( color, green, blue, alpha ) {
			return new jQuery.Color.fn.parse( color, green, blue, alpha );
		},
		spaces = {
			rgba: {
				cache: "_rgba",
				props: {
					red: {
						idx: 0,
						min: 0,
						max: 255,
						type: "int",
						empty: true
					},
					green: {
						idx: 1,
						min: 0,
						max: 255,
						type: "int",
						empty: true
					},
					blue: {
						idx: 2,
						min: 0,
						max: 255,
						type: "int",
						empty: true
					},
					alpha: {
						idx: 3,
						min: 0,
						max: 1,
						type: "float",
						def: 1
					}
				}
			},
			hsla: {
				cache: "_hsla",
				props: {
					hue: {
						idx: 0,
						mod: 360,
						type: "int",
						empty: true
					},
					saturation: {
						idx: 1,
						min: 0,
						max: 1,
						type: "float",
						empty: true
					},
					lightness: {
						idx: 2,
						min: 0,
						max: 1,
						type: "float",
						empty: true
					}
				}
			}
		},
		rgbaspace = spaces.rgba.props,
		support = color.support = {},

		// colors = jQuery.Color.names
		colors,

		// local aliases of functions called often
		each = jQuery.each;

	spaces.hsla.props.alpha = rgbaspace.alpha;

	function clamp( value, prop, alwaysAllowEmpty ) {
		if ( ( prop.empty || alwaysAllowEmpty ) && value == null ) {
			return null;
		}
		if ( prop.def && value == null ) {
			return prop.def;
		}
		if ( prop.type === "int" ) {
			value = ~~value;
		}
		if ( prop.mod ) {
			value = ( value < 0 ? value + prop.mod * ( 1 + ~~( -value / prop.mod ) ) : value ) % prop.mod;
		}
		if ( prop.type === "float" ) {
			value = parseFloat( value );
		}
		if ( jQuery.isNaN( value ) ) {
			value = prop.def;
		}
		return prop.min > value ? prop.min : prop.max < value ? prop.max : value;
	}

	color.fn = color.prototype = {
		constructor: color,
		parse: function( red, green, blue, alpha ) {
			if ( red === undefined ) {
				this._rgba = [null,null,null,null];
				return this;
			}
			if ( red instanceof jQuery || red.nodeType ) {
				red = red instanceof jQuery ? red.css( green ) : jQuery( red ).css( green );
				green = undefined;
			}

			var inst = this,
				type = jQuery.type( red ),
				rgba = this._rgba = [],
				source;

			// more than 1 argument specified - assume ( red, green, blue, alpha )
			if ( green !== undefined ) {
				red = [ red, green, blue, alpha ];
				type = "array";
			}

			if ( type === "string" ) {
				red = red.toLowerCase();
				each( stringParsers, function( i, parser ) {
					var match = parser.re.exec( red ),
						values = match && parser.parse( match ),
						parsed,
						spaceName = parser.space || "rgba",
						cache = spaces[ spaceName ].cache;


					if ( values ) {
						parsed = inst[ spaceName ]( values );
						if ( spaceName != "rgba" ) {
							inst[ cache ] = parsed[ cache ];
						}
						rgba = inst._rgba = parsed._rgba;

						// exit each( stringParsers ) here because we found ours
						return false;
					}
				});

				// Found a stringParser that handled it
				if ( rgba.length !== 0 ) {

					// if this came from a parsed string, force "transparent" when alpha is 0
					// chrome, (and maybe others) return "transparent" as rgba(0,0,0,0)
					if ( Math.max.apply( Math, rgba ) === 0 ) {
						$.extend( rgba, colors.transparent );
					}
					return this;
				}

				// named colors / default - filter back through parse function
				red = colors[ red ] || colors._default;
				return this.parse( red );
			}

			if ( type === "array" ) {
				each( rgbaspace, function( key, prop ) {
					rgba[ prop.idx ] = clamp( red[ prop.idx ], prop );
				});
				return this;
			}

			if ( type === "object" ) {
				if ( red instanceof color ) {
					each( spaces, function( spaceName, space ) {
						if ( red[ space.cache ] ) {
							inst[ space.cache ] = red[ space.cache ].slice();
						}
					});
				} else {
					each( spaces, function( spaceName, space ) {
						each( space.props, function( key, prop ) {
							var cache = space.cache;

							// if the cache doesn't exist, and we know how to convert
							if ( !inst[ cache ] && space.to ) {

								// if the value was null, we don't need to copy it
								// if the key was alpha, we don't need to copy it either
								if ( red[ key ] == null || key === "alpha") {
									return;
								}
								inst[ cache ] = space.to( inst._rgba );
							}

							// this is the only case where we allow nulls for ALL properties.
							// call clamp with alwaysAllowEmpty
							inst[ cache ][ prop.idx ] = clamp( red[ key ], prop, true );
						});
					});
				}
				return this;
			}
		},
		is: function( compare ) {
			var is = color( compare ),
				same = true,
				that = this;

			each( spaces, function( _, space ) {
				var isCache = is[ space.cache ],
					localCache;
				if (isCache) {
					localCache = that[ space.cache ] || space.to && space.to( that._rgba ) || [];
					each( space.props, function( _, prop ) {
						if ( isCache[ prop.idx ] != null ) {
							same = ( isCache[ prop.idx ] == localCache[ prop.idx ] );
							return same;
						}
					});
				}
				return same;
			}); 
			return same;
		},
		_space: function() {
			var used = [],
				inst = this;
			each( spaces, function( spaceName, space ) {
				if ( inst[ space.cache ] ) {
					used.push( spaceName );
				}
			});
			return used.pop();
		},
		transition: function( other, distance ) {
			var end = color( other ),
				spaceName = end._space(),
				space = spaces[ spaceName ],
				start = this[ space.cache ] || space.to( this._rgba ),
				arr = start.slice();

			end = end[ space.cache ];
			each( space.props, function( key, prop ) {
				var s = start[ prop.idx ],
					e = end[ prop.idx ];

				// if null, don't override start value
				if ( e === null ) {
					return;
				}
				// if null - use end
				if ( s === null ) {
					arr[ prop.idx ] = e;
				} else {
					if ( prop.mod ) {
						if ( e - s > prop.mod / 2 ) {
							s += prop.mod;
						} else if ( s - e > prop.mod / 2 ) {
							s -= prop.mod;
						}
					}
					arr[ prop.idx ] = clamp( ( e - s ) * distance + s, prop );
				}
			});
			return this[ spaceName ]( arr );
		},
		blend: function( opaque ) {
			// if we are already opaque - return ourself
			if ( this._rgba[ 3 ] === 1 ) {
				return this;
			}

			var rgb = this._rgba.slice(),
				a = rgb.pop(),
				blend = color( opaque )._rgba;

			return color( jQuery.map( rgb, function( v, i ) {
				return ( 1 - a ) * blend[ i ] + a * v;
			}));
		},
		toRgbaString: function() {
			var rgba = jQuery.map( this._rgba, function( v, i ) {
				return v == null ? ( i > 2 ? 1 : 0 ) : v;
			});

			if ( rgba[ 3 ] === 1 ) {
				rgba.length = 3;
			}

			return ( rgba.length === 3 ? "rgb(" : "rgba(" ) + rgba.join(",") + ")";
		},
		toHslaString: function() {
			var hsla = jQuery.map( this.hsla(), function( v, i ) {
				v = v == null ? ( i > 2 ? 1 : 0 ) : v;
				if ( i === 1 || i === 2 ) {
					v = Math.round( v * 100 ) + "%";
				}
				return v;
			});
			if ( hsla[ 3 ] === 1 ) {
				hsla.length = 3;
			}
			return ( hsla.length === 3 ? "hsl(" : "hsla(" ) + hsla.join(",") + ")";
		},
		toHexString: function( includeAlpha ) {
			var rgba = this._rgba.slice();
			if ( !includeAlpha ) {
				rgba.length = 3;
			}

			return "#" + jQuery.map( rgba, function( v, i ) {
				var fac = ( i === 3 ) ? 255 : 1,
					hex = ( v * fac ).toString( 16 );

				return hex.length === 1 ? "0" + hex : hex.substr(0, 2);
			}).join("");
		},
		toString: function() {
			if ( this._rgba[ 3 ] === 0 ) {
				return "transparent";
			}
			return this.toRgbaString();
		}
	};
	color.fn.parse.prototype = color.fn;

	// hsla conversions adapted from:
	// http://www.google.com/codesearch/p#OAMlx_jo-ck/src/third_party/WebKit/Source/WebCore/inspector/front-end/Color.js&d=7&l=193

	function hue2rgb( p, q, h ) {
		h = ( h + 1 ) % 1;
		if ( h * 6 < 1 ) {
			return p + (q - p) * 6 * h;
		}
		if ( h * 2 < 1) {
			return q;
		}
		if ( h * 3 < 2 ) {
			return p + (q - p) * ((2/3) - h) * 6;
		}
		return p;
	}

	spaces.hsla.to = function ( rgba ) {
		if ( rgba[ 0 ] == null || rgba[ 1 ] == null || rgba[ 2 ] == null ) {
			return [ null, null, null, rgba[ 3 ] ];
		}
		var r = rgba[ 0 ] / 255,
			g = rgba[ 1 ] / 255,
			b = rgba[ 2 ] / 255,
			a = rgba[ 3 ],
			max = Math.max( r, g, b ),
			min = Math.min( r, g, b ),
			diff = max - min,
			add = max + min,
			l = add * 0.5,
			h, s;

		if ( min === max ) {
			h = 0;
		} else if ( r === max ) {
			h = ( 60 * ( g - b ) / diff ) + 360;
		} else if ( g === max ) {
			h = ( 60 * ( b - r ) / diff ) + 120;
		} else {
			h = ( 60 * ( r - g ) / diff ) + 240;
		}

		if ( l === 0 || l === 1 ) {
			s = l;
		} else if ( l <= 0.5 ) {
			s = diff / add;
		} else {
			s = diff / ( 2 - add );
		}
		return [ Math.round(h) % 360, s, l, a == null ? 1 : a ];
	};

	spaces.hsla.from = function ( hsla ) {
		if ( hsla[ 0 ] == null || hsla[ 1 ] == null || hsla[ 2 ] == null ) {
			return [ null, null, null, hsla[ 3 ] ];
		}
		var h = hsla[ 0 ] / 360,
			s = hsla[ 1 ],
			l = hsla[ 2 ],
			a = hsla[ 3 ],
			q = l <= 0.5 ? l * ( 1 + s ) : l + s - l * s,
			p = 2 * l - q,
			r, g, b;

		return [
			Math.round( hue2rgb( p, q, h + ( 1 / 3 ) ) * 255 ),
			Math.round( hue2rgb( p, q, h ) * 255 ),
			Math.round( hue2rgb( p, q, h - ( 1 / 3 ) ) * 255 ),
			a
		];
	};


	each( spaces, function( spaceName, space ) {
		var props = space.props,
			cache = space.cache,
			to = space.to,
			from = space.from;

		// makes rgba() and hsla()
		color.fn[ spaceName ] = function( value ) {

			// generate a cache for this space if it doesn't exist
			if ( to && !this[ cache ] ) {
				this[ cache ] = to( this._rgba );
			}
			if ( value === undefined ) {
				return this[ cache ].slice();
			}

			var type = jQuery.type( value ),
				arr = ( type === "array" || type === "object" ) ? value : arguments,
				local = this[ cache ].slice(),
				ret;

			each( props, function( key, prop ) {
				var val = arr[ type === "object" ? key : prop.idx ];
				if ( val == null ) {
					val = local[ prop.idx ];
				}
				local[ prop.idx ] = clamp( val, prop );
			});

			if ( from ) {
				ret = color( from( local ) );
				ret[ cache ] = local;
				return ret;
			} else {
				return color( local );
			}
		};

		// makes red() green() blue() alpha() hue() saturation() lightness()
		each( props, function( key, prop ) {
			// alpha is included in more than one space
			if ( color.fn[ key ] ) {
				return;
			}
			color.fn[ key ] = function( value ) {
				var vtype = jQuery.type( value ),
					fn = ( key === 'alpha' ? ( this._hsla ? 'hsla' : 'rgba' ) : spaceName ),
					local = this[ fn ](),
					cur = local[ prop.idx ],
					match;

				if ( vtype === "undefined" ) {
					return cur;
				}

				if ( vtype === "function" ) {
					value = value.call( this, cur );
					vtype = jQuery.type( value );
				}
				if ( value == null && prop.empty ) {
					return this;
				}
				if ( vtype === "string" ) {
					match = rplusequals.exec( value );
					if ( match ) {
						value = cur + parseFloat( match[ 2 ] ) * ( match[ 1 ] === "+" ? 1 : -1 );
					}
				}
				local[ prop.idx ] = value;
				return this[ fn ]( local );
			};
		});
	});

	// add .fx.step functions
	each( stepHooks, function( i, hook ) {
		jQuery.cssHooks[ hook ] = {
			set: function( elem, value ) {
				value = color( value );
				if ( !support.rgba && value._rgba[ 3 ] !== 1 ) {
					var curElem = hook === "backgroundColor" ? elem.parentNode : elem,
						backgroundColor;
					do {
						backgroundColor = jQuery.curCSS( curElem, "backgroundColor" );
						if ( backgroundColor !== "" && backgroundColor !== "transparent" ) {
							break;
						}

					} while ( ( elem = elem.parentNode ) && elem.style );

					value = value.blend( color( backgroundColor || "_default" ) );
				}

				value = value.toRgbaString();

				elem.style[ hook ] = value;
			}
		};
		jQuery.fx.step[ hook ] = function( fx ) {
			if ( !fx.colorInit ) {
				fx.start = color( fx.elem, hook );
				fx.end = color( fx.end );
				fx.colorInit = true;
			}
			jQuery.cssHooks[ hook ].set( fx.elem, fx.start.transition( fx.end, fx.pos ) );
		};
	});

	// detect rgba support
	jQuery(function() {
		var div = document.createElement( "div" ),
			div_style = div.style;

		div_style.cssText = "background-color:rgba(150,255,150,.5)";
		support.rgba = div_style.backgroundColor.indexOf( "rgba" ) > -1;
	});

	// Some named colors to work with
	// From Interface by Stefan Petre
	// http://interface.eyecon.ro/
	colors = jQuery.Color.names = {
		aqua: "#00ffff",
		azure: "#f0ffff",
		beige: "#f5f5dc",
		black: "#000000",
		blue: "#0000ff",
		brown: "#a52a2a",
		cyan: "#00ffff",
		darkblue: "#00008b",
		darkcyan: "#008b8b",
		darkgrey: "#a9a9a9",
		darkgreen: "#006400",
		darkkhaki: "#bdb76b",
		darkmagenta: "#8b008b",
		darkolivegreen: "#556b2f",
		darkorange: "#ff8c00",
		darkorchid: "#9932cc",
		darkred: "#8b0000",
		darksalmon: "#e9967a",
		darkviolet: "#9400d3",
		fuchsia: "#ff00ff",
		gold: "#ffd700",
		green: "#008000",
		indigo: "#4b0082",
		khaki: "#f0e68c",
		lightblue: "#add8e6",
		lightcyan: "#e0ffff",
		lightgreen: "#90ee90",
		lightgrey: "#d3d3d3",
		lightpink: "#ffb6c1",
		lightyellow: "#ffffe0",
		lime: "#00ff00",
		magenta: "#ff00ff",
		maroon: "#800000",
		navy: "#000080",
		olive: "#808000",
		orange: "#ffa500",
		pink: "#ffc0cb",
		purple: "#800080",
		violet: "#800080",
		red: "#ff0000",
		silver: "#c0c0c0",
		white: "#ffffff",
		yellow: "#ffff00",
		transparent: [ null, null, null, 0 ],
		_default: "#ffffff"
	};
})( jQuery );;	/**
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
;/**
 * jQuery Spin 1.1.1
 *
 * Copyright (c) 2009 Naohiko MORI
 * Dual licensed under the MIT and GPL licenses.
 *
 **/
(function($){
  var calcFloat = {
    get: function(num){
      var num = num.toString();
      if(num.indexOf('.')==-1) return[0, eval(num)];
      var nn = num.split('.');
      var po = nn[1].length;
      var st = nn.join('');
      var sign = '';
      if(st.charAt(0)=='-'){
        st = st.substr(1);
        sign = '-';
      }
      for(var i=0; i<st.length; ++i) if(st.charAt(0)=='0') st=st.substr(1, st.length);
      st = sign + st;
      return [po, eval(st)];
    },
    getInt: function(num, figure){
      var d = Math.pow(10, figure);
      var n = this.get(num);
      var v1 = eval('num * d');
      var v2 = eval('n[1] * d');
      if(this.get(v1)[1]==v2) return v1;
      return(n[0]==0 ? v1 : eval(v2 + '/Math.pow(10, n[0])'));
    },
    sum: function(v1, v2){
      var n1 = this.get(v1);
      var n2 = this.get(v2);
      var figure = (n1[0] > n2[0] ? n1[0] : n2[0]);
      v1 = this.getInt(v1, figure);
      v2 = this.getInt(v2, figure);
      return eval('v1 + v2')/Math.pow(10, figure);
    }
  };
  $.extend({
    spin: {
      imageBasePath: '/img/spin/',
      spinBtnImage: 'spin-button.png',
      spinUpImage: 'spin-up.png',
      spinDownImage: 'spin-down.png',
      interval: 1,
      max: null,
      min: null,
      timeInterval: 500,
      timeBlink: 200,
      btnClass: null,
      btnCss: {cursor: 'pointer', padding: 0, margin: 0, verticalAlign: 'middle'},
      txtCss: {marginRight: 0, paddingRight: 0},
      lock: false,
      decimal: null,
      beforeChange: null,
      changed: null,
      buttonUp: null,
      buttonDown: null
    }
  });
  $.fn.extend({
    spin: function(o){
      return this.each(function(){
				o = o || {};
				var opt = {};
				$.each($.spin, function(k,v){
					opt[k] = (typeof o[k]!='undefined' ? o[k] : v);
				});
        
        var txt = $(this);
        
        var spinBtnImage = opt.imageBasePath+opt.spinBtnImage;
        var btnSpin = new Image();
        btnSpin.src = spinBtnImage;
        var spinUpImage = opt.imageBasePath+opt.spinUpImage;
        var btnSpinUp = new Image();
        btnSpinUp.src = spinUpImage;
        var spinDownImage = opt.imageBasePath+opt.spinDownImage;
        var btnSpinDown = new Image();
        btnSpinDown.src = spinDownImage;
        
        var btn = $(document.createElement('img'));
        btn.attr('src', spinBtnImage);
        if(opt.btnClass) btn.addClass(opt.btnClass);
        if(opt.btnCss) btn.css(opt.btnCss);
        if(opt.txtCss) txt.css(opt.txtCss);
        txt.after(btn);
				if(opt.lock){
					txt.focus(function(){txt.blur();});
        }
        
        function spin(vector){
          var val = txt.val();
          var org_val = val;
          if(opt.decimal) val=val.replace(opt.decimal, '.');
          if(!isNaN(val)){
            val = calcFloat.sum(val, vector * opt.interval);
            if(opt.min!==null && val<opt.min) val=opt.min;
            if(opt.max!==null && val>opt.max) val=opt.max;
            if(val != txt.val()){
              if(opt.decimal) val=val.toString().replace('.', opt.decimal);
              var ret = ($.isFunction(opt.beforeChange) ? opt.beforeChange.apply(txt, [val, org_val]) : true);
              if(ret!==false){
                txt.val(val);
                if($.isFunction(opt.changed)) opt.changed.apply(txt, [val]);
                txt.change();
                src = (vector > 0 ? spinUpImage : spinDownImage);
                btn.attr('src', src);
                if(opt.timeBlink<opt.timeInterval)
                  setTimeout(function(){btn.attr('src', spinBtnImage);}, opt.timeBlink);
              }
            }
          }
          if(vector > 0){
            if($.isFunction(opt.buttonUp)) opt.buttonUp.apply(txt, [val]);
          }else{
            if($.isFunction(opt.buttonDown)) opt.buttonDown.apply(txt, [val]);
          }
        }
        
        btn.mousedown(function(e){
          var pos = e.pageY - btn.offset().top;
          var vector = (btn.height()/2 > pos ? 1 : -1);
          (function(){
            spin(vector);
            var tk = setTimeout(arguments.callee, opt.timeInterval);
            $(document).one('mouseup', function(){
              clearTimeout(tk); btn.attr('src', spinBtnImage);
            });
          })();
          return false;
        });
      });
    }
  });
})(jQuery);
;
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
	};;