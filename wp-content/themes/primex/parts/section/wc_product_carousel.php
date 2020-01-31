<?php
/**
 * @var $content
 */

if (!is_array($content['products'])) {
    return;
}
$settings = $content['settings'];
$products = array_map( 'wc_get_product', $content['products'] );

// var_dump($settings);

$items_xl = !empty($settings['items_xl']) ? $settings['items_xl'] : 4;
$items_lg = !empty($settings['items_lg']) ? $settings['items_lg'] : 4;
$items_md = !empty($settings['items_md']) ? $settings['items_md'] : 4;

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

wp_reset_postdata();