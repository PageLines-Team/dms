<?php 


/**
 * Check if version has changed.
 */

$installed = get_theme_mod( 'pagelines_version' );
$actual = PL_CORE_VERSION;

// if new version do some housekeeping.
if ( version_compare( $actual, $installed ) > 0 ) {
	$dms_cache = new DMS_Cache;

	// we want to purge the cache during a theme update.
	$dms_cache->purge_all();
	
	// special function that runs once during upgrade from 1.x to 2.x series.
	if( version_compare( $installed, '1.5.0', '<' ) ) {
		add_action( 'after_setup_theme', 'dms_update_two_point_ohh' );
	}
}

function dms_update_two_point_ohh() {

	if( ! is_user_logged_in() )
		return false;
		
	if( pl_is_wporg() )
		return false;

	if( get_theme_mod( 'pl_installed' ) )
		return false;

	// run the 2.0 install routine.
	$install = new PageLinesInstall;
	$url = $install->run_installation_routine();
	wp_safe_redirect( $url ); 
	exit;
}
set_theme_mod( 'pagelines_version', $actual );
set_theme_mod( 'pagelines_child_version', pl_get_theme_data( get_stylesheet_directory(), 'Version' ) );
