<?php
/**
 * Classic Loop Template
 *
 * @package Hooka/Parts/Loop
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<?php if (have_posts()) : ?>
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
            <?php while (have_posts()) : the_post(); ?>
                <?php snth_show_template('loop/item-classic.php') ?>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php the_posts_pagination(
        array(
            'prev_text'    => '<i class="fas fa-chevron-left"></i>',
            'next_text'    => '<i class="fas fa-chevron-right"></i>',
        )
    ); ?>
    <!-- End Pagination -->

<?php else : ?>

    <?php //get_template_part( 'parts/content', 'missing' ); ?>

<?php endif; ?>
