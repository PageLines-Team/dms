<?php
/*
	Section: NavBar
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A responsive navigation bar for your website.
	Class Name: PLNavBar
	Workswith: header
	Loading: active
	Filter: nav, dual-width
*/

/**
 * Main section class
 *
 * @package PageLines DMS
 * @author PageLines
 */
class PLNavBar extends PageLinesSection {

	var $default_limit = 2;

	function section_styles() {
		wp_enqueue_script( 'navbar', $this->base_url.'/navbar.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function section_persistent() {
		register_nav_menus( array( 'main_nav' => __( 'Main Nav Section', 'pagelines' ) ) );
	}

	function section_opts(){

		$opts = array(
			
			array(
					'key'		=> 'navbar_logo',
					'default'	=> '[pl_parent_url]/images/dms.png',
					'version'	=> 'pro',
					'col'		=> 2,
					'type'		=> 'image_upload',
					'label'		=> __( 'NavBar Logo', 'pagelines' ),
					'title'		=> __( 'NavBar Logo', 'pagelines' ),
					'ref'		=> __( 'Use this feature to add the NavBar section as a fixed navigation bar on the top of your site.<br/><br/><strong>Notes:</strong> <br/>1. Only visible in Fixed Mode.<br/>2. Image Height is constricted to a maximum 29px.', 'pagelines' )
				),
			array(
				'default' 	=> '',
				'key'		=> 'navbar_multi_option_theme',
				'type' 		=> 'multi',
				'opts'=> array(

					 array(
							'key'			=> 'navbar_theme',
							'default'		=> 'base',
							'type' 			=> 'select',
							'label' 	=> __( 'Standard NavBar - Select Theme', 'pagelines' ),
							'opts'	=> array(
								'base'			=> array( 'name'	=> __( 'Base Color', 'pagelines' ) ),
								'black-trans'	=> array( 'name'	=> __( 'Black', 'pagelines' ) ),
								'blue'			=> array( 'name'	=> __( 'Blue', 'pagelines' ) ),
								'grey'			=> array( 'name'	=> __( 'Light Grey', 'pagelines' ) ),
								'orange'		=> array( 'name'	=> __( 'Orange', 'pagelines' ) ),
								'red'			=> array( 'name'	=> __( 'Red', 'pagelines' ) ),
							),
						),
				),
				'title'					=> __( 'NavBar Theme', 'pagelines' ),
				'help'					=> __( 'The NavBar comes with several color options. Select one to automatically configure.', 'pagelines' )

			),
			array(
				'key'		=> 'navbar_multi_option_menu',
				'type' 		=> 'multi',
				'title'		=> __( 'NavBar Menu', 'pagelines' ),
				'help'		=> __( 'The NavBar uses WordPress menus. Select one for use.', 'pagelines' ),
				'opts'		=> array(
					array(
							'key'			=> 'navbar_menu' ,
							'type' 			=> 'select_menu',
							'label' 	=> __( 'Select Menu', 'pagelines' ),
						),
				),


			),
			array(
				'key'		=> 'navbar_multi_check',
				'type' 		=> 'multi',
				'title'					=> __( 'NavBar Configuration Options', 'pagelines' ),
				'opts'		=> array(
					
					array(
						'key'			=> 'navbar_enable_hover',
						'type'			=> 'check',
						'label'			=> __( 'Activate dropdowns on hover.', 'pagelines' ),
					),

					array(
						'key'			=> 'navbar_alignment',
						'type'			=> 'check',
						'default'		=> true,
						'label'			=> __( 'Align Menu Right? (Defaults Left)', 'pagelines' ),
					),
					array(
						'key'			=> 'navbar_hidesearch',
						'type'			=> 'check',
						'default'		=> true,
						'label'			=> __(  'Hide Search?', 'pagelines' ),
					),
				),

			),



		);

		return $opts;

	}

	/**
	* Section template.
	*/
   function section_template( $location = false ) {

	$passive = ( 'passive' == $location ) ? true : false;
	$class = array();

	// if fixed mode
	if( $passive || $this->meta['draw'] == 'area'){
		$class[] = 'navbar-full-width';
		$content_width_class = 'pl-content boxed-wrap boxed-nobg';
	} else {
		$class[] = 'navbar-content-width';
		$content_width_class = '';
	}
	
	$theme = ( $this->opt( 'navbar_theme' ) ) ? $this->opt( 'navbar_theme' ) : false;

	if( is_array( $theme ) )
		$theme = reset( $theme );
	
	$align = ( $this->opt('navbar_alignment' ) ) ? $this->opt( 'navbar_alignment' ) : false;
	$hidesearch = ( $this->opt( 'navbar_hidesearch' ) ) ? $this->opt( 'navbar_hidesearch' ) : false;
	$menu = ( $this->opt( 'navbar_menu' ) ) ? $this->opt( 'navbar_menu' ) : null;
	$class[] = ( $this->opt( 'navbar_enable_hover' ) ) ? 'plnav_hover' : '';

	$pull = ( $align ) ? 'right' : 'left';
	$align_class = sprintf( 'pull-%s', $pull );

	$class[] = ( $theme ) ? sprintf( 'pl-color-%s', $theme ) : 'pl-color-black-trans';

	$classes = join(' ', $class);

	$brand = ( $this->opt( 'navbar_logo' ) || $this->opt( 'navbar_logo' ) != '' ) 
					? sprintf( '<img src="%s" alt="%s" />', $this->opt( 'navbar_logo' ), get_bloginfo( 'name' ) ) 
					: false;
					
    $navbartitle = $this->opt( 'navbar_title' );

	?>
	<div class="navbar fix <?php echo $classes; ?>">
	  <div class="navbar-inner <?php echo $content_width_class;?>">
	    <div class="navbar-content-pad fix">
		
	    	<?php if($navbartitle) printf( '<span class="navbar-title">%s</span>',$navbartitle ); ?>
	
	      <a href="javascript:void(0)" class="nav-btn nav-btn-navbar mm-toggle"> <?php _e('MENU', 'pagelines'); ?> <i class="icon icon-reorder"></i> </a>
			<?php 
				if( $brand ){
					printf( '<a class="plbrand" href="%s" title="%s">%s</a>',
						esc_url( home_url() ),
						esc_attr( get_bloginfo('name') ),
						apply_filters('navbar_brand', $brand)
					 );
				}
				pagelines_register_hook('pagelines_navbar_before_menu');
				?>
	      		<div class="nav-collapse collapse">
	       <?php 	if( ! $hidesearch ) {
	       				pagelines_register_hook('pagelines_navbar_before_search');
						pl_get_search_form();
						pagelines_register_hook('pagelines_navbar_after_search');

					}

					if ( is_array( wp_get_nav_menu_items( $menu ) ) || has_nav_menu( 'main_nav' ) ) {
					wp_nav_menu(
						array(
							'menu_class'		=> 'font-sub navline pldrop ' . $align_class,
							'menu'				=> $menu,
							'container'			=> null,
							'container_class'	=> '',
							'depth'				=> 3,
							'fallback_cb'		=> '',
							'theme_location'	=> 'main_nav',
						)
					);
					} else 
						pl_nav_fallback( 'navline pldrop '.$align_class );
					
	?>
				</div>
				<?php pagelines_register_hook('pagelines_navbar_after_menu'); ?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
<?php }

}
