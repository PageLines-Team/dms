<?php
/*
	Section: Flipper
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A great way to flip through posts. Simply select a post type and done.
	Class Name: PageLinesFlipper
	Filter: dual-width, format
*/

class PageLinesFlipper extends PageLinesSection {


	var $default_limit = 3;

	function section_persistent(){

	}

	function section_styles(){
		wp_enqueue_script( 'caroufredsel', $this->base_url.'/caroufredsel.min.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'touchswipe', $this->base_url.'/touchswipe.js', array( 'jquery' ), pl_get_cache_key(), true );
		wp_enqueue_script( 'flipper', $this->base_url.'/flipper.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function section_opts(){


		$options = array();

		$options[] = array(

			'title' => __( 'Config', 'pagelines' ),
			'type'	=> 'multi',
			'col'	=> 1,
			'opts'	=> array(
				array(
					'key'			=> 'flipper_post_type',
					'type' 			=> 'select',
					'opts'			=> pl_get_thumb_post_types(),
					'default'		=> 4,
					'label' 	=> __( 'Which post type should Flipper use?', 'pagelines' ),
					'help'		=> __( '<strong>Note</strong><br/> Post types for this section must have "featured images" enabled and be public.<br/><strong>Tip</strong><br/> Use a plugin to create custom post types for use with Flipper.', 'pagelines' ),
				),
				array(
					'key'		=> $this->id.'_format',
					'type'		=> 'select',
					'label'		=> __( 'Layout Format', 'pagelines' ),
					'opts'			=> array(
						'grid'		=> array('name' => __( 'Grid', 'pagelines' ) ),
						'masonry'	=> array('name' => __( 'Image Only', 'pagelines' ) )
					)
				),
				array(
					'key'			=> 'flipper_shown',
					'type' 			=> 'count_select',
					'count_start'	=> 1,
					'count_number'	=> 6,
					'default'		=> 3,
					'label' 		=> __( 'Max Number of Posts Shown', 'pagelines' ),
					'help'		=> __( 'This controls the maximum number of posts shown. A smaller amount may be shown based on layout width.', 'pagelines' ),
				),
				array(
					'key'			=> 'flipper_sizes',
					'type' 			=> 'select_imagesizes',
					'label' 		=> __( 'Select Thumb Size', 'pagelines' )
				),
				array(
					'key'			=> 'flipper_total',
					'type' 			=> 'count_select',
					'count_start'	=> 5,
					'count_number'	=> 20,
					'default'		=> 10,
					'label' 		=> __( 'Total Posts Loaded', 'pagelines' ),
				),
				array(
					'key'		=> $this->id.'_hide_nav',
					'type'		=> 'check',
					'label'		=> __( 'Hide Nav?', 'pagelines' )
				),


			)

		);

		$options[] = array(

			'title' => __( 'Flipper Content', 'pagelines' ),
			'col'	=> 2,
			'type'	=> 'multi',
			'help'		=> __( 'Options to control the text and link in the Flipper title.', 'pagelines' ),
			'opts'	=> array(
				array(
					'key'			=> 'flipper_title',
					'type' 			=> 'text',
					'label' 		=> __( 'Flipper Title Text', 'pagelines' ),
				),
				array(
					'key'			=> 'flipper_hide_title_link',
					'type' 			=> 'check',
					'label' 	=> __( 'Hide Title Link?', 'pagelines' ),

				),
				array(
					'key'			=> 'flipper_meta',
					'type' 			=> 'text',
					'label' 		=> __( 'Flipper Meta', 'pagelines' ),
					'ref'			=> __( 'Use shortcodes to control the dynamic meta info. Example shortcodes you can use are: <ul><li><strong>[post_categories]</strong> - List of categories</li><li><strong>[post_edit]</strong> - Link for admins to edit the post</li><li><strong>[post_tags]</strong> - List of post tags</li><li><strong>[post_comments]</strong> - Link to post comments</li><li><strong>[post_author_posts_link]</strong> - Author and link to archive</li><li><strong>[post_author_link]</strong> - Link to author URL</li><li><strong>[post_author]</strong> - Post author with no link</li><li><strong>[post_time]</strong> - Time of post</li><li><strong>[post_date]</strong> - Date of post</li><li><strong>[post_type]</strong> - Type of post</li></ul>', 'pagelines' ),
				),
				array(
					'key'			=> 'flipper_show_excerpt',
					'type' 			=> 'check',
					'label' 	=> __( 'Show excerpt?', 'pagelines' ),

				),
				array(
					'key'			=> 'disable_flipper_show_love',
					'type' 			=> 'check',
					'label' 	=> __( 'Disable social button/count?', 'pagelines' ),

				),



			)

		);


		$options[] = array(
			'key'		=> 'flipper_post_sort',
			'type'		=> 'select',
			'label'		=> __( 'Sort elements by postdate', 'pagelines' ),
			'default'	=> 'DESC',
			'opts'			=> array(
				'DESC'		=> array('name' => __( 'Date Descending (default)', 'pagelines' ) ),
				'ASC'		=> array('name' => __( 'Date Ascending', 'pagelines' ) ),
				'rand'		=> array('name'	=> __( 'Random', 'pagelines' ) )
			)
		);

		$selection_opts = array(
			array(
				'key'			=> 'flipper_meta_key',
				'type' 			=> 'text',

				'label' 	=> __( 'Meta Key', 'pagelines' ),
				'help'		=> __( 'Select only posts which have a certain meta key and corresponding meta value. Useful for featured posts, or similar.', 'pagelines' ),
			),
			array(
				'key'			=> 'flipper_meta_value',
				'type' 			=> 'text',

				'label' 	=> __( 'Meta Key Value', 'pagelines' ),
			),
		);

		if($this->opt('flipper_post_type') == 'post'){
			$selection_opts[] = array(
				'label'			=> 'Post Category',
				'key'			=> 'flipper_category',
				'type'			=> 'select_taxonomy',
				'post_type'		=> 'post',
				'help'		=> __( 'Only applies for standard blog posts.', 'pagelines' ),
			);
		}




		$options[] = array(
			'col'	=> 1,
			'title' => __( 'Advanced Post Selection', 'pagelines' ),
			'type'	=> 'multi',

			'opts'	=> $selection_opts
		);



		return $options;
	}

	function section_template(  ) {

		global $post;
		$post_type = ($this->opt('flipper_post_type')) ? $this->opt('flipper_post_type') : 'post';

		$pt = get_post_type_object($post_type);

		$shown = ($this->opt('flipper_shown')) ? $this->opt('flipper_shown') : '3';

		$total = ($this->opt('flipper_total')) ? $this->opt('flipper_total') : '10';

		$title = ($this->opt('flipper_title')) ? $this->opt('flipper_title') : $pt->label;

		$hide_link = ( $this->opt('flipper_hide_title_link') ) ? true : false;

		$show_excerpt = ($this->opt('flipper_show_excerpt')) ? $this->opt('flipper_show_excerpt') : false;
		$disable_show_love = ($this->opt('disable_flipper_show_love')) ? true : false;


		$format = ( $this->opt($this->id.'_format') ) ? $this->opt($this->id.'_format') : 'grid';

		$meta = $this->opt('flipper_meta', array( 'default' => '[post_date] [post_edit]', 'shortcode' => false ) );

		$sizes = ($this->opt('flipper_sizes')) ? $this->opt('flipper_sizes') : 'aspect-thumb';


		$sorting = ($this->opt('flipper_post_sort')) ? $this->opt('flipper_post_sort') : 'DESC';

		$orderby = ( 'rand' == $this->opt('flipper_post_sort') ) ? 'rand' : 'date';

		$the_query = array(
			'posts_per_page' 	=> $total,
			'post_type' 		=> $post_type,
			'orderby'          => $orderby,
			'order'            => $sorting,
			'suppress_filters' => '0'
		);

		if( $this->opt('flipper_meta_key') && $this->opt('flipper_meta_key') != '' && $this->opt('flipper_meta_value') ){
			$the_query['meta_key'] = $this->opt('flipper_meta_key');
			$the_query['meta_value'] = $this->opt('flipper_meta_value');
		}

		if( $this->opt('flipper_category') && $this->opt('flipper_category') != '' ){
			$cat = get_category_by_slug( $this->opt('flipper_category') );
			$the_query['category'] = $cat->term_id;
		}

		$posts = get_posts( $the_query );


		if( !empty($posts) ) {
		//	setup_postdata( $post ); ?>

				<?php if( ! $this->opt( $this->id . '_hide_nav' ) ): ?>
				<div class="flipper-heading">
					<div class="flipper-heading-wrap">
						<div class="flipper-title pl-standard-title">
							<?php
								echo $title;


								$archive_link = get_post_type_archive_link( $post_type );

								if( $archive_link && ! $hide_link ){

									printf( '<a href="%s" > %s</a>',
										$archive_link,
										__(' / View All', 'pagelines')
									);
								} else if ( $post_type == 'post' && get_option( 'page_for_posts') && !is_home() && ! $hide_link ){

									printf( '<a href="%s" > %s</a>',
										get_page_uri( get_option( 'page_for_posts') ),
										__(' / View Blog', 'pagelines')
									);
								}


								?>

						</div>
						<a class="flipper-prev pl-contrast" href="#"><i class="icon icon-angle-left"></i></a>
				    	<a class="flipper-next pl-contrast" href="#"><i class="icon icon-angle-right"></i></a>
					</div>
				</div>
				<?php endif; ?>

				<div class="flipper-wrap">

				<ul class="row flipper-items text-align-center layout-<?php echo $format;?> flipper" data-scroll-speed="800" data-easing="easeInOutQuart" data-shown="<?php echo $shown;?>">
		<?php } ?>

			<?php

			if(!empty($posts)):
				 foreach( $posts as $post ):
					global $post;
					setup_postdata( $post );


					?>


			<li style="">

				<div class="flipper-item fix">
					<?php
					if ( has_post_thumbnail() ) {
						echo get_the_post_thumbnail( $post->ID, $sizes, array('title' => ''));
					} else {
						printf('<img height="400" width="600" src="%s" alt="no image added yet." />', pl_default_image());

						}
						 ?>

					<div class="flipper-info-bg"></div>
					<a class="flipper-info pl-center-inside" href="<?php echo get_permalink();?>">

						<div class="pl-center-table"><div class="pl-center-cell">

							<?php if( $format == 'masonry' ): ?>
								<h4>
									<?php the_title(); ?>
								</h4>
								<div class="metabar">
									<?php  echo do_shortcode( '[post_date]' ); ?>
								</div>
							<?php else: ?>
								<div class="info-text"><i class="icon icon-link"></i></div>
							<?php endif;?>
						</div></div>

					</a>
				</div><!--work-item-->
				<?php if( $format == 'grid' ): ?>
				<div class="flipper-meta">
					<?php if( ! $disable_show_love ) echo pl_karma( $post->ID );?>
					<h4 class="flipper-post-title"><a href="<?php echo get_permalink();?>"><?php the_title(); ?></a></h4>
					<div class="flipper-metabar"><?php echo do_shortcode( apply_filters('pl_flipper_meta', $meta, $post->ID, pl_type_slug() )); ?></div>
					<?php if( $show_excerpt ): ?>
					<div class="flipper-excerpt pl-border">
						<?php the_excerpt();?>
					</div>
					<?php endif;?>

				</div>
				<?php endif; ?>

				<div class="clear"></div>

			</li>

			<?php endforeach; endif;


			if(!empty($posts))
		 		echo '</ul></div>';

		//	wp_reset_query();

	}




}
