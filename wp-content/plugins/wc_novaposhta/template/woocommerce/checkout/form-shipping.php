<?php
/**
 * Checkout shipping information form
**/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div class="woocommerce-shipping-fields">
    <?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

        <?php
        if ( empty( $_POST ) ) {

            $ship_to_different_address = get_option( 'woocommerce_ship_to_destination' ) === 'shipping' ? 1 : 0;
            $ship_to_different_address = apply_filters( 'woocommerce_ship_to_different_address_checked', $ship_to_different_address );

        } else {

            $ship_to_different_address = $checkout->get_value( 'ship_to_different_address' );

        }
        ?>

        <h3 id="ship-to-different-address">
            <label for="ship-to-different-address-checkbox" class="checkbox"><?php _e( 'Ship to a different address?', 'woocommerce' ); ?></label>
            <input id="ship-to-different-address-checkbox" class="input-checkbox" <?php checked( $ship_to_different_address, 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" />
        </h3>

        <div class="shipping_address">

            <?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

            <?php foreach ( $checkout->checkout_fields['shipping'] as $key => $field ) : ?>

                <?php
                if($key == 'shipping_city') {

                    //include_once ("../../../NovaPoshtaApi2.php");
                    $NP_settings = get_option( 'woocommerce_novaposhta_settings');
                    $np_API_key = $NP_settings['np_API_key'];

                    $lang = get_lang();
                    $np = new NovaPoshtaApi2($np_API_key);
                    $city = $np->getCities();

                    $city_cont = count($city['data']);
                    $city_arr = array();
                    $rr = "Ru";
                    for ($i=0; $i < $city_cont; $i++) {
                        if ($i == 0)
                            $j = "Выберите Город";
                        else {
                            $j = $city['data'][$i]["Description" . $lang];
                            $jref = $city['data'][$i]["Ref"];
                        }
                        if ($i == 0)
                            $city_arr[''] = $j ;
                        else
                            $city_arr[$jref] = $j ;
                    }
                    ?>
                    <p class="form-row form-row-wide" id="shipping_city_field">
                        <label class="" for="shipping_city"><?php _e('Town / City', 'novaposhta'); ?><abbr title="required" class="required">*</abbr></label>
                    <span>
                            <select name="shipping_city" class="select" id="shipping_city" placeholder="<?php _e('Town / City', 'novaposhta'); ?>">
                                <?php

                                foreach ($city_arr as $key => $value) {
                                    echo "<option data-index='$key' value='$value'>$value</option>";
                                }
                                ?>
                            </select>
                        </span>
                    </p>


                    <?php   continue;
                }
                elseif($key == 'shipping_np_number') {

                    $lang = get_lang();

                    ?>

                    <p class="form-row form-row-wide validate-required woocommerce-validated" id="shipping_np_number_field">
                        <label class="" id="lable_billing_np_number" for="billing_np_number"><?php _e('Warhouse NovaPoshta #', 'novaposhta'); ?>
                            <?php if ($request_np) {?> <abbr title="required" class="required">*</abbr> <?php }?>
                        </label>
                        <span>
                            <select class="chosen-select select " name="shipping_np_number" id="shipping_np_number">
                                <option value=""><?php _e('Select Warhouse NovaPoshta', 'novaposhta'); ?></option>
                                <?php

                                if ($city_ref){
                                    foreach ($result['data'] as $item) {
                                        $str1 = str_replace("\"", "", $item["Description".$lang]);
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
                else



                    ?>

                <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>

            <?php endforeach; ?>

            <?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

        </div>

    <?php endif; ?>

    <?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

    <?php if ( apply_filters( 'woocommerce_enable_order_notes_field', get_option( 'woocommerce_enable_order_comments', 'yes' ) === 'yes' ) ) : ?>

        <?php if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) : ?>

            <h3><?php _e( 'Additional information', 'woocommerce' ); ?></h3>

        <?php endif; ?>

        <?php foreach ( $checkout->checkout_fields['order'] as $key => $field ) : ?>

            <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>

        <?php endforeach; ?>

    <?php endif; ?>

    <?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</div>

<script type="text/javascript">
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var lang = "<?php echo get_lang();?>";
    jQuery(function ($) {

        $(document).ready(function () {
            var scity = '<?php echo get_user_meta( get_current_user_id(), 'shipping_city', true );?>';
            if(scity) {
                $('#shipping_city').val(scity);
                $('#shipping_city').trigger("chosen:updated");
            }


        })

        $(document).on('change', '#shipping_city', function () {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'np_api_get_cities_by_area',
                    city_ref: $('#shipping_city > option:selected').attr('data-index')
                },
                success: function (response) {
                    if (response.status) {
                        $('#shipping_np_number')
                            .find('option')
                            .remove();
                        $('#shipping_np_number')
                            .append($("<option></option>")
                                .attr("value", '')
                                .text('<?php _e('Select a warhouse', 'novaposhta'); ?>')
                            );
                        var keys = response.data.data;
                        for(var i = 0; i < keys.length; i++)
                        {
                            $('#shipping_np_number_field').prop('disabled', 'disabled');
                            $('#shipping_np_number')
                                .append($("<option></option>")
                                    .attr("value", keys[i]['Description'+lang])
                                    .text(keys[i]['Description'+lang])
                                );
                            $('#shipping_np_number_field').prop('disabled', false);
                        }
                        $('.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        })
    })
</script>

