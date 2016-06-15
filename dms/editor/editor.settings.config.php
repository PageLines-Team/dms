<?php
/**
 *
 *
 *  PageLines Default/Standard Options Lib
 *
 *
 *  @package PageLines DMS
 *  @since 3.0.0
 *
 *
 */
class EditorSettings {

	public $settings = array( );


	function __construct(){

		if( ! pl_is_wporg() ) {
			$this->settings['basic_settings'] = array(
				'name' 	=> __( 'Site Images', 'pagelines' ),
				'icon'	=> 'icon-picture',
				'pos'	=> 2,
				'opts' 	=> $this->basic()
				);
			}

		$this->settings['social_media'] = array(
			'name' 	=> __( 'Social <span class="spamp">&amp;</span> Local', 'pagelines' ),
			'icon'	=> 'icon-comments',
			'pos'	=> 5,
			'opts' 	=> $this->social()
		);
		
		$this->settings['advanced'] = array(
			'name' 	=> __( 'Advanced', 'pagelines' ),
			'icon'	=> 'icon-wrench',
			'pos'	=> 50,
			'opts' 	=> $this->advanced()
		);

		$this->settings['resets'] = array(
			'name' 	=> __( 'Resets', 'pagelines' ),
			'icon'	=> 'icon-undo',
			'pos'	=> 55,
			'opts' 	=> $this->resets()
		);
		
	}
	



	function get_set( ){

		$settings =  apply_filters('pl_settings_array', $this->settings);

		$default = array(
			'icon'	=> 'icon-edit',
			'pos'	=> 100
		);

		foreach($settings as $key => &$info){
			$info = wp_parse_args( $info, $default );
		}
		unset($info);

		uasort($settings, "cmp_by_position" );

		return apply_filters('pl_sorted_settings_array', $settings);
	}

	function cmp_by_position($a, $b) {

		if( isset( $a['pos'] ) && is_int( $a['pos'] ) && isset( $b['pos'] ) && is_int( $b['pos'] ) )
			return $a['pos'] - $b['pos'];
		else
			return 0;
	}

	function basic(){

		$settings = array(

			array(
				'key'			=> 'pagelines_favicon',
				'label'			=> __( 'Upload Favicon (32px by 32px)', 'pagelines' ),
				'type' 			=> 	'image_upload',
				'imgsize' 			=> 	'16',
				'extension'		=> 'ico,png', // ico support
				'title' 		=> 	__( 'Favicon Image', 'pagelines' ),
				'help' 			=> 	__( 'Enter the full URL location of your custom <strong>favicon</strong> which is visible in browser favorites and tabs.<br/> <strong>Must be .png or .ico file - 32px by 32px</strong>.', 'pagelines' ),
				'default'		=>  '[pl_parent_url]/images/default-favicon.png'
			),


			array(
				'key'			=> 'pl_login_image',
				'type' 			=> 	'image_upload',
				'col'			=> 2,
				'label'			=> __( 'Upload Login Image (80px Height)', 'pagelines' ),
				'imgsize' 			=> 	'80',
				'sizemode'		=> 'height',
				'title' 		=> __( 'Login Page Image', 'pagelines' ),
				'default'		=> '[pl_parent_url]/images/default-login-image.png',
				'help'			=> __( 'This image will be used on the login page to your admin. Use an image that is approximately <strong>80px</strong> in height.', 'pagelines' )
			),

			array(
				'key'			=> 'pagelines_touchicon',
				'col'			=> 3,
				'label'			=> __( 'Upload Touch Image (144px by 144px)', 'pagelines' ),
				'type' 			=> 	'image_upload',
				'imgsize' 			=> 	'72',
				'title' 		=> __( 'Mobile Touch Image', 'pagelines' ),
				'default'		=> '[pl_parent_url]/images/default-touch-icon.png',
				'help'			=> __( 'Enter the full URL location of your Apple Touch Icon which is visible when your users set your site as a <strong>webclip</strong> in Apple Iphone and Touch Products. It is an image approximately 144px by 144px in either .jpg, .gif or .png format.', 'pagelines' )
			),

		);

		return $settings;

	}


	function social(){

		$settings = array(
			array(
				'key'		=> 'karma_icon',
				'label'		=> __( 'Select icon for Social Counter', 'pagelines' ),
				'default'	=> 'sun',
				'title'		=> 'Social Counter',
				'type'		=> 'select_icon',
				'help'		=> '<a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Click here</a> for a complete list of Font Awesome Icons'
			),
			array(
				'key'		=> 'twittername',
				'type' 		=> 'text',
				'label' 	=> __( 'Your Twitter Username', 'pagelines' ),
				'title' 	=> __( 'Twitter Integration', 'pagelines' ),
				'help' 		=> __( 'This places your Twitter feed on the site. Leave blank if you want to hide or not use.', 'pagelines' )
			),
			array(
				'key'		=> 'fb_multi',
				'type'		=> 'multi', 
				'col'		=> 2,
				'title'		=> 'Facebook',
				'opts'		=> array(
					array(
						'key'		=> 'facebook_name',
						
						'type' 		=> 'text',
						'label' 	=> __( 'Your Facebook Page Name', 'pagelines' ),
						'title' 	=> __( 'Facebook Page', 'pagelines' ),
						'help' 		=> __( 'Enter the name component of your Facebook page URL. (For example, what comes after the facebook url: www.facebook.com/[name])', 'pagelines' )
					),
					array(
						'key'		=> 'facebook_app_id',
						'type' 		=> 'text',
						'label' 	=> __( 'Your Facebook App ID', 'pagelines' ),
						'title' 	=> __( 'Facebook App ID', 'pagelines' ),
						'help' 		=> __( 'Add your Facebook Application ID here.', 'pagelines' )
					),
				)
			),
			
			array(
				'key'		=> 'site-hashtag',
				
				'type' 		=> 'text',
				'label' 	=> __( 'Your Website Hashtag', 'pagelines' ),
				'title' 	=> __( 'Website Hashtag', 'pagelines' ),
				'help'	 	=> __( 'This hashtag will be used in social media (e.g. Twitter) and elsewhere to create feeds.', 'pagelines' )
			),
		
		);
		return $settings;
	}

	function advanced(){

		$settings = array(
			array(
				'key'	=> 'region_control',
				'type'	=> 'multi',
				'title'	=> __( 'Region Controls', 'pagelines' ),
				'help'	=> __( 'If you are not using these global regions of your site, you can deactivate them to clean up your UI.', 'pagelines' ),
				'opts'	=> array(
					array(
							'key'		=> 'region_disable_fixed',
							'type'		=> 'check',
							'label'		=> __( 'Disable Fixed Region?', 'pagelines' ),							  
					),
					array(
							'key'		=> 'region_disable_header',
							'type'		=> 'check',
							'label'		=> __( 'Disable Header Region?', 'pagelines' ),							  
					),
					array(
							'key'		=> 'region_disable_footer',
							'type'		=> 'check',
							'label'		=> __( 'Disable Footer Region?', 'pagelines' ),								  
					),
				)
			),
			array(
				'key'	=> 'debug_settings',
				'type'	=> 'multi',
				'col'	=> 2,
				'title'	=> __( 'Debug Options', 'pagelines' ),
				'opts'	=> array(
					array(
							'key'		=> 'enable_debug',
							'type'		=> 'check',
							'label'		=> __( 'Enable debug?', 'pagelines' ),
							'title'		=> __( 'PageLines debug', 'pagelines' ),
							'help'		=> sprintf( __( 'This information can be useful in the forums if you have a problem. %s', 'pagelines' ),
										   sprintf( '%s', ( pl_setting( 'enable_debug' ) ) ?
										   sprintf( '<br /><a href="%s" target="_blank">Click here</a> for your debug info.', site_url( '?pldebug=1' ) ) : '' ) )								  
					),
					array(
							'key'		=> 'disable_less_errors',
							'default'	=> false,
							'type'		=> 'check',
							'label'		=> __( 'Disable Error Notices?', 'pagelines' ),
							'title'		=> __( 'Less Notices', 'pagelines' ),
							'help'		=> __( 'Disable any error notices sent to wp-admin by the less system', 'pagelines' ),								  
					)
				)
			),		
			array(
				'key'	=> 'misc_advanced_settings',
				'type'	=> 'multi',
				'col'	=> 3,
				'title'	=> __( 'Miscellaneous Config', 'pagelines' ),
				'opts'	=> array(
					array(
							'key'		=> 'load_prettify_libs',
							'type'		=> 'check',
							'label'		=> __( 'Enable Code Prettify?', 'pagelines' ),
							'title'		=> __( 'Google Prettify Code', 'pagelines' ),
							'help'		=> __( "Add a class of 'prettyprint' to code or pre tags, or optionally use the [pl_codebox] shortcode. Wrap the codebox shortcode using [pl_raw] if Wordpress inserts line breaks.", 'pagelines' )
					),
					array(
							'col'		=> 2,
							'key'		=> 'partner_link',
							'type'		=> 'text',
							'label'		=> __( 'Enter Partner Link', 'pagelines' ),
							'title'		=> __( 'PageLines Affiliate/Partner Link', 'pagelines' ),
							'help'		=> __( "If you are a <a target='_blank' href='http://www.pagelines.com/partners/'>PageLines Partner</a> enter your link here and the footer link will become a partner or affiliate link.", 'pagelines' )
					),
					array(
							'col'		=> 2,
							'key'		=> 'special_body_class',
							'type'		=> 'text',
							'label'		=> __( 'Install Class', 'pagelines' ),
							'title'		=> __( 'Current Install Class', 'pagelines' ),
							'help'		=> __( "Use this option to add a class to the &gt;body&lt; element of the website. This can be useful when using the same child theme on several installations or sub domains and can be used to control CSS customizations.", 'pagelines' )
					),

					array(
						'key'		=> 'alternative_css',
						'default'	=> false,
						'type'		=> 'check',
						'col'		=> 1,
						'label'		=> __( 'Enable Alternative CSS URLS', 'pagelines' ),
						'help'		=> __( 'Some hosts with aggressive caches have issues with the CSS files, this is a possible workaround.', 'pagelines' )				
					)
				)
			),
			
			
			
		);
		return $settings;
	}

	function resets(){

		$settings = array(
			array(
					'key'		=> 'reset_global',
					'type'		=> 'action_button',
					'classes'	=> 'btn-important',
					'label'		=> __( '<i class="icon icon-undo"></i> Reset Global Settings', 'pagelines' ),
					'title'		=> __( 'Reset Global Site Settings', 'pagelines' ),
					'help'		=> __( "Use this button to reset all global settings to their default state. <br/><strong>Note:</strong> Once you've completed this action, you may want to publish these changes to your live site.", 'pagelines' )
			),
			array(
					'key'		=> 'reset_local',
					'type'		=> 'action_button',
					'classes'	=> 'btn-important',
					'label'		=> __( '<i class="icon icon-undo"></i> Reset Current Page Settings', 'pagelines' ),
					'title'		=> __( 'Reset Current Page Settings', 'pagelines' ),
					'help'		=> __( "Use this button to reset all settings on the current page back to their default state. <br/><strong>Note:</strong> Once you've completed this action, you may want to publish these changes to your live site.", 'pagelines' )
			),
			array(
					'key'		=> 'reset_type',
					'type'		=> 'action_button',
					'classes'	=> 'btn-important',
					'label'		=> __( '<i class="icon icon-undo"></i> Reset Current Post Type Settings', 'pagelines' ),
					'title'		=> __( 'Reset Current Post Type Settings', 'pagelines' ),
					'help'		=> __( "Use this button to reset all settings on the current post type back to their default state. <br/><strong>Note:</strong> Once you've completed this action, you may want to publish these changes to your live site.", 'pagelines' )
			),
			array(
					'key'		=> 'reset_cache',
					'col'		=> 2,
					'type'		=> 'action_button',
					'classes'	=> 'btn-info',
					'label'		=> __( '<i class="icon icon-trash"></i> Flush Caches', 'pagelines' ),
					'title'		=> __( 'Clear all CSS/LESS cached data.', 'pagelines' ),
					'help'		=> __( "Use this button to purge the stored LESS/CSS data. This will also clear cached pages if wp-super-cache or w3-total-cache are detected.", 'pagelines' )
			),
		);
		
		
		return $settings;
	}
}

function pl_standard_section_options( $section ){
	$options = array();
	global $plpg;

	if( $section->meta['draw'] == 'area' ){
		
		$options['background'] = pl_get_background_options( $section );
	}

	$options['standard'] = array(
	
	
		'key'			=> 'pl_section_styling',
		'type' 			=> 'multi',
		'col'			=> 1,
		'label' 	=> __( 'Standard Options', 'pagelines' ),
		'opts'	=> array(
			array(
				'key'		=> 'pl_standard_title',
				'type' 		=> 'text',
				'label' 	=> __( 'Standard Title', 'pagelines' )
			),
			array(

				'key'		=> 'pl_area_class',
				'type' 		=> 'text',
				'label' 	=> __( 'Custom Styling Classes', 'pagelines' )
			),	
			array(
				'key'		=> 'pl_standard_styles',
				'type' 		=> 'text',
				'label' 	=> __( 'Inline CSS Styling', 'pagelines' ),
				'help'		=> __( 'Use sparingly. Example: "text-transform:uppercase; font-size: 80%;"', 'pagelines' )
			),
			array(
				'key'		=> 'pl_hide_on_page',
				'type' 		=> 'text',
				'label' 	=> __( 'Hide on specific pages or posts? (IDs Comma Separated)', 'pagelines' ),
				'help'		=> sprintf( __( 'Applicable in global regions. Enter Page IDs (comma separated) to hide this section only on those pages.%s' , 'pagelines' ) ,
							( isset( $plpg->id ) ) ? ' The ID for this page/post is: ' . $plpg->id : '' )
			)
		)
	);	
	return apply_filters( 'pl_standard_section_options', $options );
}
