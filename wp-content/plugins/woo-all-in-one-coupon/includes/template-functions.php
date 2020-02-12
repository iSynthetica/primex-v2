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

function wooaiocoupon_form_messages_container() {
    ?>
    <div class="wooaiocoupon_messages">

    </div>
    <?php
}

function wooaiocoupon_get_form_messages($messages, $status = 'success') {
    if (empty($messages)) {
        return '';
    }

    ob_start();
    do_action('wooaiocoupon_form_before_message');
    do_action('wooaiocoupon_form_before_' . $status . '_message');
    foreach ($messages as $message) {
        ?>
        <p><?php echo $message; ?></p>
        <?php
    }
    do_action('wooaiocoupon_form_after_' . $status . '_message');
    do_action('wooaiocoupon_form_after_message');
    return ob_get_clean();
}

function wooaiocoupon_form_before_success_message() {
    ?>
    <div class="wooaiocoupon_success_message">
    <?php
}

function wooaiocoupon_form_before_error_message() {
    ?>
    <div class="wooaiocoupon_error_message">
    <?php
}

function wooaiocoupon_form_after_message() {
    ?>
    </div>
    <?php
}

add_action('wooaiocoupon_form_before_success_message', 'wooaiocoupon_form_before_success_message', 15);
add_action('wooaiocoupon_form_after_success_message', 'wooaiocoupon_form_after_message', 15);

add_action('wooaiocoupon_form_before_error_message', 'wooaiocoupon_form_before_error_message', 15);
add_action('wooaiocoupon_form_after_error_message', 'wooaiocoupon_form_after_message', 15);