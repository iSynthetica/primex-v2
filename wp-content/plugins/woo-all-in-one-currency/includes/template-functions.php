<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!function_exists('wooaiocurrency_currency_rate_item')) {
    function wooaiocurrency_currency_rate_item( $id, $i, $categories, $products, $discount_rule = array() ) {
        $discount_settings = Woo_All_In_One_Discount_Rules::get_product_discount($id);
        $discount_settings_products = array();
        $discount_settings_categories = array();

        if (!empty($discount_settings['products'])) {
            $discount_settings_products = array_keys($discount_settings['products']);
        }

        if (!empty($discount_settings['categories'])) {
            $discount_settings_categories = array_keys($discount_settings['categories']);
        }

        $discount_rule_amount = !empty($discount_rule['amount']) ? $discount_rule['amount'] : '';
        $discount_rule_apply = !empty($discount_rule['apply']) ? $discount_rule['apply'] : '';
        $discount_rule_apply_class = '';
        $discount_rule_container_class = 'wooaio-discount-summary-amount-item';

        if (empty($discount_rule)) {
            $discount_rule_container_class = 'wooaio-discount-edit-amount-item';
        }

        if ('by_categories' === $discount_rule_apply) {
            $discount_rule_apply_class = ' apply_by_categories';
        } elseif ('separate_products' === $discount_rule_apply) {
            $discount_rule_apply_class = ' apply_separate_products';
        }

        if ((int)$i === 0) {
            ?>
            <div class="wooaio-discount-item">
                <div style="margin-bottom: 10px;">
                    <label><?php _e('Discount amount (%)', 'woo-all-in-one-discount'); ?></label>
                </div>

                <div style="margin-bottom: 10px;">
                    <label><?php _e('Apply for', 'woo-all-in-one-discount'); ?></label>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="wooaio-discount-item wooaio-discount-amount-item <?php echo $discount_rule_container_class; ?>">
            <div>
                <div class="summary-container">
                    <p><?php echo $discount_rule_amount ?>%</p>
                </div>

                <div class="edited-container">
                    <input type="number" name="amount[<?php echo $i ?>]" value="<?php echo $discount_rule_amount ?>">
                </div>
            </div>

            <div class="apply_by_control_container">
                <div class="summary-container">
                    <p style="display: inline-block;margin-right: 10px;min-width: 250px;">
                    <?php
                    if ('all_products' === $discount_rule_apply) {
                        _e('All products', 'woo-all-in-one-discount');
                    } elseif ('by_categories' === $discount_rule_apply) {
                        _e('Products by categories', 'woo-all-in-one-discount');
                    } else {
                        _e('Separate products', 'woo-all-in-one-discount');
                    }
                    ?>
                    </p>

                    <button class="button button-small button-primary change-discount-amount" type="button"><?php _e('Change', 'woo-all-in-one-discount'); ?></button>
                    <button class="button button-small delete-discount-amount" type="button" data-id="<?php echo $id; ?>"><?php _e('Delete', 'woo-all-in-one-discount'); ?></button>
                </div>

                <div class="edited-container">
                    <label for="all_products_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                        <?php _e('All products', 'woo-all-in-one-discount'); ?>
                        <input id="all_products_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="all_products"<?php echo 'all_products' === $discount_rule_apply ? ' checked' : '' ?>>
                    </label>

                    <label for="by_categories_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                        <?php _e('Products by categories', 'woo-all-in-one-discount'); ?>
                        <input id="by_categories_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="by_categories"<?php echo 'by_categories' === $discount_rule_apply ? ' checked' : '' ?>>
                    </label>

                    <label for="separate_products_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                        <?php _e('Separate products', 'woo-all-in-one-discount'); ?>
                        <input id="separate_products_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="separate_products"<?php echo 'separate_products' === $discount_rule_apply ? ' checked' : '' ?>>
                    </label>

                    <?php
                    if (empty($discount_rule)) {
                        ?>
                        <button class="button button-small button-primary create-discount-amount" type="button" data-id="<?php echo $id; ?>"><?php _e('Create', 'woo-all-in-one-discount'); ?></button>
                        <button class="button button-small cancel-discount-amount" type="button"><?php _e('Cancel', 'woo-all-in-one-discount'); ?></button>
                        <?php
                    } else {
                        ?>
                        <button class="button button-small button-primary update-discount-amount" type="button" data-id="<?php echo $id; ?>"><?php _e('Update', 'woo-all-in-one-discount'); ?></button>
                        <button class="button button-small cancel-update-discount-amount" type="button"><?php _e('Cancel', 'woo-all-in-one-discount'); ?></button>
                        <?php
                    }
                    ?>
                </div>

                <div class="apply_by_container edited-container <?php echo $discount_rule_apply_class ?>">
                    <div class="by_categories_container multiselect-container">
                        <div>
                            <?php
                            if (!empty($categories)) {
                                ?>
                                <ul>
                                    <?php
                                    foreach ($categories as $cat_id => $category) {
                                        wooaiodiscount_categories_tree( $i, $category, $discount_settings_categories, $discount_rule );
                                    }
                                    ?>
                                </ul>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                    <div class="separate_products_container multiselect-container">
                        <div>
                            <?php
                            if (!empty($products)) {
                                ?>
                                <ul>
                                    <?php
                                    foreach ($products as $product) {
                                        wooaiodiscount_products_tree( $i, $product, $discount_settings_products, $discount_rule );
                                    }
                                    ?>
                                </ul>
                                <?php
                                //var_dump($categories);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

function wooaiocurrency_categories_tree( $i, $category, $discount_settings_categories, $discount_rule ) {
    $category_id = $category['category']->term_id;
    ?>
    <li>
        <fieldset>
            <?php
            if ($discount_rule["apply"] !== 'by_categories') {
                if (in_array($category_id, $discount_settings_categories)) {
                    ?>
                    <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>" disabled>
                    <?php
                } else {
                    ?>
                    <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>">
                    <?php
                }
            } else {
                if (in_array($category_id, $discount_settings_categories)) {
                    if (in_array($category_id, $discount_rule['categories'])) {
                        ?>
                        <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>" checked>
                        <?php
                    } else {
                        ?>
                        <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>" disabled>
                        <?php
                    }
                } else {
                    ?>
                    <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>">
                    <?php
                }
            }
            ?>
            <label for="category_<?php echo $i ?>_<?php echo $category_id; ?>"><?php echo $category['category']->name; ?></label>
        </fieldset>

        <?php
        if (!empty($category['children'])) {
            ?>
            <ul>
                <?php
                foreach ($category['children'] as $children_item) {
                    wooaiodiscount_categories_tree($i, $children_item, $discount_settings_categories, $discount_rule);
                }
                ?>
            </ul>
            <?php
        }
        ?>
    </li>
    <?php
}

function wooaiocurrency_products_tree( $i, $product, $discount_settings_products, $discount_rule ) {
    $product_id = $product->get_id();
    $product_type = $product->get_type();

    if ('grouped' !== $product_type) {
        if ($discount_rule["apply"] !== 'separate_products') {
            if (!in_array($product_id, $discount_settings_products)) {
                ?>
                <li>
                    <fieldset>
                        <input id="product_<?php echo $i ?>_<?php echo $product_id; ?>" type="checkbox" name="products[<?php echo $i ?>][]" value="<?php echo $product_id; ?>">
                        <label for="product_<?php echo $i ?>_<?php echo $product_id; ?>"><?php echo $product->get_name(); ?></label>
                    </fieldset>
                </li>
                <?php
            }
        } else {
            if (in_array($product_id, $discount_settings_products)) {
                if (in_array($product_id, $discount_rule['products'])) {
                    ?>
                    <li>
                        <fieldset>
                            <input id="product_<?php echo $i ?>_<?php echo $product_id; ?>" type="checkbox" name="products[<?php echo $i ?>][]" value="<?php echo $product_id; ?>" checked>
                            <label for="product_<?php echo $i ?>_<?php echo $product_id; ?>"><?php echo $product->get_name(); ?></label>
                        </fieldset>
                    </li>
                    <?php
                }
            } else {
                ?>
                <li>
                    <fieldset>
                        <input id="product_<?php echo $i ?>_<?php echo $product_id; ?>" type="checkbox" name="products[<?php echo $i ?>][]" value="<?php echo $product_id; ?>">
                        <label for="product_<?php echo $i ?>_<?php echo $product_id; ?>"><?php echo $product->get_name(); ?></label>
                    </fieldset>
                </li>
                <?php
            }
        }
    }
}