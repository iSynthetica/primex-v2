<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!function_exists('wooaiodiscount_discount_setting_item')) {
    function wooaiodiscount_discount_setting_item( $id, $i, $discount_rule = array() ) {
        $discount_rule_amount = !empty($discount_rule['amount']) ? $discount_rule['amount'] : '';
        $discount_rule_apply = !empty($discount_rule['apply']) ? $discount_rule['apply'] : '';
        ?>
        <div class="wooaio-discount-item wooaio-discount-amount-item">
            <div>
                <?php
                if ($i === 0) {
                    ?>
                    <div style="margin-bottom: 10px;">
                        <label><?php _e('Discount amount (%)', 'woo-all-in-one-discount'); ?></label>
                    </div>
                    <?php
                }
                ?>
                <div>
                    <input type="number" name="amount[<?php echo $i ?>]" value="<?php echo $discount_rule_amount ?>">
                </div>
            </div>

            <div>
                <?php
                if ($i === 0) {
                    ?>
                    <div style="margin-bottom: 10px;">
                        <label for="discount_description"><?php _e('Apply for', 'woo-all-in-one-discount'); ?></label>
                    </div>
                    <?php
                }
                ?>

                <div>
                    <label for="all_products" style="display: inline-block;margin-right: 10px;">
                        <?php _e('All products', 'woo-all-in-one-discount'); ?>
                        <input id="all_products" type="radio" name="apply[<?php echo $i ?>]" value="all_products"<?php echo 'all_products' === $discount_rule_apply ? ' checked' : '' ?>>
                    </label>

                    <label for="all_products" style="display: inline-block;margin-right: 10px;">
                        <?php _e('Products by categories', 'woo-all-in-one-discount'); ?>
                        <input id="all_products" type="radio" name="apply[<?php echo $i ?>]" value="by_categories"<?php echo 'by_categories' === $discount_rule_apply ? ' checked' : '' ?>>
                    </label>

                    <label for="all_products" style="display: inline-block;margin-right: 10px;">
                        <?php _e('Separate products', 'woo-all-in-one-discount'); ?>
                        <input id="all_products" type="radio" name="apply[<?php echo $i ?>]" value="separate_products"<?php echo 'separate_products' === $discount_rule_apply ? ' checked' : '' ?>>
                    </label>

                    <?php
                    if (empty($discount_rule)) {
                        ?>
                        <button class="button button-small button-primary create-discount-amount" type="button" data-id="<?php echo $id; ?>"><?php _e('Create', 'woo-all-in-one-discount'); ?></button>
                        <button class="button button-small cancel-discount-amount" type="button"><?php _e('Cancel', 'woo-all-in-one-discount'); ?></button>
                        <?php
                    } else {
                        ?>
                        <button class="button button-small button-primary change-discount-amount" type="button"><?php _e('Change', 'woo-all-in-one-discount'); ?></button>
                        <button class="button button-small delete-discount-amount" type="button" data-id="<?php echo $id; ?>"><?php _e('Delete', 'woo-all-in-one-discount'); ?></button>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}