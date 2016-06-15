<?php
/*
	Section: PopThumbs
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Adds columnized thumbnails that lightbox to full size images on click.
	Class Name: PLPopThumbs
	Edition: pro
	Filter: gallery
	Loading: active
*/


class PLPopThumbs extends PageLinesSection {

	var $default_limit = 4;

	function section_styles(){
		wp_enqueue_script('prettyphoto', $this->base_url.'/prettyphoto.min.js', array('jquery'));
		wp_enqueue_style( 'prettyphoto-css', $this->base_url.'/prettyPhoto/css/prettyPhoto.css');

	}

	function section_foot(){

		if( pl_draft_mode() )
			return false;
		?>

		<script>
			jQuery(document).ready(function(){
				jQuery("a[rel^='prettyPhoto']").prettyPhoto();
			});
		</script>
		<?php
	}

	function section_opts(){
		$options = array();

		$options[] = array(

			'title' => __( 'PopThumb Configuration', 'pagelines' ),
			'type'	=> 'multi',
			'opts'	=> array(
				array(
					'key'			=> 'popthumb_cols',
					'type' 			=> 'count_select',
					'count_start'	=> 1,
					'count_number'	=> 12,
					'default'		=> '4',
					'label' 	=> __( 'Number of Columns for Each Thumb (12 Col Grid)', 'pagelines' ),
				),
			)

		);


		$options[] = array(
			'key'		=> 'popthumb_array',
	    	'type'		=> 'accordion',
			'col'		=> 2,
			'title'		=> __('PopThumbs Setup', 'pagelines'),
			'post_type'	=> __('Thumb', 'pagelines'),
			'opts'	=> array(


				array(
					'key'	=> 'title',
					'label'	=> __( 'Title', 'pagelines' ),
					'type'			=> 'text'
				),
				array(
					'key'	=> 'text',
					'label'	=> __( 'Text', 'pagelines' ),
					'type'			=> 'text'
				),

				array(
					'key'	=> 'thumb',
					'label'	=> __( 'Thumb', 'pagelines' ),
					'type'	=> 'image_upload'
				),
				array(
					'key'	=> 'image',
					'label'	=> __( 'Image', 'pagelines' ),
					'type'	=> 'image_upload'
				),




			)
	    );

		return $options;
	}


   function section_template( ) {

		$cols = ($this->opt('popthumb_cols')) ? $this->opt('popthumb_cols') : 4;

		$item_array = $this->opt('popthumb_array');

		$format_upgrade_mapping = array(
			'title'			=> 'popthumb_title_%s',
			'text'			=> 'popthumb_text_%s',
			'image'			=> 'popthumb_image_%s',
			'thumb'			=> 'popthumb_thumb_%s',
		);

		$item_array = $this->upgrade_to_array_format( 'popthumb_array', $item_array, $format_upgrade_mapping, $this->opt('popthumb_count'));

		$count = 1;
		$width = 0;
		$output = '';

		$item_array = ( ! is_array($item_array) ) ? array( array(), array(), array() ) : $item_array;

		$num = count( $item_array );

		foreach( $item_array as $item ){

			$link = '';

			$title = pl_array_get( 'title', $item, 'PopThumb '.$count );
			$text = pl_array_get( 'text', $item );
			$img = '';

			$attach_id = pl_array_get( 'image_attach_id', $item );

			if( pl_array_get( 'image', $item ) ) {

				$full_img = pl_array_get( 'image', $item );

			} elseif( pl_array_get( 'thumb', $item ) ){

				$full_img = pl_array_get( 'thumb', $item );

			} else
				$full_img = pl_default_image();



			if( pl_array_get( 'thumb', $item ) ){

				$thumb_url = pl_array_get( 'thumb', $item );

			} elseif($attach_id && $attach_id != ''){

				$img = wp_get_attachment_image_src( $attach_id, 'basic-thumb');

				$thumb_url = $img[0];

			} elseif( pl_array_get( 'image', $item ) ){

				$thumb_url = pl_array_get( 'image', $item );

			} else
				$thumb_url = pl_default_thumb();

			$thumb = sprintf('<img src="%s" alt="%s" />', $thumb_url, $title);


			if($width == 0)
				$output .= '<div class="row fix">';

			$output .= sprintf(
				'<div class="span%s fix">
					<a class="popthumb" href="%s" rel="prettyPhoto[%s]">
						<span class="popthumb-thumb pl-animation pl-appear pl-contrast">
							%s

						</span>
						<span class="expander"><i class="icon icon-plus"></i></span>
					</a>
					<div class="popthumb-text">
						<h4 data-sync="popthumb_title_%s">%s</h4>
						<div class="popthumb-desc" data-sync="popthumb_text_%s">
							%s
						</div>
					</div>
				</div>',
				$cols,
				$full_img,
				$this->meta['unique'],
				$thumb,
				$count,
				$title,
				$count,
				$text
			);

			$width += $cols;

			if($width >= 12 || $count == $num){
				$width = 0;
				$output .= '</div>';
			}

			$count++;

		}



	?>

	<div class="popthumbs-wrap pl-animation-group">
		<?php echo $output; ?>
	</div>

<?php }


}
