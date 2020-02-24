<?php
/**
 * Last News section
 *
 * @package Hooka/Parts/Sections/Home
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if(empty($content)) {
    return;
}

$section_content = $content['section_blog'];
$num = $section_content['number'] ? $section_content['number'] : 4;

$args = array(
    'numberposts' => $num,
    'category'    => 0,
    'orderby'     => 'date',
    'order'       => 'DESC',
    'include'     => array(),
    'exclude'     => array(),
    'meta_key'    => '',
    'meta_value'  =>'',
    'post_type'   => 'post',
    'suppress_filters' => false, // подавление работы фильтров изменения SQL запроса
);

$latest_posts = get_posts( $args );

snth_before_home_section_content($content);

if ( $latest_posts ) {
    $original_post = $GLOBALS['post'];
    ?>
    <div class="row multi-columns-row">
        <?php
        foreach($latest_posts as $latest_post){

            $GLOBALS['post'] = $latest_post;
            setup_postdata( $GLOBALS['post'] );

            snth_show_template('loop/single-4-columns.php');
        }
        ?>
    </div>
    <?php
    $GLOBALS['post'] = $original_post;
}

wp_reset_postdata();

snth_after_home_section_content();
