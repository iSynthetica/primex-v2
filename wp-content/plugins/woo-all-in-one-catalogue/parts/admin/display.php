<?php
/**
 * @var $active_tab
 */
?>
<h1>Catalogue</h1>

<div class="wrap">
    <h1><?php _e('Woocommerce Product Catalogue', 'woo-all-in-one-discount') ?></h1>

    <?php include(dirname(__FILE__) . '/tabs.php'); ?>

    <?php include(dirname(__FILE__) . '/'.$active_tab.'.php'); ?>
</div>