jQuery.noConflict();
(function( $ ) {
    $(function() {
        $(document).ready(function () {
            np_field = '';
            var city = $('#billing_city').html();
            var billing_input_city = '<input style="display: block;width: 95%;height: 24px;" name="billing_city" id="billing_city" placeholder="Город" class="address-field validate-required woocommerce-validated update_totals_on_change" />';
            var shipping_input_city = '<input style="display: block;width: 95%;height: 24px;" name="shipping_city" id="shipping_city" placeholder="Город" class="address-field validate-required woocommerce-validated update_totals_on_change" />';
            var input_np = '<input style="display: none;width: 95%;height: 24px;" value="Не выбрано" name="billing_np_number" id="billing_np_number" placeholder="Отделение" class="address-field validate-required woocommerce-validated update_totals_on_change" />';
            $('#billing_city').addClass("chosen-select address-field validate-required woocommerce-validated update_totals_on_change");
            $('#billing_city_field').addClass("woocommerce-validated update_totals_on_change");
            $('#shipping_city').addClass("chosen-select address-field validate-required woocommerce-validated update_totals_on_change");
            if ($("#billing_country").attr('type') != 'hidden') {
               //$('#billing_country').addClass('chosen-select');
            }
            $('#shipping_city_field').addClass("woocommerce-validated update_totals_on_change");
            //$(document).getElementById('billing_country').removeClass('country_select').addClass('chosen-select');
            // $('#shipping_city').addClass("chosen-select");
            // $('#shipping_city').attr("class", 'chosen-select');
            MySelect = $(".chosen-select");

            $("#billing_country").change(function (e) {
                if ($(this).val() == "UA") {
                    $('#billing_city_chosen').css('display', 'block');
                    $('#shipping_city_chosen').css('display', 'block');
                    $('#billing_np_number_field').css('display', 'block');

                    if ($('input#billing_city').size())
                        $('input#billing_city').remove();
                    if ($('input#shipping_city').size())
                        $('input#shipping_city').remove();
                    if ($('input#billing_np_number').size())
                        $('input#billing_np_number').remove();
                }
                else {
                    if (!$('input#billing_city').size())
                        $(billing_input_city).insertAfter("#billing_city");
                    if (!$('input#shipping_city').size())
                        $(shipping_input_city).insertAfter("#shipping_city");
                    if (!$('input#billing_np_number').size())
                        $(input_np).insertAfter("#billing_np_number");

                    $('#billing_city_chosen').css('display', 'none');
                    $('#shipping_city_chosen').css('display', 'none');
                    $('#billing_np_number_field').css('display', 'none');
                    $('#billing_city')
                        .find('option:first-child').prop('selected', true)
                        .end().trigger('chosen:updated');
                    $('#shipping_city')
                        .find('option:first-child').prop('selected', true)
                        .end().trigger('chosen:updated');
                    $('#billing_np_number')
                        .find('option:first-child').prop('selected', true)
                        .end().trigger('chosen:updated');
                }
            });
           // if (document.getElementById("jcity_ref_field"))
           //     document.getElementById("jcity_ref_field").setAttribute("style", "display:none;");

            MySelect.chosen({width: "95%", search_contains: true});
            MySelect.trigger("chosen:open");
            MySelect.trigger("chosen:close");
			$('.chosen-container').addClass('select');
            $(document).on('change', '#billing_city', function () {

                //document.getElementById('jcity_ref').value = $('#billing_city > option:selected').attr('data-index');
                $('#jcity_ref').attr("value", $('#billing_city > option:selected').attr('data-index'));
            })
            $(document).on('change', '#shipping_city', function () {
                $('#jcity_ref').attr("value", $('#shipping_city > option:selected').attr('data-index'));
            })
        });
        $( document ).ajaxComplete(function() {
            $('#jcity_ref').attr("value", $('#billing_city > option:selected').attr('data-index'));
           // billing_np_number = $('#billing_np_number > option:selected').val();
            $('#jwarhouse_ref').attr("value", $('#billing_np_number option:selected').attr('data-ref'));
        });
    });
})(jQuery);