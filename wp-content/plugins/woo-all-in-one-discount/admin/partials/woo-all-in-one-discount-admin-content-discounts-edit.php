<?php
/**
 * @var $discount_rules
 */
$discount_rule = false;
$categories = Woo_All_In_One_Discount_Helpers::get_product_categories_tree();
$products = Woo_All_In_One_Discount_Helpers::get_products_tree();
$discount_types = Woo_All_In_One_Discount_Rules::get_product_discounts_types();

if (!empty($discount_rules[$discount_id])) {
    $discount_rule = $discount_rules[$discount_id];
}

// var_dump($discount_rule);

if (!$discount_rule) {
    ?>
    <h3 class="wp-heading-inline">
        <?php echo sprintf( __('There is no discount rule with ID %s', 'woo-all-in-one-discount'), $discount_id); ?>
    </h3>
    <?php

    return;
}
?>

<h3 class="wp-heading-inline">
    <?php _e('Product Discount Rule:', 'woo-all-in-one-service'); ?> <?php echo $discount_rule['title'] ?>
</h3>

<hr class="wp-header-end">

<div id="poststuff">
    <div id="general-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('General Discount Settings', 'woo-all-in-one-discount'); ?></span></h2>

        <div class="inside">
            <form id="general_product_discount_settings">
                <div class="wooaio-discount-item">
                    <div>
                        <label for="discount_title"><?php _e('Title', 'woo-all-in-one-discount'); ?></label>
                    </div>
                    <div>
                        <input type="text" id="discount_title" name="discount_title" value="<?php echo $discount_rule['title'] ?>">
                    </div>
                </div>

                <div class="wooaio-discount-item">
                    <div>
                        <label for="discount_description"><?php _e('Description', 'woo-all-in-one-discount'); ?></label>
                    </div>

                    <div>
                        <textarea name="discount_description" id="discount_description" rows="5"><?php echo $discount_rule['description'] ?></textarea>
                    </div>
                </div>

                <div class="wooaio-discount-item">
                    <div>
                        <label for="discount_type"><?php _e('Type', 'woo-all-in-one-discount'); ?></label>
                    </div>

                    <div>
                        <select name="discount_type" id="discount_type">
                            <?php
                            foreach ($discount_types as $discount_type_slug => $discount_type_label) {
                                ?>
                                <option value="<?php echo $discount_type_slug; ?>"<?php echo ($discount_type_slug === $discount_rule['type']) ? ' selected' : ''; ?>><?php echo $discount_type_label; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="wooaio-discount-item">
                    <div>
                        <label for="discount_priority"><?php _e('Priority', 'woo-all-in-one-discount'); ?></label>
                    </div>
                    <div>
                        <?php $priority = !empty($discount_rule['priority']) ? $discount_rule['priority'] : '10'; ?>
                        <input type="number" id="discount_priority" name="discount_priority" value="<?php echo $priority ?>">
                    </div>
                </div>

                <div class="wooaio-discount-item">
                    <div>

                    </div>

                    <div>
                        <button
                                id="update-discount-general_product_discount-submit"
                                class="button update-discount-submit"
                                data-id="<?php echo $discount_id ?>"
                                data-setting="general"
                                data-form="general_product_discount_settings"
                                type="button"
                        ><?php _e('Update', 'woo-all-in-one-discount'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="product-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('Products Discount Settings', 'woo-all-in-one-discount'); ?></span></h2>

        <div class="inside">
            <form id="price_product_discount_settings">
                <div id="price_product_discount_set">
                    <?php
                    $i = 0;
                    if (!empty($discount_rule['discounts'])) {
                        foreach ($discount_rule['discounts'] as $discount_amount_rule) {
                            wooaiodiscount_discount_setting_item( $discount_id, $i, $categories, $products, $discount_amount_rule );
                            $i++;
                        }
                    }
                    ?>
                </div>
                <div id="price_product_discount_set_action">
                    <button
                            id="add-discount-amount"
                            data-index="<?php echo $i; ?>"
                            data-id="<?php echo $discount_id ?>"
                            class="button button-primary"
                            type="button"
                    ><?php _e('Add discount amount', 'woo-all-in-one-discount'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php
    if (function_exists('run_woo_all_in_one_currency')) {
        $currency_rules = Woo_All_In_One_Currency_Rules::get_all();
        $discount_rule_currency = !empty($discount_rule['currency']) ? $discount_rule['currency'] : array();
        ?>
        <div id="before_discount-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Multicurrency rule', 'woo-all-in-one-discount'); ?></span></h2>

            <div class="inside">
                <?php
                if (!empty($currency_rules)) {
                    $categories = Woo_All_In_One_Currency_Helpers::get_product_categories_tree();
                    $products = Woo_All_In_One_Currency_Helpers::get_products_tree();

                    foreach ($currency_rules as $currency_code => $currency_rule) {
                        $current_currency_rule = !empty($discount_rule_currency[$currency_code]) ? $discount_rule_currency[$currency_code] : array('rates' => array(), 'categories' => array(), 'products' => array());
                        ?>
                        <form id="general_product_discount_currency_settings-form-<?php echo $currency_code; ?>" class="general_product_discount_currency_settings-form">
                            <div id="wooaio-discount-currency-item-<?php echo $currency_code; ?>" class="wooaio-discount-currency-item" data-id="<?php echo $discount_id ?>" data-currency-code="<?php echo $currency_code ?>">
                                <div class="wooaio-row">
                                    <div class="wooaio-col-xs-12 wooaio-col-sm-5 wooaio-col-md-2">
                                        <h3 style="margin-top: 0;margin-bottom: 10px;"><?php echo $currency_rule['title']; ?> (<?php echo $currency_code; ?>)</h3>
                                        <?php
                                        if (!empty($currency_rule['main'])) {
                                            ?>
                                            <p style="margin-top: 0;margin-bottom: 10px;">
                                                <strong><?php _e('Main site currency', 'woo-all-in-one-discount'); ?></strong>
                                            </p>
                                            <?php
                                        }
                                        ?>
                                    </div>

                                    <div class="wooaio-col-xs-12 wooaio-col-sm-7 wooaio-col-md-10">
                                        <div id="wooaio-discount-currency-item-<?php echo $currency_code; ?>-rates">
                                            <?php
                                            $i = 0;
                                            ?>
                                            <div class="wooaio-discount-currency-rate-items">
                                                <?php
                                                if (!empty($current_currency_rule['rates'])) {
                                                    foreach ($current_currency_rule['rates'] as $currency_rule_rate) {
                                                        wooaiodiscount_currency_rate_item( $currency_code, $i, $categories, $products, $currency_rule_rate, $discount_rule_currency );
                                                        $i++;
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <div class="wooaio-discount-currency-item-action">
                                                <div class="wooaio-row">
                                                    <div class="wooaio-col-xs-12">
                                                        <?php
                                                        if (!empty($current_currency_rule['rates'])) {
                                                            ?>
                                                            <button
                                                                    id="add-discount-currency-rate-<?php echo $currency_code ?>"
                                                                    data-index="<?php echo $i; ?>"
                                                                    data-id="<?php echo $discount_id ?>"
                                                                    data-currency-code="<?php echo $currency_code ?>"
                                                                    class="button button-primary add-discount-currency-rate"
                                                                    type="button"
                                                            ><?php _e('Add currency rate', 'woo-all-in-one-currency'); ?></button>
                                                            <?php
                                                        }
                                                        ?>

                                                        <button
                                                                id="copy-discount-currency-rate-<?php echo $currency_code ?>"
                                                                data-index="<?php echo $i; ?>"
                                                                data-id="<?php echo $discount_id ?>"
                                                                data-currency-code="<?php echo $currency_code ?>"
                                                                class="button button-primary copy-discount-currency-rate"
                                                                type="button"
                                                        ><?php _e('Copy from currency', 'woo-all-in-one-currency'); ?></button>

                                                        <?php
                                                        if (!empty($current_currency_rule['rates'])) {
                                                            ?>
                                                            <button
                                                                    id="delete-discount-currency-rate-<?php echo $currency_code ?>"
                                                                    data-index="<?php echo $i; ?>"
                                                                    data-id="<?php echo $discount_id ?>"
                                                                    data-currency-code="<?php echo $currency_code ?>"
                                                                    class="button button-primary delete-discount-currency-rate"
                                                                    type="button"
                                                            ><?php _e('Delete currency rule', 'woo-all-in-one-currency'); ?></button>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <hr>
                        <?php

                    }
                    ?>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>