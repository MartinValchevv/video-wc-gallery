<?php 
/**
 * Basic setup functions for the plugin
 *
 * @since 1.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugin activatation
 *
 * This function runs when user activates the plugin. Used in register_activation_hook in the main plugin file. 
 * @since 2.0
 */
function vwg_activate_plugin() {
    $option = get_option('vwg_settings_group');
    $option_uninstall_settings = get_option('vwg_uninstall_settings_group');

    if (false === $option) {
        $settings = array(
            'vwg_settings_icon' => 'far fa-play-circle',
            'vwg_settings_icon_color' => '#ffffff',
            'vwg_settings_video_controls' => 'controls',
            'vwg_settings_loop' => 'loop',
            'vwg_settings_muted' => 'muted',
            'vwg_settings_autoplay' => 'autoplay',
            'vwg_settings_show_first' => '',
            'vwg_settings_video_adapt_sizes' => '',
        );
        update_option( 'vwg_settings_group', $settings );
    }

    if (false === $option_uninstall_settings) {
        $settings = array(
            'vwg_settings_remove_settings_data' => '',
            'vwg_settings_remove_videos_data' => '',
        );
        update_option( 'vwg_uninstall_settings_group', $settings );
    }

}


/**
 * Load plugin text domain
 *
 * @since 1.0
 */
function vwg_load_plugin_textdomain() {
    load_plugin_textdomain( 'video-wc-gallery', false, '/video-wc-gallery/languages/' );
}
add_action( 'plugins_loaded', 'vwg_load_plugin_textdomain' );


/**
 * Added admin menu for Video Gallery for WooCommerce
 *
 * @since 2.0
 */
function vwg_register_video_gallery_menu() {
    global $vwg_adminPage, $vwg_pro_adminPage, $vwg_pro_activation_id;

    $vwg_adminPage = 'video-gallery_page_video-gallery-wc-settings';
    $vwg_pro_adminPage = 'video-gallery-pro_page_video-gallery-wc-settings';

    $vwg_logo_icon = 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNDAuMDQgODYuMTgiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO308L3N0eWxlPjwvZGVmcz48dGl0bGU+VW50aXRsZWQtMTwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNODkuOTQsNzVhMy4yMywzLjIzLDAsMCwxLTEuNTMsMi43M0w2OS4yMSw4OS40NWEzLjIzLDMuMjMsMCwwLDEtMy4yNC4wNiwzLjIsMy4yLDAsMCwxLTEuNjMtMi43OFY2My4yN0EzLjIyLDMuMjIsMCwwLDEsNjYsNjAuNDl2MGEzLjI2LDMuMjYsMCwwLDEsMy4yNC4wNmwxOS4yLDExLjc0QTMuMjEsMy4yMSwwLDAsMSw4OS45NCw3NVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC00Ljk4IC0zMS45MSkiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjEuNDEsMzEuOTFIMjguNTlhNS40OCw1LjQ4LDAsMCwwLTUuNDksNS40N3Y3NS4yNGE1LjQ4LDUuNDgsMCwwLDAsNS40OSw1LjQ3aDkyLjgyYTUuNDgsNS40OCwwLDAsMCw1LjQ5LTUuNDdWMzcuMzhBNS40OCw1LjQ4LDAsMCwwLDEyMS40MSwzMS45MVpNNzUsMTA5LjEyQTM0LjEyLDM0LjEyLDAsMSwxLDEwOS4xMiw3NSwzNC4xMiwzNC4xMiwwLDAsMSw3NSwxMDkuMTJaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNC45OCAtMzEuOTEpIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTQ1LDQwLjg4djY4LjI0SDEzNWMtMywwLTUuNDctMy43My01LjQ3LTguMzRWNDkuMjJjMC00LjYxLDIuNDUtOC4zNCw1LjQ3LTguMzRaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNC45OCAtMzEuOTEpIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMjAuMjcsNDkuMjJ2NTEuNTZjMCw0LjYxLTIuNDUsOC4zNC01LjQ3LDguMzRINVY0MC44OEgxNC44QzE3LjgyLDQwLjg4LDIwLjI3LDQ0LjYxLDIwLjI3LDQ5LjIyWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTQuOTggLTMxLjkxKSIvPjwvc3ZnPg==';

    add_menu_page(
        apply_filters('vwg_modify_strings', __('Video Gallery', 'video-wc-gallery')), // Page title
        apply_filters('vwg_modify_strings', __('Video Gallery', 'video-wc-gallery')), // Menu title
        'manage_options',
        'video-gallery-wc',
        'vwg_custom_settings',
        $vwg_logo_icon,
        56 // Position after WooCommerce (adjust as needed)
    );

    // Add a submenu page
    add_submenu_page(
        'video-gallery-wc',
        __('Settings', 'video-wc-gallery'), // Page title
        __('Settings', 'video-wc-gallery'), // Menu title
        'manage_options',
        'video-gallery-wc-settings',
        'vwg_custom_settings',
    );


    if (is_plugin_active('video-wc-gallery-pro/video-wc-gallery-pro.php') && $vwg_pro_activation_id) {
        add_action("load-{$vwg_pro_adminPage}", "vwg_add_metaboxes");
        add_action("admin_head-{$vwg_pro_adminPage}", "vwg_added_admin_scripts");

        add_action("load-{$vwg_pro_adminPage}", "vwg_remove_pro_version_metabox");
    } else{
        add_action("load-{$vwg_adminPage}", "vwg_add_metaboxes");
        add_action("admin_head-{$vwg_adminPage}", "vwg_added_admin_scripts");

        if (is_plugin_active('video-wc-gallery-pro/video-wc-gallery-pro.php')) {
            add_action("load-{$vwg_adminPage}", "vwg_remove_pro_version_metabox");
        }
    }
}
add_action('admin_menu', 'vwg_register_video_gallery_menu');


/**
 * Video Gallery admin menu remove submenu first element
 *
 * @since 2.0
 */
function vwg_customize_admin_menu() {
    global $submenu;

    foreach ($submenu as $key => $item) {
        if ($key == 'video-gallery-wc') {
            unset($submenu[$key][0]);
            break;
        }
    }
}
add_action('admin_menu', 'vwg_customize_admin_menu');


/**
 * Print direct link to plugin settings in plugins list in admin
 *
 * @since 2.0
 */
function vwg_settings_link( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=video-gallery-wc-settings' ) . '">' . __( 'Settings', 'video-wc-gallery' ) . '</a>',
            'get_pro' => '<a href="' . admin_url('admin.php?page=video-gallery-wc-settings#open-pro-dialog') . '" title="' . __('Get PRO', 'video-wc-gallery') . '"><b>' . __('Get PRO', 'video-wc-gallery') . '</b></a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . VWG_VIDEO_WOO_GALLERY . '/video-wc-gallery.php', 'vwg_settings_link' );

/**
 * Add donate and other links to plugins list
 *
 * @since 1.0
 */
function vwg_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'video-wc-gallery.php' ) !== false ) {
		$new_links = array(
				'donate' 	=> '<a href="https://revolut.me/mvalchev" target="_blank">Donate</a>',
				'hireme' 	=> '<a href="https://martinvalchev.com/#contact" target="_blank">Hire Me For A Project</a>',
				);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'vwg_plugin_row_meta', 10, 2 );

/**
 * Add footer info text
 *
 * @since 2.0
 */
function vwg_footer_text($default) {
    global $vwg_adminPage, $vwg_pro_adminPage;

    // Retun default on non-plugin pages
    $screen = get_current_screen();
    if ( $screen->id !== $vwg_adminPage && $screen->id !== $vwg_pro_adminPage ) {
        return $default;
    }

    $text = '<i><a target="_blank" href="#">Video Gallery for WooCommerce</a> v' . VWG_VERSION_NUM . ' by <a href="https://afowsoft.com/" title="' . __('Visit our site to get more great plugins', 'video-wc-gallery') . '" target="_blank">Afowsoft</a>.';
    $text .= ' Please <a target="_blank" href="https://wordpress.org/support/plugin/video-wc-gallery/#new-post" title="' . __('Rate the plugin', 'video-wc-gallery') . '">' . __('Rate the plugin ★★★★★', 'video-wc-gallery') . '</a>.</i> | <a href="https://translate.wordpress.org/projects/wp-plugins/video-wc-gallery/" target="_blank"><span class="dashicons dashicons-translation"></span></a> ';
    return $text;
}
add_filter('admin_footer_text', 'vwg_footer_text');


/**
 * Add donate notice
 *
 * @since 1.27
 */
function vwg_admin_init_notice_monthly() {

    if (is_plugin_active(VWG_VIDEO_WOO_GALLERY.'/video-wc-gallery.php')) {
        // Schedule the monthly event
        if (!wp_next_scheduled('vwg_monthly_admin_notice')) {
            wp_schedule_event(time(), 'monthly', 'vwg_monthly_admin_notice');
        }
    }

}
add_action('admin_init', 'vwg_admin_init_notice_monthly');

function vwg_monthly_admin_notice() {

    update_option('vwg_monthly_notice_dismissed', false);

}
add_action('vwg_monthly_admin_notice', 'vwg_monthly_admin_notice');

function vwg_display_monthly_admin_notice()
{

    $is_dismissed = get_option('vwg_monthly_notice_dismissed', false);

    if (!$is_dismissed) {
        ?>
        <div class="notice notice-info is-dismissible" id="vwg_monthly-donation-notice">
            <img src="https://ps.w.org/video-wc-gallery/assets/icon-128x128.png" style="max-width: 40px; position: absolute; top: 50%; left: 20px; transform: translateY(-50%);">
            <div class="vwg-notice-wrapper" style="margin-left: 60px; padding: 15px;">
                <p style="font-size: 16px; font-weight: bold;">🚀 <?php echo esc_html__('Help Us Improve Video Gallery for WooCommerce', 'video-wc-gallery'); ?>!</p>
                <p><?php echo esc_html__('Dear valued user,', 'video-wc-gallery'); ?></p>
                <p><?php echo esc_html__('We hope you are enjoying using Video Gallery for WooCommerce. Your support is crucial to the continued development and improvement of our plugin.', 'video-wc-gallery'); ?></p>
                <p><?php echo esc_html__('Consider making a donation to ensure we can keep providing you with top-notch features, updates, and support.', 'video-wc-gallery'); ?></p>
                <a href="https://revolut.me/mvalchev" target="_blank" class="button button-primary" style="text-decoration: none; color: #fff; padding: 8px 16px; background-color: #0073aa; border-color: #0073aa; border-radius: 4px; display: inline-block;"><?php echo esc_html__('Make a Donation', 'video-wc-gallery'); ?></a>
            </div>
        </div>
        <script>
            jQuery(document).on('click', '#vwg_monthly-donation-notice .notice-dismiss', function () {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_monthly_notice'
                });
            });
        </script>
        <style>
            @media screen and (max-width: 480px) { .vwg-notice-wrapper { margin-left: 0 !important; } #vwg_monthly-donation-notice img { left: unset !important; right: 20px !important; top: 90px !important; } }
        </style>
        <?php
    }
}
add_action('admin_notices', 'vwg_display_monthly_admin_notice');

function vwg_dismiss_monthly_notice() {

    update_option('vwg_monthly_notice_dismissed', true);

    wp_die();
}
add_action('wp_ajax_dismiss_monthly_notice', 'vwg_dismiss_monthly_notice');

