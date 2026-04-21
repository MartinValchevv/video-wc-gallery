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
 * @since 2.6
 */
function vwg_active_theme_checker()
{

    if (function_exists('flatsome_setup') || stripos(wp_get_theme()->get('Name'), 'Flatsome') !== false) {
        $use_different_logic = 'Flatsome';
    } elseif (function_exists('blocksy') || defined('BLOCKSY_VERSION') || class_exists('Blocksy_Manager')) {
        $use_different_logic = 'Blocksy';
    } elseif (defined('ASTRA_THEME_VERSION') || function_exists('astra_get_option') || stripos(wp_get_theme()->get('Name'), 'Astra') !== false || stripos((string) wp_get_theme()->get('Template'), 'astra') !== false) {
        $use_different_logic = 'Astra';
    } else {
        $use_different_logic = 'default';
    }

    return $use_different_logic;
}


/**
 * Overwrite woocommerce templates for different themes
 *
 * @since 2.6
 */
function vwg_custom_wc_template_overwrite_for_themes($located, $template_name, $args, $template_path, $default_path)
{
    global $option;

    if (isset($option['vwg_settings_show_first']) && $option['vwg_settings_show_first'] == 1) {
        if (vwg_active_theme_checker() === 'default' || vwg_active_theme_checker() === 'Astra') {
            if ($template_name === 'single-product/product-image.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/product-image.php';
            }
        } elseif (vwg_active_theme_checker() === 'Flatsome') {
            if ($template_name === 'single-product/product-image.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-image-flatsome-theme.php';
            } elseif ($template_name === 'woocommerce/single-product/product-gallery-thumbnails.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-gallery-thumbnails-flatsome-theme.php';
            }
        } elseif (vwg_active_theme_checker() === 'Blocksy') {
            if ($template_name === 'single-product/product-image.php') {
                // Use our default Woo template (contains the video-first hook)
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/product-image.php';
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
 * Flatsome Modify part template for theme
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



/**
 * Blocksy compatibility: force default Woo gallery when product has videos
 *
 * @since 2.2
 */
function vwg_blocksy_force_default_gallery( $use_default ) {
    $is_blocksy = function_exists( 'blocksy' ) || defined( 'BLOCKSY_VERSION' ) || class_exists( 'Blocksy_Manager' );
    if ( ! $is_blocksy ) {
        return $use_default;
    }

    $opt = get_option('vwg_settings_group');
    if ( isset($opt['vwg_settings_show_first']) && $opt['vwg_settings_show_first'] == 1 ) {
        return true;
    }

    global $product;
    if ( ! is_object( $product ) ) {
        $product = wc_get_product( get_the_ID() );
    }

    if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
        return $use_default;
    }

    $videos = get_post_meta( $product->get_id(), 'vwg_video_url', true );
    if ( ! empty( $videos ) ) {
        return true;
    }

    return $use_default;
}
add_filter( 'blocksy:woocommerce:product-view:use-default', 'vwg_blocksy_force_default_gallery', 5, 1 );

/**
 * Blocksy: override gallery content with our template when show_first or videos exist
 *
 * @since 2.2
 */
function vwg_blocksy_gallery_override_content( $content, $product, $gallery_images, $is_single ) {
    if ( vwg_active_theme_checker() !== 'Blocksy' ) {
        return $content;
    }

    $opt = get_option('vwg_settings_group');
    $show_first = isset($opt['vwg_settings_show_first']) && $opt['vwg_settings_show_first'] == 1;

    $has_videos = ( is_object( $product ) && method_exists( $product, 'get_id' ) )
        ? get_post_meta( $product->get_id(), 'vwg_video_url', true )
        : '';

    if ( ! $show_first && empty( $has_videos ) ) {
        return $content;
    }

    ob_start();
    wc_get_template(
        'single-product/product-image.php',
        array(),
        '',
        VWG_VIDEO_WOO_GALLERY_DIR
    );
    return ob_get_clean();
}
add_filter( 'blocksy:woocommerce:product-view:content', 'vwg_blocksy_gallery_override_content', 5, 4 );

/**
 * Astra / Astra Pro compatibility.
 *
 * Astra Pro overrides the Woo single-product gallery when its layout option
 * is "vertical-slider" or "horizontal-slider". It does so via
 * `include_once` on its own template, which bypasses `wc_get_template` and
 * therefore bypasses our template filter — so videos never get rendered.
 *
 * Astra Pro exposes `astra_addon_override_single_product_layout`; returning
 * false short-circuits the override so the standard Woo flow runs and our
 * VWG template / hooks take over. We only neutralize the override when the
 * product actually has videos (or show-first is enabled), so products
 * without videos keep Astra Pro's custom gallery layout untouched.
 *
 * @since 2.6
 */
function vwg_astra_force_default_gallery( $enabled ) {
    if ( vwg_active_theme_checker() !== 'Astra' ) {
        return $enabled;
    }

    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return $enabled;
    }

    $opt = get_option( 'vwg_settings_group' );
    if ( isset( $opt['vwg_settings_show_first'] ) && $opt['vwg_settings_show_first'] == 1 ) {
        return false;
    }

    global $product;
    if ( ! is_object( $product ) ) {
        $product = wc_get_product( get_the_ID() );
    }

    if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
        return $enabled;
    }

    $videos = get_post_meta( $product->get_id(), 'vwg_video_url', true );
    if ( ! empty( $videos ) ) {
        return false;
    }

    return $enabled;
}
add_filter( 'astra_addon_override_single_product_layout', 'vwg_astra_force_default_gallery', 5, 1 );


