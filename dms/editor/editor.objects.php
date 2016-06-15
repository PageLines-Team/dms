<?php 


/* 
 * User object abstraction class.
 */ 
class PLCustomObjects{
	
	function __construct( $slug ){
		
		$this->slug = $slug;
		
		$this->objects = $this->get_all( );
	}
	
	function default_objects(){
		$d = array();
		return $d;
	}
	
	function default_fields(){
		$d = array(
			'key'	=> false,
			'name'	=> __( 'No Name', 'pagelines' ),
			'desc'	=> '', 
			'map'	=> array(),
			'settings'	=> array()
		);
		
		return $d;
	}
	
	function get_all(){
		
		$all = pl_opt( $this->slug, pl_settings_default() );
	
		// Upgrade from legacy mode
		if( ! isset( $all['draft'] ) || empty( $all['draft'] ) ){
		
			if( ! isset( $all['draft'] ) ){
				
				$new = array(
					'draft' => $all, 
					'live'	=> $all
				);

				
			} elseif( empty( $all['draft'] ) ) {
				
				$new = wp_parse_args( array( 'draft' => $this->default_objects() ), $all );
			
			}
		
			pl_opt_update( $this->slug, $new );
			
			
			
			$all = $new;
				
		} 
		
		

		return $all[ pl_get_mode() ];
	
	}


	
	function update_objects( $action = 'draft' ){
		
		$all = pl_opt( $this->slug );
		
		if( $action == 'publish' ){
		
			$all['live'] = $all['draft'];
			
		} elseif( $action == 'revert' ) {
			
			$all['draft'] = $all['live'];
			
		} else 
			$all[ 'draft' ] = $this->objects;
		
		pl_opt_update( $this->slug, $all );
	}
	
	function create( $args = array() ){
		
		$args = wp_parse_args( $args, $this->default_fields());
		
		$key = ( $args['key'] ) ? $args['key'] : pl_create_id( $args['name'] );

		if( isset( $this->objects[$key] ) )
			$key = $key . '_' . pl_new_clone_id();

		$new = array( $key => $args );

		// turns out you can add arrays, puts new templates at front
		$this->objects = $new + $this->objects; 
		
		$this->update_objects( );
		
		return $key;
	}
	
	function retrieve( $key ){
		
		if( isset( $this->objects[ $key ]) )
			return wp_parse_args( $this->objects[ $key ], $this->default_fields() ); 
		else
			return false;
			
	}
	
	function retrieve_field( $key, $field ){
		$object = $this->retrieve( $key ); 
		
		if( $object && isset( $object[$field ]) )
			return $object[$field ]; 
		else
			return false;
	}
	
	function update( $key, $args ){
		
		$object = ( isset($this->objects[ $key ]) ) ? $this->objects[ $key ] : array();
		
		$this->objects[ $key ] = wp_parse_args( $args, $object );
		
		$this->update_objects();
		
		return $key;
		
	}
	
	function delete( $key ){
		
		if( isset( $this->objects[ $key ] ) || $this->objects[ $key ] == null){
			
			unset( $this->objects[ $key ] );
			$this->update_objects();
			return $key;
			
		} else
			return array('key not found', $key, $this->objects);
			
	}
	
}
