<?php
/**
 * Loop Single Post Item
 *
 * @package Hooka/Parts/Loop
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Post Item -->
<div class="col-sm-6 col-md-3 col-lg-3 mb-60 mb-xs-40">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="post-prev-img">
            <?php
            if('' !== get_the_post_thumbnail()) {
                ?>
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail( 'thumb_370_200_cr' ); ?>
                </a>
                <?php
            } else {
                ?>
                <a href="<?php the_permalink(); ?>"><img src="<?php echo SNTH_IMAGES_URL; ?>/blog/post-prev-1.jpg" alt="" /></a>
                <?php
            }
            ?>
        </div>

        <div class="post-prev-title font-alt">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>

        <div class="post-prev-info font-alt">
            <?php echo get_the_date() ?>
        </div>

        <div class="post-prev-text">
            <?php the_excerpt(); ?>
        </div>

        <div class="post-prev-more">
            <a href="<?php the_permalink(); ?>" class="btn btn-mod btn-gray btn-round">
                <?php _e('Read more', 'snthwp') ?> <i class="fa fa-angle-right"></i>
            </a>
        </div>
    </article>
</div>
<!-- End Post Item -->