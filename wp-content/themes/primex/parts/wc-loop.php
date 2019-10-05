<?php
$layout = 'right-sidebar';

ob_start();
if ( woocommerce_product_loop() ) {

    /**
     * Hook: woocommerce_before_shop_loop.
     *
     * @hooked woocommerce_output_all_notices - 10
     * @hooked woocommerce_result_count - 20
     * @hooked woocommerce_catalog_ordering - 30
     */
    do_action( 'woocommerce_before_shop_loop' );

    woocommerce_product_loop_start();

    if ( wc_get_loop_prop( 'total' ) ) {
        while ( have_posts() ) {
            the_post();

            /**
             * Hook: woocommerce_shop_loop.
             */
            do_action( 'woocommerce_shop_loop' );

            wc_get_template_part( 'content', 'product' );
        }
    }

    woocommerce_product_loop_end();

    /**
     * Hook: woocommerce_after_shop_loop.
     *
     * @hooked woocommerce_pagination - 10
     */
    do_action( 'woocommerce_after_shop_loop' );
} else {
    /**
     * Hook: woocommerce_no_products_found.
     *
     * @hooked wc_no_products_found - 10
     */
    do_action( 'woocommerce_no_products_found' );
}
$content = ob_get_clean();
?>
<section id="page-title">
    <div class="container clearfix">
        <h1><?php echo get_the_title(); ?></h1>
        <span>Page Content on the Left &amp; Sidebar on the Right</span>
        <?php snth_the_breadcrumbs(); ?>
    </div>
</section><!-- #page-title end -->


<section id="content">
    <div class="content-wrap">
        <div class="container clearfix">
            <?php
            if ('right-sidebar' === $layout) {
                ?>
                <div class="postcontent nobottommargin clearfix">
                    <?php echo $content; ?>
                </div>

                <div class="sidebar nobottommargin col_last clearfix">
                    <?php get_sidebar(); ?>
                </div>
                <?php
            } elseif ('left-sidebar' === $layout) {
                ?>
                <div class="postcontent nobottommargin col_last clearfix">
                    <?php echo $content; ?>
                </div>

                <div class="sidebar nobottommargin clearfix">
                    <?php get_sidebar(); ?>
                </div>
                <?php
            } else {
                ?>
                <?php echo $content; ?>
                <?php
            }
            ?>
        </div>
    </div>
</section>