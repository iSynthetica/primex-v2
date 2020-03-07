<?php
$statuses = wc_get_order_statuses();
$turbosms_settings = Woo_All_In_One_Turbosms_Helpers::get_settings();
if (!empty($turbosms_settings['login']) && !empty($turbosms_settings['password'])) {
    $client = new Woo_All_In_One_Turbosms_API;
    $auths_status = $client->getAuthStatus();

    if ('Вы успешно авторизировались' !== $auths_status->AuthResult) { // Authorization failed
        $balance = $auths_status->AuthResult;
    } else {
        $balance = $client->getBalance();

        if (!$balance) {
            $balance = 'Server Error';
        } else {
            $balance = $balance->GetCreditBalanceResult;
        }
    }

//    var_dump($client->getAuthStatus());
//    var_dump($statuses);
    
}

// phpinfo();
?>

<h3 class="wp-heading-inline">
    <?php _e('General settings', 'woo-all-in-one-turbosms'); ?>
</h3>

<hr class="wp-header-end">

<?php // var_dump($turbosms_settings); ?>

<div id="poststuff">
    <form id="wooaio-turbosms-general-settings">
        <div id="general-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('General Settings', 'woo-all-in-one-turbosms'); ?></span></h2>

            <div class="inside">
                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-4 wooaio-col-lg-3" style="margin-bottom: 10px;">
                        <label for="turbosms_sender"><?php _e('TurboSMS Sender Name', 'woo-all-in-one-turbosms'); ?></label>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-8 wooaio-col-lg-9" style="margin-bottom: 10px;">
                        <input type="text" id="turbosms_sender" name="sender" value="<?php echo $turbosms_settings['sender'] ?>">
                        <?php
                        if (empty($turbosms_settings['password'])) {
                            ?>
                            <p><?php _e('Enter TurboSMS sender name which client will see in sms', 'woo-all-in-one-turbosms'); ?></p>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-4 wooaio-col-lg-3" style="margin-bottom: 10px;">
                        <label for="turbosms_login"><?php _e('TurboSMS Login', 'woo-all-in-one-turbosms'); ?></label>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-8 wooaio-col-lg-9" style="margin-bottom: 10px;">
                        <input type="text" id="turbosms_login" name="login" value="<?php echo $turbosms_settings['login'] ?>">
                        <?php
                        if (empty($turbosms_settings['password'])) {
                            ?>
                            <p><?php _e('Enter TurboSMS login', 'woo-all-in-one-turbosms'); ?></p>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-4 wooaio-col-lg-3" style="margin-bottom: 10px;">
                        <label for="turbosms_login"><?php _e('TurboSMS Password', 'woo-all-in-one-turbosms'); ?></label>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-8 wooaio-col-lg-9" style="margin-bottom: 10px;">
                        <input type="text" id="turbosms_password" name="password" value="<?php echo $turbosms_settings['password'] ?>">
                        <?php
                        if (empty($turbosms_settings['password'])) {
                            ?>
                            <p><?php _e('Enter TurboSMS password', 'woo-all-in-one-turbosms'); ?></p>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <?php
                if (!empty($turbosms_settings['login']) && !empty($turbosms_settings['password'])) {
                    ?>
                    <div class="wooaio-row">
                        <div class="wooaio-col-xs-12 wooaio-col-md-4 wooaio-col-lg-3" style="margin-bottom: 10px;">
                            <label for="turbosms_login"><?php _e('TurboSMS balance', 'woo-all-in-one-turbosms'); ?></label>
                        </div>
                        <div class="wooaio-col-xs-12 wooaio-col-md-8 wooaio-col-lg-9" style="margin-bottom: 10px;">
                            <strong><?php echo $balance; ?></strong>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-4 wooaio-col-lg-3" style="margin-bottom: 10px;">
                        
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-8 wooaio-col-lg-9" style="margin-bottom: 10px;">
                        
                        <button
                            id="turbosms-general-settings-submit"
                            class="button turbosms-settings-submit"
                            data-setting="general"
                            data-form="wooaio-turbosms-general-settings"
                            type="button"
                        ><?php _e('Update', 'woo-all-in-one-turbosms'); ?></button>
                    </div>
                </div>
            </div>
        </div>


        <div id="general-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('SMS Text Settings', 'woo-all-in-one-turbosms'); ?></span></h2>

            <div class="inside">
            <div class="wooaio-row">
                <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-8">

                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12" style="margin-bottom: 10px;">
                        <h3 style="margin-top: 10px;margin-bottom: 5px;"><?php _e('New order', 'woo-all-in-one-turbosms'); ?></label>
                    </div>
                </div>
                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-6" style="margin-bottom: 10px;">
                        <label for="turbosms_send_new_order"><?php _e('Send SMS when order created', 'woo-all-in-one-turbosms'); ?></label>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6" style="margin-bottom: 10px;">
                        <select name="send_new_order" id="turbosms_send_new_order" class="turbosms_send_enable" data-container="<?php echo 'new_order' ?>">
                            <option value="yes"<?php echo 'yes' === $turbosms_settings['send_new_order'] ? ' selected' : '' ?>><?php echo __('Yes', 'woo-all-in-one-turbosms'); ?></option>
                            <option value="no"<?php echo 'no' === $turbosms_settings['send_new_order'] ? ' selected' : '' ?>><?php echo __('No', 'woo-all-in-one-turbosms'); ?></option>
                        </select>
                    </div>
                </div>

                <div id="turbosms_text_container_new_order" class="wooaio-row"<?php echo 'no' === $turbosms_settings['send_new_order'] ? ' style="display:none;"' : '' ?>>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6">
                        <label for="turbosms_text_new_order"><?php _e('SMS Text for new order', 'woo-all-in-one-turbosms'); ?></label>
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-6">
                        <textarea id="turbosms_text_new_order" name="text_new_order" id="" rows="4" style="width:90%;"><?php echo $turbosms_settings['text_new_order'] ?></textarea>
                    </div>
                </div>

                <hr>

                <?php
                if (!empty($statuses)) {
                    foreach ($statuses as $key => $status) {
                        $key = 'wc-' === substr( $key, 0, 3 ) ? substr( $key, 3 ) : $key;
                        $send_status = 'no';
                        $send_text = Woo_All_In_One_Turbosms_Helpers::get_order_status_changed_text();

                        if (!empty($turbosms_settings['send_order_status_' . $key])) {
                            $send_status = $turbosms_settings['send_order_status_' . $key];
                        }

                        if (isset($turbosms_settings['text_order_status_' . $key])) {
                            $send_text = $turbosms_settings['text_order_status_' . $key];
                        }
                        
                        ?>
                        <div class="wooaio-row">
                            <div class="wooaio-col-xs-12" style="margin-bottom: 10px;">
                                <h3 style="margin-top: 5px;margin-bottom: 5px;"><?php _e('Order status', 'woo-all-in-one-turbosms'); ?>: <?php echo $status; ?></label>
                            </div>
                        </div>
                        <div class="wooaio-row">
                            <div class="wooaio-col-xs-12 wooaio-col-md-6" style="margin-bottom: 10px;">
                                <label for="turbosms_send_order_status_<?php echo $key ?>"><?php echo __('Send SMS when order changed status to:', 'woo-all-in-one-turbosms') . ' <strong>' . $status . '</strong>'; ?></label>
                            </div>
                            <div class="wooaio-col-xs-12 wooaio-col-md-6" style="margin-bottom: 10px;">
                                <select name="send_order_status_<?php echo $key ?>" id="turbosms_send_order_status_<?php echo $key ?>" class="turbosms_send_enable" data-container="<?php echo $key ?>">
                                    <option value="yes"<?php echo 'yes' === $send_status ? ' selected' : '' ?>><?php echo __('Yes', 'woo-all-in-one-turbosms'); ?></option>
                                    <option value="no"<?php echo 'no' === $send_status ? ' selected' : '' ?>><?php echo __('No', 'woo-all-in-one-turbosms'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div id="turbosms_text_container_<?php echo $key ?>"  class="wooaio-row"<?php echo 'no' === $send_status ? ' style="display:none;"' : '' ?>>
                            <div class="wooaio-col-xs-12 wooaio-col-md-6">
                                <label for="turbosms_text_order_status_<?php echo $key ?>"><?php echo __('SMS Text when order status changed to:', 'woo-all-in-one-turbosms') . ' <strong>' . $status . '</strong>'; ?></label>
                            </div>
                            <div class="wooaio-col-xs-12 wooaio-col-md-6">
                                <textarea id="turbosms_text_order_status_<?php echo $key ?>" name="text_order_status_<?php echo $key ?>" id="" rows="4" style="width:90%;"><?php echo $send_text ?></textarea>
                            </div>
                        </div>

                        <hr>
                        <?php
                    }
                }
                ?>
                </div>

                <div class="wooaio-col-xs-12 wooaio-col-md-6 wooaio-col-lg-4">
                    <div class="wooaio-row">
                        <div class="wooaio-col-xs-12" style="margin-bottom: 10px;">
                            <p>
                                <?php echo __('Available shortcodes', 'woo-all-in-one-turbosms'); ?>
                            </p>

                            <ul>
                                <li><strong>{{order_number}}</strong>: <?php echo __('Order number', 'woo-all-in-one-turbosms'); ?></li>
                                <li><strong>{{order_date}}</strong>: <?php echo __('Order created date', 'woo-all-in-one-turbosms'); ?></li>
                                <li><strong>{{order_total}}</strong>: <?php echo __('Order total amount', 'woo-all-in-one-turbosms'); ?></li>
                                <li><strong>{{order_status}}</strong>: <?php echo __('Current order status', 'woo-all-in-one-turbosms'); ?></li>
                                <li><strong>{{old_order_status}}</strong>: <?php echo __('Old order status', 'woo-all-in-one-turbosms'); ?> - <?php echo __('(Only for changed status SMS)', 'woo-all-in-one-turbosms'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


                <div class="wooaio-row">
                    <div class="wooaio-col-xs-12 wooaio-col-md-4 wooaio-col-lg-3" style="margin-bottom: 10px;">
                        
                    </div>
                    <div class="wooaio-col-xs-12 wooaio-col-md-8 wooaio-col-lg-9" style="margin-bottom: 10px;">
                        
                        <button
                            id="turbosms-sms-settings-submit"
                            class="button turbosms-settings-submit"
                            data-setting="general"
                            data-form="wooaio-turbosms-general-settings"
                            type="button"
                        ><?php _e('Update', 'woo-all-in-one-turbosms'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>