<?php get_header();?>

<?php
if ( is_singular( 'product' ) ) {
    snth_show_template('wc-product.php');
} else {
    snth_show_template('wc-loop.php');
}
?>

<?php get_footer(); ?>
