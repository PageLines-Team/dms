<?php
/*
	Section: iCallout
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A quick call to action for your users
	Class Name: PLICallout
	Edition: pro
	Filter: component
	Loading: active
*/

class PLICallout extends PageLinesSection {

	var $tabID = 'highlight_meta';


	function section_opts(){
		$opts = array(
			array(
				'type' 			=> 'select',
				'title' 		=> 'Select Format',
				'key'			=> 'icallout_format',
				'label' 		=> __( 'Callout Format', 'pagelines' ),
				'opts'=> array(
					'top'			=> array( 'name' => __( 'Text on top of button', 'pagelines' ) ),
					'inline'	 	=> array( 'name' => __( 'Text/Button Inline', 'pagelines' ) )
				),
			),
			array(
				'type' 			=> 'multi',
				'col'			=> 2,
				'title' 		=> __( 'Callout Text', 'pagelines' ),
				'opts'	=> array(
					array(
						'key'			=> 'icallout_text',
						'version' 		=> 'pro',
						'type' 			=> 'text',
						'label' 		=> __( 'Callout Text', 'pagelines' ),
					),
					array(
						'type' 			=> 'select',
						'key'			=> 'icallout_text_wrap',
						'label' 		=> __( 'Callout Text Wrapper', 'pagelines' ),
						'default'		=> 'h2',
						'opts'			=> array(
							'h1'			=> array('name' => '&lt;h1&gt;'),
							'h2'			=> array('name' => '&lt;h2&gt;  (default)'),
							'h3'			=> array('name' => '&lt;h3&gt;'),
							'h4'			=> array('name' => '&lt;h4&gt;'),
							'h5'			=> array('name' => '&lt;h5&gt;'),
						)
					),

				)
			),
			array(
				'type' 			=> 'multi',
				'title' 		=> 'Link/Button',
				'col'			=> 3,
				'opts'	=> array(

					 array(
						'key'			=> 'icallout_link',
						'type' 			=> 'text',
						'label'			=> __( 'URL', 'pagelines' )
					),
					array(
						'key'			=> 'icallout_target',
						'type'			=> 'check',
						'default'		=> false,
						'label'			=> __( 'Open link in new window', 'pagelines' )
					),
					array(
						'key'			=> 'icallout_link_text',
						'type' 			=> 'text',
						'label'			=> __( 'Text on Button', 'pagelines' )
					),
					array(
						'key'			=> 'icallout_btn_theme',
						'type' 			=> 'select_button',
						'label'			=> __( 'Button Color', 'pagelines' ),
					),

				)
			)

		);

		return $opts;

	}

	function section_template() {

		$text = $this->opt('icallout_text');
		$format = ( $this->opt('icallout_format') ) ? 'format-'.$this->opt('icallout_format') : 'format-inline';
		$link = $this->opt('icallout_link');
		$link_target = ( $this->opt( 'icallout_target' ) ) ? ' target="_blank"': '';
		$theme = $this->opt('icallout_btn_theme', array( 'default' => 'btn-primary' ) );
		$link_text = $this->opt('icallout_link_text', array( 'default' => 'Learn More <i class="icon icon-angle-right"></i>' ) );
		$text_wrap = ( '' != $this->opt( 'icallout_text_wrap' ) ) ? $this->opt( 'icallout_text_wrap' ) : 'h2';

		if(!$text && !$link){
			$text = __("Call to action!", 'pagelines');
		}

		$callout_head = sprintf( '<%s class="icallout-head" data-sync="icallout_text">%s</%s>', $text_wrap, $text, $text_wrap );

		?>
		<div class="icallout-container <?php echo $format;?>">
			<?php echo $callout_head; ?>
			<a class="icallout-action btn <?php echo $theme;?> btn-large" href="<?php echo $link; ?>" <?php if($link_target){ ?> target="_blank" <?php } ?>  data-sync="icallout_link_text"><?php echo $link_text; ?></a>

		</div>
	<?php

	}
}
