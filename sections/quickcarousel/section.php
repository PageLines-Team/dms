<?php
/*
	Section: QuickCarousel
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A fast way to create an animated image carousel.
	Class Name: PLQuickCarousel
	Filter: gallery, dual-width
*/


class PLQuickCarousel extends PageLinesSection {

	function section_styles(){
		wp_enqueue_script( 'caroufredsel', $this->base_url.'/caroufredsel.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'pl-quickcarousel', $this->base_url.'/pl.quickcarousel.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function section_opts(){


		$options = array();

		$options[] = array(
				'type'	=> 'multi',
				'key'	=> 'config',
				'title'	=> 'Config',
				'col'	=> 1,
				'opts'	=> array(
					array(
						'key'			=> 'cols',
						'type' 			=> 'count_select',
						'count_start'	=> 1,
						'count_number'	=> 6,
						'default'		=> '3',
						'label' 	=> __( 'Number of Columns for Each Item (12 Col Grid)', 'pagelines' ),
					),
					array(
						'type'	=> 'text_small',
						'key'	=> 'max',
						'label'	=> 'Max Items In View',
						'default'	=> 6
					),
					array(
						'type' 			=> 'check',
						'key'			=> 'quickc_animation_disable',
						'label' 		=> __( 'Disable Animation', 'pagelines' ),
						'help' 			=> __( 'Disable the animation on pageload?.', 'pagelines' ),
					),
					array(
						'key'			=> 'vpad',
						'type' 			=> 'select_padding',
						'label' 		=> __( 'Vertical Top/Bottom Padding', 'pagelines' ),
					),
					array(
						'key'			=> 'hpad',
						'type' 			=> 'select_padding',
						'label' 		=> __( 'Horizontal Top/Bottom Padding', 'pagelines' ),
					),
				)

			);


		$options[] = array(
			'key'		=> 'array',
	    	'type'		=> 'accordion',
			'col'		=> 2,
			'opts_cnt'	=> 6,
			'title'		=> __('Image Setup', 'pagelines'),
			'post_type'	=> __('Image', 'pagelines'),
			'opts'	=> array(
				array(
					'key'		=> 'image',
					'label' 	=> __( 'Carousel Image <span class="badge badge-mini badge-warning">REQUIRED</span>', 'pagelines' ),
					'type'		=> 'image_upload',
				),
				array(
					'key'	=> 'link',
					'label'	=> __( 'Image Link', 'pagelines' ),
					'type'	=> 'text',
				),

			)
	    );


		return $options;

	}


	function get_content( $array ){

		$cols = ($this->opt('cols')) ? $this->opt('cols') : 2;
		$hpad = ($this->opt('hpad')) ? 'hpad-' . $this->opt('hpad') : '';
		$vpad = ($this->opt('vpad')) ? 'vpad-' . $this->opt('vpad') : '';
		$animation = 'pl-animation pla-from-bottom';
		$disable_animation = $this->opt( 'quickc_animation_disable', array( 'default' => false ) );
		if( $disable_animation ) {
			$animation = 'pl-animation pla-none';
		}
		$out = '';

		if( is_array( $array ) ){
			foreach( $array as $key => $item ){
				$image = pl_array_get( 'image', $item );
				$image_id = pl_get_image_id_from_src( $image );

				$link = pl_array_get( 'link', $item );

				if( $image ){

					$image_meta = wp_get_attachment_image_src( $image_id, 'aspect-thumb' );

					$image_url = (isset($image_meta[0])) ? $image_meta[0] : $image;
					
					$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					
					
					
					$image_out = ( $link ) ? sprintf( '<a href="%s"><img src="%s" alt="%s" /></a>', $link, $image_url, $image_alt) : sprintf('<img src="%s" alt="%s" />', $image_url, $image_alt );

					$out .= sprintf(
						'<div class="%s carousel-item span%s" style=""><div class="carousel-item-pad %s %s">%s</div></div>',
						$animation,
						$cols,
						$hpad,
						$vpad,
						$image_out
					);
				}

			}
		}


		return $out;
	}

   function section_template( ) {

		$classes = array();

		$max = ($this->opt('max')) ? $this->opt('max') : 6;

		$format = $this->opt('format');
		$classes[] = 'format-'.$format;

		$array = $this->opt('array');

	?>

	<div class="pl-quickcarousel <?php echo join( ' ', $classes );?> pl-animation-group row-no-response row-closed" data-max="<?php echo $max;?>">

		<?php

		$out = $this->get_content( $array );

		if( $out == '' ){
			$array = array(
				array( 'image'		=> pl_default_image() ),
				array( 'image'		=> pl_default_image() ),
				array( 'image'		=> pl_default_image() ),
				array( 'image'		=> pl_default_image() ),
				array( 'image'		=> pl_default_image() ),
				array( 'image'		=> pl_default_image() ),
				array( 'image'		=> pl_default_image() ),
			);

			$out = $this->get_content( $array );
		}

		echo $out;

		?>
	</div>



<?php }


}
