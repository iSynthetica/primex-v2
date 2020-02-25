<?php


class Woo_All_In_One_NP_API {
    public static function get_settings() {
        return get_option('woocommerce_novaposhta_general_settings', array(
            'np_API_key' => ''
        ));
    }

    public static function get_cities() {
        $cache_cities = get_transient('woocommerce_novaposhta_cities');
        $cache_cities_expire = get_transient('woocommerce_novaposhta_cities_expire');

        if (!empty($cache_cities) && !empty($cache_cities_expire)) {
            return $cache_cities;
        }

        include_once("NovaPoshtaApi2.php");
        $np_API_key = Woo_All_In_One_NP_API::get_settings()['np_API_key'];

        $np = new NovaPoshtaApi2($np_API_key);
        $city = $np->getCities();

        if (!empty($city['data'])) {
            $cities = $city['data'];

            set_transient('woocommerce_novaposhta_cities', $cities);
            set_transient('woocommerce_novaposhta_cities_expire', 1, 60 * 60 * 24);

            return $cities;
        }

        if (!empty($cache_cities)) {
            return $cache_cities;
        }

        return array();
    }

    public static function get_areas() {
        $cache_areas = get_transient('woocommerce_novaposhta_areas');
        $cache_areas_expire = get_transient('woocommerce_novaposhta_areas_expire');

        if (!empty($cache_areas) && !empty($cache_areas_expire)) {
            return $cache_areas;
        }

        include_once("NovaPoshtaApi2.php");
        $np_API_key = Woo_All_In_One_NP_API::get_settings()['np_API_key'];

        $np = new NovaPoshtaApi2($np_API_key);
        $area = $np->getAreas();

        if (!empty($area['data'])) {
            $areas = $area['data'];

            set_transient('woocommerce_novaposhta_areas', $areas);
            set_transient('woocommerce_novaposhta_areas_expire', 1, 60 * 60 * 24);

            return $areas;
        }

        if (!empty($cache_areas)) {
            return $cache_areas;
        }

        return array();
    }
}