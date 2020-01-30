<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wooaiodiscount_get_before_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;

    if (empty($wooaiodiscount_current_user_rule)) {
        return $price;
    }

    $before_discount_price = '';

    if (!empty($wooaiodiscount_current_user_rule["base_discount"]["discount_label"])) {
        $before_discount_price = '<b class="woocommerce-Price-label label">' . $wooaiodiscount_current_user_rule["base_discount"]["discount_label"] . '</b> ';
    }

    return $before_discount_price . $price;
}

function wooaiodiscount_get_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;

    if (empty($wooaiodiscount_current_user_rule)) {
        return $price;
    }

    $product_type = $product->get_type();

    if ('variable' === $product_type) {
        return wooaiodiscount_get_variable_price_html($price, $product);
    } elseif ('grouped' === $product_type) {
        return wooaiodiscount_get_grouped_price_html($price, $product);
    } else {
        return wooaiodiscount_get_simple_price_html($price, $product);
    }

    return $price;
}

function wooaiodiscount_get_simple_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;
    //wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-product.php:1758
    $product_base_price = $product->get_price();

    $before_base_price = '';

    if (!empty($wooaiodiscount_current_user_rule["base_discount"]["price_label"])) {
        $before_base_price = '<b class="woocommerce-Price-label label">' . $wooaiodiscount_current_user_rule["base_discount"]["price_label"] . '</b> ';
    }

    if ( '' === $product_base_price ) {
        $price = apply_filters( 'woocommerce_empty_price_html', '', $product );
    } else {
        if ( $product->is_on_sale() ) {
            $product_regular_price = $product->get_regular_price();
            $product_rule_price = Woo_All_In_One_Discount_Rules::get_price($product_base_price, $product);
            $product_rule_regular_price = Woo_All_In_One_Discount_Rules::get_price($product_regular_price, $product);
            $price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product_rule_regular_price ) ), wc_get_price_to_display( $product, array( 'price' => $product_rule_price ) ) ) . $product->get_price_suffix();

            if (!empty($before_base_price)) {
                $price .= '<br>';
                $price .= '<small>' . $before_base_price . wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product_regular_price ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix() . '</small>';
            }
        } else {
            $product_rule_price = Woo_All_In_One_Discount_Rules::get_price($product_base_price, $product);

            if ((int)$product_base_price === (int)$product_rule_price) {
                return $price;
            }

            $price = wc_price( $product_rule_price ) . $product->get_price_suffix();

            if (!empty($before_base_price)) {
                $price .= '<br>';
                $price .= '<small>' . $before_base_price . wc_price( $product_base_price ) . $product->get_price_suffix() . '</small>';
            }
        }
    }

    return $price;
}

function wooaiodiscount_get_variable_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;
    $prices = $product->get_variation_prices( true );

    if ( empty( $prices['price'] ) ) {
        $price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
    } else {

        $before_base_price = '';

        if (!empty($wooaiodiscount_current_user_rule["base_discount"]["price_label"])) {
            $before_base_price = '<b class="woocommerce-Price-label label">' . $wooaiodiscount_current_user_rule["base_discount"]["price_label"] . '</b> ';
        }

        $min_base_price     = current( $prices['price'] );
        $max_base_price     = end( $prices['price'] );
        $min_reg_base_price = current( $prices['regular_price'] );
        $max_reg_base_price = end( $prices['regular_price'] );

        $min_base_rule_price     = Woo_All_In_One_Discount_Rules::get_price($min_base_price, $product);
        $max_base_rule_price     = Woo_All_In_One_Discount_Rules::get_price($max_base_price, $product);
        $min_reg_base_rule_price = Woo_All_In_One_Discount_Rules::get_price($min_reg_base_price, $product);
        $max_reg_base_rule_price = Woo_All_In_One_Discount_Rules::get_price($max_reg_base_price, $product);

        if ($min_base_rule_price !== $min_base_price || $max_base_rule_price !== $max_base_price) {
            if ( $min_base_rule_price !== $max_base_rule_price ) {
                $price = wc_format_price_range( $min_base_rule_price, $max_base_rule_price );

                if (!empty($before_base_price)) {
                    $price .= '<br>';
                    $price .= '<small>' . $before_base_price . wc_format_price_range( $min_base_price, $max_base_price ) . '</small>';
                }
            } elseif ( $product->is_on_sale() && $min_reg_base_price === $max_reg_base_price ) {
                $price = wc_format_sale_price( wc_price( $max_reg_base_price ), wc_price( $min_base_price ) );

                if (!empty($before_base_price)) {
                    $price .= '<br>';
                    $price .= '<small>' . $before_base_price . wc_format_price_range( $min_base_price, $max_base_price ) . '</small>';
                }
            } else {
                $price = wc_price( $min_base_price );
            }
        } else {
            if ( $min_base_price !== $max_base_price ) {
                $price = wc_format_price_range( $min_base_price, $max_base_price );
            } elseif ( $product->is_on_sale() && $min_reg_base_price === $max_reg_base_price ) {
                $price = wc_format_sale_price( wc_price( $max_reg_base_price ), wc_price( $min_base_price ) );
            } else {
                $price = wc_price( $min_base_price );
            }
        }


        $price = $price . $product->get_price_suffix();
    }

    return $price;
}

function wooaiodiscount_get_grouped_price_html($price, $product) {
    global $wooaiodiscount_current_user_rule;
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
        $min_rule_price     = Woo_All_In_One_Discount_Rules::get_price($min_price, $product);
        $max_rule_price     = Woo_All_In_One_Discount_Rules::get_price($max_price, $product);
    } else {
        $min_price = '';
        $max_price = '';
    }

    if ( '' !== $min_price ) {

        $before_base_price = '';

        if ( $min_rule_price !== $max_rule_price ) {
            $price = wc_format_price_range( $min_rule_price, $max_rule_price );
        } else {
            $price = wc_price( $min_rule_price );
        }

        if (!empty($wooaiodiscount_current_user_rule["base_discount"]["price_label"])) {
            if ( $min_rule_price !== $max_rule_price ) {
                $before_base_price = wc_format_price_range( $min_price, $max_price );
            } else {
                $before_base_price = wc_price( $min_rule_price );
            }
        }

        $is_free = 0 === $min_price && 0 === $max_price;

        if ( $is_free ) {
            $price = apply_filters( 'woocommerce_grouped_free_price_html', __( 'Free!', 'woocommerce' ), $product );
        } else {
            $price = apply_filters( 'woocommerce_grouped_price_html', $price . $product->get_price_suffix(), $product, $child_prices );

            if (!empty($before_base_price)) {
                $price .= '<br>';
                $price .= '<small>' . '<b class="woocommerce-Price-label label">' . $wooaiodiscount_current_user_rule["base_discount"]["price_label"] . '</b> ';
                $price .= $before_base_price . $product->get_price_suffix() . '</small>';
            }
        }
    } else {
        $price = apply_filters( 'woocommerce_grouped_empty_price_html', '', $product );
    }
    return $price;
}

function wooaiodiscount_get_discount_amount_html($price, $product) {
    global $wooaiodiscount_current_user_rule;
    $product_type = $product->get_type();

    if (empty($wooaiodiscount_current_user_rule)) {
        return $price;
    }

    $discount_amount = '';

    if (!empty($wooaiodiscount_current_user_rule["base_discount"]["discount_amount_label"])) {
        $discount_amount_label = $wooaiodiscount_current_user_rule["base_discount"]["discount_amount_label"];

        if ('grouped' !== $product_type) {
            $amount = Woo_All_In_One_Discount_Rules::get_discount_amount($product);
            if (!empty($amount)) {
                $discount_amount = '<br><small><b class="woocommerce-Price-label label">' . $discount_amount_label . '</b> '. $amount .'%</small> ';
            }
        } else {
            $children         = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
            $child_discount_amounts = array();

            foreach ( $children as $child ) {
                $amount = Woo_All_In_One_Discount_Rules::get_discount_amount($child);

                if (!empty($amount)) {
                    $child_discount_amounts[] = $amount;
                }
            }

            asort( $child_discount_amounts );

            if (!empty($child_discount_amounts)) {
                if (1 === count($child_discount_amounts)) {
                    $discount_amount = '<br><small><b class="woocommerce-Price-label label">' . $discount_amount_label . '</b> '. $child_discount_amounts[0] .'%</small> ';
                } else {
                    $min_discount_amount     = current( $child_discount_amounts );
                    $max_discount_amount     = end( $child_discount_amounts );

                    if ($min_discount_amount === $max_discount_amount) {
                        $discount_amount = '<br><small><b class="woocommerce-Price-label label">' . $discount_amount_label . '</b> '. $min_discount_amount .'%</small> ';
                    } else {
                        $discount_amount = '<br><small><b class="woocommerce-Price-label label">' . $discount_amount_label . '</b> '. $min_discount_amount . ' – ' . $max_discount_amount .'%</small> ';
                    }
                }
            }
        }
    }

    return $price . $discount_amount;
}