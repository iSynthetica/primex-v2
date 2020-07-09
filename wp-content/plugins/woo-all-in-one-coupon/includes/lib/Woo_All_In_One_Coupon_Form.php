<?php


class Woo_All_In_One_Coupon_Form {
    public static function form_shortcode($atts = array()) {
        $atts = shortcode_atts(
            array(

            ),
            $atts
        );

        return Woo_All_In_One_Coupon_Form::form_html($atts);
    }

    public static function form_html($atts) {
        $coupon_rule = get_option('wooaiocoupon_rule_default', false);

        if (!$coupon_rule || empty($coupon_rule['coupon_amount'])) {
            return '';
        }

        $fields = Woo_All_In_One_Coupon_Form::get_form_fields();
        ob_start();
        ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo GRC_V3_KEY; ?>"></script>
        <div class="wooaiocoupon_form_container">
            <form class="wooaioservice_form">
                <?php do_action('wooaiocoupon_form_before_fields'); ?>
                <div class="wooaiocoupon_fields_holder">
                    <?php
                    foreach ( $fields as $key => $field ) {
                        $value = '';

                        if (!empty($fields_values[$key]['value'])) {
                            $value = $fields_values[$key]['value'];
                        }

                        woocommerce_form_field( $key, $field, $value );
                    }
                    ?>
                </div>

                <?php do_action('wooaiocoupon_form_after_fields'); ?>

                <div class="wooaiocoupon_description_holder">
                    <?php wooaiocoupon_form_description_container($coupon_rule); ?>
                </div>

                <?php wooaiocoupon_form_messages_container(); ?>

                <?php do_action('wooaiocoupon_form_before_submit'); ?>

                <?php wooaiocoupon_form_submit(); ?>

                <?php do_action('wooaiocoupon_form_after_submit'); ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function get_form_fields() {
        $fields = apply_filters( 'wooaiocoupon_fields', array (
            'coupon_name'  => array(
                // 'label'        => __( 'First name', 'woocommerce' ) . ' ' . __( 'Last name', 'woocommerce' ),
                'placeholder'  => __( 'First name', 'woocommerce' ) . ' ' . __( 'Last name', 'woocommerce' ) . ' *',
                'required'     => true,
                'class'        => array('form-row-wide'),
                'priority'     => 10,
            ),
            'coupon_phone' => array(
                // 'label'        => __( 'Phone', 'woocommerce' ),
                'placeholder'  => __( 'Phone', 'woocommerce' ),
                'required'     => false,
                'type'         => 'tel',
                'class'        => array('form-row-wide'),
                'validate'     => array('phone'),
                'priority'     => 50,
            ),
            'coupon_email' => array(
                // 'label'        => __( 'Email address', 'woocommerce' ),
                'placeholder'  => __( 'Email address', 'woocommerce' ) . ' *',
                'required'     => true,
                'type'         => 'email',
                'class'        => array('form-row-wide'),
                'validate'     => array('email'),
                'priority'     => 30,
            )
        ));

        uasort( $fields, 'wc_checkout_fields_uasort_comparison' );

        return $fields;
    }

    public static function validate_form($data) {
        $form_fields = Woo_All_In_One_Coupon_Form::get_form_fields();
        $error_array = array();

        foreach ($data as $dk => $dv) {
            $form_field = !empty($form_fields[$dk]) ? $form_fields[$dk] : false;
            $form_field_label = !empty($form_field['label']) ? $form_field['label'] : $form_field['placeholder'];

            if (!$form_field) {
                $error_array[$dk] = __('Cheating, huh!!!', 'woo-all-in-one-coupon');

                break;
            }

            $type = !empty($form_field['type']) ? $form_field['type'] : 'text';

            switch ($type) {
                case 'textarea':
                    $value = sanitize_textarea_field($dv);
                    break;
                default:
                    $value = sanitize_text_field($dv);
            }

            if (!empty($form_field['required']) && empty($value)) {
                $error_array[$dk] = sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $form_field_label ) . '</strong>' );
            } else {
                if (!empty($form_field['validate'])) {
                    foreach ($form_field['validate'] as $validate) {
                        if ('email' === $validate) {
                            $email_is_valid = is_email( $value );

                            if (!$email_is_valid) {
                                $error_array[$dk] = sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . esc_html( $form_field_label ) . '</strong>' );
                            } else {
                                $data[$dk] = $value;
                            }
                        } elseif ('phone' === $validate) {
                            $is_phone = WC_Validation::is_phone( $value );

                            if (!$is_phone) {
                                $error_array[$dk] = sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . esc_html( $form_field_label ) . '</strong>' );
                            } else {
                                $data[$dk] = $value;
                            }
                        } elseif ('date' === $validate) {
                            $timestamp = strtotime($value);

                            if (!$timestamp) {
                                $error_array[$dk] = sprintf( __( '%s is not a valid date format.', 'woo-all-in-one-service' ), '<strong>' . esc_html( $form_field_label ) . '</strong>' );
                            } else {
                                $data[$dk] = gmdate( 'Y-m-d H:i:s', $timestamp);
                            }
                        } else {
                            $data[$dk] = $value;
                        }
                    }
                }
            }
        }
        return array(
            'error' => $error_array,
            'data' => $data
        );
    }
}