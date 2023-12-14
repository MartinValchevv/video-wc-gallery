<?php
/**
 * Operations of the plugin for themes.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$option = get_option('vwg_settings_group');

/**
 * Active theme checker for different logic
 *
 * @since 1.18
 */
function vwg_active_theme_checker()
{

    if (function_exists('flatsome_setup') || stripos(wp_get_theme()->get('Name'), 'Flatsome') !== false) {
        $use_different_logic = 'Flatsome';
    } else {
        $use_different_logic = 'default';
    }

    return $use_different_logic;
}


/**
 * Overwrite woocommerce templates for different themes
 *
 * @since 1.18
 */
function vwg_custom_wc_template_overwrite_for_themes($located, $template_name, $args, $template_path, $default_path)
{
    global $option;

    if (isset($option['vwg_settings_show_first']) && $option['vwg_settings_show_first'] == 1) {
        if (vwg_active_theme_checker() === 'default') {
            if ($template_name === 'single-product/product-image.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/product-image.php';
            }
        } elseif (vwg_active_theme_checker() === 'Flatsome') {
            if ($template_name === 'single-product/product-image.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-image-flatsome-theme.php';
            } elseif ($template_name === 'woocommerce/single-product/product-gallery-thumbnails.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-gallery-thumbnails-flatsome-theme.php';
            }
        }
    } else {
        if (vwg_active_theme_checker() === 'Flatsome') {
            if ($template_name === 'woocommerce/single-product/product-gallery-thumbnails.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-gallery-thumbnails-flatsome-theme.php';
            }
        }
    }

    return $located;
}
add_filter('wc_get_template', 'vwg_custom_wc_template_overwrite_for_themes', 10, 5);

/**
 * Modify part template for theme
 *
 * @since 1.18
 */
function vwg_wc_template_part_modify($template, $slug, $name) {
    // Check if it's the template part you want to modify
    if ($slug === 'single-product/product-image' && $name === 'vertical' && vwg_active_theme_checker() === 'Flatsome') {
        // Modify the template path here
        $modify_template_path = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-image-vertical-flatsome-theme.php';

        // Check if the custom template file exists
        if (file_exists($modify_template_path)) {
            return $modify_template_path;
        }
    }

    return $template;
}
add_filter('wc_get_template_part', 'vwg_wc_template_part_modify', 10, 3);

