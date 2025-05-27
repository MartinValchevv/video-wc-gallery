<?php
/**
 * Plugin Name: Video Gallery for WooCommerce
 * Plugin URI:
 * Description: The Video Gallery for WooCommerce is a plugin that enables the addition of video files from the WordPress library to a product page, with several customizable options.
 * Author: Martin Valchev
 * Author URI:https://linktr.ee/martinvalchev
 *  Requires Plugins: woocommerce
 * Version: 1.40
 * Text Domain: video-wc-gallery
 * Domain Path: /languages
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define constants
 *
 * @since 1.2
 */
if ( ! defined( 'VWG_VERSION_NUM' ) ) 		    define( 'VWG_VERSION_NUM'		    , '1.40' ); // Plugin version constant
if ( ! defined( 'VWG_VIDEO_WOO_GALLERY' ) )		define( 'VWG_VIDEO_WOO_GALLERY'		, trim( dirname( plugin_basename( __FILE__ ) ), '/' ) ); // Name of the plugin folder eg - 'video-wc-gallery'
if ( ! defined( 'VWG_VIDEO_WOO_GALLERY_DIR' ) )	define( 'VWG_VIDEO_WOO_GALLERY_DIR'	, plugin_dir_path( __FILE__ ) ); // Plugin directory absolute path with the trailing slash. Useful for using with includes eg - /var/www/html/wp-content/plugins/video-wc-gallery/
if ( ! defined( 'VWG_VIDEO_WOO_GALLERY_URL' ) )	define( 'VWG_VIDEO_WOO_GALLERY_URL'	, plugin_dir_url( __FILE__ ) ); // URL to the plugin folder with the trailing slash. Useful for referencing src eg - http://localhost/wp/wp-content/plugins/video-wc-gallery/
if ( ! defined( 'VWG_PHP_MINIMUM_VERSION' ) )	define( 'VWG_PHP_MINIMUM_VERSION'	, '7.4' );
if ( ! defined( 'VWG_WP_MINIMUM_VERSION' ) )	define( 'VWG_WP_MINIMUM_VERSION'	, '4.8' );
if ( ! defined( 'VWG_PLUGIN_NAME' ) )	        define( 'VWG_PLUGIN_NAME'	        ,  get_file_data(__FILE__, ['Plugin Name'], false)[0] ); // Name plugin - 'Video Gallery for WooCommerce'

//require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

/**
 * Check Woocommerce is deactivate
 *
 * @since 1.0
 */
function vwg_deactivate_on_woocommerce_deactivate() {
    if ( ! class_exists( 'woocommerce' ) || ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        vwg_admin_notice_err(__('Video Gallery for WooCommerce requires WooCommerce to be installed and activated. Please activate WooCommerce and try again.', 'video-wc-gallery'));
        deactivate_plugins( plugin_basename(__FILE__) );
    }
}
add_action( 'admin_init', 'vwg_deactivate_on_woocommerce_deactivate' );


/**
 * Check Woocommerce is activate
 *
 * @since 1.0
 */
function vwg_activate_on_woocommerce_activate() {
    if ( class_exists( 'woocommerce' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        vwg_admin_notice_err(false);
    }
}
add_action( 'admin_init', 'vwg_activate_on_woocommerce_activate' );


/**
 * Database upgrade
 *
 * @since 1.0
 */
function vwg_upgrader() {
	
	// Get the current version of the plugin stored in the database.
	$current_ver = get_option( 'abl_vwg_version', '0.0' );
	
	// Return if we are already on updated version. 
	if ( version_compare( $current_ver, VWG_VERSION_NUM, '==' ) ) {
		return;
	}
	
	// This part will only be excuted once when a user upgrades from an older version to a newer version.
	
	// Finally add the current version to the database. Upgrade
	update_option( 'abl_vwg_version', VWG_VERSION_NUM );

}
add_action( 'admin_init', 'vwg_upgrader' );

/**
 * Admin notice err
 *
 * @since 1.31
 */
function vwg_admin_notice_err($msg = '') {
    if (!empty($msg)) :
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($msg); ?></p>
        </div>
    <?php endif;
}
add_action( 'admin_notices', 'vwg_admin_notice_err' );

/**
 * Deregister all scripts and styles contains id mediaelement - use defaults wordpress
 *
 * @since 1.5
 */
function vwg_deregister_mediaelement_scripts() {
    if (is_product()) {
        // Deregister scripts
        global $wp_scripts;
        foreach ($wp_scripts->registered as $handle => $script) {
            if (strpos($handle, 'mediaelement-migrate') !== false) {
                wp_dequeue_script($handle);
                wp_deregister_script($handle);
            }
        }

//        // Deregister styles
//        global $wp_styles;
//        foreach ($wp_styles->registered as $handle => $style) {
//            if (strpos($handle, 'mediaelement') !== false) {
//                wp_dequeue_style($handle);
//                wp_deregister_style($handle);
//            }
//        }
    }
}
add_action('wp_enqueue_scripts', 'vwg_deregister_mediaelement_scripts', 9999);

// Load everything
require_once( VWG_VIDEO_WOO_GALLERY_DIR . 'loader.php' );

// Register activation hook (this has to be in the main plugin file or refer bit.ly/2qMbn2O)
register_activation_hook(__FILE__, 'vwg_activate_plugin');