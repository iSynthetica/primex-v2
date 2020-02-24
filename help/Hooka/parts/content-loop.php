<?php
/**
 * Single Post template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$allowed_templates = array(
    'no-sidebar', 'sidebar-fullwidth', 'sidebar-left', 'sidebar-right'
);

$template = !empty($template) ? $template : 'no-sidebar';

$header = !empty($header) ? $header : 'loop';
$content = !empty($content) ? $content : 'loop';
$loop = !empty($loop) ? $loop : 'classic';
$sidebar = !empty($sidebar) ? $sidebar : 'single';
?>

<?php snth_show_template('content-header/'.$header.'.php') ?>

<?php  snth_before_main_content($template, $sidebar); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php if ('no-sidebar' === $template) {
            snth_show_template('content/'.$content.'.php', array('loop' => $loop));
        } else {
            ?>

            <?php snth_show_template('loop/'.$loop.'.php') ?>

            <?php
        } ?>
    </main>
</div>

<?php snth_after_main_content($template, $sidebar); ?>