<?php 
/**
 * The template for displaying Woocommerce content
 *
 * @package Hooka
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php get_header('shop'); ?>

<?php
if ( is_singular( 'product' ) ) {

     snth_show_template('content-wc-single.php', array('template' => 'no-sidebar'));

} else {

    snth_show_template('content-wc-loop.php', array('template' => 'no-sidebar'));

}
?>

<?php get_footer('shop'); ?>