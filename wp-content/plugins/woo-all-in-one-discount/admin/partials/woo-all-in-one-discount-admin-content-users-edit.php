<?php
/**
 * @var $users_rules
 */
global $wp_roles;
$discount_rule = false;
$product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
$extra_charges_rules = array();

foreach ($product_discount_rules as $rule_id => $rule_settings) {
    if ($rule_settings['type'] === 'extra_charge') {
        $extra_charges_rules[$rule_id] = $rule_settings;

        unset($product_discount_rules[$rule_id]);
    }
}

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
                                    if ('administrator' === $role_slug || 'subscriber' === $role_slug || 'customer' === $role_slug || in_array($role_slug, $access_settings_roles)) {
                                        continue;
                                    }
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
    if (!empty($extra_charges_rules)) {
        ?>
        <div id="extra_charge-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Extra charge', 'woo-all-in-one-discount'); ?></span></h2>

            <div class="inside">
                <form id="extra_charge_user_discount_settings">
                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_extra_charge"><?php _e('Assign Extra charge', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <select name="extra_charge" id="discount_extra_charge">
                                <option value=""><?php _e('-- Select Extra charge --', 'woo-all-in-one-discount'); ?></option>
                                <?php
                                $selected_extracharge = !empty($discount_rule['extra_charge']['extra_charge']) ? $discount_rule['extra_charge']['extra_charge'] : '';
                                foreach ($extra_charges_rules as $extra_charges_rule_id => $extra_charges_rule) {
                                    ?>
                                    <option value="<?php echo $extra_charges_rule_id ?>"<?php echo $selected_extracharge === $extra_charges_rule_id ? ' selected' : ''; ?>><?php echo $extra_charges_rule['title'] ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="wooaio-discount-item">
                        <div>

                        </div>

                        <div>
                            <button
                                    class="button update-user-submit"
                                    data-id="<?php echo $discount_id ?>"
                                    data-setting="extra_charge"
                                    data-form="extra_charge_user_discount_settings"
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

    <?php
    if (!empty($product_discount_rules)) {
        ?>
        <div id="base_discount-settings-container" class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Base Discount', 'woo-all-in-one-discount'); ?></span></h2>

            <div class="inside">
                <form id="base_discount_user_discount_settings">
                    <div class="wooaio-discount-item">
                        <div>
                            <label for="discount_base_discount"><?php _e('Assign Base Discount', 'woo-all-in-one-discount'); ?></label>
                        </div>
                        <div>
                            <select name="base_discount" id="discount_base_discount">
                                <option value=""><?php _e('-- Select Base Discount --', 'woo-all-in-one-discount'); ?></option>
                                <?php
                                $selected_base_discount = !empty($discount_rule['base_discount']['base_discount']) ? $discount_rule['base_discount']['base_discount'] : '';
                                foreach ($product_discount_rules as $product_discount_rule_id => $product_discount_rule) {
                                    ?>
                                    <option value="<?php echo $product_discount_rule_id ?>"<?php echo $selected_base_discount === $product_discount_rule_id ? ' selected' : ''; ?>><?php echo $product_discount_rule['title'] ?></option>
                                    <?php
                                }
                                ?>
                            </select>
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
    }
    ?>
</div>
