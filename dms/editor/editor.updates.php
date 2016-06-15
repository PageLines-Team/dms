<?php
/**
 *  DMS 3rd party plugin/theme updating class.
 *
 *  @package DMS
 *  @since 1.0
 *
 *
 */
class PageLinesEditorUpdates {
	

	function __construct() {
		$this->register_core();
		add_action( 'init', array( $this, 'check_updater_status' ) );
		add_filter( 'http_request_args', array( $this, 'pagelines_plugins_remove' ), 10, 2 );	
	}

	function pagelines_plugins_remove( $r, $url ) {

		if ( 0 === strpos( $url, 'https://api.wordpress.org/plugins/update-check/' ) ) {
			$installed = get_plugins();
			$plugins = json_decode( $r['body']['plugins'] );
			foreach ( $installed as $path => $plugin ) {
			//	var_dump( $plugin );
				$data = get_file_data( sprintf( '%s/%s', WP_PLUGIN_DIR, $path ), $default_headers = array( 'pagelines' => 'PageLines' ) );
				if ( !empty( $data['pagelines'] ) ) {
					unset( $plugins->plugins->$path );
				}
			}
			$r['body']['plugins'] = json_encode( $plugins );
		}
	return $r;
	}

	function register_core() {
		
		// if we are parent theme OR a child theme, register as DMS.
		// if we are a standalone, register as the standalone.		
		$themeslug = $this->get_themeslug();
		
		global $registered_pagelines_updates;
		if( ! is_array( $registered_pagelines_updates ) )
			$registered_pagelines_updates = array();
		$registered_pagelines_updates['dmspro'] = array(
			'type' => 'theme',
			'product_file_path'	=> 'dmspro',
			'product_name'	=> __( 'PageLines Club Membership', 'pagelines' ),
			'product_version'	=> '',
			'product_desc'		=> __( 'Activating this will automatically give you updates to any PageLines Product detected.', 'pagelines' )
			);
						
		$registered_pagelines_updates[$themeslug] = array(
			'type' => 'theme',
			'product_file_path'	=> $themeslug,
			'product_name'	=> pl_get_theme_data( get_template_directory(), 'Name'),
			'product_version'	=> pl_get_theme_data( get_template_directory(), 'Version'),
			'product_desc'		=> pl_get_theme_data( get_template_directory(), 'Description')
			);		
	}

	function get_themeslug() {
		if( defined( 'DMS_CORE' ) )
			return basename( get_stylesheet_directory() );
		else
			return 'dms';
	}

	function check_updater_status() {
		
		// if not installed, show link to install.
		if( ! pl_check_updater_exists() ) {
			add_filter( 'plugins_api', array( $this, 'pagelines_updater_install' ), 10, 3 );			
		}
		// if installed but not activated, lets activate it.
		add_action( 'admin_notices', array( $this, 'updater_install' ), 9);
	}


	function pagelines_updater_install( $api, $action, $args ) {
		$cache = rand();
		$download_url = 'http://www.pagelines.com/api/store/plugin-pagelines-updater.zip?c=' . $cache;

		if ( 'plugin_information' != $action ||
			false !== $api ||
			! isset( $args->slug ) ||
			'pagelines-updater' != $args->slug
		) return $api;

		$api = new stdClass();
		$api->name = 'PageLines Updater';
		$api->version = '1.0.0';
		$api->download_link = esc_url( $download_url );
		return $api;
	}

	function updater_install() {
		
		$screen = get_current_screen();
			
		if( pl_is_wporg() && 'appearance_page_PageLines-Admin' != $screen->id )
			return false;
		
		$message = pl_updater_txt();
		if( $message )
			echo '<div class="updated fade"><p>' . $message . '</p></div>' . "\n";
	}
}

new PageLinesEditorUpdates;