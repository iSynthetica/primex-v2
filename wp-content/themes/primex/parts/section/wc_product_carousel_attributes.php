<?php
/**
 * @var $content
 */

if (!is_array($content['products'])) {
    return;
}

$args = array(

);


$settings = $content['settings'];
$products_args = $content['products'];
// $products = array_map( 'wc_get_product', $content['products'] );

// var_dump($products_args);

$args = array();

if (!empty($products_args['per_page'])) {
    $args['limit'] = $products_args['per_page'];
}

if (!empty($products_args['recent'])) {
    $args['orderby'] = 'date';
    $args['order'] = 'DESC';
} else {
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';
}

if (!empty($products_args['category'])) {
    $categories = array();

    foreach ($products_args['category'] as $category) {
        $categories[] = $category->term_id;
    }

    if (!empty($categories)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => $categories,
            'operator' => 'IN',
            "include_children" => false
        );
    }
}

if (!empty($products_args['popularity'])) {
    if ('best_selling_products' === $products_args['popularity']) {
        $args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        $args['order']    = 'DESC';
        $args['orderby']  = 'meta_value_num';
    } elseif ('top_rated_products' === $products_args['popularity']) {
        $args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        $args['order']    = 'DESC';
        $args['orderby']  = 'meta_value_num';
    }
}

$products = wc_get_products($args);


// return;
$items_xl = !empty($settings['items_xl']) ? $settings['items_xl'] : 4;
$items_lg = !empty($settings['items_lg']) ? $settings['items_lg'] : 4;
$items_md = !empty($settings['items_md']) ? $settings['items_md'] : 4;

remove_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_quick_view_start', 15 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_quick_view', 25 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_quick_view_end', 35 );
if ( $products ) : ?>

    <div class="clear"></div>

    <div class="products-carousel products">

        <?php snth_wc_product_loop_carousel_start(true, $items_xl, $items_lg, $items_md); ?>

        <?php foreach ( $products as $product ) : ?>

            <?php
            $post_object = get_post( $product->get_id() );

            setup_postdata( $GLOBALS['post'] =& $post_object );
            ?>
            <div class="oc-item">
                <?php wc_get_template_part( 'content', 'product' ); ?>
            </div>

        <?php endforeach; ?>

        <?php snth_wc_product_loop_carousel_end(); ?>

    </div>

<?php endif;

add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_quick_view_start', 15 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_quick_view', 25 );
add_action( 'woocommerce_before_shop_loop_item_title', 'snth_wc_template_loop_product_quick_view_end', 35 );
wp_reset_postdata();