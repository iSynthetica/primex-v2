<?php
/**
 * Default Archive Page Header
 *
 * @package Hooka/Parts/ContentHeader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Head Section -->
<header class="small-section pt-10 pb-10">
    <div class="relative container align-left">
        <div class="row">
            <div class="col-md-8">
                <h1 class="hs-line-11 font-alt mb-20 mb-xs-0"><?php _e('Blog', 'snthwp') ?></h1>
            </div>

            <div class="col-md-4 mt-30">
                <?php snth_the_breadcrumbs(); ?>
            </div>
        </div>
    </div>
</header>
<!-- End Head Section -->