<?php
/**
 * Operations of the plugin are included here.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$option = get_option('vwg_settings_group');

/**
 * Enqueue CSS and JS
 *
 * @since 1.38
 */
function vwg_enqueue_scripts( $hook ) {

    if ( is_product() ) {
        // CSS
        wp_enqueue_style('vwg_fontawesome', VWG_VIDEO_WOO_GALLERY_URL . 'includes/fontawesome_v6-6-0/css/all.css', '', VWG_VERSION_NUM);
        // Enqueue Video.js CSS
        wp_enqueue_style('videojs-css', VWG_VIDEO_WOO_GALLERY_URL . 'includes/video-js/video-js.css', '', VWG_VERSION_NUM);
        
        // Enqueue Flatsome theme specific styles
        if (vwg_active_theme_checker() === 'Flatsome') {
            wp_enqueue_style('vwg-flatsome-gallery', VWG_VIDEO_WOO_GALLERY_URL . 'woocommerce-overwrite/assets/css/flatsome-gallery.css', '', VWG_VERSION_NUM);
        }

        // JS
        // Enqueue Video.js JavaScript
        wp_enqueue_script('videojs', VWG_VIDEO_WOO_GALLERY_URL . 'includes/video-js/video-js.min.js', array('jquery'), VWG_VERSION_NUM, true);

    }

}
add_action( 'wp_enqueue_scripts', 'vwg_enqueue_scripts' );

/**
 * Create video tab in Woocommerce product
 *
 * @since 1.0
 */
function vwg_add_custom_product_tab( $product_data_tabs ) {
    $product_data_tabs['vwg_video_tab'] = array(
        'label' => __( 'Video Gallery for WooCommerce', 'video-wc-gallery' ),
        'target' => 'vwg_video_tab_content',
        'class' => array( 'show_if_simple', 'show_if_variable' ),
    );
    return $product_data_tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'vwg_add_custom_product_tab', 10, 1 );

/**
 * Add the tab content
 *
 * @since 1.3
 */
function vwg_add_custom_product_tab_content() {
    global $post;
    ?>
    <div id='vwg_video_tab_content' class='panel woocommerce_options_panel'>
        <?php
        $video_url = get_post_meta( $post->ID, 'vwg_video_url', true );
        ?>
        <p class="form-field">
            <label for="add_video_button"><?php echo esc_html__('Add video from' , 'video-wc-gallery') ?></label>
            <button id="add_video_button" type="button" class="button"><?php echo esc_html__('WP Media' , 'video-wc-gallery') ?></button>
        </p>
        <ul id="sortable" class="video_gallery_wrapper">
            <?php if (!empty($video_url)) :
                $video_url = maybe_unserialize( $video_url );
                $position_counter = 0;
                ?>
                <?php foreach ($video_url as $key => $video) :
                $position_counter++;
                ?>
                <li class="ui-state video_id_<?php echo esc_attr($key) ?>" data-position="<?php echo esc_attr($position_counter) ?>" >
                    <div class="video-player" style="width:220px;height:200px; background:#222;">
                        <video width="230" height="200" controls preload="auto">
                            <source src="<?php echo esc_url($video['video_url']); ?>" type="video/webm">
                        </video>
                        <div>
                            <input type="hidden" class="video_url" name="video_url[<?php echo esc_attr($key) ?>][video_url]" value="<?php echo esc_url($video['video_url']); ?>"/>
                            <input type="hidden" class="video_thumb_url" name="video_url[<?php echo esc_attr($key) ?>][video_thumb_url]" value="<?php echo esc_url($video['video_thumb_url']); ?>"/>
                            <button data-video-id="<?php echo esc_attr($key) ?>" type="button" class="button delete_video_btn" title="Delete">X</button>
                </li>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <div class="bar-btns" <?php echo (!empty($video_url)) ? '' : 'style="display:none"'?>>
            <p>
                <button id="delete_all_video_button" type="button" class="button"><?php echo esc_html__('Delete all' , 'video-wc-gallery') ?></button>
            </p>
        </div>
    </div>
    <?php
}
add_action( 'woocommerce_product_data_panels', 'vwg_add_custom_product_tab_content' );


/**
 * Save the tab content data
 *
 * @since 1.20
 */
function vwg_save_custom_product_tab_content( $post_id ) {
    if ( isset( $_POST['video_url'] ) )  {
        $sanitized_urls = array();
        foreach ( $_POST['video_url'] as $key => $attachment ) {

            $unique_id = uniqid(); // Generate a unique identifier

            $sanitized_attachment = array(
                'video_url' => wp_kses_post( $attachment['video_url'] ),
                'video_thumb_url' => wp_kses_post( $attachment['video_thumb_url'] ),
            );

            if ( isset( $attachment['video_thumb_url'] ) ) {

                // The string contains 'data:image/png;base64,'
                if (strpos($attachment['video_thumb_url'], "data:image/png;base64,") !== false) {

                    // Decode the base64-encoded image
                    $base64_image = $attachment['video_thumb_url'];
                    // Remove the data URI scheme and get the base64-encoded image data
                    $base64_data = str_replace( 'data:image/png;base64,', '', $base64_image );
                    $decoded_image = base64_decode( $base64_data );

                    // Create a directory (if not exists) to store the uploaded images
                    $upload_dir = wp_upload_dir();
                    $target_dir = $upload_dir['basedir'] . '/video-wc-gallery-thumb/';
                    wp_mkdir_p( $target_dir );

                    // Generate a unique filename for the uploaded image
                    $filename   = 'vwg-thumb_' . $unique_id . '.png';

                    // Save the decoded image to the target directory
                    $file_path = $target_dir . $filename;
                    file_put_contents( $file_path, $decoded_image );

                    // Set the video_thumb_url to the uploaded file URL
                    $sanitized_attachment['video_thumb_url'] = $upload_dir['baseurl'] . '/video-wc-gallery-thumb/' . $filename;

                    // Resize the image to 'woocommerce_thumbnail' size
                    $thumbnail_size = wc_get_image_size( 'woocommerce_thumbnail' );
                    $thumbnail_path = $target_dir . 'vwg-thumb_' . $unique_id . '-woocommerce_thumbnail.png';
                    $thumbnail_editor = wp_get_image_editor( $file_path );
                    if ( ! is_wp_error( $thumbnail_editor ) ) {
                        $thumbnail_editor->resize( $thumbnail_size['width'], $thumbnail_size['height'], $thumbnail_size['crop'] );
                        $thumbnail_editor->save( $thumbnail_path );
                        $sanitized_attachment['woocommerce_thumbnail_url'] = $upload_dir['baseurl'] . '/video-wc-gallery-thumb/' . basename( $thumbnail_path );
                    }

                    // Resize the image to 'woocommerce_gallery_thumbnail' size
                    $gallery_thumbnail_size = wc_get_image_size( 'woocommerce_gallery_thumbnail' );
                    $gallery_thumbnail_path = $target_dir . 'vwg-thumb_' . $unique_id . '-woocommerce_gallery_thumbnail.png';
                    $gallery_thumbnail_editor = wp_get_image_editor( $file_path );
                    if ( ! is_wp_error( $gallery_thumbnail_editor ) ) {
                        $gallery_thumbnail_editor->resize( $gallery_thumbnail_size['width'], $gallery_thumbnail_size['height'], $gallery_thumbnail_size['crop'] );
                        $gallery_thumbnail_editor->save( $gallery_thumbnail_path );
                        $sanitized_attachment['woocommerce_gallery_thumbnail_url'] = $upload_dir['baseurl'] . '/video-wc-gallery-thumb/' . basename( $gallery_thumbnail_path );
                    }
                } else {
                    $sanitized_attachment['video_thumb_url'] = $attachment['video_thumb_url'];
                }

            }

            $sanitized_urls[ $key ] = $sanitized_attachment;
        }
        update_post_meta( $post_id, 'vwg_video_url', $sanitized_urls  );
    } else {
        update_post_meta( $post_id, 'vwg_video_url', '');
    }
}
add_action( 'woocommerce_process_product_meta', 'vwg_save_custom_product_tab_content' );

/**
 * Add the media upload script
 *
 * @since 1.0
 */
function vwg_add_video_upload_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

            $("#sortable").sortable({
                opacity: 0.9,
                cursor: 'move',
            });
            $("#sortable").disableSelection();

            $( '#delete_all_video_button').on('click', function () {
                $( '.video_gallery_wrapper' ).empty()
                $('.bar-btns').hide();
            })

            $(document).on('click', '.delete_video_btn', function () {
                var videoID = $(this).data('video-id')
                $(`.video_id_${videoID}`).remove()

                if ( $('.video_gallery_wrapper li').length === 0) {
                    $('.bar-btns').hide();
                }
            })


            var add_position = -1;
            var file_frame;
            $('#add_video_button').on('click', function(event) {
                event.preventDefault();
                if (file_frame) {
                    file_frame.open();
                    return;
                }
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: '<?php echo esc_html__('Select video' , 'video-wc-gallery') ?>',
                    button: {
                        text: '<?php echo esc_html__('Select video' , 'video-wc-gallery') ?>'
                    },
                    library: {
                        type: 'video'
                    },
                    multiple: false
                });
                file_frame.on('select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();

                    add_position++

                    if ($('.video_gallery_wrapper li').length !== 0) {
                        var last_pos = parseInt($('.video_gallery_wrapper li').last().data('position'))
                        add_position = last_pos + 1
                    }

                    if ($('.video_gallery_wrapper li').length < 2) {

                        $('.video_gallery_wrapper').append(`
                            <li class="ui-state video_id_${attachment.id}" data-position="${add_position}" >
                                <div class="video-player" style="width:220px;height:200px; background:#222;">
                                    <video width="230" height="200" controls preload="auto">
                                        <source src="${attachment.url}" type="video/webm">
                                    </video>
                                <div>
                                <input type="hidden" class="video_url" name="video_url[${attachment.id}][video_url]" value="${attachment.url}"/>
                                <input type="hidden" class="video_thumb_url" name="video_url[${attachment.id}][video_thumb_url]" value=""/>
                                <button data-video-id="${attachment.id}" type="button" class="button delete_video_btn" title="Delete">X</button>
                            </li>
                        `)

                        // Load the video element
                        var video = $(`.video_id_${attachment.id} video`)[0];
                        var dataURI;

                        // When the video metadata is loaded, extract the image
                        $(video).on('loadeddata', function() {
                            // Create a canvas element
                            var canvas = $('<canvas></canvas>')[0];
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;

                            // Draw the video frame on the canvas
                            var ctx = canvas.getContext('2d');
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                            // Get the image data from the canvas and create a URL for the image
                            dataURI = canvas.toDataURL('image/png');

                            $(`.video_id_${attachment.id} .video_thumb_url`).val(dataURI)

                        });

                    } else {
                        Swal.fire(
                            '<?php echo esc_html__('Ops..' , 'video-wc-gallery') ?>',
                            '<?php echo esc_html__('Can add only 2 videos !' , 'video-wc-gallery') ?>',
                            'warning'
                        )
                    }

                    $('.bar-btns').show();
                });
                file_frame.open();
            });
        });
    </script>

    <style>
        #vwg_video_tab_content .video_gallery_wrapper {
            display: flex;
            position: relative;
            align-items: center;
            flex-wrap: wrap;
            margin-left: 15px;
            margin-right: 15px
        }
        #vwg_video_tab_content .ui-state {
            margin-right: auto;
            margin-bottom: 15px;
            position: relative;
        }
        #vwg_video_tab_content .mejs-container {
            width: 100% !important;
            height: 100% !important;
        }
        #vwg_video_tab_content .ui-state .button {
            position: absolute;
            top: -10px;
            right: -10px;
            border-radius: 50%;
        }
        #vwg_video_tab_content video {
            cursor: move;
        }
        #woocommerce-product-data ul.wc-tabs li.vwg_video_tab_tab a::before {
            content: "\f236";
        }
    </style>

    <?php
}
add_action( 'admin_footer-post.php', 'vwg_add_video_upload_script' );
add_action( 'admin_footer-post-new.php', 'vwg_add_video_upload_script' );

/**
 * Add custom style and scripts in product page
 *
 * @since 1.40
 */
function vwg_add_custom_style_and_scripts_product_page() {
    if ( is_product() ) {
        global $product;
        
        // Ensure $product is a valid object and initialize if necessary
        if (!is_object($product)) {
            $product = wc_get_product(get_the_ID());
        }

        // Verify that we now have a valid product object
        if (!is_object($product)) {
            return; // Exit the function if $product is still not valid
        }
        
        $iconColor = get_option('vwg_settings_group')['vwg_settings_icon_color'];
        $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
        $adaptSettings = get_option('vwg_settings_group')['vwg_settings_video_adapt_sizes'];
        $showFirstClassSettings = get_option('vwg_settings_group')['vwg_settings_show_first'];
        $useDefaultAttrVariable = 0;

        if ($product->is_type('variable')) {
            $default_attributes = $product->get_default_attributes();
            if ($default_attributes && isset($showFirstClassSettings) && $showFirstClassSettings == 1) {
                $useDefaultAttrVariable = 1;
            }
        }

        if ($icon == 'far fa-play-circle') {
            $unuCodeIcon = 'f144';
            $iconWeight = '500';
        } elseif ($icon == 'fas fa-play-circle') {
            $unuCodeIcon = 'f144';
            $iconWeight = '900';
        } elseif ($icon == 'fas fa-play') {
            $unuCodeIcon = 'f04b';
            $iconWeight = '900';
        } elseif ($icon == 'fas fa-video') {
            $unuCodeIcon = 'f03d';
            $iconWeight = '900';
        } elseif ($icon == 'fas fa-file-video') {
            $unuCodeIcon = 'f1c8';
            $iconWeight = '900';
        } elseif ($icon == 'far fa-file-video') {
            $unuCodeIcon = 'f1c8';
            $iconWeight = '500';
        } else {
            $unuCodeIcon = 'f04b';
            $iconWeight = '900';
        }
        ?>
        <style>
            .vwg-video-wrapper { width: 100%; height: 100%; overflow: hidden; position: relative; margin: auto !important; }
            .vwg-video-wrapper img { width: 100%; height: 100%; margin: auto !important; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) }
            /*.vwg-video-wrapper img.vwg-generated-thumb { width: 100% !important; height: 100% !important;}*/
            .vwg-video-wrapper i { font-size: 24px; color: <?=esc_attr($iconColor)?>; position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%); }
            .woocommerce div.product div.images .flex-control-thumbs li .vwg-video-wrapper {cursor: pointer;opacity: .5;margin: 0;}
            .woocommerce div.product div.images .flex-control-thumbs li .vwg-video-wrapper:hover, .woocommerce div.product div.images .flex-control-thumbs li .vwg-video-wrapper.flex-active {opacity: 1;}

            /*.woocommerce-product-gallery__image .woocommerce-product-gallery__vwg_video .video-js {*/
            /*    background-color: #000;*/
            /*    margin: 0 auto;*/
            /*}*/

            /* Center the play button */
            .vwg_video_js .vjs-big-play-button {
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                border-color: <?=esc_attr($iconColor)?> !important;
            }
            /* Replace the default play button with a FontAwesome icon */
            .vwg_video_js .vjs-big-play-button .vjs-icon-placeholder:before {
                content: '\<?=esc_attr($unuCodeIcon)?>';
                font-family: 'Font Awesome 5 Free';
                font-weight: <?=esc_attr($iconWeight)?>;
                font-size: 30px;
                color: <?=esc_attr($iconColor)?>;
            }

            <?php if (isset($adaptSettings) && $adaptSettings == 1) : ?>
            .woocommerce-product-gallery__image .woocommerce-product-gallery__vwg_video video {
                object-fit: cover;
            }

            .woocommerce-product-gallery__image .woocommerce-product-gallery__vwg_video .vjs-fullscreen video {
                object-fit: contain;
            }

            @media only screen and (max-width: 1200px) {
                .woocommerce-product-gallery__image .woocommerce-product-gallery__vwg_video .vwg_video_js {
                    position: relative;
                    width: 100%;
                    padding-top: 133.33%;
                    max-width: 100%;
                    height: 0;
                }
            }
            <?php endif; ?>

        </style>

        <?php if (vwg_active_theme_checker() === 'Flatsome') : ?>

        <script>
            jQuery( document ).ready(function($) {
                setInterval(function () {
                    var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent) || /apple/i.test(navigator.vendor);
                    var $activeVideoSlide = jQuery('.woocommerce-product-gallery__image.is-selected .woocommerce-product-gallery__vwg_video')
                    if ($activeVideoSlide.length > 0 ) {
                        var vwg_video_ID = jQuery('.woocommerce-product-gallery__image.is-selected').attr('data-vwg-video')
                        var vwg_video_isAutoPlay = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('autoplay')
                        var vwg_video_loop = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('loop')
                        var vwg_video_pause = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('pause')
                        var vwg_user_pause = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('user_pause')
                        if (isSafari && vwg_video_isAutoPlay && !vwg_video_pause ) {
                            var vwgPlayer = videojs(`vwg_video_js_${vwg_video_ID}`);
                            if (vwg_video_loop) {
                                if (!vwg_user_pause) {
                                    vwgPlayer.play();
                                    // Listen for the 'pause' event to detect when the video is paused
                                    vwgPlayer.on('pause', function () {
                                        vwgPlayer.pause();
                                        var posterUrl = vwgPlayer.poster();
                                        if (posterUrl) {
                                            var posterStyle = 'url("' + posterUrl + '")';
                                            vwgPlayer.el().style.display = 'block';
                                            vwgPlayer.el().style.backgroundImage = posterStyle;
                                            vwgPlayer.el().style.backgroundSize = 'cover';
                                            vwgPlayer.el().style.backgroundPosition = 'center';
                                        }
                                        $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('user_pause', 'true');
                                    });
                                }
                            } else {
                                vwgPlayer.play();
                                vwgPlayer.on('ended', function () {
                                    vwgPlayer.currentTime(0);
                                    $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('pause', 'true');
                                });
                            }
                        }
                        jQuery('a[href="#product-zoom"]').hide()
                    } else {
                        jQuery('a[href="#product-zoom"]').show()
                    }
                }, 500); // Check every 0.5 seconds

                /**
                 * Add function fix variable product with option video show first
                 *
                 * @since 1.39
                 */
                <?php if (isset($showFirstClassSettings) && $showFirstClassSettings == 1) : ?>
                    var isFirstLoad = true;
                    
                    // Use event model instead of overwriting WooCommerce functions
                    $(document).ready(function() {
                        setTimeout(function() {
                            // Handle found_variation event
                            $(document).on('found_variation', 'form.variations_form', function(event, variation) {
                                // Modify first slide to show variation image
                                if (variation && variation.image && variation.image.src) {
                                    var $product_gallery = $('.images');
                                    var $product_img_wrap = $product_gallery
                                        .find('.woocommerce-product-gallery__image.vwg_show_first, .woocommerce-product-gallery__image--placeholder.vwg_show_first')
                                        .eq(0);
                                 
                                    var $vwg_video = $product_img_wrap.find('div.vwg_video_js');
                                    var $gallery_first_img_icon = $('.product-thumbnails .flickity-slider').find('div.col.first a i');
                                    
                                    // Hide video and icon
                                    $vwg_video.hide();
                                    $gallery_first_img_icon.hide();
                                    
                                    // Check if we have vwg-test image, if not - create it
                                    var $vwg_custom_img = $product_img_wrap.find('a').find('img.vwg-test');
                                    if (!$vwg_custom_img.length) {
                                        $product_img_wrap.find('a').append('<img class="vwg-test" />');
                                        $vwg_custom_img = $product_img_wrap.find('a').find('img.vwg-test');
                                    }
                                    
                                    // Set image attributes
                                    $vwg_custom_img.attr('src', variation.image.src);
                                    if (variation.image.srcset) {
                                        $vwg_custom_img.attr('srcset', variation.image.srcset);
                                    }
                                    $vwg_custom_img.attr('height', variation.image.src_h);
                                    $vwg_custom_img.attr('width', variation.image.src_w);
                                    $vwg_custom_img.attr('title', variation.image.title || '');
                                    $vwg_custom_img.attr('alt', variation.image.alt || '');
                                    $vwg_custom_img.attr('data-src', variation.image.full_src || '');
                                    $vwg_custom_img.attr('data-large_image', variation.image.full_src || '');                                
                                    
                                    // Resize and zoom after processing
                                    setTimeout(function() {
                                        $(window).trigger('resize');
                                        $product_gallery.trigger('woocommerce_gallery_init_zoom');
                                    }, 20);
                                }
                            });
                            
                            // Handle reset_image event
                            $(document).on('reset_image', 'form.variations_form', function(event) {
                                var $product_gallery = $('.images');
                                var $product_img_wrap = $product_gallery
                                    .find('.woocommerce-product-gallery__image.vwg_show_first, .woocommerce-product-gallery__image--placeholder.vwg_show_first')
                                    .eq(0);
                                
                                var $vwg_video = $product_img_wrap.find('div.vwg_video_js');
                                var $vwg_custom_img = $product_img_wrap.find('a').find('img.vwg-test');
                                var $gallery_first_img_icon = $('.product-thumbnails .flickity-slider').find('div.col.first a i');
                                
                                // Show video and icon again
                                $vwg_video.show();
                                $gallery_first_img_icon.show();
                                
                                // Remove custom image
                                if ($vwg_custom_img.length) {
                                    $vwg_custom_img.remove();
                                }
                                
                                // Additional attempt to show icons
                                setTimeout(function() {
                                    $('.product-thumbnails .flickity-slider').find('div.col.first a i').show();
                                }, 50);
                            });
                            
                            // Handle reset_variations button
                            $(document).on('click', '.reset_variations', function() {
                                var $product_gallery = $('.images');
                                var $product_img_wrap = $product_gallery
                                    .find('.woocommerce-product-gallery__image.vwg_show_first, .woocommerce-product-gallery__image--placeholder.vwg_show_first')
                                    .eq(0);
                                
                                var $vwg_video = $product_img_wrap.find('div.vwg_video_js');
                                var $vwg_custom_img = $product_img_wrap.find('a').find('img.vwg-test');
                                var $gallery_first_img_icon = $('.product-thumbnails .flickity-slider').find('div.col.first a i');
                                
                                // Show video and icon again
                                $vwg_video.show();
                                $gallery_first_img_icon.show();
                                
                                // Remove custom image
                                if ($vwg_custom_img.length) {
                                    $vwg_custom_img.remove();
                                }
                            });
                        }, 500); // Give enough time for WooCommerce to load
                    });
                    <?php endif; ?>

                // Add vwg-variable class to first element
                if (<?php echo $useDefaultAttrVariable; ?> === 1) {
                    $(document).on('wc_variation_form', function() {
                        setTimeout(function() {
                            $('.product-thumbnails .flickity-slider').find('div.col.first a i').hide();
                        }, 50);
                    });
                }
            });
        </script>

        <?php else: ?>
            <script>
                jQuery( document ).ready(function($) {
                    var li_height;
                    setInterval(function () {
                        jQuery('ol.flex-control-nav').each(function() {
                            jQuery(this).find('li img').each(function(index) {
                                var src = jQuery(this).attr('src');
                                if (!src.includes('/video-wc-gallery-thumb')) {
                                    li_height = jQuery(this).height();
                                }

                                // Check if use default variable value and show first option
                                if (index === 0 ) {
                                    jQuery(this).parent('li').attr('use-default-att-variable', <?php echo esc_attr($useDefaultAttrVariable) ?>)
                                    if (jQuery(this).parent('li').attr('use-default-att-variable') === 1 && jQuery(this).closest('.vwg-video-wrapper').length === 0) {
                                        jQuery(this).wrap(`<div class="vwg-video-wrapper"></div>`);
                                        jQuery(this).closest('.vwg-video-wrapper').append('<i class="<?= esc_html($icon) ?>"></i>');
                                    }
                                    jQuery(this).closest('.vwg-video-wrapper').css(`height`, `${li_height}px`)
                                }

                                // Check if the src attribute includes '/video-wc-gallery-thumb'
                                if (src.includes('/video-wc-gallery-thumb')) {
                                    var vwg_video_wrapper = jQuery(this).closest('.vwg-video-wrapper')
                                    if (vwg_video_wrapper.length === 0) {
                                        jQuery(this).wrap(`<div class="vwg-video-wrapper"></div>`);
                                        jQuery(this).closest('.vwg-video-wrapper').append('<i class="<?= esc_html($icon) ?>"></i>');
                                    }
                                    jQuery(this).closest('.vwg-video-wrapper').css(`height`, `${li_height}px`)
                                }
                            });
                        });

                    }, 500); // Check every 0.5 seconds

                    jQuery(document).on('click touchend', '.vwg-video-wrapper i', function(event) {
                        event.preventDefault();
                        if (event.type === 'touchend' || (event.originalEvent && event.originalEvent.touches)) {
                            jQuery(this).prev().trigger('touchend')
                        } else {
                            jQuery(this).prev().trigger('click');
                        }
                    });

                    setInterval(function () {
                        var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent) || /apple/i.test(navigator.vendor);
                        var $activeVideoSlide = jQuery('.woocommerce-product-gallery__image.flex-active-slide .woocommerce-product-gallery__vwg_video')
                        if ($activeVideoSlide.length > 0 ) {
                            var vwg_video_ID = jQuery('.woocommerce-product-gallery__image.flex-active-slide').attr('data-vwg-video')
                            var vwg_video_isAutoPlay = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('autoplay')
                            var vwg_video_loop = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('loop')
                            var vwg_video_pause = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('pause')
                            var vwg_user_pause = $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('user_pause')
                            if (isSafari && vwg_video_isAutoPlay && !vwg_video_pause ) {
                                var vwgPlayer = videojs(`vwg_video_js_${vwg_video_ID}`);
                                if (vwg_video_loop) {
                                    if (!vwg_user_pause) {
                                        vwgPlayer.play();
                                        // Listen for the 'pause' event to detect when the video is paused
                                        vwgPlayer.on('pause', function () {
                                            vwgPlayer.pause();
                                            var posterUrl = vwgPlayer.poster();
                                            if (posterUrl) {
                                                var posterStyle = 'url("' + posterUrl + '")';
                                                vwgPlayer.el().style.display = 'block';
                                                vwgPlayer.el().style.backgroundImage = posterStyle;
                                                vwgPlayer.el().style.backgroundSize = 'cover';
                                                vwgPlayer.el().style.backgroundPosition = 'center';
                                            }
                                            $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('user_pause', 'true');
                                        });
                                    }
                                } else {
                                    vwgPlayer.play();
                                    vwgPlayer.on('ended', function () {
                                        vwgPlayer.currentTime(0);
                                        $activeVideoSlide.find(`#vwg_video_js_${vwg_video_ID}`).attr('pause', 'true');
                                    });
                                }
                            }
                            jQuery('.woocommerce-product-gallery__trigger').hide()
                        } else {
                            jQuery('.woocommerce-product-gallery__trigger').show()
                        }
                    }, 500); // Check every 0.5 seconds

                    /**
                     * Add function fix variable product with option video show first
                     *
                     * @since 1.40
                     */
                    <?php if (isset($showFirstClassSettings) && $showFirstClassSettings == 1) : ?>
                    var isFirstLoad = true;
                    $.fn.wc_variations_image_update = function(variation) {
                        var $form             = this,
                            $product          = $form.closest('.product'),
                            $product_gallery  = $product.find('.images'),
                            $gallery_nav      = $product.find('.flex-control-nav'),
                            $gallery_img      = $gallery_nav.find('li:not(:has(div.vwg-video-wrapper)):eq(0) img'),
                            $product_img_wrap = $product_gallery
                                .find('.woocommerce-product-gallery__image:not(.vwg_show_first), .woocommerce-product-gallery__image--placeholder:not(.vwg_show_first)')
                                .eq(0),
                            $product_img      = $product_img_wrap.find('.wp-post-image'),
                            $product_link     = $product_img_wrap.find('a').eq(0);

                        if (variation && variation.image && variation.image.src && variation.image.src.length > 1) {
                            // Update main image
                            $product_img.wc_set_variation_attr('src', variation.image.src);
                            $product_img.wc_set_variation_attr('height', variation.image.src_h);
                            $product_img.wc_set_variation_attr('width', variation.image.src_w);
                            $product_img.wc_set_variation_attr('srcset', variation.image.srcset);
                            $product_img.wc_set_variation_attr('sizes', variation.image.sizes);
                            $product_img.wc_set_variation_attr('title', variation.image.title);
                            $product_img.wc_set_variation_attr('data-caption', variation.image.caption);
                            $product_img.wc_set_variation_attr('alt', variation.image.alt);
                            $product_img.wc_set_variation_attr('data-src', variation.image.full_src);
                            $product_img.wc_set_variation_attr('data-large_image', variation.image.full_src);
                            $product_img.wc_set_variation_attr('data-large_image_width', variation.image.full_src_w);
                            $product_img.wc_set_variation_attr('data-large_image_height', variation.image.full_src_h);
                            $product_img_wrap.wc_set_variation_attr('data-thumb', variation.image.src);
                            $product_link.wc_set_variation_attr('href', variation.image.full_src);
                            $product_img_wrap.addClass('vwg-variation-image-changed');

                            window.setTimeout(function() {
                                // Clear previous executions
                                clearTimeout(window.vwgClickTimeout);
                                
                                var $flexSlider = $('.woocommerce-product-gallery').data('flexslider');
                                if ($flexSlider && $flexSlider.slides) {
                                    var slideIndex = -1;
                                    $($flexSlider.slides).each(function(index) {
                                        if ($(this).hasClass('vwg-variation-image-changed')) {
                                            slideIndex = index;
                                            return false;
                                        }
                                    });
                                    
                                    if (slideIndex >= 0) {
                                        // Activate the variation slide
                                        $flexSlider.flexAnimate(slideIndex, true);
                                    }
                                }
                                
                                // Trigger resize and zoom after processing
                                $(window).trigger('resize');
                                $product_gallery.trigger('woocommerce_gallery_init_zoom');
                            }, 100);
                        } else {
                            $form.wc_variations_image_reset();
                        }
                    };

                    $.fn.wc_variations_image_reset = function() {
                        var $form             = this,
                            $product          = $form.closest('.product'),
                            $product_gallery  = $product.find('.images'),
                            $product_img_wrap = $product_gallery
                                .find('.woocommerce-product-gallery__image:not(.vwg_show_first), .woocommerce-product-gallery__image--placeholder:not(.vwg_show_first)')
                                .eq(0),
                            $product_img      = $product_img_wrap.find('.wp-post-image'),
                            $product_link     = $product_img_wrap.find('a').eq(0);

                        // Reset all variation attributes
                        $product_img.wc_reset_variation_attr('src');
                        $product_img.wc_reset_variation_attr('width');
                        $product_img.wc_reset_variation_attr('height');
                        $product_img.wc_reset_variation_attr('srcset');
                        $product_img.wc_reset_variation_attr('sizes');
                        $product_img.wc_reset_variation_attr('title');
                        $product_img.wc_reset_variation_attr('data-caption');
                        $product_img.wc_reset_variation_attr('alt');
                        $product_img.wc_reset_variation_attr('data-src');
                        $product_img.wc_reset_variation_attr('data-large_image');
                        $product_img.wc_reset_variation_attr('data-large_image_width');
                        $product_img.wc_reset_variation_attr('data-large_image_height');
                        $product_img_wrap.wc_reset_variation_attr('data-thumb');
                        $product_link.wc_reset_variation_attr('href');
                        $product_img_wrap.removeClass('vwg-variation-image-changed');
                    };
                    <?php endif; ?>

                });
            </script>
        <?php endif; ?>

        <script>
            jQuery( document ).ready(function($) {
                // Fix if have problem with first loading classes
                if ( jQuery('.vwg_video_js').attr('autoplay') && !jQuery('.vwg_video_js').hasClass('.vjs-has-started')) {
                    jQuery('.vwg_video_js').addClass('vjs-has-started')
                } else if (!jQuery('.vwg_video_js').attr('autoplay') && !jQuery('.vwg_video_js').hasClass('.vjs-has-started')) {
                    jQuery(document).on('click touchend', '.vwg_video_js .vjs-big-play-button', function(event) {
                        event.preventDefault();
                        jQuery('.vwg_video_js').addClass('vjs-has-started')
                    });
                }
            });
        </script>


        <?php
        global $product;
        $video_url = get_post_meta( $product->get_id(), 'vwg_video_url', true );
        $video_urls = maybe_unserialize($video_url);
        $product_main_image =  wp_get_attachment_image_src($product->get_image_id(), 'woocommerce_single');
        if (is_array($product_main_image)) {
            if (isset($product_main_image[1]) && isset($product_main_image[2])) {
                $width = $product_main_image[1];
                $height = $product_main_image[2];
            }
        }

        if ( $video_url ) {
            $countVideo = 0;
            foreach ($video_urls as $video) :
                $countVideo++
                ?>
                <script type="application/ld+json">
                    {
                    "@context": "http://schema.org",
                    "@type": "VideoObject",
                    "name": "<?= esc_attr($product->get_name() . ' Video - ' . esc_attr($countVideo)) ?>",
                    "description": "<?= esc_attr($product->get_short_description()) ?>",
                    "thumbnailUrl": "<?=esc_url($video['video_thumb_url']) ?>",
                    "contentUrl": "<?=esc_url($video['video_url']) ?>",
                    "encodingFormat": "video/mp4",
                    "width": "<?=esc_attr($width) ?>",
                    "height": "<?=esc_attr($height) ?>",
                    "uploadDate": "<?=esc_attr(date('c', strtotime($product->get_date_created()->date('Y-m-d H:i:s')))) ?>",
                    "duration": "PT1M30S"
                }
                </script>
            <?php endforeach;
        }

    }
}
add_action( 'wp_footer', 'vwg_add_custom_style_and_scripts_product_page' );

/**
 * Add video in product page
 *
 * @since 1.37
 */
function vwg_add_video_to_product_gallery() {
    global $product;
    
    // Ensure $product is a valid object and initialize if necessary
    if (!is_object($product)) {
        $product = wc_get_product(get_the_ID());
    }

    // Verify that we now have a valid product object
    if (!is_object($product)) {
        return; // Exit the function if $product is still not valid
    }
    
    $video_url = get_post_meta( $product->get_id(), 'vwg_video_url', true );
    $video_urls = maybe_unserialize($video_url);
    // $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
    $controls = get_option('vwg_settings_group')['vwg_settings_video_controls'];
    $loop = get_option('vwg_settings_group')['vwg_settings_loop'];
    $muted = get_option('vwg_settings_group')['vwg_settings_muted'];
    $autoplay = get_option('vwg_settings_group')['vwg_settings_autoplay'];
    $adaptClassSettings = get_option('vwg_settings_group')['vwg_settings_video_adapt_sizes'];
    $showFirstClassSettings = get_option('vwg_settings_group')['vwg_settings_show_first'];
    $product_main_image =  wp_get_attachment_image_src($product->get_image_id(), 'woocommerce_single');

    if (is_array($product_main_image)) {
        if (isset($product_main_image[1]) && isset($product_main_image[2])) {
            $width = $product_main_image[1];
            $height = $product_main_image[2];
        }
    }

    if (isset($adaptClassSettings) && $adaptClassSettings == 1) {
        $adaptClass = '';
    } else {
        $adaptClass = 'vjs-fluid';
    }

    if ( $video_url ) {
        $countVideo = 0;
        foreach ($video_urls as $video) :
            $countVideo++
            ?>
            <div data-thumb="<?=esc_url($video['video_thumb_url']) ?>"
                 data-woocommerce_gallery_thumbnail_url="<?=esc_url((isset($video['woocommerce_gallery_thumbnail_url']))?$video['woocommerce_gallery_thumbnail_url']:'') ?>"
                 data-woocommerce_thumbnail_url="<?=esc_url((isset($video['woocommerce_thumbnail_url']))?$video['woocommerce_thumbnail_url']:'') ?>"
                 data-thumb-alt=""
                 data-vwg-video="<?=esc_attr($countVideo) ?>"
                 class="woocommerce-product-gallery__image <?php echo (isset($showFirstClassSettings) && $showFirstClassSettings == 1)?'vwg_show_first':''; ?>">
                <a href="<?=esc_url($video['video_url']) ?>" class="woocommerce-product-gallery__vwg_video">
                    <video id="vwg_video_js_<?=esc_attr($countVideo) ?>" class="video-js <?=esc_attr($adaptClass) ?> vwg_video_js" width="<?=esc_attr($width) ?>" height="<?=esc_attr($height) ?>" preload="auto" <?=esc_attr($controls) ?> <?=esc_attr($autoplay) ?> <?=esc_attr($loop) ?> <?=esc_attr($muted) ?> playsinline data-setup="{}" poster="<?=esc_url($video['video_thumb_url']) ?>">
                        <source src="<?=esc_url($video['video_url']) ?>" type="video/mp4" />
                    </video>
                </a>
            </div>
        <?php endforeach;
    }
}
if (isset($option['vwg_settings_show_first']) && $option['vwg_settings_show_first'] == 1) {
    if (vwg_active_theme_checker() === 'default') {
        add_action( 'vwg_woocommerce_product_thumbnails_first_show', 'vwg_add_video_to_product_gallery', 1 );
    } elseif (vwg_active_theme_checker() === 'Flatsome') {
        add_action( 'vwg_woocommerce_product_thumbnails_first_show_flatsome_theme', 'vwg_add_video_to_product_gallery', 1 );
    }
} else {
    add_action( 'woocommerce_product_thumbnails', 'vwg_add_video_to_product_gallery', 99 );
}


/**
 * Enqueue JS - if theme not support wc-product-gallery-zoom
 *
 * @since 1.39
 */
function vwg_enqueue_overwrite_scripts() {
    // Check if we are on the product page
    if (!is_product()) {
        return;
    }
    
    // Get the current product
    global $product;
    
    // Check if we have a valid product object
    if (!is_object($product)) {
        $product = wc_get_product(get_the_ID());
    }
    
    // If we still don't have a valid product, exit
    if (!is_object($product)) {
        return;
    }
    
    // Check if the product has videos
    $video_url = get_post_meta($product->get_id(), 'vwg_video_url', true);
    
    // Continue only if there is at least one video
    if (!empty($video_url)) {
        wp_dequeue_script('flexslider');
        wp_enqueue_script('vwg-flexslider', VWG_VIDEO_WOO_GALLERY_URL . 'woocommerce-overwrite/assets/js/flexslider/jquery.flexslider.js',  array('jquery'), VWG_VERSION_NUM, true);

        if (!current_theme_supports('wc-product-gallery-zoom')) {
            wp_dequeue_script('wc-single-product');
            wp_enqueue_script('vwg-single-product', VWG_VIDEO_WOO_GALLERY_URL . 'woocommerce-overwrite/assets/js/frontend/single-product.js',  array('jquery'), VWG_VERSION_NUM, true);

            $params = array(
                'i18n_required_rating_text' => esc_attr__( 'Please select a rating', 'woocommerce' ),
                'review_rating_required'    => wc_review_ratings_required() ? 'yes' : 'no',
                'flexslider'                => apply_filters(
                    'woocommerce_single_product_carousel_options',
                    array(
                        'rtl'            => is_rtl(),
                        'animation'      => 'slide',
                        'smoothHeight'   => true,
                        'directionNav'   => false,
                        'controlNav'     => 'thumbnails',
                        'slideshow'      => false,
                        'animationSpeed' => 500,
                        'animationLoop'  => false, // Breaks photoswipe pagination if true.
                        'allowOneSlide'  => false,
                    )
                ),
                'zoom_enabled'              => apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ),
                'zoom_options'              => apply_filters( 'woocommerce_single_product_zoom_options', array() ),
                'photoswipe_enabled'        => apply_filters( 'woocommerce_single_product_photoswipe_enabled', get_theme_support( 'wc-product-gallery-lightbox' ) ),
                'photoswipe_options'        => apply_filters(
                    'woocommerce_single_product_photoswipe_options',
                    array(
                        'shareEl'               => false,
                        'closeOnScroll'         => false,
                        'history'               => false,
                        'hideAnimationDuration' => 0,
                        'showAnimationDuration' => 0,
                    )
                ),
                'flexslider_enabled'        => apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) ),
            );

            wp_localize_script( 'vwg-single-product', 'wc_single_product_params', $params );
        }
    }
}
add_action('wp_enqueue_scripts', 'vwg_enqueue_overwrite_scripts', 20);
