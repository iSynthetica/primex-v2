<?php


class Woo_All_In_One_Coupon_Helpers {
    public static function get_allowed_tabs() {
        return array(
            'coupon' => array(
                'title' => __('Coupon Settings', 'woo-all-in-one-coupon')
            ),
        );
    }
}