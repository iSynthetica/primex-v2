<?php
/**
 * Checkout terms and conditions area.
 *
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( apply_filters( 'woocommerce_checkout_show_terms', true ) && function_exists( 'wc_terms_and_conditions_checkbox_enabled' ) ) {
	do_action( 'woocommerce_checkout_before_terms_and_conditions' );

	?>
	<div class="woocommerce-terms-and-conditions-wrapper">
        <p class="woocommerce-terms-and-conditions-link">
            <a href="<?php echo home_url('/') . 'payment-delivery'; ?>"><?php echo __('Shipping and Payment', 'snthwp'); ?></a>
        </p>

        <p class="woocommerce-terms-and-conditions-link">
            <a href="<?php echo home_url('/') . 'privacy-policy'; ?>"><?php echo __('Privacy Policy', 'snthwp'); ?></a>
        </p>
	</div>
	<?php

	do_action( 'woocommerce_checkout_after_terms_and_conditions' );
}
