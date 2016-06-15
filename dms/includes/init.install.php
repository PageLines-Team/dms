<?php



class PageLinesInstall{

	function __construct(){


		add_filter( 'pl_theme_default_settings', array($this, 'mod_default_settings') );

		// Add regions to settings
		add_filter( 'pl_theme_default_regions', array($this, 'mod_default_regions') );

		// Add theme templates when default templates are set
		add_filter( 'pl_default_templates', array($this, 'add_templates_at_default') );

		add_filter( 'pl_default_template_handler', array($this, 'default_template_handling') );

	//	add_action( 'wp', array($this, 'pagelines_check_install'), 15 );
	//	add_action( 'admin_init', array( $this, 'pagelines_check_install' ), 10);
		// MUST COME AFTER FILTERS!
		$this->pagelines_check_install();

	}

	function pagelines_check_install() {

		$install = false;
		$url = add_query_arg( array( 'edtr' => 'on', 'toolbox' => 'open' ), site_url() );
		if( isset($_REQUEST['pl-install-theme'] ) && ! pl_is_wporg() ){
			$install = true;
		}

		if( is_admin() ){
			global $pagenow;

			if( is_customize_preview() ) {
				return false;
			}

			if( isset($_REQUEST['activated'] ) && $pagenow == "themes.php" && ! pl_is_wporg() ) {
				$install = true;
			}

			if( isset($_REQUEST['activated'] ) && $pagenow == "themes.php" && pl_is_wporg() ) {
				add_action( 'admin_notices', array( $this, 'install_notice' ) );
				$install = false;
			}


		if( pl_is_wporg() && isset( $_REQUEST['i-love-wporg'] ) )
			$install = true;


		}

		if( $install == true  ){

			// Simon why do we need this??
			if( get_theme_mod( 'pl_installed' ) ) {
				wp_redirect( $url );
				exit();
			} else {
				$url = $this->run_installation_routine();
				wp_redirect( $url );
				exit();
			}
		}
	}

	function install_notice() {
		?>
		<div class="updated fade">
			<p>Hey there! Looks like you just activated PageLines DMS. Remember all the editing tools are on the frontend of your site.
				<br />Click <a  href="<?php echo site_url(); ?>">here to go straight there.</a><br />Or why not let us create a draft page and apply a simple template to get you started? <a href="<?php echo admin_url( 'index.php?i-love-wporg=true' ); ?>">Yes please!<a>
					</p>
					</div>
					<?php
	}


	function run_installation_routine( $url = '' ){

		set_theme_mod( 'pl_installed', true );

		$settings = pl_get_global_settings();

		// Only sets defaults if they are null
		set_default_settings();

		if( is_file( trailingslashit( get_stylesheet_directory() ) . 'pl-config.json' ) ) {
			$settings_handler = new PageLinesSettings;
			$settings_handler->import_from_child();
		}


		$this->apply_page_templates();

		// Publish New Templates
		$tpl_handler = new PLCustomTemplates;
		$tpl_handler->update_objects( 'publish' );


		// Add Templates
		$id = $this->page_on_activation();


		// Redirect
		$url = add_query_arg( 'pl-installed-theme', pl_theme_info('template'), get_permalink( $id ) );

		return $url;

	}

	function add_templates_at_default( $tpls ){

		$tpls = array_merge( $this->page_templates( ), $tpls );

		return $tpls;

	}

	function load_page_templates(){

		$page_templates = $this->page_templates();

		foreach( $page_templates as $tpl ){
			$templateID = pl_add_or_update_template( $tpl );
		}

	}

	function apply_page_templates(){
		$mapping = $this->map_templates_to_pages();

		foreach( $mapping as $type => $tpl ){

			$id = pl_special_id( $type );
			pl_set_page_template( $id, $tpl, 'both' );
		}
	}

	// Override this to set templates on install
	function map_templates_to_pages(){
		return array();
	}

	function mod_default_regions( $defaults = array() ){

		return $this->global_region_map();

	}

	function mod_default_settings( $defaults ){

		return wp_parse_args( $this->set_global_options(), $defaults );

	}

	// Override this function in core/child themes
	// It will automatically load and/or update templates
	function page_templates( ){
		$templates = array(
			'welcome'	=> $this->template_welcome()
		);

		return $templates;
	}

	// Override this function in core/child themes
	// Use it to set global options on activation of theme
	function set_global_options( ){
		return array();
	}

	// Override this to change default templates for various types of pages
	function default_template_handling( $t ){
		return $t;
	}

	// Override this function in core/child themes
	function global_region_map(){

		$map = array(
			'header'	=> array(),
			'footer'	=> array(
				array(
					'settings'	=> array(
						'pl_area_theme' 	=> 'pl-black',
					),
					'content'	=> array(
						array(
							'object'	=> 'PageLinesColumnizer',
							'object'	=> 'PLWatermark'

						),
					)
				),
				array(
					'settings'	=> array(
						'pl_area_theme' 	=> 'pl-grey',
					),
					'content'	=> array(
						array(
							'object'	=> 'PLSocialinks',

						),
					)
				)
			),
			'fixed'	=> array(
				array( 'object'	=> 'PLNavi' )
			)
		);

		return $map;

	}

	// Override this function in core/child themes
	function activation_page_data(){

		return array();
	}


	function template_welcome(){

		$template['name'] = 'Welcome';

		$template['desc'] = 'Getting started guide &amp; template.';

		$template['map'] = array(

			array(
				'object'	=> 'PLSectionArea',
				'settings'	=> array(
					'pl_area_theme' 		=> 'pl-dark-img',
					'pl_area_background'	=> '[pl_parent_url]/images/getting-started-mast-bg.jpg',
					'pl_area_pad'		=> '80px',
					'pl_area_parallax'	=> 'pl-parallax'
				),

				'content'	=> array(
					array(
						'object'	=> 'PLMasthead',
						'settings'	=> array(
							'pagelines_masthead_title'		=> __( 'Congratulations!', 'pagelines' ),
							'pagelines_masthead_tagline'	=> sprintf( __( 'You are up and running with PageLines %s.', 'pagelines' ), PL_NICETHEMENAME ),
							'pagelines_masthead_img'		=> '[pl_parent_url]/images/getting-started-pl-logo.png',
							'masthead_button_link_2'		=> home_url(),
							'masthead_button_text_2'		=> __( 'View Your Blog Page <i class="icon icon-angle-right"></i>', 'pagelines' ),
							'masthead_button_theme_2'		=> 'btn-ol-white',
							'masthead_button_link_1'		=> '#user-guide',
							'masthead_button_text_1'		=> __( 'View User Guide <i class="icon icon-angle-down"></i>', 'pagelines' ),
							'masthead_button_theme_1'		=> 'btn-ol-white'
						)
					),
				)
			),
			array(
				'content'	=> array(
					array(
						'object'	=> 'pliBox',
						'settings'	=> array(
							'ibox_array'	=> array(
								array(
									'title'	=> __( 'Stay in Touch', 'pagelines' ),
									'text'	=> __( 'New to PageLines? Stay in touch by following us on Facebook and Twitter.<p>[like_button url="http://www.facebook.com/pagelines/" ] [twitter_button type="follow" handle="pagelines"]</p>', 'pagelines' ),
									'icon'	=> 'thumbs-up'
								),
								array(
									'title'	=> __( 'Forum', 'pagelines' ),
									'text'	=> __( 'Have questions? We are happy to help, just search or post on PageLines Forum.', 'pagelines' ),
									'icon'	=> 'comment',
									'link'	=> 'http://forum.pagelines.com/'
								),
								array(
									'title'	=> __( 'Docs', 'pagelines' ),
									'text'	=> __( 'Time to dig in. Check out the Docs for specifics on creating your dream website.', 'pagelines' ),
									'icon'	=> 'file-text',
									'link'	=> 'http://docs.pagelines.com/'
								),
							)
						)
					),
				)
			),
			array(
				'settings'	=> array(
					'pl_area_theme'	=> 'pl-black'
				),
				'content'	=> array(
					array(
						'object'	=> 'PageLinesHighlight',
						'settings'	=> array(
							'_highlight_head'		=> '<span id="user-guide">PageLines DMS User Guide</span>',
							'_highlight_subhead'	=> 'The fastest &amp; easiest way to get started.'
						)
					),
					array(
						'object'	=> 'PageLinesMediaBox',
						'span'		=> 10,
						'offset'	=> 1,
						'settings'	=> array(
							'mediabox_html'	=> '<iframe  class="scribd_iframe_embed" src="//www.scribd.com/embeds/213323278/content?start_page=1&view_mode=slideshow&access_key=key-1dzmy27btqjwamjd0dye&show_recommendations=false" data-auto-height="false" data-aspect-ratio="0.772922022279349" scrolling="no" id="doc_40327" width="100%" height="1000" frameborder="0"></iframe>'
						)
					),
				)
			),
			array(
				'content'	=> array(
					array(
						'object'	=> 'PageLinesHighlight',
						'settings'	=> array(
							'_highlight_head'		=> '<span id="user-guide">Getting Started Video</span>',
							'_highlight_subhead'	=> 'A 5-minute overview of DMS.'
						)
					),
					array(
						'object'	=> 'PageLinesMediaBox',
						'span'		=> 10,
						'offset'	=> 1,
						'settings'	=> array(
							'mediabox_html'	=> "<iframe width='700' height='420' src='//www.youtube.com/embed/BracDuhEHls?rel=0&vq=hd720' frameborder='0' allowfullscreen></iframe>"
						)
					),
				)
			),
			array(
				'settings'	=> array(
					'pl_area_theme'	=> 'pl-black',
					'pl_area_color'	=> '#337EFF',
					'pl_area_color_enable'	=> 1
				),
				'content'	=> array(
					array(
						'object'	=> 'PLICallout',
						'settings'	=> array(
							'icallout_text'		=> 'Thanks for using PageLines!',
							'icallout_link'		=> 'http://www.pagelines.com/',
							'icallout_link_text'	=> 'Visit PageLines.com',
							'icallout_btn_theme'	=> 'btn-ol-white'
						)
					),
				)
			)
		);

		return $template;
	}


	function page_on_activation( $templateID = 'welcome' ){

		global $user_ID;

		$data = $this->activation_page_data();

		$page = array(
			'post_type'		=> 'page',
			'post_status'	=> 'draft',
			'post_author'	=> $user_ID,
			'post_title'	=> __( 'PageLines Getting Started', 'pagelines' ),
			'post_content'	=> $this->getting_started_content(),
			'post_name'		=> 'pl-getting-started',
			'template'		=> 'welcome',
		);

		$post_data = wp_parse_args( $data, $page );

		// Check or add page (leave in draft mode)
		$pages = get_pages( array( 'post_status' => 'draft' ) );
		$page_exists = false;
		foreach ($pages as $page) {

			$name = $page->post_name;

			if ( $name == $post_data['post_name'] ) {
				$page_exists = true;
				$id = $page->ID;
			}

		}

		if( ! $page_exists )
			$id = wp_insert_post(  $post_data );


		pl_set_page_template( $id, $post_data['template'], 'both' );

		return $id;
	}



	function getting_started_content(){

		ob_start();

		?>
		<h3 class="center"><?php printf( __( 'Welcome to PageLines %s.', 'pagelines' ), PL_NICETHEMENAME ) ?></h3>
		<iframe  class="scribd_iframe_embed" src="//www.scribd.com/embeds/213323278/content?start_page=1&view_mode=slideshow&access_key=key-1dzmy27btqjwamjd0dye&show_recommendations=false" data-auto-height="false" data-aspect-ratio="0.772922022279349" scrolling="no" id="doc_40327" width="100%" height="1000" frameborder="0"></iframe>

		<?php

		return ob_get_clean();

	}


}
