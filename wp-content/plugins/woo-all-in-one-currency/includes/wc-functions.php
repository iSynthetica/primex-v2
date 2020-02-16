<?php

function wooaiodiscount_set_currency_rules() {
    add_filter('woocommerce_product_get_regular_price', 'wooaiocurrency_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_get_sale_price', 'wooaiocurrency_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_get_price', 'wooaiocurrency_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_regular_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_sale_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
    // add_filter('woocommerce_variation_prices', 'wooaiodiscount_variation_prices', 1000, 2 );



    add_filter('woocommerce_variation_prices_price', 'wooaiocurrency_variation_prices_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_regular_price', 'wooaiocurrency_variation_prices_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_sale_price', 'wooaiocurrency_variation_prices_price', 1000, 3 );
}

function wooaiodiscount_reset_currency_rules() {
    remove_filter('woocommerce_product_get_regular_price', 'wooaiocurrency_product_get_price', 1000 );
    remove_filter('woocommerce_product_get_sale_price', 'wooaiocurrency_product_get_price', 1000 );
    remove_filter('woocommerce_product_get_price', 'wooaiocurrency_product_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_price', 'wooaiocurrency_product_variation_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_regular_price', 'wooaiocurrency_product_variation_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_sale_price', 'wooaiocurrency_product_variation_get_price', 1000 );
    // remove_filter('woocommerce_variation_prices', 'wooaiodiscount_variation_prices', 1000 );



    remove_filter('woocommerce_variation_prices_price', 'wooaiocurrency_variation_prices_price', 1000 );
    remove_filter('woocommerce_variation_prices_regular_price', 'wooaiocurrency_variation_prices_price', 1000 );
    remove_filter('woocommerce_variation_prices_sale_price', 'wooaiocurrency_variation_prices_price', 1000 );
}

function wooaiocurrency_price($price, $product) {
    global $wooaiocurrency_rules;
    $rate = 1;
    $all_product_rate = null;
    $specified_categories_rate = null;
    $product_type = $product->get_type();

    if ('variation' === $product_type) {
        $_product = wc_get_product( $product->get_parent_id() );
        $product_id = $_product->get_id();
    } else {
        $product_id = $product->get_id();
    }

    if (!empty($wooaiocurrency_rules["current_currency_rule"]["rates"])) {
        foreach ($wooaiocurrency_rules["current_currency_rule"]["rates"] as $rate_rule) {
            if ($rate_rule['apply'] === 'all_products') {
                $all_product_rate = $rate_rule['rate'];
            }

            if ($rate_rule['apply'] === 'specified_categories') {
                $product_cats_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
                $specified_categories_rate = $rate_rule['rate'];
            }
        }
    }

    if (null !== $all_product_rate) {
        $rate = (float)$all_product_rate;
    }

    return $price * $rate;
}

function wooaiocurrency_currency($currency) {
    if (is_admin()) {
        return $currency;
    }

    $currency_rules = Woo_All_In_One_Currency_Rules::get_all();

    if (count($currency_rules) < 2) {
        return $currency;
    }

    return wooaiocurrency_get_current_currency();
}

function wooaiocurrency_product_get_price( $price, $product ) {
    if ( ! $price ) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_product_variation_get_price( $price, $product ) {
    if ( ! $price ) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_variation_prices_price($price, $product) {
    if (!$price) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_variation_prices( $prices ) {
    foreach ($prices as $price_type => $prices_amounts) {
        foreach ($prices_amounts as $product_id => $price) {
            $product = wc_get_product($product_id);
            $prices[$price_type][$product_id] = wooaiocurrency_price($price, $product);
        }
    }

    return $prices;
}