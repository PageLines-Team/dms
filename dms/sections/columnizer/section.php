<?php
/*
	Section: Columnizer
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Place this section wherever you like and use WordPress widgets and a desired number of columns, to create an instant columnized widget section.
	Class Name: PageLinesColumnizer
	Filter: widgetized
	Loading: active
*/

class PageLinesColumnizer extends PageLinesSection {

	function section_persistent(){

	}

	function section_head(){



	}

	function change_markup( $params ){

		$cols = $this->opt( 'columnizer_cols', array( 'default' => 3 ) );


		$params[0]['before_widget'] = sprintf('<div class="span%s">%s', $cols, $params[0]['before_widget']);
		$params[0]['after_widget'] = sprintf('%s</div>', $params[0]['after_widget']);

		if($this->width == 0)
			$params[0]['before_widget'] = sprintf('<div class="columnizer row fix">%s', $params[0]['before_widget']);

		$this->width += $cols;
		if($this->width >= 12 || $this->count == $this->total_widgets){
			$this->width = 0;
			$params[0]['after_widget'] = sprintf('%s</div>', $params[0]['after_widget']);
		}

		$this->count++;

		return $params;
	}

	function section_opts(){



		$opts = array(
			array(
				'title' => __( 'Columnizer Configuration', 'pagelines' ),
				'type'	=> 'multi',
				'opts'	=> array(
						array(
							'key'	=> 'columnizer_area',
							'type'	=> 'select',
							'opts'	=> get_sidebar_select(),
							'title'	=> __( 'Select Widgetized Area', 'pagelines' ),
							'label'		=>	__( 'Select widgetized area', 'pagelines' ),
							'help'		=> __( "Select the widgetized area you would like to use with this instance.", 'pagelines' ),
						),
						array(
							'key'			=> 'columnizer_cols',
							'type' 			=> 'count_select',
							'count_start'	=> 1,
							'count_number'	=> 12,
							'default'		=> '3',
							'label' 		=> __( 'Number of Grid Columns for Each Widget (12 Col Grid)', 'pagelines' ),
						),
					),
			),

			array(
				'key'	=> 'columnizer_help',
				'col'	=> 2,
				'type'	=> 'link',
				'url'	=> admin_url( 'widgets.php' ),
				'title'	=> __( 'Widgetized Areas Help', 'pagelines' ),
				'label'		=>	sprintf( '<i class="icon icon-retweet"></i> %s', __( 'Edit Widgetized Areas', 'pagelines' ) ),
				'help'		=> __( "This section uses widgetized areas that are created and edited in inside your admin.", 'pagelines' ),
			),
			array(
				'key'	=> 'columnizer_description',
				'col'	=> 2,
				'type'	=> 'textarea',
				
				'title'		=> __( 'Column Site Description', 'pagelines' ),
				'label'		=>	__( 'Column Site Description', 'pagelines' ),
				'help'		=> __( "If you use the default display of the columnizer, this field is used as a description of your company. You may want to add your address or links.", 'pagelines' ),
			)
		);

		if( !class_exists('CustomSidebars') && !function_exists('otw_sml_plugin_init') ){
			$opts[] = array(
				'key'	=> 'widgetizer_custom_sidebars',
				'type'	=> 'link',
				'col'	=> 3,
				'url'	=> 'http://wordpress.org/plugins/sidebar-manager-light/screenshots/',
				'title'	=> __( 'Get A Sidebars Plugin', 'pagelines' ),
				'label'		=> __( '<i class="icon icon-external-link"></i> Check out Sidebar Manager plugin', 'pagelines' ),
				'help'		=> __( "We have detected that you don't either the Custom Sidebars or Sidebar Manager plugins installed. We recommend you install a plugin that allows you to create custom widgetized areas on demand.", 'pagelines' ),
			);
		}

		return $opts;
	}



	/**
	* Section template.
	*/
   function section_template() {

		$area = $this->opt('columnizer_area');


		if($area){

			$this->total_widgets = pl_count_sidebar_widgets( $area );
			$this->width = 0;
			$this->count = 1;

			add_filter('dynamic_sidebar_params', array( $this, 'change_markup'));
			pagelines_draw_sidebar( $area );
			remove_filter('dynamic_sidebar_params', array( $this, 'change_markup'));

		} else {
			printf ('<ul class="columnizer row fix sidebar_widgets">%s</ul>', $this->get_default() );
		}



	}

	function get_default(){
		ob_start();
		?>

		<li id="the_default_widget_latest_posts" class="span3 widget">
			<div class="widget-pad">
				<h3 class="widget-title"><?php _e('Latest Posts','pagelines'); ?></h3>
				<?php pl_recent_posts(); ?>
			</div>
		</li>

		<li id="the_default_widget_latest_comments" class="span3 widget">
			<div class="widget-pad">
				<h3 class="widget-title"><?php _e('Recent Comments','pagelines'); ?></h3>
				<ul class="quote-list">
					<?php  pl_recent_comments();  ?>
				</ul>
			</div>
		</li>
		
		<li id="the_default_widget_latest_categories" class="span3 widget">
			<div class="widget-pad">
				<h3 class="widget-title"><?php _e('Top Categories','pagelines'); ?></h3>
				<ul class="media-list">
			<?php  echo  pl_popular_taxonomy();  ?>
				</ul>
			</div>
		</li>
		<li id="the_default_widget_more" class="span3 widget">
			<div class="widget-pad">
				<h3 class="widget-title"><?php _e('About','pagelines'); ?> <?php bloginfo('name'); ?></h3>
				<div class="textwidget">
					<?php

					if($this->opt('columnizer_description')):
						echo $this->opt('columnizer_description');
					else:
					 ?>
					<p>Lorem ipsum dolor sit amet elit, consectetur adipiscing. Vestibulum luctus ipsum id quam euismod a malesuada sapien euismot. Vesti bulum ultricies elementum interdum. </p>

					<address>PageLines Inc.<br/>
					200 Brannan St.<br/>
					San Francisco, CA 94107</address>
				<?php endif; ?>

				</div>
			</div>
		</li>


	<?php


		return ob_get_clean();
	 }

}
