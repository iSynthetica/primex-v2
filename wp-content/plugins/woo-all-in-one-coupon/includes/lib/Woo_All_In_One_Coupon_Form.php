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
        $fields = Woo_All_In_One_Coupon_Form::get_form_fields();
        ob_start();
        ?>
        <div class="wooaiocoupon_form_container">
            <form class="wooaioservice_form">
                <?php do_action('wooaiocoupon_form_before_fields'); ?>

                <?php
                foreach ( $fields as $key => $field ) {
                    $value = '';

                    if (!empty($fields_values[$key]['value'])) {
                        $value = $fields_values[$key]['value'];
                    }

                    woocommerce_form_field( $key, $field, $value );
                }
                ?>

                <?php do_action('wooaiocoupon_form_after_fields'); ?>

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
                'label'        => __( 'First name', 'woocommerce' ) . ' ' . __( 'Last name', 'woocommerce' ),
                'required'     => true,
                'class'        => array('form-row-wide'),
                'priority'     => 10,
            ),
            'coupon_phone' => array(
                'label'        => __( 'Phone', 'woocommerce' ),
                'required'     => false,
                'type'         => 'tel',
                'class'        => array('form-row-wide'),
                'validate'     => array('phone'),
                'priority'     => 50,
            ),
            'coupon_email' => array(
                'label'        => __( 'Email address', 'woocommerce' ),
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
}