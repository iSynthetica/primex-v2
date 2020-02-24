<?php
/**
 * Archive Page Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$loop = !empty($loop) ? $loop : 'classic';
?>

<!-- Section -->
<section class="page-section pt-30 pt-xs-20 pb-20 pb-xs-10">
    <div class="container relative">
        <?php snth_show_template('loop/'.$loop.'.php') ?>
    </div>
</section>
<!-- End Section -->
