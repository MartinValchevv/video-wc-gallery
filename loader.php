<?php
/**
 * Loads the plugin files
 *
 * @since 1.18
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load basic setup. Plugin list links, text domain, footer links etc. 
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'admin/basic-setup.php' );

// Load admin setup. Register menus and settings
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'admin/admin-ui-setup.php' );

// Do supports for themes
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'functions/theme-supports.php' );

// Do plugin operations
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'functions/do.php' );

// Version updater
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'update.php' );