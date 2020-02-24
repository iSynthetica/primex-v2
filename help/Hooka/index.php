<?php
/**
 * The main template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php get_header(); ?>

<?php snth_show_template('content-loop.php', array(
    'template' => 'no-sidebar',
    'content' => 'loop',
)) ?>

<?php get_footer(); ?>