<?php
function wooaioc_display_catalogue_item_description($product) {
    $short_description = $product->get_short_description();

    if (!empty($short_description)) {
        echo $short_description;
    }
}
function wooaioc_display_catalogue_item_add_to_cart($product) {
    ?>
    <div style="padding:1px 5px;display: inline-block;vertical-align: middle;">
        <button class="button catalogue-item-add-to-cart" type="button" data-id="<?php echo $product->get_id(); ?>">
            <?php echo __( 'Add to cart', 'woocommerce' ); ?>
        </button>
    </div>
    <?php
}