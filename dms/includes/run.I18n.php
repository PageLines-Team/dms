<?php
/*
 *	Setup DMS Localization
 */

add_action( 'after_setup_theme', 'dms_run_i18n' );

function dms_run_i18n() {
	load_theme_textdomain( 'pagelines', PAGELINES_CORE_LANG_DIR );

	if( defined( 'PAGELINES_THEME_LANG_DIR' ) ) {
		load_theme_textdomain( 'pagelines', PAGELINES_THEME_LANG_DIR );
	}
}
