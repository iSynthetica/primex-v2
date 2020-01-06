<?php


class Woo_All_In_One_Service_Helpers {
    public static function get_allowed_tabs() {
        return array(
            'repairs' => array(
                'title' => __('Repairs', 'woo-all-in-one-service')
            ),
            'access' => array(
                'title' => __('Access Settings', 'woo-all-in-one-service')
            ),
        );
    }
}