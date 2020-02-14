<?php
/**
 * Loop Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$class = ( $args['class'] ) ? $args['class'] : 'button';
$class .= ' button-reveal';
$add_to_cart_text = '<i class="fas fa-cart-plus"></i>' . '<span>' . esc_html( $product->add_to_cart_text() ) . '</span>';
?>
    <div class="product-add-to-cart center">
    <?php
    echo apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
        sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
            esc_url( $product->add_to_cart_url() ),
            esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
            esc_attr( $class ),
            isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
            $add_to_cart_text
        ),
    $product, $args );
    ?>
    <?php echo do_shortcode("[ti_wishlists_addtowishlist loop=yes]"); ?>
    </div>
