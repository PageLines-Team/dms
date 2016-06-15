<?php
/*
	Section: PopShot
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: An animated image shelf that pops up the images when the user scrolls them on page.
	Class Name: PLPopShot
	Filter: gallery, full-width
*/


class PLPopShot extends PageLinesSection {

	function section_opts(){


		$options = array();
		
		$options[] = array(
				'type'	=> 'multi',
				'key'	=> 'popshot_config', 
				'title'	=> 'Popshot Configuration',
				'col'	=> 1,
				'opts'	=> array(
					
					array(
						'type'	=> 'select',
						'key'	=> 'popshot_format', 
						'label'	=> 'Select Image Style',
						'opts'	=> array(
							'shadow'	=> array( 'name' => 'Images w/ Drop Shadows'),
							'nostyle'	=> array( 'name' => 'No Style'),
							'frame'		=> array( 'name' => 'Images with Frame'),
							'browser'	=> array( 'name' => 'Faux Browser Wrap (for screenshots)'),
						), 
					),
					array(
						'type'	=> 'text_small',
						'key'	=> 'popshot_height', 
						'label'	=> 'Total Height of PopShot',
						'place'	=> '280px'
					),
				)
				
			);
		
		
		$options[] = array(
			'key'		=> 'popshot_array',
	    	'type'		=> 'accordion', 
			'col'		=> 2,
			'title'		=> __('PopShot Setup', 'pagelines'), 
			'post_type'	=> __('PopShot', 'pagelines'), 
			'opts'	=> array(
				array(
					'key'		=> 'image',
					'label' 	=> __( 'PopShot Image <span class="badge badge-mini badge-warning">REQUIRED</span>', 'pagelines' ),
					'type'		=> 'image_upload',
				),
				array(
					'key'	=> 'offset',
					'label'	=> __( 'Offset from center', 'pagelines' ),
					'type'	=> 'text_small',
					'place'	=> '-300px',
					'help'	=> __( 'Left edge offset from center. For example -100px  would move the left edge of the image 100 pixels left from center.', 'pagelines' ),
				),
				array(
					'key'	=> 'width',
					'label'	=> __( 'Maximum Width', 'pagelines' ),
					'type'	=> 'text_small',
					'place'	=> '600px',
					'help'	=> __( 'Max width of image.', 'pagelines' ),
				),
				array(
					'key'	=> 'height',
					'label'	=> __( 'Maximum Height', 'pagelines' ),
					'type'	=> 'text_small',
					'place'	=> '280px',
					'help'	=> __( 'Max height from bottom in pixels.', 'pagelines' ),
				),
				array(
					'key'	=> 'index',
					'label'	=> __( 'Z-Index (Stacking order)', 'pagelines' ),
					'type'	=> 'text_small',
					'place'	=> '10',
					'help'	=> __( 'Higher numbers will be placed higher in the stack.', 'pagelines' ),
				),
				
				

			)
	    );
	
		
		return $options;

	}


	function get_content( $array ){
		
		$out = '';
		
		$browser_buttons = '<div class="browser-btns"><span class="bbtn-red"></span><span class="bbtn-orange"></span><span class="bbtn-green"></span></div>';
		
		if( is_array( $array ) ){
			foreach( $array as $key => $item ){
				$image = pl_array_get( 'image', $item ); 
				$offset = pl_array_get( 'offset', $item, '-300px' );
				$index = pl_array_get( 'index', $item, '0' );
				$width = pl_array_get( 'width', $item, '600px' );
				$height = pl_array_get( 'height', $item, '250px' );

				if( $image ){
					$out .= sprintf(
						'<div class="pl-animation pla-from-bottom popshot popshot-%s" style="margin-left: %s; z-index: %s; max-width: %s; max-height: %s;">%s<img src="%s" alt="" /></div>', 
						$key, 
						$offset, 
						$index,
						$width,
						$height,
						$browser_buttons,
						$image
					);
				}

			}
		}
		
		
		return $out;
	}

   function section_template( ) { 
	
		$classes = array(); 
		$format = $this->opt('popshot_format');
		
		if( $format == 'shadow' )
			$classes[] = 'popshot-shadow';
		elseif( $format == 'frame' )	
			$classes[] = 'popshot-frame';
		elseif( $format == 'nostyle' )	
			$classes[] = 'popshot-nostyle';
		else
			$classes[] = 'popshot-browser';
		
		$array = $this->opt('popshot_array');
		
		$height = ( $this->opt('popshot_height') ) ? $this->opt('popshot_height') : '300px';
		
		
	?>
	
	<div class="popshot-wrap <?php echo join( ' ', $classes );?>" style="height: <?php echo $height;?>;">
		<div class="pl-animation-group">
			<?php
			
			$out = $this->get_content( $array ); 
			
			if( $out == '' ){
				$array = array(
					array(
						'image'				=> pl_default_image(),
						'width'				=> '600px',
						'offset'			=> '-300px',
						
					)
				);
				
				$out = $this->get_content( $array ); 
			} 
			
			echo $out;
			
			?>
		</div>
		<div class="shelf-shadow"></div>
	</div>

<?php }


}