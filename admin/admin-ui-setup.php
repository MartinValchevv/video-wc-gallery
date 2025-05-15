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
 * @since 2.0
 */
function vwg_enqueue_css_js( $hook ) {
    global $vwg_adminPage, $vwg_pro_adminPage;


	if ( $hook != $vwg_adminPage && $hook != $vwg_pro_adminPage && $hook != "post.php" && $hook != "post-new.php") {
		return;
	}

    if ( $hook == $vwg_adminPage || $hook == $vwg_pro_adminPage ) { // if settings page VWG plugin

        // CSS
        wp_enqueue_style('vwg-admin-css', VWG_VIDEO_WOO_GALLERY_URL . 'includes/css/admin/admin.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('vwg_fontawesome', VWG_VIDEO_WOO_GALLERY_URL . 'includes/fontawesome_v6-6-0/css/all.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('woocommerce_admin_styles');

        // JS
        wp_enqueue_script('postbox');
        wp_enqueue_script('jquery-tiptip');
        wp_enqueue_script('vwg-admin', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/vwg-admin.js', array('jquery', 'wp-color-picker'), false, true);

    }

    if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
        // CSS
        wp_enqueue_style('vwg_fontawesome_admin', VWG_VIDEO_WOO_GALLERY_URL . 'includes/fontawesome_v6-6-0/css/all.css', '', VWG_VERSION_NUM);
        wp_enqueue_style('vwg-admin-pricing-css', VWG_VIDEO_WOO_GALLERY_URL . 'includes/css/admin/pricing-modal.css', '', VWG_VERSION_NUM);

        // JS
        wp_enqueue_script( 'sweetalert2', VWG_VIDEO_WOO_GALLERY_URL . 'includes/sweetalert2/sweetalert2.all.min.js', __FILE__ );
        wp_enqueue_script('vwg-pricing', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/vwg-pricing.js', array('jquery'), false, true);

        $variable_array = array(
            'VWG_Url' => VWG_VIDEO_WOO_GALLERY_URL,
        );
        wp_localize_script( 'vwg-admin', 'vwg_variable_obj', $variable_array );
        wp_localize_script( 'vwg-pricing', 'vwg_variable_obj', $variable_array );
    }

    // CSS
    wp_enqueue_style('vwg-admin-pricing-css', VWG_VIDEO_WOO_GALLERY_URL . 'includes/css/admin/pricing-modal.css', '', VWG_VERSION_NUM);

    // JS
    wp_enqueue_script( 'sweetalert2', VWG_VIDEO_WOO_GALLERY_URL . 'includes/sweetalert2/sweetalert2.all.min.js', __FILE__ );
    wp_enqueue_script('vwg-pricing', VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/vwg-pricing.js', array('jquery'), false, true);

    /**
     * Translate array for JS vwg-admin
     *
     * @since 2.0
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

    wp_localize_script('vwg-admin', 'vwg_AJAX', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('remove_unused_thumbnails_nonce')
    ));

    $variable_array = array(
        'VWG_Url' => VWG_VIDEO_WOO_GALLERY_URL,
    );
    wp_localize_script( 'vwg-admin', 'vwg_variable_obj', $variable_array );
    wp_localize_script( 'vwg-pricing', 'vwg_variable_obj', $variable_array );
}
add_action( 'admin_enqueue_scripts', 'vwg_enqueue_css_js' );

/**
 * Save settings
 * @since 2.0
 */
function vwg_save_settings() {


    if ( isset( $_POST['vwg_save_general_settings_nonce'] ) && check_admin_referer( 'vwg_save_general_settings', 'vwg_save_general_settings_nonce' ) ) {

        $icon = isset($_POST['vwg_settings_icon']) ? sanitize_text_field($_POST['vwg_settings_icon']) : '';
        $icon_color = isset($_POST['vwg_settings_icon_color']) ? sanitize_text_field($_POST['vwg_settings_icon_color']) : '';
        $controls = isset($_POST['vwg_settings_video_controls']) ? 'controls' : '';
        $loop = isset($_POST['vwg_settings_loop']) ? 'loop' : '';
        $muted = isset($_POST['vwg_settings_muted']) ? 'muted' : '';
        $autoplay = isset($_POST['vwg_settings_autoplay']) ? 'autoplay' : '';
        $showFirst = isset($_POST['vwg_settings_show_first']) ? sanitize_text_field($_POST['vwg_settings_show_first']) : '';
        $videoAdaptSizes = isset($_POST['vwg_settings_video_adapt_sizes']) ? sanitize_text_field($_POST['vwg_settings_video_adapt_sizes']) : '';

        $settings = array(
            'vwg_settings_icon' => $icon,
            'vwg_settings_icon_color' => $icon_color,
            'vwg_settings_video_controls' => $controls,
            'vwg_settings_loop' => $loop,
            'vwg_settings_muted' => $muted,
            'vwg_settings_autoplay' => $autoplay,
            'vwg_settings_show_first' => $showFirst,
            'vwg_settings_video_adapt_sizes' => $videoAdaptSizes,
        );

        update_option('vwg_settings_group', $settings);
        vwg_settings_saved_notice(__( 'General settings saved successfully.', 'video-wc-gallery' ));
    }

    if ( isset( $_POST['vwg_save_uninstall_settings_nonce'] ) && check_admin_referer( 'vwg_save_uninstall_settings', 'vwg_save_uninstall_settings_nonce' )  ) {

        $removeSettings = isset($_POST['vwg_settings_remove_settings_data']) ? sanitize_text_field($_POST['vwg_settings_remove_settings_data']) : '';
        $removeSettingsVideo = isset($_POST['vwg_settings_remove_videos_data']) ? sanitize_text_field($_POST['vwg_settings_remove_videos_data']) : '';

        $settings = array(
            'vwg_settings_remove_settings_data' => $removeSettings,
            'vwg_settings_remove_videos_data' => $removeSettingsVideo,
        );

        update_option('vwg_uninstall_settings_group', $settings);
        vwg_settings_saved_notice(__( 'Uninstall settings saved successfully.', 'video-wc-gallery' ));
    }

}
add_action('admin_init', 'vwg_save_settings');


/**
 * Register Settings and view
 * @since 2.0
 */
function vwg_custom_settings() {
    ?>
    <div id="vwg-options" class="wrap">
        <form method="post" action="" enctype="multipart/form-data" name="options-form">
            <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
            <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
            <div class="postbox-container1 header-container column-1 normal">
                <h1 style="margin-bottom: 15px;">
                    <img src="<?php echo esc_url(VWG_VIDEO_WOO_GALLERY_URL); ?>includes/images/vwg-logo.png" class="logo-image" title="Video Gallery for WooCommerce" alt="Video Gallery for WooCommerce">
                </h1>
            </div>
            <div class="clear"></div>
            <div id="poststuff">
                <div class="metabox-holder">
                    <div id="all-fileds" class="postbox-container column-1 normal">
                        <?php do_meta_boxes('video-gallery_page_video-gallery-wc-settings', 'normal', null); ?>
                        <?php do_meta_boxes('video-gallery_page_video-gallery-wc-settings', 'advanced', null); ?>
                    </div>
                    <div id="promo" class="postbox-container column-2 normal">
                        <?php do_meta_boxes('video-gallery_page_video-gallery-wc-settings', 'side', null); ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Add metaboxes
 *
 * @since 2.0
 */
function vwg_add_metaboxes() {

    add_meta_box(
        'vwg_metabox_general_settings',
        __('General Settings', 'video-wc-gallery' ),
        'vwg_render_general_settings',
        'video-gallery_page_video-gallery-wc-settings',
        'normal',
        'high'
    );

    add_meta_box(
        'vwg_metabox_thumbnails_optimization_settings',
        __('Thumbnails optimization', 'video-wc-gallery' ) . apply_filters("vwg_modify_pro_strings", "<sup> PRO</sup>"),
        'vwg_render_thumbnails_optimization_settings',
        'video-gallery_page_video-gallery-wc-settings',
        'normal',
        'high'
    );

    add_meta_box(
        'vwg_metabox_general_uninstall_settings',
        __('Uninstall settings', 'video-wc-gallery' ),
        'vwg_render_general_uninstall_settings',
        'video-gallery_page_video-gallery-wc-settings',
        'normal',
        'low'
    );

    add_meta_box(
        'vwg_metabox_get_pro_version',
        __('Get the PRO version', 'video-wc-gallery' ),
        'vwg_render_get_pro_version',
        'video-gallery_page_video-gallery-wc-settings',
        'side',
        'high'
    );

    add_meta_box(
        'vwg_metabox_review',
        __('Help us keep the plugin free & maintained', 'video-wc-gallery' ),
        'vwg_render_review',
        'video-gallery_page_video-gallery-wc-settings',
        'side',
        'low'
    );

    add_meta_box(
        'vwg_metabox_support',
        __('Something is not working ? Do you need our help ?', 'video-wc-gallery' ),
        'vwg_render_support',
        'video-gallery_page_video-gallery-wc-settings',
        'side',
        'low'
    );

    add_meta_box(
        'vwg_metabox_donate',
        __('Your support is invaluable to us! 🌟', 'video-wc-gallery' ),
        'vwg_render_donate',
        'video-gallery_page_video-gallery-wc-settings',
        'side',
        'low'
    );
}


/**
 * Render metabox General Settings
 * @since 2.0
 */
function vwg_render_general_settings() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'vwg_save_general_settings', 'vwg_save_general_settings_nonce' ); ?>
        <?php settings_fields('vwg_settings_group'); ?>
        <?php do_settings_sections('vwg_settings_group'); ?>
        <div style="margin: 15px 0;">
            <?php submit_button( __( 'Save Changes', 'video-wc-gallery' ), 'primary', 'submit', false ); ?>
        </div>
    </form>
    <?php
}

/**
 * Render metabox Thumbnails optimization
 * @since 2.0
 */
function vwg_render_thumbnails_optimization_settings() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'vwg_save_thumbnails_optimization', 'vwg_save_thumbnails_optimization_nonce' ); ?>
        <?php settings_fields('vwg_thumbnails_optimization_group'); ?>
        <?php do_settings_sections('vwg_thumbnails_optimization_group'); ?>
        <div style="margin: 15px 0;">
            <?php submit_button( __( 'Save Changes', 'video-wc-gallery' ), 'primary', 'submit', false ); ?>
        </div>
    </form>
    <?php
}

/**
 * Render metabox Uninstall Settings
 * @since 2.0
 */
function vwg_render_general_uninstall_settings() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'vwg_save_uninstall_settings', 'vwg_save_uninstall_settings_nonce' ); ?>
        <?php settings_fields('vwg_uninstall_settings_group'); ?>
        <?php do_settings_sections('vwg_uninstall_settings_group'); ?>
        <div style="margin: 15px 0;">
            <?php submit_button( __( 'Save Changes', 'video-wc-gallery' ), 'primary', 'submit', false ); ?>
        </div>
    </form>
    <?php
}

/**
 * Render metabox Get the PRO version
 * @since 2.0
 */
function vwg_render_get_pro_version() {
    $get_pro_info  = '';
    $get_pro_info .= ' <div class="get-pro-version-info">';
    $get_pro_info .= __( '<p>Unlock all features with the Pro version:</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="feature"><span class="dashicons dashicons-yes"></span> Up to 6 videos per product</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="feature"><span class="dashicons dashicons-yes"></span> Use custom SVG icon</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="feature"><span class="dashicons dashicons-yes"></span> Use optimized thumbnails</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="feature"><span class="dashicons dashicons-yes"></span> Auto convert to optimized thumbnail on upload</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="feature"><span class="dashicons dashicons-yes"></span> SEO settings for each video</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="feature"><span class="dashicons dashicons-yes"></span> Premium Support</p>', 'video-wc-gallery' );
    $get_pro_info .= __( '<p class="btn-wrap"><a href="javascript:;" class="get-vwg-pro-version-info-btn button button-primary">Get PRO</a> &nbsp;&nbsp;</p>', 'video-wc-gallery' );
    $get_pro_info .= '</div>';
    echo $get_pro_info; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Render metabox Review
 * @since 2.0
 */
function vwg_render_review() {
    $review_text  = '';
    $review_text .= '<div class="sidebar-promo">';
    $review_text .= __( '<p><b>Your review means a lot!</b> Please help us spread the word so that others know this plugin is free and well maintained! Thank you very much for <a href="https://wordpress.org/support/plugin/video-wc-gallery/reviews/?filter=5" target="_blank">reviewing the Video Gallery for WooCommerce plugin with ★★★★★ stars</a>!</p>', 'video-wc-gallery' );
    $review_text .= __( '<p><a href="https://wordpress.org/support/plugin/video-wc-gallery/reviews/?filter=5" target="_blank" class="button button-primary">Leave a Review</a> &nbsp;&nbsp;</p>', 'video-wc-gallery' );
    $review_text .= '</div>';
    echo $review_text; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Render metabox Support
 * @since 2.0
 */
function vwg_render_support() {
    $support_text  = '';
    $support_text .= '<div class="sidebar-promo">';
    $support_text .= __( '<p>We\'re here for you! We know how frustrating it is when things don\'t work!<br>Please <a href="https://wordpress.org/support/plugin/video-wc-gallery/" target="_blank">open a new topic in our official support forum</a> and we\'ll get back to you ASAP! We answer all questions, and most of them within a few hours.</p>', 'video-wc-gallery' );
    $support_text .= __( '<p><a href="https://wordpress.org/support/plugin/video-wc-gallery/" target="_blank" class="button button-secondary">Get Help Now</a></p>', 'video-wc-gallery' );
    $support_text .= '</div>';
    echo $support_text; // phpcs:ignore WordPress.Security.EscapeOutput create this Wordpress translatable
}

/**
 * Render metabox Donate
 * @since 2.0
 */
function vwg_render_donate() {
    $donate_text  = '';
    $donate_text .= '<div class="sidebar-promo">';
    $donate_text .= __( '<p>By donating to <b>Video Gallery for WooCommerce</b>, you\'re investing in its future and ensuring it remains a reliable and accessible resource for all. Will you join us in this mission and make a contribution today? Every donation, no matter the amount, helps us continue our work and reach even greater heights. <a href="https://linktr.ee/martinvalchev" target="_blank">Click here to donate now</a></p>', 'video-wc-gallery' );
    $donate_text .= __( '<p><a href="https://linktr.ee/martinvalchev" target="_blank" class="button button-primary">Donate</a> &nbsp;&nbsp;</p>', 'video-wc-gallery' );
    $donate_text .= '</div>';
    echo $donate_text; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Add JavaScript for metabox dragging
 * @since 2.0
 */
function vwg_added_admin_scripts() {
    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function() {
            jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
            postboxes.add_postbox_toggles('<?php echo esc_html('video-gallery_page_video-gallery-wc-settings'); ?>');
        });
        //]]>
    </script>
    <?php
}


/**
 * Register Settings
 * @since 2.0
 */
function vwg_register_settings() {
    add_settings_section(
        'vwg_settings_section',
        null,
        'vwg_settings_section_callback',
        'vwg_settings_group'
    );

    add_settings_section(
        'vwg_thumbnails_optimization_section',
        null,
        'vwg_thumbnails_optimization_section_callback',
        'vwg_thumbnails_optimization_group'
    );

    add_settings_section(
        'vwg_uninstall_settings_section',
        null,
        'vwg_uninstall_settings_section_callback',
        'vwg_uninstall_settings_group'
    );

    /**
     * General Settings fields
     */
    add_settings_field(
        'vwg_settings_icon',
        __( 'Select an icon to be displayed on the thumbnail in the gallery', 'video-wc-gallery' ),
        'vwg_settings_icon_callback',
        'vwg_settings_group',
        'vwg_settings_section'
    );

    add_settings_field(
        'vwg_settings_custom_svg_icon',
        __( 'Use custom SVG icon', 'video-wc-gallery' ) . apply_filters("vwg_modify_pro_strings", "<sup> PRO</sup>"),
        'vwg_settings_custom_svg_icon_callback',
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

    /**
     * Thumbnails optimization fields
     */
    add_settings_field(
        'vwg_settings_optimized_thumbnails',
        __( 'Use optimized thumbnails', 'video-wc-gallery' ) . apply_filters("vwg_modify_pro_strings", "<sup> PRO</sup>"),
        'vwg_settings_optimized_thumbnails_callback',
        'vwg_thumbnails_optimization_group',
        'vwg_thumbnails_optimization_section'
    );

    add_settings_field(
        'vwg_settings_convert_on_upload',
        __( 'Convert on upload', 'video-wc-gallery' ) . apply_filters("vwg_modify_pro_strings", "<sup> PRO</sup>"),
        'vwg_settings_convert_on_upload_callback',
        'vwg_thumbnails_optimization_group',
        'vwg_thumbnails_optimization_section'
    );

    add_settings_field(
        'vwg_settings_functions_convert',
        __( 'Bulk convert', 'video-wc-gallery' ) . apply_filters("vwg_modify_pro_strings", "<sup> PRO</sup>"),
        'vwg_settings_functions_convert_callback',
        'vwg_thumbnails_optimization_group',
        'vwg_thumbnails_optimization_section'
    );

    /**
     * Uninstall Settings fields
     */
    add_settings_field(
        'vwg_settings_remove_settings_data',
        __( 'Delete all plugin settings when uninstalling the plugin', 'video-wc-gallery' ),
        'vwg_settings_remove_settings_data_callback',
        'vwg_uninstall_settings_group',
        'vwg_uninstall_settings_section'
    );

    add_settings_field(
        'vwg_settings_remove_videos_data',
        __( 'Delete all video attachments when uninstalling the plugin', 'video-wc-gallery' ),
        'vwg_settings_remove_videos_callback',
        'vwg_uninstall_settings_group',
        'vwg_uninstall_settings_section'
    );

    /**
     * General Settings - register
     */
    register_setting( 'vwg_settings_group', 'vwg_settings_icon', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'far fa-play-circle'
    ) );

    register_setting( 'vwg_settings_group', 'vwg_settings_custom_svg_icon', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
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

    /**
     * Thumbnails optimization - register
     */
    register_setting( 'vwg_thumbnails_optimization_group', 'vwg_settings_optimized_thumbnails', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );


    register_setting( 'vwg_thumbnails_optimization_group', 'vwg_settings_convert_on_upload', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );

    /**
     * Uninstall Settings - register
     */
    register_setting( 'vwg_uninstall_settings_group', 'vwg_settings_remove_settings_data', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );

    register_setting( 'vwg_uninstall_settings_group', 'vwg_settings_remove_videos_data', array(
        'type' => 'boolean',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => false
    ) );
}

/**
 * Settings for detect not used thumbnails and deleted !
 * @since 2.0
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
        <div id="postbox-container-unused-thumbs" class="postbox-container">
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

/**
 * Thumbnails optimization boxes before render
 * @since 2.0
 */
function vwg_thumbnails_optimization_section_callback() {

}

/**
 * Uninstall Settings boxes before render
 * @since 2.0
 */
function vwg_uninstall_settings_section_callback() {

}

/**
 * General Settings render
 */
function vwg_settings_icon_callback() {
    $option = get_option( 'vwg_settings_group' );
    ?>
    <div class="radio-with-Icon" style="height: 60px;">
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

function vwg_settings_custom_svg_icon_callback() {
    $option_pro = get_option('vwg_pro_settings');
    ?>
    <input type="checkbox" name="vwg_settings_custom_svg_icon" id="vwg_settings_custom_svg_icon" value="1" <?php checked(isset($option_pro['vwg_settings_custom_svg_icon']) && $option_pro['vwg_settings_custom_svg_icon'], '1'); ?>>
    <span><?php echo esc_html__('With this setting you will be able to upload or select a custom SVG icon to use instead of the default ones.' , 'video-wc-gallery') ?>
        <?php
        $vwg_pro_feature_link = '<a class="open-vwg-modal-pro-info" href="#">' . esc_html__('PRO feature' , 'video-wc-gallery') . '</a>';
        $vwg_pro_feature_link = wp_kses_post($vwg_pro_feature_link);
        echo apply_filters('vwg_pro_feature_link', $vwg_pro_feature_link);
        ?>
    </span>
    <br>
    <font id="bypass-icon-color" class="vwg-info" style="display: none;">This option will automatically bypass <code>Choose icon color</code> option. The colors of the attached icon are used !</font>
    <div class="vwg-upload-wrapper">
        <a href="#" id="vwg-upload-svg-btn" disabled class="button button-small button-secondary"><?php echo esc_html__('Upload SVG icon', 'video-wc-gallery') ?></a>
        <?php if ( isset( $option_pro['svg_data'] ) ) : ?>
            <span class="vwg-custom-svg-wrapper">
                <img src="<?php echo esc_url( $option_pro['svg_data']['url'] ); ?>">
            </span>
        <?php endif; ?>
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

/**
 * Thumbnails Settings render
 */
function vwg_settings_optimized_thumbnails_callback() {
    $option_pro = get_option('vwg_pro_settings');
    ?>
    <label class="vwg-toggle">
        <input type="checkbox" class="vwg-toggle-checkbox" name="vwg_settings_optimized_thumbnails" id="vwg_settings_optimized_thumbnails" value="1" <?php checked(isset($option_pro['vwg_settings_optimized_thumbnails']) && $option_pro['vwg_settings_optimized_thumbnails'], '1'); ?>>
        <div class="vwg-toggle-switch"></div>
        <span class="vwg-toggle-label"><?php echo esc_html__('Optimized thumbnails in webp format will be displayed.' , 'video-wc-gallery') ?>
            <?php
            $vwg_pro_feature_link = '<a class="open-vwg-modal-pro-info" href="#">' . esc_html__('PRO feature' , 'video-wc-gallery') . '</a>';
            $vwg_pro_feature_link = wp_kses_post($vwg_pro_feature_link);
            echo apply_filters('vwg_pro_feature_link', $vwg_pro_feature_link);
            ?>
        </span>
    </label>
    <?php
}
function vwg_settings_convert_on_upload_callback() {
    $option_pro = get_option('vwg_pro_settings');
    ?>
    <input type="checkbox" name="vwg_settings_convert_on_upload" id="vwg_settings_convert_on_upload" value="1" <?php checked(isset($option_pro['vwg_settings_convert_on_upload']) && $option_pro['vwg_settings_convert_on_upload'], '1'); ?>>
    <span><?php echo esc_html__('Adding a video to a product will automatically create optimized thumbnails in webp format.' , 'video-wc-gallery') ?>
        <?php
        $vwg_pro_feature_link = '<a class="open-vwg-modal-pro-info" href="#">' . esc_html__('PRO feature' , 'video-wc-gallery') . '</a>';
        $vwg_pro_feature_link = wp_kses_post($vwg_pro_feature_link);
        echo apply_filters('vwg_pro_feature_link', $vwg_pro_feature_link);
        ?>
    </span>
    <?php
}

function vwg_settings_functions_convert_callback() {
    $option_pro = get_option('vwg_pro_settings');
    ?>
    <a href="#" id="vwg_bulk_convert" class="button button-secondary"><?php echo esc_html__('Bulk Convert', 'video-wc-gallery') ?></a>
    <a href="#" id="vwg_delete_converted_files" class="button button-secondary"><?php echo esc_html__('Delete converted files', 'video-wc-gallery') ?></a>
    <span style="line-height: 28px;"><?php echo esc_html__('Bulk convert of already existing thumbnails.' , 'video-wc-gallery') ?>
        <?php
        $vwg_pro_feature_link = '<a class="open-vwg-modal-pro-info" href="#">' . esc_html__('PRO feature' , 'video-wc-gallery') . '</a>';
        $vwg_pro_feature_link = wp_kses_post($vwg_pro_feature_link);
        echo apply_filters('vwg_pro_feature_link', $vwg_pro_feature_link);
        ?>
    </span>
    <?php
}

/**
 * Uninstall Settings render
 */
function vwg_settings_remove_settings_data_callback() {
    $option = get_option('vwg_uninstall_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_remove_settings_data" id="vwg_settings_remove_settings_data" value="1" <?php checked(isset($option['vwg_settings_remove_settings_data']) && $option['vwg_settings_remove_settings_data'], '1'); ?>>
    <?php
}

function vwg_settings_remove_videos_callback() {
    $option = get_option('vwg_uninstall_settings_group');
    ?>
    <input type="checkbox" name="vwg_settings_remove_videos_data" id="vwg_settings_remove_videos_data" value="1" <?php checked(isset($option['vwg_settings_remove_videos_data']) && $option['vwg_settings_remove_videos_data'], '1'); ?>>
    <?php
}
add_action( 'admin_init', 'vwg_register_settings' );


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


