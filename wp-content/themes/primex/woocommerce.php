<?php get_header();?>

<?php
if ( is_singular( 'product' ) ) {
    snth_show_template('wc-product.php');
} else {

}
?>

<?php get_footer(); ?>
