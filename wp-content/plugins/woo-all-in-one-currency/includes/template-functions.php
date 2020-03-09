<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (!function_exists('wooaiocurrency_currency_rate_item')) {
    function wooaiocurrency_currency_rate_item( $currency_code, $i, $categories, $products, $rule = array() ) {
        $currency_rules = Woo_All_In_One_Currency_Rules::get_all();
        $current_currency_rules = $currency_rules[$currency_code];
        $count_current_currency_rates = count($current_currency_rules["rates"]);
        $hide_delete_btn = $i === 0 && 1 < $count_current_currency_rates;
        $currency = get_option( 'woocommerce_currency' );
        $rule_products = !empty($current_currency_rules['products']) ? $current_currency_rules['products'] : array();
        $rule_categories = !empty($current_currency_rules['categories']) ? $current_currency_rules['categories'] : array();
        $rule_apply = !empty($rule['apply']) ? $rule['apply'] : 'all_products';
        $rule_apply_class = '';
        $rule_container_class = 'summary-view-item';
        $rule_rate = !empty($rule['rate']) ? $rule['rate'] : '';

        if (empty($rule)) {
            $rule_container_class = 'edit-view-item';
        }

        if ('specified_categories' === $rule_apply) {
            $rule_apply_class = ' apply_for_specified_categories';
        } elseif ('specified_products' === $rule_apply) {
            $rule_apply_class = ' apply_for_specified_products';
        }
        ?>
        <div class="wooaio-currency-item wooaio-currency-rate-item <?php echo $rule_container_class; ?>">
            <div class="wooaio-row">
                <div class="wooaio-col-xs-12 wooaio-col-sm-5 wooaio-col-md-3">
                    <div class="summary-container">
                        <label style="font-weight: bold;font-size: 15px;">
                            <?php echo $rule_rate;?>
                            <?php _e('For 1 ', 'woo-all-in-one-currency'); ?> <?php echo $currency_rules[$currency]['title']; ?>
                        </label>
                    </div>

                    <div class="edited-container">
                        <label for="">
                            <input type="number" step="0.01" name="rate[<?php echo $i ?>]" style="max-width: 80px;" value="<?php echo $rule_rate;?>">

                            <?php _e('For 1 ', 'woo-all-in-one-currency'); ?> <?php echo $currency_rules[$currency]['title']; ?>
                        </label>
                    </div>
                </div>

                <div class="wooaio-col-xs-12 wooaio-col-sm-7 wooaio-col-md-6">
                    <div class="summary-container">
                        <?php  ?>
                    </div>

                    <div class="edited-container">
                        <?php
                        // Show rule for all products only for first rule
                        if ($i === 0) {
                            ?>
                            <label for="all_products_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;">
                                <?php _e('For all products', 'woo-all-in-one-currency'); ?>
                                <input id="all_products_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="all_products"<?php echo 'all_products' === $rule_apply ? ' checked' : ''; ?>>
                            </label>
                            <?php
                        }
                        ?>

                        <label for="by_categories_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;<?php echo 0 === $i ? ' display:none;' : ''; ?>">
                            <?php _e('For specified categories', 'woo-all-in-one-currency'); ?>
                            <input id="by_categories_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="specified_categories"<?php echo 'specified_categories' === $rule_apply ? ' checked' : ''; ?>>
                        </label>

                        <label for="separate_products_<?php echo $i ?>" style="display: inline-block;margin-right: 10px;<?php echo 0 === $i ? ' display:none;' : ''; ?>">
                            <?php _e('For specified products', 'woo-all-in-one-currency'); ?>
                            <input id="separate_products_<?php echo $i ?>" type="radio" class="apply_for_radio" name="apply[<?php echo $i ?>]" value="specified_products"<?php echo 'specified_products' === $rule_apply ? ' checked' : ''; ?>>
                        </label>
                    </div>

                    <div class="apply_by_container edited-container<?php echo $rule_apply_class ?>">
                        <div class="specified_categories_container multiselect-container">
                            <div>
                                <?php
                                if (!empty($categories)) {
                                    ?>
                                    <ul>
                                        <?php
                                        foreach ($categories as $cat_id => $category) {
                                            wooaiocurrency_categories_tree( $i, $category, $rule_categories, $rule );
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
                                            wooaiocurrency_products_tree( $i, $product, $rule_products, $rule );
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
                    <?php
                    if (empty($rule)) {
                        ?>
                        <button type="button" class="currency-rate-item-create button button-primary button-small">
                            <?php _e('Create', 'woo-all-in-one-currency'); ?>
                        </button>

                        <button type="button" class="currency-rate-item-cancel button button-small">
                            <?php _e('Cancel', 'woo-all-in-one-currency'); ?>
                        </button>
                        <?php
                    } else {
                        ?>
                        <button type="button" class="currency-rate-item-edit button button-primary button-small">
                            <?php _e('Edit', 'woo-all-in-one-currency'); ?>
                        </button>

                        <button type="button" class="currency-rate-item-update button button-primary button-small">
                            <?php _e('Update', 'woo-all-in-one-currency'); ?>
                        </button>

                        <button type="button" class="currency-rate-item-change-cancel button button-small">
                            <?php _e('Cancel', 'woo-all-in-one-currency'); ?>
                        </button>

                        <?php
                        if (!$hide_delete_btn) {
                            ?>
                            <button
                                    type="button"
                                    class="currency-rate-item-change-delete button button-small"
                                    data-confirm="<?php echo __('Are you sure you want to delete this item for current currency?', 'woo-all-in-one-currency'); ?>"
                            >
                                <?php _e('Delete', 'woo-all-in-one-currency'); ?>
                            </button>
                            <?php
                        }
                        ?>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}


function wooaiocurrency_categories_tree( $i, $category, $rule_categories, $rule ) {
    $category_id = $category['category']->term_id;
    ?>
    <li>
        <fieldset>
            <?php
            if (!empty($rule["apply"]) && $rule["apply"] !== 'specified_categories') {
                if (in_array($category_id, $rule_categories)) {
                    ?>
                    <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>" disabled>
                    <?php
                } else {
                    ?>
                    <input id="category_<?php echo $i ?>_<?php echo $category_id; ?>" type="checkbox" name="categories[<?php echo $i ?>][]" value="<?php echo $category_id; ?>">
                    <?php
                }
            } else {
                if (in_array($category_id, $rule_categories)) {
                    if (in_array($category_id, $rule['categories'])) {
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
                    wooaiocurrency_categories_tree($i, $children_item, $rule_categories, $rule);
                }
                ?>
            </ul>
            <?php
        }
        ?>
    </li>
    <?php
}

function wooaiocurrency_products_tree( $i, $product, $rule_products, $rule ) {
    $product_id = $product->get_id();
    $product_type = $product->get_type();

    if ('grouped' !== $product_type) {
        if (!empty($rule["apply"]) && $rule["apply"] !== 'specified_products') {
            if (!in_array($product_id, $rule_products)) {
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
            if (in_array($product_id, $rule_products)) {
                if (in_array($product_id, $rule['products'])) {
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

function wooaiocurrency_get_current_currency() {
    global $wooaiocurrency_rules;

    if (!empty($wooaiocurrency_rules['current_currency_code'])) {
        return $wooaiocurrency_rules['current_currency_code'];
    }

    return get_option( 'woocommerce_currency' );
}

function wooaiocurrency_currency_switcher($args = array()) {
    global $wooaiocurrency_rules;

    if (empty($wooaiocurrency_rules["switcher"])) {
        return;
    }

    $currency = wooaiocurrency_get_current_currency();
    $base_currency = get_option( 'woocommerce_currency' );

    if (!empty($wooaiocurrency_rules["switcher"])) {
        ?>
        <li><a href="#"><?php echo $currency; ?></a>
            <ul id="currency-switcher" class="sub-small">
                <?php
                foreach ($wooaiocurrency_rules["switcher"] as $currency_code => $rule) {
                    if ((empty($rule['rates']) && $currency_code !== $base_currency) || $currency === $currency_code) {
                        continue;
                    }
                    ?><li><a class="change-currency" data-currency="<?php echo $currency_code ?>" href="#"><?php echo $currency_code ?></a></li><?php
                }
                ?>
            </ul>
        </li>
        <?php
    }
}

function wooaiocurrency_cart_product_price($product_price_html, $product) {
    $currency_rules = Woo_All_In_One_Currency_Rules::get_all();
    $currency = wooaiocurrency_get_current_currency();
    $base_currency = get_option( 'woocommerce_currency' );

    return $product_price_html;
}

function wooaiocurrency_before_calculate_totals($cart_object) {
    $currency_rules = Woo_All_In_One_Currency_Rules::get_all();
    $currency = wooaiocurrency_get_current_currency();
    $base_currency = get_option( 'woocommerce_currency' );

//    foreach ( $cart_object->get_cart() as $hash => $value ) {
//        $value['data']->set_price( 10 );
//    }

    return $cart_object;
}

function wooaiocurrency_set_currency_symbol() {
    add_filter('woocommerce_currency', 'wooaiocurrency_cart_currency', 1200);
}

function wooaiocurrency_reset_currency_symbol() {
    remove_filter('woocommerce_currency', 'wooaiocurrency_cart_currency', 1200);
}

function wooaiocurrency_cart_currency($currency) {
    return get_option( 'woocommerce_currency' );
}

function wooaiocurrency_before_mini_cart() {
    WC()->cart->calculate_totals();
}