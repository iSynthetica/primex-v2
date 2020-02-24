<?php
/**
 * Template Name: Contacts
 *
 * @package Hooka/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php get_header('page'); ?>

<?php snth_show_template('content-single.php', array(
    'template' => 'no-sidebar',
    'content' => 'contacts',
    'header' => 'page',

)) ?>

<?php get_footer('page'); ?>