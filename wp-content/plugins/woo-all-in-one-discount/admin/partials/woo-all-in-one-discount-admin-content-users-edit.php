<?php
/**
 * @var $users_rules
 */
global $wp_roles;
$discount_rule = false;
$product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();

if (!empty($users_rules[$discount_id])) {
    $discount_rule = $users_rules[$discount_id];
}

var_dump($discount_rule);

if (!$discount_rule) {
    ?>
    <h3 class="wp-heading-inline">
        <?php echo sprintf( __('There is no user discount rule with ID %s', 'woo-all-in-one-discount'), $discount_id); ?>
    </h3>
    <?php

    return;
}
?>

<h3 class="wp-heading-inline">
    <?php _e('User Discount Rule:', 'woo-all-in-one-service'); ?> <?php echo $discount_rule['title'] ?>
</h3>

<hr class="wp-header-end">

<div id="poststuff">
    <div id="general-settings-container" class="postbox">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('General Discount Settings', 'woo-all-in-one-discount'); ?></span></h2>

        <div class="inside">
            <form id="general_user_discount_settings">
                <div class="wooaio-discount-item">
                    <div>
                        <label for="discount_title"><?php _e('Type', 'woo-all-in-one-discount'); ?></label>
                    </div>
                    <div>
                        <p><?php echo Woo_All_In_One_Discount_Rules::get_user_discounts_types()[$discount_rule['type']] ?></p>
                        <input type="hidden" id="discount_type" name="discount_type" value="<?php echo $discount_rule['type'] ?>">
                    </div>
                </div>

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
                        <label for="discount_priority"><?php _e('Priority', 'woo-all-in-one-discount'); ?></label>
                    </div>
                    <div>
                        <?php $priority = !empty($discount_rule['priority']) ? $discount_rule['priority'] : '10'; ?>
                        <input type="number" id="discount_priority" name="discount_priority" value="<?php echo $priority ?>">
                    </div>
                </div>

                <?php
                if ('user_roles' === $discount_rule['type']) {
                    $user_role = !empty($discount_rule['role']) ? $discount_rule['role'] : '';
                    ?>
                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_role"><?php _e('User role', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <select name="discount_role" id="discount_role">
                                <option value=""><?php _e('-- select user role --', 'woo-all-in-one-service'); ?></option>
                                <?php
                                foreach ($wp_roles->role_names as $role_slug => $role_label) {
                                    ?>
                                    <option value="<?php echo $role_slug ?>"<?php echo $user_role === $role_slug ? ' selected' : ''; ?>><?php echo $role_label ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="wooaio-discount-item">
                    <div>

                    </div>

                    <div>
                        <button
                            class="button update-user-submit"
                            data-id="<?php echo $discount_id ?>"
                            data-setting="general"
                            data-form="general_user_discount_settings"
                            type="button"
                        ><?php _e('Update', 'woo-all-in-one-discount'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php
    if (!empty($product_discount_rules)) {
        $after_discount_rule = array(
            'discount_id' => '',
            'discount_label' => '',
        );
        if (!empty($discount_rule['base_discount']) && is_array($discount_rule['base_discount'])) {
            $after_discount_rule = array_merge($after_discount_rule, $discount_rule['base_discount']);
        }
        ?>
        <div id="base_discount-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Base Price Rule', 'woo-all-in-one-discount'); ?></span></h2>

            <div class="inside">
                <form id="base_discount_user_discount_settings">
                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_base_discount"><?php _e('Assign Base Discount Rule', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <select name="discount_id" id="discount_base_discount">
                                <option value=""><?php _e('-- Select Base Rule --', 'woo-all-in-one-discount'); ?></option>
                                <?php
                                $selected_base_discount = !empty($discount_rule['base_discount']['discount_id']) ? $discount_rule['base_discount']['discount_id'] : '';
                                foreach ($product_discount_rules as $product_discount_rule_id => $product_discount_rule) {
                                    ?>
                                    <option value="<?php echo $product_discount_rule_id ?>"<?php echo $after_discount_rule['discount_id'] === $product_discount_rule_id ? ' selected' : ''; ?>>
                                        <?php echo $product_discount_rule['title'] ?> (<?php echo Woo_All_In_One_Discount_Rules::get_product_discounts_types()[$product_discount_rule['type']] ?>)
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_base_discount_label" style="margin-bottom: 5px;display:block;"><?php _e('Label for discounted price', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <div style="margin-bottom: 15px;">
                                <input id="discount_base_discount_label" type="text" name="discount_label" value="<?php echo $after_discount_rule['discount_label']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="wooaio-discount-item">
                        <div>

                        </div>

                        <div>
                            <button
                                    class="button update-user-submit"
                                    data-id="<?php echo $discount_id ?>"
                                    data-setting="base_discount"
                                    data-form="base_discount_user_discount_settings"
                                    type="button"
                            ><?php _e('Update', 'woo-all-in-one-discount'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $before_discount_rule = array(
            'show_before_discount' => 'no',
            'discount_id' => '',
            'discount_label' => '',
        );
        if (!empty($discount_rule['before_discount']) && is_array($discount_rule['before_discount'])) {
            $before_discount_rule = array_merge($before_discount_rule, $discount_rule['before_discount']);
        }
        ?>

        <div id="before_discount-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Before Discount Price Rule', 'woo-all-in-one-discount'); ?></span></h2>

            <div class="inside">
                <form id="before_discount_user_discount_settings">
                    <div class="wooaio-discount-item">
                        <div>
                            <label for="show_discount_before_discount"><?php _e('Show Before discount price', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <select name="show_before_discount" id="show_discount_before_discount">
                                <option value="yes"<?php echo $before_discount_rule['show_before_discount'] === 'yes' ? ' selected' : ''; ?>><?php _e('Yes', 'woo-all-in-one-discount'); ?></option>
                                <option value="no"<?php echo $before_discount_rule['show_before_discount'] === 'no' ? ' selected' : ''; ?>><?php _e('No', 'woo-all-in-one-discount'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_before_discount_id"><?php _e('Assign before discount rule', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <select name="discount_id" id="discount_before_discount_id">
                                <option value=""><?php _e('-- Select Before Discount Rule --', 'woo-all-in-one-discount'); ?></option>
                                <?php
                                foreach ($product_discount_rules as $product_discount_rule_id => $product_discount_rule) {
                                    ?>
                                    <option value="<?php echo $product_discount_rule_id ?>"<?php echo $before_discount_rule['discount_id'] === $product_discount_rule_id ? ' selected' : ''; ?>>
                                        <?php echo $product_discount_rule['title'] ?> (<?php echo Woo_All_In_One_Discount_Rules::get_product_discounts_types()[$product_discount_rule['type']] ?>)
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_price_before_discount_label"><?php _e('Label for before discounted price', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <input id="discount_price_before_discount_label" type="text" name="discount_label" value="<?php echo $before_discount_rule['discount_label']; ?>">
                        </div>
                    </div>

                    <div class="wooaio-discount-item">
                        <div>

                        </div>

                        <div>
                            <button
                                    class="button update-user-submit"
                                    data-id="<?php echo $discount_id ?>"
                                    data-setting="before_discount"
                                    data-form="before_discount_user_discount_settings"
                                    type="button"
                            ><?php _e('Update', 'woo-all-in-one-discount'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    ?>
</div>
