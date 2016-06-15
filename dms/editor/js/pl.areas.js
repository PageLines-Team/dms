!function ($) {

	$.areaControl = {

        toggle: function(btn) {

			if(!jQuery.areaControl.isActive){

				$('body')
					.addClass('area-controls')
					.find('area-tag')
					
				//.effect('highlight')

				btn.addClass('active')

				jQuery.areaControl.isActive = true

				jQuery.areaControl.listen()

			} else {
				btn.removeClass('active')
				jQuery.areaControl.isActive = false
				$('body').removeClass('area-controls')

			}

		}

		, listen: function() {
			
			
			
			$('.area-control:not(".pro-only-disabled")').on('click.areaControl', function(e){
				e.preventDefault()

				var action = $(this).data('area-action')

				if(action == 'clone' ){
					$.areaControl.areaTools('clone', $(this))
					
				} else if (action == 'settings' ){
					$.areaControl.areaSettings($(this))

				} else if (action == 'delete' ){
					$.areaControl.deleteArea($(this))

				} else if (action == 'save' ){
					$.areaControl.saveArea($(this))

				} else if (action == 'unlock' ){
					$.areaControl.unlockArea($(this))
				}
			})


		}
		
		, unlockArea: function( btn ){
		
			var that = this
			,	theArea = btn.closest('.pl-area')
			,	theID = theArea.attr('id')
			, 	scope = plItemScope( theArea )
			
			theArea.removeClass('editing-locked custom-section')
			
			$.plDatas.handleNewItemData( theArea )
			
			$.pageBuilder.reloadConfig( {refresh: false } )
			
			$.pageBuilder.reloadAllEvents()
		
		}
		
		, deleteCustomSection: function( key ){
			
			
			
			var args = {
						mode: 'set_user_section'
					,	run: 'delete'
					, 	log: true
					,	key: key
					,	postSuccess: function( response ){
							if( !response )
								return
						
						}
				
				}
			

		
			var response = $.plAJAX.run( args )
		}
		
		, saveArea: function( btn ){
			
			var that = this
			,	theArea = btn.closest('.pl-area')
			,	theID = theArea.attr('id')
		
			var message = sprintf('<h4><i class="icon icon-save"></i> Save New Custom Section</h4><form class="modal-form" data-area="%s"><input name="name" placeholder="Section Name..." type="text" val="" /><textarea name="desc" placeholder="Description"></textarea></form>', theID)
		
		
			bootbox.confirm(
				message
				, '<i class="icon icon-remove"></i> Cancel'
				, '<i class="icon icon-save"></i> Save'
				, function( result ){
					if( result == true ){

						var areaID = $('.modal-form').data('area')
						,	theArea = $('#'+areaID)
						, 	settings = {}
						,	form = $('.modal-form').formParams()
						,	config = $.extend({}, $.plMapping.getAreaMapping( theArea ), form)
						,	args = {
									mode: 'set_user_section'
								,	run: 'create'
								, 	log: true
								,	config: config
								,	postSuccess: function( response ){
										if( !response )
											return
											
										theArea
											.addClass('custom-section editing-locked')	
											.data('custom-section', response.key)
											.attr('data-custom-section', response.key)
											
										// Reload page with custom sections tab open
										$.pageBuilder.reloadConfig( {location: 'new custom section', refresh: true, refreshArgs: '?tablink=add-new&tabsublink=custom'} )
									}
							
							}
						,	args = $.extend({}, args, form) // add form fields to post

					
						var response = $.plAJAX.run( args )
					
					}
				})
		}
		
		, areaTools: function( action, btn ){

			var that = this
			,	theArea = btn.closest('.pl-area')
				
			if( action == 'clone'){
				
				var	cloned = theArea.clone( false )

				cloned
					.insertAfter( theArea )
					.hide()
					.slideDown()
					
				cloned.find('.area-control').data('tooltip', false).tooltip('destroy')
				cloned.find('.area-control').tooltip({placement: 'top'})

				$.plDatas.handleNewItemData( cloned )
				
				$.pageBuilder.reloadAllEvents()
			}

		}

		
		, areaSettings: function( btn ){

			var that = this
			,	theArea = btn.closest('.pl-area')
			,	theID = theArea.attr('id')
			,	object = theArea.data('object') || false

			var config	= {
					sid: theArea.data('sid')
					, sobj: theArea.data('object')
					, clone: theArea.data('clone')
					, uniqueID: theArea.data('clone')
					, scope: ( theArea.parents(".template-region-wrap").length == 1 ) ? 'local' : 'global'
				}

			$('body').toolbox({
				action: 'show'
				, panel: 'section-options'
				, info: function(){

					$.optPanel.render( config )

				}
			})

		}


		, deleteArea: function( btn ){

			var currentArea = btn.closest('.pl-area')
			, 	confirmText = $.pl.lang("<h3>Are you sure?</h3><p>This action will delete this area and all its elements from this page.</p>")

			bootbox.confirm( confirmText, function( result ){
				if(result == true){

					currentArea.slideUp(500, function(){
						$.plDatas.setElementDelete( currentArea )
						$.pageBuilder.reloadConfig( {location: 'area-delete'} )
					
					})


				}

			})



		}

	

	}
}(window.jQuery);