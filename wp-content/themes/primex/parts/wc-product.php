<?php
$layout = 'right-sidebar';
$subtitle = !empty($subtitle) ? $subtitle : '';
?>
<section id="page-title">
    <div class="container clearfix">
        <h1><?php echo get_the_title(); ?></h1>
        <?php
        if (!empty($subtitle)) {
            ?><span>Page Content on the Left &amp; Sidebar on the Right</span><?php
        }
        ?>

        <?php snth_the_breadcrumbs(); ?>
    </div>
</section><!-- #page-title end -->

<section id="content">
    <div class="content-wrap">
        <div class="container clearfix">
            <?php
            if ('right-sidebar' === $layout) {
                ?>
                <div class="postcontent nobottommargin clearfix">
                    <?php wc_get_template_part( 'content', 'single-product' ); ?>
                </div>

                <div class="sidebar nobottommargin col_last clearfix">
                    <?php get_sidebar('woocommerce'); ?>
                </div>
                <?php
            } elseif ('left-sidebar' === $layout) {
                ?>
                <div class="postcontent nobottommargin col_last clearfix">
                    <?php wc_get_template_part( 'content', 'single-product' ); ?>
                </div>

                <div class="sidebar nobottommargin clearfix">
                    <?php get_sidebar('woocommerce'); ?>
                </div>
                <?php
            } else {
                ?>
                <?php wc_get_template_part( 'content', 'single-product' ); ?>
                <?php
            }
            ?>
        </div>
    </div>
</section>