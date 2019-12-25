<h1><?php echo __('Import', 'woo-all-in-one-ie'); ?></h1>
<?php
$csv_file = WOOAIOIE_PATH . 'import_primex.csv';

echo $csv_file;

$row = 1;
$fields_names = array();
$fields_values = array();
$count_fields = 0;
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $add = true;

        if (1 === $row) {
            $count_fields = count($data);
            $num = count($data);
        }
        if (1 === $row) {
            for ($c=0; $c < $num; $c++) {

                //echo "<p> $num fields in line $row: <br /></p>\n";
                $fields_names[] = $data[$c];
            }
            // echo $data[$c] . "<br />\n";
        } else {
            $product_data = array();
            $product_id = null;

            for ($c=0; $c < $count_fields; $c++) {

                //echo "<p> $num fields in line $row: <br /></p>\n";
                $field_name = $fields_names[$c];

                if ('Content' === $field_name || 'Excerpt' === $field_name) {
                    $value = '';
                } else {
                    $value = $data[$c];
                }
                if ('Product Type' === $field_name) {
                    if ('simple' !== $value) {
                        $add = true;
                    }
                }
                if ('ID' === $field_name) {
                    $product_id = $value;
                }


                $product_data[$field_name] = $value;
            }

            if ($add) {
                $fields_values[$product_id] = $product_data;
            }
        }
        $row++;
    }

    fclose($handle);
}
echo "<pre>";
echo "<p> $count_fields fields: <br /></p>\n";
// var_dump($fields_names);
print_r($fields_values);
echo "</pre>";