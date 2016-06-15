!function ($) {

$.plThemes = {


	btnActions: function(){

		$('.btn-theme-activate').on('click.paneAction', function(e){
			e.preventDefault()
		
			var args = {
					mode: 'themes'
				,	run: 'activate'
				,	confirm: true
				,	confirmText: $.pl.lang("<h3>Are you sure?</h3> <p>This will activate this theme sitewide.</p>")
				,	savingText: $.pl.lang("Activating Theme")
				,	refresh: true
				,	refreshArgs: 'pl-install-theme=1'
				,	refreshText: $.pl.lang("Successfully Activated. Refreshing page")
				, 	log: true
				,	stylesheet: $(this).data('stylesheet')
				
			}

			$.plAJAX.run( args )
		})

	}
	, actionButtons: function( data ){
		var buttons = ''
		,	theme = sprintf('data-stylesheet="%s"', data.stylesheet)

		buttons += sprintf('<a href="#" class="btn btn-primary btn-theme-activate x-remove" %s><i class="icon icon-bolt"></i> %s</a> ', theme, $.pl.lang("Activate"))


		// Can't get this to work because of a PHP loading issue
		// Must move to a plugin that loads before the 'stylesheet' is set for a child theme.
		//
		// buttons += sprintf('<a href="#" class="btn btn-theme-preview" %s><i class="icon icon-eye-open"></i> Preview</a> ', theme)

		return buttons
	}
}

}(window.jQuery);