<?php
/*
	Section: Maps
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Google maps with markers.
	Class Name: PLMaps
	Filter: component, dual-width
	Loading: active
*/


class PLMaps extends PageLinesSection {

	public $lat = '37.774929';
	public $lng = '-122.419416';
	public $desc = '<a href="http://www.pagelines.com">PageLines</a>';
	public $help = 'To find map the coordinates use this easy tool: <a target="_blank" href="http://www.mapcoordinates.net/en">ww.mapcoordinates.net</a>';

	function section_styles(){

		wp_enqueue_script( 'google-maps', 'https://maps.google.com/maps/api/js?sensor=false', NULL, NULL, true );
		wp_enqueue_script( 'pl-maps', $this->base_url.'/maps.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function section_head() {
		$locations = $this->opt('locations_array');
		$maps = array();
		$defaults = array(
			'lat'	=> floatval( $this->lat ),
			'lng'	=> floatval( $this->lng ),
			'mapinfo'	=> $this->desc,
			'image'		=> $this->base_url.'/marker.png'
		);

		if( ! is_array( $locations ) ) {
			$maps = array(
					1 => $defaults
			);
		} else {
			$maps = array();
			$i = 1;
			foreach( $locations as $k => $data ) {

				$maps[$i] = array(
					'lat'	=> ( isset( $data['latitude'] ) ) ? floatval( $data['latitude'] ): $this->lat,
					'lng'	=> ( isset( $data['longitude'] ) ) ? floatval( $data['longitude'] ) : $this->lng,
					'mapinfo'	=> ( isset( $data['text'] ) && '' != $data['text'] ) ? $data['text'] : $this->desc,
					'image'		=> ( isset( $data['image'] ) && '' != $data['image'] ) ? do_shortcode( $data['image'] ) : $this->base_url.'/marker.png'
				);
				$i++;
			}
		}

		$main = array(
			'lat'			=> $this->opt( 'center_lat', array( 'default' => $this->lat ) ),
			'lng'			=> $this->opt( 'center_lng', array( 'default' => $this->lng ) ),
			'zoom_level'	=> floatval( $this->opt( 'map_zoom_level', array( 'default' => 10 ) ) ),
			'zoom_enable'	=> $this->opt( 'map_zoom_enable', array( 'default' => true ) ),
			'enable_animation' => $this->opt( 'enable_animation', array( 'default' => true ) ),
			'image'			=> $this->base_url.'/marker.png'
		);

		wp_localize_script( 'pl-maps', 'map_data_' . $this->meta['unique'], $maps );

		wp_localize_script( 'pl-maps', 'map_main_' . $this->meta['unique'], $main );
	}

	function section_opts(){


		$options = array();

		$options[] = array(
				'type'	=> 'multi',
				'key'	=> 'plmap_config',
				'title'	=> __( 'Google Maps Configuration', 'pagelines' ),
				'col'	=> 1,
				'opts'	=> array(

					array(
						'key'	=> 'center_lat',
						'type'	=> 'text_small',
						'default'	=> $this->lat,
						'place'		=> $this->lat,
						'label'	=> __( 'Latitude', 'pagelines' ),
						'help'	=> $this->help
					),
					array(
						'key'	=> 'center_lng',
						'type'	=> 'text_small',
						'default'	=> $this->lng,
						'place'	=> $this->lng,
						'label'	=> __( 'Longitude', 'pagelines' ),
						'help'	=> $this->help
					),

					array(
						'type'	=> 'select',
						'key'	=> 'map_height',
						'default'	=> '350px',
						'label'	=> __( 'Select Map Height ( default 350px)', 'pagelines' ),
						'opts'	=> array(
							'200px'	=> array( 'name' => '200px'),
							'250px'	=> array( 'name' => '250px'),
							'300px'	=> array( 'name' => '300px'),
							'350px'	=> array( 'name' => '350px'),
							'400px'	=> array( 'name' => '400px'),
						)
					),
						array(
							'type'	=> 'count_select',
							'key'	=> 'map_zoom_level',
							'default'	=> '12',
							'label'	=> __( 'Select Map Zoom Level ( default 10)', 'pagelines' ),
							'count_start'	=> 1,
							'count_number'	=> 18,
							'default'		=> '10',
						),
						array(
							'type'	=> 'check',
							'key'	=> 'map_zoom_enable',
							'label'	=> __( 'Enable Zoom Controls', 'pagelines' ),
							'default'		=> true,
							'compile'		=> true,
						),
					array(
						'type'	=> 'check',
						'key'	=> 'enable_animation',
						'label'	=> __( 'Enable Animations', 'pagelines' ),
						'default'		=> true,
						'compile'		=> true,
					),
				)

			);

		$options[] = array(
			'key'		=> 'locations_array',
	    	'type'		=> 'accordion',
			'col'		=> 2,
			'opts_cnt'	=> 1,
			'title'		=> __('Pointer Locations', 'pagelines'),
			'post_type'	=> __('Location', 'pagelines'),
			'opts'	=> array(
				array(
					'key'		=> 'image',
					'label' 	=> __( 'Pointer Image', 'pagelines' ),
					'type'		=> 'image_upload',
					'help'		=> __( 'For best results use an image size of 64 x 64 pixels.', 'pagelines' )
				),
				array(
					'key'	=> 'latitude',
					'label'	=> __( 'Latitude', 'pagelines' ),
					'type'	=> 'text_small',
					'place'	=> '51.464382',
					'help'	=> $this->help,
				),
				array(
					'key'	=> 'longitude',
					'label'	=> __( 'Longitude', 'pagelines' ),
					'type'	=> 'text_small',
					'place'	=> '-0.256505',
					'help'	=> $this->help,
				),
				array(
					'key'	=> 'text',
					'label'	=> 'Location Description',
					'type'	=> 'textarea',
					'default'	=> $this->desc,
					'place'		=> $this->desc
				)
			)
	    );
		return $options;
	}

   function section_template( ) {
		$height = $this->opt( 'map_height', array( 'default' => '350px' ) );	
		printf( '<div class="pl-map-wrap pl-animation pl-slidedown"><div id="pl_map_%s" data-map-id="%s" class="pl-map pl-end-height" style="height: %s"></div></div>', $this->meta['unique'], $this->meta['unique'], $height );
	}
}