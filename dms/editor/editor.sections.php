<?php



class PageLinesSectionsHandler{

	var $user_sections_slug = 'pl-user-sections';

	function __construct(){
		$this->url = PL_PARENT_URL . '/editor';


		add_filter( 'pl_ajax_set_user_section', array( $this, 'edit_custom_section' ), 10, 2 );

		add_filter( 'pl_load_page_settings', array( $this, 'add_user_section_settings_to_page') );


		$this->custom = new PLCustomSections;
	}



	function load_ui_actions(){

		add_filter('pl_toolbar_config', array( $this, 'toolbar'));
		add_action('pagelines_editor_scripts', array( $this, 'scripts'));

	}

	function scripts(){
		wp_enqueue_script( 'pl-js-sections', $this->url . '/js/pl.sections.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function toolbar( $toolbar ){

		$panel = array(
			'heading'	=> __( "<i class='icon icon-random'></i> Drag to Add", 'pagelines' ),
			'add_section'	=> array(
				'name'	=> __( 'Your Sections', 'pagelines' ),
				'icon'	=> 'icon-random',
				'clip'	=> __( 'Drag on to page to add', 'pagelines' ),
				'tools'	=> sprintf( '<button class="btn btn-mini btn-reload-sections"><i class="icon icon-repeat"></i> %s</button>', __( 'Reload Sections', 'pagelines' ) ),
				'type'	=> 'call',
				'call'	=> array( $this, 'add_new_callback'),
				'filter'=> '*'
			),
			'more_sections'	=> array(
				'name'	=> __( 'Get More Sections', 'pagelines' ),
				'icon'	=> 'icon-download',
				'flag'	=> 'link-storefront'
			),
			'heading2'	=> __( "<i class='icon icon-filter'></i> Filters", 'pagelines' ),

		);

		$sorted_panel = array(
			'components'		=> array(
				'name'	=> __( 'Components', 'pagelines' ),
				'pos'	=> 20,
				'href'	=> '#add_section',
				'filter'=> '.component',
				'icon'	=> 'icon-circle-blank'
			),

			'layouts'		=> array(
				'name'	=> __( 'Layouts', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.layout',
				'icon'	=> 'icon-columns'
			),
			'full-width'	=> array(
				'name'	=> __( 'Full Width', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.full-width',
				'icon'	=> 'icon-resize-horizontal'
			),
			'formats'		=> array(
				'name'	=> __( 'Post Layouts', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.format',
				'icon'	=> 'icon-th'
			),
			'galleries'		=> array(
				'name'	=> __( 'Galleries', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.gallery',
				'icon'	=> 'icon-camera'
			),
			'navigation'	=> array(
				'name'	=> __( 'Navigation', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.nav',
				'icon'	=> 'icon-circle-arrow-right'
			),
			'sliders'		=> array(
				'name'	=> __( 'Sliders', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.slider',
				'icon'	=> 'icon-picture'
			),
			'social'	=> array(
				'name'	=> __( 'Social', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.social',
				'icon'	=> 'icon-comments'
			),
			'widgets'	=> array(
				'name'	=> __( 'Widgetized', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.widgetized',
				'icon'	=> 'icon-retweet'
			),
			'custom'	=> array(
				'name'	=> __( 'Custom', 'pagelines' ),

				'href'	=> '#add_section',
				'filter'=> '.custom-section',
				'icon'	=> 'icon-dropbox'
			),
			'misc'		=> array(
				'name'	=> __( 'Miscellaneous', 'pagelines' ),
				'href'	=> '#add_section',
				'filter'=> '.misc',
				'icon'	=> 'icon-star',
				'pos'	=> 150
			),
		);

		$default = array(
			'icon'		=> 'icon-edit',
			'pos'		=> 100,
			'filter'	=> '*'
		);

		if( empty( $this->custom->objects ) )
			unset( $sorted_panel['custom'] );

		$sorted_panel = apply_filters('pl_section_filters', $sorted_panel );



		$pos = 90;
		foreach( $sorted_panel as $key => &$info ){
			if( ! isset( $info['pos'] ) )
				$info['pos'] = $pos++;
		}
		unset( $info);

		uasort( $sorted_panel, "cmp_by_position" );

		$toolbar['add-new'] = array(
			'name'	=> __( 'Add To Page', 'pagelines' ),
			'icon'	=> 'icon-plus-sign',
			'pos'	=> 20,
			'panel'	=> array_merge( $panel, $sorted_panel )
		);

		return apply_filters( 'pl_toolbar_components', $toolbar );
	}

	function add_new_callback(){
		$this->xlist = new EditorXList;
		//$this->extensions = new EditorExtensions;
		$this->page = new PageLinesPage;

		$filter_local = array(
		    'component' => __( 'Components', 'pagelines' ),
			'layout'    => __( 'Layouts', 'pagelines' ),
			'full-width' => __( 'Full Width', 'pagelines' ),
			'format'    => __( 'Post Layouts', 'pagelines' ),
			'gallery'  => __( 'Galleries', 'pagelines' ),
			'navigation' => __( 'Navigation', 'pagelines' ),
			'slider'    => __( 'Sliders', 'pagelines' ),
			'social'     => __( 'Social', 'pagelines' ),
			'widgetized'    => __( 'Widgetized', 'pagelines' ),
			'misc'       => __( 'Miscellaneous', 'pagelines' ),
		);

		$sections = $this->get_available_sections();

		$list = '';
		$count = 1;
		foreach($sections as $key => $s){

			$img = sprintf('<img src="%s" style=""/>', $s->screenshot);

			if($s->map != ''){
				$map = json_encode( $s->map );
				$special_class = 'section-plcolumn';
			} else {
				$map = '';
				$special_class = '';
			}

			if($s->filter == 'deprecated')
				continue;

			$full_width_classes = 'pl-area-sortable area-tag';
			$content_width_classes = 'pl-sortable span12 sortable-first sortable-last';

			$full_width = ( strpos($s->filter,'full-width') !== false ) ? true : false;
			$content_width = ( strpos($s->filter,'content-width') !== false ) ? true : false;
			$dual_loading = ( strpos($s->filter,'dual-width') !== false ) ? true : false;

			$section_classes = ( $full_width ) ? $full_width_classes : $content_width_classes;



			$name = stripslashes( $s->name );

			$desc = array();

			// Start Class Array (all classes on section icon)
			$class = array('x-add-new');

			$class['section'] = $section_classes;
			$class['special'] = $special_class;

			$filters = explode(',', $s->filter);

			foreach($filters as $f){

				$class[ 'filter-'.$f ] = $f;

				$desc[] = ( isset($filter_local[trim($f)]) ) ? $filter_local[trim($f)] : ucwords(trim($f));

			}

			$desc = join( ', ', $desc );

			$number = $count++;

			if( !empty($s->isolate) ){
				$disable = true;
				foreach($s->isolate as $isolation){
					if($isolation == 'posts_pages' && $this->page->is_posts_page()){
						$disable = false;
					} elseif ($isolation == '404_page' && is_404()){
						$disable = false;
					} elseif ( $isolation == 'single' && !$this->page->is_special() ){
						$disable = false;
					} elseif ( $isolation == 'author' && 'author' == $this->page->type() ){
						$disable = false;
					}
				}

				if( $disable ) {
					$class['disable'] = 'x-disable';
					$number += 100;
				}

			}

			if( !pl_is_pro() && !empty($s->sinfo['edition']) && !empty($s->sinfo['edition']) == 'pro' ){

				$class['disable'] = 'x-disable';
				$desc = __( '<span class="badge badge-important">PRO ONLY</span>', 'pagelines' );
			}

			$data_array = array(
				'object' 	=> $s->class_name,
				'sid'		=> $s->id,
				'name'		=> $name,
				'image'		=> $s->screenshot,
				'clone'		=> pl_new_clone_id(),
				'number' 	=> $number,
				
			);

			if( !empty($s->loading) )
				$class['loading'] = 'loading-'.$s->loading;

			if( !empty( $s->ctemplate ) ){
				$class['custom'] = 'custom-section';
				$data_array['custom-section'] = $s->ctemplate;
			}

			if( !empty( $map ) ){
				$data_array['template'] = $map;
			}


			$args = array(
				'id'			=> $s->id,
				'class_array' 	=> $class,
				'data_array'	=> $data_array,
				'thumb'			=> $s->screenshot,
				'name'			=> $name,
				'sub'			=> ( $full_width ) ? __( 'Full Width', 'pagelines' ) : __( 'Content Width', 'pagelines' ),
				'docs_url'			=> ( isset( $s->docs_url ) ) ? $s->docs_url : false
			);


			$list .= $this->xlist->get_x_list_item( $args );

			if( $dual_loading ){
				$args['class_array']['section'] = $full_width_classes;
				$args['class_array'][] = 'full-width';
				$args['name'] = $name.' (Full)';
			
				$args['sub'] = __( 'Full Width', 'pagelines' );
				$list .= $this->xlist->get_x_list_item( $args );
			}

		}

		printf('<div class="x-list x-sections" data-panel="x-sections">%s</div>', $list);

	}

	function get_available_sections(){

		global $pl_section_factory;
		return array_merge( $pl_section_factory->sections, $this->layout_sections(), $this->custom->render_user_sections() );
	}

	function edit_custom_section( $response, $data ){

		if( $data['run'] == 'create' ){

			$response['key'] = $this->custom->create( $data['config'] );


		} elseif( $data['run'] == 'delete'){
			$response['delete'] = $this->custom->delete( $data['key'] );
		}


		return $response;
	}



	/*
	 * Parse the page map for user sections
	 * Replaces them with the appropriate map, and sets up a settings array for use with page settings.
	 */
	function replace_user_sections( $map ){

		$this->all_user_section_settings = array();

		foreach( $map as &$region ){
			if( is_array( $region) ){

					foreach( $region as $area_index => &$area){

						if( isset($area['ctemplate']) && $area['ctemplate'] != ''  ){

							$ctemplate = $this->custom->retrieve( $area['ctemplate'] );


							if( ! empty($ctemplate) ){

								$settings = ( isset($ctemplate['settings']) ) ? $ctemplate['settings'] : array();

								$area  = wp_parse_args( $ctemplate['map'], $area );

								$this->all_user_section_settings = array_merge( $this->all_user_section_settings, $settings );

							} else {
								// if usection isn't defined, then its been deleted or something.
								unset( $region[ $area_index ] );
							}


						}

					}
					unset($area);

			}

		}
		unset($region);

		return $map;

	}

	function get_user_section_settings(){

		return $this->all_user_section_settings;

	}

	/*
	 * Called via pl_load_page_settings filter in main settings class.
	 * Adds the compiled list of section settings created when parsing the map for user sections.
	 */
	function add_user_section_settings_to_page( $page_settings ){
		$new_page_settings = wp_parse_args( $this->get_user_section_settings(), $page_settings );

		return $new_page_settings;

	}





	function layout_sections(){

		$the_layouts = array(
			array(
				'id'			=> 'pl_split_column',
				'name'			=> '2 Columns - Split',
				'filter'		=> 'layout',
				'screenshot'	=>  PL_IMAGES . '/thumb-2column.png',
				'thumb'			=>  PL_IMAGES . '/thumb-2column.png',
				'splash'		=>  PL_IMAGES . '/splash-2column.png',
				'map'			=> array(
									array(
										'object'	=> 'PLColumn',
										'span' 		=> 6,
										'newrow'	=> true
									),
									array(
										'object'	=> 'PLColumn',
										'span' 	=> 6
									),
								)
			),
			array(
				'id'			=> 'pl_3_column',
				'name'			=> '3 Columns',
				'filter'		=> 'layout',
				'description'	=> 'Loads three equal width columns for placing sections.',
				'screenshot'	=>  PL_IMAGES . '/thumb-3column.png',
				'thumb'			=>  PL_IMAGES . '/thumb-3column.png',
				'splash'		=>  PL_IMAGES . '/splash-3column.png',
				'map'			=> array(
									array(
										'object'	=> 'PLColumn',
										'span' 		=> 4,
										'newrow'	=> true
									),
									array(
										'object'	=> 'PLColumn',
										'span' 	=> 4
									),
									array(
										'object'	=> 'PLColumn',
										'span' 	=> 4
									),
								)
			),
		);

		return pl_array_to_object( $the_layouts, pl_section_config_default() );

	}



}

function pl_custom_section_name( $key ){
	global $sections_handler;

	return $sections_handler->custom->retrieve_field( $key, 'name' );
}

function pl_section_config_default(){
	$defaults = array(
		'id'			=> '',
		'name'			=> __( 'No Name', 'pagelines' ),
		'filter'		=> 'misc',
		'description'	=> __( 'No description given.', 'pagelines' ),
		'screenshot'	=>  PL_IMAGES . '/thumb-missing.png',
		'splash'		=>  PL_IMAGES . '/splash-missing.png',
		'class_name'	=> '',
		'map'			=> '',
		'docs_url'			=> false
	);

	return $defaults;
}
class PLCustomSections extends PLCustomObjects{

	function __construct(  ){

		$this->slug = 'pl-user-sections';

		$this->objects = $this->get_all();
	}


	function render_user_sections(){

		$rendered = array();

		foreach( $this->objects as $key => $i){

			$name = ( isset($i['name']) ) ? $i['name'] : __( 'No Name', 'pagelines' );
			$desc = ( isset($i['desc']) ) ? $i['desc'] : __( 'No Description Entered.', 'pagelines' );

			$rendered[ $key ] = array(
				'id'			=> $key,
				'name'			=> $name,
				'object'		=> 'PLSectionArea',
				'description'	=> $desc,
				'filter'		=> 'custom-section, full-width',
				'ctemplate'		=> $key,
				'screenshot'	=>  PL_IMAGES . '/section-user.png',
				'thumb'			=>  PL_IMAGES . '/section-user.png',
			);

		}


		return pl_array_to_object( $rendered, pl_section_config_default() );
	}

}



