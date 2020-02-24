<?php
/**
 * Single Page Content
 *
 * @package Hooka/Parts/Content
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <!-- About Section -->
    <section class="page-section pt-20 pb-20">
        <div class="container relative">
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>

        <?php
        if (!is_page()) {
            ?>
            <div class="container">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
                        <?php snth_show_social_share(); ?>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </section>
    <!-- End About Section -->
</article>
