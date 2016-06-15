!function ($) {

	$(document).ready(function() {
		
		/***************** Testimonial Slider ******************/

		//fadeIn
	//	$('.testimonial_slider').animate({'opacity':'1'},800);

		//testimonial slider controls
		$('body').on('click testimonal-click','.pl-testimonials-container .controls li', function( e ){

			e.stopPropagation()
			
			var theTestimonials = $(this).parents('.pl-testimonials-container')
			, 	$index = $(this).index()
			,	currentHeight = theTestimonials.find('.the-testimonial').eq($index).height()
			
			if( $(this).hasClass('active') ) 
				return false

			theTestimonials
				.find('.nav-switch')
				.removeClass('active')
				
			$(this)
				.addClass('active')

			theTestimonials
				.find('.current-testimonial')
				.stop()
				.animate({'opacity':'0','left':'25px', 'z-index': '-1'},400,'easeOutCubic', function(){
					$(this).css({'left':'-25px'})
				})
				
				
			theTestimonials
				.find('.the-testimonial')
				.eq($index)
				.stop(true,true)
				.addClass('current-testimonial')
				.animate({'opacity':'1','left':'0'},600,'easeOutCubic')
				.css('z-index','20')
				
			theTestimonials
				.find('.pl-testimonials')
				.stop(true,true)
				.animate( {'height' : currentHeight + 20 + 'px' }, 450, 'easeOutCubic' )

		})

		//create controls
		$('.pl-testimonials-container').each(function(){

			

			var theTestimonials = $(this)
			,	slideNum = theTestimonials.find('.the-testimonial').length
			,	autoRotate = $(this).data('auto')
			,	autoSpeed = parseInt( $(this).data('speed') ) || 6000
			,	navMode = $(this).data('mode') || 'default'
			,	navTheme = ( navMode == 'avatar' ) ? 'nav-avatar' : 'nav-theme'
						
			theTestimonials
				.append( sprintf('<div class="controls "><ul class="%s"></ul></div>', navTheme ) )
				
			var theControls = theTestimonials.find('.controls ul')

			theTestimonials.find('.the-testimonial').each( function(i){
				
				var testimonial = $(this)
				
				if( navMode == 'avatar' ){
				
					var avatar = testimonial.data('avatar')
				
					theControls
						.append( sprintf('<li class="nav-switch"><span style="background-image: url(%s);"></span></li>', avatar) )
					
				} else {
					
					theControls
						.append('<li class="nav-switch"><span></span></li>')
				}
				
			})

			theTestimonials
			.find('.controls ul li')
			.first()
			.click()

			//autorotate
			if( autoRotate ) {
				var theRotation = setInterval( function(){ testimonialRotate( theTestimonials ) }, autoSpeed )				
			}

			theTestimonials.find('.controls li').on('testimonal-click', function(e){				
				if(typeof e.clientX != 'undefined') 
					clearInterval( theRotation )			
			})			
		})
		
		function testimonialRotate(slider){

			var $testimonialLength = slider.find('li').length
			,	$currentTestimonial = slider.find('.nav-switch.active').index()
			
			if( $currentTestimonial+1 == $testimonialLength) {
				slider.find('ul li:first-child').trigger('testimonal-click');
			} else {
				slider.find('.nav-switch.active').next('li').trigger('testimonal-click');
			}

		}

		function testimonialHeightResize(){
			$('.pl-testimonials-container').each(function(){

				var $index = $(this).find('.controls ul li.active').index()
				,	currentHeight = $(this).find('.pl-testimonials .the-testimonial').eq($index).height()
				
				$(this)
					.find('.pl-testimonials')
					.stop(true,true)
					.animate( {'height' : currentHeight + 20 + 'px' }, 450, 'easeOutCubic' );

			});
		}

		
		
	
		
	})
	

}(window.jQuery);