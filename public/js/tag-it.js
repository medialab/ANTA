(function($) {

	$.fn.tagit = function(options) {

		var el = this;
		var identifier = this.attr( "id" );
		const BACKSPACE		= 8;
		const ENTER			= 13;
		const SPACE			= 32;
		const COMMA			= 44;

		// add the tagit CSS class.
		el.addClass("tagit");

		// create the input field.
		var html_input_field = "<li class=\"tagit-new\"><input class=\"tagit-input\" type=\"text\" name=\"temporaryTag\" /></li>\n";
		el.html (html_input_field);
		
		var addTags = function( event ){
			
			for( var i = 1 ; i <  arguments.length ; i++ ){
				if (!is_new ( arguments[i] )) continue;
				create_choice( arguments[i] );
			}
		}
		// listen to addtags outer events
		$(this).bind("addTags", addTags );
		
		tag_input		= el.children(".tagit-new").children(".tagit-input");

		$(this).click(function(e){
			if (e.target.tagName == 'A') {
				// Removes a tag when the little 'x' is clicked.
				// Event is binded to the UL, otherwise a new tag (LI > A) wouldn't have this event attached to it.
				$(e.target).parent().remove();
			}
			else {
				// Sets the focus() to the input field, if the user clicks anywhere inside the UL.
				// This is needed because the input field needs to be of a small size.
				tag_input.focus();
			}
		});

		tag_input.keypress(function(event){
			if (event.which == BACKSPACE) {
				if (tag_input.val() == "") {
					// When backspace is pressed, the last tag is deleted.
					$(el).children(".tagit-choice:last").remove();
				}
			}
			// Comma/Space/Enter are all valid delimiters for new tags.
			else if (event.which == COMMA ||  event.which == ENTER) {
				event.preventDefault();

				var typed = tag_input.val();
				typed = typed.replace(/,+$/,"");
				typed = typed.trim();

				if (typed != "") {
					if (is_new (typed)) {
						create_choice (typed);
					}
					// Cleaning the input.
					tag_input.val("");
				}
			}
		});
		
		
		function is_new (value){
			
			var is_new = true;
			this.tag_input.parents("ul").children(".tagit-choice").each(function(i){
				n = $(this).children("input").val();
				if (value == n) {
					is_new = false;
				}
			})
			return is_new;
		}
		
		var create_choice = function(value){
			var el = "";
			el  = "<li class=\"tagit-choice\">\n";
			el += value + "\n";
			el += "<a class=\"close\">x</a>\n";
			el += "<input type=\"hidden\" style=\"display:none;\" value=\""+value+"\" name=\""+identifier+"[]\">\n";
			el += "</li>\n";
			var li_search_tags = this.tag_input.parent();
			$(el).insertBefore (li_search_tags);
			this.tag_input.val("");
		}
		
		// add items by default
		if( typeof(options.defaultTags)=='object'&&(options.defaultTags instanceof Array) ){
			
			for( var i = 0; i < options.defaultTags.length ; i++ ){
				if( options.defaultTags[i].length == 0 ) continue;
				if (is_new ( options.defaultTags[i] )) {
						
						create_choice ( options.defaultTags[i] );
				}
			}
		}
		
		
		tag_input.autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: "http://ws.geonames.org/searchJSON",
					dataType: "jsonp",
					data: {
						featureClass: "P",
						style: "full",
						maxRows: 12,
						name_startsWith: request.term
					},
					success: function( data ) {
						response( $.map( data.geonames, function( item ) {
							return {
								label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
								value: item.name
							}
						}));
					}
				});
			},
			open: function() {
				console.log( "open autocomplete" );
			},
			minLength: 2, 
			select: function(event,ui){
				if (is_new (ui.item.value)) {
					create_choice (ui.item.value);
				}
				// Cleaning the input.
				tag_input.val("");

				// Preventing the tag input to be update with the chosen value.
				return false;
			}
		});
		
		return this;
	};

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	};

})(jQuery);

