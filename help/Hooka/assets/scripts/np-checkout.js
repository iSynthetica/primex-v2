jQuery( function( $ ) {

    console.log('Old script');

    var is_shipping_address_checked = 0;
    var $shipping_address = $('.shipping_address');
    var chosen_shipping_rates = npCheckoutObj.chosen_shipping_rates;

    $(document).ready(function(){

        if ($shipping_address.is(':visible')) {
            is_shipping_address_checked = 1;
        } else if ($shipping_address.is(':hidden')) {
            is_shipping_address_checked = 0;
        }

        // np_set_shipping_environment_for_country();
        // np_get_city();
        // np_change_shipping_method();
        // np_get_warehouse();
    });

    $(document).on('change', '.shipping_method', function () {
        var shipping_method = $(this).val();

        np_change_shipping_method(shipping_method);
        np_get_warehouse();

        chosen_shipping_rates = document.querySelector('input[name="shipping_method[0]"]:checked').value;
    });

    $(document).on('change', '#billing_country', function () {
        np_set_shipping_environment_for_country();
    });

    $(document).on('change', '#billing_city', function () {
        $('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
        $('#jcity_ref').attr("value", $('#billing_city > option:selected').attr('data-index'));
        $('#jwarhouse_ref').attr("value", '');

        $('body').trigger('update_checkout');
        np_get_warehouse();
    });

    $(document).on('change', '#billing_np_number', function () {
        var shipping_method = np_get_selected_shipping_method();

        if (shipping_method === 'snth-npww') {
            var selectedWH = $('#billing_np_number option:selected').attr('value');

            $('#billing_address_1').val(selectedWH);
        }

        $('#jwarhouse_ref').attr("value", $('#billing_np_number option:selected').attr('data-ref'));
    });

    var np_change_shipping_method = function(shipping_method = null) {
        if(!shipping_method) {
            shipping_method = np_get_selected_shipping_method();
        }

        // Save Previous shipping method
        var prev_shipping_method = $('#jshippingmethod_ref').val();

        $('#shipping_method').removeClass(prev_shipping_method + '-selected').addClass(shipping_method + '-selected');

        // We need to remove Temp select field for City if it was Pickup Method
        if (prev_shipping_method === 'snth-pickup') {

            $('#billing_city option:eq(0)').prop('selected', true);

            var temp_field = $('#billing_city option[value="snth-pickup"]');

            if (temp_field.length) {
                temp_field.remove();
            }
        }

        // Set new shipping method and reset address field
        $('#jshippingmethod_ref').val(shipping_method);
        $('#billing_address_1').val('');

        if (shipping_method === 'snth-npww') {
            // $('#billing_city_field').css('display', 'block');
            // $('#billing_address_1_field').css('display', 'none');
            // $('#billing_np_number_field').css('display', 'block');
        } else if (shipping_method === 'snth-npwd') {
            // $('#billing_city_field').css('display', 'block');
            // $('#billing_address_1_field').css('display', 'block');
            // $('#billing_np_number_field').css('display', 'none');
        } else if (shipping_method === 'snth-pickup') {
            var localPickupCity = npCheckoutObj.messages.first_text_warhouse_pickup + ' - City';
            var localPickupAddress = npCheckoutObj.messages.first_text_warhouse_pickup + ' - Address';

            if ('' === npCheckoutObj.local_pickup_city) {
                // If there is now city to local pickup we will create Temp select field
                $('#billing_city')
                    .append($("<option></option>")
                        .attr("value", "snth-pickup")
                        .text(localPickupCity)
                        .prop('selected', true)
                    );
            } else {
                $('#billing_city option[value="'+npCheckoutObj.local_pickup_city+'"]').prop('selected', true);
            }

            if ('' !== npCheckoutObj.local_pickup_address) {
                localPickupAddress = npCheckoutObj.local_pickup_address;
            }

            $('#billing_address_1').val(localPickupAddress);
            $('#billing_state').val(localPickupAddress);
            // $('#billing_city_field').css('display', 'none');
            // $('#billing_address_1_field').css('display', 'none');
            // $('#billing_np_number_field').css('display', 'none');
        } else {
            $('#billing_address_1').val('Other');
            $('#billing_state').val('Other');
        }
    };

    var np_set_shipping_environment_for_country = function() {
        var selectedCountry = np_get_selected_country();

        $( document.body ).trigger( 'updated_checkout' );

        np_get_city(selectedCountry);


        // np_get_selected_shipping_method();
    };

    var np_get_selected_country = function() {
        var selectedCountry = 'UA';
        var countryInput = $('#billing_country');
        $('#billing_city').prop('disabled', true);

        if (countryInput.length > 0) {
            if('select' === getType(countryInput)) {
                selectedCountry = countryInput.find(":selected").val();
            } else {
                selectedCountry = countryInput.val();
            }
        }

        return selectedCountry;
    };

    var np_get_city = function(country = 'UA') {

        var cityHolder = $('#billing_city').parent('.field-column__holder');
        var cityHolderHtml = '<input type="text" class="input-text  input-md form-control " name="billing_city" id="billing_city" placeholder="" value="">';

        if ('UA' !== country) {
            cityHolder.html(cityHolderHtml);
            $('#billing_city').prop('disabled', false).trigger("chosen:updated");
            return;
        }

        $.ajax({
            type: 'POST',
            url: npCheckoutObj.ajaxurl,
            dataType: 'json',
            data: {
                action: 'np-api-get-city'
            },

            success: function (response) {
                if (response.status) {
                    var citiesArray = response.data.city.data;

                    var cityHolderHtml = '<select name="billing_city" id="billing_city" class="select select2 input-md form-control">';
                        cityHolderHtml += '<option data-index data-area value>' + npCheckoutObj.messages.first_text_select_city + '</option>';

                        for (var i = 0; i < citiesArray.length; i++) {
                            cityHolderHtml += '<option data-index="' + citiesArray[i].Ref + '" data-area="' + citiesArray[i].Area + '" value="' + citiesArray[i].Description + '">' + citiesArray[i].Description + '</option>'
                        }

                        cityHolderHtml += '</select>';

                    cityHolder.html(cityHolderHtml);

                }

                cityHolder.html(cityHolderHtml);
                $('#billing_city').prop('disabled', false).trigger("chosen:updated");
            }
        });

    };

    var getType = function(field){
        return field[0].tagName == "INPUT" ? field[0].type.toLowerCase() : field[0].tagName.toLowerCase();
    };


    var np_get_warehouse = function(whouse = null) {
        var billing_city_select = 'null',
            billing_area_select = 'null',
            billing_np_number,
            first_text_warehouse = '',
            first_value_warehouse = '',
            type_warehouse = '',
            lang = '',
            billing_state = $('#billing_state'),
            billingAddress = $('#billing_address_1'),
            billingAddressHolder = billingAddress.parent('.field-column__holder'),
            billingAddressHolderHtml = '<input type="text" class="input-text  input-md form-control " name="billing_address_1" id="billing_address_1" placeholder="" value="">',
            jarea_ref = $('#jarea_ref'),
            shipping_method = np_get_selected_shipping_method();

        if ('snth-other' === shipping_method) {
            billingAddressHolder.html(billingAddressHolderHtml);
            billingAddress = billingAddressHolder.find('#billing_address_1');
            billingAddress.val('Other');
            return;
        }

        billing_state.val('');

        if ( ( $('#billing_city > option:selected').attr('data-index') ) ) {
            billing_city_select = $('#billing_city > option:selected').attr('data-index');
            billing_area_select = $('#billing_city > option:selected').attr('data-area');
        }

        billing_np_number = $('#billing_np_number > option:selected').attr('data-index');

        if (!billing_city_select || billing_city_select === 'null') {

            if (shipping_method === 'snth-npww') {

                first_text_warehouse = npCheckoutObj.messages.first_text_warhouse_no_city;
                first_value_warehouse = 'snth-npww';

            }
            else if (shipping_method === 'snth-npwd') {

                first_text_warehouse = npCheckoutObj.messages.first_text_warhouse_no_city;
                first_value_warehouse = 'snth-npwd';

            }
            else if (shipping_method === 'snth-pickup') {

                first_text_warehouse = npCheckoutObj.messages.first_text_warhouse_no_city;
                first_value_warehouse = 'snth-pickup';
                billing_state.val('snth-pickup');

            }

            $('#billing_np_number')
                .html($("<option></option>")
                    .attr("value", first_value_warehouse)
                    .text(first_text_warehouse)
                );

            $('#billing_np_number').prop('disabled', false).trigger("chosen:updated");
            $('#jcity_ref').attr("value", '');
            $('#jwarhouse_ref').attr("value", '');

            return;
        }

        $.ajax({
            type: 'POST',
            url: npCheckoutObj.ajaxurl,
            dataType: 'json',
            data: {
                action: 'np-api-get-warehouse-by-city',
                city_ref: billing_city_select,
                area_ref: billing_area_select,
                warhouse: billing_np_number
            },

            success: function (response) {
                if (response.status) {

                    var whArray = response.data.warehouse.data[0];

                    if (shipping_method === 'snth-npww') {
                        billingAddressHolderHtml = '<select name="billing_address_1" id="billing_address_1" class="select select2 input-md form-control">';
                        billingAddressHolderHtml += '<option data-index data-area value>' + npCheckoutObj.messages.first_text_warhouse_select_np + '</option>';

                        for (var i = 0; i < whArray.length; i++) {
                            billingAddressHolderHtml += '<option data-ref="' + whArray[i].Ref + '" value="' + whArray[i].Description + '">' + whArray[i].Description + '</option>'
                        }

                        billingAddressHolderHtml += '</select>';
                    }

                    billingAddressHolder.html(billingAddressHolderHtml);

                    $('#billing_np_number').find('option').remove();

                    var type = response.data.warehouse.type[0].data;
                    var area = response.data.area.data[0].Description;
                    var areaRef = response.data.area.data[0].Ref;

                    if (shipping_method === 'snth-npww') {

                        if (billing_city_select === 'null') {
                            first_text_warehouse = npCheckoutObj.messages.first_text_warhouse_no_city;
                            billing_state.val('');
                            jarea_ref.val('');
                        } else {
                            first_text_warehouse = npCheckoutObj.messages.first_text_warhouse_select_np;
                            billing_state.val(area);
                            jarea_ref.val(areaRef);
                        }

                    }
                    else {

                        first_text_warehouse = npCheckoutObj.messages.first_text_warhouse_select_np;
                        first_value_warehouse = 'snth-npwd';

                        if (billing_city_select === 'null') {
                            billing_state.val('');
                            jarea_ref.val('');
                        } else {
                            billing_state.val(area);
                            jarea_ref.val(areaRef);
                        }
                    }



                    $('#billing_np_number')
                        .append($("<option></option>")
                            .attr("value", first_value_warehouse)
                            .text(first_text_warehouse)
                        );

                    if (shipping_method === 'snth-npww') {
                        var keys = response.data.warehouse.data[0];

                        for (var i = 0; i < keys.length; i++) {
                            for(var j = 0; j < type.length; j++){
                                if (type[j]['Ref'] == keys[i]['TypeOfWarehouse'])
                                {
                                    type_warehouse = " ("+type[j]['Description']+")";
                                    break;
                                }
                            }

                            $('#billing_np_number_field').prop('disabled', 'disabled');

                            $('#billing_np_number')
                                .append($("<option></option>")
                                    .attr("data-ref", keys[i]['Ref'])
                                    .attr("value", keys[i]['Description' + lang])
                                    .text(keys[i]['Description' + lang])
                                );
                            $('#billing_np_number_field').prop('disabled', false);
                        }
                    } else {
                        $('#billing_np_number_field').prop('disabled', 'disabled');
                        $('#billing_np_number_field').prop('disabled', false);
                    }

                    $("#billing_np_number").prop('disabled', false).trigger('chosen:updated');
                    $("#billing_np_number").on('click');

                    $('#jcity_ref').attr("value", $('#billing_city > option:selected').attr('data-index'));
                    $('#jwarhouse_ref').attr("value", $('#billing_np_number option:selected').attr('data-ref'));

                    if(whouse) {
                        $('#billing_np_number').val(whouse);
                        $('#billing_np_number').trigger("chosen:updated");
                    }

                }
            }
        });


    };

    var np_get_selected_shipping_method = function() {
        var shipping_method = $('input[name="shipping_method[0]"]:checked').val();

        if(!shipping_method) {
            shipping_method = $('input[name="shipping_method[0]"]').val()
        }

        return shipping_method;
    };

});