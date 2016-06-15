<?php

// Load options UI
require_once( PL_ADMIN . '/admin.ui.php' );

// Load options for front end
require_once( PL_ADMIN . '/admin.editor.php' );

// Load account handling
require_once( PL_ADMIN . '/admin.account.php' );

// Load option actions
require_once( PL_ADMIN . '/admin.actions.php' );

// Load option actions
require_once( PL_ADMIN . '/admin.postmeta.php' );

do_action('pagelines_admin_load', 'core'); // Hook

