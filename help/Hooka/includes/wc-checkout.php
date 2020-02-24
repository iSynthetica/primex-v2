<?php
/**
 * Woocommerce Checkout Functions
 *
 * @package Hookah/Includes/WC
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove Payment Gateways
 *
 * @param $gateways
 *
 * @return mixed
 */
function snth_wc_remove_payment_gateways( $gateways )
{
    $remove_gateways = array(
        //'WC_Gateway_BACS',
        'WC_Gateway_Cheque',
        //'WC_Gateway_COD',
        'WC_Gateway_Paypal',
    );

    foreach ( $gateways as $key => $value ) {
        if ( in_array( $value, $remove_gateways ) ) {
            unset( $gateways[ $key ] );
        }
    }
    return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'snth_wc_remove_payment_gateways', 8, 1 );

/**
 * Override checkout fields
 *
 * @param $fields
 *
 * @return array
 */
function snth_wc_override_default_address_fields($fields)
{
    unset($fields['company']);
    // unset($fields['postcode']);
    // unset($fields['country']);
    unset($fields['address_2']);

    return $fields;
}
add_filter( 'woocommerce_default_address_fields' , 'snth_wc_override_default_address_fields' );

/**
 * Outputs a checkout/address form field.
 *
 * @param string $key Key.
 * @param mixed  $args Arguments.
 * @param string $value (default: null).
 * @return string
 */
function snth_wc_checkout_form_field( $key, $args, $value = null ) {
    $defaults = array(
        'type'              => 'text',
        'label'             => '',
        'description'       => '',
        'placeholder'       => '',
        'maxlength'         => false,
        'required'          => false,
        'autocomplete'      => false,
        'id'                => $key,
        'class'             => array(),
        'label_class'       => array(),
        'input_class'       => array(),
        'return'            => false,
        'options'           => array(),
        'custom_attributes' => array(),
        'validate'          => array(),
        'default'           => '',
        'autofocus'         => '',
        'priority'          => '',
    );

    $args = wp_parse_args( $args, $defaults );
    $args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );

    if ( $args['required'] ) {
        $args['class'][] = 'validate-required';
        $required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
    } else {
        $required = '';
    }

    if ( is_string( $args['label_class'] ) ) {
        $args['label_class'] = array( $args['label_class'] );
    }

    if ( is_null( $value ) ) {
        $value = $args['default'];
    }

    // Custom attribute handling.
    $custom_attributes         = array();
    $args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

    if ( $args['maxlength'] ) {
        $args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
    }

    if ( ! empty( $args['autocomplete'] ) ) {
        $args['custom_attributes']['autocomplete'] = $args['autocomplete'];
    }

    if ( true === $args['autofocus'] ) {
        $args['custom_attributes']['autofocus'] = 'autofocus';
    }

    if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
        foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
        }
    }

    if ( ! empty( $args['validate'] ) ) {
        foreach ( $args['validate'] as $validate ) {
            $args['class'][] = 'validate-' . $validate;
        }
    }

    $field           = '';
    $label_id        = $args['id'];
    $sort            = $args['priority'] ? $args['priority'] : '';
    $field_container = '';

    if ('hidden' !== $args['type']) {
        $field_container = '<div class="row form-row__holder mb-20 mb-md-10 %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';
    }

    if (SNTH_NP && 'state' === $args['type']) {
        $args['type'] = 'text';
    }

    switch ( $args['type'] ) {
        case 'country':
            $countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

            if ( 1 === count( $countries ) ) {

                $field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';

                $field .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ', $custom_attributes ) . ' class="country_to_state" readonly="readonly" />';

            } else {

                $field = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select select2 input-md form-control' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . '><option value="">' . esc_html__( 'Select a country&hellip;', 'woocommerce' ) . '</option>';

                foreach ( $countries as $ckey => $cvalue ) {
                    $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
                }

                $field .= '</select>';
            }

            break;
        case 'state':
            /* Get country this state field is representing */
            $for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
            $states      = WC()->countries->get_states( $for_country );

            if ( is_array( $states ) && empty( $states ) ) {

                $field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';

                $field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" />';

            } elseif ( ! is_null( $for_country ) && is_array( $states ) ) {

                $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
                    <option value="">' . esc_html__( 'Select a state&hellip;', 'woocommerce' ) . '</option>';

                foreach ( $states as $ckey => $cvalue ) {
                    $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
                }

                $field .= '</select>';

            } else {

                $field .= '<input type="text" class="input-text input-md form-control ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

            }

            break;
        case 'textarea':
            $field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text input-md form-control ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

            break;
        case 'checkbox':
            $field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
                    <input 
                        type="' . esc_attr( $args['type'] ) . '" 
                        class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" 
                        name="' . esc_attr( $key ) . '" 
                        id="' . esc_attr( $args['id'] ) . '" 
                        value="1" ' . checked( $value, 1, false ) . ' 
                    /> ' . $args['label'] . $required . '</label>';

            break;
        case 'hidden':
            $field = '  <input
                        type="'. esc_attr( $args['type'] ) .'"
                        class=""
                        name="' . esc_attr( $key ) . '"
                        id="' . esc_attr( $key ) . '"
                        value="' . esc_attr( $value ) . '"
                    />';
            break;
        case 'password':
        case 'text':
        case 'email':
        case 'tel':
        case 'number':
            $field .= ' <input 
                        type="' . esc_attr( $args['type'] ) . '" 
                        class="input-text  input-md form-control ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" 
                        name="' . esc_attr( $key ) . '" 
                        id="' . esc_attr( $args['id'] ) . '" 
                        placeholder="' . esc_attr( $args['placeholder'] ) . '"  
                        value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' 
                    />';

            break;
        case 'select':
            $field   = '';
            $options = '';

            if ( ! empty( $args['options'] ) ) {
                foreach ( $args['options'] as $option_key => $option_text ) {
                    if ( '' === $option_key ) {
                        // If we have a blank option, select2 needs a placeholder.
                        if ( empty( $args['placeholder'] ) ) {
                            $args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
                        }
                        $custom_attributes[] = 'data-allow_clear="true"';
                    }
                    $options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
                }

                $field .= '<select 
                                name="' . esc_attr( $key ) . '" 
                                id="' . esc_attr( $args['id'] ) . '" 
                                class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" 
                                ' . implode( ' ', $custom_attributes ) . ' 
                                data-placeholder="' . esc_attr( $args['placeholder'] ) . '"
                            >';
                $field .= $options;
                $field .= '</select>';
            }

            break;
        case 'radio':
            $label_id = current( array_keys( $args['options'] ) );

            if ( ! empty( $args['options'] ) ) {
                foreach ( $args['options'] as $option_key => $option_text ) {
                    $field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
                    $field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label>';
                }
            }

            break;

    }

    if ( ! empty( $field )  && 'hidden' !== $args['type'] ) {
        $field_html = '<div class="col-xs-12 col-sm-5 col-lg-3 label-column__holder">';

        if ( $args['label'] && 'checkbox' !== $args['type']) {
            $field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
        }

        $field_html .= '</div>';
        $field_html .= '<div class="col-xs-12 col-sm-7 col-lg-9 field-column__holder">';

        $field_html .= $field;

        if ( $args['description'] ) {
            $field_html .= '<span class="description">' . esc_html( $args['description'] ) . '</span>';
        }

        $field_html .= '</div>';

        foreach ($args['class'] as $key => $val) {
            if ('form-row-first' === $val || 'form-row-last' === $val) {
                $args['class'][$key] = 'form-row-wide';
            }
        }

        $container_class = esc_attr( implode( ' ', $args['class'] ) );
        $container_id    = esc_attr( $args['id'] ) . '_field';
        $field           = sprintf( $field_container, $container_class, $container_id, $field_html );
    }

    $field = apply_filters( 'woocommerce_form_field_' . $args['type'], $field, $key, $args, $value );

    if ( $args['return'] ) {
        return $field;
    } else {
        echo $field; // WPCS: XSS ok.
    }
}

/**
 * Reorder Billing fields
 *
 * @param $fields
 *
 * @return mixed
 */
function snth_wc_override_checkout_fields($fields)
{

    // Reorder Billing Fields
    if ( SNTH_NP ) {
        $billing_order = array(
            "billing_first_name",
            "billing_last_name",
            "billing_country",
            "billing_state",
            "billing_city",
            "billing_postcode",
            //"billing_np_number",
            "billing_address_1",
            "billing_phone",
            "billing_email",

            "jcountry_ref",
            //"jarea_ref",
            //"jcity_ref",
            //"jaddress_ref",
            "jshippingmethod_ref",
        );
    } else {
        $billing_order = array(
            "billing_first_name",
            "billing_last_name",
            "billing_country",
            "billing_state",
            "billing_city",
            "billing_postcode",
            "billing_address_1",
            "billing_phone",
            "billing_email",
        );
    }

    $ordered_billing_fields = array();

    foreach ( $billing_order as $field ) {
        $ordered_billing_fields[$field] = $fields["billing"][$field];
    }

    $fields["billing"] = $ordered_billing_fields;

    // Reorder Shipping Fields
    if ( SNTH_NP ) {
        $shipping_order = array(
            "shipping_first_name",
            "shipping_last_name",
            "shipping_country",
            "shipping_state",
            "shipping_city",
            "shipping_address_1",
        );
    } else {
        $shipping_order = array(
            "shipping_first_name",
            "shipping_last_name",
            "shipping_country",
            "shipping_state",
            "shipping_city",
            "shipping_address_1",
        );
    }

    $ordered_shipping_fields = array();

    foreach ( $shipping_order as $field ) {
        $ordered_shipping_fields[$field] = $fields["shipping"][$field];
    }

    $fields["shipping"] = $ordered_shipping_fields;

    return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'snth_wc_override_checkout_fields' );

