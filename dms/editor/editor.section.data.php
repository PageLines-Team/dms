<?php

class PLSectionData{

	var $version_slug = "pl_db_version";
	function __construct(){
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->current_db_version = 0.43;
		$this->table_name = $wpdb->prefix . "pl_data_sections";
		$this->installed_db_version = get_option( $this->version_slug );

		// check if install needed, if so, run install routine

		if( $this->installed_db_version != $this->current_db_version || $wpdb->get_var( "SHOW TABLES LIKE '$this->table_name'" ) != $this->table_name )
			$this->install_table();
	}

	function unserialize( $data ) {
		if( is_serialized( $data ) )
			return unserialize( $data );
		else
			return json_decode( $data, true );
	}

	function install_table(){

		global $wpdb;

		$sql = "CREATE TABLE $this->table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				uid VARCHAR(10) NOT NULL,
				draft LONGTEXT NOT NULL,
				live LONGTEXT NOT NULL,
				UNIQUE KEY id (id),
				UNIQUE KEY uid (uid)
			);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( $this->version_slug, $this->current_db_version );

		$this->install_data();
	}

	function install_data() {

		$data = array(
			'uid'	=> 'u12345',
			'live'	=> '',
			'draft'	=> ''
		);
	   $rows_affected = $this->update_or_insert( $data );
	}



	function update_or_insert( $data ){

		$uid = $data['uid'];
		$draft = json_encode( $data['draft'] );
		$live = json_encode( array() );


		$query = $this->wpdb->prepare( "INSERT INTO $this->table_name (uid, draft, live)
										VALUES ( %s, %s, %s)
										ON DUPLICATE KEY UPDATE
										draft = VALUES(draft)", $uid, $draft, $live);


		$result = $this->wpdb->query( $query );

		return $result;
	}

	function create_items( $items ){

		if( ! empty( $items ) ){
			foreach( $items as $uid => $dat ){

				$result = array();
				$query = $this->wpdb->prepare( "INSERT INTO $this->table_name (uid, draft, live) VALUES ( %s, %s, %s ) ON DUPLICATE KEY UPDATE draft = VALUES(draft), live = VALUES(live);", $uid, json_encode( $dat ), json_encode( $dat ));

				$result[] = $this->wpdb->query( $query );
			}


		} else
			$result = 'No items sent to create.';


		return $result;

	}

	function publish_items( $items, $action = 'publish' ){

		$result = array();

		foreach( $items as $index => $uid ){

			if( $uid != ''){

				if( $action == 'revert' )
					$query = $this->wpdb->prepare("UPDATE $this->table_name SET draft = live WHERE uid = %s", $uid);
				else
					$query = $this->wpdb->prepare("UPDATE $this->table_name SET live = draft WHERE uid = %s", $uid);

				$this->wpdb->query( $query );

				$result[ $index ] = $uid;

			}

		}


		return $result;

	}


	function delete_items( $items ){

		$imploded_uids = join("','", $items);

		$query = $this->wpdb->prepare( "DELETE from $this->table_name Where uid IN ( %s )", $imploded_uids );

		$result = $this->wpdb->query( $query );

		return $result;

	}

	function update_section_option( $uid, $key, $value ){

		$query = $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE uid = %s", $uid );

		$result = $this->wpdb->get_results( $query );

		// no result returns empty array

		if( ! empty( $result ) ){

			foreach( $result as $section ){

				$draft_settings = stripslashes_deep( $this->unserialize( $section->draft ) );

				$draft_settings[ $key ] = $value;

				$new_draft_settings = json_encode( $draft_settings );

				$query = $this->wpdb->prepare("UPDATE $this->table_name SET draft = %s WHERE uid = %s", $new_draft_settings, $uid);

				$this->wpdb->query( $query );

				$query = $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE uid = %s", $uid );

				$result = $this->wpdb->get_results( $query );
			}

		}



	}

	function dump_opts() {

		$result = $this->wpdb->get_results( "SELECT * FROM $this->table_name");

		return $result;
	}

	function delete_section_option( $uid, $key ){

	}


	function get_section_data( $uids ){

		$start_time = microtime(TRUE);

		$imploded_uids = join("','", $uids);

		$query = sprintf("SELECT uid, draft, live from $this->table_name Where uid IN ( '%s' )", $imploded_uids );

		$rows = $this->wpdb->get_results( $query );

		$config = $this->configure_section_data( $uids, $rows );

		if( is_pl_debug() )
			pl_add_perform_data( round( microtime(TRUE) - $start_time, 3), __( 'Section Data Query', 'pagelines' ), __( 'Seconds', 'pagelines' ), __( 'Time for section data DB query and configuration.', 'pagelines' ) );

		return $config;
	}

	function configure_section_data( $uids, $rows ){

		$config = array();
		$rows_added = false;

		$mode = pl_get_mode();

		foreach( $uids as $uid ){
			$num_rows = 0;
			foreach( $rows as $set ){

				if( $set->uid == $uid ){

					$num_rows++;

					$config[ $uid ] = stripslashes_deep( $this->unserialize( $set->$mode ) );

				}


			}


			if( $num_rows == 0 ){


				$draft = new PageLinesOpts( 'draft' );
				$draft->load_page_settings();

				$upgrade_settings = array();
				$upgrade_settings['draft'] = $draft->get_set( $uid );

				$live = new PageLinesOpts( 'live' );
				$live->load_page_settings();

				$upgrade_settings['live'] = $live->get_set( $uid );

				$encoded_draft = json_encode( $upgrade_settings['draft'] );
				$encoded_live = json_encode( $upgrade_settings['live'] );

				$set = array( 'uid'	=> $uid, 'draft' => $encoded_draft, 'live' => $encoded_live );
				$num_rows++;
				$result = $this->wpdb->insert( $this->table_name, $set );
				$rows_added = true;

				$config[ $uid ] = $upgrade_settings[ $mode ];

			}

		}

		// Remove empties.
		foreach( $config as $i => $val ){

			if( empty( $val ) )
				unset( $config[ $i ] );

		}

		return $config;

	}

}
