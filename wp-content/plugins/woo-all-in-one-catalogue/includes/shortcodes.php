<?php
/**
 *
 */

/**
 * wooaioc_catalogue_shrortcode()
 *
 * @param array $attr
 * @return string
 */
function wooaioc_catalogue_shrortcode($attr = array()) {
    $atts = shortcode_atts(
        array(

        ),
        $attr
    );

    $products_args = array (
        'numberposts' => '-1',
        'orderby'     => 'date',
        'order'       => 'DESC',
        'post_type'   => 'product',
        'suppress_filters' => false, // подавление работы фильтров изменения SQL запроса
    );

    $products = get_posts( $products_args );

    $args = array(
        'products' => $products,
    );

    return wooaioc_get_template_html('catalogue.php', $args);
}

add_shortcode( 'wooaioc_catalogue', 'wooaioc_catalogue_shrortcode' );

function get_all_categories_with_products() {
    global $wpdb;

    $sql = "
SELECT p.`ID`, p.`post_title`, p.`post_name`, p.`post_type`, tr.`term_order`, tr.`term_taxonomy_id`, tt.`taxonomy`, t.`name`
FROM {$wpdb->posts} AS p
JOIN {$wpdb->term_relationships} AS tr
ON p.ID = tr.`object_id`
JOIN {$wpdb->term_taxonomy} AS tt
ON tr.`term_taxonomy_id` = tt.`term_taxonomy_id`
JOIN {$wpdb->terms} AS t
ON t.`term_id` = tt.`term_id`
WHERE p.`post_type` = 'product'
AND tt.`taxonomy` = 'product_cat';
    ";

    $results = $wpdb->get_results($sql, ARRAY_A);

    $result_array = array();

    foreach ($results as $result) {
        if (empty($result_array['term_taxonomy_id'])) {
            $result_array['term_taxonomy_id'] = array(
                'taxonomy' => $result_array['taxonomy'],
                'name' => $result_array['name'],
                'products' => array(
                    $result_array['ID'] => array(
                        'post_title' => $result_array['post_title'],
                        'post_name' => $result_array['post_name'],
                        'post_type' => $result_array['post_type'],
                    )
                )
            );
        } else {
            $result_array['term_taxonomy_id']['products'][$result_array['ID']] = array(
                'post_title' => $result_array['post_title'],
                'post_name' => $result_array['post_name'],
                'post_type' => $result_array['post_type'],
            );
        }
    }

    return $result_array;
}
