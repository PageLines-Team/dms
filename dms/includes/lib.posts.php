<?php



/*
 * STANDARD POST HELPERS
 */ 

function pagelines_media( $args = array() ){
	
	global $post;
	
	$defaults = array(
		
		'thumb-size'	=> 'landscape-thumb',
		'id'			=> $post->ID,
		
	); 
	
	$args = wp_parse_args( $args, $defaults );

	$vars = array(
		'embed'			=> get_post_meta( $args['id'], '_pagelines_video_embed', true),
		'm4v'			=> get_post_meta( $args['id'], '_pagelines_video_m4v', true),
		'ogv'			=> get_post_meta( $args['id'], '_pagelines_video_ogv', true),
		'poster'		=> get_post_meta( $args['id'], '_pagelines_video_poster', true),
		'gallery'		=> get_post_meta( $args['id'], '_pagelines_gallery_slider', true),
		'mp3'	 		=> get_post_meta( $args['id'], '_pagelines_audio_mp3', true),
	    'ogg'	 		=> get_post_meta( $args['id'], '_pagelines_audio_ogg', true),
		'quote'	 		=> get_post_meta( $args['id'], '_pagelines_quote', true),
		'link' 			=> get_post_meta( $args['id'], '_pagelines_link', true)
	);
	
	$args = wp_parse_args( $args, $vars );

	$post_format = get_post_format();
	
	
	// VIDEO
	if( $post_format == 'video' && ( ! empty( $args['embed'] ) || ! empty( $args['m4v'] ) || ! empty( $args['mov'] ) ) ){
		
	    if( !empty( $args['embed'] ) ) {
			
			$media = sprintf( '<div class="video">%s</div>', do_shortcode( $args['embed'] ) );	

		} else {

			$media = sprintf( '<div class="video">[video mp4="%s" ogv="%s"  poster="%s"]</div>', $args['m4v'], $args['ogv'], $args['poster']);	

		} 
	} 
	
	// QUOTE
	else if( $post_format == 'quote' ){
	
		$quote = ( $args['quote'] ) ? $args['quote'] : get_the_content();
	
		$content = sprintf( '<h2 class="entry-title">%s</h2> <span class="author">%s</span><span class="linkbox-icon"><i class="icon icon-quote-right icon-2x"></i></span></h2>', $quote, get_the_title());
		
		$wrapped = ( is_single()) ? sprintf('<div class="pl-linkbox pl-quote fix">%s</div>', $content ) : sprintf('<a href="%s" class="pl-linkbox pl-quote">%s</a>', get_permalink(), $content );
		
		$media = $wrapped;
	}
	
	// LINK
	else if( $post_format == 'link' ){
	
		$link = $args['link'];
		
		$link = str_replace( 'http://', '', $link );
		$link = str_replace( 'https://', '', $link );
	
		$content = sprintf( '<h2 class="entry-title">%s</h2> <span class="destination">%s</span><span class="linkbox-icon"><i class="icon icon-link icon-2x"></i></span></h2>', get_the_title(), $link );
		
		$wrapped = sprintf('<a href="http://%s" class="pl-linkbox pl-quote fix">%s</a>', $link, $content );
		
		$media = $wrapped;
	}
	
	// AUDIO
	else if( $post_format == 'audio' && ( ! empty( $args['mp3'] ) || ! empty( $args['ogg'] ) ) ){
	
		$audio_output = sprintf('[audio mp3="%s" ogg="%s"]', $args['mp3'], $args['ogg']);
		
		$media = sprintf( '<div class="pl-audio-player">%s</div>', do_shortcode( $audio_output ) );
	}
	
	// GALLERY
	else if( $post_format == 'gallery' && !empty( $args['gallery'] ) ){
		
	    $gallery_ids = pl_get_attachment_ids_from_gallery();
		
		ob_start();
	 	?>

		<div class="flex-gallery"> 
			<ul class="slides">
			<?php 
				foreach( $gallery_ids as $image_id ) {
					
					$attachment = get_post( $image_id );
					  
					$image = wp_get_attachment_image( $image_id, $args['thumb-size'], false  );
					
					$caption = ( $attachment->post_excerpt != '' ) ? sprintf('<p class="flex-caption">%s</p>', $attachment->post_excerpt) : '';
					
					printf( '<li>%s %s</li>', $image, $caption);
					
				} ?>
			</ul>
		</div><!--/gallery-->
		<?php 
		
		$media = ob_get_clean();
	}
	
	// STANDARD THUMB
	elseif ( has_post_thumbnail() ) {
		
		 $media = sprintf('<a class="post-thumbnail-link" href="%s">%s</a>', get_permalink(), get_the_post_thumbnail( $args['id'], $args['thumb-size'], array('title' => ''))); 
		
	} else 
		$media = '';
	
	return do_shortcode( $media );
}

function pl_get_attachment_ids_from_gallery() {
	
	global $post;

	if($post != null) {

	$attachment_ids = array();  
	$pattern = get_shortcode_regex();
	$ids = array();
		//finds the "gallery" shortcode, puts the image ids in an associative array: $matches[3]
		if (preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) ) {   
		
			$count = count( $matches[3] );      //in case there is more than one gallery in the post.
		
			for ($i = 0; $i < $count; $i++){
			
				$atts = shortcode_parse_atts( $matches[3][$i] );
				if ( isset( $atts['ids'] ) ){
					$attachment_ids = explode( ',', $atts['ids'] );
					$ids = array_merge($ids, $attachment_ids);
				}
			
			}	
		}

		return $ids;
	
	} else {
		
		$ids = array();
		
		return $ids;
		
	}


}

