<?php
function wooaiocoupon_form_submit() {
    $class = apply_filters('wooaiocoupon_form_submit_class', '');
    $text = apply_filters('wooaiocoupon_form_submit_text', __( 'Submit', 'woo-all-in-one-coupon' ));

    ?>
    <button type="button" class="button wooaiocoupon_submit <?php echo $class; ?>">
        <?php echo $text; ?>
    </button>
    <?php
}

add_action('wooaiocoupon_form_submit', 'wooaiocoupon_form_submit');