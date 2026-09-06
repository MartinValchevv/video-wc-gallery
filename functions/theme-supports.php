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
 * @since 2.8
 */
function vwg_active_theme_checker()
{

    if (function_exists('flatsome_setup') || stripos(wp_get_theme()->get('Name'), 'Flatsome') !== false) {
        $use_different_logic = 'Flatsome';
    } elseif (function_exists('blocksy') || defined('BLOCKSY_VERSION') || class_exists('Blocksy_Manager')) {
        $use_different_logic = 'Blocksy';
    } elseif (defined('ASTRA_THEME_VERSION') || function_exists('astra_get_option') || stripos(wp_get_theme()->get('Name'), 'Astra') !== false || stripos((string) wp_get_theme()->get('Template'), 'astra') !== false) {
        $use_different_logic = 'Astra';
    } elseif (defined('PORTO_VERSION') || function_exists('porto_setup') || stripos(wp_get_theme()->get('Name'), 'Porto') !== false || stripos((string) wp_get_theme()->get('Template'), 'porto') !== false) {
        $use_different_logic = 'Porto';
    } else {
        $use_different_logic = 'default';
    }

    return $use_different_logic;
}


/**
 * Overwrite woocommerce templates for different themes
 *
 * @since 2.8
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
        } elseif (vwg_active_theme_checker() === 'Porto') {
            if ($template_name === 'single-product/product-image.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/product-image-porto-theme.php';
            } elseif ($template_name === 'single-product/product-thumbnails.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/product-thumbnails-porto-theme.php';
            }
        }
    } else {
        if (vwg_active_theme_checker() === 'Flatsome') {
            if ($template_name === 'woocommerce/single-product/product-gallery-thumbnails.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-gallery-thumbnails-flatsome-theme.php';
            }
        } elseif (vwg_active_theme_checker() === 'Porto') {
            // Porto's woocommerce_product_thumbnails action fires outside the Owl Carousel,
            // so the default hook placement dumps videos at the bottom of the gallery.
            // Always use our overridden templates which inject the hook inside the carousel
            // and the thumbs slider, regardless of the "show first" setting.
            if ($template_name === 'single-product/product-image.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/product-image-porto-theme.php';
            } elseif ($template_name === 'single-product/product-thumbnails.php') {
                $located = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/product-thumbnails-porto-theme.php';
            }
        }
    }

    return $located;
}
add_filter('wc_get_template', 'vwg_custom_wc_template_overwrite_for_themes', 10, 5);

/**
 * Flatsome and Porto Modify part template for theme
 *
 * @since 2.8
 */
function vwg_wc_template_part_modify($template, $slug, $name) {
    $theme = function_exists('vwg_active_theme_checker') ? vwg_active_theme_checker() : 'default';

    // Flatsome vertical layout
    if ($slug === 'single-product/product-image' && $name === 'vertical' && $theme === 'Flatsome') {
        $modify_template_path = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/flatsome/product-image-vertical-flatsome-theme.php';
        if (file_exists($modify_template_path)) {
            return $modify_template_path;
        }
    }

    // Porto: builder/extended layouts load product-image.php via wc_get_template_part
    // (not wc_get_template), so we have to override here too. Same template,
    // independent of the $name suffix.
    if ($slug === 'single-product/product-image' && $theme === 'Porto') {
        $modify_template_path = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/product-image-porto-theme.php';
        if (file_exists($modify_template_path)) {
            return $modify_template_path;
        }
    }
    if ($slug === 'single-product/product-thumbnails' && $theme === 'Porto') {
        $modify_template_path = VWG_VIDEO_WOO_GALLERY_DIR . 'woocommerce-overwrite/templates/single-product/porto/product-thumbnails-porto-theme.php';
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
 * @since 2.11
 */
function vwg_blocksy_uses_forced_default_gallery() {
    static $result = null;

    if ( null !== $result ) {
        return $result;
    }

    if ( vwg_active_theme_checker() !== 'Blocksy' ) {
        $result = false;
        return $result;
    }

    $opt = get_option( 'vwg_settings_group' );
    if ( isset( $opt['vwg_settings_show_first'] ) && $opt['vwg_settings_show_first'] == 1 ) {
        $result = true;
        return $result;
    }

    global $product;
    if ( ! is_object( $product ) ) {
        $product = wc_get_product( get_the_ID() );
    }

    if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
        // Blocksy asks this before the main query has a product to look at, so
        // "no" here means "cannot tell yet", not "no videos". Answer without
        // caching, or the real answer never gets a chance to be computed.
        return false;
    }

    $result = ! empty( get_post_meta( $product->get_id(), 'vwg_video_url', true ) );

    return $result;
}

/**
 * Blocksy compatibility: force default Woo gallery when product has videos
 *
 * @since 2.11
 */
function vwg_blocksy_force_default_gallery( $use_default ) {
    return vwg_blocksy_uses_forced_default_gallery() ? true : $use_default;
}
add_filter( 'blocksy:woocommerce:product-view:use-default', 'vwg_blocksy_force_default_gallery', 5, 1 );

/**
 * Blocksy: override gallery content with our template when show_first or videos exist
 *
 * @since 2.11
 */
function vwg_blocksy_gallery_override_content( $content, $product, $gallery_images, $is_single ) {
    if ( ! vwg_blocksy_uses_forced_default_gallery() ) {
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
 * Blocksy: the theme's own gallery arrow icons.
 *
 * @since 2.11
 */
function vwg_blocksy_arrow_icons() {
    static $icons = null;

    if ( null !== $icons ) {
        return $icons;
    }

    $icons = array();

    if ( ! function_exists( 'blocksy_flexy' ) ) {
        return $icons;
    }

    ob_start();
    blocksy_flexy( array( 'enable' => false, 'has_pills' => false ) );
    $markup = ob_get_clean();

    foreach ( array( 'prev', 'next' ) as $direction ) {
        $found = preg_match(
            '/<span class="flexy-arrow-' . $direction . '[^"]*">\s*(.+?)\s*<\/span>/s',
            $markup,
            $match
        );

        // A span holding only whitespace still matches; treat it as missing.
        if ( $found && '' !== trim( $match[1] ) ) {
            $icons[ $direction ] = trim( $match[1] );
        }
    }

    // All or nothing — one arrow without the other is worse than neither.
    if ( ! isset( $icons['prev'], $icons['next'] ) ) {
        $icons = array();
    }

    return $icons;
}

/**
 * Blocksy: restore the gallery navigation arrows.
 *
 * When a product has videos we force Blocksy off its own "flexy" gallery and
 * back onto the stock WooCommerce FlexSlider one, so the theme's
 * `.flexy-arrow-prev` / `.flexy-arrow-next` markup is never rendered and the
 * prev/next arrows disappear. WooCommerce ships FlexSlider with
 * `directionNav => false`, so nothing takes their place.
 *
 * Turn FlexSlider's own direction nav on and feed it Blocksy's arrow icons.
 *
 * @since 2.11
 */
function vwg_blocksy_enable_flexslider_arrows( $options ) {
    if ( ! vwg_blocksy_uses_forced_default_gallery() ) {
        return $options;
    }

    $icons = vwg_blocksy_arrow_icons();

    if ( empty( $icons ) ) {
        // Could not read the theme's arrows; better no arrows than blank ones.
        return $options;
    }

    $options['directionNav'] = true;
    $options['prevText']     = $icons['prev'];
    $options['nextText']     = $icons['next'];

    return $options;
}
add_filter( 'woocommerce_single_product_carousel_options', 'vwg_blocksy_enable_flexslider_arrows', 20, 1 );

/**
 * Blocksy: style the FlexSlider direction nav like Blocksy's own flexy arrows.
 *
 * @since 2.11
 */
function vwg_blocksy_flexslider_arrows_styles() {
    if ( ! is_product() || ! vwg_blocksy_uses_forced_default_gallery() ) {
        return;
    }

    wp_enqueue_style(
        'vwg-blocksy-gallery',
        VWG_VIDEO_WOO_GALLERY_URL . 'woocommerce-overwrite/assets/css/blocksy-gallery.css',
        '',
        VWG_VERSION_NUM
    );
}
add_action( 'wp_enqueue_scripts', 'vwg_blocksy_flexslider_arrows_styles', 30 );

/**
 * Blocksy: may we make the gallery slides inert?
 *
 * Only on the gallery we forced onto the page, and only while the PhotoSwipe
 * lightbox is off — with the lightbox on, the slide href is what PhotoSwipe
 * reads, so it has to stay.
 *
 * @since 2.11
 */
function vwg_blocksy_can_unlink_gallery_slides() {
    return vwg_blocksy_uses_forced_default_gallery()
        && ! current_theme_supports( 'wc-product-gallery-lightbox' );
}

/**
 * Blocksy: stop gallery images from navigating to the raw image file.
 *
 * @since 2.11
 */
function vwg_blocksy_unlink_gallery_images( $html ) {
    if ( ! vwg_blocksy_can_unlink_gallery_slides() ) {
        return $html;
    }

    return preg_replace( '/<a\b[^>]*>(.*?)<\/a>/s', '$1', $html );
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'vwg_blocksy_unlink_gallery_images', 20, 1 );

/**
 * Blocksy: same for video slides, minus the unwrapping.
 *
 * @since 2.11
 */
function vwg_blocksy_unlink_video_slides( $html ) {
    if ( ! vwg_blocksy_can_unlink_gallery_slides() ) {
        return $html;
    }

    return preg_replace( '/(<a\b)\s+href=(["\'])[^"\']*\2/i', '$1', $html );
}
add_filter( 'vwg_product_gallery_html', 'vwg_blocksy_unlink_video_slides', 20, 1 );


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


