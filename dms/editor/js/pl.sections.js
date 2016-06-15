!function ($) {

$.plSections = {

	init: function(){
		this.bindActions()
		this.makeDraggable()
	}
	, bindActions: function(){
		var that = this

		$('.btn-reload-sections').on('click', function(e){

			e.preventDefault()

			var args = {
						mode: 'sections'
					,	run: 'reload'
					,	confirm: false
					,	savingText: $.pl.lang("Reloading and Registering Sections")
					,	refreshText: $.pl.lang("Sections reloaded. Refreshing page!")
					,	refresh: true
					, 	log: true
				}


			var response = $.plAJAX.run( args )

		})

	}
	, makeDraggable: function( ){

		var that = this

		$('.panel-add-new').find( '.x-item.pl-sortable:not(.x-disable)' ).draggable({
				appendTo: "body"
			, 	helper: "clone"
			, 	cursor: "move"
			, 	connectToSortable: ".pl-sortable-area"
			,	zIndex: 10000
			,	distance: 20
			, 	start: function(event, ui){

					that.switchOnAdd( ui.helper )

					ui.helper
						.css('max-width', '300px')
						.css('height', 'auto')


				}
			, 	stop: function(event, ui){
					$('body')
						.removeClass('pl-dragging')
				}
		})

		$('.panel-add-new').find( '.x-item.pl-area-sortable' ).draggable({
				appendTo: "body"
			, 	helper: "clone"
			, 	cursor: "move"
			, 	connectToSortable: ".pl-area-container"
			,	zIndex: 10000
			,	distance: 20
			, 	start: function(event, ui){

					that.switchOnAdd( ui.helper )

					ui.helper
						.css('width', '100%')
						.css('height', 'auto')


				}
			, 	stop: function(event, ui){
					$('body')
						.removeClass('pl-dragging')
				}
		})


	}
	, switchOnAdd: function( element ){


		var name = element.data('name')
		, 	image = element.data('image')
		, 	imageHTML = sprintf('<div class="pl-touchable banner-frame"><div class="pl-vignette pl-touchable-vignette"><img class="section-thumb" src="%s" /></div></div>', image )
		, 	theHTML = sprintf('<div class="pl-refresh-banner pl-contrast"><div class="banner-content">%s</div></div>', imageHTML	)


		$.pageTools.toggleGrid(false, 'show')

		element
			.removeAttr("style")
			.html(theHTML)

		if( !element.hasClass('ui-draggable-dragging') )
			element.hide()

	}
	, switchOnStop: function( element ){

		var that = this
		,	type = (element.hasClass('pl-area-sortable')) ? 'area' : 'section'
		,	activeLoad = (element.hasClass('loading-active')) ? true : false
		,	name = element.data('name')
		,	sid = element.data('sid')
		,	object = element.data('object')
		,	sectionClass = 'section-'+sid
		,	classToAdd = (type == 'section') ? 'pl-section' : 'pl-area'
		
		element
			.removeClass('x-item isotope-item x-add-new x-extension')
			.addClass( classToAdd )
			.addClass( sectionClass )
			
			
		// Don't think this is actually needed.
		var set = $.plDatas.handleNewItemData( element )
		,	newUniqueID = set.uid

		if( activeLoad ){
			
			var args = {
					run: 'load'
				,	mode: 'sections'
				,	object: object
				, 	uniqueID: newUniqueID
				,	draw: type
				, 	postSuccess: function(response){
					
						if(!response)
							return
						
						var controlType = (type == 'section') ? '.pl-section-controls' : '.pl-area-controls'
						,	wrapper = (type == 'section') ? '<div class="pl-section-pad fix">%s</div>' : '<div class="pl-area-pad fix">%s</div>'
						, 	wrapSelect = (type == 'section') ? '.pl-section-pad' : '.pl-inner'
						,	controls = $( controlType ).first().clone()	
					
						controls
							.find('.ctitle')
							.html(name)

						element
							.html( sprintf(wrapper, response.template) )
							.prepend( controls )
						
						element
							.find('.pl-animation')
							.addClass('animation-loaded')
							
							
						if(response.notice){
							element
								.find(wrapSelect)
								.append($.pl.lang("<div class='loaded-notice'><div class='the-notice'>Loaded! Note: For this section, page refresh may be needed for complete functionality (Javascript Loading).</div></div>"))


							setTimeout(function () {
							    $('.loaded-notice').slideUp()
							}, 5000)
						}	
						
							
						
						var newOpts = {}
						
						newOpts[ newUniqueID ] = {
							opts: response.opts
							, name: name
						}
						
						$.extend($.pl.config.opts, newOpts)
						
						$.pageBuilder.reloadAllEvents()
				
					}
				,	beforeSend: function( ){
						var Text = $.pl.lang("Loading")
						element
							.html('<div class="pl-refresh-banner pl-contrast"><i class="icon icon-spinner icon-spin"></i> '+Text+'</div>')
					}
			}

			$.plAJAX.run( args )
			
		} else {

			var Text = $.pl.lang("Loading")
			
			element
				.html('<div class="pl-refresh-banner pl-contrast"><i class="icon icon-spinner icon-spin"></i> '+Text+'</div>')
				
			setTimeout(function() {
				element
					.html('<div class="pl-refresh-banner pl-contrast"><i class="icon icon-thumb-up"></i> Section added! Refresh page to view.</div>')
					
			}, 700)
			
			
			// As for the loading argument, this is needed for the saving to process the area as a custom section. 
			// We were having 
			
			var img = element.find( '.banner-content' ).html()
			,	load = ( element.closest('.custom-section').length && ! element.hasClass('custom-section')) ? 'custom' : 'section'
			, 	loadArgs = {
					refresh: false
					, 	load: load
					,	object: object
					, 	uniqueID: newUniqueID
					,	draw: type
				}

			$.pageBuilder.reloadConfig( loadArgs )
			
		}
		

		if( ! element.hasClass('ui-draggable-dragging') )
			element.show()


		
	}
	
	, sectionLoader: function( obj ){
	
		var object = obj.element.data('object')
		
		var args = {
				run: 'load'
			,	mode: 'sections'
			,	object: object
			, 	postSuccess: obj.postSuccess
			,	beforeSend: obj.beforeSend
		}

		$.plAJAX.run( args )
	}
}

}(window.jQuery);