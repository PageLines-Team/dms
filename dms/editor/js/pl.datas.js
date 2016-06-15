!function ($) {


	/*
	 * Data/Settings handling functions.
	 */
	$.plDatas = {
		
		GetUIDs: function( ){

			var uids = []

			jQuery('.site-wrap').find("[data-clone]").each(function(){
				
				uids.push( $(this).data('clone') )
				
			})
			
			return uids
			
		}

		, setElementDelete: function( deleted, original ){
			
			var that = this
			,	original = original || 'true'
			,	uniqueID = deleted.data('clone')
			, 	uids = [ uniqueID ]
			
			// Recursion
			deleted.find("[data-clone]").each(function(){
				
				subuids = that.setElementDelete( $(this), 'false' )
				
				uids.concat( subuids )
				
			})
			
			// recursive
			deleted.remove()

			if( plIsset( $.pl.data.list[ uniqueID ] ) )
				delete $.pl.data.list[ uniqueID ]

			if( plIsset($.pl.data.local[ uniqueID ]) )
				delete $.pl.data.local[ uniqueID ]
				
			if( plIsset($.pl.data.type[ uniqueID ]) )
				delete $.pl.data.type[ uniqueID ]
				
			if( plIsset($.pl.data.global[ uniqueID ]) )
				delete $.pl.data.global[ uniqueID ]
		
			// only on original call 
			if( original == 'true' ){
				
				$.plSave.save({ 
					  run: 'delete_items'
					, store: uids
				})
				
			}
		
			return uids
		}
		
		
		, handleNewItemData: function( newItem ){

			var that = this
			,	set = that.newPageItemData( newItem ) // recursive function
			
			newItem
				.find('.tooltip')
				.removeClass('in')
			
		
			$.plSave.save({ 
				  run: 'create_items'
				, store: set.uids
				, test: 'something'
			})
			
			return set

		}
		
		, newPageItemData: function( element ){
			
			var that = this
			,	oldUniqueID = element.data('clone')
			, 	newUniqueID = plUniqueID()
			, 	uids = {}
			
			
			
			// Set element meta for mapping
			if( ! element.hasClass('custom-section') ){
				
				element
					.attr('data-clone', newUniqueID)
					.data('clone', newUniqueID)

				if( plIsset( $.pl.data.list[ oldUniqueID ] ) ){

					$.pl.data.list[ newUniqueID ] = $.extend({}, $.pl.data.list[ oldUniqueID ]) // must clone the element, not just assign as they stay connected

					uids[ newUniqueID ] = $.pl.data.list[ oldUniqueID ]

				}

				// Recursion
				element.find("[data-clone]").each(function(){
					
					if( ! $(this).hasClass('custom-section') && $(this).parents(".custom-section").length == 0 ){
						
						var subset = that.newPageItemData( $(this) )

						$.extend( uids, subset.uids )
						
					}
					
				})
				
				// Handle options configuration
				var	theOpts 	= ( plIsset( $.pl.config.opts[ oldUniqueID ])) ? $.pl.config.opts[ oldUniqueID ] : ''

				$.pl.config.opts[ newUniqueID ] = theOpts
				
			} 
			
			var set = {
				uid: newUniqueID
				, uids: uids
			}
			return set
		}


	}



}(window.jQuery);