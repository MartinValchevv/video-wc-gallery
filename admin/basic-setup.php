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
 * @since 1.24
 */
function vwg_activate_plugin() {
    $option = get_option('vwg_settings_group');

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
            'vwg_settings_remove_settings_data' => '',
            'vwg_settings_remove_videos_data' => '',
        );
        update_option( 'vwg_settings_group', $settings );
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
 * @since 1.30
 */
function vwg_register_video_gallery_menu() {

    $vwg_logo_icon = 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNDAuMDQgODYuMTgiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO308L3N0eWxlPjwvZGVmcz48dGl0bGU+VW50aXRsZWQtMTwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNODkuOTQsNzVhMy4yMywzLjIzLDAsMCwxLTEuNTMsMi43M0w2OS4yMSw4OS40NWEzLjIzLDMuMjMsMCwwLDEtMy4yNC4wNiwzLjIsMy4yLDAsMCwxLTEuNjMtMi43OFY2My4yN0EzLjIyLDMuMjIsMCwwLDEsNjYsNjAuNDl2MGEzLjI2LDMuMjYsMCwwLDEsMy4yNC4wNmwxOS4yLDExLjc0QTMuMjEsMy4yMSwwLDAsMSw4OS45NCw3NVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC00Ljk4IC0zMS45MSkiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjEuNDEsMzEuOTFIMjguNTlhNS40OCw1LjQ4LDAsMCwwLTUuNDksNS40N3Y3NS4yNGE1LjQ4LDUuNDgsMCwwLDAsNS40OSw1LjQ3aDkyLjgyYTUuNDgsNS40OCwwLDAsMCw1LjQ5LTUuNDdWMzcuMzhBNS40OCw1LjQ4LDAsMCwwLDEyMS40MSwzMS45MVpNNzUsMTA5LjEyQTM0LjEyLDM0LjEyLDAsMSwxLDEwOS4xMiw3NSwzNC4xMiwzNC4xMiwwLDAsMSw3NSwxMDkuMTJaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNC45OCAtMzEuOTEpIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTQ1LDQwLjg4djY4LjI0SDEzNWMtMywwLTUuNDctMy43My01LjQ3LTguMzRWNDkuMjJjMC00LjYxLDIuNDUtOC4zNCw1LjQ3LTguMzRaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNC45OCAtMzEuOTEpIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMjAuMjcsNDkuMjJ2NTEuNTZjMCw0LjYxLTIuNDUsOC4zNC01LjQ3LDguMzRINVY0MC44OEgxNC44QzE3LjgyLDQwLjg4LDIwLjI3LDQ0LjYxLDIwLjI3LDQ5LjIyWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTQuOTggLTMxLjkxKSIvPjwvc3ZnPg==';

    add_menu_page(
        __( 'Video Gallery', 'video-wc-gallery' ), // Page title
        __( 'Video Gallery', 'video-wc-gallery' ), // Menu title
        'manage_options',
        'admin.php?page=wc-settings&tab=vwg_tab',
        null,
        $vwg_logo_icon,
        56 // Position after WooCommerce (adjust as needed)
    );
}
add_action('admin_menu', 'vwg_register_video_gallery_menu');


/**
 * Print direct link to plugin settings in plugins list in admin
 *
 * @since 1.0
 */
function vwg_settings_link( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=vwg_tab' ) . '">' . __( 'Settings', 'video-wc-gallery' ) . '</a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . VWG_VIDEO_WOO_GALLERY . '/video-wc-gallery.php', 'vwg_settings_link' );

/**
 * Add donate and other links to plugins list
 *
 * @since 1.34
 */
function vwg_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'video-wc-gallery.php' ) !== false ) {
		$new_links = array(
				'donate' 	=> '<a href="https://linktr.ee/martinvalchev" target="_blank">Donate</a>',
				'hireme' 	=> '<a href="https://martinvalchev.com/#contact" target="_blank">Hire Me For A Project</a>',
				);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'vwg_plugin_row_meta', 10, 2 );


/**
 * Add donate notice
 *
 * @since 1.34
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
                <a href="https://linktr.ee/martinvalchev" target="_blank" class="button button-primary" style="text-decoration: none; color: #fff; padding: 8px 16px; background-color: #0073aa; border-color: #0073aa; border-radius: 4px; display: inline-block;"><?php echo esc_html__('Make a Donation', 'video-wc-gallery'); ?></a>
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


/**
 * Feedback when deactivate plugin view - STOP
 *
 * @since 1.18
 */
function vwg_feedback_dialog() {
    ?>
	<div id="vwg-popup-container" class="popup-container">
		<div class="popup-content">
			<h3 style="margin-top: 0;"><?php echo esc_html__('Quick Feedback' , 'video-wc-gallery') ?></h3>
            <p style="font-weight: 600;"><?php echo esc_html__('If you have a moment, please share why you are deactivating Video Gallery for WooCommerce' , 'video-wc-gallery') ?></p>
            <hr style="margin-bottom: 20px;">
			<form id="vwg-feedback-form">
				<div class="feedback-option">
					<input type="radio" name="reason" value="needed" id="vwg_needed" required>
					<label for="vwg_needed"><?php echo esc_html__('I no longer need the plugin' , 'video-wc-gallery') ?></label>
				</div>
				<div class="feedback-option">
					<input type="radio" name="reason" value="alternative" id="vwg_alternative" required>
					<label for="vwg_alternative"><?php echo esc_html__('I found a better plugin' , 'video-wc-gallery') ?></label>
				</div>
                <div class="feedback-option hidden" id="vwg_which-plugin">
                    <input type="text" placeholder="<?php echo esc_html__('Please share which plugin' , 'video-wc-gallery') ?>" name="which-plugin" id="vwg_which-plugin" style="width: 100%;"/>
                </div>
				<div class="feedback-option">
					<input type="radio" name="reason" value="get_plugin_to_work" id="vwg_get_plugin_to_work" required>
					<label for="vwg_get_plugin_to_work"><?php echo esc_html__('I couldn\'t get the plugin to work' , 'video-wc-gallery') ?></label>
				</div>
				<div class="feedback-option">
					<input type="radio" name="reason" value="temporary" id="vwg_temporary" required>
					<label for="vwg_temporary"><?php echo esc_html__('It\'s a temporary deactivation' , 'video-wc-gallery') ?></label>
				</div>
                <div class="feedback-option">
                    <input type="radio" name="reason" value="expectations" id="vwg_expectations" required>
                    <label for="vwg_expectations"><?php echo esc_html__('Plugin was not meeting expectations' , 'video-wc-gallery') ?></label>
                </div>
				<div class="feedback-option">
					<input type="radio" name="reason" value="other" id="vwg_other" required>
					<label for="vwg_other"><?php echo esc_html__('Other' , 'video-wc-gallery') ?></label>
				</div>
				<div class="feedback-option hidden" id="vwg_other-reason">
					<textarea placeholder="<?php echo esc_html__('Please share the reason' , 'video-wc-gallery') ?>" name="other-reason-text" id="vwg_other-reason-text" rows="3" style="width: 100%;"></textarea>
				</div>
				<button type="submit"><?php echo esc_html__('Submit & Deactivate' , 'video-wc-gallery') ?></button>
                <br>
                <a class="vwg-skip" href="javascript:;"><?php echo esc_html__('Skip & Deactivate' , 'video-wc-gallery') ?></a>
			</form>
		</div>
	</div>

	<style>
		.popup-container {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0, 0, 0, 0.7);
			z-index: 9999;
			display: flex;
			justify-content: center;
			align-items: center;
			visibility: hidden;
			opacity: 0;
			transition: visibility 0s, opacity 0.3s ease;
		}

		.popup-container.show {
			visibility: visible;
			opacity: 1;
		}

		.popup-content {
			background-color: #fff;
			padding: 20px;
			border-radius: 5px;
			box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
			max-width: 500px;
			width: 100%;
			text-align: center;
		}

		.popup-content h2 {
			font-size: 24px;
			margin-bottom: 20px;
		}

		.feedback-option {
			display: flex;
			align-items: center;
			margin-bottom: 10px;
		}

		.feedback-option label {
			margin-left: 10px;
		}

		.hidden {
			display: none;
		}

        #vwg-feedback-form button {
            background-color: #222;
            border-radius: 3px;
            color: #fff;
            line-height: 1;
            padding: 12px 20px;
            font-size: 13px;
			min-width: 180px;
            height: 38px;
            border-width: 0;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 5px;
        }

        .vwg-skip {
            font-size: 12px;
            color:#a4afb7;
            text-decoration: none;
            font-weight: 600;
        }

        .vwg-skip:hover {
            color:#a4afb7;
            text-decoration: underline;
        }

	</style>
    <?php
}
//add_action( 'admin_footer-plugins.php', 'vwg_feedback_dialog' );


/**
 * Feedback send email - STOP
 *
 * @since 1.18
 */
function vwg_send_deactivation_email() {

    $deactivate_reasons = [
        'needed' => [
            'title' => esc_html__( 'I no longer need the plugin', 'video-wc-gallery' ),
        ],
        'alternative' => [
            'title' => esc_html__( 'I found a better plugin', 'video-wc-gallery' ),
        ],
        'get_plugin_to_work' => [
            'title' => esc_html__( 'I couldn\'t get the plugin to work', 'video-wc-gallery' ),
        ],
        'temporary' => [
            'title' => esc_html__( 'It\'s a temporary deactivation', 'video-wc-gallery' ),
        ],
        'expectations' => [
            'title' => esc_html__( 'Plugin was not meeting expectations', 'video-wc-gallery' ),
        ],
        'other' => [
            'title' => esc_html__( 'Other', 'video-wc-gallery' ),
        ],
    ];


    $form_data = array_map( 'sanitize_text_field', $_POST['form_data'] );

    $to = 'plugins@martinvalchev.com';
    $headers[] = 'From: '.get_bloginfo('name').'<'.get_option('admin_email').'>';
    $headers[] = 'Content-Type: text/html';
    $subject = VWG_PLUGIN_NAME.' deactivated';

    $reason_title = $deactivate_reasons[$form_data['reason']]['title'];

    ob_start();
    ?>
    <html>
        <body>
        <p>The plugin <?php echo esc_html(VWG_PLUGIN_NAME)?> has been deactivated with the following reason:</p>
        <p><strong><?php echo esc_html($reason_title )?></strong></p>
        <?php if (!empty($form_data['which-plugin'])) : ?>
            <p>Plugin replaced with:</p>
            <p><strong><?php echo esc_html($form_data['which-plugin'])?></strong></p>
        <?php endif; ?>
        <?php if (!empty($form_data['other-reason-text'])) : ?>
            <p>Additional details:</p>
            <p><strong><?php echo esc_html($form_data['other-reason-text'])?></strong></p>
        <?php endif; ?>
        </body>
    </html>
    <?php
    $html = ob_get_clean();

    wp_mail($to, $subject, $html, $headers);
    wp_send_json_success('Email sent successfully');

    wp_die();

}
//add_action('wp_ajax_vwg_send_deactivation_feedback_email', 'vwg_send_deactivation_email');
//add_action('wp_ajax_nopriv_vwg_send_deactivation_feedback_email', 'vwg_send_deactivation_email');

