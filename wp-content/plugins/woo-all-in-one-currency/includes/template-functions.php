<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!function_exists('wooaiocurrency_currency_rate_item')) {
    function wooaiocurrency_currency_rate_item( $currency_code, $i, $categories, $products, $rule = array() ) {
        $currency_rules = Woo_All_In_One_Currency_Rules::get_all();
        $currency = get_woocommerce_currency();
        $currency_settings_products = array();
        $currency_settings_categories = array();
        $rule_apply = !empty($rule['apply']) ? $rule['apply'] : 'all_products';
        // $rule_apply = 'specified_categories';
        // $rule_apply = 'specified_products';

        $rule_apply_class = '';
        if ('specified_categories' === $rule_apply) {
            $rule_apply_class = ' apply_for_specified_categories';
        } elseif ('specified_products' === $rule_apply) {
            $rule_apply_class = ' apply_for_specified_products';
        }

        ?>
        <div class="wooaio-currency-item wooaio-currency-rate-item">
            <div class="wooaio-row">
                <div class="wooaio-col-xs-12 wooaio-col-sm-5 wooaio-col-md-3">
                    <label for="">
                        <input type="number" step="0.01" name="rate[<?php echo $i ?>]" style="max-width: 60px;">

                        <?php _e('For 1 ', 'woo-all-in-one-currency'); ?> <?php echo $currency_rules[$currency]['title']; ?>
                    </label>
                </div>

                <div class="wooaio-col-xs-12 wooaio-col-sm-7 wooaio-col-md-6">
                    <div class="summary-container">
                        <?php  ?>
                    </div>

                    <div class="edited-container">
                        <label for="all_products_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                            <?php _e('For all products', 'woo-all-in-one-currency'); ?>
                            <input id="all_products_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="all_products"<?php echo 'all_products' === $rule_apply ? ' checked' : ''; ?>>
                        </label>

                        <label for="by_categories_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                            <?php _e('For specified categories', 'woo-all-in-one-currency'); ?>
                            <input id="by_categories_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="specified_categories"<?php echo 'specified_categories' === $rule_apply ? ' checked' : ''; ?>>
                        </label>

                        <label for="separate_products_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                            <?php _e('For specified products', 'woo-all-in-one-currency'); ?>
                            <input id="separate_products_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="specified_products"<?php echo 'specified_products' === $rule_apply ? ' checked' : ''; ?>>
                        </label>
                    </div>
                    <?php
                    var_dump($currency_rules);
                    var_dump($currency);
                    ?>

                    <div class="apply_by_container edited-container<?php echo $rule_apply_class ?>">
                        <div class="specified_categories_container multiselect-container">
                            <div>
                                <?php
                                if (!empty($categories)) {
                                    ?>
                                    <ul>
                                        <?php
                                        foreach ($categories as $cat_id => $category) {
                                            wooaiodiscount_categories_tree( $i, $category, $currency_settings_categories, $discount_rule );
                                        }
                                        ?>
                                    </ul>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div class="specified_products_container multiselect-container">
                            <div>
                                <?php
                                if (!empty($products)) {
                                    ?>
                                    <ul>
                                        <?php
                                        foreach ($products as $product) {
                                            wooaiodiscount_products_tree( $i, $product, $currency_settings_products, $discount_rule );
                                        }
                                        ?>
                                    </ul>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wooaio-col-xs-12 wooaio-col-sm-7 wooaio-col-md-3">
                    <button class="currency-rate-item-cancel button button-small">
                        <?php _e('Cancel', 'woo-all-in-one-currency'); ?>
                    </button>
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