<?php
/**
 * FUNCTIONS / THEME INITIALIZATION
 *
 * This file loads the core framework which handles everything.
 *
 * @package     PageLines Framework
 * @since       1.0
 *
 * @link        http://www.pagelines.com/
 * @link        http://www.pagelines.com/DMS
 *
 * @author      PageLines   http://www.pagelines.com/
 * @copyright   Copyright (c) 2008-2013, PageLines  hello@pagelines.com
 *
 * @internal    last revised January 20, 2012
 * @version     ...
 *
 * @todo Define version
 */

require_once( 'includes/init.php' );

/**
 * Tell Platform 5 we are a supported theme and add header and footers into the builder.
 */
class PL5_Integration {
  
  function __construct() {
    add_action( 'after_setup_theme', array( $this, 'after_setup' ) );
  }
  
  function after_setup() {
    add_theme_support( 'pagelines' );
    add_action( 'pagelines_page',       array( $this, 'pl5_pagelines_page' ) );
    add_action( 'pagelines_after_main', array( $this, 'pl5_pagelines_after_main' ) );
  }
  
  function pl5_pagelines_page() {
    if( function_exists( 'pl_edit_head' ) ) {
      echo pl_edit_head();
    }
  }
  
  function pl5_pagelines_after_main() {
    if( function_exists( 'pl_edit_foot' ) ) {
      echo pl_edit_foot();
    }
  }
}

new PL5_Integration;
