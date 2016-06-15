<?php
/*
	Section: Testimonials
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: List testimonials with quotes, links and gravatar images.
	Class Name: PLTestimonials
	Filter: social
*/

class PLTestimonials extends PageLinesSection {

	function section_styles(){

		wp_enqueue_script( 'pl-testimonials', $this->base_url . '/pl.testimonials.js', array( 'jquery' ), pl_get_cache_key(), true );
	}
	
	function section_opts(){
		
		$options = array();
		
		$options[] = array(
			'key'		=> 'pl_testimonial_config',
	    	'type'		=> 'multi', 
			'col'		=> 1,
			'title'		=> __('Testimonials Config', 'pagelines'), 
			'opts'	=> array(
				array(
					'key'	=> 'testimonials_mode',
					'label'	=> __( 'Mode', 'pagelines' ),
					'type'	=> 'select',
					'opts'	=> array(
						'default'	=> array('name' => 'Default "dot" navigation'),
						'avatar'	=> array('name' => 'Use author gravatars'),
					),
				),
			
				// array(
				// 					'key'	=> 'testimonials_height',
				// 					'label'	=> __( 'Use consistent height?', 'pagelines' ),
				// 					'type'	=> 'check'
				// 				),
				array(
					'key'	=> 'testimonials_disable_auto',
					'label'	=> __( 'Disable Automatic Transition?', 'pagelines' ),
					'type'	=> 'check'
				),
				array(
					'key'	=> 'testimonials_speed',
					'label'	=> __( 'Time per quote in ms (ex: 10000, auto transition only) ', 'pagelines' ),
					'type'	=> 'text_small'
				),
				

			)
	    );
		
		$options[] = array(
			'key'		=> 'pl_testimonial_array',
	    	'type'		=> 'accordion', 
			'col'		=> 2,
			'title'		=> __('Testimonials Setup', 'pagelines'), 
			'post_type'	=> __('Testimonial', 'pagelines'), 
			'opts'	=> array(
				array(
					'key'	=> 'text',
					'label'	=> __( 'Text', 'pagelines' ),
					'type'			=> 'text'
				),
				array(
					'key'	=> 'cite',
					'label'	=> __( 'Citation', 'pagelines' ),
					'type'	=> 'text'
				),
				array(
					'key'	=> 'email',
					'label'	=> __( 'Email (Gravatar Mode Only)', 'pagelines' ),
					'type'	=> 'text'
				),
				

			)
	    );

		return $options;

	}

	function get_testimonials( $array ){
		
		ob_start(); 
			$count = 1;
			foreach( $array as $item ){
			
				$text = pl_array_get( 'text', $item ); 
				$cite = pl_array_get( 'cite', $item );
				$email = pl_array_get( 'email', $item );
				
				$cite = ( $cite ) ? sprintf('<cite>%s</cite>', $cite) : '';
				
				if( $text == '')
					continue;
					
				$avatar = get_avatar( $email );
				$avatar_url = pl_get_avatar_url($avatar);
				$avatar_data = sprintf('data-avatar="%s"', $avatar_url);
			?>
	  		<div class="the-testimonial" <?php echo $avatar_data;?> >
			    <blockquote>
			    	<p data-sync="<?php printf('pl_testimonial_array_item%s_text', $count);?>"><?php echo $text; ?></p>
			    	
			    </blockquote>
				<?php echo $cite; ?>
			</div>
		<?php 
			$count++; 
		}
	
		return ob_get_clean();
	}
	
	function defaults(){
		$array = array(
			array(
				'text'	=> 'The difference between stupidity and genius is that genius has its limits.',
				'cite'	=> 'Albert Einstein, <a href="http://www.pagelines.com">PageLines</a>'
			),
			array(
				'text'	=> 'Be a yardstick of quality. Some people are not used to an environment where excellence is expected.',
				'cite'	=> 'Steve Jobs, <a href="http://www.pagelines.com">PageLines</a>'
			),
			array(
				'text'	=> 'Any product that needs a manual to work is broken.',
				'cite'	=> 'Elon Musk, <a href="http://www.pagelines.com">PageLines</a>'
			),
		);
		
		return $array;
	}

	function section_template() {
	
		$mode = ( $this->opt('testimonials_mode')) ? $this->opt('testimonials_mode') : 'default';
		$height = ( $this->opt('testimonials_height')) ? 'true' : 'false';
		$auto = ( $this->opt('testimonials_disable_auto')) ? 'false' : 'true';
		$speed = ( $this->opt('testimonials_speed')) ? $this->opt('testimonials_speed') : '10000';
		
		

		?>
		<div class="pl-testimonials-container" 
			data-mode="<?php echo $mode; ?>" 
			data-height="<?php echo $height; ?>" 
			data-auto="<?php echo $auto; ?>" 
			data-speed="<?php echo $speed; ?>" 
		>
		  <div class="pl-testimonials">
			
			<?php 
			
				$t = $this->get_testimonials( $this->opt('pl_testimonial_array', array( 'default' => $this->defaults() ) ) ); 
				
				if( $t == '' )
					echo $this->get_testimonials( $this->defaults() ); 
				else
					echo $t;
				
				?>
		  </div>
		</div>
	<?php
	}
}
