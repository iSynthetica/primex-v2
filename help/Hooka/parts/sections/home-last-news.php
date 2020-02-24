<?php
/**
 * Last News section
 *
 * @package Hooka/Parts/Sections/Home
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$args = array(
    'numberposts' => 4,
    'category'    => 0,
    'orderby'     => 'date',
    'order'       => 'DESC',
    'include'     => array(),
    'exclude'     => array(),
    'meta_key'    => '',
    'meta_value'  =>'',
    'post_type'   => 'post',
    'suppress_filters' => true, // подавление работы фильтров изменения SQL запроса
);

$latest_posts = get_posts( $args );

if ( $latest_posts ) {
    $original_post = $GLOBALS['post'];
    ?>
    <section id="news" class="page-section pt-60 pt-xs-30 pb-60 pb-xs-30">
        <div class="container relative">

            <h2 class="section-title font-alt align-left mb-70 mb-sm-40">
                <?php _e('Latest News', 'snthwp') ?>

                <a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>" class="section-more right"><?php _e('All News in our blog', 'snthwp') ?> <i class="fa fa-angle-right"></i></a>

            </h2>

            <div class="row multi-columns-row">

                <?php
                foreach($latest_posts as $latest_post){

                    $GLOBALS['post'] = $latest_post;
                    setup_postdata( $GLOBALS['post'] );

                    snth_show_template('loop/single-4-columns.php');
                }
                ?>

            </div>

        </div>
    </section>
    <?php
    $GLOBALS['post'] = $original_post;
}

wp_reset_postdata();
