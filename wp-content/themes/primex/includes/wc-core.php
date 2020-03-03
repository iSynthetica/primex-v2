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


// Our hooked in function - $fields is passed via the filter!

function snth_wc_checkout_fields($fields) {
    $disabled_billing_fields = array(
        'billing_company',
        'billing_postcode',
        'billing_address_2',
    );

    foreach ($fields["billing"] as $key => $field) {
        if (in_array($key, $disabled_billing_fields)) {
            unset ($fields["billing"][$key]);
        } else {
            if ('billing_first_name' === $key) {
                $fields["billing"][$key]['priority'] = 10;
            }
            if ('billing_last_name' === $key) {
                $fields["billing"][$key]['priority'] = 20;
            }
            if ('billing_phone' === $key) {
                $fields["billing"][$key]['priority'] = 30;
                $fields["billing"][$key]['class'] = array('form-row-first');
            }
            if ('billing_email' === $key) {
                $fields["billing"][$key]['priority'] = 40;
                $fields["billing"][$key]['class'] = array('form-row-last');
            }
            if ('billing_country' === $key) {
                $fields["billing"][$key]['priority'] = 60;
            }
            if ('billing_state' === $key) {
                $fields["billing"][$key]['priority'] = 70;
            }
            if ('billing_city' === $key) {
                $fields["billing"][$key]['priority'] = 80;
            }
            if ('billing_address_1' === $key) {
                $fields["billing"][$key]['priority'] = 90;
            }
        }
    }

    $disabled_shipping_fields = array(
        'shipping_company',
        'shipping_postcode',
        'shipping_address_2',
    );

    foreach ($fields["shipping"] as $key => $field) {
        if (in_array($key, $disabled_shipping_fields)) {
            unset ($fields["shipping"][$key]);
        } else {
            if ('shipping_first_name' === $key) {
                $fields["shipping"][$key]['priority'] = 10;
            }
            if ('shipping_last_name' === $key) {
                $fields["shipping"][$key]['priority'] = 20;
            }
            if ('shipping_country' === $key) {
                $fields["shipping"][$key]['priority'] = 30;
            }
            if ('shipping_state' === $key) {
                $fields["shipping"][$key]['priority'] = 40;
            }
            if ('shipping_city' === $key) {
                $fields["shipping"][$key]['priority'] = 50;
            }
            if ('shipping_address_1' === $key) {
                $fields["shipping"][$key]['priority'] = 60;
            }
        }
    }

    return $fields;
}

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

function snth_wc_coupon_fields( $fields ) {
    foreach ($fields as $key => $field) {
        if (in_array($key, array('coupon_name', 'coupon_phone', 'coupon_email'))) {
            $fields[$key]['class'] = array('');

            $fields[$key]['input_class'][] = 'form-control';
        }
    }

    return $fields;
}

// Hook in
add_filter( 'wooaiocoupon_fields' , 'snth_wc_coupon_fields' );

function snth_wc_remove_class($field) {
    $field = str_replace('form-row', '', $field);
    return $field;
}

// Hook in
add_filter( 'woocommerce_form_field' , 'snth_wc_remove_class' );

function snth_wc_coupon_form_before_submit() {
    ?>
    <div class="">
    <?php
}
add_action('wooaiocoupon_form_before_submit', 'snth_wc_coupon_form_before_submit');
add_action('wooaiocoupon_form_before_message', 'snth_wc_coupon_form_before_submit');

function snth_wc_form_after_submit() {
    ?>
    </div>
    <?php
}
add_action('wooaiocoupon_form_after_submit', 'snth_wc_form_after_submit');
add_action('wooaiocoupon_form_after_message', 'snth_wc_form_after_submit');

function snth_wc_wooaiocoupon_form_submit_class($class) {

    return $class . ' btn-block button-reveal';
}

// Hook in
add_filter( 'wooaiocoupon_form_submit_class' , 'snth_wc_wooaiocoupon_form_submit_class' );

function snth_wc_wooaiocoupon_form_submit_text($text) {

    return '<i class="fas fa-percent"></i> <span>'.__('Get coupon', 'primex').'</span>';
}

// Hook in
add_filter( 'wooaiocoupon_form_submit_text' , 'snth_wc_wooaiocoupon_form_submit_text' );

function snth_wc_payment_gateways($gateways) {
    $array = array(
            // 'WC_Gateway_BACS',
            'WC_Gateway_Cheque',
            //'WC_Gateway_Paypal',
    );

    foreach ($gateways as $i => $gateway) {
        if (in_array($gateway, $array)) {
            unset($gateways[$i]);
        }
    }

    return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'snth_wc_payment_gateways', 1000 );