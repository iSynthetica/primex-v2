<?php
class Woo_All_In_One_Turbosms_Helpers {
    public static function get_allowed_tabs() {
        return array(
            'settings' => array(
                'title' => __('General Settings', 'woo-all-in-one-turbosms')
            ),
        );
    }

    public static function get_settings() {
        $default_settings = array(
            'sender' => sanitize_text_field(get_bloginfo( 'name' )),
            'login' => '',
            'password' => '',
            'send_new_order' => 'no',
            'text_new_order' => __('New order created', 'woo-all-in-one-turbosms'),
        );

        $settings = get_option('wooaio_turbosms_settings', array());

        $settings = array_merge($default_settings, $settings);

        return $settings;
    }

    public static function update_settings($data) {
        return update_option('wooaio_turbosms_settings', $data);
    }

    public static function get_order_status_changed_text() {
        return __('Your order #{{order_number}} status changed to {{order_status}}', 'woo-all-in-one-turbosms');
    }
}