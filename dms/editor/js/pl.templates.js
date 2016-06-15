!function ($) {

$.plTemplates = {

	init: function(){
		this.bindUIActions()
	}
	
	, defaultArgs: function( btn ){
		
		var that = this
		,	theRegion = $('[data-region="template"]')
		,	btn = btn || false
		,	key	= (btn) ? btn.closest('.pl-template-row').data('key') : false
		,	args = {
				mode: 'set_template'
				, key: key
			}
		
		return args
	}

	, bindUIActions: function(){
		
		var that = this
		,	theRegion = $('[data-region="template"]')
		
		if( plIsset( theRegion.data('custom-template') )){
			
			var templateID = theRegion.data('custom-template')
			, 	templateName = theRegion.data('template-name')
			
			theRegion
				.find('.btn-region')
					.addClass('region-unlock')
					.html(sprintf('"%s" Template (<i class="icon icon-unlock"></i> Unlock)',templateName ))
					.end()
				.find('.linked-tpl')
					.html(sprintf('&mdash; <i class="icon icon-file-text"></i> Linked to "%s" template',templateName ))
					.end()
		}
		
		// Set locking name
		$('#site [data-custom-section]').each( function(){

			if( $(this).data('custom-section') != ''){
				var sectionID = $(this).data('custom-section')
				,	sectionName = $(this).data('custom-name')

				$(this)
					.find('.area-unlock')
					.attr('title', sprintf('Break link to "%s" section', sectionName))
					.end()
					.find('.linked-section')
					.html(sprintf('&mdash; <i class="icon icon-dropbox"></i> Linked to "%s" Section',sectionName ))
					.end()
			}
		})
		
		// Load the template tooltips
		$('.tt-top').tooltip({placement: 'top'})
		$('.tt-bottom').tooltip({placement: 'bottom'})

		$(".region-unlock").on("click.regionUnlock", function(e) {

			e.preventDefault()
			
			var confirmText = $.pl.lang('<h4>Unlink Template</h4>This will remove the current pages connection to a template? Are you sure?')
			
			bootbox.confirm( confirmText, function( result ){
				if(result == true){
					
					theRegion
						.data('custom-template', false)
						.removeClass('custom-template editing-locked')
						.find('.btn-region')
							.html('Template')
							.end()
						.find('.linked-tpl')
							.html('')
							.end()
					
					$.plDatas.handleNewItemData( $('.template-region-wrap') )	
						
					$.removeData( theRegion, 'custom-template')
					
					$.pageBuilder.reloadConfig( {refresh: false } )
				}
			})
			
			
			
			
		
		})
		
		$(".load-template").on("click.loadTemplate", function(e) {

			e.preventDefault()
			
			var key = $(this).closest('.pl-template-row').data('key')
			
			theRegion
				.data('custom-template', key)
				.attr('data-custom-template', key)
				.addClass('custom-template editing-locked')
			
			$.pageBuilder.reloadConfig( { refresh: true, load: 'template' } )
		
		})

		$(".delete-template").on("click.deleteTemplate", function(e) {

			e.preventDefault()

			var defaultArgs = that.defaultArgs( $(this) )
			,	args = {
						run: 'delete'
					,	confirm: true
					, 	log: true
					,	confirmText: $.pl.lang("<h3>Are you sure?</h3><p>This will delete this template. All pages using this template will be reverted to their default page configuration.</p>")
					, 	beforeSend: function(){
							$( '.template_key_' + defaultArgs.key ).fadeOut(300, function() {
								$(this).remove()
							})

					}
				}
			,	args = $.extend({}, defaultArgs , args)
			

			$.plAJAX.run( args )

		})


		$(".form-save-template").on("submit.saveTemplate", function(e) {

			e.preventDefault()

			var form = $(this).formParams()
			,	config = $.extend({}, $.plMapping.getRegionMapping( theRegion ), form)
			,	args = {
						run: 'create'
					,	config: config
					,	postSuccess: function( response ){
							if( !response )
								return
								
							theRegion
								.addClass('custom-template editing-locked')	
								.data('custom-template', response.key)
								.attr('data-custom-template', response.key)
								
							$.pageBuilder.reloadConfig( { refresh: true } )
						}
				}
			,	args = $.extend({}, that.defaultArgs( $(this) ), args)


			$.plAJAX.run( args )


		})


		$(".template-mode-selector-update").on("click", function(e) {

			e.preventDefault()
	
	
			var theValue = $('.template-mode-selector-select').val() || ''
			,	args = {
						run: 'template_mode'
					,	value: theValue
					,	refresh: true
				}
		
			args = $.extend({}, that.defaultArgs(), args)
		
			$.plAJAX.run( args )

		})

		$(".update-template").on("click", function(e) {

			e.preventDefault()
	
			var config = $.plMapping.getRegionMapping( theRegion )
			,	args = {
						run: 'update'
					,	config: config
					,	confirm: true
					,	confirmText: $.pl.lang("<h3>Are you sure?</h3><p>This action will set the current page to this template. All pages using this template will be updated with the new config as well.</p>")
					,	postSuccess: function( response ){
							if( !response )
								return
								
							theRegion
								.addClass('custom-template editing-locked')	
								.data('custom-template', response.key)
								.attr('data-custom-template', response.key)
								
							$.pageBuilder.reloadConfig( { refresh: true } )
						}
					
				}
			,	args = $.extend({}, that.defaultArgs( $(this) ), args)

			$.plAJAX.run( args )

		})


		$(".set-tpl").on("click.defaultTemplate", function(e) {

			e.preventDefault()

			var that = this
			,	value = $(this).closest('.pl-template-row').data('key')
			,	run = $(this).data('run')
			,	args = {
						mode: 'set_template'
					,	run: 'set_'+run
					,	confirm: false
					,	refresh: false
					, 	log: true
					, 	field: $(this).data('field')
					,	value: value
					, 	postSuccess: function( response ){

							// console.log("caller is " + arguments.callee.caller.toString());


							// $.Ajax parses argument values and calles this thing, probably supposed to do that a different way
							if(!response)
								return

							var theList = $(that).closest('.pl-list-contain')

								theList
									.find('.set-tpl[data-run="'+run+'"]')
									.removeClass('active')

								theList
									.find('.active-'+run)
									.removeClass('active-'+run)


							if(response.result && response.result != false){

								$(that)
									.addClass('active')
									.closest('.x-item-actions')
									.addClass('active-'+run)

							}else {
								plPrint('Response was false.')
								plPrint( response )
							}
						}
				}
			var response = $.plAJAX.run( args )
		})
	}
}
}(window.jQuery);