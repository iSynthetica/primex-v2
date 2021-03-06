<?php


class Woo_All_In_One_Discount_Rules {
    public static function get_product_discounts_statuses() {
        return array(
            'draft' => __('Draft', 'woo-all-in-one-discount'),
            'published' => __('Published', 'woo-all-in-one-discount'),
        );
    }

    public static function get_product_discounts_types() {
        return array(
            'discount' => __('Discount', 'woo-all-in-one-discount'),
            'extra_charge' => __('Extra charge', 'woo-all-in-one-discount'),
        );
    }

    public static function get_product_discounts() {
        return get_option('wooaio_product_discount_rules', array());
    }

    public static function get_product_discount($id) {
        $rules = Woo_All_In_One_Discount_Rules::get_product_discounts();

        if (empty($rules[$id])) {
            return false;
        }

        return $rules[$id];
    }

    public static function create_product_discount($data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $id = wp_generate_uuid4();
        $priority = !empty($data['discount_priority']) ? sanitize_text_field($data['discount_priority']) : 10;

        $result = array(
            'error' => '',
            'id' => $id
        );

        $new_rule = array(
            'title' => sanitize_text_field($data['discount_title']),
            'type' => sanitize_text_field($data['discount_type']),
            'priority' => $priority,
            'description' => sanitize_textarea_field($data['discount_description']),
            'status' => 'draft',
        );

        $product_discount_rules[$id] = $new_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_product_discount($id, $setting, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();

        if (!isset($product_discount_rules[$id])) {
            $result = array(
                'error' => sprintf( __('There is no discount rule with ID %s', 'woo-all-in-one-discount'), $id),
                'id' => $id
            );

            return $result;
        }

        switch ($setting) {
            case 'general':
                return self::update_general_product_discount($id, $data);
            case 'currency':
                return self::update_currency_product_discount($id, $data);
            case 'discounts':
                return self::update_amount_product_discount($id, $data);
            case 'products':
                return self::update_product_set_discount($id, $data);
            case 'categories':
                return self::update_category_set_discount($id, $data);
        }

        $result = array(
            'error' => __('Something went wrong', 'woo-all-in-one-discount'),
            'id' => $id
        );

        return $result;
    }

    public static function update_general_product_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];
        $priority = !empty($data['discount_priority']) ? sanitize_text_field($data['discount_priority']) : 10;

        $result = array(
            'error' => '',
            'id' => $id
        );

        $product_discount_rule['title'] = sanitize_text_field($data['discount_title']);
        $product_discount_rule['type'] = sanitize_text_field($data['discount_type']);
        $product_discount_rule['priority'] = $priority;
        $product_discount_rule['description'] = sanitize_text_field($data['discount_description']);

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_amount_product_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array(
            'error' => '',
            'id' => $id
        );

        $product_discount_rule['discounts'] = $data;

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_currency_product_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array(
            'error' => '',
            'id' => $id
        );

        $product_discount_rule['currency'] = $data;

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function delete_product_discount_amount($id) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array(
            'error' => '',
            'id' => $id
        );

        if (!empty($product_discount_rule['discounts'])) {
            unset($product_discount_rule['discounts']);
        }

        if (!empty($product_discount_rule['categories'])) {
            unset($product_discount_rule['categories']);
        }

        if (!empty($product_discount_rule['products'])) {
            unset($product_discount_rule['products']);
        }

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_product_set_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array(
            'error' => '',
            'id' => $id
        );

        $product_discount_rule['products'] = $data;

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_category_set_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array(
            'error' => '',
            'id' => $id
        );

        $product_discount_rule['categories'] = $data;

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_product_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function delete_product_discount($id) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_product_discounts();

        $result = array(
            'error' => '',
            'id' => $id
        );

        if (!isset($product_discount_rules[$id])) {
            $result['error'] = sprintf( __('There is no discount rule with ID %s', 'woo-all-in-one-discount'), $id);

            return $result;
        }

        unset ($product_discount_rules[$id]);

        if (empty(count($product_discount_rules))) {
            delete_option('wooaio_product_discount_rules');
        } else {
            update_option('wooaio_product_discount_rules', $product_discount_rules);
        }

        return $result;
    }

    public static function get_user_discounts_types() {
        return array(
            'all_users' => __('All Users', 'woo-all-in-one-discount'),
            'unregistered_users' => __('Unregistered Users', 'woo-all-in-one-discount'),
            'registered_users' => __('Registered Users', 'woo-all-in-one-discount'),
            'user_roles' => __('User Roles', 'woo-all-in-one-discount'),
        );
    }

    public static function get_user_discounts() {
        return get_option('wooaio_user_discount_rules', array());
    }

    public static function create_user_discount($data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $id = wp_generate_uuid4();
        $priority = !empty($data['discount_priority']) ? sanitize_text_field($data['discount_priority']) : 10;

        $result = array(
            'error' => '',
            'id' => $id
        );

        $new_rule = array(
            'title' => sanitize_text_field($data['discount_title']),
            'type' => sanitize_text_field($data['discount_type']),
            'priority' => $priority,
            'description' => sanitize_textarea_field($data['discount_description']),
            'status' => 'draft',
        );

        $product_discount_rules[$id] = $new_rule;

        update_option('wooaio_user_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_user_discount($id, $setting, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();

        if (!isset($product_discount_rules[$id])) {
            $result = array(
                'error' => sprintf( __('There is no discount rule with ID %s', 'woo-all-in-one-discount'), $id),
                'id' => $id
            );

            return $result;
        }

        switch ($setting) {
            case 'general':
                return self::update_general_user_discount($id, $data);
            case 'base_discount':
                return self::update_base_discount_user_discount($id, $data);
            case 'before_discount':
                return self::update_before_discount_user_discount($id, $data);
        }

        $result = array(
            'error' => __('Something went wrong', 'woo-all-in-one-discount'),
            'id' => $id
        );

        return $result;
    }

    public static function update_general_user_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $product_discount_rule = $product_discount_rules[$id];
        $priority = !empty($data['discount_priority']) ? sanitize_text_field($data['discount_priority']) : 10;
        $result = array( 'error' => '', 'id' => $id );

        $product_discount_rule['title'] = sanitize_text_field($data['discount_title']);
        $product_discount_rule['type'] = sanitize_text_field($data['discount_type']);
        $product_discount_rule['priority'] = $priority;
        $product_discount_rule['description'] = sanitize_text_field($data['discount_description']);

        if ($data['discount_type'] === 'user_roles') {
            $discount_role = !empty($data["discount_role"]) ? sanitize_text_field($data["discount_role"]) : '';
            $product_discount_rule['role'] = $discount_role;
        } else {
            if (!empty($product_discount_rule['role'])) {
                unset($product_discount_rule['role']);
            }
        }

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_user_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_base_discount_user_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array( 'error' => '', 'id' => $id );

        $product_discount_rule['base_discount'] = $data;

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_user_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function update_before_discount_user_discount($id, $data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $product_discount_rule = $product_discount_rules[$id];

        $result = array( 'error' => '', 'id' => $id );

        $product_discount_rule['before_discount'] = $data;

        $product_discount_rules[$id] = $product_discount_rule;

        update_option('wooaio_user_discount_rules', $product_discount_rules);

        return $result;
    }

    public static function delete_user_discount($id) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();

        $result = array(
            'error' => '',
            'id' => $id
        );

        if (!isset($product_discount_rules[$id])) {
            $result['error'] = sprintf( __('There is no user rule with ID %s', 'woo-all-in-one-discount'), $id);

            return $result;
        }

        unset ($product_discount_rules[$id]);

        if (empty(count($product_discount_rules))) {
            delete_option('wooaio_user_discount_rules');
        } else {
            update_option('wooaio_user_discount_rules', $product_discount_rules);
        }

        return $result;
    }

    public static function get_price($product_price, $product) {
        global $wooaiodiscount_current_user_rule;
        $discount = 0;
        $all_products_discount = null;
        $category_discount = null;
        $product_discount = null;
        $discount_type = 'extra_charge';
        $product_type = $product->get_type();

        if ('variation' === $product_type) {
            $_product = wc_get_product( $product->get_parent_id() );
            $product_id = $_product->get_id();
        } else {
            $product_id = $product->get_id();
        }


        $rule = $wooaiodiscount_current_user_rule;
        $product_cats_ids = wc_get_product_term_ids( $product_id, 'product_cat' );

        if (!empty($rule['base_discount']['discount'])) {
            $discount_type = $rule["base_discount"]["type"];
            foreach ($rule['base_discount']['discount'] as $discount_rule) {
                if ($discount_rule['apply'] === 'all_products') {
                    $all_products_discount = $discount_rule['amount'];
                } elseif ($discount_rule['apply'] === 'by_categories') {

                } elseif ($discount_rule['apply'] === 'separate_products') {
                    if (in_array($product_id, $discount_rule['products'])) {
                        $product_discount = $discount_rule['amount'];
                    }
                }
            }
        }

        if (null !==  $all_products_discount) {
            $discount = $all_products_discount;
        }

        if (null !==  $category_discount) {
            $discount = $category_discount;
        }

        if (null !==  $product_discount) {
            $discount = $product_discount;
        }

        if ('extra_charge' === $discount_type) {
            $product_price = $product_price + ($product_price * ($discount / 100));
        } else {
            $product_price = $product_price - ($product_price * ($discount / 100));
        }

        return $product_price;
    }

    public static function get_discount_amount($product) {
        global $wooaiodiscount_current_user_rule;
        $discount = 0;
        $all_products_discount = null;
        $category_discount = null;
        $product_discount = null;
        $product_id = $product->get_id();
        $rule = $wooaiodiscount_current_user_rule;
        $product_cats_ids = wc_get_product_term_ids( $product_id, 'product_cat' );

        if (!empty($rule['base_discount']['discount'])) {
            foreach ($rule['base_discount']['discount'] as $discount_rule) {
                if ($discount_rule['apply'] === 'all_products') {
                    $all_products_discount = $discount_rule['amount'];
                } elseif ($discount_rule['apply'] === 'by_categories') {

                } elseif ($discount_rule['apply'] === 'separate_products') {
                    if (in_array($product_id, $discount_rule['products'])) {
                        $product_discount = $discount_rule['amount'];
                    }
                }
            }
        }

        if (null !==  $all_products_discount) {
            $discount = $all_products_discount;
        }

        if (null !==  $category_discount) {
            $discount = $category_discount;
        }

        if (null !==  $product_discount) {
            $discount = $product_discount;
        }

        return (int) $discount;
    }
}