<?php




add_action('wp_ajax_pl_editor_actions', 'pl_editor_actions');
function pl_editor_actions(){

	$postdata = $_POST;
	$response = array();
	$response['post'] = $postdata;
	$mode = $postdata['mode'];
	$run = $postdata['run'];
	$pageID = $postdata['pageID'];
	$typeID = $postdata['typeID'];
	
	$response['dataAmount'] = ( isset( $_SERVER['CONTENT_LENGTH'] ) ) ? (int) $_SERVER['CONTENT_LENGTH'] : 'No Value';

	if($mode == 'save'){
		
		$draft = new EditorDraft;
		$tpl = new EditorTemplates;
		$map = $postdata['map_object'] = new PageLinesTemplates( $tpl );

		if ( $run == 'map' || $run == 'all' || $run == 'draft' || $run == 'publish'){

			$draft->save_draft( $pageID, $typeID, $postdata['pageData'] );


		}

		elseif ( $run == 'revert' )
			$draft->revert( $postdata, $map );

		$response['state'] = $draft->get_state( $pageID, $typeID, $map );


	} elseif( $mode == 'sections'){

		if( $run == 'reload'){

			global $editorsections;
			$editorsections->reset_sections();
			$available = $editorsections->get_sections();
			$response['result'] = $available;
			
		} elseif( $run == 'load' ){

			$section_object = $postdata['object'];
			$section_unique_id = $postdata['uniqueID'];
			$draw = $postdata['draw'];

			global $pl_section_factory;

			if( is_object($pl_section_factory->sections[ $section_object ]) ){

				global $post;
			
				$post = get_post($postdata['pageID']);

				$s = $pl_section_factory->sections[ $section_object ];

				// needs to be set.. ??
				$s->meta['content'] = array();
				$s->meta['unique']	= '';
				$s->meta['draw']	= $draw;
				
				
				$opts = $s->section_opts();

				$opts = (is_array($opts)) ? $opts : array();

				$response['opts'] = array_merge($opts, pl_standard_section_options( $s ));

				ob_start();
					$s->active_loading = true;
					$s->section_template();
				$section_template = ob_get_clean();

				ob_start();
					$s->section_head();
					$s->section_foot();
				$head_foot = ob_get_clean();
				
				if($head_foot)
					$response['notice'] = true; 
				else 
					$response['notice'] = false;

				$response['template'] = ($section_template == '') ? pl_blank_template() : $section_template;

			}



		}


	} elseif ( $mode == 'settings' ){

		$plpg = new PageLinesPage( array( 'mode' => 'ajax', 'pageID' => $pageID, 'typeID' => $typeID ) );
		$draft = new EditorDraft;
		$settings = new PageLinesOpts;

		if ($run == 'reset_global'){

			reset_global_settings();

		} elseif( $run == 'reset_local' ){

			pl_reset_meta_settings( $pageID );

		} elseif( $run == 'reset_type' ){

			pl_reset_meta_settings( $typeID );

		}elseif( $run == 'delete' ){

			// delete clone index by keys


		}elseif( $run == 'exporter' ) {

			$data = $postdata['formData'];
			$data = stripslashes_deep( $data );
			$fileOpts = new EditorFileOpts;
			$response['export'] = $fileOpts->init( $data );
			$response['export_data'] = $data;

		} elseif( $run == 'reset_global_child' ) {

			$opts = array();
			$opts['global_import'] = $_POST['global_import'];
			$opts['type_import'] = $_POST['type_import'];
			$opts['page_tpl_import'] = $_POST['page_tpl_import'];
			$settings->reset_global_child( $opts );

		} elseif( 'reset_cache' == $run ) {
			$settings->reset_caches();
		} elseif( $run == 'import_from_child' ) {
			$settings->import_from_child();
		} elseif( $run == 'import_from_child_cancelled' ) {
			$settings->import_from_child_cancelled();
		}

	} else {
	
		$response = apply_filters( 'pl_ajax_'.$mode, $response, $postdata ); 
	}


	// RESPONSE
	echo json_encode(  pl_arrays_to_objects( $response ) );

	die(); // don't forget this, always returns 0 w/o
}

/* 
 * System for handling admin ajax
 **/
add_action('wp_ajax_pl_admin_ajax', 'pl_admin_ajax');
function pl_admin_ajax(){
	$response = array( 'post' => $_POST );
	$response = apply_filters( 'pl_ajax_'.$_POST['mode'], $response, $_POST ); 
	
	echo json_encode(  pl_arrays_to_objects( $response ) );

	die(); // don't forget this, always returns 0 w/o
}


add_action('wp_ajax_upload_config_file', 'pl_upload_config_file');
function pl_upload_config_file(){

	$fileOpts = new EditorFileOpts;
	$filename = $_FILES['files']['name'][0];

	$opts = array();
	$opts['global_import'] = $_POST['global_import'];
	$opts['type_import'] = $_POST['type_import'];
	$opts['page_tpl_import'] = $_POST['page_tpl_import'];

	if( preg_match( '/pl\-config[^\.]*\.json/', $filename ) ) {
		$file = $_FILES['files']['tmp_name'][0];

	$response['file'] = $file;

	if( isset( $file ) )
		$response['import_reponse'] = $fileOpts->import( $file, $opts );


		$response['import_file'] = $file;
		$response['post'] = $_POST;
	} else {
		$response['import_error'] = $filename;
	}

	echo json_encode(  pl_arrays_to_objects( $response ) );
	die();
}

add_action('wp_ajax_pl_editor_mode', 'pl_editor_mode');
function pl_editor_mode(){

	$postdata = $_POST;
	$key = 'pl_editor_state';
	$user_id = $postdata[ 'userID' ];

	$current_state = get_user_meta($user_id, $key, true);

	$new_state = (!$current_state || $current_state == 'on' || $current_state == '') ? 'off' : 'on';

	update_user_meta( $user_id, $key, $new_state );

	echo $new_state;

	die();
}



add_action('wp_ajax_pl_dms_admin_actions', 'pl_dms_admin_actions');
function pl_dms_admin_actions(){
	$response = array();
	$postdata = $_POST;
	$response['post'] = $_POST;
	$lessflush = ( isset( $postdata['flag'] ) ) ? $postdata['flag'] : false;

	$field = $postdata['setting'];
	$value = $postdata['value'];

	if( 'custom_less' == $field || 'custom_scripts' == $field ) {
		while( strchr( $value, '\\' ) ) { 
			$value = stripslashes( $value ); 
		}
	}

	pl_setting_update($field, $value );

	$response['value'] = $value;
	$response['field'] = $field;
	echo json_encode(  pl_arrays_to_objects( $response ) );
	if( $lessflush ) {
		global $dms_cache;
		$dms_cache->purge_all();
	}
	die();
}

function pl_tmp_mime_overide ( $existing_mimes = array() ) {
	$existing_mimes['svg'] = 'image/svg+xml';
	return $existing_mimes;
}


add_action( 'wp_ajax_pl_up_image', 'pl_up_image' );
function pl_up_image (){

	global $wpdb;

	$files_base = $_FILES[ 'qqfile' ];

	add_filter( 'upload_mimes', 'pl_tmp_mime_overide' );
	$arr_file_type = wp_check_filetype( basename( $files_base['name'] ));
	$uploaded_file_type = $arr_file_type['type'];

	// Set an array containing a list of acceptable formats
	$allowed_file_types = array( 'image/jpg','image/jpeg','image/gif','image/png', 'image/x-icon', 'image/svg+xml');

	if( in_array( $uploaded_file_type, $allowed_file_types ) ) {

		$files_base['name'] = preg_replace( '/[^a-zA-Z0-9._\-]/', '', $files_base['name'] );

		$override['test_form'] = false;
		$override['action'] = 'wp_handle_upload';

		$uploaded_file = wp_handle_upload( $files_base, $override );

	//	$upload_tracking[] = $button_id;

		// ( if applicable-Update option here)

		$name = sprintf( '%s%s',
			apply_filters( 'pl_up_image_prefix', 'PageLines-' ),
			addslashes( $files_base['name'] )
		);

		$attachment = array(
						'guid'				=> $uploaded_file['url'],
						'post_mime_type'	=> $uploaded_file_type,
						'post_title'		=> $name,
						'post_content'		=> '',
						'post_status'		=> 'inherit'
					);

		$attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
		wp_update_attachment_metadata( $attach_id,  $attach_data );
		
		do_action( 'after_pl_up_image', $attach_id, $attach_data );
		
	} else
		$uploaded_file['error'] = __( 'Unsupported file type!', 'pagelines' );

	if( !empty( $uploaded_file['error'] ) )
		echo sprintf( __('Upload Error: %s', 'pagelines' ) , $uploaded_file['error'] );
	else{
		
		$url = pl_shortcodize_url( $uploaded_file['url'] );
		 
		echo json_encode( array( 'url' => $url, 'success' => TRUE, 'attach_id' => $attach_id ) );

	}
	remove_filter( 'upload_mimes', 'pl_tmp_mime_overide' );
	die(); // don't forget this, always returns 0 w/o	
}

add_filter( 'pagelines_global_notification', 'pagelines_check_folders_dms');
add_filter( 'pagelines_global_notification', 'pagelines_check_dms_plugin');
add_filter( 'pagelines_global_notification', 'pagelines_check_updater');

function pagelines_check_folders_dms( $note ) {
		
	$folder = basename( get_template_directory() );

	if( 'dms' != $folder && ! defined( 'DMS_CORE' ) ){
		
		ob_start(); ?>

			<div class="alert editor-alert">
				<button type="button" class="close" data-dismiss="alert" href="#">&times;</button>
			  	<strong><i class="icon icon-warning-sign"></i> Install Problem!</strong><p>it looks like you have DMS installed in the wrong folder.<br />DMS must be installed in wp-content/themes/<strong>dms</strong>/ and not wp-content/themes/<strong><?php echo $folder; ?></strong>/</p>

			</div>
			<?php 

		$note .= ob_get_clean();
	}
	return $note;
}

function pagelines_check_dms_plugin( $note ) {
	
	if( pl_is_activated() && ! pl_has_dms_plugin() && is_super_admin() ){
		ob_start(); ?>

			<div class="editor-alert alert">
				
			  	<strong><i class="icon icon-cogs"></i> <?php _e( 'Install DMS Utilities', 'pagelines' ); ?>
			  	</strong><p><?php _e( 'Your site is "Pro activated" but we have detected that the DMS Pro Tools plugin is not activated. Grab this plugin if you have not installed it yet on <a href="http://www.pagelines.com/my-account" >PageLines.com &rarr; My-Account</a>.', 'pagelines' ); ?>
			  	</p>

			</div>

			<?php 

		$note .= ob_get_clean();
	}
	return $note;
}
	
function pagelines_check_updater( $note ) {
	
	$message = pl_updater_txt();
	
	if( $message ) {
		ob_start();
		?>
		<div class="editor-alert alert">		
		  	<p>
			<?php echo $message ?>
			</p>
		</div>
		<?php $note .= ob_get_clean();
	}
	return $note;
}		

function pl_media_library_link( $type = 'image' ){
	
	global $post;

	$post_id = ( empty($post->ID) ) ? 0 : $post->ID;

	$image_library_url = add_query_arg( 'post_id', (int) $post_id, admin_url('media-upload.php') );
//	$image_library_url = add_query_arg( 'type', $type, $image_library_url );
	$image_library_url = add_query_arg( 'post_mime_type', $type, $image_library_url );
	$image_library_url = add_query_arg( 'tab', 'library', $image_library_url);
	$image_library_url = add_query_arg( array( 'context' => 'pl-custom-attach', 'TB_iframe' => 1), $image_library_url );
	
	return $image_library_url;
	
}


$custom_attach = new PLImageUploader();

class PLImageUploader{
	function __construct() {
		if ( isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'pl-custom-attach' ) {

			$this->option_id = (isset( $_REQUEST['oid'] )) ? $_REQUEST['oid'] : '';

			add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 15, 2 );
			add_filter( 'media_upload_tabs', array( $this, 'filter_upload_tabs' ) );
			//add_filter( 'media_upload_mime_type_links', '__return_empty_array' );
			add_action( 'media_upload_library' , array( $this, 'the_js' ), 15 );
			add_action( 'admin_head', array( $this, 'media_css' ) );
			add_action('admin_print_scripts', array( $this, 'dequeue_offending_scripts' ));
		}
	}
	// dequeue scripts that break the image uploader.
	function dequeue_offending_scripts() {
		wp_enqueue_style( 'dashicons' );
		// nextgen gallery destroys media uploader. 
		wp_dequeue_script( 'frame_event_publisher' );
	}

	function media_css() {

		?>
		<style type="text/css">
		#media-upload #filter, #media-upload #media-items {
		width: 770px;
		}
		.pl_uploader_close { float:right;}
		.pl_uploader_close:before {
			font-family: "dashicons";
		    content: "\f158";
		}
		</style>
		
		<script>
		jQuery(document).ready(function() {
			jQuery( '#sidemenu' ).append('<li class="pl_uploader_close"></li>')

		})
		</script>
		<?php
	}

	function the_js(){
		?>
		<script>
		jQuery(document).ready(function(){
			jQuery('.pl-frame-button').on('click', function(){
			
				var oSel = parent.jQuery.pl.iframeSelector
				,	optID = '#' + oSel
				,	imgURL = jQuery(this).data('imgurl')
				,	imgURLShort = jQuery(this).data('short-img-url')
				, 	theOption = jQuery( '[id="'+oSel+'"]', top.document) 
				,	thePreview = theOption.closest('.upload-box').find('.opt-upload-thumb')
				
				theOption.val( imgURLShort )
				
				thePreview.html( '<div class="img-wrap"><img style="max-width:150px;max-height: 100px;" src="'+ imgURL +'" /></div>' )
				
				
				parent.eval('jQuery(".bootbox").modal("hide")')				
			})
			
			jQuery( '.pl_uploader_close' ).click(function() {
				parent.eval('jQuery(".bootbox").modal("toggle")')	
			})
		})
		</script>

		<?php
	}

	/**
	 * Replace default attachment actions with "Set as header" link.
	 *
	 * @since 3.4.0
	 */
	function attachment_fields_to_edit( $form_fields, $post ) {

		$form_fields = array();

		$attach_id = $post->ID;

		
		$image_url = wp_get_attachment_url( $attach_id );
		$short_img_url = pl_shortcodize_url( $image_url );

		$form_fields['buttons'] = array(
			'tr' => sprintf(
						'<tr class="submit"><td></td>
							<td>
							<span class="pl-frame-button admin-blue button" data-selector="%s" data-imgurl="%s" data-short-img-url="%s">%s</span>
							</td></tr>',
							$this->option_id,
							$image_url,
							$short_img_url,
							__( 'Select This For Option', 'pagelines' )
					)
		);
		$form_fields['context'] = array(
			'input' => 'hidden',
			'value' => 'pl-custom-attach'
		);
		$form_fields['oid'] = array(
			'input' => 'hidden',
			'value' => $this->option_id
		);

		return $form_fields;
		
	}

	/**
	 * Leave only "Media Library" tab in the uploader window.
	 *
	 * @since 3.4.0
	 */
	function filter_upload_tabs( $tabs ) {
		return array( 
			'library' => __('Your Media Library', 'pagelines'),
		);
	}
}