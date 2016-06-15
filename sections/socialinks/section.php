<?php
/*
	Section: Socialinks
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A social icons listing.
	Class Name: PLSocialinks
	Filter: social
	Loading: active
*/


class PLSocialinks extends PageLinesSection {

	function section_opts(){

	
		$opts = array(
		
			array(
				'type'	=> 'multi',
				'key'	=> 'sl_config', 
				'title'	=> 'Text',
				'col'	=> 1,
				'opts'	=> array(
					array(
						'type'	=> 'text',
						'key'	=> 'sl_text', 
						'label'	=> 'Socialinks Text (e.g. copyright information)',
					),
					array(
						'type'	=> 'select',
						'key'	=> 'sl_align', 
						'label'	=> 'Alignment',
						'opts'	=> array(
							'sl-links-right'	=> array( 'name' => 'Social links on right'),
							'sl-links-left'	=> array( 'name' => 'Social links on left'),
						), 
					),
					array(
						'type'	=> 'check',
						'key'	=> 'sl_web_disable', 
						'label'	=> 'Disable "Built With" Icons (HTML5, CSS3, PageLines)',
						'scope'	=> 'global'
					),
					array(
						'key'	=> 'menu',
						'type'	=> 'select_menu',
						'label'	=> 'Select Menu',
					),
				)
				
			),
			array(
				'type'	=> 'multi',
				'key'	=> 'sl_urls', 
				'title'	=> 'Link URLs',
				
				'col'	=> 2,
				'opts'	=> pl_social_links_options()
				
			)
			

		);

		return $opts;

	}
	
	
	
	
   function section_template( $location = false ) {


		$icons = pl_social_icons(); 

		$target = "target='_blank'";
		
		$text = ( $this->opt('sl_text') ) ? $this->opt('sl_text') : sprintf('&copy; %s %s', date("Y"), get_bloginfo('name'));
		
		$align = ( $this->opt('sl_align') ) ? $this->opt('sl_align') : 'sl-links-right';
		
		$menu = ( $this->opt('menu') ) ? $this->opt('menu') : false;

	?>
	<div class="socialinks-wrap fix <?php echo $align;?>">
		
		<?php 
		
				$menu_args = array(
					'theme_location' => 'socialinks_nav',
					'menu' 			=> $menu,
					'menu_class'	=> 'inline-list pl-nav sl-nav', 
					'respond'		=> false
				);
			
				$nav = ($menu) ? pl_navigation( $menu_args ) : '';
			
				echo sprintf('<div class="sl-text"><span class="sl-copy">%s</span> %s</div>', $text, $nav); 
				
			
				echo pl_social_links();
		?>
	</div>
<?php }

}
