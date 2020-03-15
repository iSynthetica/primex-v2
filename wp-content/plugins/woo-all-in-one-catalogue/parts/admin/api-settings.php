<?php

?>
<h3 class="wp-heading-inline">
    <?php echo __('API Settings', 'woo-all-in-one-catalogue'); ?>
</h3>
<div id="poststuff">
    <div id="general-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('General API Settings', 'woo-all-in-one-catalogue'); ?></span></h2>

        <div class="inside">
            <div class="wooaio-row">
                <div class="wooaio-col-xs-12 wooaio-col-sm-5 wooaio-col-md-3">
                    <label for="general_multicurrency_allow" style="font-weight:bold;font-size:15px;margin: 10px 0;display:inline-block;">
                        <?php _e('Api URL', 'woo-all-in-one-catalogue'); ?>
                    </label>
                </div>
                <div class="wooaio-col-xs-12 wooaio-col-sm-7 wooaio-col-md-6">
                    <p style="margin: 10px 0">
                        <?php echo get_site_url('/'); ?>/wooaioc-api/1/xml/
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>