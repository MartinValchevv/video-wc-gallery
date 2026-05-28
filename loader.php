<?php
/**
 * Loads the plugin files
 *
 * @since 2.8
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

// Porto theme integration. Lives next to its template overrides for tidiness.
// Loaded after do.php so the renderer function vwg_add_video_to_product_gallery
// is defined (the Porto action handler reuses it via the standard hook chain).
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/porto-theme-support.php' );

// Product Gallery Block support
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'functions/product-gallery-block.php' );

// Version updater
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'update.php' );