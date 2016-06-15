!function ($) {

	$.xList = {

		renderList: function(panel, list) {
			var items = ''

			// console.log(list)
			// return
			$.each( list , function(index, l) {

				items += sprintf('<div class="x-item %s"><div class="x-item-frame"><img src="%s" /></div></div>', l.class, l.thumb)
			})

			output = sprintf('<div class="x-list">%s</div>', items)

			panel.find('.panel-tab-content').html( output )


		}

		, listStart: function(panel, key) {

			var that = this
			,	layout = (key == 'pl-extend') ? 'masonry' : 'fitRows';

			var filter = (panel.find('.ui-tabs-active').data('filter')) ? panel.find('.ui-tabs-active').data('filter') : '*'

			panel.imagesLoaded( function(){
				panel.find('.x-list').isotope({
					itemSelector : '.x-item'
					, layoutMode : layout
					, sortBy: 'name'
					, filter: filter
					, containerStyle: { position: 'relative', overflow: 'visible' }
					, getSortData : {
						name : function ( $elem ) {
							return jQuery($elem).data('sid');
						}
					}
				})

			})

			//this.listPopOverStart()

			if(key == 'add-new'){
				$.plSections.init()
			}



			this.extensionActions()



		}

		, loadButtons: function(panel, data) {
			var buttons = ''

			if(panel == 'x-store'){
				buttons += $.plExtend.actionButtons( data )
			} else if ( panel == 'x-themes' ){
				buttons += $.plThemes.actionButtons( data )
			} else if ( panel == 'x-sections' ){
				buttons += sprintf('<a href="#" class="btn btn-small disabled"><i class="icon icon-random"></i> %s</a> ', $.pl.lang( "Drag Thumb to Page" ) )
			}


			return buttons
		}

		, loadPaneActions: function(panel) {

			var that = this

			if(panel == 'x-store'){
				$.plExtend.btnActions()
			} else if ( panel == 'x-themes' ){
				$.plThemes.btnActions()
			}


			$('.x-close').on('click.paneAction ', function(e){

				e.preventDefault()

				var theIsotope = $(this).closest('.isotope')
				,	theAction = $(this).data('action')
				,	removeItems = $('.x-remove')
				,	theActiveTab = $('.current-panel').find('.ui-state-active')
				,	activeTabFilter = theActiveTab.data('filter')
				,	theFilter = ( typeof(activeTabFilter) != 'undefined') ? activeTabFilter : '*'
				, 	unFilter = true

				if( theAction == 'delete' ){

					var theSection = $(this).data('custom-section')

					$.toolbox('hide')

					bootbox.confirm(
						'<h3>Are you sure?</h3> <p>This will delete this custom section and all linked sections in use.</p>'
						, function( result ){
							if( result === true ){

								$.areaControl.deleteCustomSection( theSection )

								$('.filter-'+theSection).addClass('x-remove') // allows unFilter to remove the deleted item
								removeItems = $('.x-remove')
								that.unFilter( theFilter, removeItems, theIsotope )

							}

							$.toolbox('show')


						})

				} else {
					that.unFilter(theFilter, removeItems, theIsotope)
				}

			})
		}

		, unFilter: function(theFilter, removeItems, theIsotope) {

			removeItems
				.off('click')

			theIsotope
				.isotope({ filter: theFilter })
				.isotope('remove', removeItems)
				.removeClass('x-pane-mode')

		}

		, extensionActions: function(){

			var that = this
			$('.x-extension').on('click.extensionItem', function(){


				var theExtension = $(this)
				,	theIsotope = $(this).parent()
				,	theID = $(this).data('extend-id')
				,	filterID = 'filter-'+theID
				,	filterClass = '.'+filterID
				,	ext = $.pl.config.extensions[theID] || false
				,	panel = theIsotope.data('panel') || false

				if(!theIsotope.hasClass('x-pane-mode') && ext){


					var btnClose = sprintf('<a class="x-close x-remove %s btn btn-close" data-action="close" ><i class="icon icon-chevron-left"></i> %s</a>', filterID, $.pl.lang( "Close" ) )

					var btnDelete = ( theExtension.hasClass('custom-section') ) ? sprintf('<a class="x-close x-remove btn btn-important" data-action="delete" data-custom-section="%s"><i class="icon icon-remove"></i> %s</a>', theID, $.pl.lang( "Delete Section" ) ) : ''

					var btns = sprintf('<div class="x-pane-btns fix">%s %s %s</div>', that.loadButtons( panel, theExtension.data() ), btnClose, btnDelete)

					var desc = sprintf('<div class="x-pane-info"><strong>%s</strong><br/>%s</div>', $.pl.lang( "Description"), ext.desc)




					var extPane = $( sprintf('<div class="x-pane x-remove x-item %s" data-extend-id="%s"><div class="x-pane-pad"><h3 class="x-pane-title">%s</h3>%s  %s</div></div>', filterID, theID, ext.name, btns, desc) )

					if( panel == 'x-sections' ){
						var prep = sprintf('<span class="x-remove badge badge-info %s"><i class="icon icon-arrow-up"></i> %s</span>', filterID, $.pl.lang( "Drag This" ) )

						theIsotope.find('.pl-sortable').append(prep)
					}


					theIsotope
						.isotope('insert', extPane)
						.isotope({filter: filterClass})
						.addClass('x-pane-mode')
				} else {
					plPrint('Extension not in JSON array')
					plPrint($.pl.config.extensions)
				}

				// load actions after elements added to DOM
				that.loadPaneActions( panel )


			})





		}

		, listPopOverStart: function(){
			$('.x-item').popover({
				template: '<div class="popover x-item-popover"><div class="arrow"></div><div class="popover-content"></div></div>'
				, trigger: 'hover'
				, html: true
				, container: $('.pl-toolbox')
				, placement: 'top'
			})

		}

		, listPopOverStop: function(){
			$('.x-item').popover('destroy')


		}


		, listStop: function(){

			var removeItems = $('.x-remove')

			removeItems
				.off('click')

			$('.x-extension')
				.off('click.extensionItem')

		 	$('.x-list.isotope')
				.removeClass('x-pane-mode')
				.isotope( 'remove', removeItems)
				.isotope( { filter: '*' })
				.isotope( 'destroy' )

			//this.listPopOverStop()
		}



	}

}(window.jQuery);
