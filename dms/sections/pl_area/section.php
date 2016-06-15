<?php
/*
	Section: Canvas Area
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Creates a full width area with a nested content width region for placing sections and columns.
	Class Name: PLSectionArea
	Filter: full-width
	Loading: active
*/


class PLSectionArea extends PageLinesSection {

	function section_head(){

		// Upgrade area background options from 1.1 > 1.2
		$upgrade_options = array(
			'pl_area_image'		=> $this->id.'_background',
			'pl_area_bg_repeat'	=> $this->id.'_repeat',
			'pl_area_bg'		=> $this->id.'_theme',
			'pl_area_bg_color_enable'	=> $this->id.'_color_enable',
			'pl_area_bg_color'	=> $this->id.'_color',
		);

		$this->upgrade_section_options( $upgrade_options );

	}

	function section_opts(){

		$options = array();

		$options[] = array(

			'key'			=> 'pl_area_pad_selects',
			'type' 			=> 'multi',
			'label' 	=> __( 'Set Area Padding', 'pagelines' ),
			'opts'	=> array(
				array(
					'key'			=> 'pl_area_pad',
					'type' 			=> 'count_select_same',
					'count_start'	=> 0,
					'count_number'	=> 200,
					'count_mult'	=> 10,
					'suffix'		=> 'px',
					'label' 	=> __( 'Area Padding (px)', 'pagelines' ),
				),
				array(
					'key'			=> 'pl_area_pad_bottom',
					'type' 			=> 'count_select_same',
					'count_start'	=> 0,
					'count_number'	=> 200,
					'count_mult'	=> 10,
					'suffix'		=> 'px',
					'label' 	=> __( 'Area Padding Bottom (if different)', 'pagelines' ),
				),
				array(
					'key'			=> 'pl_area_height',
					'type' 			=> 'text',
					'label' 	=> __( 'Area Minimum Height (px)', 'pagelines' ),
				),
			),


		);

		$options[] = array(

			'key'			=> 'pl_area_styling',
			'type' 			=> 'multi',
			'col'			=> 3,
			'label' 	=> __( 'Area Styling', 'pagelines' ),
			'opts'	=> array(
				array(
					'key'			=> 'pl_area_parallax',
					'type' 			=> 'select',
					'opts'			=> array(
						''						=> array('name' => "No Scroll Effect"),
						'pl-parallax'			=> array('name' => "Parallaxed Background Image"),
						'pl-parallax-alt'			=> array('name' => "Alternative Parallaxed Background Image"),
						'pl-parallax-new'			=> array('name' => "Improved Parallaxed Background Image"),
						'pl-scroll-translate'	=> array('name' => "Translate Content on Scroll"),
						'pl-window-height'		=> array('name' => "Set to height of window"),
					),
					'label' 	=> __( 'Scrolling effects and sizing.', 'pagelines' ),
				),
				array(
					'label' => 'Parallax speed (Improved Parallax)',
					'key'	=> 'parallax_speed',
					'type' => 'select',
					'opts' => array(
						'1'			=> array('name' => "0.1"),
						'2'			=> array('name' => "0.2 (default)"),
						'3'			=> array('name' => "0.3"),
						'4'			=> array('name' => "0.4"),
						'5'			=> array('name' => "0.5"),
					)
				),
				array(
					'label' => 'Parallax Direction (Improved Parallax)',
					'key'	=> 'parallax_direction',
					'type' => 'select',
					'opts' => array(
						'vertical'			=> array('name' => "Vertical (default)"),
						'horizontal'		=> array('name' => "Horizontal")
					)
				),
				array(
					'label' => 'Parallax layer (Improved Parallax)',
					'key'	=> 'parallax_layer',
					'type' => 'select',
					'opts' => array(
						'background'			=> array('name' => "Background (default)"),
						'foreground'		=> array('name' => "Foreground")
					)
				),

			),
			'help'	=> 'Improved Parallax has been tested on chrome, safari and firefox. It will trigger on screens larger than 1024px'

		);
		return $options;
	}



	function before_section_template( $location = '' ) {

		$this->alt_standard_title = true;

		$scroll_effect = $this->opt('pl_area_parallax');

		$this->wrapper_classes['scroll'] = $scroll_effect;

		if( 'pl-parallax-new' == $this->opt( 'pl_area_parallax' ) ) {
			$this->wrapper_classes['paraspeed'] = sprintf( 'paraspeed-%s', $this->opt( 'parallax_speed', array( 'default' => '2' ) ) );
			$this->wrapper_classes['paradirection'] = sprintf( 'paradirection-%s', $this->opt( 'parallax_direction', array( 'default' => 'vertical' ) ) );
			$this->wrapper_classes['paralayer'] = sprintf( 'paralayer-%s', $this->opt( 'parallax_layer', array( 'default' => 'background' ) ) );
		}
	}



	function section_template( ) {

		$section_output = (!$this->active_loading) ? render_nested_sections( $this->meta['content'], 1) : '';

		$style = '';
		$inner_style = '';


		// Use alt mode for this
		$title = ( $this->opt('pl_standard_title') ) ? sprintf( '<h2 class="pl-section-title pla-from-top subtle pl-animation">%s</h2>', $this->opt('pl_standard_title') ) : '';

		$inner_style .= ($this->opt('pl_area_height')) ? sprintf('min-height: %spx;', $this->opt('pl_area_height')) : '';
		$inner_classes = 'pl-inner area-region pl-sortable-area editor-row';

		$classes = '';

		// If there is no output, there should be no padding or else the empty area will have height.
		if ( $section_output || $title != '' ) {

			// global
			$default_padding = pl_setting('section_area_default_pad', array('default' => '20'));
			// opt
			$padding		= rtrim( $this->opt('pl_area_pad',			array( 'default' => $default_padding ) ), 'px' );
			$padding_bottom	= rtrim( $this->opt('pl_area_pad_bottom',	array( 'default' => $padding ) ), 'px' );

			$style .= sprintf('padding-top: %spx; padding-bottom: %spx;',
				$padding,
				$padding_bottom
			);

			$content_class = $padding ? 'nested-section-area' : '';
			$buffer = pl_draft_mode() ? sprintf('<div class="pl-sortable pl-sortable-buffer span12 offset0"></div>') : '';
			$section_output = $buffer . $section_output . $buffer;
		}
		else {
			$pad_css = '';
			$content_class = '';
		}
	?>
	<div class="pl-area-wrap <?php echo $classes;?>" style="<?php echo $style;?>">

		<div class="pl-content <?php echo $content_class;?>">
			<?php echo $title; ?>
			<div class="<?php echo apply_filters( 'pl-area-inner-classes', $inner_classes, $this->meta ); ?>" style="<?php echo apply_filters( 'pl-area-inner-style', $inner_style, $this->meta );?>">
				<?php  echo $section_output; ?>
			</div>
		</div>
	</div>
	<?php
	}


}
