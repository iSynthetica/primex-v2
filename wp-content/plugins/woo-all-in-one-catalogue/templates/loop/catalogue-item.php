<?php
/**
 * Catalogue Item
 *
 * @var $product
 */

if (empty($product)) {
    return;
}
?>
<div class="catalogue-item-container">
    <?php echo $product->post_title; ?>
</div>
