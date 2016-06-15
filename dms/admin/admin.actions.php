<?php

// Load account functions
$account_handler = new PLAccountAdmin;

// ====================================
// = Build PageLines Option Interface =
// ====================================

// Add our menus where they belong.
add_action( 'admin_menu', 'pagelines_add_admin_menu' );

add_action('admin_menu', 'pagelines_add_admin_menus');

if( ! function_exists( 'pagelines_add_admin_menu' ) ) {

	function pagelines_add_admin_menus() {}

	function pagelines_add_admin_menu() {
		global $_pagelines_account_hook;
		$_pagelines_account_hook = add_theme_page( PL_MAIN_DASH, __( 'DMS Tools', 'pagelines' ), 'edit_theme_options', PL_MAIN_DASH, 'pagelines_build_account_interface' );
	}
}

// Build option interface


/**
 * Build Extension Interface
 * Will handle adding additional sections, plugins, child themes
 */
function pagelines_build_account_interface(){

	$dms_tools = new EditorAdmin;

	$args = array(
		'title'			=> __( 'PageLines DMS', 'pagelines' ),
		'callback'		=> array( $dms_tools, 'admin_array' ),
	);
	$optionUI = new DMSOptionsUI( $args );
}

/**
 * This is a necessary go-between to get our scripts and css loaded
 * on the theme settings page only, and not the rest of the admin
 */
add_action( 'admin_menu', 'pagelines_theme_settings_init' );
function pagelines_theme_settings_init() {
	global $_pagelines_account_hook;

	add_action( "admin_print_scripts-{$_pagelines_account_hook}", 'pagelines_theme_settings_scripts' );
}



// JS/CSS
function pagelines_theme_settings_scripts() {


	wp_enqueue_script( 'pl-library', PL_PARENT_URL . '/editor/js/pl.library.js', array( 'jquery' ), pl_get_cache_key() );
	wp_enqueue_script( 'pagelines-admin', PL_JS . '/admin.pagelines.js', array( 'jquery', 'pl-library' ), pl_get_cache_key() );

	pl_enqueue_codemirror();

}

add_action('admin_head', 'add_global_admin_css');
function add_global_admin_css() {
?>
<style type="text/css">
	#toplevel_page_PageLines-Admin .wp-menu-image img{ max-width: 18px; }
	#toplevel_page_PageLines-Admin.current  .wp-menu-image img{ opacity: 1; }
</style>

<?php

}

/**
 * Setup Versions and flush caches.
 *
 * @package PageLines DMS
 * @since   2.2
 */
add_action( 'admin_init', 'pagelines_set_versions' );
function pagelines_set_versions() {

	set_theme_mod( 'pagelines_version', PL_CORE_VERSION );
	set_theme_mod( 'pagelines_child_version', pl_get_theme_data( get_stylesheet_directory(), 'Version' ) );
}

// make sure were running out of 'pagelines' folder.
add_action( 'admin_notices', 'pagelines_check_folders' );
function pagelines_check_folders() {

		if( defined( 'DMS_CORE' ) )
			return;
		$folder = basename( get_template_directory() );

		if( 'dms' == $folder )
			return;

		echo '<div class="updated">';
		printf( "<p><h3>Install Error!</h3><br />PageLines DMS must be installed in a folder called 'dms' to work with child themes and extensions.<br /><br />Current path: %s<br /></p>", get_template_directory() );
		echo '</div>';
}

add_action('admin_enqueue_scripts', 'pagelines_metabox_scripts');
function pagelines_metabox_scripts() {
	wp_enqueue_style( 'pagelines-css', sprintf( '%s/admin.css', PL_ADMIN_URI ), null, pl_get_cache_key() );
	wp_enqueue_script( 'pagelines-admin-meta', PL_ADMIN_URI .'/admin.js', array('jquery'));
}

function dms_suggest_plugin( $name, $slug, $desc = false ) {
	global $dms_suggest_plugins;

	if( ! is_admin() )
		return;

	if( '' == $slug || '' == $name )
		return;

	if( ! is_array( $dms_suggest_plugins ) )
		$dms_suggest_plugins = array();

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugins = get_plugins();
	foreach( $plugins as $s => $data ) {
		if( $name == $data['Name'] ) {
			return;
		}
	}
	$dms_suggest_plugins[$slug] = array(
		'name'		=> $name,
		'desc'		=> $desc
	);
}

add_action( 'admin_notices', 'pagelines_recommended_plugins', 11 );
function pagelines_recommended_plugins() {

	global $dms_suggest_plugins, $pagenow;
	if( isset( $_REQUEST['dms_suggest_plugins'] ) )
		set_theme_mod( 'dms_suggest_plugins', (bool) $_REQUEST['dms_suggest_plugins'] );

	if( 'index.php' != $pagenow || ! is_array( $dms_suggest_plugins ) || empty( $dms_suggest_plugins ) || ! is_super_admin() )
		return false;

	// Already dismissed.
	if( true == get_theme_mod( 'dms_suggest_plugins' ) )
		return false;

	$header = sprintf( '<div id="message" class="updated"><span class="alignright"><a href="%s">[%s]</a></span><p>%s %s %s</p>',
		admin_url( '?dms_suggest_plugins=1' ),
		__( 'dismiss this notice', 'pagelines' ),
		__( 'This theme recommends', 'pagelines' ),
		_n( 'a plugin', 'some plugins', count( $dms_suggest_plugins ), 'pagelines' ),
		__( 'from the WordPress Plugins Repository.', 'pagelines' )
		);

	$footer = '</ul></div>';
	$content = '<ul class="pl-rec-plugins">';
	foreach( $dms_suggest_plugins as $slug => $plugin ) {

		$install_link = wp_nonce_url( network_admin_url( sprintf( 'update.php?action=install-plugin&plugin=%s', $slug ) ), sprintf( 'install-plugin_%s', $slug ) );

		$content .= sprintf( '<li><strong>%s</strong><br /><i>%s</i> <a href="%s"><strong>[Install Now]</strong></a>', $plugin['name'], $plugin['desc'], $install_link );
	}

	echo $header . $content . $footer;
}
add_action('admin_enqueue_scripts', 'pagelines_enqueue_expander');
function pagelines_enqueue_expander() {
	wp_enqueue_script( 'expander', PL_JS .'/utils.expander.min.js', array('jquery'), pl_get_cache_key() );
}

add_action( 'admin_init', 'pl_admin_clear_sections' );
function pl_admin_clear_sections() {
	global $editorsections;
	$editorsections->reset_sections();
}
