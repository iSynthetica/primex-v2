<?php
add_filter( 'woocommerce_payment_gateways', 'woionp_cod_class' );

function woionp_cod_class( $gateways ) {
    // $gateways[] = 'Woo_All_In_One_NP_PG_Cod';
    $gateways[] = 'Woo_All_In_One_NP_PG_Liqpay_Card';

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

        public function is_available() {
            return true;
        }

        public function payment_fields() {
            $settings = get_option('woocommerce_np_cod_settings', array());

            if (!empty($settings['public_key']) && !empty($settings['private_key'])) {
                include_once 'LiqPay.php';
                $liqpay = new LiqPay($settings['public_key'], $settings['private_key']);

                $raw = $liqpay->cnb_form_raw(array(
                    'action'         => 'pay',
                    'amount'         => '1',
                    'currency'       => 'UAH',
                    'description'    => 'description text',
                    'order_id'       => 'order_id_1',
                    'version'        => '3',
                    'paytypes'        => 'card'
                ));
                ?>
                <div id="liqpay_checkout"></div>
                <script>
                    window.LiqPayCheckoutCallback = function() {
                        LiqPayCheckout.init({
                            data: "<?php echo $raw['data'] ?>",
                            signature: "<?php echo $raw['signature'] ?>",
                            embedTo: "#liqpay_checkout",
                            language: "ru",
                            mode: "embed" // embed || popup
                        }).on("liqpay.callback", function(data){
                            console.log(data.status);
                            console.log(data);
                        }).on("liqpay.ready", function(data){
                            // ready
                        }).on("liqpay.close", function(data){
                            // close
                        });
                    };
                </script>
                <script src="//static.liqpay.ua/libjs/checkout.js" async></script>
                <?php
            } else {
                echo __( 'Not available', 'woocommerce' );
            }

        }
    }

    class Woo_All_In_One_NP_PG_Liqpay_Card extends WC_Payment_Gateway {
        public function __construct() {
            $this->setup_properties();
            $this->init_form_fields();
            $this->init_settings();
            $this->title              = $this->get_option( 'title' );
            $this->description        = $this->get_option( 'description' );
            $this->instructions       = $this->get_option( 'instructions' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_np_instant', array( $this, 'thankyou_page' ) );
        }

        protected function setup_properties() {
            $this->id                 = 'np_instant';
            $this->icon               = apply_filters( 'woocommerce_cod_icon', '' );
            $this->method_title       = __( 'Instant payment', 'woo-all-in-one-np' );
            $this->method_description = __( 'Instant payment.', 'woo-all-in-one-np' );
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
                    'label'       => __( 'Enable Instant payment', 'woo-all-in-one-np' ),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no',
                ),
                'title'              => array(
                    'title'       => __( 'Title', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                    'default'     => __( 'Instant payment', 'woo-all-in-one-np' ),
                    'desc_tip'    => true,
                ),
                'description'        => array(
                    'title'       => __( 'Description', 'woocommerce' ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce' ),
                    'default'     => __( 'Instant payment.', 'woo-all-in-one-np' ),
                    'desc_tip'    => true,
                ),
                'instructions'       => array(
                    'title'       => __( 'Instructions', 'woocommerce' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
                    'default'     => __( 'Select one of available methods.', 'woo-all-in-one-np' ),
                    'desc_tip'    => true,
                ),
                'public_key'              => array(
                    'title'       => __( 'Public key', "woo-all-in-one-np" ),
                    'type'        => 'text',
                    'description' => __( 'Public key you can get after registration on LiqPay.', 'woo-all-in-one-np' ),
                    'default'     => '',
                    // 'desc_tip'    => true,
                ),
                'private_key'              => array(
                    'title'       => __( 'Private key', "woo-all-in-one-np" ),
                    'type'        => 'password',
                    'description' => __( 'Private key you can get after registration on LiqPay.', 'woo-all-in-one-np' ),
                    'default'     => '',
                    // 'desc_tip'    => true,
                ),
                'instant_discount'              => array(
                    'title'       => __( 'Instant payment discount (%)', "woo-all-in-one-np" ),
                    'type'        => 'number',
                    'description' => __( 'Set instant payment discount (%).', 'woo-all-in-one-np' ),
                    'default'     => '',
                    // 'desc_tip'    => true,
                ),
                'instant_discount_period'              => array(
                    'title'       => __( 'Instant payment discount period', "woo-all-in-one-np" ),
                    'type'        => 'number',
                    'description' => __( 'Set instant payment discount period.', 'woo-all-in-one-np' ),
                    'default'     => '',
                    // 'desc_tip'    => true,
                ),
                'instant_discount_period_unit'              => array(
                    'title'       => __( 'Instant payment discount period unit', "woo-all-in-one-np" ),
                    'type'        => 'select',
                    'description' => __( 'Set instant payment discount period unit (minutes, hours, days).', 'woo-all-in-one-np' ),
                    'options'   => array(
                            'none' => __( 'Select unit', "woo-all-in-one-np" ),
                            'min' => __( 'Minutes', "woo-all-in-one-np" ),
                            'hour' => __( 'Hours', "woo-all-in-one-np" ),
                            'day' => __( 'Days', "woo-all-in-one-np" ),
                    ),
                    'default'     => '',
                    // 'desc_tip'    => true,
                ),
                'enable_for_methods' => array(
                    'title'             => __( 'Enable for shipping methods', 'woocommerce' ),
                    'type'              => 'multiselect',
                    'class'             => 'wc-enhanced-select',
                    'css'               => 'width: 400px;',
                    'default'           => '',
                    'description'       => __( 'If instant payment is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce' ),
                    'options'           => $options,
                    'desc_tip'          => true,
                    'custom_attributes' => array(
                        'data-placeholder' => __( 'Select shipping methods', 'woocommerce' ),
                    ),
                ),
            );
        }

        public function is_available() {
            $settings = get_option('woocommerce_np_instant_settings', array());

            if (empty($settings['public_key']) || empty($settings['private_key'])) {
                return false;
            }

            return true;
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            if ( $order->get_total() > 0 ) {
                // Mark as processing or on-hold (payment won't be taken until delivery).
                $order->update_status( apply_filters( 'woocommerce_bacs_process_payment_order_status', 'on-hold', $order ), __( 'Awaiting payment', 'woocommerce' ) );
            } else {
                $order->payment_complete();
            }

            // Remove cart.
            WC()->cart->empty_cart();

            // Return thankyou redirect.
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }

        public function thankyou_page( $order_id ) {

        }
    }
}

function wooaio_np_payment_details( $order_id = '' ) {
    // Get order and store in $order.
    $order = wc_get_order( $order_id );
    $status = $order->get_status();
    $payment_method = $order->get_payment_method();
    $payment_url = $order->get_checkout_payment_url();

//    var_dump($status);
//    var_dump($payment_method);
//    var_dump($payment_url);

    if ('np_instant' !== $payment_method) {
        return '';
    }

    if ('on-hold' === $status || 'pending' === $status) {
        // Get the order country and country $locale.
        $country = $order->get_billing_country();
        $city = $order->get_billing_city();
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $settings = get_option('woocommerce_np_instant_settings', array());
        $lp_order_id = $order->get_order_number();
        $lp_description = __( 'Order number:', 'woocommerce' ) . ' ' . $lp_order_id . ' ('.wc_format_datetime( $order->get_date_created()).')';
        $lp_amount = $order->get_total();

        if (!empty($settings['public_key']) && !empty($settings['private_key'])) {
            include_once 'LiqPay.php';
            $liqpay = new LiqPay($settings['public_key'], $settings['private_key']);

            $raw = $liqpay->cnb_form_raw(array(
                'action'         => 'pay',
                'amount'         => $lp_amount,
                'currency'       => 'UAH',
                'description'    => $lp_description,
                'order_id'       => $lp_order_id,
                'version'        => '3',
                //'sender_country_code'        => $country,
                'sender_city'        => $city,
                'sender_first_name'        => $first_name,
                'sender_last_name'        => $last_name,
            ));
            ?>
            <div id="liqpay_checkout_holder">
                <div id="liqpay_checkout"></div>
                <script>
                    var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ) ?>';

                    window.LiqPayCheckoutCallback = function() {
                        LiqPayCheckout.init({
                            data: "<?php echo $raw['data'] ?>",
                            signature: "<?php echo $raw['signature'] ?>",
                            embedTo: "#liqpay_checkout",
                            language: "ru",
                            mode: "embed" // embed || popup
                        }).on("liqpay.callback", function(data){
                            liqpay_ajax_request(data)
                        }).on("liqpay.ready", function(data){
                            // ready
                        }).on("liqpay.close", function(data){
                            // close
                        });
                    };

                    function liqpay_ajax_request(response) {
                        var status = response.status;
                        var data = {
                            'order_id': '<?php echo $lp_order_id; ?>'
                        };

                        if ("failure" == status) {
                            data.action = "wooaionp_liqpay_error";
                        } else {
                            data.action = "wooaionp_liqpay_success";

                            jQuery.ajax({
                                type: 'post',
                                url: ajaxUrl,
                                data: data,
                                success: function (response) {
                                    var decoded;

                                    try {
                                        decoded = jQuery.parseJSON(response);
                                    } catch(err) {
                                        console.log(err);
                                        decoded = false;
                                    }

                                    if (decoded) {
                                        if (decoded.message) {
                                            alert(decoded.message);
                                        }

                                        if (decoded.fragments) {
                                            updateFragments ( decoded.fragments );
                                        }

                                        setTimeout(function () {
                                            if (decoded.url) {
                                                window.location.replace(decoded.url);
                                            } else if (decoded.reload) {
                                                window.location.reload();
                                            }
                                        }, 100);
                                    } else {
                                        alert('Something went wrong');
                                    }
                                }
                            });
                        }

                        console.log(response);
                    }
                </script>
                <script src="//static.liqpay.ua/libjs/checkout.js" async></script>
            </div>
            <?php
        } else {
            echo __( 'Not available', 'woocommerce' );
        }
    } else {

    }
}

add_action('woocommerce_order_details_before_order_table', 'wooaio_np_payment_details', 5);