<?php
/**
 * The main template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php get_header('home'); ?>

<?php snth_show_template('content-single.php', array(
    'template' => 'no-sidebar',
    'content' => 'home',
    'header' => 'home',
)) ?>

<?php get_footer(); ?>