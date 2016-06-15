<?php


/*
 * The main settings slug -- used in global and meta options
 */ 
//define('PL_SETTINGS', 'pl-settings');



function pl_setting( $key, $args = array() ){
	global $plopts;

	if(!is_object($plopts)){
		$plpg = new PageLinesPage;
		$pldraft = new EditorDraft;
		$plopts = new PageLinesOpts;
	}

	$setting = $plopts->get_global_setting( $key, $args );

	if( has_filter( "pl_setting-$key" ) )
		return apply_filters( "pl_setting-$key", $setting );

	if( is_array( $setting) )
		return $setting; 
	else 
		return do_shortcode( $setting );

}

function pl_setting_update( $args_or_key, $value = false, $scope = 'global', $mode = 'draft' ){
	$settings_handler = new PageLinesSettings;

	if( is_array($args_or_key) ){
		$args = $args_or_key;
	} else {

		$args = array(
			'key' 	=> $args_or_key,
			'val'	=> $value,
			'mode'	=> $mode,
			'scope'	=> $scope
		);

	}

	$settings_handler->update_setting( $args );

}



function pl_local( $metaID, $key ){
	
	$settings = pl_meta($metaID, PL_SETTINGS, pl_settings_default() );
	
 	return (isset($settings[pl_get_mode()][$key])) ? $settings[pl_get_mode()][$key] : false;
	
}

function pl_local_update( $metaID, $key, $value, $mode = 'draft' ){
	
	$settings = pl_meta($metaID, PL_SETTINGS, pl_settings_default() );
	
	if( $mode == 'both'){
		$settings[ 'draft' ][$key] = $value; 
		$settings[ 'live' ][$key] = $value; 
	} else 
		$settings[ $mode ][$key] = $value; 
	
	pl_meta_update($metaID, PL_SETTINGS, $settings);
		
}

function pl_meta($id, $key, $default = false){

	$data = new PageLinesData;
	return $data->meta($id, $key, $default);

}


function pl_meta_update($id, $key, $value){

	$data = new PageLinesData;
	return $data->meta_update($id, $key, $value);

}

/*
 * This class contains all methods for interacting with WordPress' data system
 * It has no dependancy so it can be used as a substitute for WordPress native functions
 * The options system inherits from it.
 */
class PageLinesData {

	function meta($id, $key, $default = false){

		$val = get_post_meta($id, $key, true);

		if( (!$val || $val == '') && $default ){

			$val = $default;

		} elseif( is_array($val) && is_array($default)) {

			$val = wp_parse_args( $val, $default );

		}

		return $val;

	}

	function meta_update($id, $key, $value){

		update_post_meta($id, $key, $value);

	}


	function opt( $key, $default = false, $parse = false ){

		$val = get_option($key);

		if( !$val ){

			$val = $default;

		} elseif( $parse && is_array($val) && is_array($default)) {

			$val = wp_parse_args( $val, $default );

		}

		return $val;

	}

	function opt_update( $key, $value ){

		update_option($key, $value);

	}

	function user( $user_id, $key, $default = false ){

		$val = get_user_meta($user_id, $key, true);

		if( !$val ){

			$val = $default;

		} elseif( is_array($val) && is_array($default)) {

			$val = wp_parse_args( $val, $default );

		}

		return $val;

	}

	function user_update( $user_id, $key, $value ){
		update_user_meta( $user_id, $key, $value );
	}



}

/*
 *  PageLines Settings Interface
 */
class PageLinesSettings extends PageLinesData {
	

	function global_settings(){

		$set = pl_get_global_settings();

		// Have to move this to an action because ploption calls pl_setting before all settings are loaded
		if( !$set || empty($set['draft']) || empty($set['live']) )
			add_action('pl_after_settings_load', 'set_default_settings');

		return $this->get_by_mode($set);

	}

	/*
	 *  Resets global options using custom child theme config file.
	 */
	function reset_global_child( $opts ){

		$fileOpts = new EditorFileOpts;		
		if( $fileOpts->file_exists() )
			$fileOpts->import( $fileOpts->file_exists() , $opts);
	}

	function import_from_child() {
		$fileOpts = new EditorFileOpts;
		$fileOpts->import( trailingslashit( get_stylesheet_directory() ) . 'pl-config.json', array() );
		
		// only do this once!! The user will still have the option to import again under import/export menus.
		set_theme_mod( 'import_from_child', true );
	}

	function import_from_child_cancelled() {
		set_theme_mod( 'import_from_child', true );
	}

	/*
	 *  Resets all cached data including any detected cache plugins.
	 */
	function reset_caches() {
		global $dms_cache;
		
		// clear draft css
		$dms_cache->purge('draft');
		//clear sections cache
		$dms_cache->purge('sections');
		// clear live css
		$dms_cache->purge('live_css');

		// reset css/js cachekey
		pagelines_reset_pl_cache_key();
	}

	/*
	 *  Resets local options to an empty set based on ID (works for type ID)
	 */
	


	/*
	 *  Update a PageLines setting using arguments
	 */
	function update_setting( $args = array() ){

		$defaults = array(
			'key'	=> '',
			'val'	=> '',
			'mode'	=> 'draft',
			'scope'	=> 'global', 
			'uid'	=> 'settings'
		);

		$a = wp_parse_args( $args, $defaults );

		$scope = $a['scope'];
		$mode = $a['mode'];
		$key = $a['key'];
		$val = $a['val'];
		$uid = $a['uid'];

		$parse_value = array( $key => $val );

		if( $scope == 'global'){

			$settings = pl_get_global_settings();
			
			$old_settings = (isset($settings[ $mode ][ $uid ])) ? $settings[ $mode ][ $uid ] : array();
	
			$settings[ $mode ][ $uid ] = wp_parse_args(  $parse_value, $old_settings);

			pl_update_global_settings( $settings );
			
		} elseif ( $scope == 'local' || $scope == 'type' ){
			global $plpg;
			
			$theID = ($scope == 'local') ? $plpg->id : $plpg->typeid;
			
			$settings = $this->meta( $theID, PL_SETTINGS, pl_settings_default() );
			
			$old_settings = (isset($settings[ $mode ][ $uid ])) ? $settings[ $mode ][ $uid ] : array();
		
			$settings[ $mode ][ $uid ] = wp_parse_args(  $parse_value, $old_settings);

			
			pl_meta_update( $theID, PL_SETTINGS, $settings );
		}
	

	}



	
	/*
	 *  Parse settings taking the top values over the bottom
	 * 	Deep parsing: Parses arguments on nested arrays then deals with overriding
	 *  Checkboxes: Handles checkboxes by using 'flip' value settings to toggle the value
	 */
	function settings_cascade( $top, $bottom ){


		if(!is_array( $bottom ))
			return $top;

		// Parse Args Deep
		foreach($bottom as $id => $settings){

			if( !isset( $top[ $id ]) )
				$top[ $id ] = $settings;

			elseif( is_array($settings) ){
				
				foreach( $settings as $key => $value ){
					
					if( !isset( $top[ $id ][ $key ] ) )
						$top[ $id ][ $key ] = $value;
						
				}
				
			}

		}

		$parsed_args = $top;

		foreach($parsed_args as $id => &$settings){

			if( is_array($settings) ){
				foreach($settings as $key => &$value){

					if(
						( !isset($value) || $value == '' || !$value )
						&& isset( $bottom[ $id ][ $key ] )
					)
						$value = $bottom[ $id ][ $key ];

					$flipkey = $key.'-flip';

					// flipping checkboxes
					if( isset( $parsed_args[$id] ) && isset( $parsed_args[$id][$flipkey] ) && isset( $bottom[$id][$key] ) ){

						$flip_val = $parsed_args[ $id ][ $flipkey ];
						$bottom_val = $bottom[ $id ][ $key ];

						if( $flip_val && $bottom_val ){
							$value = '';
						}


					}



				}
			}

		}
		unset($set);
		unset($value);

		return $parsed_args;
	}

}



/**
 *  PageLines *Page Specific* Settings Interface
 * 	Has a dependancy on the PageLinesPage object and EditorDraft object
 */
class PageLinesOpts extends PageLinesSettings {

	function __construct( $mode = 'detect' ){

		global $plpg; 
		$this->page = (isset($plpg)) ? $plpg : new PageLinesPage;
	
		$this->mode = $mode; 

		$this->local = $this->local_settings();
		$this->type = $this->type_settings();
		$this->global = $this->global_settings();
		$this->regions = (isset($this->global['regions'])) ? $this->global['regions'] : array();
		
		// Get settings from MAP
		
		$this->set = $this->page_settings();
		
		/*-- Going to load this after the map --*/
		$this->page_settings = false;

	}
	
	/*
	 * This must come after map is set up, 
	 * this way we can add/remove/substitute settings based on map config
	 */ 
	function load_page_settings(){
		
		$this->page_settings = apply_filters( 'pl_load_page_settings', $this->page_settings() );
		
	}
	
	/*
	 * Gets settings for a section based on its unique ID
	 * Used heavily in the handler as it assigns each set to meta for use with $this->opt() in sections
	 */
	function get_set( $uniqueID ){
		
		$page_settings = ( ! $this->page_settings ) ?  $this->load_page_settings() : $this->page_settings;
		
		if( isset($page_settings[ $uniqueID ]) )
			return $page_settings[ $uniqueID ]; 
		else 	
			return array();
		
	}


	/* 
	 * Use a cascade to get page's settings
	 */ 
	function page_settings(){

		$set = $this->settings_cascade( $this->local, $this->settings_cascade($this->type, $this->global));
			
		return $set;

	}



	function local_settings(){


		// if a template is active, lets use that.
		
		$set = $this->meta( $this->page->id, PL_SETTINGS );
		
		return $this->get_by_mode($set);

	}

	function type_settings(){

		$set = $this->meta( $this->page->typeid, PL_SETTINGS );

		return $this->get_by_mode($set);

	}
	
	function get_global_setting( $key, $args = array() ){
		$settings = $this->global; 
		
		$not_set = (isset($args['default'])) ? $args['default'] : false;
		
		$index = ( isset( $args['clone_id']) ) ? $args['clone_id'] : 'settings';

		return ( isset( $settings[ $index ][ $key ] ) ) ? $settings[ $index ][ $key ] : $not_set;
		
	}

	function get_setting( $key, $args = array() ){

		$scope = (isset($args['scope'])) ? $args['scope'] : 'cascade';
		
		if( $scope == 'local' ){
		
			$settings = $this->local; 
		
		} elseif( $scope == 'type' ){
		
			$settings = $this->type; 
		
		} elseif( $scope == 'global' ){
		
			$settings = $this->global; 
		
		}else 
			$settings = $this->set; 
		
		$not_set = (isset($args['default'])) ? $args['default'] : false;
		
		$index = ( isset( $args['clone_id']) ) ? $args['clone_id'] : 'settings';

		return ( isset( $settings[ $index ][ $key ] ) ) ? $settings[ $index ][ $key ] : $not_set;

	}


	function get_by_mode( $set ){
		
		$mode = ( $this->mode == 'detect' ) ? pl_get_mode() : $this->mode; 

		$set = wp_parse_args( $set, pl_settings_default() );

		return $set[ $mode ];
	}




}

































////////////////////////////////////////////////////////////////////
//
// TODO rewrite all this to use the ^^ above classes methods...
//
////////////////////////////////////////////////////////////////////
function pl_opt( $key, $default = false, $parse = false ){

	$val = get_option($key);

	if( !$val ){

		$val = $default;

	} elseif( $parse && is_array($val) && is_array($default)) {

		$val = wp_parse_args( $val, $default );

	}

	return $val;

}

function pl_opt_update( $key, $value ){

	update_option($key, $value);

}








function pl_meta_setting( $key, $metaID ){

	global $pldrft;

	$mode = $pldrft->mode;

	$set = pl_meta( $metaID, PL_SETTINGS );

	$settings = ( isset($set[ $mode ]) ) ? $set[ $mode ] : array();

	return ( isset( $settings[ $key ] ) ) ? $settings[ $key ] : false;

}


/*
 *
 * Local Option
 *
 */
function pl_settings( $mode = 'draft', $metaID = false ){

	if( $metaID ){

		$set = pl_meta( $metaID, PL_SETTINGS, pl_settings_default() );

	} else {

		$set = pl_get_global_settings();

	}

	$settings = ( isset($set[ $mode ]) ) ? $set[ $mode ] : pl_settings_default();

	return $settings;

}

function pl_settings_update( $new_settings, $mode = 'draft', $metaID = false ){

	do_action( 'pl_settings_update_action' );


	if ( $metaID )
		$settings = pl_get_meta_settings( $metaID );
	else
		$settings = pl_get_global_settings();

	// in case of empty, use live/draft default
	$settings = wp_parse_args( $settings, pl_settings_default() );

	// forgot why we stripslashes, if you remember, comment!
	$settings[ $mode ] = stripslashes_deep( $new_settings );

	// lets do some clean up
	// Gonna clear out all the empty values and arrays
	// Also, needs to be array or... deletehammer
	foreach ( $settings[ $mode ] as $uniqueID => &$the_settings )
	{	
		if ( !is_array( $the_settings ) )
			continue;

		foreach ( $the_settings as $setting_key => &$val )
		{
			if ( $val === '' ) {
				unset( $the_settings[ $setting_key ] );
			}
			
			// if a numeric index was set (bug)
			if ( is_numeric( $setting_key ) ) {
				unset( $the_settings[ $setting_key ] );
			}
		
			// accordion prevent null values from being saved and bloating things
			if ( is_array( $val ) )
			{
				foreach ( $val as $val_key => &$val_val )
				{
					if ( $val_val == 'false' || empty( $val_val ) ) {
						unset( $val[ $val_key ] );
					}
				}
				unset( $val_val );
			}
				
		}
		unset( $val );
	}
	unset( $the_settings );

	if ( $metaID )
		pl_meta_update( $metaID, PL_SETTINGS, $settings );
	else
		pl_update_global_settings( $settings );

	return $settings;
}

function pl_revert_settings( $metaID = false ){

	if( $metaID ){
		$settings = pl_meta( $metaID, PL_SETTINGS, pl_settings_default() );

	} else {
		$settings = pl_get_global_settings();
	}

	$settings['draft'] = $settings['live'];

	if( $metaID )
		pl_meta_update( $metaID, PL_SETTINGS, $settings );
	else
		pl_update_global_settings( $settings );

}


