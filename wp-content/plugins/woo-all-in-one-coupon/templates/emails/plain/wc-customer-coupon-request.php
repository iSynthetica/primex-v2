<?php
/**
 * @var $coupon_id
 * @var $email_heading
 */

$coupon = new WC_Coupon( $coupon_id );
$coupon_code = $coupon->get_code();

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="  . PHP_EOL;
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo PHP_EOL . "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL  . PHP_EOL;

echo __('Coupon Code', 'woo-all-in-one-coupon') .': ' . $coupon_code . PHP_EOL;

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
