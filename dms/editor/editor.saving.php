<?php



class PageLinesSave {


	function __construct(){


		add_filter( 'pl_ajax_fast_save', array( $this, 'fast_save' ), 10, 2 );

	}


	function fast_save( $response, $data ){

		do_action( 'pl_settings_update_action' );

		if( $data['run'] == 'map' ){

			$response = $this->save_map( $response, $data );

		} elseif (  $data['run'] == 'layout' ){

			$response = $this->save_layout( $response, $data );

		} elseif (  $data['run'] == 'form' ){

			$response = $this->save_form( $response, $data );

		} elseif (  $data['run'] == 'publish' ){

			$response = $this->publish( $response, $data );

		} elseif (  $data['run'] == 'create_items' ){

			$response = $this->create_items( $response, $data );

		} elseif (  $data['run'] == 'delete_items' ){

			$response = $this->delete_items( $response, $data );

		} elseif (  $data['run'] == 'scope' ){

			$response = $this->scope( $response, $data );

		} else
			$response['error'] = "No save operation set for ".$data['run'];

		$response['state'] = $this->get_state( $data );

		return $response;

	}

	function save_layout( $response, $data ){

		$px = $data['store']['px'];
		$percent = $data['store']['percent'];

		pl_global_setting_update('content_width_px', $px);
		pl_global_setting_update('content_width_percent', $percent);

		$response['px'] = $px;
		$response['_px'] = pl_setting('content_width_px');

		return $response;

	}

	function get_state( $data ){

		$state = array();
		$settings = array();
		$default = array('live'=> array(), 'draft' => array());

		$pageID = $data['pageID'];
		$typeID = $data['typeID'];

		// Local
		$settings['local'] = pl_meta( $pageID, PL_SETTINGS );

		if($typeID != $pageID)
			$settings['type'] = pl_meta( $typeID, PL_SETTINGS );

		$settings['global'] = pl_opt( PL_SETTINGS );

		foreach( $settings as $scope => $set ){

			$set = wp_parse_args($set, $default);

			$scope = str_replace('map-', '', $scope);

			if( $set['draft'] != $set['live'] ){
				$state[$scope] = $scope;
			}

		}

		if( count($state) > 1 )
			$state[] = 'multi';

		return $state;

	}

	function create_items( $response, $data ){

		$response['items'] = $items = ( isset($data['store']) ) ? $data['store'] : false;

		global $sections_data_handler;


		$response['result'] = ( $items ) ? $sections_data_handler->create_items( $items ) : 'No items sent.';


		return $response;
	}

	function delete_items( $response, $data ){

		$items = $data['store'];


		global $sections_data_handler;

		$response['result'] = $sections_data_handler->delete_items( $data['store'] );


		return $response;
	}

	function scope( $response, $data ){

		$scope = $data['scope'];

		$settings = ( isset( $data['store'] ) ) ? $data['store'] : false;

		if( $scope == 'global' ){

			if( $settings )
				pl_settings_update( stripslashes_deep( $settings ) );

			$response['Message'] = 'Global settings updated';

		} else {

			$template_mode = $data['templateMode'];

			$metaID = ( $template_mode == 'type' ) ? $data['typeID'] : $data['pageID'];

			pl_settings_update( $settings, 'draft', $metaID );

			$response['Message'] = sprintf( '%s with ID of %s settings updated.', $template_mode, $metaID );

		}


		return $response;
	}

	function publish( $response, $data ){

		global $sections_data_handler, $dms_cache;
		$pageID = $data['pageID'];
		$typeID = $data['typeID'];

		$response['result'] = $sections_data_handler->publish_items( $data['store'] );

		$section_handler = new PLCustomSections;
		$section_handler->update_objects( 'publish' );

		$tpl_handler = new PLCustomTemplates;
		$tpl_handler->update_objects( 'publish' );

		$settings = array();

		$settings['local'] = pl_meta( $pageID, PL_SETTINGS );
		$settings['type'] = pl_meta( $typeID, PL_SETTINGS );
		$settings['global'] = pl_get_global_settings();

		foreach($settings as $scope => $set){

			$set = wp_parse_args($set, array('live'=> array(), 'draft' => array()));

			$set['live'] = $set['draft'];

			$settings[ $scope ] = $set;

		}

		pl_meta_update( $pageID, PL_SETTINGS, $settings['local'] );
		pl_meta_update( $typeID, PL_SETTINGS, $settings['type'] );

		pl_update_global_settings( $settings['global'] );

		// Flush less
		$dms_cache->purge('live_css');

		// run clean post action to trigger caches to clear. Varnish, WPE etc.
		do_action( 'clean_post_cache' );

		return $response;
	}

	/*
	 * Saves only Map Data based on template mode (local or type)
	 */
	function save_map( $response, $data ){

		$config = $response['config'] =  $data['store'];
		$metaID = ( $data['templateMode'] == 'type' ) ? $data['typeID'] : $data['pageID'];
		$load = $response['load'] = $data['load'];
		$slug = 'ctemplate';

		foreach( pl_editor_regions() as $region ){
			if( ! isset( $config[ $region ] ) )
				$config[ $region ] = array( 'map' => array() );
		}

		foreach( $config as $region => $region_config ){

			$map = $region_config[ 'map' ];



			if( is_array( $map )){
				foreach( $map as $area => &$area_config ){

					if( isset( $area_config[ $slug ] ) && $area_config[ $slug ] != '' ){

						// if its a custom section, update that
						if( $load != 'section' ){
							$section_handler = new PLCustomSections;
							$section_handler->update( $area_config[ $slug ], array( 'map' => $area_config ) );
						}


						$area_config = array( $slug => $area_config[ $slug ] );

					} else
						$custom_template = false;


				}
				unset( $area_config );
			}


			if( $region == 'template' ) {

				$local[ $region ] = $map;

				if( isset( $region_config[ $slug ] ) && $region_config[ $slug ] != '' ){

					$custom_template = $region_config[ $slug ];

					// if its a template, update that
					if( $load != 'template' ){

						$tpl_handler = new PLCustomTemplates;
						$tpl_handler->update( $region_config[ $slug ], array( 'map' => $local[ $region ] ) );

					}

					$local[ $region ] = array( $slug => $region_config[ $slug ] );

				} else
					$custom_template = false;

			} else {

				$global[ $region ] = $map;

			}


		}




		$local_settings = pl_settings( 'draft', $metaID );
		$local_settings['custom-map'] = $local;
		pl_settings_update( $local_settings, 'draft', $metaID );

		$global_settings = pl_settings();
		$global_settings['regions'] = $global;
		pl_settings_update( $global_settings );

		return $response;
	}

	function save_form( $response, $data ){

		$form = $data['store'];
		$scope = $data['scope'];
		$key = ( isset($data['key']) ) ? $data['key'] : false;
		$uid = ( isset($data['uid']) && $data['uid'] != 'false' ) ? $data['uid'] : false;

		if( ! empty( $uid ) ){
			global $sections_data_handler;

			$response['result'] = $sections_data_handler->update_or_insert( array( 'uid' => $uid, 'draft' => $form[ $uid ] ) );
		}



		if( $scope == 'global' || ( isset( $form[ 'settings' ] ) && is_array( $form[ 'settings' ] ) ) ){

			$global_settings = pl_settings();

			// First parse sub settings field
			if( isset($form['settings']) )
				$form['settings']  = wp_parse_args( $form['settings'], $global_settings['settings'] );

			$response['form'] = $form;
			$global_settings = wp_parse_args( $form, $global_settings );
			pl_settings_update( $global_settings );

		}

		if( $scope == 'type' || $scope == 'local' ){

			$metaID = ( $scope == 'type' ) ? $data['typeID'] : $data['pageID'];

			$meta_settings = pl_settings( 'draft', $metaID );
			$meta_settings = wp_parse_args( $form, $meta_settings );
			pl_settings_update( $meta_settings, 'draft', $metaID );

		}

		if ( $scope == 'template' ){

			$handler = new PLCustomTemplates;

			$old_settings = $handler->retrieve_field( $key, 'settings' );

			$settings = wp_parse_args( $form, $old_settings );

			$handler->update( $key, array('settings' => $settings));

		}

		if ( $scope == 'section' ){

			$handler = new PLCustomSections;

			$old_settings = $handler->retrieve_field( $key, 'settings' );

			$settings = wp_parse_args( $form, $old_settings );

			$handler->update( $key, array('settings' => $settings));

			$response['settings'] = $settings;

			$response['new'] = $handler->retrieve( $key );
		}
		$response['scope'] = $scope;
		return $response;

	}
}
