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

    public static function get_product_categories_tree($parent = 0) {
        $tree = array();

        $next = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent' => $parent
        ));

        if (!empty($next)) {
            foreach ($next as $cat) {
                $tree[$cat->term_id] = array(
                    'category' => $cat,
                    'children' => Woo_All_In_One_Discount_Helpers::get_product_categories_tree($cat->term_id)
                );
            }
        }

        return $tree;
    }
}