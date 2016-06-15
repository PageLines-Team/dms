<?php
/*
	Section: PageHeader
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A dynamic page header area that supports image background and sub navigation.
	Class Name: PLPageHeader
	Filter: full-width, component
	Loading: active
*/

class PLPageHeader extends PageLinesSection {

	
	function section_opts(){
		$options = array();
		
	//	$options['config']	= array();
		$options['config'] = array(
			'title' => __( 'Header Config', 'pagelines' ),
			'type'	=> 'multi',
			'opts'	=> array(
				
				array(
					'key'			=> 'ph_format',
					'label' 		=> __( 'Format', 'pagelines' ),
					'type'			=> 'select',
					'opts'	=> array(
						'format-standard'	=> array('name'=> 'Text On Left'),
						'format-center'		=> array('name'=> 'Centered'),
					)
				),
				// array(
				// 	'key'			=> 'ph_mode',
				// 	'label' 		=> __( 'Mode', 'pagelines' ),
				// 	'type'			=> 'select',
				// 	'opts'	=> array(
				// 		'nav'	=> array('name'=> 'Use Nav Menu'),
				// 		'links'	=> array('name'=> 'Use Link Buttons'),
				// 	)
				// ),
				array(
					'key'			=> 'ph_pad_class',
					'type' 			=> 'select_padding',
					'label' 		=> __( 'Header Top/Bottom Padding in px', 'pagelines' ),
				),
			)
		);
		$options['content'] = array(
			'title' => __( 'Header Text', 'pagelines' ),
			'type'	=> 'multi',
			'col'	=> 2,
			'opts'	=> array(
				array(
					'key'			=> 'ph_header',
					'type' 			=> 'text',
					'label' 		=> __( 'Header Text', 'pagelines' ),
				),
				array(
					'key'			=> 'ph_sub',
					'type' 			=> 'text',
					'label' 		=> __( 'Header Sub Text', 'pagelines' ),
				),
			)
		);
		
		$options['meta'] = array(
				'title' => __( 'Header Meta', 'pagelines' ),
				'type'	=> 'multi',
				'col'	=> 2,
				'opts'	=> array(
					// array(
					// 	'key'			=> 'ph_menu',
					// 	'type' 			=> 'select_menu',
					// 	'label' 		=> __( 'Header Menu (menu mode only)', 'pagelines' ),
					// ),
					array(
						'key'			=> 'ph_link1',
						'type' 			=> 'button_link',
						'label' 		=> __( 'Header Link 1 (link mode only)', 'pagelines' ),
					),
					array(
						'key'			=> 'ph_link2',
						'type' 			=> 'button_link',
						'label' 		=> __( 'Header Link 2 (link mode only)', 'pagelines' ),
					),
				)			
		); 

		return $options;

	}
	
	function before_section_template( $location = '' ) {

		$this->wrapper_classes['special'] = 'pl-scroll-translate'; 

	}
	

	function section_template() {
		
		global $post;
		
		$title = ( $this->opt('ph_header') ) ? $this->opt('ph_header') : pl_smart_page_title();
		$text = ( $this->opt('ph_sub') ) ? $this->opt('ph_sub') : pl_smart_page_subtitle();
		
		$mode = ( $this->opt('ph_mode') ) ? $this->opt('ph_mode') : 'link';
		
		$container_class = array();
		$container_class[] = $this->opt('ph_format'); 
		$container_class[] = 'vpad-' . $this->opt('ph_pad_class'); 
		
		
		?>
		<div class="pl-ph-container pl-area-wrap  pl-animation pl-slidedown fix <?php echo join(' ', $container_class);?>" >
			<div class="pl-end-height pl-content fix pl-centerer" style="">
				<div class="ph-text">
					<h2 class="ph-head" data-sync="ph_header"><?php echo $title; ?></h2>
					<div class="ph-sub" ata-sync="ph_sub"><?php echo $text; ?></div>
				</div>
				<div class="ph-meta pl-centered">
					<?php 
					
						echo pl_get_button_link('ph_link1', $this); 
						echo pl_get_button_link('ph_link2', $this); 
					
						
					 ?>
				</div>
			</div>
		</div>
	<?php

	}
}
