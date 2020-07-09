<h1>Export Attributes</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';
global $wpdb;
$ser_attr = '';

$ser_attr = '';

if (empty($ser_attr)) {
    return;
}

echo "<pre>";
$attr = unserialize($ser_attr);
$existed_attr = wc_get_attribute_taxonomies();

foreach ($attr as $attr_item) {
    $attr_terms = $attr_item['attribute_terms'];
    $existed = false;

    unset($attr_item['attribute_terms']);

    foreach ($existed_attr as $existed_attr_item) {
        if ($existed_attr_item->attribute_name === $attr_item['attribute_name']) {
            $existed = $existed_attr_item->attribute_id;
        }
    }

    $attr_args = array (
        'name' => $attr_item['attribute_label'],
        'slug' => $attr_item['attribute_name'],
        'type' => $attr_item['attribute_type'],
        'order_by' => $attr_item['attribute_orderby'],
    );

    if (!empty($attr_item['attribute_public'])) {
        $attr_args['has_archives'] = true;
    }

    if (!empty($existed)) {
        $attr_args['id'] = $existed;
    }

    $create_id = wc_create_attribute( $attr_args );

    foreach ($attr_terms as $attr_terms_item) {
        print_r($attr_terms_item);
        $created_attr_id = wooaioie_add_term($attr_terms_item, $parent_id = 0, 'product_attr_ID', $attr_terms_item['taxonomy']);

        if (!empty($created_attr_id) && !empty($attr_terms_item["meta"])) {
            wooaioie_add_term_meta($created_attr_id, $attr_terms_item["meta"]);
        }
    }

//    var_dump($create_id);
//    var_dump($attr_args);
//    var_dump($existed);
//    print_r($attr_item);

}

// print_r($existed_attr);
echo "</pre>";