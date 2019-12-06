<?php
/**
 * Shortcodes library functions
 *
 * @since      0.0.1
 */

function wprshrtcd_woo_product_reviews_shrortcode($attr = array()) {

    $atts = shortcode_atts(
        array(
            'products_ids' => '',
            'product_title' => '',
            'per_page' => 5,
            'show_schema' => 'yes',
            'show_nested' => 'no',
        ),
        $attr
    );

    $hide_reviews = false;

    if (0 === $atts["per_page"] || '0' === $atts["per_page"]) {
        $hide_reviews = true;
    } elseif ('all' === $atts["per_page"]) {
        $atts["per_page"] = 0;
    }

    $product_ids = !empty($attr['products_ids']) ? $attr['products_ids'] : '';

    if (empty($product_ids)) {
        return '';
    }

    $product_ids_array = explode(',', $product_ids);
    $products_array = array();
    $products_rating_count = 0;
    $products_review_count = 0;
    $products_average_array = array();

    if (!empty($product_ids_array)) {
        foreach ($product_ids_array as $product_id) {
            $product = wc_get_product($product_id);

            if (!empty($product)) {
                $products_array[$product_id] = $product;

                $rating_count = $product->get_rating_count();
                $review_count = $product->get_review_count();
                $average      = $product->get_average_rating();

                if (!empty($rating_count)) {
                    $products_rating_count = $products_rating_count + $rating_count;
                }

                if (!empty($review_count)) {
                    $products_review_count = $products_review_count + $review_count;
                }

                if (!empty($average)) {
                    $products_average_array[] = $average;
                }
            }
        }
    }

    if (empty($products_array)) {
        return '';
    }

    $products_average = 0;

    if (!empty($products_average_array)) {

        foreach ($products_average_array as $products_average_item) {
            $products_average = (float) $products_average + (float) $products_average_item;
        }

        if (!empty($products_average)) {
            $products_average = round($products_average / count($products_average_array) , 2);
        }
    }

    $show_schema = 'yes' === $atts['show_schema'] ? true : false;
    $show_nested = 'yes' === $atts['show_nested'] ? true : false;

    $args = array(
        'products_array' => $products_array,
        'product_title' => $atts['product_title'],
        'per_page' => $atts['per_page'],
        'show_schema' => $show_schema,
        'show_nested' => $show_nested,
        'products_rating_count' => $products_rating_count,
        'products_review_count' => $products_review_count,
        'products_average' => $products_average,
        'hide_reviews' => $hide_reviews,
    );

    return wprshrtcd_get_template_html('product-reviews.php', $args);
}
add_shortcode( 'wprshrtcd_woo_product_reviews', 'wprshrtcd_woo_product_reviews_shrortcode' );