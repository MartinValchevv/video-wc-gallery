<?php
/**
 * Single Product Image - Porto theme override (Video WC Gallery)
 *
 * Mirrors Porto's woocommerce/single-product/product-image.php and inserts a
 * VWG hook so video slides can be rendered as additional Owl Carousel slides
 * (wrapped in Porto's .img-thumbnail > .inner structure) inside the gallery.
 *
 * Keep this file in sync with Porto's template when its $porto_product_layout
 * branches change.
 *
 * @version 10.5.0
 */

defined( 'ABSPATH' ) || exit;

global $post, $woocommerce, $product, $porto_settings, $porto_settings_optimize, $porto_product_layout, $porto_product_info, $porto_scatted_layout;
$attachment_ids = $product->get_gallery_image_ids();

if ( ! isset( $porto_product_layout ) && ( ! wp_doing_ajax() || ! isset( $_REQUEST['action'] ) || 'porto_product_quickview' != $_REQUEST['action'] ) ) {
		$porto_product_layout = get_post_meta( get_the_ID(), 'product_layout', true );
		if ( ! $porto_product_layout ) {
			$builder_id = porto_check_builder_condition( 'product' );
			if ( $builder_id ) {
				$porto_product_layout = 'builder';
			}
		}
		$porto_product_layout = ( ! $porto_product_layout && isset( $porto_settings['product-single-content-layout'] ) ) ? $porto_settings['product-single-content-layout'] : $porto_product_layout;
		if ( ! $porto_product_layout ) {
			$porto_product_layout = 'default';
		}
	}

$items_count            = 1;
$product_images_classes = '';
$product_image_classes  = 'img-thumbnail';
$product_images_attrs   = '';
$full_size              = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );

if ( 'extended' === $porto_product_layout ) {
	$items_count               = get_post_meta( get_the_ID(), 'product_layout_columns', true );
	$items_count               = ( ! $items_count && isset( $porto_settings['product-single-columns'] ) ) ? $porto_settings['product-single-columns'] : 3;
	if ( ! empty( $porto_product_info['items'] ) ) {
		$product_images_attrs .= ' data-items="' . $porto_product_info['items'] . '"';
	} else {
		$product_images_attrs .= ' data-items="3"';
	}
	if ( ! isset( $porto_product_info['center_mode'] ) || ! empty( $porto_product_info['center_mode'] ) ) {
		$product_images_attrs .= ' data-centeritem';
	}
	if ( ! empty( $porto_product_info['responsive'] ) ) {
		$product_images_attrs .= ' data-responsive="' . esc_attr( json_encode( $porto_product_info['responsive'] ) ) . '"';
	} else {
		$columns_responsive        = array();
		$columns_responsive['768'] = 3;
		$columns_responsive['0']   = 1;
		$product_images_attrs     .= ' data-responsive="' . esc_attr( json_encode( $columns_responsive ) ) . '"';
	}
	if ( isset( $porto_product_info['loop'] ) ) {
		$product_images_attrs .= ' data-loop="' . esc_attr( $porto_product_info['loop'] ) . '"';
	}
	if ( ! empty( $porto_product_info['margin'] ) ) {
		$product_images_attrs .= ' data-margin="' . esc_attr( $porto_product_info['margin'] ) . '"';
	}
}
if ( 'grid' === $porto_product_layout ) {
	$product_images_classes = 'product-images-block row';
	$items_count            = get_post_meta( get_the_ID(), 'product_layout_grid_columns', true );
	$items_count            = ( ! $items_count && isset( $porto_settings['product-single-columns'] ) ) ? $porto_settings['product-single-columns'] : 2;
	$items_count            = '2';
	if ( '1' === $items_count ) {
		$product_image_classes .= ' col-lg-12';
	} elseif ( '2' === $items_count ) {
		$product_image_classes .= ' col-sm-6';
	} elseif ( '3' === $items_count ) {
		$product_image_classes .= ' col-sm-6 col-lg-4';
	} elseif ( '4' === $items_count ) {
		$product_image_classes .= ' col-sm-6 col-lg-3';
	}
} elseif ( 'sticky_info' === $porto_product_layout || 'sticky_both_info' === $porto_product_layout ) {
	$product_images_classes = 'product-images-block';
} else {
	$product_images_classes = 'product-image-slider owl-carousel show-nav-hover';
	if ( 'extended' === $porto_product_layout ) {
		if ( ! empty( $porto_product_info['columns_class'] ) ) {
			$product_images_classes .= ' has-ccols-spacing ' . $porto_product_info['columns_class'];
		} else {
			$product_images_classes .= ' has-ccols ccols-1 ccols-md-3 has-ccols-spacing';
		}
		if ( ! empty( $porto_product_info['enable_flick'] ) ) {
			$product_images_classes .= ' flick-carousel';
		}
	} else {
		$product_images_classes .= ' has-ccols ccols-1';
	}
}
$attach_gallery_ids = get_post_meta( $post->ID, 'porto_product_360_gallery', true );
$image_view_cls = 'image-galley-viewer';
if ( ! $porto_settings['product-image-popup'] || 'sticky_both_info' === $porto_product_layout ) {
	$image_view_cls .= ' without-zoom';
}

$post_thumbnail_id = method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : get_post_thumbnail_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
		'images',
	)
);

?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>">
	<div class="woocommerce-product-gallery__wrapper">
<div class="product-images images">
	<?php
	$html            = '<div class="' . esc_attr( $product_images_classes ) . '"' . $product_images_attrs . '>';

	$product_icon_cl = 'porto-icon-plus';
	if ( ! empty( $porto_product_info['icon_cl'] ) ) {
		$product_icon_cl = $porto_product_info['icon_cl'];
	}

	// VWG: capture whatever the registered video renderer outputs (basic plugin's
	// vwg_add_video_to_product_gallery or Pro plugin's vwg_pro_render_youtube_videos)
	// and wrap each video <div data-vwg-video=…> block in Porto's slide structure
	// so Owl Carousel treats each as a proper slide. Done once at template start
	// then reused below depending on the "show first" setting.
	$vwg_option       = get_option( 'vwg_settings_group' );
	$vwg_show_first   = isset( $vwg_option['vwg_settings_show_first'] ) && (int) $vwg_option['vwg_settings_show_first'] === 1;
	ob_start();
	do_action( 'vwg_woocommerce_product_thumbnails_first_show_porto_theme' );
	$vwg_video_output = ob_get_clean();
	$vwg_wrapped_html = '';
	if ( '' !== trim( (string) $vwg_video_output ) ) {
		$vwg_wrapped_html = preg_replace_callback(
			'#<div\b[^>]*\bdata-vwg-video=[^>]*>.*?</a>\s*</div>#is',
			function ( $m ) {
				return '<div class="img-thumbnail vwg-porto-video-slide"><div class="inner">' . $m[0] . '</div></div>';
			},
			$vwg_video_output
		);
	}
	if ( $vwg_show_first ) {
		$html .= $vwg_wrapped_html;
	}

	if ( $post_thumbnail_id ) {
		$full_src = wp_get_attachment_image_src( $post_thumbnail_id, $full_size );
		$size     = apply_filters( 'woocommerce_gallery_image_size', ( ! empty(  $porto_scatted_layout ) || 'full_width' === $porto_product_layout ) ? 'full' : 'woocommerce_single', );
		if ( ! empty( $full_src[0] ) ) {
			$item_html = '';
			$item_html .= '<div class="' . esc_attr( $product_image_classes ) . '"><div class="inner">';
			$item_html .= wp_get_attachment_image(
				$post_thumbnail_id,
				$size,
				false,
				apply_filters(
					'woocommerce_gallery_image_html_attachment_image_params',
					array(
						'href'                    => esc_url( $full_src[0] ),
						'class'                   => 'woocommerce-main-image wp-post-image',
						'title'                   => _wp_specialchars( get_post_field( 'post_title', $post_thumbnail_id ), ENT_QUOTES, 'UTF-8', true ),
						'data-large_image_width'  => esc_attr( $full_src[1] ),
						'data-large_image_height' => esc_attr( $full_src[2] ),
					),
					$post_thumbnail_id,
					$size,
					true
				)
			);
			if ( ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout || 'sticky_both_info' === $porto_product_layout ) && $attach_gallery_ids ) {
				$item_html .= '<a role="button" aria-label="View product image as 360 degree" class="' . $image_view_cls . '" href="#"><i class="porto-icon-rotate"></i></a>';
			}
			if ( $porto_settings['product-image-popup'] && ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout ) ) {
				$item_html .= '<a role="button" aria-label="Zoom the product image" class="zoom" href="' . esc_url( $full_src[0] ) . '"><i class="' . esc_attr( $product_icon_cl ) . '"></i></a>';
			}
			$item_html .= '</div></div>';
			$html .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', $item_html, $post_thumbnail_id, '', 'large', 1 );
		}

	} else {

		$image_link      = wc_placeholder_img_src( 'woocommerce_single' );
		$product_image_classes = ( ( $product->is_type( 'variable' ) && ! empty( $product->get_visible_children() ) && '' !== $product->get_price() ?
				'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
				'woocommerce-product-gallery__image--placeholder' ) ) . ' ' . $product_image_classes;
		$item_html       = '';
		$item_html      .= '<div class="' . esc_attr( $product_image_classes ) . '"><div class="inner">';
		$item_html      .= '<img src="' . esc_url( $image_link ) . '" alt="' . esc_attr__( 'Awaiting product image', 'woocommerce' ) . '" data-large_image_width="600" data-large_image_height="600" href="' . esc_url( $image_link ) . '" class="woocommerce-main-image wp-post-image" />';
		$item_html      .= '</div></div>';
		$html           .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', $item_html, '', '', 'large', 1 );
	}

	$index = 1;

	if ( $attachment_ids ) {
		foreach ( $attachment_ids as $attachment_id ) {

			$size        = apply_filters( 'woocommerce_gallery_image_size', ( ! empty(  $porto_scatted_layout ) || 'full_width' === $porto_product_layout ) ? 'full' : 'woocommerce_single' );
			$full_src    = wp_get_attachment_image_src( $attachment_id, $full_size );
			$thumb_image = wp_get_attachment_image_src( $attachment_id, $size );
			if ( empty( $full_src[0] ) ) {
				continue;
			}
			$item_html = '';

			$item_html .= '<div class="' . esc_attr( $product_image_classes ) . '"><div class="inner">';

			if ( strpos( $product_images_classes, 'product-image-slider owl-carousel' ) !== false && isset( $porto_settings_optimize['lazyload'] ) && $porto_settings_optimize['lazyload'] ) {
				$thumb_image = wp_get_attachment_image_src( $attachment_id, $size );
				if ( $thumb_image && is_array( $thumb_image ) && count( $thumb_image ) >= 3 ) {
					$placeholder = porto_generate_placeholder( $thumb_image[1] . 'x' . $thumb_image[2] );
					$item_html       .= wp_get_attachment_image(
						$attachment_id,
						$size,
						false,
						apply_filters(
							'woocommerce_gallery_image_html_attachment_image_params',
							array(
								'data-src'                => esc_url( $thumb_image[0] ),
								'src'                     => esc_url( $placeholder[0] ),
								'href'                    => esc_url( $full_src[0] ),
								'data-large_image_width'  => esc_attr( $full_src[1] ),
								'data-large_image_height' => esc_attr( $full_src[2] ),
								'class'                   => 'owl-lazy',
							),
							$attachment_id,
							$size,
							false
						)
					);
				}
			} else {
				$item_html .= wp_get_attachment_image(
					$attachment_id,
					$size,
					false,
					apply_filters(
						'woocommerce_gallery_image_html_attachment_image_params',
						array(
							'href'                    => esc_url( $full_src[0] ),
							'class'                   => 'img-responsive',
							'data-large_image_width'  => esc_attr( $full_src[1] ),
							'data-large_image_height' => esc_attr( $full_src[2] ),
						),
						$attachment_id,
						$size,
						false
					)
				);
			}
			if ( ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout || 'sticky_both_info' === $porto_product_layout ) && $attach_gallery_ids ) {
				$item_html .= '<a role="button" aria-label="View product image as 360 degree" class="' . $image_view_cls . '" href="#"><i class="porto-icon-rotate"></i></a>';
			}
			if ( $porto_settings['product-image-popup'] && ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout ) ) {
				$item_html .= '<a role="button" aria-label="Zoom the product image" class="zoom" href="' . esc_url( $full_src[0] ) . '"><i class="' . esc_attr( $product_icon_cl ) . '"></i></a>';
			}
			$item_html .= '</div></div>';
			$index ++;
			$html .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', $item_html, $attachment_id, '', 'large', $index );
		}
	}

	// VWG: append video slides after images when "show first" is disabled.
	if ( ! $vwg_show_first ) {
		$html .= $vwg_wrapped_html;
	}

	$html .= apply_filters( 'porto_single_product_gallery_img_after', '', false, $index );
	$html .= '</div>';

	if ( ( 'default' === $porto_product_layout || 'full_width' === $porto_product_layout || 'transparent' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout || 'extended' === $porto_product_layout || 'left_sidebar' === $porto_product_layout ) && $attach_gallery_ids ) {
		$html .= '<a role="button" aria-label="View product image as 360 degree" class="' . $image_view_cls . '" href="#"><i class="porto-icon-rotate"></i></a>';
	}
	if ( $porto_settings['product-image-popup'] && ( 'default' === $porto_product_layout || 'full_width' === $porto_product_layout || 'transparent' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout || 'extended' === $porto_product_layout || 'left_sidebar' === $porto_product_layout ) ) {
		$html .= '<span class="zoom" data-index="0"><i class="' . esc_attr( $product_icon_cl ) . '"></i></span>';
	}

	if ( $attach_gallery_ids ) {
		wp_enqueue_script( 'porto-360-gallery' );
		$attach_ids = json_decode( $attach_gallery_ids );
		$html .= '<div class="d-none gallery-images-wrap"><ul class="porto-360-gallery-images" data-src="';
		foreach ( $attach_ids as $key => $attach_id ) {
			if ( 0 !== $key ) {
				$html .= ',';
			}
			$html .= wp_get_attachment_image_url( $attach_id, 'full' );
		}
		$html .= '"></ul><div class="360-degree-progress-bar"></div></div>';
	}
	echo porto_filter_output( $html ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

	?>
</div>

<?php
if ( $porto_settings['product-thumbs'] ) {
	do_action( 'woocommerce_product_thumbnails' );
}
?>
	</div>
</div>
