<?php
/**
 * Woocommerce Core library
 *
 * @package Hooka/WC/Includes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('SNTH_NP', true);

/**
 * Add Woocommerce support to theme
 */
function snth_wc_support()
{
    add_theme_support( 'woocommerce' );
    // add_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'snth_wc_support' );

/**
 * Switched off Woocommerce styling
 */
function snth_wc_dequeue_styles( $enqueue_styles )
{
    unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
    unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
    unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation

    return $enqueue_styles;
}
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
//add_filter( 'woocommerce_enqueue_styles', 'snth_wc_dequeue_styles' );

require_once(SNTH_INCLUDES.'/wc-template-hooks.php');
require_once(SNTH_INCLUDES.'/wc-template-functions.php');
require_once(SNTH_INCLUDES.'/wc-checkout.php');

if (SNTH_NP) {
    require_once(SNTH_INCLUDES.'/wc-novaposhta.php');
}

/**
 * Output WooCommerce content.
 */
function joints_woo_content() {
    if ( is_singular( 'product' ) ) {
        while ( have_posts() ) {
            the_post();

            wc_get_template_part( 'content', 'single-product' );
        }
    } else {
        wc_get_template_part( 'archive', 'product' );
    }
}

/**
 * Add discount to total cart
 *
 * @param $cart
 */
function joints_woo_add_discount( $cart ) {

    $discount = joints_woo_get_discount( $cart );

    if (0 < $discount) {
        $cart->add_fee( __( 'Your discount', 'snthwp' ) , -$discount );
    }

}
add_action( 'woocommerce_cart_calculate_fees', 'joints_woo_add_discount' );

/**
 * Count total discount
 *
 * @param $cart
 *
 * @return float|int
 */
function joints_woo_get_discount( $cart ) {

    $discount = 0;

    $cart_content = $cart->get_cart();

    $wholesale_groups = joints_woo_get_wholesale_groups();
    $wholesale_group_products = joints_woo_group_cart_by_wholesale_groups($wholesale_groups, $cart_content);

    if (!empty($wholesale_group_products)) {
        foreach ($wholesale_group_products as $wholesale_group => $wholesale_product) {
            $discount += joints_woo_get_product_discount($wholesale_group, $wholesale_product);
        }
    }

    if (empty($wholesale_group_products['hookah-hose-karma'])) {
        $discount += joints_woo_get_hookah_hoses_discount( $cart_content );
    }

    return $discount;
    return $hookah_discount + $hookah_hoses_discount;
}

/**
 * Get List of Wholesale Groups
 */
function joints_woo_get_wholesale_groups() {
    $wholesale_groups = get_terms( 'wholesale_group', array(
        'hide_empty' => false,
    ) );

    return $wholesale_groups;
}

/**
 *
 */
function joints_woo_group_cart_by_wholesale_groups($wholesale_groups, $cart_content) {
    $cart_products_array = joints_woo_group_content_by_products($cart_content);
    $wholesale_group_products = array();

    foreach ($wholesale_groups as $wholesale_group) {
        $min_quantity = get_field('product_min_discount', 'term_' . $wholesale_group->term_id);

        $args = array(
            'post_type' => 'product',
            'numberposts' => -1,
            'wholesale_group' => $wholesale_group->slug
        );
        $products = get_posts( $args );

        $products_array = array();

        foreach ($products as $product) {
            $_product_id = $product->ID;

            foreach ($cart_products_array as $cart_id => $cart_total) {
                if ($_product_id === $cart_id) {
                    $products_array[$_product_id] = $cart_total;

                    continue;
                }
            }
        }

        $wholesale_group_products[$wholesale_group->slug]['min_quantity'] = (int) $min_quantity;
        $wholesale_group_products[$wholesale_group->slug]['quantity'] = 0;
        $wholesale_group_products[$wholesale_group->slug]['products'] = $products_array;
    }

    foreach ($wholesale_group_products as $wholesale_group => $data) {
        $data_products = $data['products'];

        if (empty($data_products)) {
            unset($wholesale_group_products[$wholesale_group]);
        } else {
            foreach ($data_products as $data_product) {
                $wholesale_group_products[$wholesale_group]['quantity'] += $data_product['quantity'];
            }
        }
    }

    foreach ($wholesale_group_products as $wholesale_group => $data) {
        if ($data['min_quantity'] > $data['quantity']) {
            unset($wholesale_group_products[$wholesale_group]);
        }
    }

    return $wholesale_group_products;
}

function joints_woo_group_content_by_products($cart_content) {
    $cart_products_array = array();

    foreach ( $cart_content as $product ) {
        $_product = $product['data'];
        $_type = $_product->get_type( );
        $_id = $_product->get_id( );

        if ('variation' === $_type) {
            $_id = $_product->get_parent_id( );
        }

        if (!empty($cart_products_array[$_id])) {
            $cart_products_array[$_id]['quantity'] =  $cart_products_array[$_id]['quantity'] + (int) $product['quantity'];
            $cart_products_array[$_id]['total'] =  $cart_products_array[$_id]['total'] + (int) $product['line_total'];
        } else {
            $cart_products_array[$_id]['quantity'] =  (int) $product['quantity'];
            $cart_products_array[$_id]['total'] =  (int) $product['line_total'];
        }
    }

    return $cart_products_array;
}

function joints_woo_get_product_discount($wholesale_group, $wholesale_product) {
    $product_discount = 0;

    foreach ($wholesale_product['products'] as $product_id => $product) {
        $product_discount += joints_woo_calculate_product_discount($wholesale_group, $product_id, $product, $wholesale_product['quantity']);
    }

    return $product_discount;
}

function joints_woo_calculate_product_discount($wholesale_group, $product_id, $product, $wholesale_quantity) {
    $wholesale_table = joints_woo_get_product_wholesale_table($wholesale_group, $product_id);

    if (!$wholesale_table) {
        return 0;
    }

    foreach ($wholesale_table as $rule) {
        $min = $rule['discount_price']['min'];
        $max = $rule['discount_price']['max'];

        if (
            ('' !== $max && '' !== $min && $wholesale_quantity <= $max && $wholesale_quantity >= $min) ||
            ('' === $max && '' !== $min && $wholesale_quantity >= $max)
        ) {
            $currency = apply_filters( 'wcml_price_currency', NULL );

            if ($currency) {
                $price = $rule['discount_price']['price'];

                $price = apply_filters('wcml_raw_price_amount', $price, $currency);
            } else {
                $price = $rule['discount_price']['price'];
            }

            break;
        } else {
            $price = 0;
        }
    }

    //return 25;
    return $product['total'] - ($price * $product['quantity']);
}

function joints_woo_get_product_wholesale_table($wholesale_group, $product) {

    $wholesale_table = get_field('product_discount_table', $product);

    if (!$wholesale_table) {
        $wholesale_group_object = get_term_by( 'slug', $wholesale_group, 'wholesale_group' );
        $wholesale_table = get_field('product_discount_table', 'term_' . $wholesale_group_object->term_id);
    }

    return $wholesale_table;
}

/**
 * Get table with discounted prices for hookah
 *
 * @param $id
 *
 * @return mixed|null
 */
function joints_woo_get_hookah_discount_table($id) {

    $discount_table = get_field('hookah_discount_table', $id);

    if (!$discount_table) {
        $discount_table = get_field('hookah_discount_table', 'options');
    }

    return $discount_table;
}

/**
 * Get hoses discount
 *
 * @param $cart_content
 *
 * @return int
 */
function joints_woo_get_hookah_hoses_discount( $cart_content ) {

    $hookah_hoses_discount = 0;
    $count_for_discount = 0;

    $count_hookah_hoses = joints_woo_count_product_by_category( $cart_content, array('hookah-hoses'));
    $count_hookah = joints_woo_count_product_by_category( $cart_content, array('hookahs'));

    if (0 !== $count_hookah && 0 !== $count_hookah_hoses ) {
        $count_for_discount = $count_hookah_hoses > $count_hookah ? $count_hookah : $count_hookah_hoses;
    }

    if ($count_for_discount === 0) {
        return 0;
    }

    $just_hookah_hoses = joints_woo_get_product_by_category( $cart_content, array('hookah-hoses'));

    $discount = joints_calculate_hookah_hoses_discount($just_hookah_hoses, $count_for_discount);

    return $discount;
}


function joints_calculate_hookah_hoses_discount($hookah_hoses, $count) {
    $hookah_hoses_discounts = array();
    $discount = 0;

    foreach ($hookah_hoses as $product) {
        $_product = $product['data'];
        $_id = $_product->get_id( );
        $_quantity = $product['quantity'];
        $_price = (int) $_product->get_price();

        $discount_price = joints_woo_get_hookah_hoses_discount_table($_id);

        if ($discount_price) {
            $currency = apply_filters( 'wcml_price_currency', NULL );

            if ($currency) {
                $discount_price = apply_filters('wcml_raw_price_amount', $discount_price, $currency);
            }
        }

        if ( $discount_price && $discount_price < $_price ) {
            for ($i = 0; $i < $_quantity; $i++ ) {
                $hookah_hoses_discounts[] = $_price - $discount_price;
            }
        }
    }

    sort($hookah_hoses_discounts);

    for ($i = 0; $i < $count; $i++) {
        $discount +=  $hookah_hoses_discounts[$i];
    }

    return $discount;
}

/**
 * Get table with discounted prices for hookah
 *
 * @param $id
 *
 * @return mixed|null
 */
function joints_woo_get_hookah_hoses_discount_table($id) {

    $discount_table = get_field('hookah_hoses_with_hookah_discount', $id);

    if (!$discount_table) {
        $discount_table = get_field('hookah_hoses_with_hookah_discount', 'options');
    }

    return $discount_table;
}

/**
 * Count products in certain category
 *
 * @param $cart_content
 * @param $category
 *
 * @return int
 */
function joints_woo_count_product_by_category( $cart_content, $category ) {
    $count = 0;

    foreach ($cart_content as $cart_item_key => $values) {
        $_product = $values['data'];
        $_type = $_product->get_type( );
        $_id = $_product->get_id( );

        if ('variation' === $_type) {
            $_id = $_product->get_parent_id( );
        }

        $terms = get_the_terms( $_id, 'product_cat' );

        if(empty($terms)) {
            continue;
        }

        foreach ($terms as $term) {
            if(in_array($term->slug, $category)) {
                $count += $values['quantity'];
            }
        }
    }

    return $count;
}

/**
 * Get list of product by category
 *
 * @param $cart_content
 * @param $category
 *
 * @return array
 */
function joints_woo_get_product_by_category( $cart_content, $category ) {
    $category_products = array();

    foreach ($cart_content as $cart_item_key => $values) {
        $_product = $values['data'];
        $_type = $_product->get_type( );
        $_id = $_product->get_id( );

        if ('variation' === $_type) {
            $_id = $_product->get_parent_id( );
        }

        $terms = get_the_terms( $_id, 'product_cat' );

        if(empty($terms)) {
            continue;
        }

        foreach ($terms as $term) {
            if(in_array($term->slug, $category)) {
                $category_products[$cart_item_key] = $values;
            }
        }
    }

    return $category_products;
}

function joints_woo_remove_shipping_price($label, $method) {
    $label = $method->get_label();

    return $label;
}
add_filter('woocommerce_cart_shipping_method_full_label', 'joints_woo_remove_shipping_price', 600, 2);
