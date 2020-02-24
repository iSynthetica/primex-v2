<?php
/**
 * Default Single Page Header
 *
 * @package Hooka/Parts/ContentHeader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$header = !empty($header) ? $header : 'page';

?>

<!-- Head Section -->
<header class="article-header page-section bg-dark-alfa-50 parallax-3"
        data-background="<?php echo SNTH_IMAGES_URL; ?>/full-width-images/section-bg-10.jpg">
    <div class="relative container align-left">

        <div class="row">

            <div class="col-md-8">
                <?php the_title('<h1 class="hs-line-11 font-alt mb-20 mb-xs-0">', '</h1>') ?>
                <div class="hs-line-4 font-alt">
                    Extraordinary art team &&nbsp;creative minimalism lovers
                </div>
            </div>

            <div class="col-md-4 mt-30">
                <?php snth_the_breadcrumbs(); ?>
            </div>
        </div>

    </div>
</header>
<!-- End Head Section -->