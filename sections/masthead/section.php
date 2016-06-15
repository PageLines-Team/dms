<?php
/*
	Section: Masthead
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A responsive full width splash and text area. Great for getting big ideas across quickly.
	Class Name: PLMasthead
	Edition: pro
	Workswith: templates, main, header, morefoot
	Filter: component
	Loading: active
*/

/**
 * Main section class
 *
 * @package PageLines DMS
 * @author PageLines
 */
class PLMasthead extends PageLinesSection {

    var $tabID = 'masthead_meta';

    function section_head() {

    	if($this->opt('pagelines_masthead_html')) { ?>
	    		<script>
	    		  jQuery(document).ready(function(){
				    jQuery(".video-splash").fitVids();
				  });
	    		</script>
	    	<?php }
    }
	
	function section_opts(  ){

		$options = array(
				array(
					'key'	=> 'pagelines_masthead_splash_multi',
					'type' 	=> 'multi',
					'title' => __('Masthead Splash Options','pagelines'),
					'opts'	=> array(
						array(
							'key'			=> 'pagelines_masthead_img',
							'type' 			=> 'image_upload',
							'imagepreview' 	=> '270',
							'has_alt'		=> true,
							'label' 	=> __( 'Upload custom image', 'pagelines' ),
						),
						array(
							'key'			=> 'pagelines_masthead_html',
							'type' 			=> 'textarea',
							'label' 	=> __( 'Masthead Video (optional, to be used instead of image)', 'pagelines' ),
						),
						array(
							'key'			=> 'masthead_html_width',
							'type' 			=> 'text',
							'label' 	=> __( 'Maximum width of splash in px (default is full width)', 'pagelines' ),
						),
					),
					'help'                   => __( 'Upload an image to serve as a splash image, or use an embed code for full width video.', 'pagelines' ),
				),
				array(
						'col'				=> 2,
						'key'				=> 'pagelines_masthead_text',
						'type' 				=> 'multi',
						'label' 		=> __( 'Masthead Text', 'pagelines' ),
						'title' 			=> $this->name . __( ' Text', 'pagelines' ),
						'opts'	=> array(
							array(
								'key'		=> 'pagelines_masthead_title',
								'type'		=> 'text',
								'label'		=> __( 'Title', 'pagelines' ), 
							),
							array(
								'key'	=> 'pagelines_masthead_tagline',
								'type'	=> 'text',
								'label'	=>__( 'Tagline', 'pagelines' ), 
							)
						),

				),
		); 
			
		for($i = 1; $i <= 2; $i++){

			$options[] = array(
				'key'		=> 'masthead_button_multi_'.$i,
				'type'		=> 'multi',
				'col'		=> 3,
				'title'		=> __('Masthead Action Button '.$i, 'pagelines'),
				'opts'	=> array(
					array(
						'key'		=> 'masthead_button_link_'.$i,
						'type' => 'text',
						'label' => __( 'Enter the link destination (URL - Required)', 'pagelines' ),

					),
					array(
						'key'		=> 'masthead_button_text_'.$i,
						'type' 			=> 'text',
						'label' 	=> __( 'Masthead Button Text', 'pagelines' ),
					 ),

					array(
						'key'		=> 'masthead_button_target_'.$i,
						'type'			=> 'check',
						'default'		=> false,
						'label'	=> __( 'Open link in new window.', 'pagelines' ),
					),
					array(
						'key'		=> 'masthead_button_theme_'.$i,
						'type'			=> 'select_button',
						'default'		=> false,
						'label'		=> __( 'Select Button Color', 'pagelines' ),
					
					),
				)
			);

		}
			
				
		$options[] = array(
					'col'		=> 2,
					'key'		=> 'masthead_menu',
					'type' 			=> 'select_menu',
					'title'			=> __( 'Masthead Menu', 'pagelines' ),
					'inputlabel' 	=> __( 'Select Masthead Menu', 'pagelines' ),
				); 
		$options[] = array(
					'col'				=> 2,
					'key'		=> 'masthead_meta',
					'type' 			=> 'text',
					'title'			=> __( 'Masthead Meta', 'pagelines' ),
					'inputlabel' 	=> __( 'Enter Masthead Meta Text', 'pagelines' ),
				); 

		

		return $options;
	}
	
	

	/**
	* Section template.
	*/
   function section_template() {
   		$mast_title = $this->opt('pagelines_masthead_title');
   		$mast_img = $this->opt('pagelines_masthead_img' );
		$mast_tag = $this->opt('pagelines_masthead_tagline');
		$mast_menu = $this->opt( 'masthead_menu', array( 'default' => null ) );
		$masthead_meta = $this->opt('masthead_meta');

		$masthtmlwidth = ($this->opt('masthead_html_width')) ? $this->opt('masthead_html_width').'px' : '';

		$mast_title = (!$mast_title) ? 'Hello.' : $mast_title;

		$classes = ($mast_img) ? 'with-splash' : '';
		
	?>

	<header class="jumbotron masthead <?php echo $classes;?>">
	  	<?php
	
			$theimg = $this->image( 'pagelines_masthead_img', false, array( 'masthead-img' ) );
	  		$masthtml = $this->opt('pagelines_masthead_html');

	  		if($mast_img)
	  			printf('<div class="splash" style="max-width:%s;margin:0 auto;">%s</div>',$masthtmlwidth,$theimg);

	  		if($masthtml)
	  			printf('<div class="video-splash" style="max-width:%s;margin:0 auto;">%s</div>',$masthtmlwidth,$masthtml);

	  	?>

	  <div class="inner">
	  	<?php

	  		printf('<h1 class="masthead-title" data-sync="pagelines_masthead_title">%s</h1>',$mast_title);

			printf('<p class="masthead-tag" data-sync="pagelines_masthead_tagline">%s</p>',$mast_tag);

	  	?>

		<?php if( $this->opt('masthead_button_link_1') || $this->opt('masthead_button_link_2') ): ?>
	    <p class="download-info">

	    <?php
			for ($i = 1; $i <= 2; $i++){
				$btn_link = $this->opt('masthead_button_link_'.$i); // Flag

				$btn_text = $this->opt('masthead_button_text_'.$i, array( 'default' => __('Start Here', 'pagelines') ) );

				$target = ( $this->opt( 'masthead_button_target_'.$i ) ) ? 'target="_blank"' : '';

				$btheme = $this->opt( 'masthead_button_theme_' . $i, array( 'default' => '' ) );

				if($btn_link)
					printf('<a %s class="btn %s btn-large" href="%s" data-sync="masthead_button_text_%s">%s</a> ', $target, $btheme, $btn_link, $i, $btn_text);
			}

	    ?>
		</p>
		<?php endif; ?>
	  </div>
	<?php if( is_array( wp_get_nav_menu_items( $mast_menu ) ) || $masthead_meta ): ?>
		<div class="mastlinks">
			<?php
			if( is_array( wp_get_nav_menu_items( $mast_menu ) ) )
				wp_nav_menu(
					array(
						'menu_class'  => 'quick-links',
						'menu' => $mast_menu,
						'container' => null,
						'container_class' => '',
						'depth' => 1,
						'fallback_cb'=>''
					)
				);


			if($masthead_meta)
				printf( '<div class="quick-links mastmeta">%s</div>', do_shortcode($masthead_meta) );

			?>
		</div>
	<?php endif; ?>
	</header>
		<?php
	}
}