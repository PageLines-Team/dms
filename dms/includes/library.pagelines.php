<?php
/**
 *  This file adds the main menu, free version must be under appearance, this file is NOT in the free version.
 *  @package PageLines DMS
 *  @since 1.0.1
 *	
 *	// set memory limit
 *	// dashboard menus
 *	// shortcodes extended
 */

add_filter( 'pagelines_global_notification', 'pagelines_check_pro_nag' );
function pagelines_check_pro_nag( $note ) {
	if( pl_is_pro_check() )
		return $note;
	ob_start();
	?>
	<div class="alert editor-alert">
		<button type="button" class="close" data-dismiss="alert" href="#">&times;</button>
	  	<strong><i class="icon icon-star"></i> <?php _e( 'Upgrade to Pro!', 'pagelines' ); ?>
	  	</strong> <br/>
		<?php _e( 'You are currently using the basic DMS version. Pro activate this site for tons more features and support.', 'pagelines' ); ?>
		
		<a href="http://www.pagelines.com/DMS" class="btn btn-mini" target="_blank"><i class="icon icon-thumbs-up"></i> <?php _e( 'Learn More About Pro', 'pagelines' ); ?>
		</a>
		</div>
		<?php
		$note .= ob_get_clean();
		return $note;
}
add_filter( 'pl_is_activated', 'pl_is_activated_check' );
add_filter( 'pl_is_pro', 'pl_is_pro_check' );
add_filter( 'pl_pro_text', 'pl_pro_text_check' );
add_filter( 'pl_pro_disable_class', 'pl_pro_disable_class_check' );

function pl_is_pro_check() {
	return true;
}

function pl_is_activated_check(){	
	// AP stop putting return true here!!
	$status = get_option( 'dms_activation', array( 'active' => false, 'key' => '', 'message' => '', 'email' => '' ) );
	$pro = (isset($status['active']) && true === $status['active']) ? true : false;
	return $pro;	
}

function pl_pro_text_check(){
	return ( ! pl_is_pro_check()) ? __('(Pro Edition Only)', 'pagelines') : '';
}

function pl_pro_disable_class_check(){
	return ( ! pl_is_pro_check()) ? 'pro-only-disabled' : ''; 
}



/**
 *  Boost memory for LESS compile
 *  @package PageLines DMS
 *  @since 1.2
 *
 */
add_action( 'pagelines_max_mem', create_function('',"@ini_set('memory_limit',WP_MAX_MEMORY_LIMIT);") );

/**
 *  Dashboard menus
 *  @package PageLines DMS
 *  @since 1.2
 *
 */
function pagelines_add_admin_menus() {

	global $_pagelines_account_hook;
	
	$_pagelines_account_hook = pagelines_insert_menu( PL_MAIN_DASH, __( 'Dashboard', 'pagelines' ), 'edit_theme_options', PL_MAIN_DASH, 'pagelines_build_account_interface' );

}

/**
 *
 * PageLines menu wrapper
 */
function pagelines_insert_menu( $page_title, $menu_title, $capability, $menu_slug, $function ) {

	return add_submenu_page( PL_MAIN_DASH, $page_title, $menu_title, $capability, $menu_slug, $function );

}

/**
 * Full version menu wrapper.
 *
 */
function pagelines_add_admin_menu() {
		global $menu;

		// Create the new separator
		$menu['2.995'] = array( '', 'edit_theme_options', 'separator-pagelines', '', 'wp-menu-separator' );

		// Create the new top-level Menu
 		add_menu_page( 'PageLines', 'PageLines', 'edit_theme_options', PL_MAIN_DASH, 'pagelines_build_account_interface', PL_PARENT_URL . '/images/admin-icon.png', '2.996' );
}

/**
 * Full version CSS File Write Function
 * Same as the version in less.functions used by the free version
 * Difference is if this function fails with WP_Filesystem it will fall back to regular file_put_contents()
 */
function pl_css_write_file( $folder, $file, $css ) {
	
	$failed = false;
	
	if( !is_dir( $folder ) ) {
		if( true !== wp_mkdir_p( $folder ) )
			return false;
	}
	add_filter('request_filesystem_credentials', '__return_true' );
	include_once( ABSPATH . 'wp-admin/includes/file.php' );
	if ( is_writable( $folder ) ){
		$creds = request_filesystem_credentials( site_url() );
		if ( ! WP_Filesystem($creds) )
			$failed = true;
	}
	global $wp_filesystem;
	if( is_object( $wp_filesystem ) && ! $failed ) {
		$c = $wp_filesystem->put_contents( trailingslashit( $folder ) . $file, $css, FS_CHMOD_FILE);
		if( ! $c )
			$failed = true;
	}
	
	if( $failed ) {
		//lets try file_put_contents then!
		$c = file_put_contents( trailingslashit( $folder ) . $file, $css );
		if( ! $c )
			return pl_less_save_last_error( 'Unable to access filesystem. Check file permissions on uploads dir. Even file_put_contents() failed on: ' . $folder, false );
	}
			
	return true; // file written
	}

/**
 *  The bulk of the PL Shortcodes.
 *  @package PageLines DMS
 *  @since 1.2
 *
 */
class PL_ShortCodes_Libs extends PageLines_ShortCodes {
	
	// 1. Return link in page based on Bookmark
	// USAGE : [bookmark id="21" text="Link Text"]
	function bookmark_link( $atts ) {

	 	//extract page name from the shortcode attributes
	 	extract( shortcode_atts( array( 'id' => '0', 'text' => '' ), $atts ) );

	 	//convert the page name to a page ID
	 	$bookmark = get_bookmark( $id );

		if( isset( $text ) ) $ltext = $text;
		else $ltext = $bookmark->link_name;


		$pagelink = "<a href=\"".$bookmark->link_url."\" target=\"".$bookmark->link_target."\">".$ltext."</a>";
	 	return $pagelink;
	}

	// 2. Function for creating a link from a page name
	// USAGE : [link pagename="My Example Page" linktext="Link Text"]
	function create_pagelink( $atts ) {

	 	//extract page name from the shortcode attributes
	 	extract( shortcode_atts( array( 'pagename' => 'home', 'linktext' => '' ), $atts ) );

	 	//convert the page name to a page ID
	 	$page = get_page_by_title( $pagename );

	 	//use page ID to get the permalink for the page
	 	$link = get_permalink( $page );

	 	//create the link and output
	 	$pagelink = "<a href=\"".$link."\">".$linktext."</a>";

	 	return $pagelink;
	}
	
	// 4. GOOGLE MAPS //////////////////////////////////////////////////

	    // you can use the default width and height
	    // The only requirement is to add the address of the map
	    // Example:
	    // [googlemap address="san diego, ca"]
	    // or with options
	    // [googlemap width="200" height="200" address="San Francisco, CA 92109"]
	function googleMaps( $atts, $content = null ) {

		 extract( shortcode_atts( array(

		 'width'	=> '480',
		 'height'	=> '480',
		 'locale'	=> 'en',
		 'address'	=> ''
	 ), $atts ) );
	 $src = "http://maps.google.com/maps?f=q&source=s_q&hl=".$locale."&q=".$address;
	 return '<iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$src.'&amp;output=embed"></iframe>';
	}

	// 5. GOOGLE CHARTS  //////////////////////////////////////////////////

		// Gets Google charts
		// USAGE
		//		[chart data="0,12,24,26,32,64,54,24,22,20,8,2,0,0,3" bg="F7F9FA" size="200x100" type="sparkline"]
		//		[chart data="41.52,37.79,20.67,0.03" bg="F7F9FA" labels="Reffering+sites|Search+Engines|Direct+traffic|Other" colors="058DC7,50B432,ED561B,EDEF00" size="488x200" title="Traffic Sources" type="pie"]

	function chart_shortcode( $atts ) {
		extract( shortcode_atts( array(
		    'data' => '',
		    'colors' => '',
		    'size' => '400x200',
		    'bg' => 'ffffff',
		    'title' => '',
		    'labels' => '',
		    'advanced' => '',
		    'type' => 'pie'
		), $atts ) );

				switch ( $type ) {
					case 'line' :
						$charttype = 'lc'; break;
					case 'xyline' :
						$charttype = 'lxy'; break;
					case 'sparkline' :
						$charttype = 'ls'; break;
					case 'meter' :
						$charttype = 'gom'; break;
					case 'scatter' :
						$charttype = 's'; break;
					case 'venn' :
						$charttype = 'v'; break;
					case 'pie' :
						$charttype = 'p3'; break;
					case 'pie2d' :
						$charttype = 'p'; break;
					default :
						$charttype = $type;
					break;
				}
				$string = '';
				if ( $title ) $string .= '&chtt='.$title.'';
				if ( $labels ) $string .= '&chl='.$labels.'';
				if ( $colors ) $string .= '&chco='.$colors.'';
				$string .= '&chs='.$size.'';
				$string .= '&chd=t:'.$data.'';
				$string .= '&chf=bg,s,'.$bg.'';

		return '<img title="'.$title.'" src="http://chart.apis.google.com/chart?cht='.$charttype.''.$string.$advanced.'" alt="'.$title.'" />';
	}

	// 6. GET POST FIELD BY OFFSET //////////////////////////////////////////////////
	// Get a post based on offset from the last post published (0 for last post)
	// USAGE: [postfeed field="post_title"  offset="0" customfield="true" ]
	function get_postfeed( $atts ) {

		//extract page name from the shortcode attributes
		extract( shortcode_atts( array( 'field' => 'post_title', 'offset' => '0', 'customfield' => "" ), $atts ) );

		//returns an array of objects
		$thepost = get_posts( 'numberposts=1&offset='.$offset );

		if( $customfield == 'true' ){
			$postfield = get_post_meta( $thepost[0]->ID, $field, true );
		}else{
			$postfield = $thepost[0]->$field;
		}
		return $postfield;
	}

	// 7. Created a container for dynamic html layout
	// USAGE: [cbox width="50%" leftgutter="15px" rightgutter="0px"] html box content[/cbox]
	function dynamic_box( $atts, $content = null ) {

	 	//extract page name from the shortcode attributes
	 	extract( shortcode_atts( array( 'width' => '30%', 'leftgutter' => '10px', 'rightgutter' => '0px' ), $atts ) );

	 	$cbox = '<div class="cbox" style="float:left;width:'.$width.';"><div class="cbox_pad" style="margin: 0px '.$rightgutter.' 0px '.$leftgutter.'">'.do_shortcode( $content ).'</div></div>';

	return $cbox;
	}

	// 8. Created a container for dynamic html layout
	// USAGE: [container id="mycontainer" class="myclass"] 'cboxes' see shortcode below [/container]
	function dynamic_container( $atts, $content = null ) {

	 	//extract page name from the shortcode attributes
	 	extract( shortcode_atts( array( 'id' => 'container', 'class' => '' ), $atts ) );

 		$container = '<div style="width: 100%;" class="container">'.do_shortcode( $content ).'<div class="clear"></div></div>';

	 	return $container;
	}
		
	/**
	 * 18.Shortcode to display Pinterest button
	 *
	 * @example <code>[pinterest_button img=""]</code> is the default usage
	 * @example <code>[pinterest_button img=""]</code>
	 */
	function pl_pinterest_button( $atts ){

			$defaults = array(
				'url' => get_permalink(),
				'img' => '',
				'title' => urlencode( the_title_attribute( array( 'echo' => false ) ) ),
			);

			$atts = shortcode_atts( $defaults, $atts );

			$out = sprintf( '<a href="http://pinterest.com/pin/create/button/?url=%s&amp;media=%s&amp;description=%s" class="pin-it-button" count-layout="horizontal"><img style="border:0px;" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>',
			$atts['url'],
			$atts['img'],
			$atts['title']
			);
			
			global $shortcode_js; 
			
			$shortcode_js['pinterest'] = '<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>';

			return $out;

		}

	/**
	 * 1X.Shortcode to display Google Plus 1 Button
	 *
	 * @example <code>[googleplus]</code> is the default usage
	 * @example <code>[googleplus size="" count=""]</code>
	 * @example available attributes for size include small, medium, and tall
	 * @example avialable counts include inline, and bubble
	 */
    function pl_googleplus_button ( $atts ) {

    	$defaults = array(
    		'size' => 'medium',
    		'count' => '',
    		'url' => get_permalink()

	    );

    	$atts = shortcode_atts($defaults, $atts);

    	ob_start();

		 printf( '<div class="g-plusone" data-size="%s" data-href="%s"></div>',
			$atts['size'],
			$atts['count'],
			$atts['url']
		);
		
		global $shortcode_js; 
		
		ob_start();
		
		?>
		<script type="text/javascript">
		  (function() {
		    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		    po.src = 'https://apis.google.com/js/platform.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		  })();
		</script>
		
		<?php 
		
		$shortcode_js['gplus'] = ob_get_clean();

		return ob_get_clean();
    }

	/**
	 * . Shortcode to display Linkedin Share Button
	 *
	 * @example <code>[linkedin]</code> is the default usage
	 * @example <code>[linkedin count="vertical"]</code>
	 */
    function pl_linkedinshare_button ($atts) {

			$defaults = array(
				'url'	=> get_permalink(),
				'count'	=> 'horizontal'
			);

			$atts = wp_parse_args( $atts, $defaults );


            $out = sprintf( '<script type="IN/Share" data-url="%s" data-counter="%s"></script>',
					$atts['url'],
					$atts['count']
				);
				
			global $shortcode_js; 

			$shortcode_js['linkedin'] = '<script src="//platform.linkedin.com/in.js" type="text/javascript"></script>';

           return $out;

    }

	/**
	 * 19. Shortcode to display Tweet button
	 *
	 * @example <code>[twitter_button type=""]</code> is the default usage
	 * @example <code>[twitter_button type="follow"]</code>
	 */
	function pl_twitter_button( $args ){

		$defaults = array(
			'type'      => '',
			'permalink'	=> get_permalink(),
			'handle'	=> ( pl_setting( 'twittername' ) ) ? pl_setting( 'twittername' ) : 'PageLines' ,
			'title'		=> ''
			);

			$a = wp_parse_args( $args, $defaults );

			if ($a['type'] == 'follow') {

				$out = sprintf( '<a href="https://twitter.com/%1$s" class="twitter-follow-button" data-show-count="true" data-show-screen-name="false">Follow @%1$s</a>',
					$a['handle']
						);

			} else {

				$out = sprintf( '<a href="https://twitter.com/share" class="twitter-share-button" data-url="%s" data-text="%s" data-via="%s">Tweet</a>',
					$a['type'],
					$a['permalink'],
					$a['title'],
					$a['handle']
					);
			}
			
			global $shortcode_js; 

			$shortcode_js['twitter'] = '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
			
			return $out;
	}

	/**
	 * 20. Shortcode to display Facebook Like button
	 *
	 * @example <code>[like_button]</code> is the default usage
	 * @example <code>[like_button]</code>
	 */
	function pl_facebook_shortcode( $args ){

			$defaults = array(
				'url'	=> get_permalink(),
				'width'		=> '80',
				'type'		=> 'like'
			);

			$a = wp_parse_args( $args, $defaults );

			$app_id = '';
			if( false !== pl_setting( 'facebook_app_id' ) )
				$app_id = sprintf( '&appId=%s', pl_setting( 'facebook_app_id' ) );
			else
				$app_id = sprintf( '&appId=%s', '244419892345248' );
			
			ob_start();
			?>
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&<?php echo $app_id; ?>&version=v2.0";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
			<?php
			
			$facebook_js = ob_get_clean();

			global $shortcode_js; 

			$shortcode_js['facebook'] = $facebook_js;
							
			return sprintf( '<div class="fb-%s" style="vertical-align: top;padding-right:8px" data-href="%s" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>',
					$a['type'],
					$a['url'],
					$a['width']
				);

				

		}

	/**
	 * 21. This function/shortcode will show all authors on a post
	 *
	 * @example <code>[show_authors]</code> is the default usage
	 * @example <code>[show_authors]</code>
	 */
	function show_multiple_authors() {

		if( class_exists( 'CoAuthorsIterator' ) ) {

			$i = new CoAuthorsIterator();
			$return = '';
			$i->iterate();
			$return .= '<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'">'.get_the_author_meta( 'display_name' ).'</a>';
			while( $i->iterate() ){
				$return.= $i->is_last() ? ' and ' : ', ';
				$return .= '<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'">'.get_the_author_meta( 'display_name' ).'</a>';
			}

			return $return;

		} else {
			//fallback
		}
	}

	/**
	 * 22.Bootstrap Code Shortcode
	 *
	 * @example <code>[pl_codebox]...[/pl_codebox]</code> is the default usage
	 * @example <code>[pl_codebox scrollable="yes"].box{margin:0 auto;}[/pl_codebox]</code> for lots of code
	 */

	function pl_codebox_shortcode ( $atts, $content = null ) {

	    extract( shortcode_atts( array(
			'scrollable' => 'no',
			'linenums' => 'yes',
			'language'	=> 'html'
		), $atts ) );

        $scrollable = ( $scrollable == 'yes' ) ? 'pre-scrollable' : '';
		$linenums = ( $linenums == 'yes' ) ? 'linenums' : '';
		$language = 'lang-'.$language;

		// Grab Shortcodes
		$pattern = array(

			'#([a-z]+\=[\'|"][^\'|"]*[\'|"])#m',
			'#(\[[^]]*])#m',

		);
		$replace = array(
			'<span class="sc_var">$1</span>',
			'<span class="sc_code">$1</span>'
		);

		$code = preg_replace( $pattern, $replace, esc_html( $content ) );

		$out = sprintf( '<pre class="%s prettyprint %s %s">%s</pre>',
					$scrollable,
					$language,
					$linenums,
					$code
				);

		return $out;
	}

	/**
	 * 23. Bootstrap Labels Shortcode
	 *
	 * @example <code>[pl_label type=""]My Label[/pl_label]</code> is the default usage
	 * @example <code>[pl_label type="info"]label[/pl_label]</code>
	 * @example Available types include info, success, warning, error
	 */
	function pl_label_shortcode( $atts, $content = null ) {

	    $defaults = array(
	    	'type' => 'info',
	    );

    	$atts = shortcode_atts( $defaults, $atts );

	    $out = sprintf( '<span class="label label-%s">%s</span>',
					$atts['type'],
					do_shortcode( $content )
				);

	    return $out;
	}

	/**
	 * 24. Bootstrap Badges Shortcode
	 *
	 * @example <code>[pl_badge type="info"]My badge[/pl_badge]</code> is the default usage
	 * @example <code>[pl_badge type="info"]badge[/pl_badge]</code>
	 * @example Available types include info, success, warning, error
	 */
	function pl_badge_shortcode( $atts, $content = null ) {

	    $defaults = array(
	    	'type' => 'info',
	    );

		$atts = shortcode_atts( $defaults, $atts );

	    $out = sprintf( '<span class="badge badge-%s">%s</span>',
					$atts['type'],
					do_shortcode( $content )
				);

	    return $out;
	}


	/**
	 * 25. Bootstrap Alertbox Shortcode
	 *
	 * @example <code>[pl_alertbox type="info"]My alert[/pl_alertbox]</code> is the default usage
	 * @example <code>[pl_alertbox type="info" closable="yes"]My alert[/pl_alertbox]</code> makes an alert that can be toggled away with a close button
	 * @example <code>[pl_alertbox type="info"]<h4 class="pl-alert-heading">Heading</h4>My alert[/pl_alertbox]</code>
	 * @example Available types include info, success, warning, error
	 */
	function pl_alertbox_shortcode( $atts, $content = null ) {

		$content = str_replace( '<br />', '', str_replace( '<br>', '', $content ) );

		$defaults = array(
				    'type' => 'info',
				    'closable' =>'no',
					);

        $atts = shortcode_atts( $defaults, $atts );

        $closed = sprintf( '<div class="alert alert-%s"><a class="close" data-dismiss="alert" href="#">×</a>%s</div>',
				$atts['type'],
				do_shortcode( $content )
		);

        if ( $atts['closable'] === 'yes' ) {

			return $closed;

    	}

	    $out = sprintf( '<div class="alert alert-%s alert-block">%2$s</div>',
					$atts['type'],
					do_shortcode( $content )
				);

		return $out;
	}

	/**
	 * 26. Bootstrap Blockquote Shortcode
	 *
	 * @example <code>[pl_blockquote pull="" cite=""]My quote[/pl_blockquote]</code> is the default usage
	 * @example <code>[pl_blockquote pull="right" cite="Someone Famous"]My quote pulled right with source[/pl_blockquote]</code>
	 */
	function pl_blockquote_shortcode( $atts, $content = null) {

		$defaults = array(
			'pull'	=> '',
			'cite'	=> ''
		);

		$atts = shortcode_atts( $defaults, $atts );

		$out = sprintf( '<blockquote class="side-%1$s"><p>%3$s<small>%2$s</small></p></blockquote>',
					$atts['pull'],
					$atts['cite'],
					do_shortcode( $content )
				);

		return $out;

	}

	/**
	 * 27. Bootstrap Button Shortcode
	 *
	 * @example <code>[pl_button type="" size="" link="" target=""]...[/pl_button]</code> is the default usage
	 * @example <code>[pl_button type="info" size="small" link="#" target="blank"]My Button[/pl_button]</code>
	 * @example Available types include info, success, warning, danger, inverse
	 * @example Available sizes include large, medium, and mini
	 */
	function pl_button_shortcode( $atts, $content = null, $target = null ) {

		$defaults = array(
			'type' => 'info',
			'size' => 'small',
			'link' => '#',
			'target' => '_self'
		);

		$atts = shortcode_atts( $defaults, $atts );

	    $target = ( $target == 'blank' ) ? '_blank' : '';

	    $out = sprintf( '<a href="%1$s" target="%2$s" class="btn btn-%3$s btn-%4$s">%5$s</a>',
					$atts['link'],
					$atts['target'],
					$atts['type'],
					$atts['size'],
					do_shortcode( $content )
					);

		return $out;
	}


	/**
	 * 28. Bootstrap Button Group Shortcode - Builds a group of buttons as a menu
	 *
	 * @example <code>[pl_buttongroup]<a href="#" class="btn btn-info">...[/pl_buttongroup]</code> is the default usage
	 * @example <code>[pl_buttongroup]<a href="#" class="btn btn-info"><a href="#" class="btn btn-info"><a href="#" class="btn btn-info">[/pl_button]</code>
	 * @example Available types include info, success, warning, danger, inverse
	 */
	function pl_buttongroup_shortcode( $atts, $content = null ) {

		$content = str_replace( '<br />', '', str_replace( '<br>', '', $content ) );

    	return sprintf( '<div class="btn-group">%s</div>', do_shortcode( $content ) );

	}


	/**
	 * 29. Bootstrap Dropdown Button Shortcode - Builds a button with contained dropdown menu
	 *
	 * @example <code>[pl_buttondropdown size="" type="" label=""]<li><a href="#">...</a></li>[/pl_buttondropdown]</code> is the default usage
	 * @example <code>[pl_buttondropdown size="large" type="info" label="button"]<li><a href="#"></li><li><a href="#"></li><li><a href="#"></li>[/pl_buttondropdown]</code>
	 * @example Available types include info, success, warning, danger, inverse
	 */
	function pl_buttondropdown_shortcode( $atts, $content = null  ) {

	    $defaults = array(
		    'size' => '',
		    'type' => '',
		    'label' => ''
		);

		$atts = shortcode_atts( $defaults, $atts );

	    $out = sprintf( '<div class="btn-group"><button class="btn btn-%s btn-%s dropdown-toggle" data-toggle="dropdown" href="#">%s <span class="caret"></span></button><ul  class="dropdown-menu">%s</ul></div>',
	        $atts['size'],
	      	$atts['type'],
	        $atts['label'],
			do_shortcode( $content )
	    );

	    return $out;
	}


	/**
	 * 30. Bootstrap Split Button Dropdown - Builds a button with split button dropdown caret
	 *
	 * @example <code>[pl_splitbuttondropdown size="" type="" label=""]<li><a href="#">...</a></li>[/pl_splitbuttondropdown]</code> is the default usage
	 * @example <code>[pl_splitbuttondropdown size="large" type="info" label="button"]<li><a href="#"></li><li><a href="#"></li><li><a href="#"></li>[/pl_splitbuttondropdown]</code>
	 * @example Available types include info, success, warning, danger, inverse
	 */
	function pl_splitbuttondropdown_shortcode( $atts, $content = null ) {

	    $defaults = array(
		    'size' => '',
		    'type' => '',
		    'label' => ''
	    );

		$atts = shortcode_atts( $defaults, $atts );

	    $out = sprintf( '<div class="btn-group"><a class="btn btn-%1$s btn-%2$s" >%3$s</a><a class="btn btn-%1$s btn-%2$s dropdown-toggle" data-toggle="dropdown"><span  class="caret"></span></a><ul class="dropdown-menu">%4$s</ul></div>',
	      	$atts['size'],
	        $atts['type'],
	        $atts['label'],
			do_shortcode( $content )
	    );

	    return $out;
	}

	 /**
	 * 31. Bootstrap Tooltips
	 *
	 * @example <code>[pl_tooltip tip=""]...[/pl_tooltip]</code> is the default usage
	 * @example <code>This is a [pl_tooltip tip="Cool"]tooltip[/pl_tooltip] example.</code>
	 */
	function pl_tooltip_shortcode( $atts, $content = null ) {

	    $defaults = array(
	    	'tip' => 'Tip',
	    	'position'  => 'right'
	    );

        $atts = shortcode_atts( $defaults, $atts );

		global $shortcode_js; 

		$shortcode_js['tooltips'] = '<script> jQuery(function(){ jQuery("a[rel=tooltip]").tooltip();}); </script>';

			ob_start();

			printf( '<a href="#" rel="tooltip" title="%s" data-placement="%s">%s</a>',
				$atts['tip'],
				$atts['position'],
				do_shortcode( $content )
			);

			return ob_get_clean();

	}

	/**
	 * 32. Bootstrap Popovers
	 *
	 * @example <code>[pl_popover title="" content=""]...[/pl_popover]</code> is the default usage
	 * @example <code>This is a [pl_popover title="Popover Title" content="Some content that you can have inside the Popover"]popover[/pl_popover] example.</code>
	 */
    function pl_popover_shortcode( $atts, $content = null ) {

	    $defaults = array(
	    	'title' => 'Popover Title',
	    	'content' => 'Content',
	    	'position'  => 'right'
	    );

	    $atts = shortcode_atts( $defaults, $atts );
	
			global $shortcode_js; 

			$shortcode_js['popover'] = '<script> jQuery(function(){ jQuery("a[rel=popover]").popover({ html:true, trigger: "hover" }).click(function(e) { e.preventDefault() }); }); </script>';

	    ob_start();


    	printf( '<a href="#" rel="popover" title="%s" data-content="%s" data-placement="%s">%s</a>',
			$atts['title'],
			$atts['content'],
			$atts['position'],
			do_shortcode( $content )
		);

    	return ob_get_clean();

	}


	/**
	 * 33. Bootstrap Accordion - Collapsable Content
	 *
	 * @example <code>[pl_accordion name="accordion"] [accordioncontent name="accordion" number="1" heading="Tile 1"]Content 1 [/accordioncontent] [accordioncontent name="accordion" number="2" heading="Title 2"]Content 2 [/accordioncontent] [/pl_accordion]</code> is the default usage
	 */
	function pl_accordion_shortcode( $atts, $content = null ) {

		$defaults = array(

			'name' => '',

		);

		$atts = shortcode_atts( $defaults, $atts );

		$out = sprintf( '<div id="%s" class="accordion">%s</div>',
		$atts['name'],
		do_shortcode( $content )
		);
	return $out;
	}

	//Accordion Content
	function pl_accordioncontent_shortcode( $atts, $content = null, $open = null ) {

	    $defaults = array(
		    'name' => '',
		    'heading' => '',
		    'number' => '',
		    'open' => ''
	    );

        $atts = shortcode_atts( $defaults, $atts );
		$open = ( $atts['open'] == 'yes' ) ? 'in' : '';
	    $out = sprintf( '<div class="accordion-group"><div class="accordion-heading pl-contrast"><a class="accordion-toggle" data-toggle="collapse" data-parent="#%1$s" href="#collapse%3$s">%2$s</a></div><div id="collapse%3$s" class="accordion-body collapse %4$s"><div class="accordion-inner">%5$s</div></div></div>',
	      	$atts['name'],
	        $atts['heading'],
	        $atts['number'],
	        $open,
			do_shortcode( $content )

	    );

	    return $out;
	}

	/**
	 * 34. Bootstrap Carousel
	 *
	 * @example <code>[pl_carousel name=""][pl_carouselimage first="yes" title="" imageurl="" ]Caption[/pl_carouselimage][pl_carouselimage title="" imageurl="" ]Caption[/pl_carouselimage][/pl_carousel]</code> is the default usage
	 * @example <code>[pl_carousel name="PageLinesCarousel"][pl_carouselimage first="yes" title="Feature 1" imageurl="" ]Image 1 Caption[/pl_carouselimage][pl_carouselimage title="Feature 2" imageurl=""]Image 2 Caption[/pl_carouselimage][pl_carouselimage title="Feature 3" imageurl=""]Image 3 Caption[/pl_carouselimage][/pl_carousel]</code>
	 */
    function pl_carousel_shortcode( $atts, $content = null ) {

		global $carousel_js;

		if ( isset($atts['speed']) && '0' === $atts['speed'] )
			$atts['speed'] = 'pause'; // 0 will be striped by array_filter

		// remove any empty array keys that have empty values to enforce defaults (eg: name="" or speed="") that would otherwise break things
		$atts = array_filter($atts);

	    $defaults = array(
			'name'  => 'PageLines Carousel',
			'speed' => 5000 // default bootstrap transition time
	    );

	    $atts = shortcode_atts( $defaults, $atts );

	    $carousel_id = sanitize_title_with_dashes( $atts['name'], null, 'save' ); // convert it to a valid id attribute if it isn't.
	    $speed = absint($atts['speed']);

	    if ( ! isset($carousel_js) )
	    	$carousel_js = array();
	    else {
	    	if ( array_key_exists($carousel_id, $carousel_js) )
	    		$carousel_id = $carousel_id.'-'.count($carousel_js);
	    }

	    // store away the values for consolidated output in the footer
	    $carousel_js[$carousel_id] = array(
	    	'id' => $carousel_id,
	    	'speed' => $speed
	    	);

		return sprintf( '<div id="%2$s" class="carousel slide"><div class="carousel-inner">%1$s</div><a class="carousel-control left" href="#%2$s" data-slide="prev">&lsaquo;</a><a class="carousel-control right" href="#%2$s" data-slide="next">&rsaquo;</a></div>',
		do_shortcode( $content ),
		$carousel_id
		);
	}
	//Carousel Images
	function pl_carouselimage_shortcode( $atts, $content = null ) {

		// remove any empty string attributes to use defaults
		$atts = array_filter($atts);

	    extract( shortcode_atts( array(
		    'first' => '',
		    'title' => '',
		    'imageurl' => sprintf( '%s/screenshot.png', PL_PARENT_URL ), // fallback "reminder" image
		    'caption' => '',
	    ), $atts ) );

	    $first = ( $first == 'yes' ) ? 'active' : '';
	    $content = ( $content <> '' ) ? "<div class='carousel-caption'><h4>$title</h4><p>$content</p></div>" : ''; // changed to work without captions

		return sprintf( '<div class="item %s"><img src="%s">%s</div>', // changed to work without captions
				$first,
				$imageurl,
				do_shortcode( $content )
				);
	}
	
		/**
		 * 35. Bootstrap Tabs
		 *
		 * @example <code>[pl_tabs][pl_tabtitlesection type=""][pl_tabtitle active="" number="1"]...[/pl_tabtitle][pl_tabtitle number="2"]...[/pl_tabtitle][/pl_tabtitlesection][pl_tabcontentsection][pl_tabcontent active="" number="1"]...[/pl_tabcontent][pl_tabcontent number=""]...[/pl_tabcontent][/pl_tabcontentsection][/pl_tabs]</code> is the default usage
		 * @example <code>[pl_tabs][pl_tabtitlesection type="tabs"][pl_tabtitle active="yes" number="1"]Title 1[/pl_tabtitle][pl_tabtitle number="2"]Title 2[/pl_tabtitle][/pl_tabtitlesection][pl_tabcontentsection][pl_tabcontent active="yes" number="1"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ac mi enim, at consectetur justo.[/pl_tabcontent][pl_tabcontent number="2"]Second content there.[/pl_tabcontent][/pl_tabcontentsection][/pl_tabs]</code>
		 * @example Available types include tabs, pills
		 */

	    function pl_tabs_shortcode( $atts, $content = null ) {

	    	return sprintf( '<div class="tabs">%s</div>', do_shortcode( $content ) );

		}

		//Tab Titles Section
			function pl_tabtitlesection_shortcode( $atts, $content = null ) {

				extract( shortcode_atts( array(
			    	'type' => '',
			    ), $atts ) );

			    ob_start();

			    	?>
			    		<script>
				    		jQuery(function(){
								 jQuery('a[data-toggle="tab"]').on('shown', function (e) {
								  e.target // activated tab
								  e.relatedTarget // previous tab
								})
							});
			    		</script><?php

			    printf( '<ul class="nav nav-%s">%s</ul>',
				$type,
				do_shortcode( $content )
				);

			    return ob_get_clean();
			}

		//Tab Titles
			function pl_tabtitle_shortcode( $atts, $content = null ) {

			    extract( shortcode_atts( array(
					'active' => '',
					'number' => ''
				), $atts ) );

			    $active = ( $active == 'yes' ) ? "class='active'" : '';

			    $out = sprintf( '<li %s><a href="#%s" data-toggle="tab">%s</a></li>',
						$active,
						$number,
						do_shortcode( $content )
						);

			    return $out;
			}

		//Tab Content Section
			function pl_tabcontentsection_shortcode( $atts, $content = null ) {

			    return '<div class="tab-content">'.do_shortcode( $content ).'</div>';

			}

		//Tab Content
			function pl_tabcontent_shortcode( $atts, $content = null ) {

			    extract( shortcode_atts( array(
				    'active' => '',
				    'number' => ''
			    ), $atts ) );

			    $active = ( $active == 'yes' ) ? "active" : '';

			    return sprintf( '<div class="tab-pane %s" id="%s"><p>%s</p></div>',
						$active,
						$number,
						do_shortcode( apply_filters( 'the_content',$content ) )
						);
			}

		/**
		 * 36. Bootstrap Modal Popup Window
		 *
		 * @example <code>[pl_modal title="" type="" colortype="" label=""]...[/pl_modal]</code>
		 * @example <code>[pl_modal title="Title" type="label" colortype="info" label="Click Me!"]Some content here for the cool modal pop up. You can have all kinds of cool stuff in here.[/pl_modal]</code>
		 * @example available types include button, label, and badge
		 * @example available color types include default, success, warning, important, info, and inverse
		 */
		function pl_modal_shortcode( $atts, $content = null ) {

		    extract( shortcode_atts( array(
			    'title'		=> '',
			    'type'		=> '',
			    'colortype' => '',
			    'label' 	=> '',
			    'show'		=> 'false',
				'hash'		=> rand()
		    ), $atts ) );

		    	ob_start();

		    		?>
					<script>
		            	jQuery(function(){
							jQuery('#modal_<?php echo $hash; ?>').appendTo(jQuery('body')).modal({
								keyboard: true
								, show: <?php echo $show; ?>
							});
						});
					</script><?php

				  printf( '<a data-toggle="modal" role="button" href="#modal_%6$s" class="%2$s %2$s-%3$s">%5$s</a><div id="modal_%6$s" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-header"><a class="close" data-dismiss="modal" aria-hidden="true">×</a><h3>%s</h3></div><div class="modal-body"><p>%4$s</p></div><div class="modal-footer"><a href="#" class="btn btn-%3$s" data-dismiss="modal" aria-hidden="true">%7$s</a></div></div>',
					$title,
					$type,
					$colortype,
					do_shortcode( $content ),
					$label,
					$hash,
					__( 'Close', 'pagelines' )
			        );

	        	return ob_get_clean();

		}
		
		/**
		 * 38. Used to create general buttons and button links
		 *
		 * @example <code>[button]</code> is the default usage
		 * @example <code>[button format="edit_post" before="<b>" after="</b>"]</code>
		 */
		function pagelines_button_shortcode( $atts ) {

			$defaults = array(
				'color'	=> 'grey',
				'size'	=> 'normal',
				'align'	=> 'right',
				'style'	=> '',
				'type'	=> 'button',
				'text'	=> '&nbsp;',
				'pid'	=> 0,
				'class'	=> null,
			);
			$atts = shortcode_atts( $defaults, $atts );

			$button = sprintf( '<div class="blink"><div class="blink-pad">%s</div></div>', $text );

			$output = sprintf( '<div class="%s %s %s blink-wrap">%s</div>', $special, $size, $color, $button );

			return apply_filters( 'pagelines_button_shortcode', $output, $atts );

		}

		/**
		 * XX. Responsive Videos
		 *
		 * @example <code>[pl_video]</code> is the default usage
		 * @example <code>[pl_video type="youtube" url="urltovideo"]</code>
		 */
	    function pl_video_shortcode ($atts) {

	    	extract( shortcode_atts( array(
	    		'type' =>'',
		    	'id' =>'',
		    	'width' => '100%',
		    	'height' => '100%',
		    	'wmode'	=> 'transparent',
		    	'related' => '',
		    	), $atts ) );
	    	if ( $related )
				$related = '?rel=0';

	    	switch( $type ) {


	    		case 'vimeo':
	    			$out = sprintf( '<div class="pl-video vimeo"><iframe src="//player.vimeo.com/video/%s" width="%s" height="%s"  frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen wmode="%s"></iframe></div>',$id, $width, $height, $wmode );
	    			break;

	    		case 'dailymotion':
	    			$out = sprintf( '<div class="pl-video dailymotion"><iframe src="//www.dailymotion.com/embed/video/%s" width="%s" height="%s"  frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen wmode="%s"></iframe></div>',$id, $width, $height, $wmode );
	    			break;

	    		default:
	    			$out = sprintf('<div class="pl-video youtube"><iframe src="//www.youtube.com/embed/%s%s" width="%s" height="%s" frameborder="0" allowfullscreen wmode="%s"></iframe></div>', $id, $related, $width, $height, $wmode );
	    	}
		    return $out;
	    }
}
new PL_ShortCodes_Libs;
