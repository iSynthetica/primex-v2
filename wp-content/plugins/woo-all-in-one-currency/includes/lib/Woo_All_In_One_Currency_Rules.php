<?php

/**
 * Class Woo_All_In_One_Currency_Helpers
 */
class Woo_All_In_One_Currency_Rules {
    public static function get_all() {
        $woocommerce_currencies = Woo_All_In_One_Currency_Rules::get_woocommerce_currencies();
        $currency = get_woocommerce_currency();
        $currency_rules = get_option('wooaio_currency_rules', array());

        if (empty($currency_rules[$currency])) {
            $woocommerce_currencies[$currency]['base'] = true;
            $currency_rules[$currency] = $woocommerce_currencies[$currency];
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
    }

    public static function update($id, $data) {

    }

    public static function delete($code) {
        $currency_rules = get_option('wooaio_currency_rules', array());

        if (!empty($currency_rules[$code])) {
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