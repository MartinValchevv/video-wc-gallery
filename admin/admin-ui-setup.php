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
 * @since 1.0
 */
function vwg_enqueue_css_js( $hook ) {

	if ( $hook != "woocommerce_page_wc-settings" && $hook != "post.php" && $hook != "post-new.php" && $hook != "plugins.php") {
		return;
	}

    if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'vwg_tab' ) { // if settings page in Woocommerce

        // CSS
        wp_enqueue_style('vwg-admin-css', VWG_VIDEO_WOO_GALLERY_URL . 'includes/css/admin/admin.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('vwg_fontawesome', VWG_VIDEO_WOO_GALLERY_URL . 'includes/fontawesome5/css/all.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('wp-color-picker');

        // JS
        wp_enqueue_script('vwg-admin', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/vwg-admin.js', array('jquery', 'wp-color-picker'), false, true);

    }

    // JS
    wp_enqueue_script( 'sweetalert2', VWG_VIDEO_WOO_GALLERY_URL . 'includes/sweetalert2/sweetalert2.all.min.js', __FILE__ );
    wp_enqueue_script( 'vwg-feedback', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/feedback.js', __FILE__ );

    /**
     * Translate array for JS vwg-admin
     *
     * @since 1.0
     */
    $translation_array = array(
        'yes' => __( 'Yes, confirm', 'video-wc-gallery' ),
        'are_you_sure' => __( 'Are you sure ?', 'video-wc-gallery' ),
        'cancel_text' => __( 'No, cancel', 'video-wc-gallery' ),
        'changes_are_saved' => __( 'Changes are saved', 'video-wc-gallery' ),
        'remove_plugin_data_txt' => __( 'This setting, in the event of uninstallation of the plugin, will delete all plugin settings made so far!', 'video-wc-gallery' ),
        'remove_video_txt' => __( 'This setting will remove any video that has been added to a product when the plugin is uninstalled!', 'video-wc-gallery' ),
        'deactivating' => __( 'Deactivating...', 'video-wc-gallery' ),
    );
    wp_localize_script( 'vwg-admin', 'translate_obj', $translation_array );
    wp_localize_script( 'vwg-feedback', 'translate_obj', $translation_array );

}
add_action( 'admin_enqueue_scripts', 'vwg_enqueue_css_js' );

/**
 * Save settings
 * @since 1.0
 */
function vwg_save_settings() {

    $icon = isset( $_POST['vwg_settings_icon'] ) ? sanitize_text_field( $_POST['vwg_settings_icon'] ) : '';
    $icon_color = isset( $_POST['vwg_settings_icon_color'] ) ? sanitize_text_field( $_POST['vwg_settings_icon_color'] ) : '';
    $controls = isset( $_POST['vwg_settings_video_controls'] ) ? 'controls' : '';
    $loop = isset( $_POST['vwg_settings_loop'] ) ? 'loop' : '';
    $muted = isset( $_POST['vwg_settings_muted'] ) ? 'muted' : '';
    $autoplay = isset( $_POST['vwg_settings_autoplay'] ) ? 'autoplay' : '';
    $removeSettings = isset( $_POST['vwg_settings_remove_settings_data'] ) ? sanitize_text_field( $_POST['vwg_settings_remove_settings_data'] ) : '';
    $removeSettingsVideo = isset( $_POST['vwg_settings_remove_videos_data'] ) ? sanitize_text_field( $_POST['vwg_settings_remove_videos_data'] ) : '';

    $settings = array(
        'vwg_settings_icon' => $icon,
        'vwg_settings_icon_color' => $icon_color,
        'vwg_settings_video_controls' => $controls,
        'vwg_settings_loop' => $loop,
        'vwg_settings_muted' => $muted,
        'vwg_settings_autoplay' => $autoplay,
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
 * @since 1.1
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
        esc_url('https://revolut.me/mvalchev'),
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
 * @since 1.0
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

function vwg_settings_section_callback() {

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

function vwg_settings_remove_settings_data_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_remove_settings_data" id="vwg_settings_remove_settings_data" value="1" <?php checked($option['vwg_settings_remove_settings_data'], '1'); ?>>
    <?php
}

function vwg_settings_remove_videos_callback() {
    $option = get_option('vwg_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_remove_videos_data" id="vwg_settings_remove_videos_data" value="1" <?php checked($option['vwg_settings_remove_videos_data'], '1'); ?>>
    <?php
}


add_action( 'admin_init', 'vwg_register_settings' );
add_action( 'woocommerce_settings_tabs_vwg_tab', 'vwg_custom_settings' );




