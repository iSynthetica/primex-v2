<?php
/**
 * @var $discount_rules
 */
$discount_rule = false;
$categories = Woo_All_In_One_Discount_Helpers::get_product_categories_tree();
$products = Woo_All_In_One_Discount_Helpers::get_products_tree();

if (!empty($discount_rules[$discount_id])) {
    $discount_rule = $discount_rules[$discount_id];
}

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
    if (false) {
        ?>
        <div id="user-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Users Discount Settings', 'woo-all-in-one-discount'); ?></span></h2>

            <div class="inside">
                <form id="user_product_discount_settings">

                </form>
            </div>
        </div>
        <?php
    }
    ?>
</div>