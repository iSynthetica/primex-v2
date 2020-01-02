<?php
/**
 * Add Woocommerce support to theme
 */

// Update Cart Count After AJAX
add_filter( 'woocommerce_add_to_cart_fragments', 'snth_wc_cart_count_fragments', 10, 1 );

function snth_wc_cart_count_fragments( $fragments ) {
    $fragments['#top-cart'] = do_shortcode('[snth_cart_icon]');
    return $fragments;
}

// Hook in
add_filter( 'woocommerce_checkout_fields' , 'snth_wc_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function snth_wc_checkout_fields( $fields ) {
    // Billing
    $allowed_billing_fields = array(
        'billing_first_name',
        'billing_last_name',
        'billing_phone',
        'billing_email',
    );

    foreach ($fields["billing"] as $field_name => $field_value) {
        if (!in_array($field_name, $allowed_billing_fields)) {
            unset ($fields["billing"][$field_name]);
        }
    }

    if (!empty($fields["billing"]["billing_phone"])) {
        $class = !empty($fields["billing"]["billing_phone"]['class']) ? $fields["billing"]["billing_phone"]['class'] : array();

        $change_class = true;

        foreach ($class as $ci => $cv) {
            if ('form-row-wide' === $cv) {
                $class[$ci] = 'form-row-first';
                $change_class = false;
            }
        }

        if ($change_class) {
            $class[] = 'form-row-first';
        }

        $fields["billing"]["billing_phone"]['class'] = $class;
    }

    if (!empty($fields["billing"]["billing_email"])) {
        $class = !empty($fields["billing"]["billing_email"]['class']) ? $fields["billing"]["billing_email"]['class'] : array();

        $change_class = true;

        foreach ($class as $ci => $cv) {
            if ('form-row-wide' === $cv) {
                $class[$ci] = 'form-row-last';
                $change_class = false;
            }
        }

        if ($change_class) {
            $class[] = 'form-row-last';
        }

        $fields["billing"]["billing_email"]['class'] = $class;
    }

    // Shipping
    $allowed_shipping_fields = array(
        'shipping_first_name',
        'shipping_last_name',
        'shipping_phone',
        'shipping_email',
    );

    foreach ($fields["shipping"] as $field_name => $field_value) {
        if (!in_array($field_name, $allowed_shipping_fields)) {
            unset ($fields["shipping"][$field_name]);
        }
    }

    return $fields;
}