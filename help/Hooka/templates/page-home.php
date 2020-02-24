<?php
/**
 * Template Name: Home
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
    'content' => 'home',
    'header' => 'home',
)) ?>

<?php get_footer(); ?>