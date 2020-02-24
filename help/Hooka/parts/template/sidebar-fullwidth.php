<?php
/**
 * Single Post template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$content = !empty($content) ? $content : 'page';
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <?php snth_show_template('content/'.$content.'.php') ?>

        <?php endwhile; endif; ?>
    </main>
</div>