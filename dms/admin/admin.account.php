<?php


class PLAccountAdmin {
	
	function __construct(){
		
		add_action( 'pl_ajax_pl_account_actions', array( $this, 'pl_account_actions' ), 10, 2 );
		
//		add_action( 'admin_init', array( $this, 'activation_check_function' ) );
		
		add_action( 'pagelines_options_pagelines_account', array( $this, 'pagelines_account') );
	}
	
	
	function get_account_data(){
		
		$data = array(
			'email'		=> '', 
			'key'		=> '',
			'message'	=> '', 
			'avatar'	=> '', 
			'name'		=> '',
			'description'	=> '',
			'active'	=> false, 
			'real_user'	=> false,
			'url'		=> '',
			'karma'		=> 0,
			'lifetime_karma'	=> 0
			
		);
		
		$activation_data = (get_option( 'dms_activation' ) && is_array(get_option( 'dms_activation' ))) ? get_option( 'dms_activation' ) : array();
		
		$data = wp_parse_args( $activation_data, $data);
		
		return $data;
		
	}
	
	function pagelines_account(){

		$disabled = '';
		$email = '';
		$key = '';
		$activate_text = '<i class="icon icon-star"></i> Activate Pro';
		$activate_btn_class = 'btn-primary'; 
		
		
		$data = $this->get_account_data();
		
		$active = $data['active'];
		
		$disable = ($active) ? 'disabled' : '';

		$activation_message = ($data['message'] == '') ? 'Site not activated.' : $data['message'];

		?>	
		<div class="pl-account-form">
			
			<div class="update-nag pl-account-message">
				
				<div class="the-msg">
					<?php if( $active ):  ?>
						<?php _e( 'Pro Activated!', 'pagelines' ); ?>
						<span class="description"><a href="http://www.pagelines.com/my-account/">View PageLines Account</a></span>
					<?php else: ?>
						<strong><?php _e( 'Site Not Activated', 'pagelines' ); ?></strong>
					<?php endif; ?>
				</div>
			</div>
		</div>	

		<?php
		return;
	}
	
	function activation_check_function() {

		if( defined( 'DOING_AJAX' ) && true == DOING_AJAX )
			return;

		if ( ! current_user_can( 'edit_theme_options' ) )
			return;

		if( ! pl_is_pro() ) // no need if were not activated
			return;

		$data = get_option( 'dms_activation', array( 'active' => false, 'key' => '', 'message' => '', 'email' => '' ) );

		if( ! isset( $data['date'] ) ) {
			$data['date'] = date( 'Y-m-d' );
		}

		if( isset( $data['date'] ) && $data['date'] > date( 'Y-m-d' ) && $data['date'] <= date('Y-m-d', strtotime('+7 days', strtotime( $data['date'] ) ) ) )
			return false;
		else
			$data['date'] = date( 'Y-m-d' );

		$key = (isset($data['key'])) ? $data['key'] : false;
		$email = (isset($data['email'])) ? $data['email'] : false;

		
		$url = sprintf( 'http://www.pagelines.com/index.php?wc-api=software-api&request=%s&product_id=dmspro&licence_key=%s&email=%s&instance=%s', 'check', $key, $email, site_url() );


		$result = wp_remote_get( $url );

		// if wp_error save error and abort.
		if( is_wp_error($result) ) {
			$data['last_error'] = $result->get_error_message();
			update_option( 'dms_activation', $data );
			return false;
		} else {
			$data['last_error'] = '';
			update_option( 'dms_activation', $data );
		}

		// do a couple of sanity checks..
		if( ! is_array( $result ) )
			return false;

		if( ! isset( $result['body'] ) )
			return false;

		$rsp = json_decode( $result['body'] );

		if( ! is_object( $rsp ) )
			return false;

		if( ! isset( $rsp->success ) )
			return false;

		// if success is true means the key was valid, move along nothing to see here.
		if( true == $rsp->success ) {

			$data['date'] = date('Y-m-d', strtotime('+7 days', strtotime( $data['date'] ) ) );
			update_option( 'dms_activation', $data );

			return;
		}

		if( isset( $rsp->error ) && isset( $rsp->code ) ) {
			// lets try again tomorrow
			$data['date'] = date('Y-m-d', strtotime('+1 days', strtotime( $data['date'] ) ) );
			$data['trys'] = ( isset( $data['trys'] ) ) ? $data['trys'] + 1 : 1;
			update_option( 'dms_activation', $data );

			if( $data['trys'] < 3 ) // try 2 times.
				return;

			self::send_email( $rsp, $data );
		}
	}

	function send_email( $rsp, $data ) {

			$data = get_option( 'dms_activation' );
			$key = (isset($data['key'])) ? $data['key'] : '';
			
			$message = sprintf( "The DMS activation key %s failed to authenticate after 2 tries. Please log into your account and check your subscription at https://www.pagelines.com/my-account/\n\nThe keyserver error was: %s", $key, $rsp->error );
			wp_mail( get_bloginfo( 'admin_email' ), 'DMS Activation Failed', $message );
			update_option( 'dms_activation', array() );
	}

	

	
	function remote_key_request( $request, $key, $email ){
		
		$url = sprintf( 
			'http://www.pagelines.com/?wc-api=software-api&request=%s&product_id=dmspro&licence_key=%s&email=%s&instance=%s', 
			$request, 
			$key, 
			$email, 
			site_url() 
		);	

		$data = wp_remote_get( $url );

		$rsp = ( ! is_wp_error( $data ) && isset( $data['body'] ) ) ? (array) json_decode( $data['body'] ) : array();
		
		return $rsp;
		
	}
	
	function remote_user_request( $email, $type = 'std' ){
		
		$url = sprintf( 
				'%s&request=public_user&email=%s&type=%s', 
				PL_API_URL,
				$email,
				$type
			);
		$data = wp_remote_get( $url, array( 'timeout' => 20 ) );		
		$rsp = ( ! is_wp_error( $data ) && isset($data['body'] ) ) ? (array) json_decode( $data['body'] ) : array();		
		return $rsp;
	}
	
	function pl_account_actions( $response, $postdata ) {
		$response['he'] = 'whats';
	
	
		$key = $postdata['key'];
		$email = $postdata['email'];	
		$reset = ($postdata['reset'] == "true") ? true : false ;
		$update = ($postdata['update'] == "true") ? true : false ;
				
		$response = array( 
			'key'	=> $key, 
			'email'	=> $email, 
			'reset'	=> $reset
		);
		$rsp = '';
		
		$default_activation = array( 
						'active' 			=> false,
						'message' 			=> '',  
						'key' 				=> '', 
						'email' 			=> $email, 
						'date'				=> date( 'Y-m-d' ),
						'name'				=> '', 
						'description'		=> '', 
						'karma'				=> '', 
						'lifetime_karma'	=> '', 
						'avatar'			=> '',
						
					);
		
		
		// DEACTIVATION
		
		// grab erroneous output
		ob_start();
		
		$old_activation = get_option( 'dms_activation' ); 

		$old_activation = wp_parse_args( $old_activation, $default_activation);

		$currently_active = $old_activation['active'];
		
		if( $reset && $currently_active ){
			
			$current_key = ( isset( $old_activation['key'] ) ) ? $old_activation['key'] : false;
			$current_email = ( isset( $old_activation['email'] ) ) ? $old_activation['email'] : false;
			
			$rsp = $this->remote_key_request( 'deactivation', $current_key, $current_email );
			
			$response['deactivation_response'] = $rsp; 
			
			$response['messages'][] = (isset($rsp['error'])) ? $rsp['error'] : '<i class="icon icon-remove"></i> Deactivated!';
			$response['messages'][] = (isset($rsp['message'])) ? $rsp['message'] : '';
			$message = ( isset( $rsp[ 'message' ] ) ) ? $rsp[ 'message' ] : '';
			$instance = ( isset( $rsp[ 'instance' ] ) ) ? $rsp[ 'instance' ] : '';
			
			$new = array(
				'key'		=> '',
				'active'	=> false,
				'message'	=> $message,
			); 	
			
			$data_to_store = wp_parse_args( $new, $old_activation ); 
			
			update_option( 'dms_activation', $data_to_store );
			
			$response['refresh'] = true;
		}
		
		// ACCOUNT
		if( $email != '' && ! $reset ){
			
			$new_install = get_option('pl_new_install');
			
			$type = ( !$new_install || $new_install == 'yes' ) ? 'new_activation' : 'std';
			
			$rsp = $this->remote_user_request( $email, $type );
			
			$rsp['email'] = $email; // not passed back on error
			
			// Email doesn't exist
			if( isset($rsp['error']) ){
				
				$rsp['real_user'] = false;
				$updated_user = wp_parse_args( $rsp, $default_activation ); 
			
			} else {
				$rsp['real_user'] = true;
				$updated_user = wp_parse_args( $rsp, $old_activation ); 
				
				update_option('pl_new_install', 'no');
			}
		
			$response[ 'user_data' ] = $updated_user;
			
			$response['messages'][] = (isset($rsp['error'])) ? $rsp['error'] : '<i class="icon icon-user"></i> User Updated!';
			$response['messages'][] = (isset($rsp['message'])) ? $rsp['message'] : '';
		
			// SET KEY
			
			// ACTIVATION OR DEACTIVATION 
			
				// If currently active, and key is blank that means they want to deactivate
				// If not set and blank, who cares? 
				// If set, and error, update message?
				// If email unset and key, then it will error. I guess deactivate.
				
			
			
			if( $key != '' && ! $currently_active ){
				
				$request = 'activation';
				$response['request'] = $request;
				
				$rsp = $this->remote_key_request( $request, $key, $email );
				
				$response[ 'data' ] = $rsp;
				
				$message = ( isset( $rsp[ 'message' ] ) ) ? $rsp[ 'message' ] : '';
				
				$instance = ( isset( $rsp[ 'instance' ] ) ) ? $rsp[ 'instance' ] : '';
				
				// Set messages for quick JS response 
				$response['messages'][] = (isset($rsp['error'])) ? $rsp['error'] : '<i class="icon icon-star"></i> Site Activated!';
				$response['messages'][] = (isset($rsp['message'])) ? $rsp['message'] : '';
				
				
				if( isset( $rsp['activated'] ) && $rsp['activated'] == true ){
					
					$new = array(
						'key'		=> $key,
						'active'	=> true,
						'instance'	=> $instance,
						'message'	=> $message,
					); 	
					
					$data_to_store = wp_parse_args( $new, $updated_user ); 
					
					update_option( 'dms_activation', $data_to_store );
					
					
				}
				
				
					
			} else {
				$response['refresh'] = true;
				update_option( 'dms_activation', $updated_user );
			}	
			
		
			
		} elseif( ! $reset ) {
			
			$response['messages'][] = 'Site not activated.';
			$response['messages'][] = 'No email set.';
				 	
			
		}
		
		if( ! isset( $rsp['error'] ) || $rsp['error'] == '' ){
			$response['refresh'] = true;
		}

		$response['erroneous_output'] = ob_get_clean();
		
		return $response;
	}
}