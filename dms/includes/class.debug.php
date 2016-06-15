<?php
/**
 *
 * PageLines Debugging system
 *
 * Enabled in Framework admin 'advanced' tab
 * Show server debug info using special URL
 *
 * @package PageLines DMS
 * @subpackage Debugging
 * @since 2.1
 *
 */


/**
 * PageLines Debugging Information Class
 *
 * @package PageLines DMS
 * @subpackage Debugging
 * @since 2.1
 *
 */
class PageLinesDebug {

	// Array of debugging information
	var $debug_info = array();


	/**
	*
	* @TODO document
	*
	*/
	function __construct( ) {

		$this->wp_debug_info();
		$this->debug_info_template();
	}

	/**
	 * Main output.
	 * @return str Formatted results for page.
	 *
	 */
	function debug_info_template(){

		$out = '';
		foreach($this->debug_info as $element ) {

			$class = '';
			$style = '';
			if ( $element['value'] ) {
				if( isset( $element['style'] ) )
					$style = sprintf( ' style="%s"', $element['style'] );
				$out .= sprintf( '<p><strong><span%s>%s</span></strong><br />%s', $style, ucfirst($element['title']), ucfirst($element['value']) );
				
				$out .= (isset($element['extra'])) ? "<br /><kbd>{$element['extra']}</kbd>" : '';
				$out .= '</p>';
			}
		}
		wp_die( sprintf( '<h2>DMS Debug Info</h2>%s', $out ), 'PageLines Debug Info', array( 'response' => 200, 'back_link' => true) );
	}

	/**
	 * Debug tests.
	 * @return array Test results.
	 */
	function wp_debug_info(){

		global $wpdb, $wp_version, $platform_build;

			// Set data & variables first
			$uploads = wp_upload_dir();
			// Get user role
			$current_user = wp_get_current_user();
			$user_roles = $current_user->roles;
			$user_role = array_shift($user_roles);

			// Format data for processing by a template

			$this->debug_info[] = array(
				'title'	=> 'WordPress Version',
				'value' => $wp_version,
			);

			$this->debug_info[] = array(
				'title'	=> 'WordPress Debug',
				'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Enabled' : 'Disabled',
			);
			
			$this->debug_info[] = array(
				'title'	=> 'Multisite Enabled',
				'value' => ( is_multisite() ) ? 'Yes' : 'No',
			);

			$this->debug_info[] = array(
				'title'	=> 'Current Role',
				'value' => $user_role,
			);

			$this->debug_info[] = array(
				'title'	=> 'Framework Path',
				'value' => '<kbd>' . pl_get_template_directory() . '</kbd>',
			);

			$this->debug_info[] = array(
				'title'	=> 'Framework URI',
				'value' => '<kbd>' . pl_get_template_directory_uri() . '</kbd>',
			);

			$this->debug_info[] = array(
				'title'	=> 'Framework Version',
				'value' => PL_CORE_VERSION,
			);

			$this->debug_info[] = array(
				'title'	=> 'PHP Version',
				'value' => floatval( phpversion() ),
			);

			$this->debug_info[] = array(
				'title'	=> 'Child theme',
				'value' => ( get_template_directory() != get_stylesheet_directory() ) ? 'Yes' : '',
				'extra' => get_stylesheet_directory() . '<br />' . get_stylesheet_directory_uri()
			);

			$this->debug_info[] = array(
				'title'	=> 'PHP Safe Mode',
				'value' => ( (bool) ini_get('safe_mode') ) ? 'Yes! Deprecated as of PHP 5.3 and removed in PHP 5.4':'',
			);

			$this->debug_info[] = array(
				'title'	=> 'PHP Open basedir restriction',
				'value' => ( (bool) ini_get('open_basedir') ) ? 'This can cause issues with uploads if it is not setup correctly' : '',
				'extra'	=> ini_get('open_basedir')
			);

			$this->debug_info[] = array(
				'title'	=> 'WP_DEBUG',
				'value' => (defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No',
			);

			$this->debug_info[] = array(
				'title'	=> 'WP memory limit',
				'value' => size_format( $this->let_to_num( WP_MEMORY_LIMIT ) ),
			);
			
			$this->debug_info[] = array(
				'title'	=> 'WP MAX memory limit',
				'value' => size_format( $this->let_to_num( WP_MAX_MEMORY_LIMIT ) ),
			);
			
			$this->debug_info[] = array(
				'title'	=> 'PHP memory limit',
				'value' => size_format( $this->let_to_num( ini_get('memory_limit') ) ),
			);

			$this->debug_info[] = array(
				'title'	=> 'Mysql version',
				'value' => $wpdb->db_version(),
			);


			$this->debug_info[] = array(
				'title'	=> 'WP Max Upload Size',
				'value' => size_format( wp_max_upload_size() ),
			);
			
			$this->debug_info[] = array(
				'title'	=> 'PHP POST Max Size',
				'value' => size_format( $this->let_to_num( ini_get('post_max_size') ) ),
			);
			
			$this->debug_info[] = array(
				'title'	=> 'PHP Max Execution Time',
				'value' => ini_get('max_execution_time') . 's',
			);

			$this->debug_info[] = array(
				'title'	=> 'PHP type',
				'value' => php_sapi_name(),
			);
			
			$this->debug_info[] = array(
				'title'	=> 'WebServer software',
				'value' => esc_html( $_SERVER['SERVER_SOFTWARE'] ),
			);

			$processUser = ( ! function_exists( 'posix_geteuid') || ! function_exists( 'posix_getpwuid' ) ) ? 'Posix functions are disabled on this host. Not necessarily a problem, but if the user needs FTP/SFTP to install plugins/themes then creating CSS files might be an issue.' : posix_getpwuid(posix_geteuid());
			if ( is_array( $processUser ) )
				$processUser = $processUser['name'];

			$this->debug_info[] = array(
				'title'	=> 'PHP User',
				'value' => $processUser,
			);

			$this->debug_info[] = array(
				'title'	=> 'OS',
				'value' => PHP_OS,
			);
			
			$status = get_option( 'dms_activation' );
			if ( pl_is_activated() && isset( $status['email'] ) ) {
				
				$this->debug_info[] = array(
					'title'	=> 'Licence OK',
					'value' => $status['email'],
					'extra'	=> '',
				);
			}

			$this->debug_info[] = array(
				'title'	=> 'Installed Plugins',
				'value' => $this->debug_get_plugins(),
				'level'	=> false
			);
			if( get_theme_mod( 'less_last_error' ) ) {
			$this->debug_info[] = array(
				'title'	=> 'DMS Internal Warning',
				'value' => 'Less Subsystem',
				'extra'	=> get_theme_mod( 'less_last_error' ),
				'style'	=> 'color:red;'
			);
		}
	}
	/**
	 * Get active plugins.
	 * @return str List of plugins.
	 *
	 */
	function debug_get_plugins() {
		$plugins = get_option('active_plugins');
		if ( $plugins ) {
			$plugins_list = '';
			foreach($plugins as $plugin_file) {
					$plugins_list .= '<kbd>' . $plugin_file . '</kbd>';
					$plugins_list .= '<br />';
			}
			return ( isset( $plugins_list ) ) ? "{$plugins_list}" : '';
		}
	}
	
	function let_to_num( $size ) {
	    $l 		= substr( $size, -1 );
	    $ret 	= substr( $size, 0, -1 );
	    switch( strtoupper( $l ) ) {
		    case 'P':
		        $ret *= 1024;
		    case 'T':
		        $ret *= 1024;
		    case 'G':
		        $ret *= 1024;
		    case 'M':
		        $ret *= 1024;
		    case 'K':
		        $ret *= 1024;
	    }
	    return $ret;
	}
//-------- END OF CLASS --------//
}

if ( ! is_admin() ) {
	if( isset( $_GET['pldebug'] ) )
		new PageLinesDebug;
}