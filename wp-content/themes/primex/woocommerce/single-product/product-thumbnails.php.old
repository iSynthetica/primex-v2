<?php
/**
 * Single Product Thumbnails
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-thumbnails.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     3.5.1
 */

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product;
$attachment_ids = array();
$attachment_ids[] = $product->get_image_id();
$attachment_ids = array_merge($attachment_ids, $product->get_gallery_image_ids());

if ( $attachment_ids && $product->get_image_id() ) {
    ?>
    <div id="woocommerce-product-gallery__thumbnails" class="owl-carousel" data-margin="5" data-nav="true" data-pagi="false" data-items-xs="2" data-items-sm="3" data-items-md="4" data-items-lg="5">
        <?php
        $index = 0;
        foreach ( $attachment_ids as $attachment_id ) {
            ?>
            <div class="oc-item">
                <?php
                echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', snth_wc_get_gallery_image_html( $attachment_id, $index ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
                ?>
            </div>
            <?php
            $index++;
        }
        ?>
    </div>
    <?php
}
?>
