<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Everything in uninstall.php will be executed when user decides to delete the plugin. 
 * @since		1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// If uninstall not called from WordPress, then die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) die;

/**
 * Delete database settings
 *
 * @since		1.0
 */
$option = get_option('vwg_settings_group');

if ($option['vwg_settings_remove_videos_data'] == 1) {

    // Delete all instances of the 'vwg_video_url' custom field
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
            'vwg_video_url'
        )
    );
}

if ($option['vwg_settings_remove_settings_data'] == 1) {
    delete_option( 'vwg_settings_group' );
}
delete_option( 'abl_vwg_version' );