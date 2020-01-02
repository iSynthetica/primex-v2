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

function snth_wc_service_fields( $fields ) {
    foreach ($fields as $key => $field) {
        if (in_array($key, array('repair_name', 'repair_phone', 'repair_email', 'repair_created_date', 'repair_order_date', 'repair_np_ttn'))) {
            $fields[$key]['class'] = array('col-12', 'col-md-6', 'col-lg-4');

            $fields[$key]['input_class'][] = 'form-control';
        }

        if (in_array($key, array('repair_product', 'repair_serial', 'repair_set', 'repair_fault'))) {
            $fields[$key]['class'] = array('col-12', 'col-md-6');

            $fields[$key]['input_class'][] = 'form-control';
        }

        if (in_array($key, array('repair_state'))) {
            $fields[$key]['class'] = array('col-12');

            $fields[$key]['input_class'][] = 'form-control';
        }
    }

    return $fields;
}

// Hook in
add_filter( 'wooaioservice_fields' , 'snth_wc_service_fields' );

function snth_wc_remove_class($field) {
    $field = str_replace('form-row', '', $field);
    return $field;
}

// Hook in
add_filter( 'woocommerce_form_field' , 'snth_wc_remove_class' );

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