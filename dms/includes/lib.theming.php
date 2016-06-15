<?php

function pl_faux_browser( $get = 'buttons' ){
	return '<div class="pl-browser-header"><div class="browser-btns"><span class="bbtn-red"></span><span class="bbtn-orange"></span><span class="bbtn-green"></span></div></div>';
}

function pl_social_icons(){
	$icons = array(
		'facebook',
		'linkedin',
		'instagram',
		'twitter',
		'youtube',
		'google-plus',
		'pinterest',
		'dribbble',
		'flickr',
		'github',
	); 
	
	return $icons;
}

function pl_social_links_options(){
	$the_urls = array(); 
	
	$icons = pl_social_icons();
	
	foreach($icons as $icon){
		$the_urls[] = array(
			'label'	=> ui_key($icon) . ' URL', 
			'key'	=> 'sl_'.$icon,
			'type'	=> 'text',
			'scope'	=> 'global',
		); 
	}
	
	return $the_urls;
}

function pl_social_links(){
	
	$target = "target='_blank'";
	ob_start(); 
	?>
	<div class="sl-links">
	<?php 
	
	foreach( pl_social_icons() as $icon){
	
		$url = ( pl_setting('sl_'.$icon) ) ? pl_setting('sl_'.$icon) : false;
	
		if( $url )
			printf('<a href="%s" class="sl-link" %s><i class="icon icon-%s"></i></a>', $url, $target, $icon); 
	}
	
	if( ! pl_setting( 'sl_web_disable' ) ){
		
		?><span class="sl-web-links"><a class="sl-link"  title="CSS3 Valid"><i class="icon icon-css3"></i></a><a class="sl-link" title="HTML5 Valid"><i class="icon icon-html5"></i></a><a class="sl-link" href="http://www.pagelines.com" title="Built with PageLines DMS"><i class="icon icon-pagelines"></i></a>
		</span>
		<?php 
		
	}
	
	?> </div>
	
	<?php 
	return ob_get_clean();
}

function pl_navigation( $args = array() ){
	
	$respond = ( isset( $args['respond'] ) && ! $args['respond'] ) ? '' : 'respond';
	
	$menu_classes = sprintf('menu-toggle mm-toggle %s', $respond);
		
	$dropdown_theme = ( pl_setting('nav_dropdown_bg') ) ? sprintf('dd-theme-%s', pl_setting('nav_dropdown_bg')) : 'dd-theme-dark';
	$dropdown_toggle = ( pl_setting('nav_dropdown_toggle') ) ? sprintf('dd-toggle-%s', pl_setting('nav_dropdown_toggle')) : 'dd-toggle-hover';
	
	$top_classes = $dropdown_theme . ' ' . $dropdown_toggle;
		
	if( ( ! isset( $args['menu'] ) || empty( $args['menu'] ) ) && ! has_nav_menu( $args['theme_location'] ) ){
		
		$out = sprintf('<ul class="inline-list pl-nav"><li class="popup-nav"><a class="menu-toggle mm-toggle show-me"><i class="icon icon-reorder"></i></a></li></ul>');
		
	} else {
		
		// allow inline styles on nav ( offsets! )
		if( isset( $args['attr'] ) ){
			$args['items_wrap'] = '<ul id="%1$s" class="%2$s '.$top_classes.'" '.$args['attr'].'>%3$s<li class="popup-nav"><a class="'.$menu_classes.'"><i class="icon icon-reorder"></i></a></li></ul>'; 
		}
		
		$defaults = array(
			'menu_class'		=> 'inline-list pl-nav',
			'menu'				=> pl_setting( 'primary_navigation_menu' ),
			'container'			=> null,
			'container_class'	=> '',
			'depth'				=> 3,
			'fallback_cb'		=> '',
			'items_wrap'      	=> '<ul id="%1$s" class="%2$s '.$top_classes.'" style="">%3$s<li class="popup-nav"><a class="'.$menu_classes.'"><i class="icon icon-reorder"></i></a></li></ul>',
			'style'				=> false, 
			'echo'				=> false,
			'pl_behavior'		=> 'standard',
			'walker' 			=> new PageLines_Walker_Nav_Menu
		); 

		$args = wp_parse_args( $args, $defaults );
		
		
		$args['menu_class'] .= ' '. $respond;
		
		$out = str_replace("\n","", wp_nav_menu( $args ));
	}	
	return $out;
}

// Adds arrows and classes
class PageLines_Walker_Nav_Menu extends Walker_Nav_Menu {

    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {

  		$id_field = $this->db_fields['id'];

        if (!empty($children_elements[$element->$id_field]) && $element->menu_item_parent == 0) {
	
            $element->title =  $element->title . '<span class="sub-indicator"><i class="icon icon-angle-down"></i></span>';
			$element->classes[] = 'sf-with-ul';

        }

		if (!empty($children_elements[$element->$id_field]) && $element->menu_item_parent != 0) {
            $element->title =  $element->title . '<span class="sub-indicator"><i class="icon icon-angle-right"></i></span>';
        }

        Walker_Nav_Menu::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
    }
}


function pl_posts_404(){

	$head = __('Nothing Found', 'pagelines');

	$subhead = ( is_search() ) ? __('Try another search?', 'pagelines') : __("Sorry, what you are looking for isn't here.", 'pagelines');

	$the_text = sprintf('<h2 class="center">%s</h2><p class="subhead center">%s</p>', $head, $subhead);

	return sprintf( '<section class="billboard">%s <div class="center fix">%s</div></section>', apply_filters('pagelines_posts_404', $the_text), pagelines_search_form( false ));

}

function pl_get_core_header(){
	require_once( pl_get_template_directory() . '/header.php' );
}

function pl_get_core_footer(){
	require_once( pl_get_template_directory() . '/footer.php' );
}

// This file contains utilities for theme development and theme user experience

function pl_theme_info( $field ){
	$theme_info = wp_get_theme();
	return $theme_info->$field;
}


// Blog Sections & Post Utilities


function pl_post_avatar( $post_id, $size ){
	
	$author_name = get_the_author();
	$default_avatar = PL_IMAGES . '/avatar_default.gif';
	$author_desc = custom_trim_excerpt( get_the_author_meta('description', $p->post_author), 10);
	$author_email = get_the_author_meta('email', $p->post_author);
	$avatar = get_avatar( $author_email, '32' );
	
}

function pl_list_pages( $number = 6 ){

	$pages_out = '';

	$pages = wp_list_pages('echo=0&title_li=&sort_column=menu_order&depth=1');

	$pages_arr = explode("\n", $pages);
	
	for($i=0; $i < $number; $i++){

		if(isset($pages_arr[$i]))
			$pages_out .= $pages_arr[$i];

	}
	
	return $pages_out;
	
}

function pl_recent_comments( $number = 3 ){

	$comments = get_comments( array( 'number' => $number, 'status' => 'approve' ) );
	if ( $comments ) {
		foreach ( (array) $comments as $comment) {
			
			if( 'comment' != get_comment_type( $comment ) )
				continue;

			$post = get_post( $comment->comment_post_ID );
			$link = get_comment_link( $comment->comment_ID ); 
			
			$avatar = pl_get_avatar_url( get_avatar( $comment ) ); 
			$img = ($avatar) ? sprintf('<div class="img rtimg"><a class="the-media" href="%s" style="background-image: url(%s)"></a></div>', $link, $avatar) : '';
			
			printf(
				'<li class="media fix">%s<div class="bd"><div class="the-quote pl-contrast"><div class="title" >"%s"</div><div class="excerpt">%s <a href="%s">%s</a></div></div></div></li>', 
				$img, 
				stripslashes( mb_substr( wp_filter_nohtml_kses( $comment->comment_content ), 0, 50 ,'UTF-8' ) ),
				__( 'on', 'pagelines' ),
				$link,
				custom_trim_excerpt($post->post_title, 3)
				
			);
		}
		
	}
	
}

function pl_recent_posts( $number = 3 ){?>
	<ul class="media-list">
		<?php

		foreach( get_posts( array('numberposts' => $number ) ) as $p ){
			
			
			$img_src = (has_post_thumbnail( $p->ID )) ? pl_the_thumbnail_url( $p->ID, 'thumbnail') : '';
		
			$img = ( $img_src != '' ) ? sprintf('<div class="img"><a class="the-media" href="%s" style="background-image: url(%s)"></a></div>', get_permalink( $p->ID ), $img_src) : '';

			printf(
				'<li class="media fix">%s<div class="bd"><div class="wrp"><a class="title" href="%s">%s</a><span class="excerpt">%s</span></div></div></li>',
				$img,
				get_permalink( $p->ID ),
				$p->post_title,
				pl_short_excerpt($p->ID, 6)
			);

		} ?>
	</ul>
<?php }

function pl_popular_taxonomy( $number_of_categories = 6, $taxonomy = 'category' ){
	
	$args = array( 
		'number' 	=> $number_of_categories,
		'depth' 	=> 1, 
		'title_li' 	=> '', 
		'orderby' 	=> 'count', 
		'show_count' => 1, 
		'order' 	=> 'DESC',
		'taxonomy'	=> $taxonomy,
		'echo'		=> 0
	);
	
	return wp_list_categories( $args );

}

function pl_media_list( $title, $list ){
	
	return sprintf( '<ul class="media-list"><lh class="title">%s</lh>%s</ul>', $title, $list);
	
	
}

function pl_get_image_id_from_src( $file_url ) {
    
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$file_path = ltrim( str_replace( $upload_dir['baseurl'], '', $file_url ), '/');

		$statement = $wpdb->prepare( "SELECT `ID` FROM {$wpdb->prefix}posts AS posts JOIN {$wpdb->prefix}postmeta AS meta on meta.`post_id`=posts.`ID` WHERE posts.`guid`='%s' OR (meta.`meta_key`='_wp_attached_file' AND meta.`meta_value` LIKE '%%%s');",
				$file_url,
        $file_path
		);

    $attachment = $wpdb->get_col($statement);

    if (count($attachment) < 1) {
        return false;
    }

    return $attachment[0]; 
}
