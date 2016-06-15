<?php

class EditorLayout {


	function __construct(  ){
		
		add_filter('pl_settings_array', array( $this, 'add_settings'));
		add_filter('pless_vars', array( $this, 'add_less_vars'));
		add_filter('pagelines_body_classes', array( $this, 'add_body_classes'));

	}
	
	function add_body_classes($classes){
		
		$classes[] = ( pl_setting( 'layout_display_mode' ) == 'display-boxed' ) ? 'display-boxed' : 'display-full';
	
		return $classes;
		
	}
	
	function add_settings( $settings ){

		$settings['layout'] = array(
			'name' 	=> __( 'Layout <span class="spamp">&amp;</span> Nav', 'pagelines' ),
			'icon' 	=> 'icon-fullscreen',
			'pos'	=> 2,
			'opts' 	=> $this->options()
		);

		return $settings;
	}
	
	
	function options(){



		$settings = array(
			array(
				'key'		=> 'layout_opts',
				'type' 		=> 'multi',
				'title' 	=> __( 'Layout Configuration', 'pagelines' ),
				'opts' 		=> array(
					array(
						'key'		=> 'layout_mode',
						'type' 		=> 'select',
						'label' 	=> __( 'Select Content Width Mode', 'pagelines' ),
						'title' 	=> __( 'Layout Mode', 'pagelines' ),
						'opts' 		=> array(
							'pixel' 	=> array('name' => __( 'Pixel Width Based Layout', 'pagelines' )),
							'percent' 	=> array('name' => __( 'Percentage Width Based Layout', 'pagelines' ))
						),
						'default'	=> 'pixel',
					),
					array(
						'key'		=> 'layout_display_mode',
						'type' 		=> 'select',
						'label' 	=> __( 'Select Layout Display', 'pagelines' ),
						'title' 	=> __( 'Display Mode', 'pagelines' ),
						'opts' 		=> array(
							'display-full' 		=> array('name' => __( 'Full Width Display', 'pagelines' )),
							'display-boxed' 	=> array('name' => __( 'Boxed Display', 'pagelines' ))
						),
						'default'	=> 'display-full',
					),
				),
			),
			
			
			

		);


		$settings[] = array(

			'key'		=> 'layout_navigations',
			'col'		=> 2,
			'type' 		=> 'multi',
			'label' 	=> __( 'Default Navigation Setup', 'pagelines' ),
			'help'	 	=> __( 'These will be used in mobile menus and optionally other places throughout your site.', 'pagelines' ),
			'opts'	=> array(
				array(
					'key'		=> 'primary_navigation_menu',
					'type' 		=> 'select_menu',
					'label' 	=> __( 'Primary Navigation Menu', 'pagelines' ),
				
					
				),
				array(
					'key'		=> 'secondary_navigation_menu',
					'type' 		=> 'select_menu',
					'label' 	=> __( 'Secondary Navigation Menu', 'pagelines' ),
				
				),
				
				array(
					'key'		=> 'nav_dropdown_bg',
					'type' 		=> 'select',
					'label' 	=> __( 'Standard Nav Dropdown Background', 'pagelines' ),
					'default'	=> 'dark',
					'opts' 		=> array(
						'dark' 		=> array('name' => __( 'Dark Dropdowns', 'pagelines' )),
						'light' 	=> array('name' => __( 'Light Dropdowns', 'pagelines' ))
					),
				),
				
				array(
					'key'		=> 'nav_dropdown_toggle',
					'type' 		=> 'select',
					'label' 	=> __( 'Standard Nav Dropdown Toggle', 'pagelines' ),
					'default'	=> 'hover',
					'opts' 		=> array(
						'hover' 	=> array('name' => __( 'On Hover', 'pagelines' )),
						'click' 	=> array('name' => __( 'On Click', 'pagelines' ))
					),
				),
				array(
					'key'		=> 'mobile_menus_disable_search',
					'type' 		=> 'check',
					'label' 	=> __( 'Disable Mobile Menu Search Field', 'pagelines' ),
					'default'	=> 'false'
				)
			)
		);
		
		

		return apply_filters('pl_layout_settings', $settings);

	}


	function add_less_vars( $less_vars ){

		// if pixel mode assign pixel option

		if( pl_setting( 'layout_mode' ) == 'percent' )
			$value = (pl_setting( 'content_width_percent' )) ? pl_setting( 'content_width_percent' ) : '80%';
		else
			$value = (pl_setting( 'content_width_px' ) && pl_setting( 'content_width_px' ) != '') ? pl_setting( 'content_width_px' ) : '1100px';

	
		// if percent mode assign percent option

		$less_vars['plContentWidth'] = $value;
		$less_vars['pl-page-width'] = $value;

		return $less_vars;

	}

	function get_layout_mode(){

		$value = (pl_setting( 'layout_mode' )) ? pl_setting( 'layout_mode' ) : 'pixel';

		return $value;

	}


}
