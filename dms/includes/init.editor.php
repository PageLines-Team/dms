<?php
/**
 * This file initializes the PageLines Editor
 *
 * @package PageLines DMS
 * @since 3.0.0
 *
 */

global $pagelines_editor;
$pagelines_editor = new PageLinesEditor;

class PageLinesEditor {

	function __construct() {

		$this->load_files();

		// TEMPLATE ACTIONS
		// !important - must load after $post variable
		// ALSO, bbPress and other plugins make adjustments to queries which change pages from 404 to something else.
		// Therefore must come after WP_Query (parse query)

		// workaround for customizer in WP >4.3
		// default was 'wp' but customizer needs the libs loaded on 'init'
		if( is_customize_preview() ) {
			$hook = 'init';
		} else {
			$hook = 'wp';
		}

		add_action( $hook, array( $this, 'load_libs' ), 10);
		add_action( 'admin_init', array( $this, 'load_libs' ), 5);

		add_filter( 'parse_request', array( $this, 'check_for_type' ) );

		add_action('wp_enqueue_scripts', array( $this, 'process_styles' ));

		add_action( 'wp_head', array( $this, 'process_head' ) );
		add_action( 'wp_footer', array( $this, 'process_foot' ) );

		// RENDER SECTION TEMPLATES ACTIONS
		add_action( 'pagelines_fixed', array( $this, 'process_fixed' ) );
		add_action( 'pagelines_header', array( $this, 'process_header' ) );
		add_action( 'pagelines_template', array( $this, 'process_template' ) );
		add_action( 'pagelines_footer', array( $this, 'process_footer' ) );
	}

	function load_files(){

		require_once( PL_EDITOR . '/editor.settings.php' );
		require_once( PL_EDITOR . '/editor.actions.php' );
		require_once( PL_EDITOR . '/editor.draft.php' );
		require_once( PL_EDITOR . '/editor.layout.php' );
		require_once( PL_EDITOR . '/editor.saving.php' );

		require_once( PL_EDITOR . '/editor.settings.config.php' );
		require_once( PL_EDITOR . '/editor.typography.php' );
		require_once( PL_EDITOR . '/editor.importexport.php' );
		require_once( PL_EDITOR . '/editor.color.php' );

		// User objects base class
		require_once( PL_EDITOR . '/editor.objects.php' );
		require_once( PL_EDITOR . '/editor.section.data.php' );

		require_once( PL_EDITOR . '/editor.templates.php' );

		// Mobile
		require_once( PL_EDITOR . '/mobile.menu.php' );

		// Interfaces
		require_once( PL_EDITOR . '/editor.xlist.php' );
		require_once( PL_EDITOR . '/panel.code.php' );

		require_once( PL_EDITOR . '/editor.updates.php' );

		require_once( PL_EDITOR . '/editor.sections.php' );
		require_once( PL_EDITOR . '/panel.extend.php' );
		require_once( PL_EDITOR . '/panel.themes.php' );

		require_once( PL_EDITOR . '/panel.settings.php' );

		require_once( PL_EDITOR . '/editor.extensions.php' );
		require_once( PL_EDITOR . '/editor.interface.php' );
		require_once( PL_EDITOR . '/editor.integrations.php' );
		require_once( PL_EDITOR . '/editor.regions.php' );
		require_once( PL_EDITOR . '/editor.areas.php' );
		require_once( PL_EDITOR . '/editor.page.php' );
		require_once( PL_EDITOR . '/editor.handler.php' );
		require_once( PL_EDITOR . '/editor.loader.php' );
		require_once( PL_EDITOR . '/editor.help.php' );
		require_once( PL_EDITOR . '/editor.api.php' );
		require_once( PL_EDITOR . '/editor.fileopts.php' );
		require_once( PL_EDITOR . '/editor.sections.register.php' );
	}


	function check_for_type( $wp ) {

		global $pl_404;
		if( isset( $wp->query_vars['pagename']) && false !== strpos($wp->query_vars['pagename'], 'members' ) ) {
			$pl_404 = false;
		} else {
			$pl_404 = true;
		}
	}

	function load_libs(){


		if( ! pl_use_editor() )
			return;

		global $plpg;
		global $pldraft;
		global $plopts;
		global $editorless;
		global $storeapi;
		global $fileopts;
		global $sections_handler;
		global $templates_handler;
		global $sections_data_handler;

		$plpg = $this->page = new PageLinesPage;
		$pldraft = $this->draft = new EditorDraft( $this->page );
		$storeapi = $this->storeapi = new EditorStoreFront;
		$this->layout = new EditorLayout();

		$sections_data_handler = new PLSectionData;

		$templates_handler = new EditorTemplates;

		// Mapping
		$this->map = new PageLinesTemplates( $templates_handler ); // this needs to be rewritten and moved to mapping class

		$this->saving_utility = new PageLinesSave;

		// Must come before settings
		$this->foundry = new PageLinesFoundry;
		$this->typography = new EditorTypography( $this->foundry );
		$this->importexport = new PLImportExport;
		$this->color = new EditorColor;
		$this->siteset = new EditorSettings;
		$this->extensions = new EditorExtensions;

		$less_engine = new PageLinesLESSEngine;

		$pless = new PageLinesLess;
		$fileOpts = new EditorFileOpts;
		$this->editor_less = new EditorLessHandler($pless);

		$loader = new PageLinesPageLoader;
		$help = new EditorHelpPanel;


		pagelines_register_hook('pl_after_settings_load'); // hook

		$plopts = $this->opts = new PageLinesOpts;

		// Mobile
		$this->mobile_menu = new PageLinesMobileMenu;

		// Interfaces
		$this->xlist = new EditorXList;
		$sections_handler = new PageLinesSectionsHandler;
		$sections_handler->load_ui_actions();
		$this->settings_panel = new PageLinesSettingsPanel;
		$this->themer = new EditorThemeHandler;
		$this->code = new EditorCode( $this->draft );
		$this->areas = new PageLinesAreas;

		// Editor UX Elements
		$this->interface = new EditorInterface( $this->page, $this->siteset, $this->draft, $templates_handler, $this->map, $this->extensions, $this->themer );

		// Master UX Handler
		$this->handler = new PageLinesTemplateHandler(
					$this->interface,
					$this->areas,
					$this->page,
					$this->siteset,
					$this->foundry,
					$this->map,
					$this->draft,
					$this->opts,
					$this->layout,
					$this->extensions

				);

	}

	function process_styles(){

		if( pl_draft_mode() )
			pagelines_add_bodyclass('pl-editor');

		$this->handler->process_styles();
	}

	function process_head(){

		if( ! is_object( $this->handler ) )
			return false;

		$this->handler->process_head();
	}

	function process_foot(){

			if( ! is_object( $this->handler ) )
				return false;

			$this->handler->process_foot();
	}

	function process_fixed(){

		if( ! is_object( $this->handler ) )
			return false;

		$this->handler->process_region('fixed');

	}

	function process_header(){

		if( ! is_object( $this->handler ) )
			return false;

		$this->handler->process_region('header');

	}
	function process_template(){

		$this->handler->process_region('template');

	}

	function process_footer(){

		$this->handler->process_region('footer');

	}


}
