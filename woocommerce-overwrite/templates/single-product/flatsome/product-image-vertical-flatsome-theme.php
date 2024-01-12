<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.1
 */

defined( 'ABSPATH' ) || exit;

// FL: Disable check, Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
//if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
//	return;
//}

global $product;

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
	'woocommerce-product-gallery',
	'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
	'woocommerce-product-gallery--columns-' . absint( $columns ),
	'images',
) );

$slider_classes = array('product-gallery-slider','slider','slider-nav-small','mb-0');
$rtl = 'false';
if(is_rtl()) $rtl = 'true';

// Image Zoom
if(get_theme_mod('product_zoom', 0)){
  $slider_classes[] = 'has-image-zoom';
}

?>
<div class="row row-small">
<div class="col large-10">
<?php do_action('flatsome_before_product_images'); ?>

<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?> relative mb-half has-hover" data-columns="<?php echo esc_attr( $columns ); ?>">

  <?php do_action('flatsome_sale_flash'); ?>

  <div class="image-tools absolute top show-on-hover right z-3">
    <?php do_action('flatsome_product_image_tools_top'); ?>
  </div>

  <figure class="woocommerce-product-gallery__wrapper <?php echo implode(' ', $slider_classes); ?>"
        data-flickity-options='{
                "cellAlign": "center",
                "wrapAround": true,
                "autoPlay": false,
                "prevNextButtons":true,
                "adaptiveHeight": true,
                "imagesLoaded": true,
                "lazyLoad": 1,
                "dragThreshold" : 15,
                "pageDots": false,
                "rightToLeft": <?php echo $rtl; ?>
       }'>
    <?php
    do_action( 'vwg_woocommerce_product_thumbnails_first_show_flatsome_theme' );
    if ( $product->get_image_id() ) {
      $html  = flatsome_wc_get_gallery_image_html( $post_thumbnail_id, true );
    } else {
      $html  = '<div class="woocommerce-product-gallery__image--placeholder">';
      $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
      $html .= '</div>';
    }

		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

    do_action( 'woocommerce_product_thumbnails' );

    ?>
  </figure>

  <div class="image-tools absolute bottom left z-3">
    <?php do_action('flatsome_product_image_tools_bottom'); ?>
  </div>
</div>
<?php do_action('flatsome_after_product_images'); ?>
</div>

<?php

  $attachment_ids = $product->get_gallery_image_ids();
  $thumb_count = count($attachment_ids)+1;

  $post_video_thumbnail = 0;
  $option = get_option('vwg_settings_group');

  $rtl = 'false';

  if(is_rtl()) $rtl = 'true';

  $thumb_cell_align = "left";

  if ( $attachment_ids ) {
	  $loop              = 0;
	  $image_size        = 'gallery_thumbnail';
	  $gallery_class     = array( 'product-thumbnails', 'thumbnails' );
	  $gallery_thumbnail = wc_get_image_size( apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size ) );

    if($thumb_count <= 5){
      $gallery_class[] = 'slider-no-arrows';
    }

    $gallery_class[] = 'slider row row-small row-slider slider-nav-small small-columns-4';

    ?>
    <div class="col large-2 large-col-first vertical-thumbnails pb-0">

    <div class="<?php echo implode(' ', $gallery_class); ?>"
      data-flickity-options='{
                "cellAlign": "left",
                "wrapAround": false,
                "autoPlay": false,
                "prevNextButtons": false,
                "asNavFor": ".product-gallery-slider",
                "percentPosition": true,
                "imagesLoaded": true,
                "pageDots": false,
                "rightToLeft": <?php echo $rtl; ?>,
                "contain":  true
            }'>
      <?php if (isset($option['vwg_settings_show_first']) && $option['vwg_settings_show_first'] == 1) : ?>

          <?php

          if (has_post_thumbnail()) {
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


          if ( has_post_thumbnail() && $post_video_thumbnail ) :
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
              </div>
          <?php endif;

          $video_url = get_post_meta( $product->get_id(), 'vwg_video_url', true );
          $video_urls = maybe_unserialize($video_url);
          $icon = get_option('vwg_settings_group')['vwg_settings_icon'];
          $iconColor = get_option('vwg_settings_group')['vwg_settings_icon_color'];

          foreach ( $attachment_ids as $attachment_id ) {

              if (is_array($attachment_id)) {
                  if ($gallery_thumbnail['width'] !== 100 ) {
                      $image = '<img src="' . $attachment_id['woocommerce_thumbnail_url'] . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
                  } else {
                      $image = '<img src="' . $attachment_id['woocommerce_gallery_thumbnail_url'] . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '" class="attachment-woocommerce_thumbnail" />';
                  }

                  echo sprintf('<div class="col"><a>%s<i class="'.$icon.'" style="font-size: 24px; color: '.$iconColor.'; position: absolute; left: 50%%; top: 50%%; transform: translate(-50%%,-50%%);"></i></a></div>', $image);
              } else {

                  $classes = array( '' );
                  $image_class = esc_attr( implode( ' ', $classes ) );
                  $image =  wp_get_attachment_image_src( $attachment_id, apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_'.$image_size ));
                  $image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
                  $image = '<img src="'.$image[0].'" alt="'.$image_alt.'" width="'.$gallery_thumbnail['width'].'" height="'.$gallery_thumbnail['height'].'"  class="attachment-woocommerce_thumbnail" />';

                  echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<div class="col vwg-variable"><a>%s</a></div>', $image ), $attachment_id, $post->ID, $image_class );
              }

              $loop++;
          }
          ?>

      <?php else : ?>
        <?php

       if ( has_post_thumbnail() ) :
		   ?>
        <div class="col is-nav-selected first">
          <a>
            <?php
              $image_id = get_post_thumbnail_id($post->ID);
              $image =  wp_get_attachment_image_src( $image_id, apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_'.$image_size ) );
              $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
              $image = '<img src="'.$image[0].'" alt="'.$image_alt.'" width="'.$gallery_thumbnail['width'].'" height="'.$gallery_thumbnail['height'].'" class="attachment-woocommerce_thumbnail" />';

              echo $image;
            ?>
          </a>
        </div>
      <?php endif;

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

        $classes = array( '' );
        $image_class = esc_attr( implode( ' ', $classes ) );
        $image =  wp_get_attachment_image_src( $attachment_id, apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_'.$image_size ));
        $image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
        $image = '<img src="'.$image[0].'" alt="'.$image_alt.'" width="'.$gallery_thumbnail['width'].'" height="'.$gallery_thumbnail['height'].'"  class="attachment-woocommerce_thumbnail" />';

        echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<div class="col"><a>%s</a></div>', $image ), $attachment_id, $post->ID, $image_class );
        }

        $loop++;
      }
      ?>
      <?php endif; ?>
    </div>
    </div>
<?php } ?>
</div>
