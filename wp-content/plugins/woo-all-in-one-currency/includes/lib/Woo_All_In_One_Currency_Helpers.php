<?php

/**
 * Class Woo_All_In_One_Currency_Helpers
 */
class Woo_All_In_One_Currency_Helpers {
    public static function get_allowed_tabs() {
        return array(
            'currency' => array(
                'title' => __('Currency Settings', 'woo-all-in-one-currency')
            ),
        );
    }
}