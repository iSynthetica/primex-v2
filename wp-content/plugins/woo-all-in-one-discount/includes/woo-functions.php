<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wooaiodiscount_product_discount_price($product_price, $product, $rule_type = 'base_discount') {
    global $wooaiodiscount_current_user_rule;
    global $wooaiodiscount_current_user_rule_products;
    global $wooaiodiscount_product_rules;
    global $wooaiodiscount_current_discount_rule_id;

    if (function_exists('run_woo_all_in_one_currency')) {
        wooaiocurrency_reset_currency_rules();

        $base_discount_id = !empty($wooaiodiscount_current_user_rule["base_discount"]["discount_id"]) ? $wooaiodiscount_current_user_rule["base_discount"]["discount_id"] : false;
        $before_discount_id = !empty($wooaiodiscount_current_user_rule["before_discount"]["discount_id"]) ? $wooaiodiscount_current_user_rule["before_discount"]["discount_id"] : false;

        if (!empty($base_discount_id)) {
            $base_discount_currency = !empty($wooaiodiscount_product_rules[$base_discount_id]["currency"]) ? $wooaiodiscount_product_rules[$base_discount_id]["currency"] : false;
        }

        if (!empty($before_discount_id)) {
            $before_discount_currency = !empty($wooaiodiscount_product_rules[$before_discount_id]["currency"]) ? $wooaiodiscount_product_rules[$before_discount_id]["currency"] : false;
        }
    }

    if (empty($wooaiodiscount_current_user_rule_products)) {
        $wooaiodiscount_current_user_rule_products = array();
    }

    $discount = 0;
    $before_discount = 0;

    $all_products_discount = null;
    $category_discount = null;
    $product_discount = null;
    $discount_type = 'extra_charge';

    $all_products_before_discount = null;
    $category_before_discount = null;
    $product_before_discount = null;
    $before_discount_type = 'extra_charge';

    $product_type = $product->get_type();

    if ('variation' === $product_type) {
        $_product = wc_get_product( $product->get_parent_id() );
        $saved_product_id = $product->get_id();
        $product_id = $_product->get_id();
    } else {
        $product_id = $product->get_id();
        $saved_product_id = $product_id;
    }

    if (!empty($wooaiodiscount_current_user_rule_products[$saved_product_id])) {
        $product_prices = $wooaiodiscount_current_user_rule_products[$saved_product_id];

        if ($rule_type === 'base_discount') {
            if (!empty($product_prices['discount_price'])) {
                return $product_prices['discount_price'];
            }
        } else {
            if (!empty($product_prices['before_discount_price'])) {
                return $product_prices['before_discount_price'];
            }
        }
    } else {
        $product_prices = array(
            'discount_price' => '',
            'before_discount_price' => '',
            'discount_amount' => '',
            'discount_percent' => '',
        );
    }

    $rule = $wooaiodiscount_current_user_rule;
    $product_cats_ids = wc_get_product_term_ids( $product_id, 'product_cat' );

    // Calculate discount
    if (!empty($rule['base_discount']['discount'])) {
        $discount_type = $rule['base_discount']["type"];

        foreach ($rule['base_discount']['discount'] as $discount_rule) {
            if ($discount_rule['apply'] === 'all_products') {
                $all_products_discount = $discount_rule['amount'];
            } elseif ($discount_rule['apply'] === 'by_categories') {

            } elseif ($discount_rule['apply'] === 'separate_products') {
                if (in_array($product_id, $discount_rule['products'])) {
                    $product_discount = $discount_rule['amount'];
                }
            }
        }
    }

    if (null !==  $all_products_discount) {
        $discount = $all_products_discount;
    }

    if (null !==  $category_discount) {
        $discount = $category_discount;
    }

    if (null !==  $product_discount) {
        $discount = $product_discount;
    }

    if ('extra_charge' === $discount_type) {
        $product_discount_price = $product_price + ($product_price * ($discount / 100));
    } else {
        $product_discount_price = $product_price - ($product_price * ($discount / 100));
    }

    if (function_exists('run_woo_all_in_one_currency')) {
        global $wooaiocurrency_rules;
        $temp_currency_rule = $wooaiocurrency_rules;

        if (!empty($base_discount_currency)) {
            $current_currency_code = $wooaiocurrency_rules['current_currency_code'];

            if (!empty($base_discount_currency[$current_currency_code])) {
                $wooaiocurrency_rules['current_currency_rule']['rates'] = $base_discount_currency[$current_currency_code]['rates'];
            }
        }

        $product_discount_price = wooaiocurrency_price($product_discount_price, $product);
        $wooaiocurrency_rules = $temp_currency_rule;
    }

    $product_discount_price = ceil($product_discount_price);

    $product_prices['discount_price'] = $product_discount_price;

    // Calculate before discount
    if (!empty($rule['before_discount']['discount'])) {
        $before_discount_type = $rule['before_discount']["type"];

        foreach ($rule['before_discount']['discount'] as $discount_rule) {
            if ($discount_rule['apply'] === 'all_products') {
                $all_products_before_discount = $discount_rule['amount'];
            } elseif ($discount_rule['apply'] === 'by_categories') {

            } elseif ($discount_rule['apply'] === 'separate_products') {
                if (in_array($product_id, $discount_rule['products'])) {
                    $product_before_discount = $discount_rule['amount'];
                }
            }
        }
    }

    if (null !==  $all_products_before_discount) {
        $before_discount = $all_products_before_discount;
    }

    if (null !==  $category_before_discount) {
        $before_discount = $category_before_discount;
    }

    if (null !==  $product_before_discount) {
        $before_discount = $product_before_discount;
    }

    if ('extra_charge' === $before_discount_type) {
        $product_before_discount_price = $product_price + ($product_price * ($before_discount / 100));
    } else {
        $product_before_discount_price = $product_price - ($product_price * ($before_discount / 100));
    }

    if (function_exists('run_woo_all_in_one_currency')) {
        global $wooaiocurrency_rules;
        $temp_currency_rule = $wooaiocurrency_rules;

        if (!empty($before_discount_currency)) {
            $current_currency_code = $wooaiocurrency_rules['current_currency_code'];

            if (!empty($before_discount_currency[$current_currency_code])) {
                $wooaiocurrency_rules['current_currency_rule']['rates'] = $before_discount_currency[$current_currency_code]['rates'];
            }
        }

        $product_before_discount_price = wooaiocurrency_price($product_before_discount_price, $product);
        $wooaiocurrency_rules = $temp_currency_rule;
    }

    $product_before_discount_price = ceil($product_before_discount_price);

    $product_prices['before_discount_price'] = $product_before_discount_price;

    $discount_amount = $product_before_discount_price - $product_discount_price;

    if (0 > $discount_amount) { // extra charge
        $discount_amount = -1 * $discount_amount;
        $discount_percent = ($discount_amount / $product_discount_price) * 100;
    } else { // discount
        $discount_percent = ($discount_amount / $product_before_discount_price) * 100;
    }

    $product_prices['discount_amount'] = $discount_amount;
    $product_prices['discount_percent'] = ceil($discount_percent);

    $wooaiodiscount_current_user_rule_products[$saved_product_id] = $product_prices;

    if ('base_discount' === $rule_type) {
        $product_price = $product_discount_price;
    } else {
        $product_price = $product_before_discount_price;
    }

    return $product_price;
}

// TODO - Maybe remve after testing
function wooaiodiscount_variation_prices($prices, $product) {
    foreach ($prices as $price_type => $prices_amounts) {
        foreach ($prices_amounts as $product_id => $price) {
            $product = wc_get_product($product_id);
            $prices[$price_type][$product_id] = wooaiodiscount_product_discount_price($price, $product);
        }
    }

    return $prices;
}

function wooaiodiscount_set_discount_rules() {
    add_filter('woocommerce_product_get_regular_price', 'wooaiodiscount_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_get_sale_price', 'wooaiodiscount_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_get_price', 'wooaiodiscount_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_price', 'wooaiodiscount_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_regular_price', 'wooaiodiscount_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_sale_price', 'wooaiodiscount_product_variation_get_price', 1000, 2 );
    // add_filter('woocommerce_variation_prices', 'wooaiodiscount_variation_prices', 1000, 2 );

    add_filter('woocommerce_variation_prices_price', 'wooaiodiscount_variation_prices_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_regular_price', 'wooaiodiscount_variation_prices_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_sale_price', 'wooaiodiscount_variation_prices_price', 1000, 3 );
}

function wooaiodiscount_product_get_price($price, $product) {
    if (!$price) {
        return $price;
    }

    return wooaiodiscount_product_discount_price($price, $product);
}

function wooaiodiscount_product_variation_get_price($price, $product) {
    if (!$price) {
        return $price;
    }

    return wooaiodiscount_product_discount_price($price, $product);
}

function wooaiodiscount_variation_prices_price($price, $variation, $product) {
    if (!$price) {
        return $price;
    }

    wc_delete_product_transients($variation->get_id());

    return wooaiodiscount_product_discount_price($price, $variation);
}

function wooaiodiscount_reset_discount_rules() {
    remove_filter('woocommerce_product_get_regular_price', 'wooaiodiscount_product_get_price', 1000 );
    remove_filter('woocommerce_product_get_sale_price', 'wooaiodiscount_product_get_price', 1000 );
    remove_filter('woocommerce_product_get_price', 'wooaiodiscount_product_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_price', 'wooaiodiscount_product_variation_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_regular_price', 'wooaiodiscount_product_variation_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_sale_price', 'wooaiodiscount_product_variation_get_price', 1000 );
    // remove_filter('woocommerce_variation_prices', 'wooaiodiscount_variation_prices', 1000 );

    remove_filter('woocommerce_variation_prices_price', 'wooaiodiscount_variation_prices_price', 1000 );
    remove_filter('woocommerce_variation_prices_regular_price', 'wooaiodiscount_variation_prices_price', 1000 );
    remove_filter('woocommerce_variation_prices_sale_price', 'wooaiodiscount_variation_prices_price', 1000 );
}

/**
 * Set prices before discount
 */
function wooaiodiscount_set_before_discount_rules() {
    add_filter('woocommerce_product_get_regular_price', 'wooaiodiscount_product_get_before_discount_price', 1000, 2 );
    add_filter('woocommerce_product_get_sale_price', 'wooaiodiscount_product_get_before_discount_price', 1000, 2 );
    add_filter('woocommerce_product_get_price', 'wooaiodiscount_product_get_before_discount_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_price', 'wooaiodiscount_product_variation_get_before_discount_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_regular_price', 'wooaiodiscount_product_variation_get_before_discount_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_sale_price', 'wooaiodiscount_product_variation_get_before_discount_price', 1000, 2 );
    // add_filter('woocommerce_variation_prices', 'wooaiodiscount_variation_prices', 1000, 2 );

    add_filter('woocommerce_variation_prices_price', 'wooaiodiscount_variation_prices_before_discount_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_regular_price', 'wooaiodiscount_variation_prices_before_discount_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_sale_price', 'wooaiodiscount_variation_prices_before_discount_price', 1000, 3 );
}

function wooaiodiscount_product_get_before_discount_price($price, $product) {
    if (!$price) {
        return $price;
    }

    return wooaiodiscount_product_discount_price($price, $product, 'before_discount');
}

function wooaiodiscount_product_variation_get_before_discount_price($price, $product) {
    if (!$price) {
        return $price;
    }

    return wooaiodiscount_product_discount_price($price, $product, 'before_discount');
}

function wooaiodiscount_variation_prices_before_discount_price($price, $variation, $product) {
    if (!$price) {
        return $price;
    }

    wc_delete_product_transients($variation->get_id());

    return wooaiodiscount_product_discount_price($price, $variation, 'before_discount');
}

/**
 * Reset prices before discount
 */
function wooaiodiscount_reset_before_discount_rules() {
    remove_filter('woocommerce_product_get_regular_price', 'wooaiodiscount_product_get_before_discount_price', 1000 );
    remove_filter('woocommerce_product_get_sale_price', 'wooaiodiscount_product_get_before_discount_price', 1000 );
    remove_filter('woocommerce_product_get_price', 'wooaiodiscount_product_get_before_discount_price', 1000 );
    remove_filter('woocommerce_product_variation_get_price', 'wooaiodiscount_product_variation_get_before_discount_price', 1000 );
    remove_filter('woocommerce_product_variation_get_regular_price', 'wooaiodiscount_product_variation_get_before_discount_price', 1000 );
    remove_filter('woocommerce_product_variation_get_sale_price', 'wooaiodiscount_product_variation_get_before_discount_price', 1000 );
    // remove_filter('woocommerce_variation_prices', 'wooaiodiscount_variation_prices', 1000 );

    remove_filter('woocommerce_variation_prices_price', 'wooaiodiscount_variation_prices_before_discount_price', 1000 );
    remove_filter('woocommerce_variation_prices_regular_price', 'wooaiodiscount_variation_prices_before_discount_price', 1000 );
    remove_filter('woocommerce_variation_prices_sale_price', 'wooaiodiscount_variation_prices_before_discount_price', 1000 );
}






// Get simple product price HTML
function wooaiodiscount_simple_get_price_html( $product ) {
    if ( '' === $product->get_price() ) {
        $price = apply_filters( 'woocommerce_empty_price_html', '', $product );
    } elseif ( $product->is_on_sale() ) {
        $price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
    } else {
        $price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
    }

    return $price;
}

function wooaiodiscount_get_variation_prices( $product ) {
    $prices_array = array(
        'price'         => array(),
        'regular_price' => array(),
        'sale_price'    => array(),
    );

    $variation_ids = $product->get_visible_children();

    foreach ( $variation_ids as $variation_id ) {
        $variation = wc_get_product( $variation_id );

        if ( $variation ) {
            $price         = apply_filters( 'woocommerce_variation_prices_price', $variation->get_price( 'edit' ), $variation, $product );
            $regular_price = apply_filters( 'woocommerce_variation_prices_regular_price', $variation->get_regular_price( 'edit' ), $variation, $product );
            $sale_price    = apply_filters( 'woocommerce_variation_prices_sale_price', $variation->get_sale_price( 'edit' ), $variation, $product );

            // Skip empty prices.
            if ( '' === $price ) {
                continue;
            }

            // If sale price does not equal price, the product is not yet on sale.
            if ( $sale_price === $regular_price || $sale_price !== $price ) {
                $sale_price = $regular_price;
            }

            // If we are getting prices for display, we need to account for taxes.
            if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
                $price         = '' === $price ? '' : wc_get_price_including_tax(
                    $variation,
                    array(
                        'qty'   => 1,
                        'price' => $price,
                    )
                );
                $regular_price = '' === $regular_price ? '' : wc_get_price_including_tax(
                    $variation,
                    array(
                        'qty'   => 1,
                        'price' => $regular_price,
                    )
                );
                $sale_price    = '' === $sale_price ? '' : wc_get_price_including_tax(
                    $variation,
                    array(
                        'qty'   => 1,
                        'price' => $sale_price,
                    )
                );
            } else {
                $price         = '' === $price ? '' : wc_get_price_excluding_tax(
                    $variation,
                    array(
                        'qty'   => 1,
                        'price' => $price,
                    )
                );
                $regular_price = '' === $regular_price ? '' : wc_get_price_excluding_tax(
                    $variation,
                    array(
                        'qty'   => 1,
                        'price' => $regular_price,
                    )
                );
                $sale_price    = '' === $sale_price ? '' : wc_get_price_excluding_tax(
                    $variation,
                    array(
                        'qty'   => 1,
                        'price' => $sale_price,
                    )
                );
            }

            $prices_array['price'][ $variation_id ]         = wc_format_decimal( $price, wc_get_price_decimals() );
            $prices_array['regular_price'][ $variation_id ] = wc_format_decimal( $regular_price, wc_get_price_decimals() );
            $prices_array['sale_price'][ $variation_id ]    = wc_format_decimal( $sale_price, wc_get_price_decimals() );
        }
    }

    foreach ( $prices_array as $price_key => $variation_prices ) {
        asort( $variation_prices );
        $prices_array[ $price_key ] = $variation_prices;
    }

    return $prices_array;
}

function wooaiodiscount_variable_get_price_html( $product, $discounted = true ) {
    $price = '';

    if ($discounted) {
        $prices = $product->get_variation_prices( true );
    } else {
        $prices = wooaiodiscount_get_variation_prices( $product );
    }

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
            $price = wc_format_sale_price( wc_price( $max_reg_price ), wc_price( $min_price ) );
        } else {
            $price = wc_price( $min_price );
        }

        $price = $price . $product->get_price_suffix();
    }

    return $price;
}

function wooaiodiscount_grouped_get_price_html( $product, $price = '' ) {
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

    return $price;
}

function wooaiodiscount_get_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;
    $product_type = $product->get_type();
    $label = "";

    if (!empty($wooaiodiscount_current_user_rule["base_discount"]["discount_label"])) {
        $label = $wooaiodiscount_current_user_rule["base_discount"]["discount_label"] . " ";
    }

    $label = apply_filters('wooaiodiscount_base_discount_label', $label);

    if ('grouped' === $product_type) {
        $basic_price = $label . wooaiodiscount_grouped_get_price_html( $product );
    } elseif ('variable' === $product_type) {
        $basic_price = $label .wooaiodiscount_variable_get_price_html( $product );
    } else {
        $basic_price = $label . wooaiodiscount_simple_get_price_html( $product );
    }

    return $basic_price;
}

function wooaiodiscount_get_before_discount_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;
    $product_type = $product->get_type();
    $label = "";

    if (!empty($wooaiodiscount_current_user_rule["before_discount"]["discount_label"])) {
        $label = $wooaiodiscount_current_user_rule["before_discount"]["discount_label"] . " ";
    }

    $label = apply_filters('wooaiodiscount_before_discount_label', $label);

    wooaiodiscount_reset_discount_rules();
    wooaiodiscount_set_before_discount_rules();

    if ('grouped' === $product_type) {
        $after_price = "<br> <small>" . $label . wooaiodiscount_grouped_get_price_html( $product ) . '</small>';
    } elseif ('variable' === $product_type) {
        $after_price = "<br> <small>" . $label . wooaiodiscount_variable_get_price_html( $product ) . '</small>';
    } else {
        $after_price = "<br> <small>" . $label . wooaiodiscount_simple_get_price_html( $product ) . '</small>';
    }

    wooaiodiscount_reset_before_discount_rules();
    wooaiodiscount_set_discount_rules();

    return $price . $after_price;
}

function wooaiodiscount_get_after_price_html($product) {
    $product_type = $product->get_type();

    if ('simple' === $product_type) {
        return "<br>" . wooaiodiscount_simple_get_price_html( $product );
    }

    return "";
}

// $price_hash, $product, $for_display

function wooaiodiscount_get_variation_prices_hash($price_hash, $product, $for_display) {
    $current_user_hash = get_transient('wooaiodiscount_current_user_rule_hash');
    $price_hash['wooaiodiscount_current_user_rule_hash'] = $current_user_hash;
    return $price_hash;
}
add_filter('woocommerce_get_variation_prices_hash', 'wooaiodiscount_get_variation_prices_hash', 1000, 3);