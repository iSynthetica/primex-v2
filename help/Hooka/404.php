<?php
/**
 * The main template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$template = !empty($template) ? $template : 'no-sidebar';
?>

<?php get_header('page'); ?>

<?php snth_show_template('content-header/404.php') ?>

<?php  snth_before_main_content('no-sidebar'); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
                <?php snth_show_template('content/404.php'); ?>
        </main>
    </div>

<?php snth_after_main_content('no-sidebar'); ?>

<?php get_footer('page'); ?>