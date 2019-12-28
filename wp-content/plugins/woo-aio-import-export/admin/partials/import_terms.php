<h1>Import Terms</h1>
<?php
include WOOAIOIE_PATH . 'admin/partials/menu.php';

$terms_ser = '';

if (empty($terms_ser)) {
    return;
}

$unserialize = unserialize($terms_ser);
$tax_array = array();
$parent_array = array();
$add_parent = true;
$add_children = true;
$import = true;

foreach ($unserialize as $item) {
    $tax_array[$item['term_taxonomy_id']] = $item;
}

ksort($tax_array);

foreach ($unserialize as $item) {
    if (!empty($item['parent'])) {
        $parent_array[$item['parent']][] = $item['term_taxonomy_id'];
    }
}

echo "<pre>";
print_r($tax_array);
print_r($parent_array);

// Add parent Tax
if ($add_parent) {
    foreach ($parent_array as $parent_id => $children) {
        $parent_term = $tax_array[$parent_id];
        $created_id = wooaioie_add_term($parent_term);

        if (!empty($created_id) && !empty($parent_term["meta"])) {
            wooaioie_add_term_meta($created_id, $parent_term["meta"]);
        }

        unset($tax_array[$parent_id]);

        // Add children tax
        if (!empty($created_id) && $add_children) {
            foreach ($children as $child) {
                $child_term = $tax_array[$child];

                $created_child_id = wooaioie_add_term($child_term, $created_id);

                if (!empty($created_child_id) && !empty($child_term["meta"])) {
                    wooaioie_add_term_meta($created_child_id, $child_term["meta"]);
                }

                unset($tax_array[$child]);
            }
        }
    }
}

if ($import) {
    if (!empty($tax_array)) {
        foreach ($tax_array as $id => $term) {
            $created_id = wooaioie_add_term($term);

            if (!empty($created_id) && !empty($term["meta"])) {
                wooaioie_add_term_meta($created_id, $term["meta"]);
            }

            unset($tax_array[$id]);
        }
    }
}
