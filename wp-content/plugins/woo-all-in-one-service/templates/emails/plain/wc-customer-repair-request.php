<?php
/**
 * @var $repair
 */
$repair_id = $repair['ID'];

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="  . PHP_EOL;
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo PHP_EOL . "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL  . PHP_EOL;
echo $repair_id;
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
