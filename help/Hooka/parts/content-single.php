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

$header = !empty($header) ? $header : 'single';
$content = !empty($content) ? $content : 'single';
$sidebar = !empty($sidebar) ? $sidebar : 'single';
?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <?php snth_show_template('content-header/'.$header.'.php') ?>

    <?php  snth_before_main_content($template, $sidebar); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php if ('no-sidebar' === $template) {
                    snth_show_template('content/'.$content.'.php');
                } else {
                    the_content();
                } ?>
            </article>
        </main>
    </div>

    <?php snth_after_main_content($template, $sidebar); ?>

<?php endwhile; endif; ?>