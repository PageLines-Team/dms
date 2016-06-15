<?php
/*
	Section: RevSlider
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A professional and versatile slider section. Can be customized with several transitions and a large number of slides.
	Class Name: plRevSlider
	Filter: slider, full-width
*/


class plRevSlider extends PageLinesSection {

	var $default_limit = 3;

	function section_opts(){

		$options = array();

		$options[] = array(

			'title' => __( 'Slider Configuration', 'pagelines' ),
			'type'	=> 'multi',
			'opts'	=> array(
				array(
					'key'			=> 'revslider_delay',
					'type' 			=> 'text_small',
					'default'		=> 12000,
					'label' 		=> __( 'Time Per Slide in Milliseconds (e.g. 12000)', 'pagelines' ),
				),
				array(
					'key'			=> 'revslider_height',
					'type' 			=> 'text_small',
					'default'		=> 500,
					'label' 		=> __( 'Slider Height in Pixels (e.g. 500)', 'pagelines' ),
				),
				array(
					'key'			=> 'revslider_fullscreen',
					'type' 			=> 'check',
					'label' 		=> __( 'Set to full window height? (Overrides height setting)', 'pagelines' ),
					'help' 			=> __( 'This option will set the slider to the height of the users browser window on load, it will also resize as needed.', 'pagelines' ),
				)
			)

		);

		$options[] = array(
			'key'		=> 'revslider_array',
	    	'type'		=> 'accordion', 
			'col'		=> 2,
			'title'		=> __('Slides Setup', 'pagelines'), 
			'post_type'	=> __('Slide', 'pagelines'), 
			'opts'	=> array(
				array(
					'key'		=> 'background',
					'label' 	=> __( 'Slide Background Image <span class="badge badge-mini badge-warning">REQUIRED</span>', 'pagelines' ),
					'type'		=> 'image_upload',
					'sizelimit'	=> 2097152, // 2M
					'help'		=> __( 'For high resolution, 2000px wide x 800px tall images. (2MB Limit)', 'pagelines' )
					
				),
				array(
					'key'	=> 'title',
					'label'	=> __( 'Slide Title', 'pagelines' ),
					'type'	=> 'text'
				),
				array(
					'key'	=> 'text',
					'label'	=> __( 'Slide Text', 'pagelines' ),
					'type'			=> 'text'
				),
				array(
					'key'			=> 'element_color',
					'label' 		=> __( 'Text', 'pagelines' ),
					'type'			=> 'select',
					'opts'	=> array(
						'element-light'	=> array('name'=> 'Light Text and Elements'),
						'element-dark'		=> array('name'=> 'Dark Text and Elements'),
					)
				),
				array(
					'key'	=> 'location',
					'label'	=> __( 'Slide Text Location', 'pagelines' ),
					'type'			=> 'select',
					'opts'	=> array(
						'left-side'	=> array('name'=> 'Text On Left'),
						'right-side'	=> array('name'=> 'Text On Right'),
						'centered'		=> array('name'=> 'Centered'),
					)
				),
				
				array(
					'key'		=> 'transition',
					'label'		=> __( 'Slide Transition', 'pagelines' ),
					'type'		=> 'select_same',
					'opts'		=> $this->slider_transitions()
				),
				array(
					'key'	=> 'link',
					'label'	=> __( 'Primary Button', 'pagelines' ),
					'type'	=> 'button_link'
				),
				array(
					'key'	=> 'link_2',
					'label'	=> __( 'Secondary Button', 'pagelines' ),
					'type'	=> 'button_link'
				),
				
				array(
					'key'		=> 'video',
					'label' 	=> __( 'HTML5 Background Video', 'pagelines' ),
					'type'		=> 'media_select_video',
					
				),
				// array(
				// 					'key'		=> 'video_embed',
				// 					'label' 	=> __( 'Full Width Video', 'pagelines' ),
				// 					'type'		=> 'text',
				// 					
				// 				),
				array(
					'key'		=> 'extra',
					'label'		=> __( 'Slide Extra Captions', 'pagelines' ),
					'type'		=> 'textarea',
					'ref'		=> __( 'Add extra Revolution Slider caption markup here. Rev slider is based on Revolution Slider, a jQuery plugin. It supports a wide array of functionality including video embeds and additional transitions if you can handle HTML. Check out the <a href="http://www.orbis-ingenieria.com/code/documentation/documentation.html" target="_blank">docs here</a>.', 'pagelines' )
				),
				

			)
	    );


		return $options;
	}

	function slider_transitions(){

		$transitions = array(
			'boxslide',
			'boxfade',
			'curtain-1',
			'curtain-2',
			'curtain-3',
			'slideleft',
			'slideright',
			'slideup',
			'slidedown',
			'fade',
			'random',
			'slidehorizontal',
			'slidevertical',
			'papercut',
			'flyin',
			'turnoff',
			'cube',
			'3dcurtain-vertical',
			'3dcurtain-horizontal',
		);

		return $transitions;

	}
	function section_styles(){

		wp_enqueue_script( 'revolution-plugins', $this->base_url.'/rs-plugin/js/jquery.themepunch.plugins.min.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'revolution', $this->base_url.'/rs-plugin/js/jquery.themepunch.revolution.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_style(  'revolution', sprintf( '%s/rs-plugin/css/settings.css', $this->base_url ), null, pl_get_cache_key() );
		
		wp_enqueue_script( 'pagelines-slider', $this->base_url.'/pl.slider.js', array( 'jquery', 'revolution' ), pl_get_cache_key(), true );
		


	}
	
	function process_slides( $array ){
		
		$output = '';
		
		if( is_array($array) ){
			
			
			foreach( $array as $slide ){
				
				$the_bg = pl_array_get( 'background', $slide );
				$the_bg_id = pl_get_image_id_from_src( $the_bg );
				$extra = pl_array_get( 'extra', $slide ); 
				$video_embed = false;// pl_array_get( 'video_embed', $slide ); 
				 

				if( $the_bg || $extra || $video_embed ){
					
					$video = pl_array_get( 'video', $slide ); 
					$the_title = pl_array_get( 'title', $slide );
					$the_text = pl_array_get( 'text', $slide );
					
					$element_color = pl_array_get( 'element_color', $slide ); 
					
					$link = pl_array_get( 'link', $slide ); 
					$link_text = pl_array_get( 'link_text', $slide, __('More', 'pagelines') ); 
					$link_style = pl_array_get( 'link_style', $slide, 'btn-ol-white' ); 
					
					$link_2 = pl_array_get( 'link_2', $slide ); 
					$link_2_text = pl_array_get( 'link_2_text', $slide, __('Check it out', 'pagelines') ); 
					$link_2_style = pl_array_get( 'link_2_style', $slide, 'btn-info' );

					$the_location = pl_array_get( 'location', $slide ); 

					$transition = pl_array_get( 'transition', $slide, 'fade' ); 
					
					
					
					
					if($the_location == 'centered'){
						$the_x = 'center';
						$caption_class = 'centered sfb stb';
					} elseif ($the_location == 'right-side'){
						$the_x = '560';
						$caption_class = 'right-side sfr str';
					} else {
						$the_x =  '0';
						$caption_class = 'left-side sfl stl';
					}
					
					$the_bg = ( $the_bg ) ? $the_bg : $this->base_url.'/black-default-bg.png';
					$bg_alt = get_post_meta( $the_bg_id, '_wp_attachment_image_alt', true );

					$bg = sprintf('<img src="%s" alt="%s" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat">', $the_bg, $bg_alt);
					
					$the_text = ( $the_text != '' ) ? sprintf('<small>%s</small>', $the_text) : '';

					$content = sprintf('<h2 class="slider-text"><span class="slide-title">%s</span> %s</h2>', $the_title, $the_text);

					$link = ( $link ) ? sprintf('<a href="%s" class="btn btn-large slider-btn %s">%s</a>', $link, $link_style, $link_text) : false;
					$link_2 = ( $link_2 ) ? sprintf('<a href="%s" class="btn btn-large slider-btn %s">%s</a>', $link_2, $link_2_style, $link_2_text) : false;
					
					$buttons = ($link || $link_2) ? sprintf( '<div class="slider-buttons">%s %s</div>', $link, $link_2 ) : '';

				
					if( ! $extra ){
						
						$caption = sprintf(
								'<div class="caption slider-content %s" data-x="%s" data-y="center" data-speed="300" data-start="500" data-easing="easeOutExpo">%s %s</div>',
								$caption_class,
								$the_x,
								$content,
								$buttons
						);
						
					} else
						$caption = '';
						
					if( ! empty( $video) ){
						
						$video_caption = $this->get_video_caption( $video, pl_array_get( 'video_2', $slide ) );
						
					} else
						$video_caption = ''; 


					$output .= sprintf(
						'<li data-transition="%s" data-slotamount=	"10" class="%s bg-video-canvas">%s %s %s %s</li>', 
						
						$transition, 
						$element_color,
						$bg, 
						$video_caption, 
						$caption, 
						$extra
					);
				}
				
			
			}
		
		}
		
		return $output;
		
	}


	function render_slides(){
	
		$slide_array = $this->opt('revslider_array');
		
		$format_upgrade_mapping = array(
			'background'	=> 'revslider_bg_%s',
			'text'			=> 'revslider_text_%s',
			'link'			=> 'revslider_link_%s',
			'location'		=> 'revslider_text_location_%s',
			'transition'	=> 'revslider_transition_%s',
			'extra'			=> 'revslider_extra_%s'
		); 
		
		$slide_array = $this->upgrade_to_array_format( 'revslider_array', $slide_array, $format_upgrade_mapping, $this->opt('revslider_count'));
		
	
		$output = $this->process_slides( $slide_array );
		
		// Set defaults if not set.
		if( $output == '' ){
			$slide_array = array(
				array(
					'title'			=> 'RevSlider',
					'text'			=> 'Congrats! You have successfully installed this slider. Now just set it up.',
					'link'			=> 'http://www.pagelines.com/',
					'link_text'		=> 'Visit PageLines.com',
					'background'	=> PL_IMAGES . '/getting-started-mast-bg.jpg',
					'location'		=> 'centered'
				)
			);
			$output = $this->process_slides( $slide_array );
		}
		
		
				
	
		return $output;
	}
	
	function get_video_caption( $source1 = '', $source2 = ''){
		ob_start();
		?>
		<div class="bg-video-viewport" style="position: absolute; top: 0; left: 0;">
		 <video class="bg-video" width="100%" height="100%"
			poster='<?php echo pl_transparent_image(); ?>' data-setup="{}" loop autoplay muted style="">
			
			<?php echo pl_get_video_sources( array( $source1, $source2 ) ); ?>
		</video>
		</div>
		
		<?php
		
		return ob_get_clean();
	}



   function section_template( ) {

		$full = ( $this->opt('revslider_fullscreen') ) ? 'on' : 'off';

	?>
	<div class="pl-scroll-translate">
		<div class="pl-area-wrap">
			<div class="pl-loader pl-loader-element" style="">
				<div class="pl-loader-content pl-animation pl-appear"><div class="pl-spinner"></div></div>
			</div>
			<div class="revslider-container pl-slider-container" 
				data-videojs="<?php echo $this->base_url.'/rs-plugin/videojs/';?>"
				data-delay="<?php echo $this->opt('revslider_delay'); ?>"
				data-height="<?php echo $this->opt('revslider_height'); ?>" 
				data-fullscreen="<?php echo $full;?>">
				
				<div class="header-shadow"></div>
				<div class="pl-slider revslider-full " style="">
					<ul>
						<?php

							$slides = $this->render_slides();

							echo $slides;
					
						?>

					</ul>

					<div class="tp-bannertimer tp-top" style=""></div>
					
				</div>
			</div>
		</div>
	</div>
		<?php
	}


}
