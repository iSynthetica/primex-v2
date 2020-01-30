<?php

/**
 * Class Woo_All_In_One_Currency_Helpers
 */
class Woo_All_In_One_Currency_Rules {
    public static function get_all() {
        $woocommerce_currencies = Woo_All_In_One_Currency_Rules::get_woocommerce_currencies();
        $currency = get_option( 'woocommerce_currency' );
        $currency_rules = get_option('wooaio_currency_rules', array());

        if (!isset($currency_rules[$currency])) {
            $currency_rules[$currency] = array();
            update_option('wooaio_currency_rules', $currency_rules);
        }

        $is_main_set = false;

        foreach ($currency_rules as $currency_code => $currency_rule) {
            if (!empty($currency_rules[$currency_code]['main'])) {
                $is_main_set = true;
            }
        }

        if (empty($is_main_set)) {
            $currency_rules[$currency]['main'] = 1;
            update_option('wooaio_currency_rules', $currency_rules);
        }

        foreach ($currency_rules as $currency_code => $currency_rule) {
            $currency_rules[$currency_code] = array_merge($currency_rules[$currency_code], $woocommerce_currencies[$currency_code]);
        }

        return $currency_rules;
    }

    public static function create($data) {
        $currency_rules = get_option('wooaio_currency_rules', array());
        $currency_code = $data['currency_code'];

        if (!empty($currency_rules[$currency_code])) {
            return array(
                'error' => __('Already exists', 'woo-all-in-one-currency'),
                'currency_code' => $currency_code
            );
        }

        $currency_rules[$currency_code] = array();
        update_option('wooaio_currency_rules', $currency_rules);

        return array(
            'error' => '',
            'currency_code' => $currency_code
        );
    }

    public static function update($currency_code, $data) {
        $currency_rules = get_option('wooaio_currency_rules', array());

        if (!isset($currency_rules[$currency_code])) {
            $currency_rules[$currency_code] = array();
        }

        foreach ($data as $field => $value) {
            $currency_rules[$currency_code][$field] = $value;
        }

        update_option('wooaio_currency_rules', $currency_rules);

        return array(
            'error' => '',
            'currency_code' => $currency_code
        );
    }

    public static function delete($code) {
        $currency_rules = get_option('wooaio_currency_rules', array());

        if (isset($currency_rules[$code])) {
            unset($currency_rules[$code]);
        }

        update_option('wooaio_currency_rules', $currency_rules);

        return array(
            'error' => '',
            'code' => $code
        );
    }

    public static function get_woocommerce_currencies() {
        $woocommerce_currencies = get_woocommerce_currencies();
        $woocommerce_currencies_array = array();

        foreach ($woocommerce_currencies as $code => $title) {
            $woocommerce_currencies_array[$code]['title'] = $title;
            $symbol = get_woocommerce_currency_symbol( $code );
            $woocommerce_currencies_array[$code]['symbol'] = $symbol;
        }

        return $woocommerce_currencies_array;
    }
}