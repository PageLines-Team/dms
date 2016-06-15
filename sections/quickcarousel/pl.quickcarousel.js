!function ($) {
	
	$(document).ready(function() {
		
    	
		$('.pl-quickcarousel').each(function(){
			
	    	var theCarousel = $(this)
	    	var columns = (parseInt(theCarousel.attr('data-max'))) ?  parseInt(theCarousel.attr('data-max')) :  5;
	
	    	if($(window).width() < 690 && $('body').attr('data-responsive') == '1') { 
				columns = 2; 
				theCarousel.addClass('phone') 
			}

			if(theCarousel.find('img').length == 0) theCarousel = $('body');

			theCarousel.imagesLoaded( function(instance){
				var tallestImage = 0
				
				theCarousel.find( '> li' ).each(function(){
					tallestImage = ($(this).height() > tallestImage) ?  $(this).height() : tallestImage;
				});
				
				
			
		    	theCarousel.carouFredSel({
						width: "100%",
					  	height: "auto",
			    		circular: true,
			    		responsive: true, 
				        items       : {
					        visible     : {
					            min         : 1,
					            max         : columns
					        }
					    },
					    swipe       : {
					        onTouch     : true,
					        onMouse         : true
					    },
					    scroll: {
					    	items           : 1,
					    	easing          : 'easeInOutCubic',
				            duration        : '800',
				            pauseOnHover    : true
					    },
					    auto    : {
					    	play            : true,
					    	timeoutDuration : 3000
					    }
			    })

			    theCarousel
			    	.parents('.carousel-wrap')
   					.wrap('<div class="carousel-outer">');
   
		    			    


		    })

	    })
	
		//cients carousel height
  		$(window).resize(function(){
  		  			
  			$('.pl-quickcarousel').each(function(){ 

				var theCarousel = $(this)
				,	tallestImage = 0

			  	theCarousel.find( '> li' ).each(function(){
					tallestImage = ($(this).height() > tallestImage) ?  $(this).height() : tallestImage;
				});	

			  	theCarousel
					.css('height',tallestImage)
					.end()
					.parent()
						.css('height',tallestImage)
	
			})
		
		})	
		
		$(window).trigger('resize');
		
	})
}(window.jQuery);