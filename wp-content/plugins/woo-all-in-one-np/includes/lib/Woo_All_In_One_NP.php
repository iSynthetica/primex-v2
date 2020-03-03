<?php

function woionp_sm_init() {
    class Woo_All_In_One_NP_SM extends WC_Shipping_Method {
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'novaposhta_warehouse_warehouse';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __('NovaPoshta Warehouse - Warehouse', 'woo-all-in-one-np');
            $this->method_description = __('NovaPoshta Warehouse - Warehouse shipping method', 'woo-all-in-one-np'); // Description shown in admin
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();

            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->title      = $this->get_option( 'title' );
        }

        public function init_form_fields() {
            include_once("NovaPoshtaApi2.php");

            $this->instance_form_fields = array(
                'title'      => array(
                    'title'       => __( 'Title', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                    'default'     => $this->method_title,
                    'desc_tip'    => true,
                ),
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable NovaPostha', 'woo-all-in-one-np'),
                    'default' => 'yes'
                ),
                'description' => array(
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'desc_tip' => true,
                    'description' => __('This controls the description which the user sees during checkout.', 'woo-all-in-one-np'),
                    'default' => __("NovaPoshta Description", 'woo-all-in-one-np')
                ),
            );
        }

        public function calculate_shipping( $package = array() ) {
            $rate = array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => '',
                'calc_tax' => 'per_item'
            );

            // Register the rate
            $this->add_rate( $rate );
        }
    }

    class Woo_All_In_One_Courier_SM extends WC_Shipping_Method {
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'novaposhta_courier';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __('Courier Shipping', 'woo-all-in-one-np');
            $this->method_description = __('Courier Shipping Method', 'woo-all-in-one-np');
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $title = $this->get_option( 'title' );
            $option = get_option($this->get_instance_option_key());

            if (!empty($option['courier_settings'])) {
                $courier_settings = $option['courier_settings'];
            } else {
                $courier_settings = $this->get_option( 'courier_settings' );
            }

            $this->title = $title;
            $this->courier_settings = $courier_settings;

            add_filter( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', array( $this, 'save_courier_settings' ), 10, 2);
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->instance_form_fields = include( 'settings/courier-shipping.php' );
        }

        public function generate_courier_settings_html( $key, $data ) {
            $option = get_option($this->get_instance_option_key());
            $courier_settings = array();

            if (!empty($option['courier_settings'])) {
                $courier_settings = $option['courier_settings'];
            }
            ob_start();
            include ( 'views/html-courier-shipping.php' );
            return ob_get_clean();
        }

        public function save_courier_settings($instance_settings, $shipping_method) {
            $allowed_settings = array( 'title', 'enabled', 'courier_settings' );

            foreach ($instance_settings as $key => $setting) {
                if (!in_array($key, $allowed_settings, true)) {
                    unset($instance_settings[$key]);
                }
            }

            $locations = array();

            if ( isset( $_POST['courier_setting_state'] ) && isset( $_POST['courier_setting_city'] ) && isset( $_POST['courier_setting_cost'] ) ) {

                $courier_setting_state   = wc_clean( wp_unslash( $_POST['courier_setting_state'] ) );
                $courier_setting_city = wc_clean( wp_unslash( $_POST['courier_setting_city'] ) );
                $courier_setting_cost = wc_clean( wp_unslash( $_POST['courier_setting_cost'] ) );

                foreach ( $courier_setting_state as $i => $name ) {
                    if ( ! isset( $courier_setting_state[ $i ] ) ) {
                        continue;
                    }

                    $locations[] = array(
                        'courier_setting_state'         => $courier_setting_state[ $i ],
                        'courier_setting_city'          => $courier_setting_city[ $i ],
                        'courier_setting_cost'          => $courier_setting_cost[ $i ],
                    );
                }
            }

            $instance_settings['courier_settings'] = $locations;

            return $instance_settings;
        }

        public function calculate_shipping( $package = array() ) {
            $option = get_option($this->get_instance_option_key());
            $courier_settings = array();

            if (!empty($option['courier_settings'])) {
                $courier_settings = $option['courier_settings'];
            }

            if (empty($courier_settings)) {
                return;
            }

            foreach ($courier_settings as $courier_setting) {
                if (empty($courier_setting['courier_setting_state']) || empty($courier_setting['courier_setting_city'])) {
                    return;
                }
            }

            $rate = array(
                'id' => $this->get_rate_id(),
                'label' => $this->title,
                'package' => $package,
            );

            // Register the rate
            $this->add_rate( $rate );
        }
    }

    class Woo_All_In_One_Localpickup_SM extends WC_Shipping_Method {
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'novaposhta_local_pickup';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __('Local Pickup', 'woo-all-in-one-np');
            $this->method_description = __('Local Pickup shipping method', 'woo-all-in-one-np');
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $title = $this->get_option( 'title' );
            $option = get_option($this->get_instance_option_key());

            if (!empty($option['local_pickup_locations'])) {
                $local_pickup_locations = $option['local_pickup_locations'];
            } else {
                $local_pickup_locations = $this->get_option( 'local_pickup_locations' );
            }

            $this->title = $title;
            $this->pickup_locations = $local_pickup_locations;

            add_filter( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', array( $this, 'save_pickup_locations' ), 10, 2);
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->instance_form_fields = include( 'settings/localpickup-shipping.php' );
        }

        public function generate_local_pickup_locations_html( $key, $data ) {
            $option = get_option($this->get_instance_option_key());
            $local_pickup_locations = array();

            if (!empty($option['local_pickup_locations'])) {
                $local_pickup_locations = $option['local_pickup_locations'];
            }
            ob_start();
            include ( 'views/html-localpickup-shipping.php' );
            return ob_get_clean();
        }

        /**
         * Save save_pickup_locations table.
         */
        public function save_pickup_locations($instance_settings, $shipping_method) {
            $allowed_settings = array( 'title', 'enabled', 'local_pickup_locations' );

            foreach ($instance_settings as $key => $setting) {
                if (!in_array($key, $allowed_settings, true)) {
                    unset($instance_settings[$key]);
                }
            }

            $locations = array();

            if ( isset( $_POST['pickup_location_state'] ) && isset( $_POST['pickup_location_city'] ) && isset( $_POST['pickup_location_address'] ) ) {

                $pickup_location_state   = wc_clean( wp_unslash( $_POST['pickup_location_state'] ) );
                $pickup_location_city = wc_clean( wp_unslash( $_POST['pickup_location_city'] ) );
                $pickup_location_address      = wc_clean( wp_unslash( $_POST['pickup_location_address'] ) );

                foreach ( $pickup_location_state as $i => $name ) {
                    if ( ! isset( $pickup_location_state[ $i ] ) ) {
                        continue;
                    }

                    $locations[] = array(
                        'pickup_location_state'         => $pickup_location_state[ $i ],
                        'pickup_location_city'          => $pickup_location_city[ $i ],
                        'pickup_location_address'       => $pickup_location_address[ $i ],
                    );
                }
            }

            $instance_settings['local_pickup_locations'] = $locations;

            return $instance_settings;
        }

        public function calculate_shipping( $package = array() ) {
            $rate = array(
                'id' => $this->get_rate_id(),
                'label' => $this->title,
                'calc_tax' => 'per_item',
    			'package' => $package,
            );

            // Register the rate
            $this->add_rate( $rate );
        }
    }
}

function woionp_sm_add_nova_poshta($methods)
{

    $methods['novaposhta_warehouse_warehouse'] = 'Woo_All_In_One_NP_SM';
    $methods['novaposhta_local_pickup'] = 'Woo_All_In_One_Localpickup_SM';
    $methods['novaposhta_courier'] = 'Woo_All_In_One_Courier_SM';

    return $methods;

}

add_filter('woocommerce_shipping_methods', 'woionp_sm_add_nova_poshta');

function woionp_shipping_packages($packages)
{
    // return $packages;
}

// add_filter('woocommerce_shipping_packages', 'woionp_shipping_packages');
