<?php

global $post, $product;

$attachment_ids = $product->get_gallery_image_ids();
$post_thumbnail = has_post_thumbnail();
$thumb_count    = count( $attachment_ids );

$post_video_thumbnail = 0;
$option = get_option('vwg_settings_group');


if ( $post_thumbnail ) $thumb_count++;

// Disable thumbnails if there is only one extra image.
if ( $post_thumbnail && $thumb_count == 1 ) {
	return;
}

$rtl              = 'false';
$thumb_cell_align = 'left';

if ( is_rtl() ) {
	$rtl              = 'true';
	$thumb_cell_align = 'right';
}

if ( $attachment_ids ) {
	$loop          = 0;
	$image_size    = 'thumbnail';
	$gallery_class = array( 'product-thumbnails', 'thumbnails' );

	// Check if custom gallery thumbnail size is set and use that.
	$image_check = wc_get_image_size( 'gallery_thumbnail' );
	if ( $image_check['width'] !== 100 ) {
		$image_size = 'gallery_thumbnail';
	}

	$gallery_thumbnail = wc_get_image_size( apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size ) );

	if ( $thumb_count < 5 ) {
		$gallery_class[] = 'slider-no-arrows';
	}

	$gallery_class[] = 'slider row row-small row-slider slider-nav-small small-columns-4';
	?>
	<div class="<?php echo implode( ' ', $gallery_class ); ?>"
		data-flickity-options='{
			"cellAlign": "<?php echo $thumb_cell_align; ?>",
			"wrapAround": false,
			"autoPlay": false,
			"prevNextButtons": true,
			"asNavFor": ".product-gallery-slider",
			"percentPosition": true,
			"imagesLoaded": true,
			"pageDots": false,
			"rightToLeft": <?php echo $rtl; ?>,
			"contain": true
		}'>
        <?php if (isset($option['vwg_settings_show_first']) && $option['vwg_settings_show_first'] == 1) : ?>
            <?php

            if ($post_thumbnail) {
                $attachment_ids[] = get_post_thumbnail_id($post->ID);
                array_unshift($attachment_ids, array_pop($attachment_ids));
            }

            $video_url = get_post_meta( $product->get_id(), 'vwg_video_url', true );
            $video_urls = maybe_unserialize($video_url);
            $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
            $iconColor = get_option('vwg_settings_group')['vwg_settings_icon_color'];

            if ( $video_url ) {
                $countVideo = 0;
                foreach ($video_urls as $video) {
                    $countVideo++;
                    $attachment_ids[] = array(
                        'video_thumb_url' => isset($video['video_thumb_url']) ? esc_url($video['video_thumb_url']) : '',
                        'woocommerce_thumbnail_url' => isset($video['woocommerce_thumbnail_url']) ? esc_url($video['woocommerce_thumbnail_url']) : esc_url($video['video_thumb_url']),
                        'woocommerce_gallery_thumbnail_url' => isset($video['woocommerce_gallery_thumbnail_url']) ? esc_url($video['woocommerce_gallery_thumbnail_url']) : esc_url($video['video_thumb_url']),
                        'video_url' => isset($video['video_url']) ? esc_url($video['video_url']) : '',
                        'count' => $countVideo
                    );
                }
            }

            $attachment_ids = array_reverse($attachment_ids);

            foreach ($attachment_ids as $key => $video) {
                if (is_array($video) && isset($video['count']) && $video['count'] === 1) {
                    $post_video_thumbnail = 1;
                    $videoThumbUrl = $video['video_thumb_url'];
                    $woocommerce_thumbnail_url = $video['woocommerce_thumbnail_url'];
                    $woocommerce_gallery_thumbnail_url = $video['woocommerce_gallery_thumbnail_url'];
                    $videoUrl = $video['video_url'];

                    unset($attachment_ids[$key]);
                    break;
                }
            }


            if ( $post_thumbnail && $post_video_thumbnail) :
                ?>
                <div class="col is-nav-selected first">
                <a>
                    <?php
                    if ($gallery_thumbnail['width'] !== 100 ) {
                        $image = '<img src="' . $woocommerce_thumbnail_url . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
                    } else {
                        $image = '<img src="' . $woocommerce_gallery_thumbnail_url . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
                    }

                    echo sprintf('%s<i class="'.$icon.'" style="font-size: 24px; color: '.$iconColor.'; position: absolute; left: 50%%; top: 50%%; transform: translate(-50%%,-50%%);"></i>', $image);
                    ?>
                </a>
                </div><?php
            endif;

            foreach ( $attachment_ids as $attachment_id ) {

                if (is_array($attachment_id)) {
                    if ($gallery_thumbnail['width'] !== 100 ) {
                        $image = '<img src="' . $attachment_id['woocommerce_thumbnail_url'] . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
                    } else {
                        $image = '<img src="' . $attachment_id['woocommerce_gallery_thumbnail_url'] . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
                    }

                    echo sprintf('<div class="col"><a>%s<i class="'.$icon.'" style="font-size: 24px; color: '.$iconColor.'; position: absolute; left: 50%%; top: 50%%; transform: translate(-50%%,-50%%);"></i></a></div>', $image);
                } else {

                    $classes = array('');
                    $image_class = esc_attr(implode(' ', $classes));
                    $image = wp_get_attachment_image_src($attachment_id, apply_filters('woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size));

                    if (empty($image)) {
                        continue;
                    }

                    $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                    $image = '<img src="' . $image[0] . '" alt="' . $image_alt . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '"  class="attachment-woocommerce_thumbnail" />';

                    echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<div class="col vwg-variable"><a>%s</a></div>', $image ), $attachment_id, $post->ID, $image_class );
                }

                $loop ++;
            }
            ?>
        <?php else : ?>
		<?php


		if ( $post_thumbnail ) :
			?>
			<div class="col is-nav-selected first">
				<a>
					<?php
					$image_id  = get_post_thumbnail_id( $post->ID );
					$image     = wp_get_attachment_image_src( $image_id, apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size ) );
					$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					$image     = '<img src="' . $image[0] . '" alt="' . $image_alt . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';

					echo $image;
					?>
				</a>
			</div><?php
		endif;


        $video_url = get_post_meta( $product->get_id(), 'vwg_video_url', true );
        $video_urls = maybe_unserialize($video_url);
        $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
        $iconColor = get_option('vwg_settings_group')['vwg_settings_icon_color'];

        if ( $video_url ) {
            $countVideo = 0;
            foreach ($video_urls as $video) {
                $countVideo++;
                $attachment_ids[] = array(
                    'video_thumb_url' => isset($video['video_thumb_url']) ? esc_url($video['video_thumb_url']) : '',
                    'woocommerce_thumbnail_url' => isset($video['woocommerce_thumbnail_url']) ? esc_url($video['woocommerce_thumbnail_url']) : esc_url($video['video_thumb_url']),
                    'woocommerce_gallery_thumbnail_url' => isset($video['woocommerce_gallery_thumbnail_url']) ? esc_url($video['woocommerce_gallery_thumbnail_url']) : esc_url($video['video_thumb_url']),
                    'video_url' => isset($video['video_url']) ? esc_url($video['video_url']) : '',
                    'count' => $countVideo
                );
            }
        }

		foreach ( $attachment_ids as $attachment_id ) {

        if (is_array($attachment_id)) {
            if ($gallery_thumbnail['width'] !== 100 ) {
                $image = '<img src="' . $attachment_id['woocommerce_thumbnail_url'] . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
            } else {
                $image = '<img src="' . $attachment_id['woocommerce_gallery_thumbnail_url'] . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
            }

            echo sprintf('<div class="col"><a>%s<i class="'.$icon.'" style="font-size: 24px; color: '.$iconColor.'; position: absolute; left: 50%%; top: 50%%; transform: translate(-50%%,-50%%);"></i></a></div>', $image);
        } else {

            $classes = array('');
            $image_class = esc_attr(implode(' ', $classes));
            $image = wp_get_attachment_image_src($attachment_id, apply_filters('woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size));

            if (empty($image)) {
                continue;
            }

            $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            $image = '<img src="' . $image[0] . '" alt="' . $image_alt . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '"  class="attachment-woocommerce_thumbnail" />';

            echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<div class="col"><a>%s</a></div>', $image ), $attachment_id, $post->ID, $image_class );
        }

			$loop ++;
		}
		?>
        <?php endif; ?>
	</div>
	<?php
} ?>
