<?php
add_filter( 'woocommerce_payment_gateways', 'woionp_cod_class' );

function woionp_cod_class( $gateways ) {
    $gateways[] = 'Woo_All_In_One_NP_PG_Cod'; // your class name is here
    return $gateways;
}

add_action( 'plugins_loaded', 'woionp_init_cod_class' );

function woionp_init_cod_class() {
    class Woo_All_In_One_NP_PG_Cod extends WC_Payment_Gateway {
        public function __construct() {
            $this->setup_properties();
            $this->init_form_fields();
            $this->init_settings();
            $this->title              = $this->get_option( 'title' );
            $this->description        = $this->get_option( 'description' );

            $this->title              = $this->get_option( 'title' );
            $this->description        = $this->get_option( 'description' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        /**
         * Setup general properties for the gateway.
         */
        protected function setup_properties() {
            $this->id                 = 'np_cod';
            $this->icon               = apply_filters( 'woocommerce_cod_icon', '' );
            $this->method_title       = __( 'NP Cash on delivery', 'woocommerce' );
            $this->method_description = __( 'Have your customers pay with cash (or by other means) upon delivery.', 'woocommerce' );
            $this->has_fields         = false;
        }


        public function init_form_fields() {

            $options    = array();
            $data_store = WC_Data_Store::load( 'shipping-zone' );
            $raw_zones  = $data_store->get_zones();

            foreach ( $raw_zones as $raw_zone ) {
                $zones[] = new WC_Shipping_Zone( $raw_zone );
            }

            $zones[] = new WC_Shipping_Zone( 0 );

            foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

                $options[ $method->get_method_title() ] = array();

                // Translators: %1$s shipping method name.
                $options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'woocommerce' ), $method->get_method_title() );

                foreach ( $zones as $zone ) {

                    $shipping_method_instances = $zone->get_shipping_methods();

                    foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

                        if ( $shipping_method_instance->id !== $method->id ) {
                            continue;
                        }

                        $option_id = $shipping_method_instance->get_rate_id();

                        // Translators: %1$s shipping method title, %2$s shipping method id.
                        $option_instance_title = sprintf( __( '%1$s (#%2$s)', 'woocommerce' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

                        // Translators: %1$s zone name, %2$s shipping method instance name.
                        $option_title = sprintf( __( '%1$s &ndash; %2$s', 'woocommerce' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'woocommerce' ), $option_instance_title );

                        $options[ $method->get_method_title() ][ $option_id ] = $option_title;
                    }
                }
            }

            $this->form_fields = array(
                'enabled'            => array(
                    'title'       => __( 'Enable/Disable', 'woocommerce' ),
                    'label'       => __( 'Enable cash on delivery', 'woocommerce' ),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no',
                ),
                'title'              => array(
                    'title'       => __( 'Title', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                    'default'     => __( 'Cash on delivery', 'woocommerce' ),
                    'desc_tip'    => true,
                ),
                'description'        => array(
                    'title'       => __( 'Description', 'woocommerce' ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce' ),
                    'default'     => __( 'Pay with cash upon delivery.', 'woocommerce' ),
                    'desc_tip'    => true,
                ),
                'instructions'       => array(
                    'title'       => __( 'Instructions', 'woocommerce' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
                    'default'     => __( 'Pay with cash upon delivery.', 'woocommerce' ),
                    'desc_tip'    => true,
                ),
                'enable_for_methods' => array(
                    'title'             => __( 'Enable for shipping methods', 'woocommerce' ),
                    'type'              => 'multiselect',
                    'class'             => 'wc-enhanced-select',
                    'css'               => 'width: 400px;',
                    'default'           => '',
                    'description'       => __( 'If COD is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce' ),
                    'options'           => $options,
                    'desc_tip'          => true,
                    'custom_attributes' => array(
                        'data-placeholder' => __( 'Select shipping methods', 'woocommerce' ),
                    ),
                ),
            );
        }
    }
}