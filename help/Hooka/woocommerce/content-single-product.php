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

<!-- Product Content -->
<div class="row mb-40 mb-xs-20">

    <div class="col-sm-12">
        <?php wc_print_notices(); ?>
    </div>

    <!-- Product Images -->
    <div class="col-xs-12 col-md-6 col-lg-6 mb-md-20">

        <?php wc_get_template( 'single-product/product-image.php' ); ?>

    </div>
    <!-- End Product Images -->

    <!-- Product Description -->
    <div class="col-xs-12 col-md-6 col-lg-6 mb-xs-30">

        <div class="row">
            <div class="col-xs-12 lead mt-0 mb-0">
                <?php wc_get_template( 'single-product/title.php' ); ?>
            </div>
        </div>

        <hr class="mt-0 mb-0"/>

        <?php wc_get_template( 'single-product/price.php' ); ?>

        <hr class="mt-0 mb-20"/>

        <div class="section-text mb-20">
            <?php wc_get_template( 'single-product/short-description.php' ); ?>
        </div>

        <hr class="mt-0 mb-10"/>

        <div class="mb-20">
            <?php woocommerce_template_single_add_to_cart(); ?>
        </div>

        <div class="mb-10">
            <?php snth_show_social_share(); ?>
        </div>

    </div>
    <!-- End Product Description -->

</div>
<!-- End Product Content -->

<!-- Tabs -->
<?php wc_get_template( 'single-product/tabs/tabs.php' ); ?>
<!-- End Tabs -->

<?php snth_wc_hookah_upsell_display(); ?>