<?php


class Woo_All_In_One_Discount_Helpers {
    public static function get_allowed_tabs() {
        return array(
            'discounts' => array(
                'title' => __('Product Discounts', 'woo-all-in-one-discount')
            ),
            'access' => array(
                'title' => __('Settings', 'woo-all-in-one-discount')
            ),
        );
    }
}