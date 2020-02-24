<?php
/**
 * Default Products Archive Header
 *
 * @package Hooka/Parts/ContentHeader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Head Section -->
<section class="page-section bg-dark-alfa-30" data-background="<?php echo SNTH_IMAGES_URL; ?>/full-width-images/section-bg-10.jpg">
    <div class="relative container align-left">

        <div class="row">

            <div class="col-md-8">
                <h1 class="hs-line-11 font-alt mb-20 mb-xs-0">Shop</h1>
                <div class="hs-line-4 font-alt">
                    Choose the best products in our shop
                </div>
            </div>

            <div class="col-md-4 mt-30">
                <?php snth_the_breadcrumbs(); ?>
            </div>
        </div>

    </div>
</section>
<!-- End Head Section -->