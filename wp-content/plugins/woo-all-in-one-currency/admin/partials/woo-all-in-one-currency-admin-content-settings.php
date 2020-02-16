<?php
$general_multicurrency_settings = Woo_All_In_One_Currency_Rules::get_general_currency_settings();
?>

<hr class="wp-header-end">

<div id="poststuff">
    <div id="general-currency-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('General Currency Settings', 'woo-all-in-one-currency'); ?></span></h2>

        <div class="inside">
            <form action="">
                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-sm-5 wooaio-col-md-3">
                        <label for="general_multicurrency_allow" style="font-weight: bold;font-size: 15px;">
                            <?php _e('Currency in cart', 'woo-all-in-one-currency'); ?>
                        </label>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-sm-7 wooaio-col-md-6">
                        <select name="general_multicurrency_allow" id="general_multicurrency_allow">
                            <option value="no"<?php echo $general_multicurrency_settings['multicurrency_allow'] === 'no' ? ' selected' : ''; ?>><?php _e('Only main currency', 'woo-all-in-one-currency'); ?></option>
                            <option value="yes"<?php echo $general_multicurrency_settings['multicurrency_allow'] === 'yes' ? ' selected' : ''; ?>><?php _e('Multi currency', 'woo-all-in-one-currency'); ?></option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
