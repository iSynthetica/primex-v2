<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return;

if ( $related_products ) : ?>

	<section class="related products page-section pt-60 pb-60 pt-md-40 pb-md-40 pt-xs-40 pb-xs-40">
        <div class="container relative">
            <h2 class="section-title font-alt mb-70 mb-sm-40"><?php esc_html_e( 'Related products', 'woocommerce' ); ?></h2>

            <div class="row multi-columns-row">

                <?php foreach ( $related_products as $related_product ) : ?>

                    <?php
                    $post_object = get_post( $related_product->get_id() );

                    setup_postdata( $GLOBALS['post'] =& $post_object );

                    wc_get_template_part( 'content', 'product' ); ?>

                <?php endforeach; ?>

            </div>
        </div>
	</section>

<?php endif;

wp_reset_postdata();
