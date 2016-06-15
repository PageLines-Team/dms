!function ($) {

	$(document).ready(function() {
		
		$('.pl-slider-container').each(function(){
			
			var videoJSPath = $(this).data('videojs')
			,	theDelay = $(this).data('delay') || 12000
			,	theHeight = $(this).data('height') || 500
			,	fullScreen = $(this).data('fullscreen') || "off"
			
			var revAPI = $(this).find('.pl-slider').revolution({
				delay: theDelay,
				startheight:theHeight,
				onHoverStop:"on",
				hideThumbs: 10,
				navigationType:"bullet",
				navigationArrows:"solo",
				navigationStyle:"square",
				navigationHAlign:"center",
				navigationVAlign:"bottom",
				navigationHOffset:0,
				navigationVOffset:20,
				soloArrowLeftHalign:"left",
				soloArrowLeftValign:"center",
				soloArrowLeftHOffset:0,
				soloArrowLeftVOffset:0,
				soloArrowRightHalign:"right",
				soloArrowRightValign:"center",
				soloArrowRightHOffset:0,
				soloArrowRightVOffset:0,
				touchenabled:"on",
				stopAtSlide:-1,
				stopAfterLoops:-1,
				hideCaptionAtLimit:0,
				hideAllCaptionAtLilmit:0,
				hideSliderAtLimit:0,
			
				shadow:0,
				videoJsPath:videoJSPath,
				fullWidth:"on",					
				fullScreen: fullScreen,
				minFullScreenHeight: 400,
				fullScreenOffsetContainer: ".pl-fixed-top, #wpadminbar, .pl-toolbox"
				
			})
				
			
			$(this).find('.tp-leftarrow').html('<i class="icon icon-angle-left"></i>')
			$(this).find('.tp-rightarrow').html('<i class="icon icon-angle-right"></i>')
			
			revAPI.bind("revolution.slide.onchange",function (e,data) {
				
				var slider = $(this)
				,	slide = slider.find('ul').find('li').eq( data.slideIndex - 1 )
				,	container = slider.parent()
				
				container.removeClass('element-dark element-light')
			  
				if( slide.hasClass('element-dark') )
					container.addClass('element-dark')
				else if( slide.hasClass('element-light') )
					container.addClass('element-light')
							
				
			});
			
			
			revAPI.bind("revolution.slide.onafterswap",function (e,data) {
			
				$(window).trigger('resize')
				
				$(this).parent().parent().find('.pl-loader').hide()
				
				$(this).animate({'opacity': 1},500)	
				
			});
			
			
			
			
			
		})
		
	})
	

}(window.jQuery);