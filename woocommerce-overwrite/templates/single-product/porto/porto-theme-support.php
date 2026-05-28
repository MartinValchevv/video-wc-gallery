<?php
/**
 * Porto theme support for Video WC Gallery.
 *
 * Porto ships custom Owl-Carousel based single-product/product-image.php and
 * product-thumbnails.php templates, so we ship overrides at:
 *   woocommerce-overwrite/templates/single-product/porto/product-image-porto-theme.php
 *   woocommerce-overwrite/templates/single-product/porto/product-thumbnails-porto-theme.php
 *
 * Both overrides fire dedicated actions inside the carousels:
 *   - vwg_woocommerce_product_thumbnails_first_show_porto_theme  (main slider)
 *   - vwg_woocommerce_product_thumbnails_after_porto_theme       (thumbs slider)
 *
 * The main-slider action is hooked to whichever renderer is active
 * (vwg_add_video_to_product_gallery in the free plugin or
 * vwg_pro_render_youtube_videos in the Pro plugin); the template wraps each
 * <div data-vwg-video=…> block in Porto's <div class="img-thumbnail"><div class="inner">…</div></div>
 * structure so Owl treats them as proper slides.
 *
 * The thumbs-slider action is handled here, since Porto's thumbs are
 * standalone <div class="img-thumbnail"><img></div> markup with no equivalent
 * in the standard VWG flow.
 *
 * @since 2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'vwg_porto_is_active' ) ) {

    function vwg_porto_is_active() {
        return function_exists( 'vwg_active_theme_checker' ) && vwg_active_theme_checker() === 'Porto';
    }

    /**
     * Collect VWG video data for the current product (respecting the configured limit).
     *
     * @return array
     */
    function vwg_porto_get_videos() {
        if ( ! function_exists( 'is_product' ) || ! is_product() ) {
            return array();
        }
        global $product;
        if ( ! is_object( $product ) ) {
            $product = wc_get_product( get_the_ID() );
        }
        if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
            return array();
        }
        $raw = get_post_meta( $product->get_id(), 'vwg_video_url', true );
        if ( empty( $raw ) ) {
            return array();
        }
        $videos = maybe_unserialize( $raw );
        if ( ! is_array( $videos ) ) {
            return array();
        }
        if ( function_exists( 'vwg_get_video_limit' ) ) {
            $limit = (int) vwg_get_video_limit();
            if ( $limit > 0 ) {
                $videos = array_slice( $videos, 0, $limit );
            }
        }
        return $videos;
    }

    /**
     * Render Porto-shaped thumbnail entries for each VWG video, with a
     * FontAwesome play overlay that respects the plugin's icon & colour settings.
     *
     * Fired from product-thumbnails-porto-theme.php inside the
     * .product-thumbs-slider Owl Carousel container.
     */
    function vwg_porto_render_thumbnails() {
        $videos = vwg_porto_get_videos();
        if ( empty( $videos ) ) {
            return;
        }
        $opt        = get_option( 'vwg_settings_group' );
        $icon_class = ! empty( $opt['vwg_settings_icon'] ) ? $opt['vwg_settings_icon'] : 'fas fa-play';
        $i = 0;
        foreach ( $videos as $video ) {
            $i++;
            $thumb = '';
            if ( ! empty( $video['woocommerce_gallery_thumbnail_url'] ) ) {
                $thumb = $video['woocommerce_gallery_thumbnail_url'];
            } elseif ( ! empty( $video['woocommerce_thumbnail_url'] ) ) {
                $thumb = $video['woocommerce_thumbnail_url'];
            } elseif ( isset( $video['video_thumb_url'] ) ) {
                $thumb = $video['video_thumb_url'];
            }
            if ( empty( $thumb ) ) {
                continue;
            }
            printf(
                '<div class="img-thumbnail vwg-porto-video-thumb" data-vwg-video-thumb="%1$d"><img class="img-responsive" alt="%2$s" src="%3$s" width="300" height="300" loading="lazy" /><span class="vwg-porto-video-thumb-icon" aria-hidden="true"><i class="%4$s"></i></span></div>',
                (int) $i,
                esc_attr__( 'Product video', 'video-wc-gallery' ),
                esc_url( $thumb ),
                esc_attr( $icon_class )
            );
        }
    }
    add_action( 'vwg_woocommerce_product_thumbnails_after_porto_theme', 'vwg_porto_render_thumbnails', 10 );

    /**
     * CSS shim for the play-icon overlay on video thumbnails and main slide sizing.
     */
    function vwg_porto_print_style() {
        if ( ! vwg_porto_is_active() || ! is_product() ) {
            return;
        }
        $opt        = get_option( 'vwg_settings_group' );
        $icon_color = ! empty( $opt['vwg_settings_icon_color'] ) ? $opt['vwg_settings_icon_color'] : '#ffffff';
        ?>
<style id="vwg-porto-style">
/* Thumbnails — Porto wraps thumbs in three different containers depending on
   the product layout:
     .product-thumbs-slider          (default, left_sidebar) — Owl carousel
     .product-thumbnails-inner       (full_width, centered_vertical_zoom) — plain div
     .product-thumbs-vertical-slider (transparent) — slick.js vertical slider
   The overlay needs to render in all three. */
.vwg-porto-video-thumb{position:relative;cursor:pointer}
.vwg-porto-video-thumb .vwg-porto-video-thumb-icon{position:absolute;top:50%;left:50%;width:34px;height:34px;transform:translate(-50%,-50%);background:rgba(0,0,0,.55);border-radius:50%;pointer-events:none;display:flex;align-items:center;justify-content:center;z-index:2}
.vwg-porto-video-thumb .vwg-porto-video-thumb-icon i{color:<?php echo esc_attr( $icon_color ); ?>;font-size:14px;line-height:1}

/* Main video slide: keep video.js's own fluid layout intact, just make sure
   Porto's zoom overlays don't sit on top of the player. */
.product-image-slider .vwg-porto-video-slide .inner{width:100%}
.product-image-slider .vwg-porto-video-slide .woocommerce-product-gallery__image{width:100%}
.product-image-slider .vwg-porto-video-slide .zoomContainer,
.product-image-slider .vwg-porto-video-slide .zoomWindow,
.product-image-slider .vwg-porto-video-slide .zoomWindowContainer,
.product-image-slider .vwg-porto-video-slide .easyzoom-flyout,
.product-image-slider .vwg-porto-video-slide .easyzoom-notice,
.product-image-slider .vwg-porto-video-slide .zoomImg{display:none !important}
</style>
        <?php
    }
    add_action( 'wp_head', 'vwg_porto_print_style', 99 );

    /**
     * JS: bind video-thumbnail clicks to Porto's main image carousel.
     *
     * Porto already has a click handler on .owl-item that calls selectThumb,
     * but binding our own (with stopPropagation) gives us a reliable path that
     * is independent of Porto's internal init order and that we control for
     * future tweaks (e.g. autoplay on selection).
     */
    function vwg_porto_print_script() {
        if ( ! vwg_porto_is_active() || ! is_product() ) {
            return;
        }
        ?>
<script id="vwg-porto-script">
(function($){
    if (!$) return;

    function playActiveVideo($image_slider){
        if (typeof window.videojs === 'undefined') return;
        var $vid = $image_slider.find('.owl-item.active .vwg-porto-video-slide .vwg_video_js').first();
        if (!$vid.length) return;
        var id = $vid.attr('id');
        try {
            var player = window.videojs.getPlayer(id);
            if (player && typeof player.play === 'function') {
                var p = player.play();
                if (p && typeof p.catch === 'function') p.catch(function(){});
            }
        } catch (e) {}
    }

    function pauseAllVideos($image_slider){
        if (typeof window.videojs === 'undefined') return;
        $image_slider.find('.vwg_video_js').each(function(){
            try {
                var player = window.videojs.getPlayer( $(this).attr('id') );
                if (player && typeof player.pause === 'function' && !player.paused()) player.pause();
            } catch(e){}
        });
    }

    $(function(){
        // Stop the wrapping <a href="…youtube…"> from hijacking clicks on the
        // video.js play button on a video slide.
        $(document).on('click.vwgPortoLink',
            '.product-image-slider .vwg-porto-video-slide a.woocommerce-product-gallery__vwg_video',
            function(e){ e.preventDefault(); }
        );

        // Watch every Porto image slider on the page: when Owl moves to a
        // video slide, autoplay it; when it moves away, pause everything.
        $('.product-image-slider').each(function(){
            var $image_slider = $(this);
            if ($image_slider.data('vwgPortoSliderBound')) return;
            $image_slider.data('vwgPortoSliderBound', true);

            $image_slider.on('translated.owl.carousel', function(){
                pauseAllVideos($image_slider);
                setTimeout(function(){ playActiveVideo($image_slider); }, 50);
            });
        });
    });
})(window.jQuery);
</script>
        <?php
    }
    add_action( 'wp_footer', 'vwg_porto_print_script', 99 );
}
