<?php
/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = ( 'billing' === $load_address ) ? __( 'Billing address', 'woocommerce' ) : __( 'Shipping address', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>

	<form method="post">

		<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3>

		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="woocommerce-address-fields__field-wrapper">
				<?php foreach ( $address as $key => $field ) : ?>
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
					} elseif ($key == 'shipping_city') {
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
						<p class="form-row form-row-wide  validate-required woocommerce-validated" id="shipping_city_field">
							<label class="" for="shipping_city"><?php _e('Town / City','novaposhta'); ?><abbr title="required"
							                                                                                 class="required">*</abbr></label>
                    <span>
                            <select name="shipping_city" class="select" id="shipping_city"
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
					} elseif (($key == 'billing_np_number')) {

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
					} else

					woocommerce_form_field( $key, $field, ! empty( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : $field['value'] ); ?>
				<?php endforeach; ?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			<p>
				<input type="submit" class="button" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>" />
				<?php wp_nonce_field( 'woocommerce-edit_address' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</p>
		</div>

	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>

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



		$(document).on('change', '#shipping_city', function () {
			/*console.log(chosen_shipping_rates);*/

 			$('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
				$('body').trigger('update_checkout');
				ger_warhouse();

		})
		$(document).on('change', '#billing_city', function () {
				$('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
				$('body').trigger('update_checkout');
				ger_warhouse();

		})
		$("#ship-to-different-address-checkbox").change(function () {
			if(chosen_shipping_rates == 'novaposhta') {
				$('#billing_np_number').prop('disabled', true).trigger("chosen:updated");
				ger_warhouse();
			}
		})



		function ger_warhouse() {
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
					//warhouse: billing_np_number
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
									.attr("value", keys[i]['Description' + lang])
									.text(keys[i]['Description' + lang]+type_wh)
								);
							$('#billing_np_number_field').prop('disabled', false);
						}
						$("#billing_np_number").prop('disabled', false).trigger('chosen:updated');
						$("#billing_np_number").on('click');
						//$('.chosen-select').trigger("chosen:updated");
					}
				}
			});
		}
	})
</script>