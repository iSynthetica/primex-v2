<h1>Export Attributes</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';
global $wpdb;
$table_name = 'woocommerce_attribute_taxonomies';
$table_db_name = $wpdb->prefix . $table_name;

$table_wt_name = 'woocommerce_termmeta';
$table_wt_db_name = $wpdb->prefix . $table_wt_name;

$sql = "SHOW TABLES";

$tables = $wpdb->get_results( $sql, ARRAY_N );
$table_exists = false;
$table_wt_exists = false;

foreach ($tables as $table) {
    if ($table[0] === $table_db_name) {
        $table_exists = true;
    }

    if ($table[0] === $table_wt_db_name) {
        $table_wt_exists = true;
    }
}

if (!$table_exists) {
    return;
}

$sql = "SELECT * FROM  {$table_db_name}";

$attr_tax = $wpdb->get_results( $sql, ARRAY_A );

if (!empty($attr_tax)) {
    foreach ($attr_tax as $attr_tax_index => $attr_tax_item) {
        $taxonomy = 'pa_' . $attr_tax_item['attribute_name'];

        $sql = "SELECT tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug, tt.description, tt.parent
        FROM  {$wpdb->term_taxonomy} AS tt
        JOIN {$wpdb->terms} AS t
        ON t.term_id = tt.term_id
        WHERE tt.taxonomy IN ('{$taxonomy}');";

        $terms = $wpdb->get_results( $sql, ARRAY_A );

        foreach ($terms as $ti => $term) {
            $terms[$ti]['description'] = base64_encode(preg_replace('~[\r\n]+~', ' ', trim(htmlspecialchars($term['description']))));

            $term_metas = array();

            if ($table_wt_exists) {
                $sql = "SELECT wtm.meta_key, wtm.meta_value
                FROM  {$table_wt_db_name} AS wtm
                WHERE wtm.woocommerce_term_id IN ({$term['term_taxonomy_id']})";
                $result = $wpdb->get_results( $sql, ARRAY_A );

                $term_metas = array_merge($term_metas, $result);
            }

            $sql = "SELECT tm.meta_key, tm.meta_value
                    FROM  {$wpdb->termmeta} AS tm
                    WHERE tm.term_id IN ({$term['term_taxonomy_id']})";

            $result = $wpdb->get_results( $sql, ARRAY_A );

            $term_metas = array_merge($term_metas, $result);

            $metas = array();

            foreach ($term_metas as $meta) {
                $metas[] = $meta;
            }

            $terms[$ti]['meta'] = $metas;
        }

        $attr_tax[$attr_tax_index]['attribute_terms'] = $terms;
    }
}

$attr_tax_ser = serialize($attr_tax);

?>
<textarea id="" rows="10" style="width: 100%;max-width: 1000px;"><?php echo $attr_tax_ser ?></textarea>
<?php

echo "<pre>";
print_r($attr_tax);
echo "</pre>";

//array (
//    'name' => 'Test Attr',
//    'slug' => 'test-attr',
//    'type' => 'select',
//    'order_by' => 'menu_order',
//    'has_archives' => 0,
//)