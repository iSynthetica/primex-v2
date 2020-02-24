<?php
/**
 * Single Product template file
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

$header = !empty($header) ? $header : 'wc-single';
$content = !empty($content) ? $content : 'wc-single';
$sidebar = !empty($sidebar) ? $sidebar : 'single';
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <?php snth_show_template('content-header/'.$header.'.php') ?>

    <?php  snth_before_main_content($template, $sidebar); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <article id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php if ('no-sidebar' === $template) {
                    snth_show_template('content/'.$content.'.php');
                } else {
                    wc_get_template_part( 'content', 'single-product' );
                } ?>
            </article>
        </main>
    </div>

    <?php snth_after_main_content($template, $sidebar); ?>

    <!-- Divider -->
    <hr class="mt-0 mb-0 "/>
    <!-- End Divider -->

    <?php woocommerce_output_related_products(); ?>

<?php endwhile; endif; ?>