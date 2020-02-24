<?php
/**
 * Woocommerce NovaPoshta functions
 *
 * @package Hookah/Includes/WC
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function snth_wc_NP_footer_script() {
    $chosen_shipping_rates = WC()->session->get('chosen_shipping_methods');
    $local_pickup_city = get_option('snth_wc_np_city_dep');
    $local_pickup_area = get_option('snth_wc_np_area_dep');
    $local_pickup_address = get_option('snth_wc_np_address_dep');

    ?>
    <script>
        jQuery( function( $ ) {
            window.onLoadFirst = true;

            var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ) ?>';

            var textFirstSelectCity = '<?php echo __('Select a City','snthwp') ?>';
            var textFirstSelectWarehouse = '<?php echo __('Select a Warehouse','snthwp') ?>';

            var localPickupCity = '<?php echo $local_pickup_city; ?>',
                localPickupArea = '<?php echo $local_pickup_area; ?>',
                localPickupAddress = '<?php echo $local_pickup_address; ?>';

            var jcountryRef         = $('#jcountry_ref'),
                jshippingmethodRef  = $('#jshippingmethod_ref');

            var is_shipping_address_checked     = 0,
                chosen_shipping_rates           = '<?php echo $chosen_shipping_rates[0] ?>';

            $(document).ready(function() {

                if (window.onLoadFirst) {

                    $('#billing_country').prop('disabled', 'disabled');
                    $('#billing_state').val('').prop('disabled', 'disabled');
                    $('#billing_city').val('').prop('disabled', 'disabled');
                    $('#billing_address_1').val('').prop('disabled', 'disabled');

                    window.onLoadFirst = false;
                }

                var selectedCountry = np_get_selected_value('billing_country');
                var selectedShippingMethod = np_get_selected_shipping_method();

                np_show_fields();

                jcountryRef.val(selectedCountry);
                jshippingmethodRef.val(selectedShippingMethod);
            });

            $(document).on('change', '#billing_country', function () {

                var selectedCountry = np_get_selected_value('billing_country');
                jcountryRef.val(selectedCountry);

                if (window.onLoadFirst) {

                    $('#billing_country').prop('disabled', 'disabled');
                    $('#billing_state').val('').prop('disabled', 'disabled');
                    $('#billing_city').val('').prop('disabled', 'disabled');
                    $('#billing_address_1').val('').prop('disabled', 'disabled');

                    window.onLoadFirst = false;
                }

                $('#order_review').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                np_show_fields();
                np_set_city_field();
            });

            $(document).on('change', '#billing_state', function () {

                var selectedArea = np_get_selected_value('billing_state');
                $('#billing_state').prop('disabled', false);
            });

            $(document).on('change', '#billing_city', function () {

                var selectedCity = np_get_selected_value('billing_city');

                np_set_state_field();
                np_set_address_field();
            });

            $(document).on('change', '#billing_address_1', function () {
                var selectedAddress = np_get_selected_value('billing_address_1');
            });

            $(document).on('change', '.shipping_method', function () {

                console.log('.shipping_method');

                $('#billing_state').val('').prop('disabled', 'disabled');
                $('#billing_city').val('').prop('disabled', 'disabled');
                $('#billing_address_1').val('').prop('disabled', 'disabled');

                var selectedShippingMethod = np_get_selected_shipping_method();
                jshippingmethodRef.val(selectedShippingMethod);

                $('#order_review').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                np_show_fields();
                np_set_state_field();
                np_set_city_field();
                np_set_address_field();
            });

            function np_show_fields() {
                let selectedCountry = np_get_selected_value('billing_country'),
                    shippingMethod = np_get_selected_shipping_method();

                let billing_state_field = $('#billing_state_field'),
                    billing_city_field = $('#billing_city_field'),
                    billing_postcode = $('#billing_postcode_field'),
                    billing_address_1_field = $('#billing_address_1_field');

                billing_state_field.hide();
                billing_city_field.hide();
                billing_address_1_field.hide();
                billing_postcode.hide();

                if ('UA' !== selectedCountry) {
                    $('.wc_payment_methods payment_methods methods').hide();
                } else {
                    $('.wc_payment_methods payment_methods methods').show();
                }

                if ('UA' !== selectedCountry || 'snth-other' === shippingMethod) {
                    billing_state_field.show();
                    billing_city_field.show();
                    billing_address_1_field.show();
                    billing_postcode.show();
                } else if ('snth-pickup' === shippingMethod) {

                } else if ('snth-npww' === shippingMethod) {
                    billing_city_field.show();
                    billing_address_1_field.show();
                }
            }

            $( document.body ).on( 'updated_checkout', function(e) {

                var oldShippingMethod = jshippingmethodRef.val();
                var selectedShippingMethod = np_get_selected_shipping_method();

                jshippingmethodRef.val(selectedShippingMethod);

                $('#billing_country').prop('disabled', false);
                $('#billing_state').prop('disabled', false);
                $('#billing_city').prop('disabled', false);
                $('#billing_address_1').prop('disabled', false);
                $('#billing_postcode').prop('disabled', false);

                console.log('updated_checkout');
            } );

            /**
             * Set Billing state field
             */
            function np_set_state_field() {

                let selectedCountry = np_get_selected_value('billing_country'),
                    shippingMethod = np_get_selected_shipping_method();

                if ( 'UA' !== selectedCountry || 'snth-other' === shippingMethod ) {

                    $('#billing_state').val('').prop('disabled', 'disabled').trigger("chosen:updated");

                    return;
                }

                if('snth-pickup' === shippingMethod) {
                    $('#billing_state').val(localPickupArea).prop('disabled', 'disabled').trigger("chosen:updated");

                    return;
                }

                var billing_area_select = $('#billing_city > option:selected').attr('data-area');

                if ( billing_area_select ) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxUrl,
                        dataType: 'json',
                        data: {
                            action: 'np-api-get-state-by-city',
                            area_ref: billing_area_select
                        },

                        success: function (response) {
                            if (response.status) {
                                var areaArray = response.data.area.data[0];

                                $('#billing_state').prop('disabled', 'disabled').val(areaArray.Description).trigger("chosen:updated");

                                $('#billing_state').trigger('change');

                                console.log(billing_area_select);
                            }
                        }
                    });
                }
            }

            /**
             * Set Billing City Field
             */
            function np_set_city_field() {
                let selectedCountry = np_get_selected_value('billing_country'),
                    shippingMethod = np_get_selected_shipping_method();

                let cityHolder      = $('#billing_city').parent('.field-column__holder'),
                    cityHolderHtml  = '<input type="text" class="input-text  input-md form-control " name="billing_city" id="billing_city" placeholder="" value="">';

                if ( 'UA' !== selectedCountry ||  'snth-other' === shippingMethod ) {
                    cityHolder.html(cityHolderHtml);

                    $('#billing_city').val('').prop('disabled', 'disabled').trigger("chosen:updated");
                    $('#billing_city').trigger('change');


                    $('#order_review').unblock();
                    return;
                }

                if('snth-pickup' === shippingMethod) {
                    cityHolder.html(cityHolderHtml);

                    $('#billing_city').val(localPickupCity).prop('disabled', 'disabled').trigger("chosen:updated");
                    $('#billing_city').trigger('change');


                    $('#order_review').unblock();

                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: ajaxUrl,
                    dataType: 'json',
                    data: { action: 'np-api-get-city' },
                    success: function (response) {
                        if (response.status) {
                            var citiesArray = response.data.city.data;

                            var cityHolderHtml = '<select name="billing_city" id="billing_city" class="select select2 input-md form-control">';

                                    cityHolderHtml += '<option data-index data-area value selected>' + textFirstSelectCity + '</option>';

                                for (var i = 0; i < citiesArray.length; i++) {
                                    cityHolderHtml += '<option data-index="' + citiesArray[i].Ref + '" data-area="' + citiesArray[i].Area + '" value="' + citiesArray[i].Description + '">';
                                    cityHolderHtml +=  citiesArray[i].Description;
                                    cityHolderHtml += '</option>';
                                }

                                cityHolderHtml += '</select>';

                                cityHolder.html(cityHolderHtml);
                        }

                        $('#billing_city').prop('disabled', 'disabled').trigger("chosen:updated");
                        $('#billing_city').trigger('change');
                    }
                });
            }

            /**
             * Set Billing Address Field
             */
            function np_set_address_field() {
                let selectedCity        = np_get_selected_value('billing_city'),
                    selectedCountry = np_get_selected_value('billing_country'),
                    selectedShippingMethod      = np_get_selected_shipping_method(),

                    addressHolder       = $('#billing_address_1').parent('.field-column__holder'),
                    addressHolderHtml   = '<input type="text" class="input-text  input-md form-control " name="billing_address_1" id="billing_address_1" placeholder="" value="">';

                if ( 'UA' !== selectedCountry ||  'snth-other' === selectedShippingMethod ) {
                    addressHolder.html(addressHolderHtml);
                    $('#billing_address_1').prop('disabled', 'disabled').val('').trigger("chosen:updated");
                    $('#billing_postcode').prop('disabled', 'disabled').val('').trigger("chosen:updated");



                    $('#order_review').unblock();
                    return;
                }

                if('snth-pickup' === selectedShippingMethod) {
                    addressHolder.html(addressHolderHtml);
                    $('#billing_address_1').prop('disabled', 'disabled').val(localPickupAddress).trigger("chosen:updated");
                    $('#billing_postcode').prop('disabled', 'disabled').val('111111111').trigger("chosen:updated");



                    $('#order_review').unblock();
                    return;
                }

                if('snth-npwd' === selectedShippingMethod) {
                    addressHolder.html(addressHolderHtml);
                    $('#billing_address_1').prop('disabled', 'disabled').val('').trigger("chosen:updated");
                    $('#billing_postcode').prop('disabled', 'disabled').val('111111111').trigger("chosen:updated");



                    $('#order_review').unblock();
                    return;
                }

                if('snth-npww' === selectedShippingMethod) {
                    var billing_city_select = $('#billing_city > option:selected').attr('data-index');

                    $.ajax({
                        type: 'POST',
                        url: ajaxUrl,
                        dataType: 'json',
                        data: {
                            action: 'np-api-get-warehouse-by-city',
                            city_ref: billing_city_select
                        },

                        success: function (response) {
                            if (response.status) {
                                var warehousesArray = response.data.warehouse.data[0];

                                var addressHolderHtml = '<select name="billing_address_1" id="billing_address_1" class="select select2 input-md form-control">';
                                    addressHolderHtml += '<option data-index data-area value>' + textFirstSelectWarehouse + '</option>';

                                for (var i = 0; i < warehousesArray.length; i++) {
                                    addressHolderHtml += '<option data-index="' + warehousesArray[i].Ref + '" ';
                                    addressHolderHtml += 'data-city="' + warehousesArray[i].CityRef + '" ';
                                    addressHolderHtml += 'value="' + warehousesArray[i].Description + '">';
                                    addressHolderHtml += warehousesArray[i].Description;
                                    addressHolderHtml += '</option>';
                                }

                                addressHolderHtml += '</select>';

                                addressHolder.html(addressHolderHtml);
                                $('#billing_postcode').prop('disabled', 'disabled').val('111111111').trigger("chosen:updated");



                                $('#order_review').unblock();
                            }
                        }
                    });


                    addressHolder.html(addressHolderHtml);
                    $('#billing_address_1').prop('disabled', 'disabled').trigger("chosen:updated");

                    return;
                }
            }

            /**
             * Get selected field value
             */
            function np_get_selected_value(field) {
                var selectedValue = '';
                var inputField = $('#'+field);

                if(inputField.length > 0) {
                    if('select' === getType(inputField)) {
                        selectedValue = inputField.find(":selected").val();
                    } else {
                        selectedValue = inputField.val();
                    }
                }

                return selectedValue;
            }

            /**
             * Get selected shipping method
             * @returns {jQuery}
             */
            function np_get_selected_shipping_method() {
                var shippingMethod = $('input[name="shipping_method[0]"]:checked').val();

                if(!shippingMethod) {
                    shippingMethod = $('input[name="shipping_method[0]"]').val()
                }

                return shippingMethod;
            }

            function getType(field){
                return field[0].tagName == "INPUT" ? field[0].type.toLowerCase() : field[0].tagName.toLowerCase();
            }
        });
    </script>
    <?php
}
add_action( 'wp_footer', 'snth_wc_NP_footer_script' );