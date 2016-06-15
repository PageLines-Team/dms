<?php
/*
	Section: QuickSlider
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A responsive slider that is easy to use and setup.
	Class Name: PageLinesQuickSlider
	Cloning: true
	Workswith: main, templates, sidebar_wrap
	Filter: slider
*/

/**
 * Main section class
 *
 * @package PageLines DMS
 * @author PageLines
 */
class PageLinesQuickSlider extends PageLinesSection {

	var $default_limit = 2;

	/**
	 * Load styles and scripts
	 */
	function section_styles(){
		wp_enqueue_script( 'flexslider', PL_JS . '/script.flexslider.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function old_section_head(){

		$animation = ($this->opt('quick_transition') == 'slide_v' || $this->opt('quick_transition') == 'slide_h') ? 'slide' : 'fade';
		$transfer = ($this->opt('quick_transition') == 'slide_v') ? 'vertical' : 'horizontal';

		$slideshow = ($this->opt('quick_slideshow')) ? 'true' : 'false';

		$clone_class = 'pl-clone' . $this->oset['clone_id'];

		$control_nav = (!$this->opt('quick_nav') || $this->opt('quick_nav') == 'both' || $this->opt('quick_nav') == 'control_only') ? 'true' : 'false';
		$direction_nav = (!$this->opt('quick_nav') || $this->opt('quick_nav') == 'both' || $this->opt('quick_nav') == 'arrow_only') ? 'true' : 'false';
		?>
<script>
jQuery(window).load(function() {
	// var theSlider = jQuery('<?php echo $this->prefix();?> .flexslider');
	// theSlider.flexslider({
	// 	controlsContainer: '.fs-nav-container',
	// 	animation: '<?php echo $animation;?>',
	// 	slideDirection: '<?php echo $transfer;?>',
	// 	slideshow: <?php echo $slideshow;?>,
	// 	directionNav: <?php echo $direction_nav;?>,
	// 	controlNav: <?php echo $control_nav;?>
	// });
});
</script>
<?php }

	/**
	* Section template.
	*/
   function section_template() {

	$control_nav = (!$this->opt('quick_nav') || $this->opt('quick_nav') == 'both' || $this->opt('quick_nav') == 'control_only') ? 'true' : 'false';

	$transition = ( $this->opt('quick_transition') == 'slide_h' ) ? 'slide' : 'fade';
	$animate = ( $this->opt('quick_slideshow') ) ? 'true' : 'false';

	$nav_class = ($control_nav) ? 'control-nav' : 'no-control-nav';
	?>
	<div class="flexwrap animated fadeIn <?php echo 'wrap-'.$nav_class;?>">
		<div class="fslider">
		<div class="flex-gallery flexslider <?php echo 'pl-clone' . $this->oset['clone_id'];?>" data-transition="<?php echo $transition;?>" data-animate="<?php echo $animate;?>">
		  <ul class="slides">

			<?php

			$item_array = $this->opt('quickslider_array');

			$format_upgrade_mapping = array(
				'image'			=> 'quick_image_%s',
				'text'			=> 'quick_text_%s',
				'link'			=> 'quick_link_%s',
				'location'		=> 'quick_text_location_%s',
			); 

			$item_array = $this->upgrade_to_array_format( 'quickslider_array', $item_array, $format_upgrade_mapping, $this->opt('quick_slides'));

			$output = '';

			$num = count( $item_array );

			if( is_array($item_array) ){
				
				foreach( $item_array as $item ){

					$the_image = pl_array_get( 'image', $item );
          
					
					if( $the_image ){

						$the_text =  pl_array_get( 'text', $item );
						$the_link =  pl_array_get( 'link', $item );
            $image_id = pl_get_image_id_from_src( $the_image );
            $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

						$text = ($the_text) ? sprintf('<p class="flex-caption">%s</p>', $the_text) : '';

						$img = sprintf('<img src="%s" alt="%s" />', $the_image, $image_alt );

						$slide = ( $the_link ) ? sprintf('<a href="%s">%s</a>', $the_link, $img ) : $img;
						
						$output .= sprintf('<li>%s %s</li>',$slide, $text);
					}

				}
				
			}
		

			if($output == '')
				$this->do_defaults();
			else 
				echo $output;


			?>
		  </ul>
		</div>
		</div>
	</div>

		<?php
	}

	function do_defaults(){

		printf(
			'<li><img src="%s" /></li><li><img src="%s" /></li><li><img src="%s" /></li>',
			$this->images.'/image3.jpg',
			$this->images.'/image1.jpg',
			$this->images.'/image2.jpg'
		);
	}

	function section_opts(){
		
			$options = array();

			$options[] = array(

				'title' => __( 'Slider Configuration', 'pagelines' ),
				'type'	=> 'multi',
				'opts'	=> array(
					array(
						'key'			=> 'quick_transition',
						'type' 			=> 'select',
						'default'		=> 'fade',
						'label' 	=> __( 'Select Transition Type', 'pagelines' ),
						'opts' => array(
							'fade' 			=> array('name' => __( 'Use Fading Transition', 'pagelines' ) ),
							'slide_h' 		=> array('name' => __( 'Use Slide/Horizontal Transition', 'pagelines' ) ),
						),
					), 
					array(
						'key'			=> 'quick_slideshow',
						'type' 			=> 'check',
						'label' 	=> __( 'Animate Slideshow Automatically?', 'pagelines' ),
					
					)
				)

			);


			$options[] = array(
				'key'		=> 'quickslider_array',
		    	'type'		=> 'accordion', 
				'col'		=> 2,
				'title'		=> __('Slides Setup', 'pagelines'), 
				'post_type'	=> __('Slide', 'pagelines'), 
				'opts'	=> array(
					array(
						'key'		=> 'image',
						'label' 	=> __( 'Slide Background Image', 'pagelines' ),
						'type'		=> 'image_upload',
						'sizelimit'	=> 2097152, // 2M
						'help'		=> __( 'For high resolution, 2000px wide x 800px tall images. (2MB Limit)', 'pagelines' )

					),

					array(
						'key'	=> 'text',
						'label'	=> __( 'Slide Text', 'pagelines' ),
						'type'			=> 'text'
					),
					array(
						'key'	=> 'link',
						'label'	=> __( 'Slide Link URL', 'pagelines' ),
						'type'			=> 'text'
					),
					


				)
		    );
		
		return $options;
		
	}
	
}
