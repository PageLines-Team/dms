<?php
/*
	Section: Docker
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: The easiest way to add docs to WordPress. Docker has a sticky sidebar and can use any registered post type.
	Class Name: PLDocker
	Filter: format, dual-width
*/

class PLDocker extends PageLinesSection {


	var $default_limit = 3;

	function section_persistent(){

	}

	function section_styles(){
		wp_enqueue_script( 'stickysidebar', $this->base_url.'/stickysidebar.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'pl-docker', $this->base_url.'/pl.docker.js', array( 'jquery', 'stickysidebar' ), pl_get_cache_key(), true );
	}

	function section_opts(){

		
		$options = array();

		$options['help'] = array(
			'title' => __( 'Using This Section', 'pagelines' ),
			'type'	=> 'multi',
			'col'	=> 1,
			'opts'	=> array(
				array(
					'key'		=> 'help',
					'type'		=> 'link',
					'label'		=> __( 'Using Docker', 'pagelines' ),
					'help'		=> __( 'Using Docker is simple. Usually, it requires two steps.<p>1. Docker uses a custom post type of your choosing. So you probably want to use a plugin to create a custom post type and select it in Docker options.</p><p>2. You will need to add Docker to both its root page and to the template for the custom post type you have created.</p>', 'pagelines' ),
				),

			)
		);

		$options['config'] = array(

			'title' => __( 'Config', 'pagelines' ),
			'type'	=> 'multi',
			'col'	=> 2,
			'opts'	=> array(
				array(
					'key'		=> 'format',
					'type'		=> 'select',
					'label'		=> __( 'Format', 'pagelines' ),
					'opts'			=> array(
						'right'		=> array('name' => __( 'Align Right', 'pagelines' ) ),
						'left'	=> array('name' => __( 'Align Left', 'pagelines' ) )
					)
				),
				array(
					'key'		=> 'nav_title',
					'type'		=> 'text',
					'label'		=> __( 'Nav Title', 'pagelines' ),
				),
				array(
					'key'		=> 'nav_title_link',
					'type'		=> 'text',
					'label'		=> __( 'Nav Title Link URL', 'pagelines' ),
				),
				

			)

		);
		$options['post'] = array(

			'title' => __( 'Posts', 'pagelines' ),
			'type'	=> 'multi',
			'col'	=> 3,
			'opts'	=> pl_get_post_type_options()

		);


		
	


		return $options;
	}
	

	
	function section_template(  ) {
		global $post;
		$posts = $this->get_posts();
		
		$title = ( $this->opt('nav_title') ) ? $this->opt('nav_title') : '';
		
		$title = ( $this->opt('nav_title_link') ) ? sprintf('<a href="%s">%s</a>', $this->opt('nav_title_link'), $title) : '';
		
		$title = ( $title != '' ) ? sprintf( '<lh>%s</lh>', $title ) : '';

		?>
		<div class="docker-wrapper row">
			<div class="docker-sidebar pl-border">
				<div class="docker-mobile-drop pl-contrast">Select <i class="icon icon-caret-down"></i></div>
				<ul class="standard-list theme-list-nav">
					
					<?php echo $title; ?>
				<?php 
				foreach( $posts as $p ){
					$list_class = ( $p->ID == $post->ID ) ? 'current-menu-item' : '';
					printf( '<li class="%s"><a href="%s">%s</a></li>', $list_class, get_permalink( $p->ID ), $p->post_title );
				}
				?>
				</ul>
			</div>
			<div class="docker-content hentry">
				<h2 class="docker-title"><?php the_title(); ?></h2>
				<?php the_content(); ?>
				<?php echo do_shortcode('[post_edit]'); ?>
			</div>
		</div>
		<?php 
	}




}

