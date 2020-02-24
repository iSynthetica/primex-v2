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
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.2
 */

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product;

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
	'woocommerce-product-gallery',
	'images',
) );

$is_hookah = snth_is_product_hookah();
?>

<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
    <figure class="woocommerce-product-gallery__wrapper">
        <?php
        if($is_hookah) {
            ?>
            <div class="row">
                <div class="column col-xs-9 col-xs-push-3">
            <?php
        }
        ?>
        <div class="woocommerce-product-main-image__wrapper">
            <?php
            if ( has_post_thumbnail() ) {
                $html  = snth_wc_get_gallery_image_html(
                    $post_thumbnail_id,
                    true,
                    array(
                        'img_class' => 'woocommerce-gallery zoomImage',
                        'link_class' => 'woocommerce-gallery',
                        'holder_class'  => 'post-prev-img',
                    ),
                    $is_hookah
                );
            } else {
                $html  = '<div class="woocommerce-product-gallery__image--placeholder">';
                $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
                $html .= '</div>';
            }

            echo $html;
            ?>


        </div>
        <?php
        if($is_hookah) {
            ?>
                </div>

                <div class="column col-xs-3 col-xs-pull-9">
                    <?php wc_get_template( 'single-product/product-hookah-parts.php' ); ?>
                </div>
            </div>
            <?php
        }
        ?>

        <?php do_action( 'woocommerce_product_thumbnails' ); ?>
    </figure>
</div>
