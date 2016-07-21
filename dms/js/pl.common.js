!function ($) {


	// --> Initialize
	$(document).ready(function() {

		if( ! isThemeCustomizer() ) {
			$(document).trigger( 'sectionStart' )
		}


		$.plCommon.init()



		$.plMobilizer.init()

		$(".fitvids").fitVids(); // fit videos

		$.plSupersized.init()

		$.plNavigation.init()

		$.plParallax.init()

		$.plKarma.init()

		$.plGallery.init()

		$.plVideos.init()

		$.plSocial.init()

		$.plAnimate.initAnimation()

		$('.pl-credit').show()

		$.ResizeCanvasVids.init()

		$.BodyClass.init()

		// Master resize trigger
		$(window).trigger('resize')

	})

	/**
	 * code from https://gist.github.com/matthewspencer/71b7973837a39db6a49f
	 */
	function isThemeCustomizer() {
	  if ( window.parent.location === window.location ) {
	    return false;
	  }
	  if ( null === window.parent.document.getElementById( 'customize-preview' ) ) {
	    return false;
	  }
	  return true;
	}

	function getPLFixedHeight(){

		return $('.pl-fixed-top').height() + $('#wpadminbar').height() + $('.pl-toolbox').height()

	}

	function shuffle(o){ //v1.0
		for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
		return o;
	};

	function plRandSort(c) {
	    var o = new Array();
	    for (var i = 0; i < c; i++) {
			o.push(i);

	    }
	    return shuffle(o);
	}


	$(window).load(function() {
		$.plCommon.plVerticalCenter('.pl-centerer', '.pl-centered')
		// $('.pl-section').on('plresize', function(){
		// 			$.plCommon.plVerticalCenter('.pl-centerer', '.pl-centered')
		// 		})
	})

	$.BodyClass = {
		init: function(){
			$(window).resize(function () {
				$.BodyClass.doResize()
			})
			$.BodyClass.doResize()
		}
		,	doResize: function(){
			$(document.body).removeClass('pl-res-phone pl-res-tablet pl-res-desktop')
			var width = $(window).width()
			if( width < 480 ) {
				$(document.body).addClass('pl-res-phone')
			} else if( width < 1024 ){
				$(document.body).addClass('pl-res-tablet')
			} else {
				$(document.body).addClass('pl-res-desktop')
			}
		}
	}

	$.plSupersized = {
		init: function(){
			if ($("#supersized").length > 0){
				$(".site-wrap").addClass('supersized')
				jQuery.supersized({ slides: [{ image : supersize_image.url }]})
			}
		}
	}

	$.ResizeCanvasVids = {
		init: function(){
			$.ResizeCanvasVids.checkLoop()
		}

	,	checkLoop: function(){

			$('.bg-video').each( function(){
				var video = this
				$(video).hide()
				var intervalId = setInterval(function() {
					if (video.readyState === 4) {
						$(window).trigger('resize')
						clearInterval(intervalId)
						$(video).fadeIn('slow')
						return false
					} else {
						plPrint('nope')
					}
				}, 100);
			})
		}
	}

	$.plVideos = {
		init: function(){


			$(window).resize(function () {

				$(".bg-video").each(function () {



					var vid = $(this)
					, 	canvas = vid.closest('.bg-video-canvas')
					,	viewport = vid.parent()

					if( plIsset( this.videoWidth ) ){

						var width = parseInt( this.videoWidth )
						, 	height = parseInt( this.videoHeight )

						canvas.attr('data-height', height)
						canvas.attr('data-width', width)

					} else {

						var width = parseInt( canvas.attr('data-width') )
						, 	height = parseInt( canvas.attr('data-height') )

					}

					$.plVideos.resizeToCover( vid, canvas, viewport, height, width )



				})

			})



			// $('.bg-video-canvas').on('plresize', function(){
			// 			$(window).trigger('resize')
			// 		})

		}

		, resizeToCover: function( vid, canvas, viewport, vH, vW ){


			var canvasWidth	= canvas.width()
			, 	canvasHeight = canvas.height()

		    viewport.width( canvasWidth )
		    viewport.height( canvasHeight )

		    var scale_h = canvasWidth / vW
		    var scale_v = canvasHeight / vH
		    var scale = scale_h > scale_v ? scale_h : scale_v

		    // don't allow scaled width < minimum video width
		    if (scale * vW < 300) {
				scale = 300 / vW
			}

			// console.log('scale ' + scale )
			// 		console.log('canvasWidth ' + canvasWidth )
			// 		console.log('canvasHeight ' + canvasHeight )
			// 		console.log('vW ' + scale * vW )
			// 		console.log('vH ' + scale * vH )
			//
			// 		vid.css('border', '5px solid red')
		    // now scale the video
		    vid.width(scale * vW);
		    vid.height(scale * vH);

		    // and center it by scrolling the video viewport
		    viewport.scrollLeft(( vid.width() - canvasWidth ) / 2);
		    viewport.scrollTop(( vid.height() - canvasHeight ) / 2);

		}
	}

	$.plGallery = {
		init: function(){
			//gallery
			$('.flex-gallery').each(function(){

				var gallery = $(this)
				,	animate = gallery.data('animate') || true
				,	smoothHeight = gallery.data('smoothheight') || true
				,	transition = gallery.data('transition') || 'fade'


				gallery.imagesLoaded( function(instance){



					gallery.flexslider({
				        animation: transition
						, smoothHeight: smoothHeight
						, slideshow: animate
				    })

					if( gallery.find('.slides li').length <= 1 ){
						gallery.find('.flex-direction-nav').hide()
					}

					////gallery slider add arrows
					$('.flex-gallery .flex-direction-nav li a.flex-next').html('<i class="icon icon-angle-right"></i>')
					$('.flex-gallery .flex-direction-nav li a.flex-prev').html('<i class="icon icon-angle-left"></i>')

				});

			});



		}
	}



	$.plKarma = {

		init: function(){

			$('body').on('click','.pl-karma', function() {

					var karmaLink = $(this)
					,	id = karmaLink.attr('id')

					if( karmaLink.hasClass('loved') )
						return false

					if( karmaLink.hasClass('inactive') )
						return false;

					var passData = {
						action: 'pl_karma',
						karma_id: id
					}

					$.post( plKarma.ajaxurl, passData, function( data ){

						karmaLink
							.find( '.pl-social-count' )
							.html( data )
							.end()
								.addClass( 'loved' )
								.attr( 'title', 'You already gave karma!' )
							.end()
								.find( 'span')
								.css({ 'opacity': 1, 'width':'auto' } )

					});

					karmaLink
						.addClass('inactive')

					return false
			})
		}


	}

	$.plNavigation = {
		init: function(){

			var that = this

			// Bootstrap style dropdowns
			that.initDrops()

			// Superfish style dropdowns
			that.initSFMenu()

		}
		, initSFMenu: function(){
			$('.sf-menu').each(function(){

				if( $(this).hasClass('dd-toggle-click') ){
					$(this).superclick({
						 delay: 300,
						 speed: 'fast',
						 speedOut: 'fast',
						 animation:   {opacity:'show'}
					})
				} else {
					$(this).superfish({
						 delay: 800,
						 speed: 'fast',
						 speedOut: 'fast',
						 animation:   {opacity:'show'}
					})
				}

				// needs display: table for appro rendering. so we use visible
				$(this).find('.sub-menu').css('visibility', 'visible')

				var offset = $(this).data('offset') || false

				if( offset ){
					$(this)
						.find('> li > ul')
						.css('top', offset)
				}

				$(this).find('.megamenu').each(function(){
					var cols = $(this).find('> .sub-menu > li').length

					$(this).addClass('mega-col-'+cols)
				})

				$(this).find('.panelmenu').each(function(){
					var cols = $(this).find('> .sub-menu > li').length
					,	colWidth = 180
					,	menuWidth = cols * colWidth
					,	fromLeft = $(this).offset().left
					,	winWidth = $(window).width()
					,	setClass

					if( fromLeft > (winWidth / 2) ){
						setClass = 'panel-right'
					} else
						setClass = 'panel-left'

					$(this)
						.addClass( setClass )
						.find('> .sub-menu')
							.css('width', menuWidth)

				})

			})
		}
		, initDrops: function(){

			var a = 1

			$(".pl-dropdown > li > ul").each(function(){

				var b = ""

				$(this).addClass("dropdown-menu");

				if( $(this).siblings("a").children("i").length===0 ){
					b = ' <i class="icon icon-caret-down"></i>'
				}

				$(this).siblings("a")
					.addClass("dropdown-toggle")
					.attr( "href", "#m" + a )
					.attr("data-toggle","dropdown")
					.append(b)
					.parent()
					.attr( "id", "m" + a++ )
					.addClass("dropdown")

				$(this)
					.find('.sub-menu')
					.addClass("dropdown-menu")
					.parent()
					.addClass('dropdown-submenu')
			})

			$(".dropdown-toggle").dropdown()

		}
	}

	$.plParallax = {


		init: function(speed){

			var that = this
			var width = $(window).width()

			if( $('.pl-parallax-alt').length >= 1){

				$(window).scroll(function(){

					$('.pl-parallax-alt').each( function( element ){

						var scrolltop = $(window).scrollTop()
						var scrollwindow = scrolltop + $(window).height();
						var sectionoffset = $(this).offset().top;
						var backgroundscroll = scrollwindow - sectionoffset;
						if( scrollwindow > sectionoffset ) {
							$(this).css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");
							$(this).css("background-attachment", "fixed")
							$(this).css("background-size", "cover")
							$(this).css("background-repeat", "no-repeat")
						}
					})
				})
			}

			if( $('.pl-parallax').length >= 1){
				$('.pl-parallax').each( function( element ){
					$(this).parallax( '50%', .5, true, 'background' )
				})
			}

			if( $('.pl-parallax-new').length >= 1 ) {
				if( width > 1024 ) {
					$('.pl-parallax-new').each( function( element ){
						var speed = $(this).attr("class").match(/paraspeed-([0-9])\b/)
						var direction = $(this).attr("class").match(/paradirection-([a-z]+)\b/)
						var layer = $(this).attr("class").match(/paralayer-([a-z]+)\b/)
//						$(this).css('background-repeat', 'no-repeat')
						$(this).attr('data-enllax-ratio', speed[1] / 10 )
						$(this).attr('data-enllax-direction', direction[1] )
						$(this).attr('data-enllax-type', layer[1] )
					})
					$(window).enllax()
				}
			}

			if( $('.pl-scroll-translate').length >= 1){

				$('.pl-scroll-translate .pl-area-wrap').each(function(element){

					$(this).parallax('50%', .4, true, 'translate')
				})
			}
			that.windowSizer()
		}

		, windowSizer: function(){

			if( $('.pl-window-height').length >= 1 ){

				$(window).resize(function () {

					$('.pl-window-height').each(function(element){

						var theArea = $(this)
						, 	windowHeight = $(window).height() - getPLFixedHeight()
						,	theContent = theArea.find('.pl-area-wrap')
						,	contentHeight = theContent.outerHeight()
						, 	offsetMargin = (contentHeight / 2) * -1

						if( windowHeight > (contentHeight + 20) ){

							theArea.height(windowHeight).css('min-height', 'auto')


						} else {
							theArea.height(contentHeight + 20).css('min-height', 'auto')
						}

						theContent.css('margin-top', offsetMargin).fadeIn(1000)

					})

				})

			}
		}

	}

	$.plMobilizer = {

		init: function(){
			var that = this

			that.mobileMenu()
		}

		, mobileMenu: function(){
			var that = this
			, 	theBody = $('body')
			, 	menuToggle = $('.mm-toggle')
			,	siteWrap = $('.site-wrap')
			, 	mobileMenu = $('.pl-mobile-menu')

			menuToggle.on('click.mmToggle', function(e){

				e.stopPropagation()
			//	mobileMenu.css('max-height', siteWrap.height())

				if( !siteWrap.hasClass('show-mobile-menu') ){

					siteWrap.addClass('show-mobile-menu')
					mobileMenu.addClass('show-menu')


					$('.site-wrap, .mm-close').one('click', function(){
						siteWrap.removeClass('show-mobile-menu')
						mobileMenu.removeClass('show-menu')
					})
				} else {
					siteWrap.removeClass('show-mobile-menu')
					mobileMenu.removeClass('show-menu')
				}
			})
		}
	}

	$.plSocial = {

		init: function(){

			var that = this

			if ( $('.pl-social-counters').length) {

				var title = $('title')

				that.shareTitle = encodeURI( $("meta[property='pl-share-title']").attr('content') )
				that.shareDesc = encodeURI( $("meta[property='pl-share-desc']").attr('content') )
				that.shareImg = encodeURI( $("meta[property='pl-share-img']").attr('content') ) || false
				that.shareLocation = window.location

				that.loadSocialCounts()
			}

		}

		, loadSocialCounts: function(){
			var that = this

			that.facebook()
			that.twitter() // twitter API no longer public
			that.pinterest()
			that.linkedin()

		}
		, pinterest: function(){

			var that = this
			,	url = '//api.pinterest.com/v1/urls/count.json?url='+that.shareLocation+'&callback=?'
			,	shareBtn = $('[data-social="pinterest"]')

			that.fetchCount(url, shareBtn)

			shareBtn.click( function(){

				var shareUrl = '//pinterest.com/pin/create/button/?url='+that.shareLocation+'&media='+that.shareImg+'&description='+that.shareTitle

				// if no image dont show the button.
				if( that.shareImg ) {
					that.openWindow( shareUrl, 'pinterestShare')
				}

				return false;

			})

		}

		, twitter: function(){

			var that = this
			,	url = '//urls.api.twitter.com/1.1/urls/count.json?url='+that.shareLocation+'&callback=?'
			,	shareBtn = $('[data-social="twitter"]')

			// twitter shutdown the open API
			//	that.fetchCount(url, shareBtn)


			shareBtn.click( function(){

				var shareUrl = '//twitter.com/intent/tweet?text='+ that.shareTitle +' '+that.shareLocation

				that.openWindow( shareUrl, 'twitterShare')

				return false;

			})


		}

		, linkedin: function(){

			var that = this
			,	url = '//www.linkedin.com/countserv/count/share?url='+that.shareLocation+'&callback=?'
			,	shareBtn = $('[data-social="linkedin"]')


			that.fetchCount(url, shareBtn)


			shareBtn.click( function(){

				var shareUrl = '//www.linkedin.com/shareArticle?url='+that.shareLocation+'&title='+that.shareTitle+'&summary='+that.shareDesc


				that.openWindow( shareUrl, 'linkedInShare')

				return false;

			})


		}

		, facebook: function(){

			var that = this
			,	url = "//graph.facebook.com/?id="+ that.shareLocation +'&callback=?'
			,	shareBtn = $('[data-social="facebook"]')


			that.fetchCount(url, shareBtn)

			shareBtn.click( function(){

				var shareUrl = '//www.facebook.com/sharer/sharer.php?u='+that.shareLocation

				that.openWindow( shareUrl, 'fbShare')

				return false;

			})

		}

		, openWindow: function( url, name ){

			var setup = "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0"

			window.open( url, name, setup )

		}

		, fetchCount: function( url, btn ){

			var that = this
			,	editor = $( 'body' ).hasClass('pl-editor')

			// if in the editor, dont do this ... saves loadtime!
			if( editor ) {
				return false
			}

			// SHARE COUNT
			$.getJSON( url, function( data ) {

				var theCount = ( (data.count != 0) && (data.count != undefined) && (data.count != null) ) ? data.count : 0

				theCount = ( (data.shares != 0) && (data.shares != undefined) && (data.shares != null) ) ? data.shares : theCount

				btn
					.find('.pl-social-count')
					.html( theCount )

				that.loadInButton()

			})

		}
		, loadInButton: function(){

		}
	}

	$.plAnimate = {

		initAnimation: function(){

			var that = this

			$.plAnimate.plWaypoints()

		}

		, plWaypoints: function(selector, options_passed){

			var defaults = {
					offset: '85%' // 'bottom-in-view'
					, triggerOnce: true
				}
				, options  = $.extend({}, defaults, options_passed)
				, delay = 150

			$('.pl-animation-group')
				.find('.pl-animation')
				.addClass('pla-group')

			$('.pl-animation:not(.pla-group)').each(function(){

				var element = $(this)

				element.appear(function() {

				  	if( element.hasClass('pl-slidedown') ){

						var endHeight = element.find('.pl-end-height').outerHeight()

						element.css('height', endHeight)

					}


				 	$(this)
						.addClass('animation-loaded')
						.trigger('animation_loaded')

				})

			})

			$('.pl-animation-group').each(function(){

				var element = $(this)

				element.appear(function() {

					var animationNum = $(this).find('.pl-animation').size()
					,	randomLoad = plRandSort(animationNum)



				   	$(this)
						.find('.pl-animation')
						.each( function(i){
							var element = $(this)

							setTimeout(
								function(){
									element
										.addClass('animation-loaded hovered')

									setTimeout(function(){
										element.removeClass('hovered');
									}, 700);
								}
								, (i * 200)
							);
						})

				})

			})



			$('.pl-counter').each(function(){

				var cntr = $(this)

				cntr.appear( function() {

					var the_number = parseInt( cntr.text() )

					cntr.countTo({
							from: 0
						,	to: the_number
						,	speed: 2000
						,	refreshInterval: 30
						, 	formatter: function( value, options){

							value = Math.round( value )
							var n =  value.toString()

							n = n.replace(/\B(?=(\d{3})+(?!\d))/g, ",")

							return n
						}
					})

				})

			})
		}

	}

	$.plCommon = {

		init: function(){
			var that = this
			that.setFixedHeight()

		//	$.resize.delay = 100 // resize throttle

			var fixedTop = $('.pl-fixed-top')
			, 	fixedOffset = ( plIsset( fixedTop[0] ) ) ? fixedTop[0].offsetTop : 0

			// fixedTop.on('plresize', function(){
			// 				that.setHeight()
			// 			})

			$(document).on('ready scroll', function() {
			    var docScroll = $(document).scrollTop()

			    if (docScroll >= fixedOffset) {
			        fixedTop.addClass('is-fixed');
			 		fixedTop.removeClass('is-not-fixed');
			    } else {
					fixedTop.addClass('is-not-fixed');
			        fixedTop.removeClass('is-fixed')
			    }

			})

			$('.pl-make-link').on('click', function(){
				var url = $(this).data('href') || '#'
				, 	newWindow = $(this).attr('target') || false

				if( newWindow )
					window.open( url, newWindow )
				else
					window.location.href = url

			})

			that.handleSearchfield()


		}

		, handleSearchfield: function(){



			$('.searchfield').on('focus', function(e){

				$(this).parent().parent().addClass('has-focus')

			}).on( 'blur', function(e){

				$(this).parent().parent().removeClass('has-focus')

			})

			$('.pl-searcher').on('click touchstart', function(e){

				e.stopPropagation()

				var searchForm = $(this)

				$(this).addClass('has-focus').parent().find( '.searchfield' ).focus()

				$('body').on('click touchstart', function(e){
					searchForm.removeClass('has-focus')
				})
			})

		}

		, setFixedHeight: function(){

			var height = $('.pl-fixed-top').height()

			$('.fixed-top-pusher').height(height)

		}

		, plVerticalCenter: function( container, element, offset ) {

			jQuery( container ).each(function(){

				var colHeight = jQuery(this).height()
				,	centeredElement = jQuery(this).find( element )
				,	infoHeight = centeredElement.height()
				, 	offCenter = offset || 0

				centeredElement.css('margin-top', ((colHeight / 2) - (infoHeight / 2 )) + offCenter )
			})

		}


	}

}(window.jQuery);
