<?php




/*
 * Less handler needs to compile live on 'publish' and load in the header of the website
 *
 * It needs to grab variables (or create a filter) that can be added by settings, etc.. (typography)

 * Inline LESS will be used to handle draft mode, and previewing of changes.

 */

class EditorLessHandler{

	var $pless_vars = array();
	var $draft;
	var $pless;
	var $lessfiles;

	function __construct( PageLinesLess $pless ) {

		if( pl_less_dev() )
			return;

		$this->pless = $pless;
		$this->lessfiles = get_core_lessfiles();
		$this->draft_less_file = sprintf( '%s/editor-draft.css', pl_get_css_dir( 'path' ) );
		$this->draft_less_url = sprintf( '%s/editor-draft.css', pl_get_css_dir( 'url' ) );

		if( pl_draft_mode() ){
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_draft_css' ) );
			add_action( 'wp_print_styles', array( $this, 'dequeue_live_css' ), 12 );
			add_action( 'template_redirect', array( $this, 'pagelines_draft_render' ) , 9);
			add_action( 'wp_footer', array( $this, 'print_core_less') );
			add_action( 'admin_notices', array( $this, 'check_last_error' ) );
		}

	}

	function check_last_error() {

		$error = get_theme_mod( 'less_last_error' );

		if( ! current_user_can('manage_options' ) || false ===  get_theme_mod( 'less_last_error' ) )
			return;

		if( false === ( strpos( $error, 'filesystem' ) ) ) {
			// css error...
			$message = sprintf( 'DMS Less System encountered an error<br /><kbd>%s</kbd>', $error );
		} else {
			// this mssage can be suppressed.
			if( true === pl_setting( 'disable_less_errors' ) )
				return;

			$message = sprintf( "It appears your WordPress install can't write to your servers file system. This may adversely affect your sites performance. Check with your host about resolving this issue.<br />To hide these notices, visit PageLines options panel.<br /><i>%s</i>", $error );
		}
		printf( '<div class="updated fade"><p>%s</p></div>', $message );
	}

	/**
	 *
	 *  Dequeue the regular css.
	 *
	 */
	static function dequeue_live_css() {
		wp_deregister_style( 'pagelines-less-sections' );
		wp_deregister_style( 'pagelines-less-core' );
		wp_deregister_style( 'pagelines-less-legacy' );
	}

	/**
	 * Output raw core less into footer for use with less.js
	 * Will output the same LESS that is used when compiling with PHP
	 * Allows for all custom variables, mixins, as well as any filtered/overriden files
	 */
	function print_core_less() {

		$core_less = $this->pless->add_constants('');
		$core_less .= $this->pless->add_bootstrap();

		printf('<div id="pl_core_less" style="display:none;">%s</div>', $core_less );
	}

	/**
	 *
	 *  Display Draft Less.
	 *
	 *  @package PageLines DMS
	 *  @since 3.0
	 */
	function pagelines_draft_render() {

		if( isset( $_GET['pagedraft'] ) ) {

			global $post;
			$this->compare_less();

			pl_set_css_headers();

			if( is_file( $this->draft_less_file ) ) {
				echo pl_file_get_contents( $this->draft_less_file );
			} else {
				$core = $this->get_draft_core();

				$css = pl_css_minify( $core['compiled_core'] );
				$css .= pl_css_minify( $core['compiled_sections'] );

				$this->write_draft_less_file( $css );
				echo $css;
			}
			die();
		}
	}

	/**
	 *
	 * Enqueue special draft css.
	 *
	 *  @package PageLines DMS
	 *  @since 3.0
	 */
	function enqueue_draft_css() {

		if( is_file( $this->draft_less_file ) ) {

			$url = str_replace( array( 'http://', 'https://' ), '//', $this->draft_less_url );
			wp_register_style( 'pagelines-draft',  $url, false, pl_get_cache_key(), 'all' );
		} else {
			// make url safe.
			global $post;

			$url = ( is_object( $post ) && ! is_front_page() ) ? trailingslashit( get_permalink( $post->ID ) ) : trailingslashit( site_url() );

			wp_register_style( 'pagelines-draft',  add_query_arg( array( 'pagedraft' => 1 ), $url ), false, null, 'all' );


		}
		wp_enqueue_style( 'pagelines-draft' );
	}


	/**
	 *
	 *  Build our 'data' for compile.
	 *
	 */
	public function draft_core_data() {


			$data = array(
				'sections'	=> get_all_active_sections(),
				'core'		=> get_core_lesscode( $this->lessfiles )
			);

			return $data;
	}

	/**
	 *
	 *  Main draft function.
	 *  Fetches data from a cache and compiles befor returning to EditorLess.
	 *
	 *  @package PageLines DMS
	 *  @since 3.0
	 */
	public function get_draft_core() {

		$raw				= pl_cache_get( 'draft_core_raw', array( $this, 'draft_core_data' ) );
		$compiled_core		= pl_cache_get( 'draft_core_compiled', array( $this, 'compile' ), array( $raw['core'] ) );
		$compiled_sections	= pl_cache_get( 'draft_sections_compiled', array( $this, 'compile' ), array( $raw['sections'] ) );

		return array(
			'compiled_core'	=> $compiled_core,
			'compiled_sections'	=> $compiled_sections,
			);
	}

	/**
	 *
	 *  Compare Less
	 *  If PL_LESS_DEV is active compare cached draft with raw less, if different purge cache, this fires before the less is compiled.
	 *
	 *  @package PageLines DMS
	 *  @since 3.0
	 */
	function compare_less() {

		global $dms_cache;

		$cached_constants = (array) pl_cache_get('pagelines_less_vars' );

		$diff = array_diff( $this->pless->constants, $cached_constants );

		if( ! empty( $diff ) ){

			// cache new constants version
			pl_cache_put( $this->pless->constants, 'pagelines_less_vars');

			// force recompile
			$dms_cache->purge('draft');
		}


		if( pl_draft_mode() )  {

			$check = false;

			if( defined( 'PL_LESS_DEV' ) && true == PL_LESS_DEV )
				$check = true;

			if( 1 == pl_setting( 'less_dev_mode' ) )
				$check = true;

			if( ! $check )
				return;

			$raw_cached = pl_cache_get( 'draft_core_raw', array( $this, 'draft_core_data' ) );

			// check if a cache exists. If not dont bother carrying on.
			if( isset( $raw_cached['core'] ) ){
				// Load all the less. Not compiled.
				$raw = $this->draft_core_data();

				if( $raw_cached['core'] != $raw['core'] )
					$dms_cache->purge('draft');

				if( $raw_cached['sections'] != $raw['sections'] )
					$dms_cache->purge('draft');
			}
		}
	}

	/**
	 *
	 *  Compile less into css.
	 *
	 *  @package PageLines DMS
	 *  @since 3.0
	 */
	public function compile( $data ) {
		do_action( 'pagelines_max_mem' );
		return $this->pless->raw_less( $data );
	}



	function write_draft_less_file($css) {

		$folder = pl_get_css_dir( 'path' );
		$file = 'editor-draft.css';

		pl_css_write_file( $folder, $file, $css );
	}
}





/**
 *
 *  PageLines Less Language Parser
 *
 *  @package PageLines DMS
 *	@subpackage Less
 *  @since 2.0.b22
 *
 */
class PageLinesLess {

	private $lparser = null;
	public $constants = '';

	/**
     * Establish the default LESS constants and provides a filter to over-write them
     *
     * @uses    pl_hashify - adds # symbol to CSS color hex values
     * @uses    page_line_height - calculates a line height relevant to font-size and content width
     */
	function __construct() {

		if( pl_less_dev() )
			return;

		global $less_vars;

		// PageLines Variables
		$constants = array(
			'plRoot'				=> sprintf( "\"%s\"", PL_PARENT_URL ),
			'plCrossRoot'			=> sprintf( "\"//%s\"", str_replace( array( 'http://','https://' ), '', PL_PARENT_URL ) ),
			'plSectionsRoot'		=> sprintf( "\"%s\"", PL_SECTION_ROOT ),
			'plPluginsRoot'			=> sprintf( "\"%s\"", WP_PLUGIN_URL ),
			'plChildRoot'			=> sprintf( "\"%s\"", PL_CHILD_URL ),
			'plExtendRoot'			=> sprintf( "\"%s\"", PL_EXTEND_URL ),
			'plPluginsRoot'			=> sprintf( "\"%s\"", plugins_url() ),
		);

		if(is_array($less_vars))
			$constants = array_merge($less_vars, $constants);


		$this->constants = apply_filters('pless_vars', $constants);
	}


	public function raw_less( $lesscode, $type = 'core' ) {

		return $this->raw_parse($lesscode, $type);
	}

	private function raw_parse( $pless, $type ) {

		require_once( PL_INCLUDES . '/less.plugin.php' );

		if( ! $this->lparser )
			$this->lparser = new plessc();

		$pless = $this->add_constants( '' ) . $this->add_bootstrap() . $pless;

		try {
			$css = $this->lparser->compile( $pless );
		} catch ( Exception $e) {
			plupop( "pl_less_error_{$type}", $e->getMessage() );
			return sprintf( "/* LESS PARSE ERROR in your %s CSS: %s */\r\n", ucfirst( $type ), $e->getMessage() );
		}

		// we're good!
		plupop( "pl_less_error_{$type}", false );
		return $css;
	}

	function add_constants( $pless ) {

		$prepend = '';

		foreach($this->constants as $key => $value)
			$prepend .= sprintf('@%s:%s;%s', $key, $value, "\n");

		return $prepend . $pless;
	}

	function add_bootstrap( ) {
		$less = '';

		$less .= load_less_file( 'variables' );
		$less .= load_less_file( 'colors' );
		$less .= load_less_file( 'mixins' );

		return $less;
	}

	// private function add_core_less($pless){
	//
	// 	global $disabled_settings;
	//
	// 	$add_color = (isset($disabled_settings['color_control'])) ? false : true;
	// 	$color = ($add_color) ? pl_get_core_less() : '';
	// 	return $pless . $color;
	// }



}








class PageLinesRenderCSS {

	var $lessfiles;
	var $types;
	var $ctimeout;
	var $btimeout;
	var $blog_id;

	function __construct() {

		if( pl_less_dev() )
			return;

		global $blog_id;
		$this->url_string = '%s/?pageless=%s';
		$this->ctimeout = 86400;
		$this->btimeout = 604800;
		$this->types = array( 'sections', 'core', 'custom' );
		$this->lessfiles = get_core_lessfiles();
		self::actions();
	}

	/**
	 *
	 *  Dynamic mode, CSS is loaded to a file using wp_rewrite
	 *
	 */
	private function actions() {

		add_filter( 'query_vars', array( $this, 'pagelines_add_trigger' ) );
		add_action( 'template_redirect', array( $this, 'pagelines_less_trigger' ) , 15);
		add_action( 'template_redirect', array( $this, 'less_file_mode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_less_css' ) );
		add_action( 'pagelines_head_last', array( $this, 'draw_inline_custom_css' ) , 25 );

		add_action( 'extend_flush', array( $this, 'flush_version' ), 1 );
		add_filter( 'pagelines_insert_core_less', array( $this, 'pagelines_insert_core_less_callback' ) );
		add_action( 'admin_notices', array( $this,'less_error_report') );
		add_action( 'wp_before_admin_bar_render', array( $this, 'less_css_bar' ) );

		if ( defined( 'PL_CSS_FLUSH' ) )
			do_action( 'extend_flush' );
		do_action( 'pagelines_max_mem' );

	}

	function minify( $css ){
		return pl_css_minify( $css );
	}

	function less_file_mode() {

		global $blog_id;
		if ( ! get_theme_mod( 'pl_save_version' ) ) {
			set_theme_mod( 'pl_save_version', time() );
		}

		if( defined( 'LESS_FILE_MODE' ) && false == LESS_FILE_MODE )
			return pl_less_save_last_error( 'LESS_FILE_MODE is set to false', false );

		if( defined( 'PL_NO_DYNAMIC_URL' ) && true == PL_NO_DYNAMIC_URL )
			return pl_less_save_last_error( 'PL_NO_DYNAMIC_URL is set to true', false );

		$folder = pl_get_css_dir( 'path' );
		$url = pl_get_css_dir( 'url' );

		if( '1' == pl_setting( 'alternative_css' ) )
			$file = 'compiled-css-core.css';
		else
			$file = sprintf( 'compiled-css-core-%s.css', get_theme_mod( 'pl_save_version' ) );;

		if( file_exists( trailingslashit( $folder ) . $file ) ){
			define( 'DYNAMIC_FILE_URL', trailingslashit( $url ) );
			return;
		}

		$a = $this->get_compiled_core();
		$b = $this->get_compiled_sections();
		$out = '';
		$out .= pl_css_minify( $a['core'] );
		if ( is_multisite() )
			$blog = sprintf( ' on blog [%s]', $blog_id );
		else
			$blog = '';

		$mem = ( function_exists('memory_get_usage') ) ? round( memory_get_usage() / 1024 / 1024, 2 ) : 0;
		$out .= sprintf( __( '%s/* CSS was compiled at %s and took %s seconds using %sMB of unicorn dust%s.*/', 'pagelines' ), "\n", date( DATE_RFC822, $a['time'] ), $a['c_time'], $mem, $blog );

		$this->write_css_file( 'core', $out );

		$this->write_css_file( 'sections', $b['sections'] );
	}

	function write_css_file( $area, $css ){

		$folder = pl_get_css_dir( 'path' );

		if( '1' == pl_setting( 'alternative_css' ) )
			$file = sprintf( 'compiled-css-%s.css', $area );
		else
			$file = sprintf( 'compiled-css-%s-%s.css', $area, get_theme_mod( 'pl_save_version' ) );

		$check = pl_css_write_file( $folder, $file, $css );

		if( $check ) {
			$url = pl_get_css_dir( 'url' );
			if( ! defined( 'DYNAMIC_FILE_URL') )
				define( 'DYNAMIC_FILE_URL', $url );

			pl_less_save_last_error( '', true );
		}
	}

	function less_css_bar() {
		foreach ( $this->types as $t ) {
			if ( pl_setting( "pl_less_error_{$t}" ) ) {

				global $wp_admin_bar;
				$wp_admin_bar->add_menu( array(
					'parent' => false,
					'id' => 'less_error',
					'title' => sprintf( '<span class="label label-warning pl-admin-bar-label">%s</span>', __( 'LESS Compile error!', 'pagelines' ) ),
					'href' => admin_url( PL_SETTINGS_URL ),
					'meta' => false
				));
				$wp_admin_bar->add_menu( array(
					'parent' => 'less_error',
					'id' => 'less_message',
					'title' => sprintf( __( 'Error in %s Less code: %s', 'pagelines' ), $t, pl_setting( "pl_less_error_{$t}" ) ),
					'href' => admin_url( PL_SETTINGS_URL ),
					'meta' => false
				));
			}
		}
	}

	function less_error_report() {

		$default = '<div class="updated fade update-nag"><div style="text-align:left"><h4>PageLines %s LESS/CSS error.</h4>%s</div></div>';

		foreach ( $this->types as $t ) {
			if ( pl_setting( "pl_less_error_{$t}" ) )
				printf( $default, ucfirst( $t ), pl_setting( "pl_less_error_{$t}" ) );
		}
	}

	/**
	 *
	 * Get custom CSS
	 *
	 *  @package PageLines DMS
	 *  @since 2.2
	 */
	function draw_inline_custom_css() {
		// always output this, even if empty - container is needed for live compile
		$a = $this->get_compiled_custom();
		return inline_css_markup( 'pagelines-custom', rtrim( pl_css_minify( $a['custom'] ) ) );
	}

	/**
	 *
	 *  Enqueue the dynamic css file.
	 *
	 *  @package PageLines DMS
	 *  @since 2.2
	 */
	function load_less_css() {

		if( defined( 'DYNAMIC_FILE_URL' ) ) {

			$area = 'core';

			if( '1' == pl_setting( 'alternative_css' ) )
				$file = sprintf( '/compiled-css-%s.css', $area );
			else
				$file = sprintf( '/compiled-css-%s-%s.css', $area, get_theme_mod( 'pl_save_version' ) );

			$url = pl_get_css_dir( 'url' ) . $file;

			if( '1' == pl_setting( 'alternative_css' ) )
				wp_enqueue_style( 'pagelines-less-core',  $this->get_dynamic_url( $url ), false, get_theme_mod( 'pl_save_version' ), 'all' );
			else
				wp_enqueue_style( 'pagelines-less-core',  $this->get_dynamic_url( $url ), false, null, 'all' );



			$area = 'sections';
			if( '1' == pl_setting( 'alternative_css' ) )
				$file = sprintf( '/compiled-css-%s.css', $area );
			else
				$file = sprintf( '/compiled-css-%s-%s.css', $area, get_theme_mod( 'pl_save_version' ) );

			$url = pl_get_css_dir( 'url' ) . $file;
			if( '1' == pl_setting( 'alternative_css' ) )
				wp_enqueue_style( 'pagelines-less-sections',  $this->get_dynamic_url( $url ), false, get_theme_mod( 'pl_save_version' ), 'all' );
			else
				wp_enqueue_style( 'pagelines-less-sections',  $this->get_dynamic_url( $url ), false, null, 'all' );
		} else {
			wp_enqueue_style( 'pagelines-less-legacy',  $this->get_dynamic_url( null ), false, null, 'all' );
		}

	}

	function get_dynamic_url( $url ) {

		global $blog_id;
		$version = get_theme_mod( "pl_save_version" );

		if ( ! $version )
			$version = '1';

		$id = ( is_multisite() ) ? $blog_id : '1';

		$version = sprintf( '%s_%s', $id, $version );

		$parent = apply_filters( 'pl_parent_css_url', PL_PARENT_URL );

		if ( ! defined( 'DYNAMIC_FILE_URL' ) )
			$url = add_query_arg( 'pageless', $version, trailingslashit( site_url() ) );

		if ( has_action( 'pl_force_ssl' ) )
			$url = str_replace( 'http://', 'https://', $url );

		return apply_filters( 'pl_dynamic_css_url', $url );
	}

	function get_base_url() {

		if(function_exists('icl_get_home_url')) {
		    return icl_get_home_url();
		  }

		return get_home_url();
	}

	/**
	 *
	 *  Get compiled/cached CSS
	 *
	 *  @package PageLines DMS
	 *  @since 2.2
	 */
	function get_compiled_core() {

		if ( ! pl_draft_mode() && is_array( $a = get_transient( 'pagelines_core_css' ) ) ) {
			return $a;
		} else {

			$start_time = microtime(true);

			unset( $this->lessfiles[ array_search( 'pl-editor', $this->lessfiles ) ] );

			$core_less = get_core_lesscode( $this->lessfiles );

			$pless = new PagelinesLess();

			$core_less = $pless->raw_less( $core_less  );

			$end_time = microtime(true);
			$a = array(
				'core'		=> $core_less,
				'c_time'	=> round(($end_time - $start_time),5),
				'time'		=> time()
			);
			if ( strpos( $core_less, 'PARSE ERROR' ) === false ) {
				set_transient( 'pagelines_core_css', $a, $this->ctimeout );
				set_transient( 'pagelines_core_css_backup', $a, $this->btimeout );
				return $a;
			} else {
				pl_less_save_last_error( $core_less, false );
				return get_transient( 'pagelines_core_css_backup' );
			}
		}
	}

	/**
	 *
	 *  Get compiled/cached CSS
	 *
	 *  @package PageLines DMS
	 *  @since 2.2
	 */
	function get_compiled_sections() {

		if ( ! pl_draft_mode() && is_array( $a = get_transient( 'pagelines_sections_css' ) ) ) {
			return $a;
		} else {

			$start_time = microtime(true);

			$sections = get_all_active_sections();

			$pless = new PagelinesLess();
			$sections =  $pless->raw_less( $sections, 'sections' );
			$end_time = microtime(true);
			$a = array(
				'sections'	=> $sections,
				'c_time'	=> round(($end_time - $start_time),5),
				'time'		=> time()
			);
			if ( strpos( $sections, 'PARSE ERROR' ) === false ) {
				set_transient( 'pagelines_sections_css', $a, $this->ctimeout );
				set_transient( 'pagelines_sections_css_backup', $a, $this->btimeout );
				return $a;
			} else {
				pl_less_save_last_error( $sections, false );
				return get_transient( 'pagelines_sections_css_backup' );
			}
		}
	}


	/**
	 *
	 *  Get compiled/cached CSS
	 *
	 *  @package PageLines DMS
	 *  @since 2.2
	 */
	function get_compiled_custom() {

		if ( ! pl_draft_mode() && is_array(  $a = get_transient( 'pagelines_custom_css' ) ) ) {
			return $a;
		} else {

			$start_time = microtime(true);

			$custom = stripslashes( pl_setting( 'custom_less' ) );

			$pless = new PagelinesLess();
			$custom =  $pless->raw_less( $custom, 'custom' );
			$end_time = microtime(true);
			$a = array(
				'custom'	=> $custom,
				'c_time'	=> round(($end_time - $start_time),5),
				'time'		=> time()
			);
			if ( strpos( $custom, 'PARSE ERROR' ) === false ) {
				set_transient( 'pagelines_custom_css', $a, $this->ctimeout );
				set_transient( 'pagelines_custom_css_backup', $a, $this->btimeout );
				return $a;
			} else {

				pl_less_save_last_error( $custom, false );
				return get_transient( 'pagelines_custom_css_backup' );
			}
		}
	}


	function pagelines_add_trigger( $vars ) {
	    $vars[] = 'pageless';
	    return $vars;
	}

	function pagelines_less_trigger() {
		global $blog_id;

		if( intval( get_query_var( 'pageless' ) ) ) {

			pl_set_css_headers();

			$defaults = array(
				'type'	=> '',
				'core'	=> '',
				'dynamic' => ''
			);
			$a = $this->get_compiled_core();
			$a = wp_parse_args( $a, $defaults );
			$b = $this->get_compiled_sections();



			$gfonts = preg_match( '#(@import[^;]*;)#', $a['type'], $g );

			if ( $gfonts ) {
				$a['core'] = sprintf( "%s\n%s", $g[1], $a['core'] );
				$a['type'] = str_replace( $g[1], '', $a['type'] );
			}

			echo pl_css_minify( $a['core'] );
			echo pl_css_minify( $b['sections'] );
			echo pl_css_minify( $a['type'] );
			echo pl_css_minify( $a['dynamic'] );

			$mem = ( function_exists('memory_get_usage') ) ? round( memory_get_usage() / 1024 / 1024, 2 ) : 0;

			$blog = ( is_multisite() ) ? sprintf( ' on blog [%s]', $blog_id ) : '';

			echo sprintf( __( '%s/* CSS was compiled in legacy mode at %s and took %s seconds using %sMB of unicorn dust%s.*/', 'pagelines' ), "\n", date( DATE_RFC822, $a['time'] ), $a['c_time'],  $mem, $blog );

			die();
		}
	}

	/**
	 *
	 *  Flush rewrites/cached css
	 *
	 *  @package PageLines DMS
	 *  @since 2.2
	 */
	static function flush_version( $rules = true ) {

		$types = array( 'sections', 'core', 'custom' );

		$folder = trailingslashit( pl_get_css_dir( 'path' ) );

		// clean css files
		self::prune_css_files();

		// Attempt to flush super-cache and w3 cache.

		if( function_exists( 'prune_super_cache' ) ) {
			global $cache_path;
			$GLOBALS["super_cache_enabled"] = 1;
        	prune_super_cache( $cache_path . 'supercache/', true );
        	prune_super_cache( $cache_path, true );
		}

		if( $rules )
			flush_rewrite_rules( true );

		set_theme_mod( 'pl_save_version', time() );

		$types = array( 'sections', 'core', 'custom' );

		foreach( $types as $t ) {

			$compiled = get_transient( "pagelines_{$t}_css" );
			$backup = get_transient( "pagelines_{$t}_css_backup" );

			if ( ! is_array( $backup ) && is_array( $compiled ) && strpos( $compiled[$t], 'PARSE ERROR' ) === false )
				set_transient( "pagelines_{$t}_css_backup", $compiled, 604800 );

			delete_transient( "pagelines_{$t}_css" );
		}
	}
	/**
	 * Prune all css files
	 */
	static function prune_css_files() {
		$folder = trailingslashit( pl_get_css_dir( 'path' ) );
		$files = glob( $folder . '*.css' );
			foreach ($files as $file) {
					unlink($file);
			}
	}

	function pagelines_insert_core_less_callback( $code ) {

		global $pagelines_raw_lesscode_external;
		$out = '';
		if ( is_array( $pagelines_raw_lesscode_external ) && ! empty( $pagelines_raw_lesscode_external ) ) {

			foreach( $pagelines_raw_lesscode_external as $file ) {

				if( is_file( $file ) )
					$out .= pl_file_get_contents( $file );
			}
			return $code . $out;
		}
		return $code;
	}



} //end of PageLinesRenderCSS
