<?php
/**
 * Template Name: About 1
 *
 * @package Hooka/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php get_header(); ?>

<?php snth_show_template('content-single.php', array(
    'template' => 'no-sidebar',
    'content' => 'about',
    'header' => 'page',

)) ?>

<?php get_footer(); ?>