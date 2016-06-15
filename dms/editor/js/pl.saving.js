!function ($) {


	/*
	 * AJAX Actions
	 */
	$.plSave = {

		save: function( opts ){
			
			var opts = opts || {}
			,	args = {
						mode: 'fast_save'
					,	run: 'all'
					,	log: true
					,	store: $.pl.data
					,	savingText: $.pl.lang("Saving.")
					,	refresh: false
					,	refreshText: $.pl.lang("Successfully saved! Refreshing page...")
					, 	templateMode: $.pl.config.templateMode || 'local'
				}
			
			$.pageBuilder.updatePage({ location: 'save-data' })

			$.extend( args, opts )
			
			

			$.plAJAX.run( args )
			

		}


	}



}(window.jQuery);