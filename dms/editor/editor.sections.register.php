<?php

/**
 * PageLines Sections Registration Class
 *
 * An object that is used to scan and load sections, then add them to the factory object.
 *
 * @author 		PageLines
 * @category 	Core
 * @package 	DMS
 * @version     1.2
 */

class PLSectionsRegister {
	
	function __construct() {
		if( defined( 'DMS_CORE' ) )
			add_filter( 'pl_section_filters', array( $this, 'add_theme_section_filter' ) );
	}

	function add_theme_section_filter( $items ) {


		$items['theme'] = array(
			'name'	=> sprintf( __( 'Theme: %s', 'pagelines' ), PL_NICETHEMENAME ),
			'href'	=> '#add_section',
			'filter'=> '.theme',
			'icon'	=> 'icon-laptop',
			'pos'	=> 30
		);
	
		return $items;
	}
	
	// start the shitstorm
	function generate_data() {
		
		$sections = array();
		$section_dirs = $this->get_section_dirs();
	
		// get the raw data populated.
		foreach ( $section_dirs as $type => $dir )
			$sections[ $type ] = $this->get_sections_data( $dir, $type );

		$sections['editor'] = $this->get_all_plugins();

		return $sections;
	}

	function reset_sections() {
		set_theme_mod( 'editor-sections-data', array() );
	}

	// save to our option
	function save_sections( $data ) {
		set_theme_mod( 'editor-sections-data', $data );
	}

	// return all sections as array. (optional $type)
	// also runs on 'after_setup_theme' to make sure all sections are loaded and ready to go.
	function get_sections( $type = false ) {
		
		global $editor_sections_data;
		
		// check the global 1st, we dont want to use an option if we cant help it.
		if( is_array( $editor_sections_data ) && ! empty( $editor_sections_data ) ) {
			if( $type && isset( $editor_sections_data[$type]) )
				return $editor_sections_data[$type];
			else
				return $editor_sections_data;
		}
		
		$data = get_theme_mod( 'editor-sections-data' );
		
		// if no data is stored, it was either reset or never created
		if( ! is_array( $data ) || empty( $data ) ) {
			$data = $this->generate_data();
			$this->save_sections( $data );
		}		

		$editor_sections_data = $data;
		// by now we are sure to have an array even if its empty
		if( $type && isset( $data[$type]) )
			return $data[$type];
		else
			return $data;
	}

	// return an array of valid folders to check.
	function get_section_dirs() {

		$section_dirs = array();
		$theme_sections_dir = PL_CHILD_DIR . '/sections';

		// if we are a child theme
		if ( is_child_theme() && is_dir( $theme_sections_dir ) )
			$section_dirs['custom'] = $theme_sections_dir;

		// if we are a nested theme, register the theme sections folder
		if ( defined( 'DMS_CORE' ) && is_dir( PL_THEME_DIR . '/sections' ) )
			$section_dirs['theme'] = PL_THEME_DIR . '/sections';

		// load sections that might be in plugins.
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		foreach ( get_plugins() as $plugin => $data ) {
			$slug = dirname( $plugin );
			$path = path_join( WP_PLUGIN_DIR, "$slug/sections" );
			if ( is_dir( $path ) && is_plugin_active( $plugin ) )
				$section_dirs[ $slug ] = $path;
		}
		// include the core sections
		$section_dirs['parent'] = PL_SECTIONS;
		
		// deprecated, this was the sections plugin
		//$section_dirs['child'] = PL_EXTEND_DIR;

		return apply_filters( 'pagelines_sections_dirs', $section_dirs );
	}

	// register with the foundry.
	function register_sections() {
		
		global $pl_section_factory;

		$disabled = apply_filters( 'pagelines_section_disabled', array() );

		// load the main array
		$sections = $this->get_sections();

		// filter main array containing child and parent and any custom sections
		$sections = apply_filters( 'pagelines_section_admin', $sections );
		
		// right here we go...
		foreach ( $sections as $type ) {
		
			// if the type is empty move along
			if ( !is_array( $type ) || empty( $type ) )
				continue;
			
			// now register each section in the type
			foreach ( $type as $section ) {
				
				// deprecated, loads the less?
				$section['loadme'] = true;
				
				/**
				* Checks to see if we are a child section, if so disable the parent
				* Also if a parent section and disabled, skip.
				* AND if we are a nested theme disable the parent.
				*/
				if ( 'parent' != $section['type'] && isset( $sections['parent'][ $section['class'] ] ) ) {
					$disabled['parent'][ $section['class'] ] = true;
				}


				// TODO add inception here if we are nested theme and user adds a child theme, double overide??	
				
				// check if section is already disabled.
				if ( isset( $disabled[ $section['type'] ][ $section['class'] ] ) && ! $section['persistant'] )
					continue;
				
				$section_data = array(
					'base_dir'  => $section['base_dir'],
					'base_url'  => $section['base_url'],
					'base_file' => $section['base_file'],
					'name'		=> $section['name']
				);
				
				if( isset( $disabled[$section['type']][ $section['class'] ] ) && true == $disabled[$section['type']][ $section['class'] ] )
					continue;
				
				if ( ! class_exists( $section['class'] ) && is_file( $section['base_file'] ) ) {
					include( $section['base_file'] );
					$pl_section_factory->register( $section['class'], $section_data );
				}
			} // /type			
		} // /register loop
	}

	// list plugins filter out non PageLines
	function get_all_plugins() {

		$default_headers = array(
			'External'		=> 'External',
			'Demo'			=> 'Demo',
			'tags'			=> 'Tags',
			'version'		=> 'Version',
			'author'		=> 'Author',
			'authoruri'		=> 'Author URI',
			'description'	=> 'Description',
			'classname'		=> 'Class Name',
			'depends'		=> 'Depends',
			'workswith'		=> 'workswith',
			'isolate'		=> 'isolate',
			'edition'		=> 'edition',
			'cloning'		=> 'cloning',
			'failswith'		=> 'failswith',
			'tax'			=> 'tax',
			'persistant'	=> 'Persistant',
			'format'		=> 'Format',
			'classes'		=> 'Classes',
			'filter'		=> 'Filter',
			'loading'		=> 'Loading',
			'PageLines'		=> 'PageLines',
			'Section'		=> 'Section',
			'Plugin Name'	=> 'Plugin Name',
			'Docs'	=> 'Docs',
		);

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		$installed_plugins = get_plugins();
		
		$pl_plugins = array();
		foreach( $installed_plugins as $path => $plugin ) {
					
			if ( ! is_plugin_active( $path ) )
				continue;

			$fullpath = sprintf( '%s%s', trailingslashit( WP_PLUGIN_DIR ), $path );
			
			$data = get_file_data( $fullpath, $default_headers );
			
			if( ! $data['PageLines'] || ! $data['Section'] )
				unset( $installed_plugins[$path] );
			else {
				
				$base_dir = dirname( $fullpath );
				$base_url = untrailingslashit( plugins_url( '', $path ) );
				
				$section_paths = array(
						'class'			=> $data['classname'],
						'type'			=> 'editor',
						'tags'			=> $data['tags'],
						'author'		=> $data['author'],
						'version'		=> $data['version'],
						'authoruri'		=> ( isset( $data['authoruri'] ) ) ? $data['authoruri'] : '',
						'docs'			=> ( isset( $data['docs'] ) ) ? $data['docs'] : '',
						'description'	=> $data['description'],
						'name'			=> $data['Plugin Name'],
						'base_url'		=> $base_url,
						'base_dir'		=> $base_dir,
						'base_file'		=> $fullpath,
						'workswith'		=> ( $data['workswith'] ) ? array_map( 'trim', explode( ',', $data['workswith'] ) ) : '',
						'isolate'		=> ( $data['isolate'] ) ? array_map( 'trim', explode( ',', $data['isolate'] ) ) : '',
						'edition'		=> $data['edition'],
						'cloning'		=> ( 'true' === $data['cloning'] ) ? true : '',
						'failswith'		=> ( $data['failswith'] ) ? array_map( 'trim', explode( ',', $data['failswith'] ) ) : '',
						'tax'			=> $data['tax'],
						'demo'			=> $data['Demo'],
						'external'		=> $data['External'],
						'persistant'	=> $data['persistant'],
						'format'		=> $data['format'],
						'classes'		=> $data['classes'],
						'screenshot'	=> ( is_file( $base_dir . '/thumb.png' ) ) ? $base_url . '/thumb.png' : '',
						'splash'		=> ( is_file( $base_dir . '/splash.png' ) ) ? $base_url . '/splash.png' : '',
						'less'			=> ( is_file( $base_dir . '/style.less' ) ) ? true : false,
						'loadme'		=> true,
						'price'			=> '',
						'purchased'		=> true,
						'uid'			=> '',
						'filter'		=> $data['filter'],
						'loading'		=> $data['loading']
				);
							
				$data = wp_parse_args( $section_paths, $data );
				$pl_plugins[$data['classname']] = $data;		
			}				
		}
		return $pl_plugins;
	}
	
	/**
	 *
	 * Helper function
	 * Returns array of section files.
	 * @return array of php files
	 * @author Simon Prosser
	 **/
	function get_sections_data( $dir, $type ) {

		if ( 'parent' != $type && ! is_dir( $dir ) )
			return;

		// if ( is_multisite() )
		// 	$store_sections = $this->get_latest_cached( 'sections' );

		$default_headers = array(
			'External'		=> 'External',
			'Demo'			=> 'Demo',
			'tags'			=> 'Tags',
			'version'		=> 'Version',
			'author'		=> 'Author',
			'authoruri'		=> 'Author URI',
			'section'		=> 'Section',
			'description'	=> 'Description',
			'classname'		=> 'Class Name',
			'depends'		=> 'Depends',
			'workswith'		=> 'workswith',
			'isolate'		=> 'isolate',
			'edition'		=> 'edition',
			'cloning'		=> 'cloning',
			'failswith'		=> 'failswith',
			'tax'			=> 'tax',
			'persistant'	=> 'Persistant',
			'format'		=> 'Format',
			'classes'		=> 'Classes',
			'filter'		=> 'Filter',
			'loading'		=> 'Loading',
			'docs'			=> 'Docs',
		);

		$sections = array();

		// setup out directory iterator.
		// symlinks were only supported after 5.3.1
		// so we need to check first ;)
		$it = ( strnatcmp( phpversion(), '5.3.1' ) >= 0 )
			? new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::FOLLOW_SYMLINKS		) , RecursiveIteratorIterator::SELF_FIRST )
			: new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST	)
		);

		foreach ( $it as $fullFileName => $fileSPLObject ) {

			if ( basename( $fullFileName ) == PL_EXTEND_SECTIONS_PLUGIN )
				continue;

			if ( 'php' != pathinfo( $fileSPLObject->getFilename(), PATHINFO_EXTENSION ) )
				continue;

			$base_url = null;
			$base_dir = null;
			$load     = true;
			$price    = '';
			$uid      = '';
			$headers  = get_file_data( $fullFileName, $default_headers );
			$filters = array();

			// If no pagelines class headers ignore this file.
			// beyond this point $fullFileName should refer to a section.php
			if ( !$headers['classname'] )
				continue;
			
			preg_match( '#[\/|\-]sections[\/|\\\]([^\/|\\\]+)#', $fullFileName, $out );
			$folder = sprintf( '/%s', $out[1] );
			
			// base values
			$version  = $headers['version'] ? $headers['version'] : PL_CORE_VERSION;
			$base_dir = PL_SECTIONS . $folder;
			$base_url = PL_SECTION_ROOT . $folder;

			if ( 'child' == $type ) {
				$base_url =  PL_EXTEND_URL . $folder;
				$base_dir =  PL_EXTEND_DIR . $folder;
			}
			if ( 'custom' == $type ) {
				$base_url =  get_stylesheet_directory_uri()  . '/sections' . $folder;
				$base_dir =  get_stylesheet_directory()  . '/sections' . $folder;
			}
			if ( 'theme' == $type ) {
				$base_url =  get_template_directory_uri()  . '/sections' . $folder;
				$base_dir =  get_template_directory()  . '/sections' . $folder;
			}
			/*
			* Look for custom dirs.
			*/
			if ( !in_array( $type, array( 'custom', 'child', 'parent', 'editor', 'theme' ) ) ) {

				// Ok so we're a plugin then.. if not active then bypass.
				$plugin_slug = $type;

				// base plugin path
				$plugin = sprintf( '%s/%s.php', $plugin_slug, $plugin_slug );

				$check = str_replace('\\', '/', $fullFileName); // must convert backslashes before preg_match
				preg_match( '#\/sections\/([^\/]+)#', $check, $out );

				// check for active container plugin and existing individual section directory
				if ( ! is_plugin_active( $plugin ) || ! isset( $out[1] ) )
					continue;

				$section_slug = $out[1];

				$base_url = sprintf( '%s/sections/%s',
					untrailingslashit( plugins_url( $plugin_slug ) ),
					$section_slug
				);
				$base_dir = dirname( $fullFileName );
			}


			// do we need to load this section?
			// if ( 'child' == $type && is_multisite() ) {
			// 	$load      = false;
			// 	$slug      = basename( $folder );
			// 	$purchased = ( isset( $store_sections->$slug->purchased ) ) ? $store_sections->$slug->purchased : '';
			// 	$plus      = ( isset( $store_sections->$slug->plus_product ) ) ? $store_sections->$slug->plus_product : '';
			// 	$price     = ( isset( $store_sections->$slug->price ) ) ? $store_sections->$slug->price : '';
			// 	$uid       = ( isset( $store_sections->$slug->uid ) ) ? $store_sections->$slug->uid : '';
			// 
			// 	if ( 'purchased' === $purchased )
			// 		$load = true;
			// 	elseif ( $plus && pagelines_check_credentials( 'plus' ) )
			// 		$load = true;
			// 	else {
			// 		$disabled = pl_get_disabled_sections();
			// 
			// 		if ( ! isset( $disabled['child'][ $headers['classname'] ] ) )
			// 			$load = true;
			// 	}
			// }

			if ( $load )
				$purchased = 'purchased';

			$filters = explode( ',', $headers['filter'] );

			if( 'theme' == $type ) {
				$filters[] = 'theme';
			}
			
			$filters = implode( $filters, ',' );

			$sections[ $headers['classname'] ] = array(
				'class'			=> $headers['classname'],
				'depends'		=> $headers['depends'],
				'type'			=> $type,
				'tags'			=> $headers['tags'],
				'author'		=> $headers['author'],
				'version'		=> $version,
				'authoruri'		=> ( isset( $headers['authoruri'] ) ) ? $headers['authoruri'] : '',
				'description'	=> $headers['description'],
				'name'			=> $headers['section'],
				'base_url'		=> $base_url,
				'base_dir'		=> $base_dir,
				'base_file'		=> $fullFileName,
				'workswith'		=> ( $headers['workswith'] ) ? array_map( 'trim', explode( ',', $headers['workswith'] ) ) : '',
				'isolate'		=> ( $headers['isolate'] ) ? array_map( 'trim', explode( ',', $headers['isolate'] ) ) : '',
				'edition'		=> $headers['edition'],
				'cloning'		=> ( 'true' === $headers['cloning'] ) ? true : '',
				'failswith'		=> ( $headers['failswith'] ) ? array_map( 'trim', explode( ',', $headers['failswith'] ) ) : '',
				'tax'			=> $headers['tax'],
				'demo'			=> $headers['Demo'],
				'external'		=> $headers['External'],
				'persistant'	=> $headers['persistant'],
				'format'		=> $headers['format'],
				'classes'		=> $headers['classes'],
				'screenshot'	=> ( is_file( $base_dir . '/thumb.png' ) ) ? $base_url . '/thumb.png' : '',
				'less'			=> ( is_file( $base_dir . '/color.less' ) || is_file( $base_dir . '/style.less' ) ) ? true : false,
				'loadme'		=> $load,
				'price'			=> $price,
				'purchased'		=> $purchased,
				'uid'			=> $uid,
				'filter'		=> $filters,
				'loading'		=> $headers['loading'],
				'docs'			=> ( isset( $headers['docs'] ) ) ? $headers['docs'] : false,
			);
		}

		return $sections;
	}
	// backwards compatability 
	function pagelines_register_sections( $a = false, $b = false ) {
		_pl_deprecated_function( __FUNCTION__, '2.0', '$editorsections->get_sections()' );
		return $this->get_sections();
	}
}

global $editorsections, $load_sections;
$load_sections = $editorsections = new PLSectionsRegister;
