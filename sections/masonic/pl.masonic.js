!function ($) {

	$(document).ready(function() {




		plMasonryLayout()
		$(window).resize( plMasonryLayout )

		$('.masonic-nav a').click(function(e){
			 e.preventDefault()


			var theLink = $(this)
			,	theFilter = theLink.text()
			// highlight
			$('.masonic-nav li').removeClass('pl-link active')
			theLink.parent().addClass('pl-link active')


			// title text
			$('.masonic-title').text( theFilter )

			 //add css animation only for sorting
				var clearIsoAnimation = null;
			  clearTimeout(clearIsoAnimation);
			  $('.isotope, .isotope .isotope-item').css('transition-duration','0.7s');
			  clearIsoAnimation = setTimeout(function(){  $('.isotope, .isotope .isotope-item').css('transition-duration','0s'); },700);

			var selector = $(this).attr('data-filter')
			, 	theIsotope = $(this).closest('.masonic-wrap').find('.isotope')

			theIsotope
				.isotope({ filter: selector })

			return false;
		})


		function plMasonryLayout( ){

				var scrollSpeed
				, 	easing

				,	numberCols = 3

				$('.masonic-gallery').each(function(  ){

						var theGallery = $(this)
						, 	format = theGallery.data('format')
						,	layoutMode = ( format == 'grid' ) ? 'fitRows' : 'masonry'
						, 	shown = theGallery.data('shown') || 3
						,	scrollSpeed = theGallery.data('scroll-speed') || 700
						,	easing = theGallery.data('easing') || 'linear'

						theGallery.imagesLoaded(  function(){

							var windowWidth = window.innerWidth
							,	galWidth = theGallery.width()
							,	masonrySetup = { }
							,	numCols

							if( windowWidth >= 1620 ){
								numCols = 5
							} else if ( windowWidth >= 1300 ){
								numCols = 4
							} else if ( windowWidth >= 990 ){
								numCols = 3
							} else if ( windowWidth >= 470 ){
								numCols = 2
							} else {
								numCols = 1
							}

							masonrySetup = {
								columnWidth: parseInt( galWidth / numCols )
							}


							theGallery.isotope({
								resizable: false,
								itemSelector : 'li',
								filter: '*',
								layoutMode: layoutMode,
								masonry: masonrySetup
							}).isotope( 'reLayout' )


						})

				})


		}


	})
}(window.jQuery);
