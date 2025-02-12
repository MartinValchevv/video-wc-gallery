<?php
/**
 * Admin setup for the plugin
 *
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Enqueue Admin CSS and JS
 *
 * @since 1.32
 */
function vwg_enqueue_css_js( $hook ) {

	if ( $hook != "woocommerce_page_wc-settings" && $hook != "post.php" && $hook != "post-new.php" && $hook != "plugins.php") {
		return;
	}

    if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'vwg_tab' ) { // if settings page in Woocommerce

        // CSS
        wp_enqueue_style('vwg-admin-css', VWG_VIDEO_WOO_GALLERY_URL . 'includes/css/admin/admin.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('vwg_fontawesome', VWG_VIDEO_WOO_GALLERY_URL . 'includes/fontawesome_v6-6-0/css/all.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('wp-color-picker');

        // JS
        wp_enqueue_script('vwg-admin', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/vwg-admin.js', array('jquery', 'wp-color-picker'), false, true);

    }

    // JS
    wp_enqueue_script( 'sweetalert2', VWG_VIDEO_WOO_GALLERY_URL . 'includes/sweetalert2/sweetalert2.all.min.js', __FILE__ );
    // wp_enqueue_script( 'vwg-feedback', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/feedback.js', __FILE__ );

    /**
     * Translate array for JS vwg-admin
     *
     * @since 1.20
     */
    $translation_array = array(
        'yes' => __( 'Yes, confirm', 'video-wc-gallery' ),
        'are_you_sure' => __( 'Are you sure ?', 'video-wc-gallery' ),
        'to_delete_unused_thumbs' => __( 'want to delete unused thumbnails', 'video-wc-gallery' ),
        'cancel_text' => __( 'No, cancel', 'video-wc-gallery' ),
        'changes_are_saved' => __( 'Changes are saved', 'video-wc-gallery' ),
        'remove_plugin_data_txt' => __( 'This setting, in the event of uninstallation of the plugin, will delete all plugin settings made so far!', 'video-wc-gallery' ),
        'remove_video_txt' => __( 'This setting will remove any video that has been added to a product when the plugin is uninstalled!', 'video-wc-gallery' ),
        'deactivating' => __( 'Deactivating...', 'video-wc-gallery' ),
        'deleting' => __( 'Deleting...', 'video-wc-gallery' ),
        'deleting_thumbs' => __( 'thumbnails deleted', 'video-wc-gallery' ),
        'autoplay_settings_info' => __( 'Autoplay in most browsers requires muted audio to provide a better user experience. Autoplaying videos with sound can be disruptive, so browser vendors often restrict autoplay to muted videos by default to prevent unexpected and intrusive playback.', 'video-wc-gallery' ),
    );
    wp_localize_script( 'vwg-admin', 'translate_obj', $translation_array );
    // wp_localize_script( 'vwg-feedback', 'translate_obj', $translation_array );

    wp_localize_script('vwg-admin', 'vwg_AJAX', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('remove_unused_thumbnails_nonce')
    ));

}
add_action( 'admin_enqueue_scripts', 'vwg_enqueue_css_js' );

/**
 * Save settings
 * @since 1.24
 */
function vwg_save_settings() {

    $icon = isset( $_POST['vwg_settings_icon'] ) ? sanitize_text_field( $_POST['vwg_settings_icon'] ) : '';
    $icon_color = isset( $_POST['vwg_settings_icon_color'] ) ? sanitize_text_field( $_POST['vwg_settings_icon_color'] ) : '';
    $controls = isset( $_POST['vwg_settings_video_controls'] ) ? 'controls' : '';
    $loop = isset( $_POST['vwg_settings_loop'] ) ? 'loop' : '';
    $muted = isset( $_POST['vwg_settings_muted'] ) ? 'muted' : '';
    $autoplay = isset( $_POST['vwg_settings_autoplay'] ) ? 'autoplay' : '';
    $showFirst = isset( $_POST['vwg_settings_show_first'] ) ? sanitize_text_field( $_POST['vwg_settings_show_first'] ) : '';
    $videoAdaptSizes = isset( $_POST['vwg_settings_video_adapt_sizes'] ) ? sanitize_text_field( $_POST['vwg_settings_video_adapt_sizes'] ) : '';
    $removeSettings = isset( $_POST['vwg_settings_remove_settings_data'] ) ? sanitize_text_field( $_POST['vwg_settings_remove_settings_data'] ) : '';
    $removeSettingsVideo = isset( $_POST['vwg_settings_remove_videos_data'] ) ? sanitize_text_field( $_POST['vwg_settings_remove_videos_data'] ) : '';

    $settings = array(
        'vwg_settings_icon' => $icon,
        'vwg_settings_icon_color' => $icon_color,
        'vwg_settings_video_controls' => $controls,
        'vwg_settings_loop' => $loop,
        'vwg_settings_muted' => $muted,
        'vwg_settings_autoplay' => $autoplay,
        'vwg_settings_show_first' => $showFirst,
        'vwg_settings_video_adapt_sizes' => $videoAdaptSizes,
        'vwg_settings_remove_settings_data' => $removeSettings,
        'vwg_settings_remove_videos_data' => $removeSettingsVideo,
    );

    update_option( 'vwg_settings_group', $settings );

}
add_filter('woocommerce_settings_save_vwg_tab', 'vwg_save_settings');

/**
 * Add a new tab to WooCommerce settings page.
 * @since 1.0
 */
function vwg_add_custom_settings_tab( $tabs ) {
    $tabs['vwg_tab'] = __( 'Video Gallery for WooCommerce', 'video-wc-gallery' );
    return $tabs;
}
add_filter( 'woocommerce_settings_tabs_array', 'vwg_add_custom_settings_tab', 50 );


/**
 * Register Settings and view
 * @since 1.0
 */
function vwg_custom_settings() {
    ?>
    <div class="wrap vwg-wrap">
        <?php
        settings_fields('vwg_settings_group');
        do_settings_sections('vwg_settings_group');
        ?>
    </div>
    <?php
}

/**
 * Plugin info view
 * @since 1.34
 */
function vwg_plugin_info() {
    $allowed_tags = array(
        'a' => array(
            'href' => array(),
            'target' => array(),
        ),
        'br' => array(),
    );
    $vwg_footer_text = sprintf( __( 'If you like this plugin, please <a href="%s" target="_blank">make a donation</a> or leave me a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating to support continued development. Thanks a bunch!', 'video-wc-gallery' ),
        esc_url('https://linktr.ee/martinvalchev'),
        esc_url('https://wordpress.org/support/plugin/video-wc-gallery/reviews/?rate=5#new-post')
    );
    $vwg_support_links = sprintf( __( '<a href="%s" target="_blank">Get support</a>', 'video-wc-gallery' ),
        esc_url('https://wordpress.org/support/plugin/video-wc-gallery/#new-post'),
    );
    ?>
    <div id="postbox-container-1" class="postbox-container vwg_postbox-container">
        <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
            <div id="submitdiv" class="postbox ">
                <div class="postbox-header">
                    <h2 class="hndle ui-sortable-handle" style="padding: 0 10px;"><?php echo esc_html__('Video Gallery for WooCommerce' , 'video-wc-gallery') ?></h2>
                </div>
                <div class="inside">
                    <p><?php echo wp_kses($vwg_footer_text, $allowed_tags); ?></p>
                    <p style="text-align: center;"><?php echo wp_kses($vwg_support_links, $allowed_tags); ?> | <a href="https://translate.wordpress.org/projects/wp-plugins/video-wc-gallery/" target="_blank"><span class="dashicons dashicons-translation"></span></a></p>
                    <hr>
                    <p style="text-align: center"><?php echo esc_html__('Video Gallery for WooCommerce version:' , 'video-wc-gallery') ?> <?php echo esc_html(VWG_VERSION_NUM) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'woocommerce_after_settings_vwg_tab', 'vwg_plugin_info' );


/**
 * Register Settings
 * @since 1.35
 */
function vwg_register_settings() {
    add_settings_section(
        'vwg_settings_section',
        __( 'Video Gallery for WooCommerce Settings', 'video-wc-gallery' ),
        'vwg_settings_section_callback',
        'vwg_settings_group'
    );


    add_settings_field(
        'vwg_settings_icon',
        __( 'Select an icon to be displayed on the thumbnail in the gallery', 'video-wc-gallery' ),
        'vwg_settings_icon_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_icon_color',
        __( 'Choose icon color', 'video-wc-gallery' ),
        'vwg_settings_icon_color_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_video_controls',
        __( 'Show video controls options', 'video-wc-gallery' ),
        'vwg_settings_video_controls_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_loop',
        __( 'The video will repeat after it ends', 'video-wc-gallery' ),
        'vwg_settings_loop_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_muted',
        __( 'The video will be muted', 'video-wc-gallery' ),
        'vwg_settings_muted_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_autoplay',
        __( 'Autoplay the video', 'video-wc-gallery' ),
        'vwg_settings_autoplay_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_show_first',
        __( 'Show video first in product gallery', 'video-wc-gallery' ) . wc_help_tip(__('This setting may not work properly with some themes', 'video-wc-gallery'), 'warning'),
        'vwg_settings_show_first_callback', 
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_video_adapt_sizes',
        __( 'Adjust the video size according to the theme settings', 'video-wc-gallery' ) . wc_help_tip(__('This setting may not work properly with some themes', 'video-wc-gallery'), 'warning'),
        'vwg_settings_video_adapt_sizes_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_remove_settings_data',
        __( 'Delete all plugin settings when uninstalling the plugin', 'video-wc-gallery' ),
        'vwg_settings_remove_settings_data_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_remove_videos_data',
        __( 'Delete all video attachments when uninstalling the plugin', 'video-wc-gallery' ),
        'vwg_settings_remove_videos_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    register_setting( 'vwg_settings_group', 'vwg_settings_icon', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'far fa-play-circle'
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_icon_color', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '#ffffff'
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_video_controls', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => true
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_loop', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => true
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_muted', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => true
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_autoplay', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => true
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_show_first', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_video_adapt_sizes', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_remove_settings_data', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_remove_videos_data', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );


}

/**
 * Settings for detect not used thumbnails and deleted !
 * @since 1.3
 */
function vwg_settings_section_callback() {
    $detectedThumbs = vwg_detect_attached_thumbs();
    $count_for_delete = '';
    $files_for_delete = '';
    if ( isset($detectedThumbs) ) {
        $count_for_delete = $detectedThumbs['not_attached_count'];
        $files_for_delete = $detectedThumbs['file'];
    }
    ?>

    <?php if ($count_for_delete) : ?>
    <div id="dashboard-widgets" class="metabox-holder vwg-dashboard-widgets-unused-thumbs">
        <div id="postbox-container-1" class="postbox-container">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div id="metabox" class="postbox">
                    <div class="inside">
                        <div class="main">
                            <p><strong><?php echo sprintf(esc_html__('There are generated %s thumbnails which are not used !', 'video-wc-gallery'), $count_for_delete) ?></strong></p>
                            <p><?php echo esc_html__('From the "Delete all" button you will delete all thumbnails that are not used so that they do not take up space' , 'video-wc-gallery') ?></p>
                            <p><a class="button button-primary" id="delete_unused_thumbs" style="background: #d63638; border-color: #d63638;"><?php echo esc_html__('Delete all' , 'video-wc-gallery') ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input id="files_for_delete" type="hidden" value="<?php echo (isset($files_for_delete)) ? implode(',',$files_for_delete ) : '' ?>">
    <?php endif; ?>

    <?php
}

function vwg_settings_icon_callback() {
    $option = get_option( 'vwg_settings_group' );
    ?>
    <div class="radio-with-Icon">
        <p class="radioOption">
            <input type="radio" name="vwg_settings_icon" id="vwg_settings_icon_play-circle" value="far fa-play-circle" <?php checked($option['vwg_settings_icon'], 'far fa-play-circle'); ?> class="ng-valid ng-dirty ng-touched ng-empty">
            <label for="vwg_settings_icon_play-circle">
                <i class="far fa-play-circle"></i>
            </label>
        </p>
        <p class="radioOption">
            <input type="radio" name="vwg_settings_icon" id="vwg_settings_icon_play-circle-full"
                   value="fas fa-play-circle" <?php checked($option['vwg_settings_icon'], 'fas fa-play-circle'); ?>>
            <label for="vwg_settings_icon_play-circle-full">
                <i class="fas fa-play-circle"></i>
            </label>
        </p>
        <p class="radioOption">
            <input type="radio" name="vwg_settings_icon" id="vwg_settings_icon_fa-play" value="fas fa-play" <?php checked($option['vwg_settings_icon'], 'fas fa-play'); ?> class="ng-valid ng-dirty ng-touched ng-empty">
            <label for="vwg_settings_icon_fa-play">
                <i class="fas fa-play"></i>
            </label>
        </p>
        <p class="radioOption">
            <input type="radio" name="vwg_settings_icon" id="vwg_settings_icon_fa-video" value="fas fa-video" <?php checked($option['vwg_settings_icon'], 'fas fa-video'); ?> class="ng-valid ng-dirty ng-touched ng-empty">
            <label for="vwg_settings_icon_fa-video">
                <i class="fas fa-video"></i>
            </label>
        </p>
        <p class="radioOption">
            <input type="radio" name="vwg_settings_icon" id="vwg_settings_icon_fa-file-video-full"
                   value="fas fa-file-video" <?php checked($option['vwg_settings_icon'], 'fas fa-file-video'); ?> class="ng-valid ng-dirty ng-touched ng-empty">
            <label for="vwg_settings_icon_fa-file-video-full">
                <i class="fas fa-file-video"></i>
            </label>
        </p>
        <p class="radioOption">
            <input type="radio" name="vwg_settings_icon" id="vwg_settings_icon_fa-file-video"
                   value="far fa-file-video" <?php checked($option['vwg_settings_icon'], 'far fa-file-video'); ?> class="ng-valid ng-dirty ng-touched ng-empty">
            <label for="vwg_settings_icon_fa-file-video">
                <i class="far fa-file-video"></i>
            </label>
        </p>
    </div>
    <?php
}

function vwg_settings_icon_color_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="text" id="vwg_settings_icon_color" name="vwg_settings_icon_color" class="vwg_settings_icon_color" value="<?php echo esc_attr($option['vwg_settings_icon_color']) ?>">
    <?php
}

function vwg_settings_video_controls_callback() {
    $option = get_option('vwg_settings_group');
    $checked = ($option['vwg_settings_video_controls'] === 'controls') ? 'checked' : '';
    ?>
    <input type="checkbox" name="vwg_settings_video_controls" id="vwg_settings_video_controls" value="controls" <?php echo esc_attr($checked); ?>>
    <?php
}

function vwg_settings_loop_callback() {
    $option = get_option('vwg_settings_group');
    $checked = ($option['vwg_settings_loop'] === 'loop') ? 'checked' : '';
    ?>
    <input type="checkbox" name="vwg_settings_loop" id="vwg_settings_loop" value="loop" <?php echo esc_attr($checked); ?>>
    <?php
}

function vwg_settings_muted_callback() {
    $option = get_option('vwg_settings_group');
    $checked = ($option['vwg_settings_muted'] === 'muted') ? 'checked' : '';
    ?>
    <input type="checkbox" name="vwg_settings_muted" id="vwg_settings_muted" value="muted" <?php echo esc_attr($checked); ?>>
    <?php
}

function vwg_settings_autoplay_callback() {
    $option = get_option('vwg_settings_group');
    $checked = ($option['vwg_settings_autoplay'] === 'autoplay') ? 'checked' : '';
    ?>
    <input type="checkbox" name="vwg_settings_autoplay" id="vwg_settings_autoplay" value="autoplay" <?php echo esc_attr($checked); ?>>
    <?php
}

function vwg_settings_show_first_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_show_first" id="vwg_settings_show_first" value="1" <?php checked(isset($option['vwg_settings_show_first']) && $option['vwg_settings_show_first'], '1'); ?>>
    <?php
}

function vwg_settings_video_adapt_sizes_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_video_adapt_sizes" id="vwg_settings_video_adapt_sizes" value="1" <?php checked(isset($option['vwg_settings_video_adapt_sizes']) && $option['vwg_settings_video_adapt_sizes'], '1'); ?>>
    <?php
}

function vwg_settings_remove_settings_data_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_remove_settings_data" id="vwg_settings_remove_settings_data" value="1" <?php checked(isset($option['vwg_settings_remove_settings_data']) && $option['vwg_settings_remove_settings_data'], '1'); ?>>
    <?php
}

function vwg_settings_remove_videos_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_remove_videos_data" id="vwg_settings_remove_videos_data" value="1" <?php checked(isset($option['vwg_settings_remove_videos_data']) && $option['vwg_settings_remove_videos_data'], '1'); ?>>
    <?php
}


add_action( 'admin_init', 'vwg_register_settings' );
add_action( 'woocommerce_settings_tabs_vwg_tab', 'vwg_custom_settings' );


/**
 * Function for detect attached thumbs
 * @since 1.20
 */
function vwg_detect_attached_thumbs() {
    $attachedThumbs = array();
    $not_attachedThumbs = array();
    $counter = 0;
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
            foreach ( $video_urls as $attachment ) {
                $video_thumb_url = $attachment['video_thumb_url'];
                $woocommerce_thumbnail_url = isset($attachment['woocommerce_thumbnail_url']) ? $attachment['woocommerce_thumbnail_url'] : '';
                $woocommerce_gallery_thumbnail_url = isset($attachment['woocommerce_gallery_thumbnail_url']) ? $attachment['woocommerce_gallery_thumbnail_url'] : '';
                $filename_pattern = '/vwg-thumb_(.+)\.png/';
                if ( preg_match( $filename_pattern, $video_thumb_url, $matches ) === 1 ) {
                    $attachedThumbs[] = $matches[0];
                }
                if ( preg_match( $filename_pattern, $woocommerce_thumbnail_url, $matches ) === 1 ) {
                    $attachedThumbs[] = $matches[0];
                }
                if ( preg_match( $filename_pattern, $woocommerce_gallery_thumbnail_url, $matches ) === 1 ) {
                    $attachedThumbs[] = $matches[0];
                }
            }
        }
    }

    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/video-wc-gallery-thumb/';

    $files = scandir($target_dir);
    if ($files !== false) {
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                // $file_path = $target_dir . $file;
                if (!in_array($file, $attachedThumbs)) {
                    // The file is not in the list of attached thumbs, perform necessary action
                    $counter++;
                    $not_attachedThumbs['not_attached_count'] = $counter;
                    $not_attachedThumbs['file'][] = $file;
                }
            }
        }
    }


    if ( !empty($not_attachedThumbs) ) {
        return $not_attachedThumbs;
    }

}


/**
 * AJAX function for remove unused thumbnails
 * @since 1.32
 */
function remove_unused_thumbnails() {

    // Verify nonce
    check_ajax_referer('remove_unused_thumbnails_nonce', 'security');

    // Check if the user has the appropriate capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        wp_die();
    }

    $for_delete = explode(',', $_POST['files_for_del']);

    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/video-wc-gallery-thumb/';
    $counter = 0;
    $deleted_attachedThumbs = array();

    $files = scandir($target_dir);

    if ($files !== false) {
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $file_path = $target_dir . $file;
                if (in_array($file, $for_delete)) {
                    // The file is not in the list of attached thumbs, perform necessary action
                    unlink($file_path);
                    $counter++;
                    $deleted_attachedThumbs['count_delete'] = $counter;
                    $deleted_attachedThumbs['deleted_file'][] = $file;
                }
            }
        }
    }

    // Prepare the response
    $response = array(
        'count_delete' => $deleted_attachedThumbs['count_delete'],
        'deleted_file' => $deleted_attachedThumbs['deleted_file'],
    );

    // Send the response back to the AJAX request
    wp_send_json_success($response);
    wp_die();
}
add_action('wp_ajax_remove_unused_thumbnails', 'remove_unused_thumbnails');
add_action('wp_ajax_nopriv_remove_unused_thumbnails', 'remove_unused_thumbnails');

/**
 * Custom filter to modify help tip icon
 * @since 1.35
 */
function vwg_custom_help_tip($tip, $sanitized_tip, $original_tip, $allow_html) {
    // Check if 'info' is passed as second parameter to wp_help_tip()
    if (func_num_args() > 2 && $allow_html === 'info') {
        return sprintf(
            '<span class="woocommerce-help-tip dashicons dashicons-info" data-tip="%s"></span>',
            $sanitized_tip
        );
    } elseif (func_num_args() > 2 && $allow_html === 'warning') {
        return sprintf(
            '<span class="woocommerce-help-tip dashicons dashicons-warning" data-tip="%s"></span>',
            $sanitized_tip
        );
    }
    
    // Return original tip if not info
    return $tip;
}
add_filter('wc_help_tip', 'vwg_custom_help_tip', 10, 4);


