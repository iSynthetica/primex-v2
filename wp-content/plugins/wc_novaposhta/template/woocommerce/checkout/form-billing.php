<?php
/**
 * Checkout billing information form
 *
 * @author    WooThemes
 * @package  WooCommerce/Templates
 * @version     2.1.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** @global WC_Checkout $checkout */
?>
<div class="woocommerce-billing-fields">
    <?php if (WC()->cart->ship_to_billing_address_only() && WC()->cart->needs_shipping()) : ?>

        <h3><?php _e('Billing &amp; Shipping','novaposhta'); ?></h3>

    <?php else : ?>

        <h3><?php _e('Billing Details','novaposhta'); ?></h3>

    <?php endif; ?>

    <?php do_action('woocommerce_before_checkout_billing_form',$checkout); ?>

    <?php foreach ($checkout->checkout_fields['billing'] as $key => $field) : ?>
        <?php

        if ($key == 'billing_city') {
            //include_once ("../../../NovaPoshtaApi2.php");
            $NP_settings = get_option('woocommerce_novaposhta_settings');
            $np_API_key = $NP_settings['np_API_key'];

            $lang = get_lang();
            $np = new NovaPoshtaApi2($np_API_key);
            $city = $np->getCities();
            if(!$city['success']){
                $admin_email = get_option('admin_email');
                $blog_name = get_option('blogname');
                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($admin_email, $blog_name, "NovaPoshta ERROR: ".$city['errors'][0], $headers );
            }
            $city_cont = count($city['data']);
            $city_arr = array();
            $rr = "Ru";
            for ($i = 0; $i < $city_cont; $i++) {
                if ($i == 0) $j = __('Select a city','novaposhta');
                else {
                    $j = $city['data'][$i]["Description" . $lang];
                    $jref = $city['data'][$i]["Ref"];
                }
                if ($i == 0) $city_arr[''] = $j;
                else
                    $city_arr[$jref] = $j;
            }
            ?>
            <p class="form-row form-row-wide  validate-required woocommerce-validated" id="billing_city_field">
                <label class="" for="billing_city"><?php _e('Town / City','novaposhta'); ?><abbr title="required"
                                                                                                 class="required">*</abbr></label>
                <span>
                            <select name="billing_city" class="select" id="billing_city"
                                    placeholder="<?php _e('Town / City','novaposhta'); ?>">
                                <?php
                                $pos = 0;
                                foreach ($city_arr as $key => $value) {
                                    if ($pos == 0) echo "<option data-index='$key' value=''>$value</option>";
                                    else
                                        echo "<option data-index='$key' value='$value'>$value</option>";
                                    $pos++;
                                }
                                ?>
                            </select>
                        </span>
            </p>


            <?php continue;
        }
        elseif (($key == 'billing_np_number') && (WC()->cart->needs_shipping())) {

            $lang = get_lang();

            $request_np_field = $NP_settings['request_np_field'];
            if ($request_np_field == "0") $request_np = "";
            else $request_np = "validate-required";

            //  $request_np = "validate-required";
            ?>

            <p class="form-row form-row-wide <?php echo $request_np; ?> woocommerce-validated" id="billing_np_number_field">
                <label class="" id="lable_billing_np_number"
                       for="billing_np_number"><?php _e('Warhouse NovaPoshta #','novaposhta'); ?>
                    <?php if ($request_np) { ?> <abbr title="required" class="required">*</abbr> <?php } ?>
                </label>
                <span>
                            <select class="chosen-select select" name="billing_np_number" id="billing_np_number">
                                <?php

                                if ($city_ref) {
                                    foreach ($result['data'] as $item) {
                                        $str1 = str_replace("\"","",$item["Description" . $lang]);
                                        echo "<option value='$str1' > $str1</option>";
                                    }
                                }
                                ?>
                            </select>
                        </span>
            </p>


            <?php
            continue;
        }
        else woocommerce_form_field($key,$field,$checkout->get_value($key)); ?>

    <?php endforeach; ?>

</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
    <div class="woocommerce-account-fields">
        <?php if ( ! $checkout->is_registration_required() ) : ?>

            <p class="form-row form-row-wide create-account">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ) ?> type="checkbox" name="createaccount" value="1" /> <span><?php _e( 'Create an account?', 'woocommerce' ); ?></span>
                </label>
            </p>

        <?php endif; ?>

        <?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

        <?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

            <div class="create-account">
                <?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
                    <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>

        <?php endif; ?>

        <?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
    </div>
<?php endif; ?>
<script type="text/javascript">
    jQuery(function ($) {
        var checked_ship = 0;
        var np_field = '';
        var chosen_shipping_rates;
        if ($('.shipping_address').is(':visible')) {
            checked_ship = 1;
        }
        if ($('.shipping_address').is(':hidden')) {
            checked_ship = 0;
        }
        ger_warhouse();

        var chosen_shipping_rates = "<?php global $woocommerce;  $chosen_shipping_rates = WC()->session->get('chosen_shipping_methods');
            echo $chosen_shipping_rates[0];
            ?>";

        $(document).ready(function () {
            var bcity = '<?php echo get_user_meta( get_current_user_id(), 'billing_city', true );?>';
            var bvhouse = '<?php echo get_user_meta( get_current_user_id(), 'billing_np_number', true );?>';
            if(bcity) {
                $('#billing_city').val(bcity);
                $('#billing_city').trigger("chosen:updated");
                ger_warhouse(bvhouse);
            }

                //var ship_method = $('.shipping_method').val();
            var ship_method = $('input[name="shipping_method[0]"]:checked').val();
            if(!ship_method) {
                ship_method = $('input[name="shipping_method[0]"]').val()
            }

            if(ship_method != 'novaposhta'){
                $('#billing_np_number_field').css('display','none');
                $('<p class="form-row woocommerce-validated billing_np_number_field_temp" id="billing_np_number_field" data-priority="" style="display:none;">' +
                    '<input type="text" class="input-text" name="billing_np_number" id="billing_np_number_temp"  value="NONE"/></p>').insertAfter($('#billing_np_number_field'));
            }
            else{
                if($('.billing_np_number_field_temp').length > 0) {
                    $('.billing_np_number_field_temp').remove();
                }
            }
        })

        $(document).on('change', '#shipping_city', function () {
            /*console.log(chosen_shipping_rates);*/
            chosen_shipping_rates = document.querySelector('input[name="shipping_method[0]"]:checked').value;
            if(chosen_shipping_rates == 'novaposhta') {
                $('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
                $('body').trigger('update_checkout');
                ger_warhouse();
            }
        })
        $(document).on('change', '#billing_np_number', function () {
            $('#jwarhouse_ref').attr("value", $('#billing_np_number option:selected').attr('data-ref'));
        })
        $(document).on('change', '#billing_city', function () {
            if(chosen_shipping_rates == 'novaposhta') {
                $('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
                $('body').trigger('update_checkout');
                ger_warhouse();
            }
        })
        $("#ship-to-different-address-checkbox").change(function () {
            if(chosen_shipping_rates == 'novaposhta') {
                $('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
                ger_warhouse();
            }
        })

        //var ajaxurl = "<?php // echo admin_url('admin-ajax.php'); ?>";
        /*$(document).on('change', '#billing_np_number', function () {

            var select_warhouse = $(this).chosen().val();
            console.log(select_warhouse);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'np_select_warhouse',
                    warhouse: select_warhouse
                },
                success: function (response) {
                    if (response.status) {
                    }
                }
            })
        })*/


        $(document).on('change', '.shipping_method', function () {
            ship_method = $(this).val();
            if(ship_method != 'novaposhta'){
                $('#billing_np_number_field').css('display','none');
                $('<p class="form-row woocommerce-validated billing_np_number_field_temp" id="billing_np_number_field" data-priority="" style="display:none;">' +
                    '<input type="text" class="input-text" name="billing_np_number" id="billing_np_number_temp"  value="NONE"/></p>').insertAfter($('#billing_np_number_field'));
            }
            else{
                if($('.billing_np_number_field_temp').length > 0) {
                    $('.billing_np_number_field_temp').remove();
                }
            }



            $('#billing_np_number').val($('#billing_np_number').find("option:first-child").text()).trigger('chosen:updated');
            chosen_shipping_rates = document.querySelector('input[name="shipping_method[0]"]:checked').value;
        })
        function ger_warhouse(bvhouse = null) {
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var lang = "<?php echo get_lang();?>";
            var billing_city_select;
            var billing_np_number;
            var input = document.getElementById("ship-to-different-address-checkbox");
            var first_text_warhouse = '';
            var isChecked = '';
            var checked_ship = 0;
            var type_wh = '';
            if ($('.shipping_address').is(':visible')) {
                checked_ship = 1;
            }
            if ($('.shipping_address').is(':hidden')) {
                checked_ship = 0;
            }
            var address_checkbox = document.getElementById('ship-to-different-address-checkbox');
            if (address_checkbox) {
                isChecked = input.checked;
                checked_ship = (isChecked) ? "1" : "0";
            }
            if (checked_ship == 0) {
                $('#billing_np_number_field').insertAfter($('#billing_city_field'));
            }
            else {
                if ($('#order_comments_field')) {
                    $('#billing_np_number_field').insertAfter($('#shipping_city_field'));
                    $('#order_comments_field').appendTo($('#billing_np_number_field'));
                }
                else
                    $('#billing_np_number_field').appendTo($('#shipping_city_field'));

            }

            billing_city_select = 'null';
            if (($('#billing_city > option:selected').attr('data-index')) && (checked_ship == 0))
                billing_city_select = $('#billing_city > option:selected').attr('data-index');
            if (($('#shipping_city > option:selected').attr('data-index')) && (checked_ship == 1))
                billing_city_select = $('#shipping_city > option:selected').attr('data-index');
            billing_np_number = $('#billing_np_number > option:selected').attr('data-index');

            if(!billing_city_select || billing_city_select == 'null'){
                first_text_warhouse = '<?php _e('At first Select City','novaposhta'); ?>';
                $('#billing_np_number')
                    .append($("<option></option>")
                        .attr("value", '')
                        .text(first_text_warhouse)
                    );
                return;}


            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'np_api_get_cities_by_area',
                    city_ref: billing_city_select,
                    warhouse: billing_np_number
                },
                success: function (response) {
                    if (response.status) {
                        var type = response.data.type[0].data;
                        if (billing_city_select == 'null') first_text_warhouse = '<?php _e('At first Select City','novaposhta'); ?>';
                        else first_text_warhouse = '<?php _e('Select Warhouse','novaposhta'); ?>';
                        $('#billing_np_number')
                            .find('option')
                            .remove();
                        $('#billing_np_number')
                            .append($("<option></option>")
                                .attr("value", '')
                                .text(first_text_warhouse)
                            );
                        var keys = response.data.data[0];

                        for (var i = 0; i < keys.length; i++) {
                            for(var j = 0; j < type.length; j++){
                                if (type[j]['Ref'] == keys[i]['TypeOfWarehouse'])
                                {
                                    type_wh = " ("+type[j]['Description']+")";
                                    break;
                                }

                            }
                            $('#billing_np_number_field').prop('disabled', 'disabled');
                            $('#billing_np_number')
                                .append($("<option></option>")
                                    .attr("data-ref", keys[i]['Ref'])
                                    .attr("value", keys[i]['Description' + lang])
                                    //.text(keys[i]['Description' + lang]+type_wh)
                                    .text(keys[i]['Description' + lang])
                                );
                            $('#billing_np_number_field').prop('disabled', false);
                        }
                        $("#billing_np_number").prop('disabled', false).trigger('chosen:updated');
                        $("#billing_np_number").on('click');
                        if(bvhouse) {
                            $('#billing_np_number').val(bvhouse);
                            /*$("#billing_np_number option").each(function() {
                                console.log($(this).text());
                                if($(this).text() == bvhouse) {
                                    $(this).attr('selected', 'selected');
                                }
                            });*/
                            $('#billing_np_number').trigger("chosen:updated");
                        }
                        //$('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        }
    })
</script>