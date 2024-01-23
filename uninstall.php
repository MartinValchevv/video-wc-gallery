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
 * @since		1.27
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

    // Delete all file and directory video-wc-gallery-thumb
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/video-wc-gallery-thumb/';

    if (is_dir($target_dir)) {
        // Open the directory
        $dir_handle = opendir($target_dir);

        // Loop through the directory and delete files
        while (($file = readdir($dir_handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $file_path = $target_dir . $file;
                unlink($file_path); // Delete the file
            }
        }

        closedir($dir_handle); // Close the directory handle

        // Delete the directory itself
        rmdir($target_dir);
    } else {
        echo 'Directory does not exist.';
    }

}

if ($option['vwg_settings_remove_settings_data'] == 1) {
    delete_option( 'vwg_settings_group' );
}
delete_option( 'abl_vwg_version' );
delete_option('vwg_monthly_notice_dismissed');
wp_clear_scheduled_hook('vwg_monthly_admin_notice');