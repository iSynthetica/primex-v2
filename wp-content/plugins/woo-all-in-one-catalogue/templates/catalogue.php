<?php
/**
 * Show Catalogue
 *
 * @var $products
 */

if (empty($products)) {
    return;
}

$download_catalogue_label = apply_filters('wooaioc_download_catalogue_label', __('Download catalogue', 'woo-all-in-one-catalogue'));

// var_dump(get_all_categories_with_products());
$catalogue_tree = wooaioc_get_product_categories_tree();

if (!empty($catalogue_tree)) {
    ?>
    <div class="catalogue-action-container">
        <a href="<?php echo get_home_url() . '/wooaioc-download-catalogue/excel'; ?>" class="button"><?php echo $download_catalogue_label; ?></a>
    </div>
    <div class="catalogue-container">
        <ul>
            <?php
            foreach ($catalogue_tree as $catalogue_item) {
                wooaioc_display_catalogue_item($catalogue_item);
            }
            ?>
        </ul>
    </div>
    <div class="catalogue-action-container">
        <a href="<?php echo get_home_url() . '/wooaioc-download-catalogue/excel'; ?>" class="button"><?php echo $download_catalogue_label; ?></a>
    </div>
    <?php
}
?>
