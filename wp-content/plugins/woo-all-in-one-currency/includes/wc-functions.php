<?php

function wooaiocurrency_price($price, $product) {
    global $wooaiocurrency_rules;
    $rate = 1;
    $all_product_rate = null;

    if (!empty($wooaiocurrency_rules["current_currency_rule"]["rates"])) {
        foreach ($wooaiocurrency_rules["current_currency_rule"]["rates"] as $rate_rule) {
            if ($rate_rule['apply'] === 'all_products') {
                $all_product_rate = $rate_rule['rate'];
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

function wooaiocurrency_product_get_regular_price( $price, $product ) {
    if ( ! $price ) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_product_get_sale_price( $price, $product ) {
    if ( ! $price ) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_product_get_price( $price, $product ) {
    if ( ! $price ) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_product_variation_get_regular_price( $price, $product ) {
    if ( ! $price ) {
        return $price;
    }

    return wooaiocurrency_price($price, $product);
}

function wooaiocurrency_product_variation_get_sale_price( $price, $product ) {
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

function wooaiocurrency_variation_prices( $prices ) {
//    if ( ! $price ) {
//        return $price;
//    }

    foreach ($prices as $price_type => $prices_amounts) {
        foreach ($prices_amounts as $product_id => $price) {
            $product = wc_get_product($product_id);
            $prices[$price_type][$product_id] = wooaiocurrency_price($price, $product);
        }
    }

    return $prices;
}