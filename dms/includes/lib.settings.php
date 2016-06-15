<?php 

define( 'PL_SETTINGS', pl_base_options_slug() );

//The default state of the settings, if empty
function pl_settings_default(){
	return array( 'draft' => array(), 'live' => array() );
}

function pl_base_options_slug(){
	return 'pl-settings';
}

function pl_theme_options_slug(){
	
	if( pl_theme_info('template') != 'dms' )
		return pl_base_options_slug() . '-' . pl_theme_info('template');
	else {
		return pl_base_options_slug();
	}
	
}



function pl_global_setting_update( $key, $value ){
	
	$settings = pl_get_global_settings();
	
	$settings[ pl_get_mode() ]['settings'][$key] = $value; 
	
	pl_update_global_settings( $settings );
	
}

function pl_global( $key ){
	
	$settings = pl_get_global_settings();
	
 	return (isset($settings[pl_get_mode()][$key])) ? $settings[pl_get_mode()][$key] : false;
	
}

function pl_global_update( $key, $value ){
	
	$settings = pl_get_global_settings();
	
	$settings[ pl_get_mode() ][$key] = $value; 
	
	pl_update_global_settings( $settings );
	
}

function pl_get_global_settings( $mode = false ){
	
	$all_settings = get_option( pl_theme_options_slug() );
	
	if( ! $all_settings )
		$all_settings = pl_settings_default();
	
	if( $mode ){
		
		if (isset( $all_settings[$mode] ) )
		 	$settings = $all_settings[$mode];
		else
			$settings = array();
		
	} else 
		$settings = $all_settings;
	
	return $settings;
}

function pl_update_global_settings( $settings ){
	
	update_option( pl_theme_options_slug(), $settings);
	
}

/*
 * META SETTINGS FUNCTIONS
 */ 
function pl_reset_meta_settings( $metaID ){

	$set = pl_get_meta_settings( $metaID );

	$set['draft'] = array();

	pl_update_meta_settings( $metaID, $set );

}


function pl_get_meta_settings( $metaID ){
	
	$settings = get_post_meta( $metaID, pl_base_options_slug(), true );
	
	if( ! $settings )
		$settings = pl_settings_default();
		
	return $settings;
	
}

function pl_update_meta_settings( $metaID, $settings ){
	
	update_post_meta( $metaID, pl_base_options_slug(), $settings );
	
}

function pl_update_single_meta_setting( $metaID, $key, $value, $mode = 'both' ){
	$settings = pl_get_meta_settings( $metaID );
	
	if( $mode == 'both'){
		$settings[ 'draft' ][$key] = $value; 
		$settings[ 'live' ][$key] = $value; 
	} else 
		$settings[ $mode ][$key] = $value; 
	
	pl_update_meta_settings($metaID, $settings);
	
}

/*
 *  Resets global options to an empty set
 */
function reset_global_settings(){

	$settings = pl_get_global_settings();

	$set['draft'] = array();
	
	pl_update_global_settings( $set );
	
	set_default_settings();

	global $dms_cache;
	$dms_cache->purge('draft');
	
	return $set;
}


function set_default_settings(){

	$settings = pl_get_global_settings();

	
	if( ! $settings ){
		$settings = pl_settings_default();
	}
		
	
	$settings_defaults = apply_filters( 'pl_theme_default_settings', get_default_settings() );
	
	$region_defaults = apply_filters( 'pl_theme_default_regions', array() );

	if( empty( $settings['draft'] ) ){
		$settings['draft'] = array(
			'settings'	=>	$settings_defaults,
			'regions'	=>	$region_defaults
		);
	}
	
	if( empty( $settings['live'] ) ){
		$settings['live'] = array(
			'settings'	=>	$settings_defaults,
			'regions'	=>	$region_defaults
		);
	}
		
	pl_update_global_settings( $settings );
	
	return $settings;

}

function get_default_settings(){
	$settings_object = new EditorSettings;

	$settings = $settings_object->get_set();


	$defaults = array();
	foreach($settings as $tab => $tab_settings){
		foreach($tab_settings['opts'] as $index => $opt){
			if($opt['type'] == 'multi'){
				foreach($opt['opts'] as $subi => $sub_opt){
					if(isset($sub_opt['default'])){
						$defaults[ $sub_opt['key'] ] = $sub_opt['default'];
					}
				}
			}
			if(isset($opt['default'])){
				$defaults[ $opt['key'] ] = $opt['default'];
			}
		}
	}

	return $defaults;
}