<?php
/**
 * Default Products Archive Header
 *
 * @package Hooka/Parts/ContentHeader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if (is_shop()) {
    $title = get_field('shop_page_title', 'options');
    $show_title = $title ? $title : woocommerce_page_title(false);
    $shop_page_thumbnail = get_field('shop_page_thumbnail', 'options');
    $show_thumbnail = $shop_page_thumbnail['sizes']['thumb_1920_1080_cr'];
    ?>
    <!-- Head Section -->
    <section class="page-section bg-dark-alfa-30 pt-30 pb-250 pb-sm-80 pb-md-140" data-background="<?php echo $show_thumbnail; ?>">
        <div class="relative container align-left">

            <div class="row">

                <div class="col-md-8">
                    <h1 class="hs-line-11 font-alt mb-20 mb-xs-0"><?php echo $show_title; ?></h1>
                </div>

                <div class="col-md-4 mt-30">
                    <?php snth_the_breadcrumbs(); ?>
                </div>
            </div>

        </div>
    </section>
    <!-- End Head Section -->
    <?php
} else {
    ?>
    <!-- Head Section -->
    <section class="small-section pt-10 pb-10">
        <div class="relative container align-left">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="hs-line-11 font-alt mb-0"><?php woocommerce_page_title(); ?></h1>
                </div>

                <div class="col-md-4 mt-30">
                    <?php snth_the_breadcrumbs(); ?>
                </div>
            </div>
        </div>
    </section>
    <!-- End Head Section -->
    <?php
}
?>
