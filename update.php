<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Update function when update to new version
 *
 * @since 1.24
 */
function vwg_update_to_new_version() {
    $plugin_data = get_file_data( VWG_VIDEO_WOO_GALLERY_DIR . '/video-wc-gallery.php', array( 'Version' ) );
    $plugin_version = $plugin_data[0];

    // Check if the current plugin version matches the desired version
    if ($plugin_version === '1.24') {
        $existing_settings = get_option('vwg_settings_group', array());
        if (!isset($existing_settings['vwg_settings_video_adapt_sizes'])) {
             $existing_settings['vwg_settings_video_adapt_sizes'] = '';
             update_option('vwg_settings_group', $existing_settings);
        }
    }

    if ($plugin_version === '1.3') {
        $products = get_posts( array(
            'post_type'   => 'product',
            'numberposts' => -1,
            'meta_query'  => array(
                array(
                    'key'     => 'vwg_video_url',
                    'compare' => 'EXISTS',
                ),
            ),
        ) );

        foreach ( $products as $product ) {
            $video_urls = get_post_meta( $product->ID, 'vwg_video_url', true );
            if ( ! empty( $video_urls ) ) {
                foreach ( $video_urls as $key => $attachment ) {

                    $sanitized_attachment = array(
                        'video_url' => wp_kses_post( $attachment['video_url'] ),
                        'video_thumb_url' => wp_kses_post( $attachment['video_thumb_url'] ),
                    );

                    if ( isset( $attachment['video_thumb_url'] ) && strpos( $attachment['video_thumb_url'], 'data:image/png;base64,' ) === 0 ) {
                        // Decode the base64-encoded image
                        $base64_image = $attachment['video_thumb_url'];
                        // Remove the data URI scheme and get the base64-encoded image data
                        $base64_data = str_replace( 'data:image/png;base64,', '', $base64_image );
                        $decoded_image = base64_decode( $base64_data );

                        // Create a directory (if not exists) to store the uploaded images
                        $upload_dir = wp_upload_dir();
                        $target_dir = $upload_dir['basedir'] . '/video-wc-gallery-thumb/';
                        wp_mkdir_p( $target_dir );

                        // Generate a unique filename for the uploaded image
                        $filename = 'vwg-thumb_' . uniqid() . '.png';

                        // Save the decoded image to the target directory
                        $file_path = $target_dir . $filename;
                        file_put_contents( $file_path, $decoded_image );

                        // Set the video_thumb_url to the uploaded file URL
                        $sanitized_attachment['video_thumb_url'] = $upload_dir['baseurl'] . '/video-wc-gallery-thumb/' . $filename;
                    }

                    $sanitized_urls[ $key ] = $sanitized_attachment;
                }
                update_post_meta( $product->ID, 'vwg_video_url', $sanitized_urls  );
            }
        }
    }
}
add_action( 'admin_init', 'vwg_update_to_new_version' );
add_action('wp_loaded', 'vwg_update_to_new_version');



/**
 * Admin notice info if have version msg
 *
 * @since 1.3
 */
// Function to display admin message
function vwg_show_admin_message_for_version() {
    $plugin_data = get_file_data( VWG_VIDEO_WOO_GALLERY_DIR . '/video-wc-gallery.php', array( 'Version' ) );
    $plugin_version = $plugin_data[0];

    // Check if the current plugin version matches the desired version
//    if ($plugin_version === '1.3') {
//        $message = __('', 'video-wc-gallery');
//        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($message) . '</p></div>';
//    }
}
add_action( 'admin_notices', 'vwg_show_admin_message_for_version' );

