<?php
/**
 * The main template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php get_header('page'); ?>

<?php snth_show_template('content-single.php', array(
    'template' => 'no-sidebar',
    'content' => 'single',
    'header' => 'page',

    )) ?>

<?php get_footer('page'); ?>