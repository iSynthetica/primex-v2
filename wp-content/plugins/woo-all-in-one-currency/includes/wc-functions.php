<?php

function wooaiocurrency_price($price, $product) {
    global $wooaiocurrency_rules;
    global $wooaiodiscount_current_discount_rule_id;

    if (!$wooaiocurrency_rules) {
        $wooaiocurrency_rules = wooaiocurrency_set_global_currency_rule( true );
    }

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

    $product_price = $price * $rate;

    return $product_price;
}

function wooaiocurrency_set_currency_rules() {
    add_filter('woocommerce_product_get_regular_price', 'wooaiocurrency_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_get_sale_price', 'wooaiocurrency_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_get_price', 'wooaiocurrency_product_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_regular_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_product_variation_get_sale_price', 'wooaiocurrency_product_variation_get_price', 1000, 2 );
    add_filter('woocommerce_variation_prices_price', 'wooaiocurrency_variation_prices_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_regular_price', 'wooaiocurrency_variation_prices_price', 1000, 3 );
    add_filter('woocommerce_variation_prices_sale_price', 'wooaiocurrency_variation_prices_price', 1000, 3 );
}

function wooaiocurrency_reset_currency_rules() {
    remove_filter('woocommerce_product_get_regular_price', 'wooaiocurrency_product_get_price', 1000 );
    remove_filter('woocommerce_product_get_sale_price', 'wooaiocurrency_product_get_price', 1000 );
    remove_filter('woocommerce_product_get_price', 'wooaiocurrency_product_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_price', 'wooaiocurrency_product_variation_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_regular_price', 'wooaiocurrency_product_variation_get_price', 1000 );
    remove_filter('woocommerce_product_variation_get_sale_price', 'wooaiocurrency_product_variation_get_price', 1000 );
    remove_filter('woocommerce_variation_prices_price', 'wooaiocurrency_variation_prices_price', 1000 );
    remove_filter('woocommerce_variation_prices_regular_price', 'wooaiocurrency_variation_prices_price', 1000 );
    remove_filter('woocommerce_variation_prices_sale_price', 'wooaiocurrency_variation_prices_price', 1000 );
}

function wooaiocurrency_currency($currency) {

    $do_load = false;

    if (!is_admin()) {
        $do_load = true;
    }

    $do_load = apply_filters('wooaiocurrency_load_global_currency_rule', $do_load);

    if (!$do_load) {
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

function wooaiocurrency_variation_prices_price($price, $variation, $product) {
    if (!$price) {
        return $price;
    }

    wc_delete_product_transients($variation->get_id());

    return wooaiocurrency_price($price, $variation);
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

function wooaiocurrency_set_global_currency_rule($return = false) {
    $do_load = false;

    if (!is_admin()) {
        $do_load = true;
    }

    $do_load = apply_filters('wooaiocurrency_load_global_currency_rule', $do_load);

    if (!$do_load) {
        return;
    }

    global $wooaiocurrency_rules;
    $general_multicurrency_settings = Woo_All_In_One_Currency_Rules::get_general_currency_settings();

    $wooaiocurrency_rules = array();
    $base_currency = get_option( 'woocommerce_currency' );
    $main_currency = $base_currency;

    $currency_rules = Woo_All_In_One_Currency_Rules::get_all();

    foreach ($currency_rules as $currency_code => $currency_rule) {
        if ($currency_code !== $base_currency && empty($currency_rule['rates'])) {
            unset($currency_rules[$currency_code]);
        }

        if (!empty($currency_rule['main'])) {
            $main_currency = $currency_code;
        }
    }

    if (count($currency_rules) < 2) {
        $wooaiocurrency_rules['current_currency_code'] = $base_currency;
        $wooaiocurrency_rules['current_currency_rule'] = $currency_rules[$base_currency];
        $wooaiocurrency_rules['switcher'] = array();
    } else {
        if ( $general_multicurrency_settings["multicurrency_allow"] === 'no' && is_cart_or_checkout() ) {
            $current_currency = $main_currency;
        } elseif (!empty($_COOKIE['wooaiocurrency'])) {
            $current_currency = $_COOKIE['wooaiocurrency'];

            if (empty($currency_rules[$current_currency])) {
                $current_currency = false;

                setcookie('wooaiocurrency_update_minicart', 1, time() + (86400 * 360), '/');
                unset($_COOKIE['wooaiocurrency']);
                $res = setcookie('wooaiocurrency', '', time() - 3600);
            }
        }

        if (empty($current_currency)) {
            foreach ($currency_rules as $currency_code => $currency_rule) {
                if (!empty($currency_rule['main'])) {
                    $current_currency =  $currency_code;
                }
            }
        }

        if (empty($current_currency)) {
            $current_currency =  $base_currency;
        }

        $wooaiocurrency_rules['current_currency_code'] = $current_currency;
        $wooaiocurrency_rules['current_currency_rule'] = $currency_rules[$current_currency];
        $switcher_rules = $currency_rules;

        unset($switcher_rules[$current_currency]);

        $wooaiocurrency_rules['switcher'] = $switcher_rules;
    }

    set_transient('wooaiodiscount_current_currency_hash', md5( wp_json_encode( $wooaiocurrency_rules ) ) );

    if ($return) {
        return $wooaiocurrency_rules;
    }
}

function is_cart_or_checkout() {
    if (is_cart() || is_checkout()) {
        return true;
    }

    if (isset($_REQUEST['wc-ajax']) && sanitize_text_field($_REQUEST['wc-ajax']) == 'update_order_review') {
        return true;
    }

    $current_url = @$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
    if ( $_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443' ) {
        $current_url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
    } else {
        $current_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    $root = isset( $_SERVER['PHP_SELF'] ) ? $_SERVER['PHP_SELF'] : '';
    if ( $root ) {
        $root = str_replace( '/index.php', '', $root );
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            $path = str_replace( $root, '', $_SERVER['REQUEST_URI'] );
            // Retrieve the current post's ID based on its URL

            $id = get_page_by_path( $path );
            if ( is_object( $id ) ) {
                $id = $id->ID;
            } else {
                $id = url_to_postid( $current_url );;
            }
        } else {
            // Retrieve the current post's ID based on its URL
            $id = url_to_postid( $current_url );
        }

    } else {
        // Retrieve the current post's ID based on its URL
        $id = url_to_postid( $current_url );
    }

    $checkout_page_id = wc_get_page_id( 'checkout' );
    $cart_page_id = wc_get_page_id( 'cart' );

    return $id == $checkout_page_id || $id == $cart_page_id;
}

function wooaiocurrency_get_variation_prices_hash($price_hash, $product, $for_display) {
    $current_user_hash = get_transient('wooaiodiscount_current_currency_hash');
    $price_hash['wooaiodiscount_current_currency_hash'] = $current_user_hash;
    return $price_hash;
}

add_filter('woocommerce_get_variation_prices_hash', 'wooaiocurrency_get_variation_prices_hash', 1000, 3);