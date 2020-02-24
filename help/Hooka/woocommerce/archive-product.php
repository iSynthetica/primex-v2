<?php
/**
 * Single Page Content
 *
 * @package Hooka/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<!-- Section -->

<div class="row">

    <!-- Content -->
    <div class="col-sm-12">
        <?php wc_print_notices(); ?>
    </div>

    <div class="col-sm-12">
        <div class="row multi-columns-row">
            <?php
            if ( woocommerce_product_loop() ) {
                if ( wc_get_loop_prop( 'total' ) ) {
                    while ( have_posts() ) {
                        the_post();

                        /**
                         * Hook: woocommerce_shop_loop.
                         *
                         * @hooked WC_Structured_Data::generate_product_data() - 10
                         */
                        do_action( 'woocommerce_shop_loop' );

                        wc_get_template_part( 'content', 'product' );
                    }
                }
            }
            ?>
        </div>

        <!-- Pagination -->
        <?php the_posts_pagination(
            array(
                'prev_text'    => '<i class="fas fa-chevron-left"></i>',
                'next_text'    => '<i class="fas fa-chevron-right"></i>',
            )
        ); ?>
        <!-- End Pagination -->

    </div>
    <!-- End Content -->


</div>
<!-- End Section -->
