<?php
/*
	Section: Navi
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A stylized navigation bar with multiple modes and styles.
	Class Name: PLNavi
	Filter: nav, dual-width
*/


class PLNavi extends PageLinesSection {


	function section_persistent(){
		register_nav_menus( array( 'navi_nav' => __( 'Navi Section', 'pagelines' ) ) );

	}

	function section_opts(){

		$opts = array(
			array(
				'type'	=> 'multi',
				'key'	=> 'navi_content',
				'title'	=> __( 'Logo', 'pagelines' ),
				'col'	=> 1,
				'opts'	=> array(
					array(
						'type'	=> 'image_upload',
						'key'	=> 'navi_logo',
						'label'	=> __( 'Navboard Logo', 'pagelines' ),
						'has_alt'	=> true,
						'opts'	=> array(
							'center_logo'	=> 'Center: Logo | Right: Pop Menu | Left: Site Search',
							'left_logo'		=> 'Left: Logo | Right: Standard Menu',
						),
					),
					array(
						'type'		=> 'check',
						'key'		=> 'navi_logo_disable',
						'label'		=> __( 'Disable Logo?', 'pagelines' ),
						'default'	=> false
					),
					array(
						'type'		=> 'check',
						'key'		=> 'navi_site_info',
						'label'		=> __( 'Show site title and tagline? (Logo must be disabled)', 'pagelines' ),
						'default'	=> false
					)
				)

			),
			array(
				'type'	=> 'multi',
				'key'	=> 'navi_nav',
				'title'	=> 'Navigation',
				'col'	=> 2,
				'opts'	=> array(
					array(
						'key'	=> 'navi_help',
						'type'	=> 'help_important',
						'label'	=> __( 'Using Megamenus (multi column drop down)', 'pagelines' ),
						'help'	=> __( 'Want a full width, multi column "mega menu"? Simply add a class of "megamenu" to the list items using the WP menu creation tool.', 'pagelines' )
					),
					array(
						'key'	=> 'navi_menu',
						'type'	=> 'select_menu',
						'label'	=> __( 'Select Menu', 'pagelines' ),
					),
					array(
						'key'	=> 'navi_search',
						'type'	=> 'check',
						'label'	=> __( 'Hide Search?', 'pagelines' ),
					),
					array(
						'key'	=> 'navi_offset',
						'type'	=> 'text_small',
						'place'	=> '100%',
						'label'	=> __( 'Dropdown offset from top of nav (optional)', 'pagelines' ),
						'help'	=> __( 'Default is 100% aligned to bottom. Can be PX or %.', 'pagelines' )
					)	
				)
			)
		);

		return $opts;

	}

	/**
	* Section template.
	*/
   function section_template( $location = false ) {

		$menu = ( $this->opt('navi_menu') ) ? $this->opt('navi_menu') : false;
		$offset = ( $this->opt('navi_offset') ) ? sprintf( 'data-offset="%s"', $this->opt('navi_offset') ) : false;
		$hide_search = ( $this->opt('navi_search') ) ? true : false;
		$class = ( $this->meta['draw'] == 'area' ) ? 'pl-content' : '';

		$blog_name = get_bloginfo( 'name' );
		$blog_tagline = get_bloginfo( 'description' );

		$hide_logo = ( $this->opt('navi_logo_disable') ) ? $this->opt('navi_logo_disable') : false;
		$show_site_info = ( $this->opt('navi_site_info') ) ? $this->opt('navi_site_info') : false;

		$logo_container_class = ( $this->opt('navi_logo_disable') ) ? ' ' : 'navi-container';

	?>
	<div class="navi-wrap <?php echo $class; ?> fix">
		<div class="navi-left <?php echo $logo_container_class; ?>">

			<?php if( '1' !== $hide_logo ) { ?>
				
				<a href="<?php echo home_url('/');?>"><?php echo $this->image( 'navi_logo', pl_get_theme_logo(), array(), get_bloginfo('name')); ?></a>
			
			<?php } else if ( '1' == $show_site_info ) { ?>
				
				<a href="<?php echo home_url('/');?>"><h1 class="navi-site-title"><?php echo $blog_name; ?></h1></a>
				<h2 class="navi-site-description"><?php echo $blog_tagline; ?></h2>

			<?php } ?>
		</div>
		<div class="navi-right">
			<?php

				$menu_args = array(
					'theme_location' => 'navi_nav',
					'menu' => $menu,
					'menu_class'	=> 'inline-list pl-nav sf-menu',
					'attr'			=> $offset,
				);
				echo pl_navigation( $menu_args );

				if( ! $hide_search )
					pagelines_search_form( true, 'navi-searchform');
			?>

		</div>
		<div class="navi-left navi-search">

		</div>



	</div>
<?php }

}


