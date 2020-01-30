<?php


class Woo_All_In_One_Service_Helpers {
    public static function get_allowed_tabs() {
        $user = wp_get_current_user();

        if (!$user) {
            return array();
        }

        $user_roles = $user->roles;

        if (!in_array('administrator',$user_roles)) {
            return array(
                'repairs' => array(
                    'title' => __('Repairs', 'woo-all-in-one-service')
                ),
            );
        }

        return array(
            'repairs' => array(
                'title' => __('Repairs', 'woo-all-in-one-service')
            ),
            'access' => array(
                'title' => __('Access Settings', 'woo-all-in-one-service')
            ),
        );
    }

    public static function get_user_access_levels() {
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        $access_settings = get_option('wooaioservice_access_settings', false);
        $access_levels = array();

        if (in_array('administrator',$user_roles)) {
            $access_levels[] = 'delete';
        } else {
            foreach ($user_roles as $role) {
                if (!empty($access_settings[$role])) {
                    $access_levels[] = $access_settings[$role]['rule'];
                }
            }

            if (!empty($access_levels)) {
                $access_levels = array_unique($access_levels);
            }
        }

        return array_values($access_levels);
    }
}