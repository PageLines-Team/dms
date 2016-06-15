!function ($) {
	
	
	
	$(document).ready(function() {
		
		var docHeight = $(document).height()
		
		
		function setStickySidebar(){
			$('.docker-wrapper').each(function(){

				var stdOffset = 20
				,	theWrapper = $(this)
				,	theSidebar = theWrapper.find('.docker-sidebar')
				,	sidebarTopOff = $('.pl-fixed-top').height() + theSidebar.position().top + $('#wpadminbar').height() + stdOffset 
				,	sidebarBottomOff = docHeight + stdOffset*2 - theWrapper.offset().top - theWrapper.height() 


				 theSidebar.sticky({
							topSpacing: sidebarTopOff
							, bottomSpacing: sidebarBottomOff
						})


			})
			
		}
		
		$('.docker-mobile-drop').on('click', function(){
			var theList = $(this).next()
			
			if( theList.hasClass('show-me') )
				theList.removeClass('show-me')
			else 
				theList.addClass('show-me')
				
		})
		
		setStickySidebar()
		

		$(window).resize(function(){
			
			$('.docker-sidebar').sticky('update')
			
		})
		
	})
}(window.jQuery);