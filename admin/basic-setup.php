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
 * @since 1.14
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
 * Feedback when deactivate plugin view
 *
 * @since 1.1
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
add_action( 'admin_footer-plugins.php', 'vwg_feedback_dialog' );


/**
 * Feedback send email
 *
 * @since 1.1
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
add_action('wp_ajax_vwg_send_deactivation_feedback_email', 'vwg_send_deactivation_email');
add_action('wp_ajax_nopriv_vwg_send_deactivation_feedback_email', 'vwg_send_deactivation_email');

