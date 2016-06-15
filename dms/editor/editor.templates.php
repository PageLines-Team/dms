<?php

class PageLinesTemplates {

	var $map_option_slug = 'pl-template-map';

	function __construct( EditorTemplates $tpl ){

		$this->tpl = $tpl;
		
		$this->mode = pl_get_mode();
	
		global $plpg; 
		$this->page = $plpg;
		
		$this->set = new PageLinesOpts;
		
		add_filter( 'pl_load_page_settings', array( $this, 'add_template_settings_to_page') );
	}
	
	function add_template_settings_to_page( $page_settings ){
		
		global $pl_custom_template;
		
		$template_settings = ( ! empty( $pl_custom_template ) ) ? $pl_custom_template['settings'] : array();
		
		$new_page_settings = wp_parse_args( $template_settings, $page_settings );
		
		return $new_page_settings;
		
	}
	

	function get_map( ){

		global $sections_handler;
		global $pl_custom_template; 
		
		$pl_custom_template = false;

		$map['fixed'] = $this->get_region( 'fixed' );
		$map['header'] = $this->get_region( 'header' );
		$map['footer'] = $this->get_region( 'footer' );
		$map['template'] = $this->get_region( 'template' );
		
		
		
		$map = $sections_handler->replace_user_sections( $map );
		
		
		
		return $map;

	}
	
	

	function get_region( $region ){
		
		if( $region == 'header' || $region == 'footer' || $region == 'fixed' ){
			
			$map = $this->set->regions; 
				
		} elseif( $region == 'template' ){
			
			$map = false;
			
			$set = ($this->page->template_mode() == 'local') ? $this->set->local : $this->set->type;

				
			
			if( isset( $set['custom-map'] ) && is_array( $set['custom-map'] ) ){
				
			
				$map = $set['custom-map'];
				
				
	
				if( isset( $map[ $region ]['ctemplate'] ) ){
					
					$key = $map[ $region ]['ctemplate'];
					
					global $pl_custom_template;
					
					$pl_custom_template = $this->tpl->handler->retrieve( $key ); 
					
					if( $pl_custom_template && !empty($pl_custom_template)){
						
						$pl_custom_template['key'] = $key;

						$map[ $region ] = $pl_custom_template['map'];
						
					} else 
						$map = false;					
				}
					

			} elseif( is_page() && isset( $this->set->global['page-template']) ){
				
				$key = $this->set->global['page-template']; 
				$map = $this->tpl->handler->retrieve_field( $key, 'map'); 
				
			}
				
			
		}
		
		
		
		$region_map = ( $map && isset($map[ $region ]) ) ? $map[ $region ] : $this->default_region( $region );		

		return $region_map;
		
	}
	
	function upgrade_navbar_settings(){
		
		$settings = array(
			'navbar_theme'			=> pl_setting('fixed_navbar_theme' ),
			'navbar_alignment'		=> pl_setting('fixed_navbar_alignment' ),
			'navbar_hidesearch'		=> pl_setting('fixed_navbar_hidesearch' ),
			'navbar_menu'			=> pl_setting('fixed_navbar_menu' ),
			'navbar_enable_hover'	=> pl_setting('fixed_navbar_enable_hover' ),
			'navbar_logo'			=> pl_setting('navbar_logo' ),
		);
		
		return $settings;
	}
	

	function default_region( $region ){
		
		$d = array();
		
		if( $region == 'header' ){
			
			$d = array( array( 'content' => array( ) ) );
			
		} elseif( $region == 'fixed' ){
			
			$nav = ( pl_is_wporg() ) ? 'PLNavBar' : 'PLNavi';
			
			$d = array( 
					array( 
						'object' 	=> $nav,
					) 
				);
			
		} elseif( $region == 'footer' ){
			
			$d = array(
				array(
					'content'	=> array(
						array(
							'object' => 'SimpleNav'
						)
					)
				)

			);
			
		} elseif( $region == 'template' ){
			
			$d = array( pl_default_template() );
			
		}
		
		return $d;

		
	}

}

class EditorTemplates {

	var $template_slug = 'pl-user-templates';
	var $default_template_slug = 'pl-default-tpl';
	var $map_option_slug = 'pl-template-map';
	var $template_id_slug = 'pl-template-id';


	var $page_template_slug = 'pl-page-template'; 

	function __construct( ){
	
		global $plpg;
		$this->page = $plpg;

		$this->default_type_tpl = ($plpg && $plpg != '') ? pl_local( $plpg->typeid, 'page-template' ) : false;

		$this->default_global_tpl = pl_global( 'page-template' );

		$this->default_tpl = ( $this->default_type_tpl ) ? $this->default_type_tpl : $this->default_global_tpl;

		$this->url = PL_PARENT_URL . '/editor';

		$this->handler = new PLCustomTemplates;

		add_filter('pl_toolbar_config', array( $this, 'toolbar'));
		add_filter('pagelines_editor_scripts', array( $this, 'scripts'));

		add_action( 'admin_init', array( $this, 'admin_page_meta_box'));
		add_action( 'post_updated', array( $this, 'save_meta_options') );
		
		add_filter( 'pl_ajax_set_template', array( $this, 'set_template' ), 10, 2 );
		
	

	}
	
	
	function set_template( $response, $data ){
		
		$run = $data['run'];
		
		if ( $run == 'update'){
		
			$response['key'] = $this->handler->update( $data['key'], $data['config'] );

		} elseif ( $run == 'delete'){

			$response['key'] = $this->handler->delete( $data['key'] );

		} elseif ( $run == 'create' ){

			$response['key'] = $this->handler->create( $data['config'] );

		} 
		// set editing scope
		elseif ( $run == 'template_mode'){

			$value = $data['value'];
			
			$pageID = $data['pageID'];
			
			$key = 'pl_template_mode';
			
			pl_meta_update( $pageID, $key, $value );

			$response['result'] = pl_meta( $pageID, $key);

		} elseif( $run == 'set_global' ){

			$field = 'page-template';
			$value = $data['value'];

			$previous_val = pl_global( $field );

			if($previous_val == $value)
				pl_global_update( $field, false );
			else
				pl_global_update( $field, $value );


			$response['result'] = pl_global( $field );

		}
		$response['hi'] = 'hello';
		
		
		return $response;
	}

	function scripts(){
		wp_enqueue_script( 'pl-js-mapping', $this->url . '/js/pl.mapping.js', array('jquery'), pl_get_cache_key(), true);
		wp_enqueue_script( 'pl-js-templates', $this->url . '/js/pl.templates.js', array( 'jquery' ), pl_get_cache_key(), true );
	}

	function toolbar( $toolbar ){
		
		
		$toolbar['page-setup'] = array(
			'name'	=> __( 'Page Setup', 'pagelines' ),
			'icon'	=> 'icon-file-text',
			'pos'	=> 30,
			'panel'	=> array(
				
				'heading2'	=> __( "Page Setup", 'pagelines' ),
				'tmp_load'	=> array(
					'name'	=> __( 'Templates', 'pagelines' ),
					'call'	=> array( $this, 'user_templates'),
					'icon'	=> 'icon-copy',
					'filter' => '*'
				),
				'tmp_save'	=> array(
					'name'	=> __( 'Page Handling', 'pagelines' ),
					'call'	=> array( $this, 'page_controls'),
					'icon'	=> 'icon-info'
				),
			)

		);

		return $toolbar;
	}

	function user_templates(){
		$slug = $this->default_template_slug;
		$this->xlist = new EditorXList;
		$templates = '';
		$list = '';
		$tpls = pl_meta( $this->page->id, $this->map_option_slug, pl_settings_default());

		$custom_template_handler = new PLCustomTemplates;

		$all_templates = $custom_template_handler->get_all();
		
		$count = 1;
		$cols = 6;
		$num = count( $all_templates );
		
		foreach( $all_templates as $index => $template){


			$classes = array( sprintf('template_key_%s', $index) );

			$action_classes = array('x-item-actions'); 
			
			global $pl_custom_template; 

			if(! empty( $pl_custom_template ) ){
				$action_classes[] = ($index === $pl_custom_template['key']) ? 'active-template' : '';
			}
			
			$action_classes[] = ($index === $this->default_global_tpl) ? 'active-global' : '';
			$action_classes[] = ($index === $this->default_type_tpl && !$this->page->is_special()) ? 'active-type' : '';
			

			ob_start();
			
			echo pl_grid_tool('row_start', $cols, $count, $num, 'pl-list-row');
			?>
			
			<div class="row span<?php echo $cols;?> pl-template-row <?php echo join(' ', $classes); ?>" data-key="<?php echo $index;?>">
				
				<div class="span8 list-head">
					<div class="list-title"><?php echo stripslashes( $template['name'] ); ?></div>
					<div class="list-desc">
						<?php echo stripslashes( $template['desc'] ); ?>
					</div>
				</div>
				<div class="span4 list-actions">
					<div class="<?php echo join(' ', $action_classes);?>">

						<button class="btn btn-mini btn-primary load-template"><?php _e( 'Load', 'pagelines' ); ?>
						</button>

						<button class="btn btn-mini btn-important the-active-template"><?php _e( 'Active', 'pagelines' ); ?>
						</button>

						<div class="btn-group dropup">
						  <a class="btn btn-mini dropdown-toggle actions-toggle" data-toggle="dropdown" href="#">
						    <i class="icon icon-caret-down"></i>
						  </a>
							<ul class="dropdown-menu">
								<li ><a class="update-template">
								<i class="icon icon-edit"></i> <?php _e( 'Update Template with Current Configuration', 'pagelines' ); ?>

								</a></li>

								<li><a class="set-tpl" data-run="global">
								<i class="icon icon-globe"></i> <?php _e( 'Set as Page Global Default', 'pagelines' ); ?>

								</a></li>

								<li><a class="delete-template">
								<i class="icon icon-remove"></i> <?php _e( 'Delete This Template', 'pagelines' ); ?>

								</a></li>

							</ul>
						</div>
						<button class="btn btn-mini tpl-tag global-tag tt-top" title="Current Sitewide Default"><i class="icon icon-globe"></i></button>
						<button class="btn btn-mini tpl-tag posttype-tag tt-top" title="Current Post Type Default"><i class="icon icon-pushpin"></i></button>
					</div>
				</div>
			</div>

			<?php
			
			echo pl_grid_tool('row_end', $cols, $count, $num);
			
			$count++;

			$list .= ob_get_clean();




		}

		


		ob_start(); 
		?>

		<form class="opt standard-form form-save-template">
			<fieldset>
				<h4><?php _e( 'Save Current Page As New Template', 'pagelines' ); ?>
				</h4>
				</span>
				<label for="template-name"><?php _e( 'Template Name (required)', 'pagelines' ); ?>
				</label>
				<input type="text" id="template-name" name="name" required />

				<label for="template-desc"><?php _e( 'Template Description', 'pagelines' ); ?>
				</label>
				<textarea rows="4" id="template-desc" name="desc" ></textarea>
				
				<button type="submit" class="btn btn-primary btn-save-template"><?php _e( 'Save New Template', 'pagelines' ); ?>
				</button>
			</fieldset>
		</form>

		<?php
		
		$form = ob_get_clean();
		
		printf('<div class="row"><div class="span8"><div class="pl-list-contain">%s</div></div><div class="span4">%s</div></div>', $list, $form);
	}
	
	function page_information(){
		
		global $plpg;
		
		$info['template-mode'] = array(
			'num'		=> $this->handling_selector(),
			'label'		=> '',
			'title'		=> __( 'Template Handling', 'pagelines' ),
			'info'		=> __( 'The scope of template layout handling. Local applies only on this page, type applies across all of same type.', 'pagelines' )
		);
		
		$info['template'] = array(
			'num'		=> $plpg->template(),
			'label'		=> '',
			'title'		=> __( 'Template', 'pagelines' ),
			'info'		=> __( 'The ID of the current template being used.', 'pagelines' )
		);
		
		$info['type'] = array(
			'num'		=> $plpg->type,
			'label'		=> '',
			'title'		=> __( 'Current Page Type', 'pagelines' ),
			'info'		=> __( 'The classification of WordPress page.', 'pagelines' )
		);
		
		$info['typeid'] = array(
			'num'		=> $plpg->typeid,
			'label'		=> '',
			'title'		=> __( 'Current Type ID', 'pagelines' ),
			'info'		=> __( 'A meta ID associated with this type of page. Used for settings for entire type.', 'pagelines' )
		);
		
		$info['id'] = array(
			'num'		=> $plpg->id,
			'label'		=> '',
			'title'		=> __( 'Current Page ID', 'pagelines' ),
			'info'		=> __( 'A meta ID associated with this specific page.', 'pagelines' )
		);
		
		
		return $info;
		
	}
	
	function handling_selector(){
		global $plpg;
		$mode = $plpg->template_mode();
		
		ob_start();
		?>
		<div class="template-mode-selector">
			<select class="template-mode-selector-select">
				<option value="local" <?php if($mode == 'local') echo 'selected'; ?>>LOCAL - Layout is current page specific</option>
				<option value="type" <?php if($mode == 'type') echo 'selected'; ?>>TYPE - Layout is current page type specific</option>
			</select>
			<a class="btn btn-mini btn-primary template-mode-selector-update">Update</a>
		</div>
		
		<?php 
		
		return ob_get_clean();
	}

	function page_controls(){
		global $plpg;
		?>
		<div class="row">
			<div class="controls-table">
				<table class="data-table">
				<?php foreach( $this->page_information() as $key => $item ){
					
					printf('<tr><th>%s</th><td>%s %s</td><td>%s</td></tr>', $item['title'], $item['num'], $item['label'], $item['info']); 
					
					
				}?>
				</table>
				
			</div>
		</div>
		<?php

	}
	
	
	function admin_page_meta_box(){
		remove_meta_box( 'pageparentdiv', 'page', 'side' );
			
		add_meta_box('specialpagelines', __( 'DMS Page Setup', 'pagelines' ), array( $this, 'page_attributes_meta_box'), 'page', 'side');

	}

	/* 
	 * Used for WordPress Post Saving of PageLines Template
	 */ 
	function save_meta_options( $postID ){
		$post = $_POST;
		if((isset($post['update']) || isset($post['save']) || isset($post['publish']))){


			$user_template = (isset($post['pagelines_template'])) ? $post['pagelines_template'] : '';

			if($user_template != ''){
				
				pl_set_page_template( $postID, $user_template, 'both' );
				
			}


		}
	}
	/* 
	 * Adds PageLines Template selector when creating page/post
	 */
	function page_attributes_meta_box( $post ){
		global $pl_custom_template;

		$post_type_object = get_post_type_object($post->post_type);

		///// CUSTOM PAGE TEMPLATE STUFF /////

			$options = '<option value="">Select Template</option>';
			
			$set = pl_meta($post->ID, PL_SETTINGS);

			$current = ( is_array( $set ) && isset( $set['live']['custom-map']['template']['ctemplate'] ) ) ? $set['live']['custom-map']['template']['ctemplate'] : '';

			$custom_template_handler = new PLCustomTemplates;

			foreach( $custom_template_handler->get_all() as $index => $t){

				$sel = '';

				$sel = ( $current === $index ) ? 'selected' : '';
				
				$options .= sprintf('<option value="%s" %s>%s</option>', $index, $sel, $t['name']);
			}

			printf('<p><strong>%1$s</strong></p>', __('Load PageLines Template', 'pagelines'));

			printf('<select name="pagelines_template" id="pagelines_template">%s</select>', $options);

		///// END TEMPLATE STUFF /////


		if ( $post_type_object->hierarchical ) {
			$dropdown_args = array(
				'post_type'        => $post->post_type,
				'exclude_tree'     => $post->ID,
				'selected'         => $post->post_parent,
				'name'             => 'parent_id',
				'show_option_none' => __('(no parent)', 'pagelines' ),
				'sort_column'      => 'menu_order, post_title',
				'echo'             => 0,
			);

			$dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );
			$pages = wp_dropdown_pages( $dropdown_args );
			if ( ! empty($pages) ) {
				printf('<p><strong>%1$s</strong></p>', __( 'Parent Page', 'pagelines' ) );
				echo $pages;
			}
		}

		printf('<p><strong>%1$s</strong></p>', __( 'Page Order', 'pagelines' ) );
		printf('<input name="menu_order" type="text" size="4" id="menu_order" value="%s" /></p>', esc_attr($post->menu_order) );
	}
}

class PLCustomTemplates extends PLCustomObjects{
	
	function __construct(  ){
		
		$this->slug = 'pl-user-templates';
		
		$this->objects = $this->get_all();
		
	}
	
	function default_objects(){

		$t = array();

		$t[ 'default' ] = array(
				'name'	=> __( 'Default', 'pagelines' ),
				'desc'	=> __( 'Standard page configuration. (Content and Primary Sidebar.)', 'pagelines' ),
				'map'	=> array(
					'template' => pl_default_template( true )
				)
			);

		$t[ 'feature' ] = array(
			'name'	=> __( 'Feature Template', 'pagelines' ),
			'desc'	=> __( 'A page template designed to quickly and concisely show off key features or points. (RevSlider, iBoxes, Flipper)', 'pagelines' ),
			'map'	=> array(
				array(
					'object'	=> 'plRevSlider',
				),
				array(
					'content'	=> array(
						array(
							'object'	=> 'pliBox',

						),
						array(
							'object'	=> 'PageLinesFlipper',

						),
					)
				)
			)
		);

		$t[ 'landing' ] = array(
				'name'	=> __( 'Landing Page', 'pagelines' ),
				'desc'	=> __( 'A simple page design with highlight section and postloop (content).', 'pagelines' ),
				'map'	=> array(
					'template' => array(
						'area'	=> 'TemplateAreaID',
						'content'	=> array(
							array(
								'object'	=> 'PageLinesHighlight',
							),
							array(
								'object'	=> 'PageLinesPostLoop',
								'span'		=> 8, 
								'offset'	=> 2
							),

						)
					)
				)
		);

		return apply_filters('pl_default_templates', $t);
	}
}

function pl_default_template( $standard = false ){
	$sidebar = array(
		'object'	=> 'PLColumn',
		'span' 	=> 4,
		'content'	=> array(
			array(
				'object'	=> 'PLRapidTabs'
			),
			array(
				'object'	=> 'PrimarySidebar'
			),
		)
	);

	// 404 Page
	if( is_404() ){

		$content = array(
			array(
				'object'	=> 'PageLinesNoPosts',
				'span' 		=> 10,
				'offset'	=> 1
			)
		);

	} 
	
	// Standard WP page default
	elseif( is_page() ){

		$content = array(
			array(
				'object'	=> 'PageLinesPostLoop',
				'span' 		=> 10,
				'offset'	=> 1
			)
		);

	} 
	
	// Post Page 
	elseif( is_single() ) {

		$content = array(
					array(
						'object'	=> 'PLColumn',
						'span' 		=> 8,
						'content'	=> array(
							array(
								'object'	=> 'PageLinesPostLoop'
							),
							array(
								'object'	=> 'PageLinesComments'
							)
						)
					),
					
					$sidebar 
				);

	} 
	
	
	// Overall Default 
	else {
		$content = array( 
					array(
						'object'	=> 'PLColumn',
						'span' 	=> 8,
						'content'	=> array(
							array(
								'object'	=> 'PageLinesPostLoop'
								),
							array(
								'object'	=> 'PageLinesPagination'
								)
						)
					),
					$sidebar
				);
	}


	return apply_filters( 'pl_default_template_handler', array( 'content' => $content ) );

}

function pl_add_or_update_template( $args ){
	$tpls = new PLCustomTemplates;
	
	$key = $tpls->create( $args );
	
	return $key;
}

function pl_set_page_template( $metaID, $key, $mode = 'both' ){
	
	// set local meta
	
	$map = array();
	
	$map['template']['ctemplate'] = $key;
	
	pl_update_single_meta_setting( $metaID, 'custom-map', $map, $mode );

	
}


