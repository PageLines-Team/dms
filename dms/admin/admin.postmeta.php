<?php 

	
add_action('add_meta_boxes', 'pagelines_metabox_posts');
function pagelines_metabox_posts(){
	
	$type = pl_get_current_post_type();
	
	if( ! post_type_supports( $type, 'post-formats' ) )
		return false;
	
	$meta_box = array(
		'id' => 'pagelines-metabox-post-gallery',
		'title' =>  __('Gallery', 'pagelines'),
		'description' => 'Insert a WP gallery using the "Add Media" button above, use the gallery slider checkbox below to transform your images into a slider.',
		'post_type' => pl_get_current_post_type(),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
					'name' =>  __('Gallery Slider', 'pagelines'),
					'desc' => __('Would you like to turn your gallery into a slider?', 'pagelines'),
					'id' => '_pagelines_gallery_slider',
					'type' => 'checkbox',
                    'std' => 1
				)
		)
	);
	$callback = create_function( '$post,$meta_box', 'pl_create_meta_box( $post, $meta_box["args"] );' );
    add_meta_box( $meta_box['id'], $meta_box['title'], $callback, $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box );
	
	
	
	/* 
	 * QUOTE
	 */
    $meta_box = array(
		'id' => 'pagelines-metabox-post-quote',
		'title' =>  __('Quote Settings', 'pagelines'),
		'description' => '',
		'post_type' => pl_get_current_post_type(),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
					'name' =>  __('Quote Content', 'pagelines'),
					'desc' => __('Please type the text for your quote here.', 'pagelines'),
					'id' => '_pagelines_quote',
					'type' => 'textarea',
                    'std' => ''
				)
		)
	);
    add_meta_box( $meta_box['id'], $meta_box['title'], $callback, $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box );
	
	
	/* 
	 * LINK
	 */
	$meta_box = array(
		'id' => 'pagelines-metabox-post-link',
		'title' =>  __('Link Settings', 'pagelines'),
		'description' => '',
		'post_type' => pl_get_current_post_type(),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
					'name' =>  __('Link URL', 'pagelines'),
					'desc' => __('Please input the URL for your link. I.e. http://www.pagelines.com', 'pagelines'),
					'id' => '_pagelines_link',
					'type' => 'text',
					'std' => ''
				)
		)
	);
    add_meta_box( $meta_box['id'], $meta_box['title'], $callback, $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box );
    
	/* 
	 * VIDEO
	 */
    $meta_box = array(
		'id' => 'pagelines-metabox-post-video',
		'title' => __('Video Settings', 'pagelines'),
		'description' => '',
		'post_type' => pl_get_current_post_type(),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array( 
				'name' => __('M4V File URL', 'pagelines'),
				'desc' => __('Please upload the .m4v video file.', 'pagelines'),
				'id' => '_pagelines_video_m4v',
				'type' => 'media', 
				'std' => ''
			),
			array( 
					'name' => __('OGV File URL', 'pagelines'),
					'desc' => __('Please upload the .ogv video file', 'pagelines'),
					'id' => '_pagelines_video_ogv',
					'type' => 'media',
					'std' => ''
				),
			array( 
					'name' => __('Preview Image', 'pagelines'),
					'desc' => __('Image should be at least 680px wide.Only applies to self hosted videos.', 'pagelines'),
					'id' => '_pagelines_video_poster',
					'type' => 'file',
					'std' => ''
				),
			array(
					'name' => __('Embedded Code', 'pagelines'),
					'desc' => __('If the video is an embed rather than self hosted, enter in a Vimeo or Youtube embed code here.', 'pagelines'),
					'id' => '_pagelines_video_embed',
					'type' => 'textarea',
					'std' => ''
				)
		)
	);
	add_meta_box( $meta_box['id'], $meta_box['title'], $callback, $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box );
	
	/* 
	 * AUDIO
	 */
	$meta_box = array(
		'id' => 'pagelines-metabox-post-audio',
		'title' =>  __('Audio Settings', 'pagelines'),
		'description' => '',
		'post_type' => pl_get_current_post_type(),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array( 
				'name' => __('MP3 File URL', 'pagelines'),
				'desc' => __('Please enter in the .mp3 file URL', 'pagelines'),
				'id' => '_pagelines_audio_mp3',
				'type' => 'text',
				'std' => ''
			),
			array( 
					'name' => __('OGA File URL', 'pagelines'),
					'desc' => __('Please enter in the URL to the .ogg or .oga file', 'pagelines'),
					'id' => '_pagelines_audio_ogg',
					'type' => 'text',
					'std' => ''
				)
		)
	);
	add_meta_box( $meta_box['id'], $meta_box['title'], $callback, $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box );
}


/* 
 * CREATE META
 */
function pl_create_meta_box( $post, $meta_box )
{
	
    if( !is_array($meta_box) ) return false;
    
    if( isset($meta_box['description']) && $meta_box['description'] != '' ){
    	echo '<p>'. $meta_box['description'] .'</p>';
    }
    
	wp_nonce_field( basename(__FILE__), 'pagelines_meta_box_nonce' );
	echo '<table class="form-table pagelines-metabox-table">';
 	
	$count = 0;
	
	foreach( $meta_box['fields'] as $field ){

		$meta = get_post_meta( $post->ID, $field['id'], true );
		
		$inline = null;
		if(isset($field['extra'])) { $inline = true; }
		
		if($inline == null) {
			echo '<tr><th><label for="'. $field['id'] .'"><strong>'. $field['name'] .'</strong> <span>'. $field['desc'] .'</span></label></th>';
		}

		
		switch( $field['type'] ){	
			case 'text': 
				echo '<td><input type="text" name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'" value="'. ($meta ? $meta : $field['std']) .'" size="30" /></td>';
				break;	
				
			case 'textarea':
				echo '<td><textarea class="large-text" name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'" rows="8" cols="5">'. ($meta ? $meta : $field['std']) .'</textarea></td>';
				break;
			case 'media_textarea':
				echo '<td><div style="display:none;" class="attr_placeholder" data-poster="" data-media-mp4="" data-media-ogv=""></div><textarea name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'" rows="8" cols="5">'. ($meta ? $meta : $field['std']) .'</textarea></td>';
				break;
			
			case 'editor' :
				$settings = array(
		            'textarea_name' => 'pagelines_meta['. $field['id'] .']',
		            'editor_class' => '',
		            'wpautop' => true
		        );
		        wp_editor($meta, $field['id'], $settings );
				
				break;
			case 'file':
				 
				echo '<td><input type="hidden" id="' . $field['id'] . '" name="pagelines_meta[' . $field['id'] . ']" value="' . ($meta ? $meta : $field['std']) . '" />';
		        echo '<img class="redux-opts-screenshot" id="redux-opts-screenshot-' . $field['id'] . '" src="' . ($meta ? $meta : $field['std']) . '" />';
		        if( ($meta ? $meta : $field['std']) == '') {$remove = ' style="display:none;"'; $upload = ''; } else {$remove = ''; $upload = ' style="display:none;"'; }
		        echo ' <a data-update="Select File" data-choose="Choose a File" href="javascript:void(0);"class="redux-opts-upload button-secondary"' . $upload . ' rel-id="' . $field['id'] . '">' . __('Upload', 'pagelines') . '</a>';
				printf(' <a href="javascript:void(0);" class="redux-opts-upload-remove button" %s rel-id="%s">%s</a></td>', $remove, $field['id'], __('Remove Upload', 'pagelines'));
		        
				break;
 			
			case 'media':
				 
				echo '<td><input type="text" class="file_display_text" id="' . $field['id'] . '" name="pagelines_meta[' . $field['id'] . ']" value="' . ($meta ? $meta : $field['std']) . '" />';
		        if( ($meta ? $meta : $field['std']) == '') {$remove = ' style="display:none;"'; $upload = ''; } else {$remove = ''; $upload = ' style="display:none;"'; }
		        echo ' <a data-update="Select File" data-choose="Choose a File" href="javascript:void(0);"class="redux-opts-media-upload button-secondary"' . $upload . ' rel-id="' . $field['id'] . '">' . __('Add Media', 'pagelines') . '</a>';
		       
		        printf(' <a href="javascript:void(0);" class="redux-opts-upload-media-remove button" %s rel-id="%s">%s</a></td>', $remove, $field['id'], __('Remove Media', 'pagelines'));
				break;
				
			case 'images':
			    echo '<td><input type="button" class="button" name="' . $field['id'] . '" id="pagelines_images_upload" value="' . $field['std'] .'" /></td>';
			    break;
				
			case 'select':
				echo'<td><select name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'">';
				foreach( $field['options'] as $key => $option ){
					echo '<option value="' . $key . '"';
					if( $meta ){ 
						if( $meta == $key ) echo ' selected="selected"'; 
					} else {
						if( $field['std'] == $key ) echo ' selected="selected"'; 
					}
					echo'>'. $option .'</option>';
				}
				echo'</select></td>';
				break;
			case 'choice_below' :
				
				wp_register_style(
	                'redux-opts-jquery-ui-custom-css',
	                apply_filters('redux-opts-ui-theme',  pagelines_FRAMEWORK_DIRECTORY . 'options/css/custom-theme/jquery-ui-1.10.0.custom.css'),
	                '',
	                time(),
	                'all'
	            );
				 wp_enqueue_style('redux-opts-jquery-ui-custom-css');
		         wp_enqueue_script(
		            'redux-opts-field-button_set-js', 
		            pagelines_FRAMEWORK_DIRECTORY . 'options/fields/button_set/field_button_set.js', 
		            array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'),
		            time(),
		            true
		        );
				echo '<td colspan="8">';
				    echo '<fieldset class="buttonset">';
						foreach( $field['options'] as $key => $option ){
				
							echo '<input type="radio" id="pagelines_meta_'. $key .'" name="pagelines_meta['. $field["id"] .']" value="'. $key .'" ';
							if( $meta ){ 
								if( $meta == $key ) echo ' checked="checked"'; 
							} else {
								if( $field['std'] == $key ) echo ' checked="checked"';
							}
							echo ' /> ';
							echo '<label for="pagelines_meta_'. $key .'"> '.$option.'</label>';
							
						}
					echo '</fieldset>';
				echo '</td>';
				break;
			case 'multi-select':
				echo'<td><select multiple="multiple" name="pagelines_meta['. $field['id'] .'][]" id="'. $field['id'] .'">';
				foreach( $field['options'] as $key => $option ){
					echo '<option value="' . $key . '"';
					if( $meta ){
						
						echo (is_array($meta) && in_array($key, $meta)) ? ' selected="selected"' : '';
           				 
						if( $meta == $key ) echo ' selected="selected"'; 
					} else {
						if( $field['std'] == $key ) echo ' selected="selected"'; 
					}
					echo'>'. $option .'</option>';
				}
				echo'</select></td>';
				break;
				
			case 'slide_alignment' :
				
				wp_register_style(
	                'redux-opts-jquery-ui-custom-css',
	                apply_filters('redux-opts-ui-theme',  pagelines_FRAMEWORK_DIRECTORY . 'options/css/custom-theme/jquery-ui-1.10.0.custom.css'),
	                '',
	                time(),
	                'all'
	            );
				 wp_enqueue_style('redux-opts-jquery-ui-custom-css');
		         wp_enqueue_script(
		            'redux-opts-field-button_set-js', 
		            pagelines_FRAMEWORK_DIRECTORY . 'options/fields/button_set/field_button_set.js', 
		            array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'),
		            time(),
		            true
		        );
				echo '<td>';
				    echo '<fieldset class="buttonset">';
						foreach( $field['options'] as $key => $option ){
				
							echo '<input type="radio" id="pagelines_meta_'. $key .'" name="pagelines_meta['. $field["id"] .']" value="'. $key .'" ';
							if( $meta ){ 
								if( $meta == $key ) echo ' checked="checked"'; 
							} else {
								if( $field['std'] == $key ) echo ' checked="checked"';
							}
							echo ' /> ';
							echo '<label for="pagelines_meta_'. $key .'"> '.$option.'</label>';
							
						}
					echo '</fieldset>';
				echo '</td>';
				break;
			case 'radio':
				echo '<td>';
				foreach( $field['options'] as $key => $option ){
					echo '<label class="radio-label"><input type="radio" name="pagelines_meta['. $field['id'] .']" value="'. $key .'" class="radio"';
					if( $meta ){ 
						if( $meta == $key ) echo ' checked="checked"'; 
					} else {
						if( $field['std'] == $key ) echo ' checked="checked"';
					}
					echo ' /> '. $option .'</label> ';
				}
				echo '</td>';
				break;
			case 'slider_button_text':
				if($field['extra'] == 'first'){
					$count++;
					echo '<tr><td><label><strong>Button #'.$count.'</strong> <span>Configure your button here.</span> </label></td>';
				}
				echo '<td class="inline">';
				if($inline != null) {
					echo '<label for="'. $field['id'] .'"><strong>'. $field['name'] .'</strong>
			 		 <span>'. $field['desc'] .'</span></label>';
				}
				echo '<input type="text" name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'" value="'. ($meta ? $meta : $field['std']) .'" size="30"  />';
				echo '</td>';
				break;
			case 'slider_button_textarea':
				if($field['extra'] == 'first'){
					$count++;
					echo '<tr><td><label><strong>Button #'.$count.'</strong> <span>Configure your button here.</span> </label></td>';
				}
				echo '<td class="inline">';
				if($inline != null) {
					echo '<label for="'. $field['id'] .'"><strong>'. $field['name'] .'</strong>
			 		 <span>'. $field['desc'] .'</span></label>';
				}
				echo '<textarea name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'">'.($meta ? $meta : $field['std']) .'</textarea>';
				echo '</td>';
				break;
				
			case 'slider_button_select':
				echo '<td class="inline">';
				if($inline != null) {
					echo '<label for="'. $field['id'] .'"><strong>'. $field['name'] .'</strong>
			 		 <span>'. $field['desc'] .'</span></label>';
				}
				echo'<select name="pagelines_meta['. $field['id'] .']" id="'. $field['id'] .'">';
				foreach( $field['options'] as $key => $option ){
					echo '<option value="' . $key . '"';
					if( $meta ){ 
						if( $meta == $key ) echo ' selected="selected"'; 
					} else {
						if( $field['std'] == $key ) echo ' selected="selected"'; 
					}
					echo'>'. $option .'</option>';
				}
				echo'</select></td>';
				if($field['extra'] == 'last'){
					echo '</tr>';
				}
				break;
			case 'checkbox':
			    echo '<td>';
			    $val = '';
                if( $meta ) {
                    if( $meta == 'on' ) $val = ' checked="checked"';
                } else {
                    if( $field['std'] == 'on' ) $val = ' checked="checked"';
                }

                echo '<input type="hidden" name="pagelines_meta['. $field['id'] .']" value="off" />
                <input type="checkbox" id="'. $field['id'] .'" name="pagelines_meta['. $field['id'] .']" value="on"'. $val .' /> ';
			    echo '</td>';
			    break;
		}
		
	
		
		if($inline == null) {
			echo '</tr>';
		}
	}
 
	echo '</table>';
}

/* 
 * SAVE META
 */
add_action( 'save_post', 'pagelines_save_meta_box' );
function pagelines_save_meta_box( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	
	if ( !isset($_POST['pagelines_meta']) || !isset($_POST['pagelines_meta_box_nonce']) || !wp_verify_nonce( $_POST['pagelines_meta_box_nonce'], basename( __FILE__ ) ) )
		return;
	
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) return;
	} 
	else {
		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	}
 
	foreach( $_POST['pagelines_meta'] as $key=>$val ){
		update_post_meta( $post_id, $key, $val );
	}

}

function pl_get_current_post_type() {
	global $post, $typenow, $current_screen;
	
	//we have a post so we can just get the post type from that
	if ( $post && $post->post_type )
		return $post->post_type;
    
	//check the global $typenow - set in admin.php
	elseif( $typenow )
		return $typenow;
    
	//check the global $current_screen object - set in sceen.php
	elseif( $current_screen && $current_screen->post_type )
		return $current_screen->post_type;
  
	//lastly check the post_type querystring
	elseif( isset( $_REQUEST['post_type'] ) )
		return sanitize_key( $_REQUEST['post_type'] );
	
	//we do not know the post type!
	return null;
}







