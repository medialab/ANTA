 
		$('.remove-document-tag').click( function( event ) {
			
			event.preventDefault();
			
			// calling tag div
			var divTag = $(this).parent().parent(); 
			
			// category div
			var divCategory = divTag.parent().parent();
			
			console.log( ".remove-document-tag: " + removeDocumentTagUrl + $(this).attr( 'href' ) );
			
			// perform ajax, then if ok delete its container, parent.parent
			$.ajax({
				url:removeDocumentTagUrl,
				data: $(this).attr( 'href' ),
				dataType: 'json',
				success:function( result ){
					
					console.log( result );
					
					if( result.status != 'ok' ){
						return;
					}
					
					// deleting stuff
					divTag.remove();
					
					// remove category if it's empty
					if( divCategory.find( ".item-tag" ).length == 0 ){
						console.log( divCategory );
						divCategory.remove();
					}
					
				
				}
			});
		});
		