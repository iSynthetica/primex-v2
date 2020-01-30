<?php
$currency_rules = Woo_All_In_One_Currency_Rules::get_all();
$current_currency_rule = isset($currency_rules[$currency_id]) ? $currency_rules[$currency_id] : false;
$categories = Woo_All_In_One_Currency_Helpers::get_product_categories_tree();
$products = Woo_All_In_One_Currency_Helpers::get_products_tree();

wooaiocurrency_get_current_currency();

if (empty($current_currency_rule)) {
    ?>
    <h3 class="wp-heading-inline">
        <?php _e( 'No Currency Rule Found for', 'woo-all-in-one-currency' ); ?><?php echo $currency_id; ?>
    </h3>
    <?php

    return;
}
?>
<h3 class="wp-heading-inline">
    <?php _e('Currency Rule Edit', 'woo-all-in-one-currency'); ?>: <?php echo $current_currency_rule['title']; ?>
</h3>

<hr class="wp-header-end">

<?php var_dump($currency_rules); ?>

<div id="poststuff">
    <div id="general-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('General Currency Settings', 'woo-all-in-one-currency'); ?></span></h2>

        <div class="inside">

        </div>
    </div>

    <div id="currency-rate-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('Currency Rate Settings', 'woo-all-in-one-currency'); ?></span></h2>

        <div class="inside">
            <form id="wooaio-currency-rate-settings">
                <input type="hidden" name="currency_code" value="<?php echo $currency_id ?>">
                <?php
                $i = 0;
                ?>
                <div id="currency_rate_set">
                    <?php
                    if (!empty($current_currency_rule['rates'])) {
                        foreach ($current_currency_rule['rates'] as $currency_code => $currency_rule) {
                            wooaiocurrency_currency_rate_item( $currency_id, $i, $categories, $products, $currency_rule );
                            $i++;
                        }
                    }
                    ?>
                </div>

                <div id="currency_rate_set_action">
                    <div class="wooaio-row">
                        <div class="wooaio-col-xs-12">
                            <button
                                    id="add-currency-rate"
                                    data-index="<?php echo $i; ?>"
                                    data-id="<?php echo $currency_id ?>"
                                    class="button button-primary"
                                    type="button"
                            ><?php _e('Add currency rate', 'woo-all-in-one-currency'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
