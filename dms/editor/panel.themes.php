<?php

class EditorThemeHandler {

	var $preview_slug = 'pl-theme-preview';

	function __construct(  ){
		
		add_filter('pl_toolbar_config', array( $this, 'toolbar'));
		$this->url = PL_PARENT_URL . '/editor';
	}
	
	function toolbar( $toolbar ){
		if( ! is_array( $this->user_theme_tabs() ) )
			return $toolbar;
		$toolbar['theme'] = array(
			'name'	=> __( 'Theme Opts', 'pagelines' ),
			'icon'	=> 'icon-picture',
			'pos'	=> 40,
			'panel'	=> $this->user_theme_tabs()

		);
		
		return apply_filters('pl_themes_tabs_final', $toolbar);
	}
		
	function user_theme_tabs(){
		global $pl_user_theme_tabs; 
		
		if( isset( $pl_user_theme_tabs ) && !empty( $pl_user_theme_tabs ) && is_array( $pl_user_theme_tabs ) )
			return $pl_user_theme_tabs;		
	}
}
