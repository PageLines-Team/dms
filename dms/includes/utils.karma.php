<?php


class PLKarma {
	
	var $option_key = '_pl_karma';
	
	 function __construct()   {	
		
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
        add_action( 'wp_ajax_pl_karma', array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_pl_karma', array( $this, 'ajax' ) );
		
	}
	
	function enqueue_scripts() {
		
		wp_localize_script( 'pagelines-common', 'plKarma', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
		
	}
	
	function ajax($post_id) {
		
		$field_id = ( isset( $_POST['karma_id'] ) ) ? $_POST['karma_id'] : false;
		
		if( $field_id ) {
			
			$post_id = str_replace('pl-karma-', '', $_POST['karma_id']);
			echo $this->karma_post($post_id, 'update');
		
		} else {
			$post_id = str_replace('pl-karma-', '', $_POST['karma_id']);
			echo $this->karma_post($post_id, 'get');
		}
		
		exit;
	}
	
	
	function karma_post($post_id, $action = 'get') {
	
		if( ! is_numeric($post_id) ) 
			return;
	
		$karma_count = get_post_meta($post_id, $this->option_key, true);
	
		switch($action) {
		
			case 'get':
			
				
				if( ! $karma_count ){
					
					$karma_count = 0;
					
					add_post_meta($post_id, $this->option_key, $karma_count, true);
				
				}
				
				return $karma_count;
				
				break;
				
			case 'update':
				
				
				if( isset($_COOKIE[ $this->option_key. $post_id]) ) return $karma_count;
				
				$karma_count++;
				
				update_post_meta( $post_id, $this->option_key, $karma_count );
				
				setcookie( $this->option_key. $post_id, $post_id, time()*20, '/');
				
				return $karma_count;
				break;
		
		}
	}


	function add_karma( $id = false, $args = array() ) {
		
		$defaults = array(
			'classes'	=> '',
			'attr'		=> '',
			'icon'		=> ''
		);
		
		$atts = wp_parse_args( $args, $defaults ); 
		
		global $post;
		
		$id = ($id) ? $id : $post->ID;

		$output = $this->karma_post($id);
  
  		$class = 'pl-karma pl-social-counter pl-social-pagelines';

  		$title = __('Give Karma', 'pagelines');

		if( isset( $_COOKIE['pl_karma_'. $id] ) ){
			$class = 'pl-karma loved';
			$title = __('You already gave karma!', 'pagelines');
		}
		
		$karma_icon = ( '' != pl_setting( 'karma_icon' ) ) ? pl_setting( 'karma_icon' ) : 'sun';
		if( '' != $atts['icon'] )
			$karma_icon = $atts['icon'];
		
		return sprintf('<a href="#" class="%s %s" id="pl-karma-%s" title="%s" data-social="pagelines" %s> <span class="pl-social-icon"><i class="icon icon-%s"></i></span> <span class="pl-social-count">%s</span></a>', 
						$class, 
						$atts['classes'],
						$id,
						$title,
						$atts['attr'],
						$karma_icon,
						$output
					);
		
	}
	
}


global $pl_karma;
$pl_karma = new PLKarma();

function pl_karma( $id = false, $args = array() ) {
	
	global $pl_karma;

	return $pl_karma->add_karma( $id, $args ); 
	
}

