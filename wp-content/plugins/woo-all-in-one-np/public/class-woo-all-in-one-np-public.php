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

    public function footer_script() {
	    $np_cities = Woo_All_In_One_NP_API::get_cities();
	    $np_areas = Woo_All_In_One_NP_API::get_areas();
	    $np_areas_by_ref = array();

	    foreach ($np_areas as $np_area) {
            $np_areas_by_ref[$np_area['Ref']] = $np_area;
        }

	    $np_cities_json = json_encode($np_cities, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_FORCE_OBJECT);
        $np_areas_json = json_encode($np_areas_by_ref, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_FORCE_OBJECT);
        ?>

        <input type="hidden" id="np_cities_json" value='<?php echo $np_cities_json; ?>'>
        <input type="hidden" id="np_areas_json" value='<?php echo $np_areas_json; ?>'>

        <script>
            jQuery( function( $ ) {
                window.billing_city_val = $('#billing_city').val();
                window.billing_address_1_val = $('#billing_address_1').val();
                window.billing_state_val = $('#billing_state_val').val();

                // $('#billing_city').val('');
                // $('#billing_address_1').val('');
                // $('#billing_state').val('');

                window.onLoadFirst = true;

                window.np_areas = null;

                window.selected_shipping_method = null;
                window.billing_city = $('#billing_city');

                window.billing_address_1 = $('#billing_address_1');
                window.billing_address_1_label = $('#billing_address_1_field label');

                window.np_warehouse_label = '<label for="billing_address_1" class=""><?php echo __("NovaPoshta warehouse", "woo-all-in-one-np"); ?>&nbsp;<abbr title="<?php echo __( "required", "woocommerce" ); ?>">*</abbr></label>';
                var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ) ?>';

                $(document).on('change', '.shipping_method', function () {
                    window.selected_shipping_method = np_get_selected_shipping_method();
                    np_generate_cities_field();
                    console.log(window.selected_shipping_method);
                });

                $(document).on('change', '#billing_country', function () {
                    np_generate_cities_field();
                });

                $(document).on('change', '#billing_city', function () {
                    np_generate_warehouse_field();
                });

                $(document).ready(function() {
                    if (window.onLoadFirst) {
                        window.selected_shipping_method = np_get_selected_shipping_method();
                        window.np_areas = $.parseJSON($('#np_areas_json').val());

                        np_generate_cities_field();

                        window.onLoadFirst = false;
                    }
                });

                function np_generate_cities_field() {
                    var selectedCountry = np_get_selected_value('billing_country');

                    if ('novaposhta_warehouse_warehouse' == window.selected_shipping_method && 'UA' == selectedCountry) {
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
                    } else {
                        $('#billing_state_field').show().val('');

                        if ($('#billing_city').hasClass("select2-hidden-accessible")) {
                            $('#billing_city').selectWoo('destroy');
                        }

                        if ($('#billing_address_1').hasClass("select2-hidden-accessible")) {
                            $('#billing_address_1').selectWoo('destroy');
                        }

                        $('#billing_city').replaceWith(window.billing_city);
                        $('#billing_city').val('');

                        $('#billing_address_1_field label').replaceWith(window.billing_address_1_label);
                    }
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
                                placeholder: '<?php echo __("Select warehouse"); ?>',
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
