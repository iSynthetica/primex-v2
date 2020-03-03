<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://synthetica.com.ua
 * @since      1.0.0
 *
 * @package    Woo_All_In_One_Np
 * @subpackage Woo_All_In_One_Np/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_All_In_One_Np
 * @subpackage Woo_All_In_One_Np/public
 * @author     Synthetica <i.synthetica@gmail.com>
 */
class Woo_All_In_One_Np_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-all-in-one-np-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-all-in-one-np-public.js', array( 'jquery' ), $this->version, true );
	}

    public function add_novaposhta_sm($methods) {
        return $methods;
    }

    public function init() {
        add_action('woocommerce_shipping_init', 'woionp_sm_init');
        add_action('woocommerce_shipping_init', 'woionp_sm_settings_init');
    }

    public function woocommerce_form_field( $field, $key, $args, $value ) {
	    if ('billing_city' === $key) {
            $packages           = WC()->shipping()->get_packages();

            $chosen_method = isset( WC()->session->chosen_shipping_methods ) ? WC()->session->chosen_shipping_methods : '';
        }
	    return $field;
    }

    public function ajax_get_warehouse_by_city() {
        if (empty($_POST['selectedCity'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-np'));

            wooaio_ajax_response('error', $response);
        }

        $warehouses = Woo_All_In_One_NP_API::get_warehouses_by_city(sanitize_text_field($_POST['selectedCity']));

        $response = array('warehouses' => json_encode($warehouses, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_FORCE_OBJECT));
        wooaio_ajax_response('success', $response);
    }

    public function ajax_liqpay_success() {
        if (empty($_POST['order_id'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-np'));

            wooaio_ajax_response('error', $response);
        }
        $order_id = sanitize_text_field($_POST['order_id']);

        $order = wc_get_order( $order_id );

        if (!$order) {
            $response = array('message' => __('Wrong order number', 'woo-all-in-one-np'));

            wooaio_ajax_response('error', $response);
        }

        $order_note = __('Order payed', 'woo-all-in-one-np');
        $order->update_status( 'processing', $order_note, true );
        $order->set_date_paid(time());
        $order->save();

        $response = array('reload' => 0);
        wooaio_ajax_response('success', $response);
    }

    public function ajax_liqpay_error() {
        if (empty($_POST['order_id'])) {
            $response = array('message' => __('Cheating, huh!!!', 'woo-all-in-one-np'));

            wooaio_ajax_response('error', $response);
        }

        $response = array('message' => __('Error!!!', 'woo-all-in-one-np'));
        wooaio_ajax_response('success', $response);
    }

    public function footer_script() {
	    global $wpdb;
        $local_pickup_settings_sql = "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '%woocommerce_novaposhta_local_pickup%'";
        $local_pickup_settings_result = $wpdb->get_results($local_pickup_settings_sql, ARRAY_A);
        $local_pickup_settings = array();

        if (!empty($local_pickup_settings_result)) {
            foreach ($local_pickup_settings_result as $setting) {
                $setting_name = $setting['option_name'];
                $setting_name_array = explode('_', $setting_name);
                $setting_id = $setting_name_array[4];
                $setting = unserialize($setting['option_value']);
                $local_pickup_settings[$setting_id] = $setting;
            }
        }


	    $np_cities = Woo_All_In_One_NP_API::get_cities();
	    $np_areas = Woo_All_In_One_NP_API::get_areas();
	    $np_areas_by_ref = array();

	    foreach ($np_areas as $np_area) {
            $np_areas_by_ref[$np_area['Ref']] = $np_area;
        }

	    $np_cities_json = json_encode($np_cities, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_FORCE_OBJECT);
        $np_areas_json = json_encode($np_areas_by_ref, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_FORCE_OBJECT);
        $np_local_pickup_json = json_encode($local_pickup_settings, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_FORCE_OBJECT);
        ?>

        <input type="hidden" id="np_cities_json" value='<?php echo $np_cities_json; ?>'>
        <input type="hidden" id="np_areas_json" value='<?php echo $np_areas_json; ?>'>
        <input type="hidden" id="np_local_pickup_json" value='<?php echo $np_local_pickup_json; ?>'>

        <script>
            jQuery( function( $ ) {
                window.billing_state_val = $('#billing_state').val();
                window.billing_city_val = $('#billing_city').val();
                window.billing_address_1_val = $('#billing_address_1').val();
                window.shipping_state_val = $('#shipping_state').val();
                window.shipping_city_val = $('#shipping_city').val();
                window.shipping_address_1_val = $('#shipping_address_1').val();
                window.onLoadFirst = true;
                window.np_areas = null;
                window.selected_shipping_method = null;

                window.billing_state = $('#billing_state');
                window.billing_city = $('#billing_city');
                window.billing_address_1 = $('#billing_address_1');

                window.shipping_state = $('#shipping_state');
                window.shipping_city = $('#shipping_city');
                window.shipping_address_1 = $('#shipping_address_1');

                window.billing_address_1_label = $('#billing_address_1_field label');
                window.shipping_address_1_label = $('#shipping_address_1_field label');

                window.np_warehouse_label = '<label for="billing_address_1" class=""><?php echo __("NovaPoshta warehouse", "woo-all-in-one-np"); ?>&nbsp;<abbr title="<?php echo __( "required", "woocommerce" ); ?>">*</abbr></label>';

                var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ) ?>';

                $(document).on('change', '.shipping_method', function () {
                    window.selected_shipping_method = np_get_selected_shipping_method();
                    np_generate_cities_field();
                });

                $(document).on('change', '#billing_country', function () {
                    np_generate_cities_field();
                });

                $(document).on('change', '#billing_city', function () {
                    np_generate_warehouse_field();
                });

                $(document).on('change', '#local_pickup_locations', function () {
                    np_generate_local_pickup_location_field();
                });

                $(document).ready(function() {
                    // Check if only one country available
                    var billing_country = $('#billing_country');

                    if('select' !== getType(billing_country)) {
                        $('#billing_country_field').hide();
                        $('#shipping_country_field').hide();
                    }

                    window.selected_shipping_method = np_get_selected_shipping_method();
                    window.np_areas = $.parseJSON($('#np_areas_json').val());

                    np_generate_cities_field();

                    window.onLoadFirst = false;
                });

                function np_generate_local_pickup_location_field() {
                    var selectedState = np_get_selected_value('local_pickup_locations', 'state');
                    var selectedCity = np_get_selected_value('local_pickup_locations', 'city');
                    var selectedAddress = np_get_selected_value('local_pickup_locations', 'address');

                    $('#billing_state').val(selectedState);
                    $('#billing_city').val(selectedCity);
                    $('#billing_address_1').val(selectedAddress);
                }

                function np_generate_novaposhta_warehouse_warehouse_fields() {
                    np_maybe_clear_pickup_locations_fields();
                    $('#billing_state_field').hide();
                    var np_cities = $.parseJSON($('#np_cities_json').val());
                    var selectedCity = null;

                    var cities_html = '<select name="billing_city" id="billing_city" class="select select2 input-md form-control">';
                    cities_html += '<option value="" ></option>';

                    for (var i in np_cities) {
                        var selected = '';
                        var city = np_cities[i];

                        if (city.Description == window.billing_city_val) {
                            selected = ' selected';
                            selectedCity = city.Ref;
                        }

                        cities_html += '<option data-ref="'+city.Ref+'" data-area="'+city.Area+'" value="'+city.Description+'"'+selected+' >'+city.Description+ '</option>';
                    }

                    cities_html += '</select>';

                    $('#billing_city').replaceWith(cities_html);
                    $('#billing_address_1_field label').replaceWith(window.np_warehouse_label);

                    $('#billing_city').selectWoo({
                        placeholder: '<?php echo __("Select city", "woo-all-in-one-np"); ?>',
                        width: '100%'
                    });

                    if (selectedCity) {
                        np_generate_warehouse_field();
                    } else {
                        var cities_html = '<select name="billing_address_1" id="billing_address_1" class="select select2 form-control">';
                        cities_html += '<option value="" ></option>';

                        cities_html += '</select>';


                        if ($('#billing_address_1').hasClass("select2-hidden-accessible")) {
                            $('#billing_address_1').selectWoo('destroy');
                        }

                        $('#billing_address_1').replaceWith(cities_html);

                        $('#billing_address_1').selectWoo({
                            placeholder: '<?php echo __("Select city first", "woo-all-in-one-np"); ?>',
                            width: '100%'
                        });
                    }
                }

                function np_generate_cities_field() {
                    var selectedCountry = np_get_selected_value('billing_country');

                    if ('novaposhta_warehouse_warehouse' == window.selected_shipping_method && 'UA' == selectedCountry) {
                        np_generate_novaposhta_warehouse_warehouse_fields();
                    } else if(window.selected_shipping_method.startsWith('novaposhta_local_pickup')) {
                        np_maybe_clear_np_shipping_fields();

                        var shipping_method_name_array = window.selected_shipping_method.split(':');
                        var shipping_method_id = shipping_method_name_array[1];
                        var settings = $.parseJSON($('#np_local_pickup_json').val());
                        var settings_by_id = settings[shipping_method_id];
                        var locations = settings_by_id.local_pickup_locations;

                        var cities_html = '<select id="local_pickup_locations" class="select select2 input-md form-control">';
                        cities_html += '<option value="" ></option>';
                        for (var i in locations) {
                            var city = locations[i];

                            cities_html += '<option data-state="'+city.pickup_location_state+'" data-city="'+city.pickup_location_city+'" data-address="'+city.pickup_location_address+'" value="'+city.pickup_location_address+'">';
                            cities_html += city.pickup_location_city+ ', ' + city.pickup_location_address;
                            cities_html += '</option>';
                        }
                        cities_html += '</select>';

                        $(cities_html).insertAfter('#billing_address_1');

                        $('#local_pickup_locations').selectWoo({
                            placeholder: '<?php echo __("Select location", "woo-all-in-one-np"); ?>',
                            width: '100%'
                        });

                        $('#billing_state_field').hide();
                        $('#billing_city_field').hide();
                        $('#billing_address_1').hide();
                    } else {
                        np_maybe_clear_np_shipping_fields();
                        np_maybe_clear_pickup_locations_fields();
                    }
                }

                function np_maybe_clear_pickup_locations_fields() {
                    if ($('#local_pickup_locations').length) {
                        if ($('#local_pickup_locations').hasClass("select2-hidden-accessible")) {
                            $('#local_pickup_locations').selectWoo('destroy');
                        }

                        $('#local_pickup_locations').remove();

                        $('#billing_state_field').show();
                        $('#billing_city_field').show();
                        $('#billing_address_1').show();
                    }
                }

                function np_maybe_clear_np_shipping_fields() {

                    if ($('#billing_city').hasClass("select2-hidden-accessible")) {
                        $('#billing_city').selectWoo('destroy');
                    }

                    $('#billing_city').replaceWith(window.billing_city);
                    $('#billing_city').val('');

                    if ($('#billing_address_1').hasClass("select2-hidden-accessible")) {
                        $('#billing_address_1').selectWoo('destroy');
                    }

                    $('#billing_address_1').replaceWith(window.billing_address_1);
                    $('#billing_address_1').val('');

                    $('#billing_address_1_field label').replaceWith(window.billing_address_1_label);

                    $('#billing_state_field').show();
                    $('#billing_state').val('');
                }

                function np_generate_warehouse_field() {
                    var selectedCountry = np_get_selected_value('billing_country');

                    if ('novaposhta_warehouse_warehouse' == window.selected_shipping_method && 'UA' == selectedCountry) {
                        var selectedCity = np_get_selected_value('billing_city', 'ref');
                        var selectedArea = np_get_selected_value('billing_city', 'area');

                        var selectedAreaVal = window.np_areas[selectedArea];
                        $('#billing_state').val(selectedAreaVal.Description);

                        var data = {
                            'action': 'wooaionp_get_warehouse_by_city',
                            'selectedCity': selectedCity
                        };

                        ajaxRequest(data, function(decoded) {
                            var np_cities = $.parseJSON(decoded.warehouses);

                            var cities_html = '<select name="billing_address_1" id="billing_address_1" class="select select2 form-control">';
                            cities_html += '<option value="" ></option>';

                            for (var i in np_cities) {
                                var selected = '';
                                var city = np_cities[i];

                                if (city.Description == window.billing_address_1_val) {
                                    selected = ' selected';
                                }
                                cities_html += '<option data-ref="'+city.Ref+'" value="'+city.Description+'"'+selected+' >'+city.Description+ '</option>';
                            }

                            cities_html += '</select>';


                            if ($('#billing_address_1').hasClass("select2-hidden-accessible")) {
                                $('#billing_address_1').selectWoo('destroy');
                            }

                            $('#billing_address_1').replaceWith(cities_html);

                            $('#billing_address_1').selectWoo({
                                placeholder: '<?php echo __("Select warehouse", "woo-all-in-one-np"); ?>',
                                width: '100%'
                            });

                            // console.log(decoded);
                        }, function(decoded) {
                            // console.log(selectedCity);
                        });
                        // console.log(selectedCity);
                    }
                }

                function np_get_selected_shipping_method() {
                    var shippingMethod = $('input[name="shipping_method[0]"]:checked').val();

                    if(!shippingMethod) {
                        shippingMethod = $('input[name="shipping_method[0]"]').val()
                    }

                    return shippingMethod;
                }

                /**
                 * Get selected field value
                 */
                function np_get_selected_value(field, data) {
                    var selectedValue = '';
                    var inputField = $('#'+field);

                    if(inputField.length > 0) {
                        if('select' === getType(inputField)) {
                            if (data) {
                                selectedValue = inputField.find(":selected").data(data);
                            } else {
                                selectedValue = inputField.find(":selected").val();
                            }
                        } else {
                            selectedValue = inputField.val();
                        }
                    }

                    return selectedValue;
                }

                function getType(field){
                    return field[0].tagName == "INPUT" ? field[0].type.toLowerCase() : field[0].tagName.toLowerCase();
                }

                function ajaxRequest(data, cb, cbError) {
                    $.ajax({
                        type: 'post',
                        url: ajaxUrl,
                        data: data,
                        success: function (response) {
                            var decoded;

                            try {
                                decoded = $.parseJSON(response);
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

                                if (decoded.success) {
                                    if (typeof cb === 'function') {
                                        cb(decoded);
                                    }
                                } else {
                                    if (typeof cbError === 'function') {
                                        cbError(decoded);
                                    }
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
            });
        </script>
        <?php
    }
}
