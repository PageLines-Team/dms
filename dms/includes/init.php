<?php
/**
 * This file initializes the PageLines framework
 *
 * @package PageLines DMS
 *
*/



/**
 * Run the starting hook
 */
do_action('pagelines_hook_pre', 'core'); // Hook
define('PL_INCLUDES', pl_get_template_directory() . '/includes');

// Load deprecated functions
require_once( PL_INCLUDES.'/deprecated.php' );

require_once( PL_INCLUDES . '/version.php' );

// Setup Globals
require_once( PL_INCLUDES . '/init.globals.php');

// cache functions.
require_once( PL_INCLUDES . '/lib.cache.php' );

// Run version checks and setup
require_once( PL_INCLUDES . '/run.versioning.php');

// LOCALIZATION - Needs to come after config_theme and before localized config files
require_once( PL_INCLUDES . '/run.I18n.php');

// Installation Routine
require_once( PL_INCLUDES . '/init.install.php' );

if( file_exists( PL_THEME_DIR . '/config.php' ) )
	require_once( PL_THEME_DIR . '/config.php' );

// Utility functions and hooks/filters
require_once( PL_INCLUDES . '/lib.settings.php' );

// Utility functions and hooks/filters
require_once( PL_INCLUDES . '/lib.utils.php' );

// Applied on load
require_once( PL_INCLUDES . '/lib.load.php' );

// Various elements and WP utilities
require_once( PL_INCLUDES . '/lib.elements.php' );

// Applied in head
require_once( PL_INCLUDES . '/lib.head.php' );

// Applied in body
require_once( PL_INCLUDES . '/lib.body.php' );

// Utility Functions -- Theming
require_once( PL_INCLUDES . '/utils.karma.php' );
require_once( PL_INCLUDES . '/lib.theming.php' );

// Post Media Functions
require_once( PL_INCLUDES . '/lib.posts.php' );


// Shortcodes
require_once( PL_INCLUDES . '/class.shortcodes.php');

// Removed in Free/WPORG Version
if ( is_file( PL_INCLUDES . '/library.pagelines.php' ) )
	require_once( PL_INCLUDES . '/library.pagelines.php');
else
	define( 'IS_WPORG', true );

// Start the editor
require_once( PL_INCLUDES . '/init.editor.php' );

// V3 Editor functions --- > always load
require_once( PL_INCLUDES . '/lib.editor.php' );

// Commerce Stuff
require_once( PL_INCLUDES . '/lib.commerce.php' );

// LESS Functions
require_once( PL_INCLUDES . '/less.functions.php' );

// LESS Handling -> Legacy Approach
require_once( PL_INCLUDES . '/less.legacy.php' );

// LESS Handling -> DMS Approach
require_once( PL_INCLUDES . '/less.engine.php' );



// Base Section Class
require_once( PL_INCLUDES . '/class.sections.php' );

// Typography Foundry / Fonts
require_once( PL_INCLUDES . '/class.foundry.php' );



// Run the pagelines_init Hook
pagelines_register_hook('pagelines_hook_init'); // Hook

function pl_get_template_directory() {
	if( defined( 'DMS_CORE' ) ) {
		$folder = 'dms';
		if( defined( 'DMS_CORE_DIR' ) )
			$folder = DMS_CORE_DIR;
		return trailingslashit( get_template_directory() ) . $folder;
	} else {
		return get_template_directory();
	}
}

function pl_get_template_directory_uri(){
	if( defined( 'DMS_CORE' ) ) {
		$folder = 'dms';
		if( defined( 'DMS_CORE_DIR' ) )
			$folder = DMS_CORE_DIR;
		return trailingslashit( get_template_directory_uri() ) . $folder;
	} else {
		return get_template_directory_uri();
	}
}

