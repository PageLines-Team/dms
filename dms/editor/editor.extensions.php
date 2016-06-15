<?php





class EditorExtensions {

	var $ext = array();

	function get_list(){
		$this->get_themes();
		$this->get_sections();

		return $this->ext;
	}

	function get_themes(){
		// Themes
		$themes = wp_get_themes();


		if(is_array($themes)){

			foreach($themes as $theme => $t){
				$class = array();

				$tags = $t->get('Tags');

				if( $t->get_template() != 'dms' && ! in_array('dms', $tags) ){
					continue;
				}
				
				$thumb = $t->get_screenshot( );

				if( is_file( sprintf( '%s/splash.png', $t->get_stylesheet_directory() ) ) )
				 	$splash = sprintf( '%s/splash.png', $t->get_stylesheet_directory_uri()  );
				else
					$splash = $thumb;

				$this->ext[ $theme ] = array(
					'id'		=> $theme,
					'name'		=> $t->name,
					'desc'		=> $t->description,
					'thumb'		=> $thumb,
					'splash'	=> $splash,
					'purchase'	=> '',
					'overview'	=> '',
					'status'	=> $this->theme_status( $t->get_template() )
				);
			}
		}
	}

	function get_sections(){
		
		$sections_handler = new PageLinesSectionsHandler;
		$sections = $sections_handler->get_available_sections();

		foreach($sections as $key => $s){

			$this->ext[ $s->id ] = array(
				'id'		=> $s->id,
				'name'		=> stripslashes( $s->name ),
				'desc'		=> stripslashes( $s->description ),
				'thumb'		=> $s->screenshot,
				'purchase'	=> '',
				'overview'	=> '',
				'docs_url'		=> ( isset( $s->docs_url ) ) ? $s->docs_url : false
			);

		}
	}



	function theme_status( $slug ) {

		// lets see if the stylesheet exists....
		$theme = wp_get_theme( $slug );

		$current = wp_get_theme();

		if( $theme->Name == $current )
			return 'active';
		if( $theme->exists() )
			return 'installed';
		else
			return false;
	}
}