<?php
// $coupon_id = Woo_All_In_One_Coupon_Model::generate_coupon();
// Woo_All_In_One_Coupon_Model::send_email_to_client($coupon_id, 'syntheticafreon@gmail.com');

$coupon_rule = Woo_All_In_One_Coupon_Model::get_coupon_rule();
?>
<h3 class="wp-heading-inline">
    <?php _e('Coupon settings', 'woo-all-in-one-coupon'); ?>
</h3>

<hr class="wp-header-end">

<div id="poststuff">
    <div id="general_coupon_settings_container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('General Coupon Settings', 'woo-all-in-one-coupon'); ?></span></h2>

        <div class="inside">
            <form id="general_coupon_settings_form">
                <input type="hidden" id="coupon_rule" name="coupon_rule" value="wooaiocoupon_rule_default">
                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4" style="margin-bottom: 10px;">
                        <?php _e('Coupon discount amount', 'woo-all-in-one-coupon'); ?>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <input type="number" id="coupon_amount" name="coupon_amount" value="<?php echo $coupon_rule['coupon_amount'] ?>">
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4" style="margin-bottom: 10px;">
                        <?php _e('Coupon minimum cart amount', 'woo-all-in-one-coupon'); ?>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <input type="number" id="minimum_amount" name="minimum_amount" value="<?php echo $coupon_rule['minimum_amount'] ?>">
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4" style="margin-bottom: 10px;">
                        <?php _e('Coupon expire after (days)', 'woo-all-in-one-coupon'); ?><br>
                        <small><?php _e('Left empty if you do not want to set expiration date', 'woo-all-in-one-coupon'); ?></small>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <input step="1" type="number" id="expiry_date" name="expiry_date" value="<?php echo $coupon_rule['expiry_date'] ?>">
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4" style="margin-bottom: 10px;">
                        <?php _e('Who can use coupon', 'woo-all-in-one-coupon'); ?>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <select name="customer_email" id="customer_email">
                            <option value="email"<?php echo 'email' === $coupon_rule['customer_email'] ? ' selected' : '' ?>>
                                <?php _e('Only customer with email submit', 'woo-all-in-one-coupon'); ?>
                            </option>
                            <option value="any"<?php echo 'any' === $coupon_rule['customer_email'] ? ' selected' : '' ?>>
                                <?php _e('Any who has coupon', 'woo-all-in-one-coupon'); ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4" style="margin-bottom: 10px;">
                        <?php _e('Form description', 'woo-all-in-one-coupon'); ?>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <textarea name="form_description" id="form_description" cols="46" rows="5"><?php echo $coupon_rule['form_description'] ?></textarea>
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4" style="margin-bottom: 10px;">
                        <?php _e('Email description', 'woo-all-in-one-coupon'); ?>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <textarea name="email_description" id="email_description" cols="46" rows="5"><?php echo $coupon_rule['email_description'] ?></textarea>
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4">

                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8" style="margin-bottom: 10px;">
                        <button
                                id="save_coupon_submit"
                                class="button save-coupon-submit"
                                type="button"
                        ><?php _e('Update', 'woo-all-in-one-coupon'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

coupon settings
