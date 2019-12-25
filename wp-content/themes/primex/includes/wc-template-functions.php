<?php
/**
 * Single product
 * content-single-product.php
 */

function snth_wc_template_after_price_line() {
    ?>
    <div class="clear"></div>
    <div class="line"></div>
    <?php
}

function snth_wc_get_gallery_image_html( $attachment_id, $main_image = false ) {
    $flexslider        = (bool) apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) );
    $gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
    $thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
    $image_size        = apply_filters( 'woocommerce_gallery_image_size', $flexslider || $main_image ? 'woocommerce_single' : 'woocommerce_single' );
    $full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
    $thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
    $full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
    $alt_text          = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
    $image             = wp_get_attachment_image(
        $attachment_id,
        $image_size,
        false,
        apply_filters(
            'woocommerce_gallery_image_html_attachment_image_params',
            array(
                'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
                'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
                'data-src'                => esc_url( $full_src[0] ),
                'data-large_image'        => esc_url( $full_src[0] ),
                'data-large_image_width'  => esc_attr( $full_src[1] ),
                'data-large_image_height' => esc_attr( $full_src[2] ),
                'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
            ),
            $attachment_id,
            $image_size,
            $main_image
        )
    );

    return '<div data-thumb="' . esc_url( $full_src[0] ) . '" data-thumb-alt="' . esc_attr( $alt_text ) . '" class="woocommerce-product-gallery__image slide"><a href="' . esc_url( $full_src[0] ) . '">' . $image . '</a></div>';
}



function snth_wc_template_loop_product_image_start() {
    ?>
    <div class="product-image">
    <?php
}

/**
 * Get the product thumbnail for the loop.
 */
function snth_wc_template_loop_product_thumbnail() {
    ?>
    <?php
    echo woocommerce_get_product_thumbnail(); // WPCS: XSS ok.
    ?>
    <?php
}

function snth_wc_template_loop_product_image_end() {
    ?>
    </div>
    <?php
}

function snth_wc_template_loop_product_quick_view_start() {
    ?>
    <div class="product-add-to-cart-quick-view">
        <a href="#" class="add-to-cart"><i class="icon-shopping-cart"></i><span> Add to Cart</span></a>
    <?php
}

/**
 * Get the add to cart template for the loop.
 *
 * @param array $args Arguments.
 */
function snth_wc_template_loop_add_to_cart( $args = array() ) {
    global $product;

    if ( $product ) {
        $defaults = array(
            'quantity'   => 1,
            'class'      => implode(
                ' ',
                array_filter(
                    array(
                        'button',
                        'product_type_' . $product->get_type(),
                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                        $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                    )
                )
            ),
            'attributes' => array(
                'data-product_id'  => $product->get_id(),
                'data-product_sku' => $product->get_sku(),
                'aria-label'       => $product->add_to_cart_description(),
                'rel'              => 'nofollow',
            ),
        );

        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

        if ( isset( $args['attributes']['aria-label'] ) ) {
            $args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
        }

        wc_get_template( 'loop/add-to-cart.php', $args );
    }
}

function snth_wc_template_loop_quick_view() {
    ?>
    <a href="include/ajax/shop-item.html" class="item-quick-view" data-lightbox="ajax"><i class="icon-zoom-in2"></i><span> Quick View</span></a>
    <?php
}

function snth_wc_template_loop_product_quick_view_end() {
    ?>
    </div>
    <?php
}

function snth_wc_template_loop_product_desc_start() {
    ?>
    <div class="product-desc center">
    <?php
}

/**
 * Show the product title in the product loop. By default this is an H2.
 */
function snth_wc_template_loop_product_title() {
    ?>
    <div class="product-title">
        <h3><?php echo get_the_title(); ?></h3>
    </div>
    <?php
}

function snth_wc_template_loop_price() {
    ?>
    <div class="product-price">
        <?php wc_get_template( 'loop/price.php' ); ?>
    </div>
    <?php
}

function snth_wc_template_loop_product_desc_end() {
    ?>
    </div>
    <?php
}