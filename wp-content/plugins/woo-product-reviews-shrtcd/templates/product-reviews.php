<?php
/**
 * @var $products_array
 * @var $per_page
 * @var $product_title
 * @var $products_rating_count
 * @var $products_review_count
 * @var $products_average
 * @var $show_schema
 * @var $show_nested
 * @var $hide_reviews
 */

/**
 * Hook: wprshrtcd_before_reviews_container.
 */
do_action( 'wprshrtcd_before_reviews_container' );

if (!empty($products_array)) {
    $products_keys = array_keys($products_array);

    $args = array('post__in' => $products_keys);

    $product_title = !empty($product_title) ? $product_title : get_the_title($products_keys[0]);
    ?>
    <div class="woocommerce-tabs">
        <div id="reviews" class="product-reviews-container"<?php echo $show_schema ? ' itemscope itemtype="http://schema.org/Product"' : ''; ?>>
            <h4><span<?php echo $show_schema ? ' itemprop="name"' : ''; ?>><?php echo $product_title; ?></span></h4>

            <span<?php echo $show_schema ? ' itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating"' : ''; ?>>
                <?php echo __('Average rating', 'woo-product-reviews-shrtcd'); ?>: <strong<?php echo $show_schema ? ' itemprop="ratingValue' : ''; ?>"><?php echo $products_average; ?></strong>
                <?php echo __('based on', 'woo-product-reviews-shrtcd'); ?> <strong<?php echo $show_schema ? ' itemprop="ratingCount"' : ''; ?>><?php echo $products_rating_count; ?></strong> <?php echo __('reviews', 'woo-product-reviews-shrtcd'); ?>
            </span>
            <?php
            if (!$hide_reviews) {
                $args['status'] = 'approve';
                $comments = get_comments( $args );

                if (!$show_nested) {
                    foreach ($comments as $comment_i => $comment_data) {
                        if (!empty($comment_data->comment_parent)) {
                            unset($comments[$comment_i]);
                        }
                    }
                }
                ?>

                <ol class="commentlist">
                    <?php
                    $comments_args = array(
                        'page' => 1,
                        'per_page' => $per_page,
                        'reverse_top_level' => false,
                        'show_schema' => $show_schema,
                        'callback' => 'wprshrtcd_comments'
                    );

                    wp_list_comments($comments_args, $comments);
                    ?>
                </ol>
                <?php
            }
            ?>
        </div>
    </div>
<?php
}

/**
 * Hook: wprshrtcd_after_reviews_container.
 */
do_action( 'wprshrtcd_after_reviews_container' );
?>