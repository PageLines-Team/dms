


Object.keys = Object.keys || function(o) {
	
    var result = []

    for(var name in o) {
	
        if (o.hasOwnProperty(name))
          result.push(name)

    }

    return result
}

jQuery(document).ready(function($){

	jQuery('.pl-map').each(function(){

		var id = jQuery(this).data('map-id')
		,	map_main = window["map_main_" + id]
		,	map_data = window["map_data_" + id]

		runmap(id,map_main,map_data)
	})

	function runmap(id, map_main, map_data){


    var zoomLevel = parseFloat(map_main.zoom_level) || 12
    ,	centerlat = parseFloat(map_main.lat) || 37.7830061
	,	centerlng = parseFloat(map_main.lng) || -122.3902466
	,	markerImg = map_main.image
	,	enableZoom = map_main.zoom_enable || true
	,	latLng = new google.maps.LatLng(centerlat,centerlng)
	,	mobile = jQuery( 'body' ).hasClass('pl-res-phone' ) || false
	,	tablet = jQuery( 'body' ).hasClass('pl-res-tablet' ) || false
	
	var mapOptions = {
		center: latLng,
		zoom: zoomLevel,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		scrollwheel: false,
		panControl: false,
		zoomControl: enableZoom,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.LARGE,
			position: google.maps.ControlPosition.LEFT_CENTER
		},
		mapTypeControl: false,
		scaleControl: false,
		streetViewControl: false

    }

	if( mobile || tablet ) {
		mapOptions.minZoom = zoomLevel
		mapOptions.maxZoom = zoomLevel
		mapOptions.draggable = false
		mapOptions.scrollwheel = false
		mapOptions.panControl = false
		mapOptions.zoomControl = false
	}
	
	
    var div = "pl_map_" + id

	var map = new google.maps.Map(document.getElementById(div), mapOptions);

	google.maps.event.addListenerOnce(map, 'tilesloaded', function() {
	
		//don't start the animation until the marker image is loaded if there is one
		if(markerImg.length > 0) {
			var markerImgLoad = new Image();
			markerImgLoad.src = markerImg;
	
			$(markerImgLoad).load(function(){
				 setMarkers(map,id);
			});
		}
		else {
			setMarkers(map,id);
		}
	    });

    }

    function setMarkers(map, id) {

		var	map_main = window["map_main_" + id]
		,	map_data = window["map_data_" + id]
		,	enableAnimation = map_main.enable_animation || true
		,	animationDelay = 180
		var infoWindows = [];

	   	if ( 1 == enableAnimation ){
			enableAnimation = google.maps.Animation.BOUNCE
		} else {
			enableAnimation = false
		}
		for (var i = 1; i <= Object.keys(map_data).length; i++) {

			(function(i) {
				setTimeout(function() {

					var image = (map_data[i].image) || markerImg

			      var marker = new google.maps.Marker({
			      	position: new google.maps.LatLng(map_data[i].lat, map_data[i].lng),
			        map: map,
					infoWindowIndex : i - 1,
					animation: enableAnimation,
					icon: image,
					optimized: true
			      });
				  setTimeout(function(){marker.setAnimation(null);},200);

			      //infowindows
			      var infowindow = new google.maps.InfoWindow({
			   	    content: map_data[i].mapinfo,
			    	maxWidth: 400
				  });

				  infoWindows.push(infowindow);

			      google.maps.event.addListener(marker, 'click', (function(marker, i) {
			        return function() {
			        	infoWindows[this.infoWindowIndex].open(map, this);
			        }

			      })(marker, i));

		         }, i * animationDelay);


		     }(i));


		 }//end for loop
	}//setMarker

})
