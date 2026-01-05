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
 * @since 2.3
 */
function vwg_register_video_gallery_menu() {
    global $vwg_adminPage, $vwg_pro_adminPage, $vwg_pro_activation_id;

    $vwg_adminPage = 'video-gallery_page_video-gallery-wc-settings';
    $vwg_pro_adminPage = 'video-gallery-pro_page_video-gallery-wc-settings';

    $vwg_logo_icon = 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNTYgMjU2Ij48ZGVmcz48c3R5bGU+LmNscy0xe2ZpbGw6I2ZmZjt9PC9zdHlsZT48L2RlZnM+PHRpdGxlPmljb24tMjU2eDI1Nl93aGl0ZTwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMjIzLjU4LDczLjI2YTkuNDcsOS40NywwLDAsMC03LjI3LTMuNjFIMTkyLjIyYTU1LjM0LDU1LjM0LDAsMCwxLDUuMTEsMTAuMjhoMTQuOTVhMS44OSwxLjg5LDAsMCwxLDEuODksMS44OXYxLjg5YTEuODksMS44OSwwLDAsMS0xLjg5LDEuODlIMTkzLjIyYTQzLjgsNDMuOCwwLDAsMC0yLTUuNjcsNTAuNDcsNTAuNDcsMCwwLDAtNS44NC0xMC4yOCw1MSw1MSwwLDAsMC04Mi40NCwwLDUwLDUwLDAsMCwwLTUuODUsMTAuMjgsNDMuNzYsNDMuNzYsMCwwLDAtMiw1LjY3SDg5LjE3QTUyLjYyLDUyLjYyLDAsMCwxLDkxLDc5LjkzYTU0LjEzLDU0LjEzLDAsMCwxLDUuMTEtMTAuMjhINzAuNTFjLTgtMjIuOTItMjUuMy0yMy43LTMxLjg1LTIzLjA5YTIuMjksMi4yOSwwLDAsMC0yLDIuODJsMS4yNSw1YTIsMiwwLDAsMCwyLjE0LDEuNDlDNDQuMjIsNTUuNDcsNTQuODgsNTYsNjAuOSw3MXYwYTEuMTYsMS4xNiwwLDAsMCwwLC40OWw1LjgsMjAuNzNIOTMuNjJhNDQuNTcsNDQuNTcsMCwwLDAtLjUxLDUuNjdINjguMzJsMi4xMSw3LjU3SDc5LjhhMS45MSwxLjkxLDAsMCwxLDEuOSwxLjl2MS44OWExLjksMS45LDAsMCwxLTEuOSwxLjg5SDcybDIuNjUsOS40Nkg5NC45NGExLjkxLDEuOTEsMCwwLDEsMS45LDEuOXYxLjg5YTEuOSwxLjksMCwwLDEtMS45LDEuODlINzYuMjZsOS4yOCwzMy4xNmExMi43NiwxMi43NiwwLDAsMC00Ljc5LDEwLjM3YzAsMTEsOC4yNSwxNiwxNiwxNi4wOWExNCwxNCwwLDAsMC0zLjY1LDkuNDYsMTQuMiwxNC4yLDAsMSwwLDIwLjQyLTEyLjc0YzQzLjkzLTguOTMsNzAtMTAuMzcsNzcuNTQtNC4yNmEzLjYxLDMuNjEsMCwwLDEsMS4yNiwxLjczLDEuNCwxLjQsMCwwLDAsMS4zMSwxLjA3aDYuODdhMS4zNywxLjM3LDAsMCwwLDEuMzQtMS41NUExMi43NiwxMi43NiwwLDAsMCwxOTcsMTcxYy0xMy44LTExLjE3LTU3LjI5LTMuNzEtOTMuNjIsNC4wNS0zLC42My01Ljg1LDEuMjMtNi43LDEuMzYtMi40OCwwLTYuNDQtMS02LjQ0LTYuNjIsMC0yLjg2LDIuMzEtNCw0LjI0LTQuNDNoMGwxMDEuOTEtOS4yNWExMC40NiwxMC40NiwwLDAsMCw5LjItNy41NEwyMjUsODEuMjVBOSw5LDAsMCwwLDIyMy41OCw3My4yNlpNMTEzLjg3LDE5NS4zN2E2LjYzLDYuNjMsMCwxLDEtNi42My02LjYzQTYuNjMsNi42MywwLDAsMSwxMTMuODcsMTk1LjM3Wk05MC42OCwxMzkuNTRhMS44OSwxLjg5LDAsMCwxLTEuODktMS44OVYxMzZhMi4xMSwyLjExLDAsMCwxLDIuMS0yLjFoMTUuMjRhNDUuMjYsNDUuMjYsMCwwLDAsNiw1LjY4Wm01My40NywxLjg5QTQxLjYyLDQxLjYyLDAsMCwxLDEwNSw4NS42YTQwLjI5LDQwLjI5LDAsMCwxLDIuNTctNS42Nyw0MSw0MSwwLDAsMSw3LjktMTAuMjgsNDEuNTEsNDEuNTEsMCwwLDEsNTcuMzQsMCw0MC45Miw0MC45MiwwLDAsMSw3Ljg5LDEwLjI4LDQwLjM0LDQwLjM0LDAsMCwxLDIuNTgsNS42Nyw0MS42NSw0MS42NSwwLDAsMS0zOS4xNCw1NS44M1oiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xNzcuMjcsMjA5LjU2YTE0LjIsMTQuMiwwLDEsMSwxNC4xOS0xNC4xOUExNC4yMSwxNC4yMSwwLDAsMSwxNzcuMjcsMjA5LjU2Wm0wLTIwLjgyYTYuNjMsNi42MywwLDEsMCw2LjYyLDYuNjNBNi42NCw2LjY0LDAsMCwwLDE3Ny4yNywxODguNzRaIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTY1LjY3LDEwMy43MWwtMTUuMjQsOUwxMzUuMiwxMjEuNmE1Ljc3LDUuNzcsMCwwLDEtOC42OS00LjkyTDEyNi40LDk5bC0uMS0xMy40LDAtNC4yNmE1LjkyLDUuOTIsMCwwLDEsLjE3LTEuNDEsNS43NSw1Ljc1LDAsMCwxLDguNDMtMy42NGw2LjM5LDMuNjQsOSw1LjA5LDEsLjU4LDE0LjMzLDguMTRBNS43NSw1Ljc1LDAsMCwxLDE2NS42NywxMDMuNzFaIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMzEuNzEsOTIuMjNINThhMSwxLDAsMCwxLDEsMVY5Ni45YTEsMSwwLDAsMS0xLDFIMzEuNmExLDEsMCwwLDEtMS0xVjkzLjM0QTEuMTEsMS4xMSwwLDAsMSwzMS43MSw5Mi4yM1oiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik00NSwxMDUuNDdINTkuODhhMSwxLDAsMCwxLDEsMXYzLjY4YTEsMSwwLDAsMS0xLDFoLTE1YTEsMSwwLDAsMS0xLTF2LTMuNTZBMS4xMSwxLjExLDAsMCwxLDQ1LDEwNS40N1oiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik01Mi41MywxMjAuNjFoMTNhMSwxLDAsMCwxLDEsMXYzLjY4YTEsMSwwLDAsMS0xLDFINTIuNDFhMSwxLDAsMCwxLTEtMXYtMy41NkExLjExLDEuMTEsMCwwLDEsNTIuNTMsMTIwLjYxWiIvPjwvc3ZnPg==';

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
            'get_pro' => '<a href="https://nitramix.com/projects/video-gallery-for-woocommerce" title="' . __('Get PRO', 'video-wc-gallery') . '"><b>' . __('Get PRO', 'video-wc-gallery') . '</b></a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . VWG_VIDEO_WOO_GALLERY . '/video-wc-gallery.php', 'vwg_settings_link' );

/**
 * Add donate and other links to plugins list
 *
 * @since 2.0
 */
function vwg_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'video-wc-gallery.php' ) !== false ) {
		$new_links = array(
				'donate' 	=> '<a href="https://nitramix.com/donate/" target="_blank">Donate</a>',
				'hireme' 	=> '<a href="https://nitramix.com/contact/" target="_blank">Hire Me For A Project</a>',
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

    $text = '<i><a target="_blank" href="https://nitramix.com/projects/video-gallery-for-woocommerce">Video Gallery for WooCommerce</a> v' . VWG_VERSION_NUM . ' by <a href="https://nitramix.com/" title="' . __('Visit our site to get more great plugins', 'video-wc-gallery') . '" target="_blank">Nitramix</a>.';
    $text .= ' Please <a target="_blank" href="https://wordpress.org/support/plugin/video-wc-gallery/reviews/?filter=5" title="' . __('Rate the plugin', 'video-wc-gallery') . '">' . __('Rate the plugin ★★★★★', 'video-wc-gallery') . '</a>.</i> | <a href="https://translate.wordpress.org/projects/wp-plugins/video-wc-gallery/" target="_blank"><span class="dashicons dashicons-translation"></span></a> ';
    return $text;
}
add_filter('admin_footer_text', 'vwg_footer_text');


/**
 * Add donate notice
 *
 * @since 2.4
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
        <div class="notice notice-info is-dismissible" style="border-left-color: #7e3fec;" id="vwg_monthly-donation-notice">
            <img src="<?php echo esc_url(VWG_VIDEO_WOO_GALLERY_URL); ?>includes/images/vwg-logo.png" style="max-width: 40px; position: absolute; top: 50%; left: 20px; transform: translateY(-50%);">
            <div class="vwg-notice-wrapper" style="margin-left: 60px; padding: 15px;">
                <p style="font-size: 16px; font-weight: bold;">🚀 <?php echo esc_html__('Help Us Improve Video Gallery for WooCommerce', 'video-wc-gallery'); ?>!</p>
                <p><?php echo esc_html__('Dear valued user,', 'video-wc-gallery'); ?></p>
                <p><?php echo esc_html__('We hope you are enjoying using Video Gallery for WooCommerce. Your support is crucial to the continued development and improvement of our plugin.', 'video-wc-gallery'); ?></p>
                <p><?php echo esc_html__('Consider making a donation to ensure we can keep providing you with top-notch features, updates, and support.', 'video-wc-gallery'); ?></p>
                <a href="https://nitramix.com/donate/" target="_blank" class="button button-primary" style="text-decoration: none; color: #fff; background-color: #7e3fec; border-color: #7e3fec; border-radius: 4px; display: inline-block;"><?php echo esc_html__('Make a Donation', 'video-wc-gallery'); ?></a>
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

