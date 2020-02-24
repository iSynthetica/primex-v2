<?php
/**
 * Single Post template file
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php get_header('single'); ?>

<?php snth_show_template('content-single.php', array('template' => 'no-sidebar')) ?>

<?php get_footer('single'); ?>