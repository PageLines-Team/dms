/*
Plugin: jQuery Parallax
Version 1.1.3
Author: Ian Lunn
Twitter: @IanLunn
Author URL: http://www.ianlunn.co.uk/
Plugin URL: http://www.ianlunn.co.uk/plugins/jquery-parallax/

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

(function( $ ){
	var $window = $(window);
	var windowHeight = $window.height();

	$window.resize(function () {
		windowHeight = $window.height();
	});

	$.fn.parallax = function(xpos, speedFactor, outerHeight, theMode) {
		alert('hi')
		var $this = $(this);
		var getHeight;
		var firstTop;
		var paddingTop = 0;
		
		var theMode = theMode || 'background'
		
		//get the starting position of each element to have parallax applied to it		
		$this.each(function(){
		    firstTop = $this.offset().top;
		});

		if (outerHeight) {
			getHeight = function(jqo) {
				return jqo.outerHeight(true);
			};
		} else {
			getHeight = function(jqo) {
				return jqo.height();
			};
		}
			
		// setup defaults if arguments aren't specified
		if (arguments.length < 1 || xpos === null) xpos = "50%";
		if (arguments.length < 2 || speedFactor === null) speedFactor = 0.1;
		if (arguments.length < 3 || outerHeight === null) outerHeight = true;
		
		// function to be called whenever the window is scrolled or resized
		function update(){
			var pos = $window.scrollTop();				
			
			$this.each(function(){
				var $element = $(this);
				var top = $element.parent().offset().top;
				var height = getHeight($element);
				var fixedHeight = $('.pl-fixed-top').height()
				$this.css('border', '5px solid red')
				// Check if totally above or totally below viewport
				if (top + height < pos || top > pos + windowHeight) {
					return;
				}

				var trns = pos + fixedHeight - top
			
				if( theMode == 'translate' && trns > 0){
					
						var diff =  (trns + height) / (trns * 6);
						
						
 						if (diff > 1) 
							diff = 1;
			            else if (diff < 0) 
							diff = 0;

						$this
							.css('transform', 'translate(0, ' + Math.round( .6 * trns ) + 'px)' )
							.find('.pl-content')
								.css('opacity', diff)
						
				} else if( theMode == 'translate' ){
					$this
						.css( 'transform', 'translate(0, 0)' )
						.find('.pl-content')
							.css('opacity', 1)
				}
				
				if(  theMode == 'background' ){
					
					$this.css('backgroundPosition', xpos + " " + Math.round((-100 - pos) * speedFactor) + "px");
					
				}
					
				
			
			});
		}		

		$window.bind('scroll', update).resize(update);
		update();
	};
})(jQuery);
