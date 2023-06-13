<?php
/**
 * Operations of the plugin are included here.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue CSS and JS
 *
 * @since 1.0
 */
function vwg_enqueue_scripts( $hook ) {

    if ( is_product() ) {
        // CSS
        wp_enqueue_style('vwg_fontawesome', VWG_VIDEO_WOO_GALLERY_URL . 'includes/fontawesome5/css/all.css', '', VWG_VERSION_NUM);

        // JS

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
 * @since 1.3
 */
function vwg_save_custom_product_tab_content( $post_id ) {
    if ( isset( $_POST['video_url'] ) )  {
        $sanitized_urls = array();
        foreach ( $_POST['video_url'] as $key => $attachment ) {

            $sanitized_attachment = array(
                'video_url' => wp_kses_post( $attachment['video_url'] ),
                'video_thumb_url' => wp_kses_post( $attachment['video_thumb_url'] ),
            );


            if ( isset( $attachment['video_thumb_url'] ) ) {
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
                $filename = 'vwg-thumb_' . uniqid() . '.png';

                // Save the decoded image to the target directory
                $file_path = $target_dir . $filename;
                file_put_contents( $file_path, $decoded_image );

                // Set the video_thumb_url to the uploaded file URL
                $sanitized_attachment['video_thumb_url'] = $upload_dir['baseurl'] . '/video-wc-gallery-thumb/' . $filename;
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
 * @since 1.3
 */
function vwg_add_custom_style_and_scripts_product_page() {
    if ( is_product() ) {
        $iconColor = get_option('vwg_settings_group')['vwg_settings_icon_color'];
        $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
        ?>
        <style>
            .vwg-video-wrapper { width: 100%; height: 100%; overflow: hidden; position: relative; margin: auto !important; }
            .vwg-video-wrapper img { width: 100%; height: 100%; margin: auto !important; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) }
            .vwg-video-wrapper i { font-size: 24px; color: <?=esc_attr($iconColor)?>; position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%); }
            .woocommerce div.product div.images .flex-control-thumbs li .vwg-video-wrapper {cursor: pointer;opacity: .5;margin: 0;}
            .woocommerce div.product div.images .flex-control-thumbs li .vwg-video-wrapper:hover, .woocommerce div.product div.images .flex-control-thumbs li .vwg-video-wrapper.flex-active {opacity: 1;}

            .woocommerce-product-gallery__image .woocommerce-product-gallery__vwg_video video {
                display: block;
                width: 100%;
                height: auto;
            }

            .woocommerce-product-gallery__image .woocommerce-product-gallery__vwg_video video:not(:playing) {
                opacity: 0;
                visibility: hidden;
            }
        </style>

        <script>
            jQuery( document ).ready(function() {
                jQuery(document).on('click', '.vwg-video-wrapper i', function() {
                    jQuery(this).prev().click()
                });
            });

            jQuery(window).on('load', function() {
                var li_height;
                jQuery('ol.flex-control-nav li img').each(function(index) {
                    var src = jQuery(this).attr('src');
                    if (index === 0) {
                        li_height = jQuery(this).parent('li').height();
                    }
                    // Check if the src attribute includes uploads/video-wc-gallery-thumb'
                    if (src.includes('uploads/video-wc-gallery-thumb')) {
                        jQuery(this).wrap(`<div class="vwg-video-wrapper"></div>`);
                        jQuery(this).closest('.vwg-video-wrapper').append('<i class="<?= esc_html($icon) ?>"></i>');
                        jQuery(this).closest('.vwg-video-wrapper').css(`height`, `${li_height}px`)
                    }
                });
            });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'vwg_add_custom_style_and_scripts_product_page' );

/**
 * Add video in product page
 *
 * @since 1.3
 */
function vwg_add_video_to_product_gallery() {
    global $product;
    $video_url = get_post_meta( $product->get_id(), 'vwg_video_url', true );
    $video_urls = maybe_unserialize($video_url);
    // $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
    $controls = get_option('vwg_settings_group')['vwg_settings_video_controls'];
    $loop = get_option('vwg_settings_group')['vwg_settings_loop'];
    $muted = get_option('vwg_settings_group')['vwg_settings_muted'];
    $autoplay = get_option('vwg_settings_group')['vwg_settings_autoplay'];

    if ( $video_url ) {
        foreach ($video_urls as $video) :
            ?>
            <div data-thumb="<?=esc_url($video['video_thumb_url']) ?>" data-thumb-alt="" data-vwg-video="1" class="woocommerce-product-gallery__image">
                <a href="<?=esc_url($video['video_url']) ?>" class="woocommerce-product-gallery__vwg_video">
                    <video src="<?=esc_url($video['video_url']) ?>" <?=esc_attr($controls) ?> <?=esc_attr($autoplay) ?> <?=esc_attr($loop) ?> <?=esc_attr($muted) ?> playsinline></video>
                </a>
            </div>
        <?php endforeach;
    }
}
add_action( 'woocommerce_product_thumbnails', 'vwg_add_video_to_product_gallery', 99 );

