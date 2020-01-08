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

        $result = array(
            'error' => '',
            'id' => $id
        );

        $new_rule = array(
            'title' => sanitize_text_field($data['discount_title']),
            'type' => sanitize_text_field($data['discount_type']),
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

        $result = array(
            'error' => '',
            'id' => $id
        );

        $product_discount_rule['title'] = sanitize_text_field($data['discount_title']);
        $product_discount_rule['type'] = sanitize_text_field($data['discount_type']);
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

    public static function get_user_discounts() {
        return get_option('wooaio_user_discount_rules', array());
    }

    public static function create_user_discount($data) {
        $product_discount_rules = Woo_All_In_One_Discount_Rules::get_user_discounts();
        $id = wp_generate_uuid4();

        $result = array(
            'error' => '',
            'id' => $id
        );

        $new_rule = array(
            'title' => sanitize_text_field($data['discount_title']),
            'description' => sanitize_textarea_field($data['discount_description']),
            'status' => 'draft',
        );

        $product_discount_rules[$id] = $new_rule;

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
}