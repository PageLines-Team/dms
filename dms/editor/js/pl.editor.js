// PageLines Editor - Copyright 2013

!function ($) {
	 

	// --> Initialize
	$(document).ready(function() {
		
		$.CheckCustomize.init()
		$.pageTools.startUp()

	})
	
	$.CheckCustomize = {		
		init: function() {
			
			// Check if we are in the customizer IFRAME, only way to get here is from wp-admin/themes.php.
			// If we are, redirect to homepage, with editor open, seeing as thats what the user wants.
			if (top != self){
				var url = document.referrer			
				// only redirect if the referrer is wp-admin, some people might actually still be using frames :o				
				if( false != strpos( url, 'wp-admin/themes.php' ) ) {
					top.location.replace($.pl.config.urls.siteURL + '?edtr=on&toolbox=open')
				}					
			}
			
			// If we are not in an iframe, check URL for toolbox=open
			var toolbox = GetQueryStringParams('toolbox') || false
			if( toolbox ) {
				$.toolbox('show')
			}
		}
	}
	
	$.plHotKeys = {
		init: function(){
			
			
			$.jQee('alt+a', function() {

				$('[data-action="toggle-grid"]')
					.trigger('click')
			})
			
			$.jQee('alt+s', function() {

				$('[data-mode="publish"]')
					.trigger('click')
			})		
			
			$.jQee('meta+r ctrl+r', function() {
			
				window.onbeforeunload = null
				return true
			})
			
		}
	}

	// Event Listening
	$.pageTools = {

		startUp: function(){
			
			$.plDev.init()
			$.plHotKeys.init()

			$(".dropdown-toggle").dropdown()

			var storeMap = ( $('body').hasClass('pl-save-map-on-load') ) ? true : false
			
			$.pageBuilder.reloadConfig({ location: 'start', storeMap: storeMap })

			this.theToolBox = $('body').toolbox()

			$.pageBuilder.showEditingTools()

			$.plAJAX.init()
			
			

			$.plTemplates.init()

			this.bindUIActions()
			
			that.toggleGrid( true )

			$.plCommon.setFixedHeight()

			// run after transition
			setTimeout(function(){
				$.plCommon.setFixedHeight()
			}, 500)
			
		}
		

		
		, bindUIActions: function() {

			that = this

			$('.btn-toolbox').tooltip({placement: 'top', delay: { show: 1000 }})
			$('[data-toggle="tooltip"]').tooltip({placement: 'top'})
			
			$(".alert").alert()
			// Show unload if state is changed from live, will be overridden if 
			// the state changes or by other user actions.
		//	if( $('#stateTool').data('show-unload') == 'yes' )

			
			// Click event listener
			$(".btn-toolbox:not(.btn-link)").on("click.toolboxHandle", function(e) {

				e.preventDefault()
				

				var btn = $(this)
				, 	btnAction = btn.data('action')

				if( btnAction == 'drag-drop' )
					$.pageBuilder.showEditingTools()
				else if( btn.hasClass('btn-panel') )
					$.pageTools.showPanel(btnAction)
				else if( btn.hasClass('btn-toggle-grid') )
					$.pageTools.toggleGrid( )
			})

			$(".btn-action").on("click.actionButton", function(e) {

				e.preventDefault()

				var btn = $(this)
				, 	btnAction = btn.data('action')

				if( btnAction == 'pl-toggle' )
					$.plAJAX.toggleEditor( btnAction )

				if( btnAction == 'toggle_grid' )
					that.toggleGrid()

			})
			
			$("[data-tab-link]").on("click.tabLink", function(e) {

				e.preventDefault()
				
				var tabLink =  $(this).data('tab-link') || ''
				,	tabSubLink = $(this).data('stab-link')|| ''
				
				$.pageTools.tabLink( tabLink, tabSubLink)
				

			})
			
			if(getURLParameter('tablink')){
				var tabLink = getURLParameter('tablink')
				, 	tabSubLink = getURLParameter('tabsublink')|| ''
				
				that.tabLink(tabLink, tabSubLink)
			}
			
        }

		, tabLink: function( tabLink, tabSubLink ){
			
			var tabLink =  tabLink || ''
			,	tabSubLink = tabSubLink || ''
	
			tabSubLink = ( tabSubLink != '') ? tabSubLink : '0'
			
			if( tabLink != '' ){
				
				var tabLoad = (tabSubLink != '') ? tabSubLink : '0'
				
				$('.panel-'+tabLink)
					.attr('data-tab-load', tabSubLink)
					.data('tab-load', tabSubLink)
				
				$( '[data-action="'+tabLink+'"]' )
					.trigger('click')
					
				$('[data-tab-action="'+tabSubLink+'"] a')
					.trigger('click')
					
				
				return
			}
			
			
		}

		, toggleGrid: function( load, action){

			var load = load || false
			,	action = action || 'toggle'
			
			
			if( action != 'show' 
				&& (
					(!load && $('body').hasClass('drag-drop-editing')) 
					|| (load && store.get('plPagePreview') == true)
				)
			){
				
				$('[data-action="toggle-grid"]').addClass('active-tab')
				$('body').removeClass('drag-drop-editing width-resize')
				store.set('plPagePreview', true)
				$('.ui-sortable:not(.toolbox-sortable)').sortable( "disable" )
				
			} else {
				
				$('[data-action="toggle-grid"]').removeClass('active-tab')
				$('body').addClass('drag-drop-editing width-resize')
				store.set('plPagePreview', false)
				$('.ui-sortable').sortable( "enable" )
				
			}
			
			$.plCommon.setFixedHeight()

		}
		
		, filterItems: function( tab, panel ){
			var theIsotope = panel.find('.isotope')
			,	removeItems = $('.x-remove')
			, 	theFilter = tab.data('filter') || '*'
			
			
			theIsotope
				.isotope({ filter: theFilter })
				.isotope('remove', removeItems)
				.removeClass('x-pane-mode')
		
		}

		, showPanel: function( key ){

			var that = this
			,	selectedPanel = $('.panel-'+key)
			, 	selectedTab = $('[data-action="'+key+'"]')
			, 	allPanels = $('.tabbed-set')
			

			$('body')
				.toolbox('show')

			store.set('toolboxPanel', key)

			if(selectedPanel.hasClass('current-panel'))
				return

			$('.btn-toolbox:not(.btn-toggle-grid)').removeClass('active-tab')

			allPanels
				.removeClass( 'current-panel' )
				.hide()

			$('.pl-toolbox .ui-tabs').tabs('destroy')

			var activeTabSlug = selectedPanel.attr('data-tab-load')
			, 	theSlug = activeTabSlug || getURLParameter('tabsublink')
			, 	theATab = selectedPanel.find('[data-tab-action="'+theSlug+'"]')
			, 	activeIndex = theATab.parent().children('li').index( theATab )
			, 	activeTab = (activeIndex > 1) ? activeIndex : 0
			
		
			if(activeTab == 0){
				
				tabMemory = store.get( 'plTabMemory' )
			
				if(plIsset(tabMemory) && plIsset(tabMemory[key]))
					activeTab = tabMemory[key]	
					
			}
			
			

			selectedPanel.tabs({
				
				active: activeTab
				, show: 600
				, create: function(event, ui){
					
					var theTab = ui.tab
					,	tabMeta = theTab.attr('data-tab-meta') || ''
					, 	tabAction = theTab.attr('data-tab-action') || ''
					,	tabPanel = $("[data-panel='"+tabAction+"']")
					
					if( tabMeta == 'options' ){
						that.loadPanelOptions( selectedPanel, tabAction, tabAction )
					}
					
				
					
					selectedPanel.find('.tabs-nav li').on('click.panelTab', function(){
						
						that.filterItems($(this), selectedPanel)
						
					})
					
					$('body').trigger('pl-tab-build', [ theTab ])
					
				}
				
				, beforeActivate: function(e, ui){
					
					var theTab = ui.newTab
					,	tabFlag = theTab.attr('data-flag') || ''
					
					if ( tabFlag == 'link-storefront' ){

						e.preventDefault()
						
						var storeURL = 'http://www.pagelines.com/shop'
						
						window.open( storeURL )
						
							
						return false

					}
				}
				, activate: function(e, ui){
					
					var theTab = ui.newTab
					, 	tabAction = theTab.attr('data-tab-action') || ''
					,	tabPanel = $("[data-panel='"+tabAction+"']")
					,	tabFlag = theTab.attr('data-flag') || ''
					,	tabLink = theTab.attr('data-tab-link') || ''
					,	tabSubLink = theTab.attr('data-stab-link') || ''
					,	tabMeta = theTab.attr('data-tab-meta') || ''
					
					
					if(tabMeta == 'options'){
						that.loadPanelOptions( selectedPanel, tabAction )
					}
		
						
				
					if( tabLink != '' ){
						
						var tabLoad = (tabSubLink != '') ? tabSubLink : '0'
						
						$('.panel-'+tabLink)
							.attr('data-tab-load', tabSubLink)
							.data('tab-load', tabSubLink)
						
						$( '[data-action="'+tabLink+'"]' )
							.trigger('click')
						
						return
					}


					
					
					
					var tabMemory = store.get( 'plTabMemory' )
					, 	obj = {}
					
					obj[key] = ui.newTab.parent().children('li').index(ui.newTab)
				
					tabMemory = $.extend(tabMemory, obj)
					
				//	plPrint(tabMemory)

					store.set('plTabMemory', tabMemory)
					
					$('body').trigger('pl-tab-build', [ theTab ])

				}
			})

			selectedPanel
				.addClass('current-panel')
				.show()
				.trigger('shown')


			// Has to be after shown
			if( key == 'settings'){

				var config = {
						mode: 'settings'
						, sid: 'settings'
						, settings: $.pl.config.settings
					}
					
				$.optPanel.render( config )

			} else if (key == 'section-options'){

				$('body').toolbox({
					action: 'show'
					, panel: key
					, info: function(){

						$.optPanel.render( config )

					}
				})

			} else if( plIsset( $.pl.config[key] ) ){
				
				
				var config = {
						mode: 'settings'
						, sid: key
						, panel: key
						, settings: $.pl.config[key]
					}
					
				$.optPanel.render( config )
				
			} else if ( key == 'add-new'){
				
				$.pageTools.toggleGrid(false, 'show')
				
			}

			selectedTab.addClass('active-tab')
			
			$('body').trigger('panelSetup', [ selectedPanel ])

			$.xList.listStop()

			$.xList.listStart(selectedPanel, key)
			
		

		}
		
		, loadPanelOptions: function( panel, key, load ){
			
			var set = $.pl.config.panels[key] || false
			
			
			if(set){
				var config = {
						mode: 'panel'
						, panel: panel.data('key')
						, settings: set
						, tab: key
						, load: load
					}

				$.optPanel.render( config )
			}
			
			
		}

		, stateInit: function( key, call_on_true, call_on_false, toggle ){

			var localState = ( localStorage.getItem( key ) )
			,	theState = (localState == 'true') ? true : false


			if( toggle ){
				theState = (theState) ? false : true;
				localStorage.setItem( key, theState )
			}

			if (!theState){

				$('[data-action="'+key+'"]').removeClass('active-tab')

				if($.isFunction(call_on_false))
					call_on_false.call( key )
			}

			if (theState){

				$('[data-action="'+key+'"]').addClass('active-tab')

				if($.isFunction(call_on_true))
					call_on_true.call( key )
			}

		}

	}

	// Page Drag/Drop Builder
    $.pageBuilder = {

		toggle: function( ){

			var localState = ( localStorage.getItem( 'drag-drop' ) )
			,	theState = (localState == 'true') ? true : false

			if( !theState ){

				theState = true

				$.pageBuilder.showEditingTools()

			} else {

				theState = false

				$.pageBuilder.hide()

			}

			localStorage.setItem( 'drag-drop', theState )

		}

		, showEditingTools: function() {

			// Graphical Flare
			$('[data-action="drag-drop"]').addClass('active')

			// Enable CSS
			$('body').addClass('drag-drop-editing')

			// JS
			$.pageBuilder.startDroppable()

			$.pageBuilder.sectionControls()

			$.areaControl.toggle($(this))

			$.widthResize.startUp()

			
		}
		
		, reloadAllEvents: function(){
			// reload events
			$('.s-control')
				.off('click.sectionControls')

			$.pageBuilder.sectionControls()

			$('.area-control')
				.off('click.areaControl')

			$.areaControl.listen()
			
			$.pageBuilder.startDroppable( )
			$.widthResize.startUp()
			
			$.pageBuilder.reloadConfig( {location: 'reload all'} )
		}
		

		, hide: function() {

			$('body').removeClass('drag-drop-editing')

			$('[data-action="drag-drop"]').removeClass('active')

			$('.s-control')
				.off('click.sectionControls')

			$.areaControl.toggle($(this))

			$.widthResize.shutDown()

		}
		

		, sectionControls: function() {
		
			var that = this
			, proBtn = sprintf( '<br/> <a class="btn btn-primary" href="http://www.pagelines.com/pricing/" target="_blank"><i class="icon icon-pagelines"></i> %s <i class="icon icon-external-link"></i></a>', $.pl.lang( "Learn more about being a PRO" ) )
			
			$('.pro-only-disabled').on('click', function(e){
				bootbox.alert($.pl.lang("<h4>This capability is pro edition only.</h4>")+proBtn)
			})
			
			
			$('.pro-section .section-edit').addClass('pro-only-disabled').attr('title', $.pl.lang("Edit (Pro Only)")).on('click', function(e){
				bootbox.alert($.pl.lang("<h4>This is a Pro Section</h4>Editing Pro sections requires Pro Membership.<br/>")+proBtn)
			})
			
			$('.s-control').tooltip({placement: 'top'})
			
			
			
			$('.pl-area .s-control:not(".pro-only-disabled")').on('click.sectionControls', function(e){

				e.preventDefault()
				e.stopPropagation()

				// cause options to save in last panel
				// If a user clicks on edit button of another section and it doesn't save, percieved as bug
				$('.current-panel').find('.lstn').first().trigger('blur')

				var btn = $(this)
				,	section = btn.closest(".pl-section")
				,	scope = ( section.parents(".template-region-wrap").length == 1 ) ? 'local' : 'global'
				,	config	= {
						sid: section.data('sid')
						, sobj: section.data('object')
						, clone: section.data('clone')
						, uniqueID: section.data('clone')
						, scope: scope
					}
				,	storeData = true
				
				// Remove tool tips, sometimes there's quirks
				$('.pl-section-controls')
					.find('.tooltip')
					.removeClass('in')
					

				if(btn.hasClass('section-edit')){
					
					storeData = false
					// TODO Open up and load options panel
					
					// This is needed so we can control which scope tabs are shown
					$('[data-key="section-options"]')
						.data('section-uid', config.uniqueID)
						.attr('data-section-uid', config.uniqueID)
						
					$('body').toolbox({
						action: 'show'
						, panel: 'section-options'
						, info: function(){

							$.optPanel.render( config )

						}
					})

				} else if (btn.hasClass('section-delete')){

					storeData = false
					
					bootbox.confirm($.pl.lang("<h3>Are you sure?</h3><p>This will remove this section and its settings from this page.</p>"), function( result ){

						if(result == true){
							$.plDatas.setElementDelete( section ) // recursive function

							$.pageBuilder.reloadConfig( {location: 'section-delete'} )
					
						} 

					})

				} else if (btn.hasClass('section-clone')){

					var	cloned = section.clone( true, true )
					
					cloned
						.insertAfter(section)
						.hide()
						.fadeIn()

					$.plDatas.handleNewItemData( cloned )
					
				} else if ( btn.hasClass('section-increase')){

					var sizes = $.plMapping.getColumnSize(section)

					if ( sizes[1] )
						section.removeClass( sizes[0] ).addClass( sizes[1] )


				} else if ( btn.hasClass('section-decrease')){

					var sizes = $.plMapping.getColumnSize( section )

					if (sizes[2])
						section.removeClass(sizes[0]).addClass(sizes[2]) // Switch for next class


				} else if ( btn.hasClass('section-offset-increase')){

					var sizes = $.plMapping.getOffsetSize( section )

					if (sizes[1])
						section.removeClass(sizes[0]).addClass(sizes[1])


				} else if ( btn.hasClass('section-offset-reduce')){

					var sizes = $.plMapping.getOffsetSize( section )

					if (sizes[1])
						section.removeClass(sizes[0]).addClass(sizes[2])


				} else if ( btn.hasClass('section-start-row') ){

					section.toggleClass('force-start-row')

				}

				// "delete" has a confirm, so doesn't need this
				if( storeData )
					$.pageBuilder.reloadConfig( { location: 'section-control' } )
					
			

			})

		}

		, updatePage: function( obj ){
			
			var templateMode = $.pl.config.templateMode || 'local'
			
			$('.pl-sortable-area').each(function () {
				$.pageBuilder.alignGrid( this )
			})
			
			var mapConfig = $.plMapping.getCurrentMap()	
			
			$.pl.data[templateMode]['custom-map'] = $.extend({}, { template: mapConfig.template.map })
			
			if( plIsset(mapConfig.header) )
				$.pl.data.global.regions = $.extend( $.pl.data.global.regions, { header: mapConfig.header.map })
				
			if( plIsset(mapConfig.footer) )
				$.pl.data.global.regions = $.extend( $.pl.data.global.regions, { footer: mapConfig.footer.map })
			
			$(window).trigger('resize')

			return mapConfig
			
		} 

        , reloadConfig: function( obj ) {

			$('.pl-sortable-area')
				.addClass('editor-row')
				.find('.pl-section')
				.addClass('pl-sortable')

			var that = this
			,	obj = obj || {}
			, 	location = obj.location || 'none'
			, 	refresh  = obj.refresh || false
			,	refreshText = (typeof obj.refreshText !== 'undefined') ? obj.refreshText : $.pl.lang( "Refreshing page..." )
			,	storeMap = (typeof obj.storeMap !== 'undefined') ? obj.storeMap : true
			,	templateMode = $.pl.config.templateMode || 'local'
			,	map = that.updatePage( obj )
			
			$.plCommon.setFixedHeight()
			
			if( storeMap ){
				
				var saveArgs = {
					run: 'map'
					, store: map
					, refresh: refresh
					, refreshText: refreshText
					, postSuccess: function( rsp ){

						if(!rsp)
							return

						if(rsp.changes && rsp.changes.local == 1){
							$('.x-item-actions')
							 	.removeClass('active-template')
						}

					}
				}
				
				$.extend( saveArgs, obj )
				
				
				$.plSave.save( saveArgs )

			}

        }

		, alignGrid: function( area ) {

            var that = this
			,	total_width = 0
            ,	width = 0
            ,	next_width = 0
			,	avail_offset = 0
			, 	sort_area = $(area)
			, 	len = sort_area.children(".pl-sortable").length

  			that.isAreaEmpty( sort_area )

            sort_area.children(".pl-sortable:not(.pl-sortable-buffer)").each( function ( index ) {

                var section = $(this)
				,	col_size = $.plMapping.getColumnSize( section )
				,	off_size = $.plMapping.getOffsetSize( section )


				if(sort_area.hasClass('pl-sortable-column')){

					if(section.hasClass('level1')){
						section
							.removeClass('level1')
							.removeClass(col_size[0])
							.removeClass(off_size[0])
							.addClass('span12 offset0 level2')

						col_size = $.plMapping.getColumnSize( section, true )
						off_size = $.plMapping.getOffsetSize( section, true )
					} else {
						section
							.addClass('level2')
					}

				} else {

					section
						.removeClass("level2")
						.addClass("level1")

				}

				// First/last spacing
				section
					.removeClass("sortable-first sortable-last")

				if ( index == 0 )
					section.addClass("sortable-first")
				else if ( index === len - 1 )
					section.addClass("sortable-last")


				// Deal with width and offset
				width = col_size[4] + off_size[3]

				total_width += width

				avail_offset = 12 - col_size[4];

				if( avail_offset == 0 )
					section.addClass('cant-offset')
				else
					section.removeClass('cant-offset')

				if(width > 12){
					avail_offset = 12 - col_size[4];
					section.removeClass( off_size[0] ).addClass( 'offset'+avail_offset )
					off_size = $.plMapping.getOffsetSize( section )
				}

               	// Set Numbers
				section.find(".section-size:first").html( col_size[3] )
				section.find(".offset-size:first").html( off_size[3] )

				if (total_width > 12 || section.hasClass('force-start-row')) {

                    section
						.addClass('sortable-first')
                    	.prev('.pl-sortable')
						.addClass("sortable-last")

                    total_width = width

                }

            })

        }

		, isAreaEmpty: function(area){
			var addTo = (area.hasClass('pl-sortable-column')) ? area.parent() : area

			if(!area.children(".pl-sortable").not('.ui-sortable-helper').length)
			    addTo.addClass('empty-area')
			else
			    addTo.removeClass('empty-area')

		}



		, startDroppable: function( reloadSort ){

			var that = this
			,	reloadSort = reloadSort || false
			,	sortableArgs = {}

		 
			$('.pl-region')
	  			.find('.pl-area .pl-sortable-area')
					.sortable( that.sortableArguments( 'section' ) )
					.end()
   				.find('.pl-area-container')
   					.sortable( that.sortableArguments( 'area' ) )
		
	

		}


		, sortableArguments: function( type ){

			var that = this
			,	type = type || 'section'
			,	sortableSettings = {}
			,	items = ( type == 'section' ) ? '.pl-sortable' : '.pl-area-sortable'
			,	container = ( type == 'section' ) ? '.pl-sortable-area' : '.pl-area-container'
			,	placeholder = ( type == 'section' ) ? 'pl-placeholder' : 'pl-area-placeholder'
			,	handle = ( type == 'section' ) ? false : '.area-reorder'

				sortableSettings = {
				       	items: 	items
					,	connectWith: container
					,	appendTo: document.body
					,	placeholder: placeholder
					,	forcePlaceholderSize: true
			        ,	tolerance: "pointer"		// basis for calculating where to drop
					,	helper: 	"clone" 		// needed or positioning issues ensue
					,	scrollSensitivity: 50
					,	scrollSpeed: 40
			        ,	cursor: "move"
					,	distance: 3
					,	delay: 200
					, 	handle: handle
					,	zIndex: 9999
					, start: function(event, ui){

						$('body')
							.addClass('pl-dragging')
							.toolbox('hide')

						if( ui.item.hasClass('x-item') )
							$.plSections.switchOnAdd(ui.item)

						// allows us to change sizes when dragging starts, while keeping good dragging
						$( this ).sortable( "refreshPositions" )


						// Prevents double nesting columns and other recursion bugs.
						// Remove all drag and drop elements and disable sortable areas within columns if
						// the user is dragging a column
						if( ui.item.hasClass('section-plcolumn') ){

							$( '.section-plcolumn .pl-sortable-column' ).removeClass('pl-sortable-area ui-sortable')
							$( '.section-plcolumn .pl-section' ).removeClass('pl-sortable')

							$( '.ui-sortable' ).sortable( 'refresh' )

						}
						
						// Use this to help keep data clean when moving between scopes
						var templateMode = $.pl.config.templateMode || 'local'
						,	startScope = (ui.item.parents(".template-region-wrap").length == 1) ? templateMode : 'global'
						
						ui.item.attr('data-start-scope', startScope)

					}
					, stop: function(event, ui){

						$('body')
							.removeClass('pl-dragging')

						// when new sections are added
						ui.item.find('.banner-refresh').fadeIn('slow')

						if( ui.item.hasClass('section-plcolumn') ){

							$( '.section-plcolumn .pl-sortable-column' ).addClass('pl-sortable-area ui-sortable')
							$( '.section-plcolumn .pl-section' ).addClass('pl-sortable')

							$( '.ui-sortable' ).sortable( 'refresh' )

						}

						if(ui.item.hasClass('x-item'))
							$.plSections.switchOnStop(ui.item)
							
					
						// Move data when changing scopes
						that.moveDataOnDrag( ui.item )
						

					}

					, over: function(event, ui) {

			           $( "#droppable" ).droppable( "disable" )

						ui.placeholder.css({
							maxWidth: ui.placeholder.parent().width()
						})

			        }
					, update: function( event, ui ) {
						
						// the x-item draggables have to make adjustments before they are recognized
						// Saving twice creates a race condition
						if(!ui.item.hasClass('loading-active'))
							that.reloadConfig( {location: 'update sortable'} )
					}
				}

			return sortableSettings
		}

		// Moves data when changing scopes
		, moveDataOnDrag: function( element ){	
			
			var uniqueID = element.attr('data-clone')
			, 	templateMode = $.pl.config.templateMode || 'local'
			,	newScope = (element.parents(".template-region-wrap").length == 1) ? templateMode : 'global'
			, 	oldScope = element.attr('data-start-scope')
			,	store = {}
				
			
			// if data wasn't set or scope wasn't changed
			if( !plIsset( $.pl.data[ oldScope ][ uniqueID ] ) || newScope == oldScope ) 
				return
				
			element
				.attr('data-start-scope', newScope)
		
			//	console.log($.pl.data[ oldScope ])
			// move scope, then delete from old scope
			$.pl.data[ newScope ][ uniqueID ] = $.pl.data[ oldScope ][ uniqueID ]
			
			delete $.pl.data[ oldScope ][ uniqueID ]
			
			store[ newScope ] = $.pl.data[ newScope ]
			store[ oldScope ] = $.pl.data[ oldScope ]
		
		//	$.plAJAX.saveData()
		}
    }
}(window.jQuery);