!function ($) {


	/*
	 * Developer Functions
	 */
	$.plDev = {
		
		init: function(){
						
			$('.plprint-container').hide()
			
			$( "body" ).on( "pl-tab-build", function( event, tab ) {
				
				var theTab = tab
				,	tabMeta = theTab.attr('data-tab-meta') || ''
				, 	tabAction = theTab.attr('data-tab-action') || ''
				,	tabPanel = $("[data-panel='"+tabAction+"']")
				, 	output = ''
				
				if( theTab.hasClass('tab-dev_log') ){
					
					if( $('.plprint-container').length != 0 ){
						
						$('.plprint-container').each( function(){
							output += '<div class="alert editor-alert">Print</div>'
							output += $(this).html()
						})
						
						
						
					}
					
				} else if( theTab.hasClass('tab-dev-page') ){
					
					var tbl = ''
					for ( var key in $.plDevData ) {
						if ($.plDevData.hasOwnProperty(key)) {
							var obj = $.plDevData[key];


							tbl += sprintf( '<tr><th>%s</th><td>%s</td><td>%s</td></tr>', obj.title, obj.num, obj.info )

						}
					}
					
					// localstorage
					tbl += sprintf( '<tr><th>LocalStorage</th><td>%s</td><td>How much space is used.</td></tr>', localStorageSpace() )
					
					output += sprintf( '<table class="data-table" >%s</table>', tbl )
					
				}
				
				if( output != ''){
					// weird, not fun, load order issues. Do this once on initial, again after settings have a chance to fill in
					// needs to be after tabs setup because we're also using setting which fill the pane
					tabPanel.find('.opt-fill-in').html( output )
					$('body').one('panelSetup', function(e, panel){
						tabPanel.find('.opt-fill-in').html( output )
					})
					
				}
				
			} );
		}

	}



}(window.jQuery);