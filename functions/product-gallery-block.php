<?php
/**
 * Product Gallery Block Support
 *
 * Adds video support for the WooCommerce Product Gallery Block
 * (the block-based gallery using WordPress Interactivity API).
 *
 * @since 2.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get video data with synthetic negative IDs for block gallery integration.
 *
 * @since 2.5
 * @param int $product_id The product ID.
 * @return array Array of video data with synthetic IDs.
 */
function vwg_block_get_video_data( $product_id ) {
    $video_url  = get_post_meta( $product_id, 'vwg_video_url', true );
    $video_urls = maybe_unserialize( $video_url );

    if ( empty( $video_url ) || ! is_array( $video_urls ) ) {
        return array();
    }

    $break_rule = vwg_get_video_limit();
    $videos     = array();
    $count      = 0;

    foreach ( $video_urls as $video ) {
        $count++;
        if ( $count > $break_rule ) {
            break;
        }

        // Synthetic negative ID: -(product_id * 100 + video_index)
        // This avoids collision with real WordPress attachment IDs (always positive).
        $synthetic_id = -( absint( $product_id ) * 100 + $count );

        $videos[] = array(
            'id'              => $synthetic_id,
            'video_url'       => $video['video_url'] ?? '',
            'thumb_url'       => $video['video_thumb_url'] ?? '',
            'wc_thumb_url'    => $video['woocommerce_thumbnail_url'] ?? ( $video['video_thumb_url'] ?? '' ),
            'wc_gallery_thumb' => $video['woocommerce_gallery_thumbnail_url'] ?? ( $video['video_thumb_url'] ?? '' ),
            'index'           => $count,
        );
    }

    return $videos;
}

/**
 * Filter: Modify the root Product Gallery block output.
 *
 * - Adds video synthetic IDs to the imageData context
 * - Adds video metadata (vwgVideos) for frontend JS
 * - Updates navigation state
 * - Injects video poster images into the dialog
 * - Removes single-image class when videos are added
 *
 * @since 2.5
 * @param string   $block_content The block rendered HTML.
 * @param array    $parsed_block  The parsed block data.
 * @param WP_Block $instance      The block instance.
 * @return string Modified block content.
 */
function vwg_block_gallery_render( $block_content, $parsed_block, $instance ) {
    $post_id = $instance->context['postId'] ?? 0;
    if ( ! $post_id ) {
        return $block_content;
    }

    $product = wc_get_product( $post_id );
    if ( ! $product instanceof WC_Product ) {
        return $block_content;
    }

    $videos = vwg_block_get_video_data( $product->get_id() );
    if ( empty( $videos ) ) {
        return $block_content;
    }

    $settings   = get_option( 'vwg_settings_group' );
    $show_first = ! empty( $settings['vwg_settings_show_first'] );

    // --- 1. Modify data-wp-context ---
    $p = new WP_HTML_Tag_Processor( $block_content );
    if ( $p->next_tag() ) {
        $context_json = $p->get_attribute( 'data-wp-context' );
        if ( $context_json ) {
            $context = json_decode( $context_json, true );
            if ( is_array( $context ) ) {
                $video_ids = array_map( function ( $v ) {
                    return $v['id'];
                }, $videos );

                // Add video IDs to imageData.
                if ( $show_first ) {
                    $context['imageData']      = array_merge( $video_ids, $context['imageData'] ?? array() );
                    $context['selectedImageId'] = $video_ids[0];
                    $context['isDisabledPrevious'] = true;
                    $context['isDisabledNext']     = count( $context['imageData'] ) <= 1;
                } else {
                    $context['imageData'] = array_merge( $context['imageData'] ?? array(), $video_ids );
                }

                $context['hideNextPreviousButtons'] = count( $context['imageData'] ) <= 1;

                // Add video metadata for JS.
                $vwg_videos = array();
                foreach ( $videos as $v ) {
                    $vwg_videos[ (string) $v['id'] ] = array(
                        'url'   => $v['video_url'],
                        'thumb' => $v['thumb_url'],
                    );
                }
                $context['vwgVideos'] = $vwg_videos;

                $p->set_attribute(
                    'data-wp-context',
                    wp_json_encode( $context, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP )
                );
            }
        }

        // Remove single-image class if we're adding videos.
        $p->remove_class( 'is-single-product-gallery-image' );

        $block_content = $p->get_updated_html();
    }

    // --- 2. Inject video poster images into dialog ---
    $dialog_content_marker = 'wc-block-product-gallery-dialog__content';
    $dialog_pos = strpos( $block_content, $dialog_content_marker );
    if ( false !== $dialog_pos ) {
        // Find the closing > of the dialog content div.
        $content_tag_end = strpos( $block_content, '>', $dialog_pos );
        if ( false !== $content_tag_end ) {
            $video_dialog_html = '';
            foreach ( $videos as $v ) {
                $video_dialog_html .= sprintf(
                    '<img data-image-id="%d" src="%s" loading="lazy" decoding="async" alt="%s" data-vwg-video-src="%s" class="vwg-block-dialog-video-poster" />',
                    $v['id'],
                    esc_url( $v['thumb_url'] ),
                    esc_attr( sprintf( __( 'Video %d', 'video-wc-gallery' ), $v['index'] ) ),
                    esc_url( $v['video_url'] )
                );
            }

            if ( $show_first ) {
                // Insert after the opening tag of dialog content.
                $block_content = substr_replace( $block_content, $video_dialog_html, $content_tag_end + 1, 0 );
            } else {
                // Insert before closing </div> of dialog content.
                $dialog_content_end = strpos( $block_content, '</div>', $content_tag_end );
                if ( false !== $dialog_content_end ) {
                    $block_content = substr_replace( $block_content, $video_dialog_html, $dialog_content_end, 0 );
                }
            }
        }
    }

    return $block_content;
}
add_filter( 'render_block_woocommerce/product-gallery', 'vwg_block_gallery_render', 10, 3 );

/**
 * Filter: Inject video slides into the large image container.
 *
 * Adds <li> elements with video poster <img> tags into the <ul> gallery container.
 * The poster images have data-vwg-video-src attributes for JS to detect and overlay
 * Video.js players when the slide becomes active.
 *
 * @since 2.5
 * @param string   $block_content The block rendered HTML.
 * @param array    $parsed_block  The parsed block data.
 * @param WP_Block $instance      The block instance.
 * @return string Modified block content.
 */
function vwg_block_large_image_render( $block_content, $parsed_block, $instance ) {
    $post_id = $instance->context['postId'] ?? 0;
    if ( ! $post_id ) {
        return $block_content;
    }

    $product = wc_get_product( $post_id );
    if ( ! $product instanceof WC_Product ) {
        return $block_content;
    }

    $videos = vwg_block_get_video_data( $product->get_id() );
    if ( empty( $videos ) ) {
        return $block_content;
    }

    $settings   = get_option( 'vwg_settings_group' );
    $show_first = ! empty( $settings['vwg_settings_show_first'] );

    // Get product main image dimensions for sizing.
    $product_main_image = wp_get_attachment_image_src( $product->get_image_id(), 'woocommerce_single' );
    $width  = is_array( $product_main_image ) ? ( $product_main_image[1] ?? 600 ) : 600;
    $height = is_array( $product_main_image ) ? ( $product_main_image[2] ?? 600 ) : 600;

    // Build video <li> elements.
    $video_html = '';
    foreach ( $videos as $index => $v ) {
        $loading = ( $show_first && 0 === $index ) ? 'fetchpriority="high"' : 'loading="lazy" fetchpriority="low"';

        $video_html .= sprintf(
            '<li class="wc-block-product-gallery-large-image__wrapper vwg-block-video-slide">'
            . '<div class="wc-block-components-product-image">'
            . '<img class="wc-block-woocommerce-product-gallery-large-image__image vwg-block-poster-image"'
            . ' data-image-id="%d"'
            . ' src="%s"'
            . ' width="%d"'
            . ' height="%d"'
            . ' alt="%s"'
            . ' data-vwg-video-src="%s"'
            . ' data-vwg-video-index="%d"'
            . ' tabindex="-1"'
            . ' draggable="false"'
            . ' decoding="async"'
            . ' %s'
            . ' data-wp-on--touchstart="actions.onTouchStart"'
            . ' data-wp-on--touchmove="actions.onTouchMove"'
            . ' data-wp-on--touchend="actions.onTouchEnd"'
            . ' />'
            . '</div>'
            . '</li>',
            $v['id'],
            esc_url( $v['thumb_url'] ),
            (int) $width,
            (int) $height,
            esc_attr( sprintf( __( 'Product Video %d', 'video-wc-gallery' ), $v['index'] ) ),
            esc_url( $v['video_url'] ),
            $v['index'],
            $loading
        );
    }

    if ( empty( $video_html ) ) {
        return $block_content;
    }

    // Inject into the <ul> container.
    if ( $show_first ) {
        // Insert after the opening <ul ...> tag.
        $ul_pos = strpos( $block_content, '<ul' );
        if ( false !== $ul_pos ) {
            $ul_close = strpos( $block_content, '>', $ul_pos );
            if ( false !== $ul_close ) {
                $block_content = substr_replace( $block_content, $video_html, $ul_close + 1, 0 );
            }
        }
    } else {
        // Insert before the closing </ul>.
        $ul_end = strrpos( $block_content, '</ul>' );
        if ( false !== $ul_end ) {
            $block_content = substr_replace( $block_content, $video_html, $ul_end, 0 );
        }
    }

    return $block_content;
}
add_filter( 'render_block_woocommerce/product-gallery-large-image', 'vwg_block_large_image_render', 10, 3 );

/**
 * Filter: Inject video thumbnails into the thumbnails block.
 *
 * Adds thumbnail elements with play icon overlay.
 * Handles the edge case where the thumbnails block returns empty
 * (product has only 1 image) by building the full container.
 *
 * @since 2.5
 * @param string   $block_content The block rendered HTML.
 * @param array    $parsed_block  The parsed block data.
 * @param WP_Block $instance      The block instance.
 * @return string Modified block content.
 */
function vwg_block_thumbnails_render( $block_content, $parsed_block, $instance ) {
    $post_id = $instance->context['postId'] ?? 0;
    if ( ! $post_id ) {
        return $block_content;
    }

    $product = wc_get_product( $post_id );
    if ( ! $product instanceof WC_Product ) {
        return $block_content;
    }

    $videos = vwg_block_get_video_data( $product->get_id() );
    if ( empty( $videos ) ) {
        return $block_content;
    }

    $settings   = get_option( 'vwg_settings_group' );
    $show_first = ! empty( $settings['vwg_settings_show_first'] );

    // Get block attributes.
    $attrs              = $parsed_block['attrs'] ?? array();
    $aspect_ratio       = $attrs['aspectRatio'] ?? '1';
    $thumbnail_size_pct = str_replace( '%', '', $attrs['thumbnailSize'] ?? '25%' );
    $active_style       = $attrs['activeThumbnailStyle'] ?? 'overlay';

    // Build video thumbnail HTML.
    $video_thumbs_html = '';
    foreach ( $videos as $index => $v ) {
        $is_first_overall = $show_first && 0 === $index;
        $active_class     = $is_first_overall ? ' wc-block-product-gallery-thumbnails__thumbnail__image--is-active' : '';
        $tab_index        = $is_first_overall ? '0' : '-1';

        $video_thumbs_html .= sprintf(
            '<div class="wc-block-product-gallery-thumbnails__thumbnail vwg-block-thumbnail-video">'
            . '<img class="wc-block-product-gallery-thumbnails__thumbnail__image%s"'
            . ' data-image-id="%d"'
            . ' src="%s"'
            . ' alt="%s"'
            . ' data-wp-on--click="actions.selectCurrentImage"'
            . ' data-wp-on--keydown="actions.onThumbnailsArrowsKeyDown"'
            . ' data-wp-watch="callbacks.toggleActiveThumbnailAttributes"'
            . ' decoding="async"'
            . ' tabindex="%s"'
            . ' draggable="false"'
            . ' loading="lazy"'
            . ' role="option"'
            . ' style="aspect-ratio: %s"'
            . ' />'
            . '<span class="vwg-block-play-icon" aria-hidden="true"></span>'
            . '</div>',
            esc_attr( $active_class ),
            $v['id'],
            esc_url( $v['wc_gallery_thumb'] ),
            esc_attr( sprintf( __( 'Video %d', 'video-wc-gallery' ), $v['index'] ) ),
            esc_attr( $tab_index ),
            esc_attr( $aspect_ratio )
        );
    }

    // Edge case: Thumbnails block returned empty (product has <=1 image).
    // We need to build the entire thumbnails container.
    if ( empty( trim( $block_content ) ) ) {
        $block_content = vwg_block_build_full_thumbnails(
            $product,
            $videos,
            $video_thumbs_html,
            $aspect_ratio,
            $thumbnail_size_pct,
            $active_style,
            $show_first
        );
        return $block_content;
    }

    // Normal case: Inject video thumbnails into existing container.
    $scrollable_marker = 'wc-block-product-gallery-thumbnails__scrollable';
    $scrollable_pos    = strpos( $block_content, $scrollable_marker );

    if ( false !== $scrollable_pos ) {
        if ( $show_first ) {
            // Insert after the opening tag of the scrollable container.
            $scrollable_tag_end = strpos( $block_content, '>', $scrollable_pos );
            if ( false !== $scrollable_tag_end ) {
                // If show_first, remove --is-active from the first existing image thumbnail.
                $block_content = str_replace(
                    'wc-block-product-gallery-thumbnails__thumbnail__image wc-block-product-gallery-thumbnails__thumbnail__image--is-active',
                    'wc-block-product-gallery-thumbnails__thumbnail__image',
                    $block_content
                );
                // Also reset tabindex from 0 to -1 for the first existing thumbnail.
                // We do this by finding the first thumbnail img after scrollable and changing tabindex.
                $block_content = substr_replace( $block_content, $video_thumbs_html, $scrollable_tag_end + 1, 0 );
            }
        } else {
            // Insert before the closing </div> of the scrollable container.
            // Structure: <div.thumbnails><div.scrollable>...thumbnails...</div></div>
            // We need to insert before the scrollable's closing </div> (second-to-last).
            $last_div = strrpos( $block_content, '</div>' );
            if ( false !== $last_div ) {
                $second_last_div = strrpos( substr( $block_content, 0, $last_div ), '</div>' );
                if ( false !== $second_last_div ) {
                    $block_content = substr_replace( $block_content, $video_thumbs_html, $second_last_div, 0 );
                }
            }
        }
    }

    return $block_content;
}
add_filter( 'render_block_woocommerce/product-gallery-thumbnails', 'vwg_block_thumbnails_render', 10, 3 );

/**
 * Build the full thumbnails container when WooCommerce's thumbnails block returns empty.
 *
 * This happens when the product has only 1 image (no gallery images).
 * With videos added, we need to show thumbnails, so we build the complete HTML.
 *
 * @since 2.5
 * @param WC_Product $product            The product object.
 * @param array      $videos             Video data array.
 * @param string     $video_thumbs_html  Pre-built video thumbnail HTML.
 * @param string     $aspect_ratio       Thumbnail aspect ratio.
 * @param string     $thumbnail_size_pct Thumbnail size percentage.
 * @param string     $active_style       Active thumbnail style (overlay|outline).
 * @param bool       $show_first         Whether to show videos first.
 * @return string Complete thumbnails HTML.
 */
function vwg_block_build_full_thumbnails( $product, $videos, $video_thumbs_html, $aspect_ratio, $thumbnail_size_pct, $active_style, $show_first ) {
    // Get product images.
    $featured_id  = $product->get_image_id();
    $gallery_ids  = $product->get_gallery_image_ids();
    $all_image_ids = array();

    if ( $featured_id ) {
        $all_image_ids[] = $featured_id;
    }
    if ( ! empty( $gallery_ids ) ) {
        $all_image_ids = array_unique( array_merge( $all_image_ids, $gallery_ids ) );
    }

    // Determine the image size.
    $image_size = '1' === $aspect_ratio ? 'woocommerce_thumbnail' : 'woocommerce_single';

    // Build image thumbnails.
    $image_thumbs_html = '';
    foreach ( $all_image_ids as $index => $image_id ) {
        $img_src = wp_get_attachment_image_src( $image_id, $image_size );
        $srcset  = wp_get_attachment_image_srcset( $image_id, $image_size );
        $sizes   = wp_get_attachment_image_sizes( $image_id, $image_size );
        $alt     = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

        if ( ! $alt ) {
            $alt = $product->get_title();
        }

        $is_first      = ( ! $show_first && 0 === $index );
        $active_class   = $is_first ? ' wc-block-product-gallery-thumbnails__thumbnail__image--is-active' : '';
        $tab_index_attr = $is_first ? '0' : '-1';

        $image_thumbs_html .= sprintf(
            '<div class="wc-block-product-gallery-thumbnails__thumbnail">'
            . '<img class="wc-block-product-gallery-thumbnails__thumbnail__image%s"'
            . ' data-image-id="%d"'
            . ' src="%s"'
            . ' srcset="%s"'
            . ' sizes="%s"'
            . ' alt="%s"'
            . ' data-wp-on--click="actions.selectCurrentImage"'
            . ' data-wp-on--keydown="actions.onThumbnailsArrowsKeyDown"'
            . ' data-wp-watch="callbacks.toggleActiveThumbnailAttributes"'
            . ' decoding="async"'
            . ' tabindex="%s"'
            . ' draggable="false"'
            . ' loading="lazy"'
            . ' role="option"'
            . ' style="aspect-ratio: %s"'
            . ' />'
            . '</div>',
            esc_attr( $active_class ),
            (int) $image_id,
            esc_url( $img_src ? $img_src[0] : '' ),
            esc_attr( $srcset ? $srcset : '' ),
            esc_attr( $sizes ? $sizes : '' ),
            esc_attr( $alt ),
            esc_attr( $tab_index_attr ),
            esc_attr( $aspect_ratio )
        );
    }

    // Combine based on show_first.
    $all_thumbs = $show_first ? $video_thumbs_html . $image_thumbs_html : $image_thumbs_html . $video_thumbs_html;

    // Only render if total items > 1.
    $total_items = count( $all_image_ids ) + count( $videos );
    if ( $total_items <= 1 ) {
        return '';
    }

    return sprintf(
        '<div class="wc-block-product-gallery-thumbnails wc-block-product-gallery-thumbnails--active-%s"'
        . ' style="--wc-block-product-gallery-thumbnails-size:%d;"'
        . ' data-wp-interactive="woocommerce/product-gallery"'
        . ' data-wp-class--wc-block-product-gallery-thumbnails--overflow-top="context.thumbnailsOverflow.top"'
        . ' data-wp-class--wc-block-product-gallery-thumbnails--overflow-bottom="context.thumbnailsOverflow.bottom"'
        . ' data-wp-class--wc-block-product-gallery-thumbnails--overflow-left="context.thumbnailsOverflow.left"'
        . ' data-wp-class--wc-block-product-gallery-thumbnails--overflow-right="context.thumbnailsOverflow.right">'
        . '<div class="wc-block-product-gallery-thumbnails__scrollable"'
        . ' data-wp-init--init-resize-observer="callbacks.initResizeObserver"'
        . ' data-wp-init--hide-ghost-overflow="callbacks.hideGhostOverflow"'
        . ' data-wp-on--scroll="actions.onScroll"'
        . ' role="listbox">'
        . '%s'
        . '</div>'
        . '</div>',
        esc_attr( $active_style ),
        absint( $thumbnail_size_pct ),
        $all_thumbs
    );
}

/**
 * Enqueue block gallery specific CSS and JS.
 *
 * @since 2.5
 */
function vwg_block_enqueue_assets() {
    if ( ! is_product() ) {
        return;
    }

    $product = wc_get_product( get_the_ID() );
    if ( ! $product ) {
        return;
    }

    $videos = vwg_block_get_video_data( $product->get_id() );
    if ( empty( $videos ) ) {
        return;
    }

    // CSS.
    wp_enqueue_style(
        'vwg-gallery-block',
        VWG_VIDEO_WOO_GALLERY_URL . 'includes/css/vwg-gallery-block.css',
        array(),
        VWG_VERSION_NUM
    );

    // JS — depends on videojs which is enqueued by vwg_enqueue_scripts().
    wp_enqueue_script(
        'vwg-gallery-block',
        VWG_VIDEO_WOO_GALLERY_URL . 'includes/js/vwg-gallery-block.js',
        array( 'videojs' ),
        VWG_VERSION_NUM,
        true
    );

    // Pass settings to JS.
    $settings = get_option( 'vwg_settings_group' );

    // Determine icon unicode and weight.
    $icon = $settings['vwg_settings_icon'] ?? 'fas fa-play';
    $icon_map = array(
        'far fa-play-circle' => array( 'unicode' => '\f144', 'weight' => '400' ),
        'fas fa-play-circle' => array( 'unicode' => '\f144', 'weight' => '900' ),
        'fas fa-play'        => array( 'unicode' => '\f04b', 'weight' => '900' ),
        'fas fa-video'       => array( 'unicode' => '\f03d', 'weight' => '900' ),
        'fas fa-file-video'  => array( 'unicode' => '\f1c8', 'weight' => '900' ),
        'far fa-file-video'  => array( 'unicode' => '\f1c8', 'weight' => '400' ),
    );
    $icon_data = $icon_map[ $icon ] ?? array( 'unicode' => '\f04b', 'weight' => '900' );

    wp_localize_script( 'vwg-gallery-block', 'vwgBlockData', array(
        'controls'    => ! empty( $settings['vwg_settings_video_controls'] ),
        'loop'        => ! empty( $settings['vwg_settings_loop'] ),
        'muted'       => ! empty( $settings['vwg_settings_muted'] ),
        'autoplay'    => ! empty( $settings['vwg_settings_autoplay'] ),
        'adaptSizes'  => ! empty( $settings['vwg_settings_video_adapt_sizes'] ),
        'iconColor'   => $settings['vwg_settings_icon_color'] ?? '#ffffff',
        'iconUnicode' => $icon_data['unicode'],
        'iconWeight'  => $icon_data['weight'],
    ) );

    // Dynamic CSS custom properties for icon styling.
    $icon_color = esc_attr( $settings['vwg_settings_icon_color'] ?? '#ffffff' );
    $inline_css = sprintf(
        ':root { --vwg-icon-color: %s; }' .
        '.vwg-block-play-icon::before { content: "%s"; font-weight: %s; }' .
        '.vwg-block-video-container .vjs-big-play-button { border-color: %s !important; }' .
        '.vwg-block-video-container .vjs-big-play-button .vjs-icon-placeholder::before { content: "%s"; font-family: "Font Awesome 6 Free"; font-weight: %s; font-size: 30px; color: %s; }',
        $icon_color,
        $icon_data['unicode'],
        $icon_data['weight'],
        $icon_color,
        $icon_data['unicode'],
        $icon_data['weight'],
        $icon_color
    );
    wp_add_inline_style( 'vwg-gallery-block', $inline_css );
}
add_action( 'wp_enqueue_scripts', 'vwg_block_enqueue_assets' );
