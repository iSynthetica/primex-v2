<?php
/**
 * Woocommerce Template Functions
 *
 * @package Hookah/Includes/WC
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns the price in html format.
 *
 * @return string
 *
 */
function snth_wc_get_price_html()
{
    global $product;

    $product_type = $product->get_type();

    if ( 'variable' === $product_type ) {
        $prices = $product->get_variation_prices( true );

        if ( empty( $prices['price'] ) ) {
            $price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
        } else {
            $min_price     = current( $prices['price'] );
            $max_price     = end( $prices['price'] );
            $min_reg_price = current( $prices['regular_price'] );
            $max_reg_price = end( $prices['regular_price'] );

            if ( $min_price !== $max_price ) {
                $price = wc_format_price_range( $min_price, $max_price );
            } elseif ( $product->is_on_sale() && $min_reg_price === $max_reg_price ) {
                $price = snth_wc_format_sale_price( wc_price( $max_reg_price ), wc_price( $min_price ) );
            } else {
                $price = '<strong>' . wc_price( $min_price ) . '</strong>';
            }

            $price = apply_filters( 'woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product );
        }

        return apply_filters( 'woocommerce_get_price_html', $price, $product );
    } elseif ( 'grouped' === $product_type  ) {
        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
        $child_prices     = array();
        $children         = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );

        foreach ( $children as $child ) {
            if ( '' !== $child->get_price() ) {
                $child_prices[] = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $child ) : wc_get_price_excluding_tax( $child );
            }
        }

        if ( ! empty( $child_prices ) ) {
            $min_price = min( $child_prices );
            $max_price = max( $child_prices );
        } else {
            $min_price = '';
            $max_price = '';
        }

        if ( '' !== $min_price ) {
            if ( $min_price !== $max_price ) {
                $price = wc_format_price_range( $min_price, $max_price );
            } else {
                $price = wc_price( $min_price );
            }

            $is_free = 0 === $min_price && 0 === $max_price;

            if ( $is_free ) {
                $price = apply_filters( 'woocommerce_grouped_free_price_html', __( 'Free!', 'woocommerce' ), $product );
            } else {
                $price = apply_filters( 'woocommerce_grouped_price_html', $price . $product->get_price_suffix(), $product, $child_prices );
            }
        } else {
            $price = apply_filters( 'woocommerce_grouped_empty_price_html', '', $product );
        }

        return apply_filters( 'woocommerce_get_price_html', $price, $product );
    }

    if ( '' === $product->get_price() ) {
        $price = apply_filters( 'woocommerce_empty_price_html', '', $product );
    } elseif ( $product->is_on_sale() ) {
        $price = snth_wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
    } else {
        $price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
    }

    return apply_filters( 'woocommerce_get_price_html', $price, $product );
}

/**
 * Format a sale price for display.
 */
function snth_wc_format_sale_price( $regular_price, $sale_price ) {
    $price = '<del class="section-text"><small>' .
             ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) .
             '</small></del> <ins><strong>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) .
             '</strong></ins>';

    return apply_filters( 'woocommerce_format_sale_price', $price, $regular_price, $sale_price );
}

/**
 * Change default currency symbol to custom one
 *
 * @param $currency_symbol
 * @param $currency
 *
 * @return mixed|null|string|void
 */
function snth_wc_change_currency_symbol( $currency_symbol, $currency )
{
    switch( $currency ) {
        case 'UAH': $currency_symbol = __(' uah', 'snthwp'); break;
    }
    return $currency_symbol;
}
add_filter('woocommerce_currency_symbol', 'snth_wc_change_currency_symbol', 10, 2);

/**
 * Output a list of variation attributes for use in the cart forms.
 *
 * @param array $args Arguments.
 * @since 2.4.0
 */
function snth_wc_dropdown_variation_attribute_options( $args = array() ) {
    $args = wp_parse_args( apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ), array(
        'options'          => false,
        'attribute'        => false,
        'product'          => false,
        'selected'         => false,
        'name'             => '',
        'id'               => '',
        'class'            => '',
        'show_option_none' => __( 'Choose an option', 'woocommerce' ),
    ) );

    // Get selected value.
    if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
        $selected_key     = 'attribute_' . sanitize_title( $args['attribute'] );
        $args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( urldecode( wp_unslash( $_REQUEST[ $selected_key ] ) ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
    }

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
    $id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
    $class                 = $args['class'];
    $show_option_none      = (bool) $args['show_option_none'];
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

    if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[ $attribute ];
    }

    $html  = '<select id="' . esc_attr( $id ) . '" class="input-md form-control ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
    $html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

    if ( ! empty( $options ) ) {
        if ( $product && taxonomy_exists( $attribute ) ) {
            // Get terms if this is a taxonomy - ordered. We need the names too.
            $terms = wc_get_product_terms( $product->get_id(), $attribute, array(
                'fields' => 'all',
            ) );

            foreach ( $terms as $term ) {
                if ( in_array( $term->slug, $options, true ) ) {
                    $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
                }
            }
        } else {
            foreach ( $options as $option ) {
                // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                $selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                $html    .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
            }
        }
    }

    $html .= '</select>';

    echo apply_filters( 'woocommerce_dropdown_variation_attribute_options_html', $html, $args ); // WPCS: XSS ok.
}

/**
 * Output the variable product add to cart area.
 */
function snth_wc_variable_add_to_cart() {
    global $product;

    // Enqueue variation scripts.
    wp_enqueue_script( 'snth_wc-add-to-cart-variation' );

    // Get Available variations?
    $get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

    // Load the template.
    wc_get_template( 'single-product/add-to-cart/variable.php', array(
        'available_variations' => $get_variations ? $product->get_available_variations() : false,
        // 'available_variations' => $get_variations ? snth_wc_get_available_variations($product) : false,
        'attributes'           => $product->get_variation_attributes(),
        'selected_attributes'  => $product->get_default_attributes(),
    ) );
}

function snth_wc_available_variation($available_variation, $product, $variation)
{

    if (!empty($available_variation['image'])) {
        $available_variation['image'] = snth_wc_get_product_attachment_props( $variation->get_image_id() );
    }
    return $available_variation;

}

/**
 * Gets data about an attachment, such as alt text and captions.
 *
 * @return array
 */
function snth_wc_get_product_attachment_props( $attachment_id = null, $product = false ) {
    $props      = array(
        'title'   => '',
        'caption' => '',
        'url'     => '',
        'alt'     => '',
        'src'     => '',
        'srcset'  => false,
        'sizes'   => false,
    );
    $attachment = get_post( $attachment_id );

    $is_hookah = snth_is_product_hookah();

    if ( $attachment ) {
        $props['title']   = wp_strip_all_tags( $attachment->post_title );
        $props['caption'] = wp_strip_all_tags( $attachment->post_excerpt );
        $props['url']     = wp_get_attachment_url( $attachment_id );

        // Alt text.
        $alt_text = array( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ), $props['caption'], wp_strip_all_tags( $attachment->post_title ) );

        if ( $product && $product instanceof WC_Product ) {
            $alt_text[] = wp_strip_all_tags( get_the_title( $product->get_id() ) );
        }

        $alt_text     = array_filter( $alt_text );
        $props['alt'] = isset( $alt_text[0] ) ? $alt_text[0] : '';

        // Large version.
        $src                 = wp_get_attachment_image_src( $attachment_id, 'full' );
        $props['full_src']   = $src[0];
        $props['full_src_w'] = $src[1];
        $props['full_src_h'] = $src[2];

        // Gallery thumbnail.
        $gallery_thumbnail                = wc_get_image_size( 'gallery_thumbnail' );
        $gallery_thumbnail_size           = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
        $src                              = wp_get_attachment_image_src( $attachment_id, $gallery_thumbnail_size );
        $props['gallery_thumbnail_src']   = $src[0];
        $props['gallery_thumbnail_src_w'] = $src[1];
        $props['gallery_thumbnail_src_h'] = $src[2];

        // Thumbnail version.
        $src                  = wp_get_attachment_image_src( $attachment_id, 'woocommerce_thumbnail' );
        $props['thumb_src']   = $src[0];
        $props['thumb_src_w'] = $src[1];
        $props['thumb_src_h'] = $src[2];

        // Image source.
        $size = $is_hookah ? 'thumb_530_720_cr' : 'thumb_720_720_cr';
        $src             = wp_get_attachment_image_src( $attachment_id, $size );
        $props['src']    = $src[0];
        $props['src_w']  = $src[1];
        $props['src_h']  = $src[2];
        $props['srcset'] = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, $size ) : false;
        $props['sizes']  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, $size ) : false;

        // Image Zoom
        $size = $is_hookah ? 'single_hookah_zoom' : 'single_product_zoom';
        $src            = wp_get_attachment_image_src( $attachment_id, $size );
        $props['zoom_thumb_src']   = $src[0];
        $props['zoom_thumb_src_w'] = $src[1];
        $props['zoom_thumb_src_h'] = $src[2];
    }
    return $props;
}

/**
 * Get HTML for a gallery image.
 *
 * Woocommerce_gallery_thumbnail_size, woocommerce_gallery_image_size and woocommerce_gallery_full_size accept name based image sizes, or an array of width/height values.
 */
function snth_wc_get_gallery_image_html( $attachment_id, $main_image = false, $args = array(), $is_hookah = false ) {

    $args = wp_parse_args( $args, array(
        'img_class'     => '',
        'link_class'    => '',
        'holder_class'  => '',
    ) );

    $flexslider        = (bool) apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) );
    $gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
    $thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
    $image_size        = apply_filters( 'woocommerce_gallery_image_size', $main_image ? ( $is_hookah ? 'thumb_530_720_cr' : 'thumb_720_720_cr') : $thumbnail_size );
    $zoom_size         = $is_hookah ? 'single_hookah_zoom' : 'single_product_zoom';
    $full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', $zoom_size ) );
    $thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
    $full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
    $zoom_src          = wp_get_attachment_image_src( $attachment_id, $zoom_size );

    $image_class = $main_image ? 'wp-post-image ' . $args['img_class'] : ' ' . $args['img_class'];

    $image_attr = array(
        'title'                   => get_post_field( 'post_title', $attachment_id ),
        'data-caption'            => get_post_field( 'post_excerpt', $attachment_id ),
        'data-src'                => $full_src[0],
        'data-large_image'        => $full_src[0],
        'data-large_image_width'  => $full_src[1],
        'data-large_image_height' => $full_src[2],
        'class'                   => $image_class,
    );

    if ( $main_image ) {
        $image_attr['data-zoom-image'] = $zoom_src[0];
    }

    $image             = wp_get_attachment_image( $attachment_id, $image_size, false, $image_attr);

    $link_class = ' ' . $args['link_class'];

    $holder_class = 'woocommerce-product-gallery__image ' . $args['holder_class'];

    $holder_data_string = '';

    if (!empty($args['holder_data'])) {
        foreach ($args['holder_data'] as $key => $value) {
            $holder_data_string .= ' ' . $key . '="' . $value . '"';
        }
    }

    $html = '<div 
                data-thumb="' . esc_url( $thumbnail_src[0] ) . '" 
                class="'.$holder_class.'"' . $holder_data_string .'
             >';
    $html .= '<a href="' . esc_url( $full_src[0] ) . '" class="'.$link_class.'">';
    $html .= $image;
    $html .= '</a>';
    $html .= '</div>';


    return $html;
}

function snth_wc_get_accessories_array() {
    return array(
        'hookah-trays',
        'hookah-bases',
        'hookah-parts',
        'hookah-hoses',
        'hookah-bowls',
    );
}

/**
 * Output Hookah up sells.
 *
 * @param int    $limit (default: -1).
 * @param int    $columns (default: 4).
 * @param string $orderby Supported values - rand, title, ID, date, modified, menu_order, price.
 * @param string $order Sort direction.
 */
function snth_wc_hookah_upsell_display() {
    global $product, $post;

    if ( !$product ) {
        return;
    }

    $accessories_array = snth_wc_get_accessories_array();

    $upsell_ids = get_field('product_upsells_products', $post->ID);

    if (empty($upsell_ids)) {
        if (snth_is_product_hookah()) {
            $upsell_ids = get_field('hookah_upsells_products', 'options');
        } else {
            foreach ($accessories_array as $category) {
                if (snth_is_product($category)) {
                    $upsell_ids = get_field($category . '_upsells_products', 'options');

                    continue;
                }

                if (empty($upsell_ids)) {
                    $upsell_ids = get_field('hookah-accessories_upsells_products', 'options');
                }
            }
        }
    }

    if (empty($upsell_ids)) {
        return;
    }

    wc_get_template( 'single-product/hookah-up-sells.php', array(
        'upsells_ids'        => $upsell_ids,
    ) );
}

function snth_woo_show_products_slider( $product_ids )
{
    $products = array();

    foreach ($product_ids as $id) {
        if($product = wc_get_product( $id )) {
            $products[] = $product;
        }
    }

    ?>
    <div class="products-carousel products">
        <?php foreach ( $products as $product ) : ?>

            <?php
            $post_object = get_post( $product->get_id() );

            setup_postdata( $GLOBALS['post'] =& $post_object );

            wc_get_template_part( 'content', 'product-carousel' );
            ?>

        <?php endforeach; ?>
    </div>
    <?php
    wp_reset_postdata();
}

function snth_is_product($category) {
    global $post;

    $terms = get_the_terms( $post->ID, 'product_cat' );

    if(empty($terms)) {
        return false;
    }

    $hookah_terms = array(
        $category
    );

    foreach ($terms as $term) {
        if(in_array($term->slug, $hookah_terms)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if current product is Hookah category
 */
function snth_is_product_hookah()
{
    return snth_is_product('hookahs') || snth_is_product('hookahs-en');
}

/**
 * Check if current product is Hookah category
 */
function snth_is_product_hookah_hose()
{
    return snth_is_product('hookah-hoses') || snth_is_product('hookah-hoses-en');
}

/**
 * Remove 'Select option" list item
 *
 * @param $html
 * @param $args
 *
 * @return mixed
 */
function snth_woo_filter_dropdown_option_html( $html, $args ) {
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );
    $show_option_none_html = '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

    $html = str_replace($show_option_none_html, '', $html);

    return $html;
}

add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'snth_woo_filter_dropdown_option_html', 12, 2 );