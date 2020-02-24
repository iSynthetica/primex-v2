<?php
/**
 * Single Products Header
 *
 * @package Hooka/Parts/ContentHeader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Head Section -->
<section class="small-section bg-gray pt-10 pb-10">
    <div class="relative container align-left">
        <div class="row">
            <div class="col-md-8">
                <?php the_title('<h1 class="hs-line-11 font-alt mb-0 mb-xs-0">', '</h1>') ?>
            </div>

            <div class="col-md-4 mt-30">
                <?php snth_the_breadcrumbs(); ?>
            </div>
        </div>
    </div>
</section>
<!-- End Head Section -->