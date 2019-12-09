<?php
global $wpdb;
$sql = "SELECT ID,  post_content, post_title, post_excerpt, post_status, post_name, post_parent, post_type
FROM {$wpdb->posts} 
WHERE post_type = 'product';
";

$products = $wpdb->get_results( $sql, ARRAY_A );
$products_to_import = array();

foreach ($products as $i => $product) {
    $sql = "SELECT meta_key,  meta_value
    FROM {$wpdb->postmeta} 
    WHERE post_id = '{$product['ID']}';
    ";

    $product_meta = $wpdb->get_results( $sql, ARRAY_A );
    $products[$i]['meta'] = $product_meta;

    $products_to_import[] = array(
        'id' => $product['ID'],
        'type' => $product['ID'],
    );
}
echo "<pre>";
print_r($products_to_import);
echo "</pre>";

$fields = 'ID,Type,SKU,Name,Published,"Is featured?","Visibility in catalog","Short description",Description,"Date sale price starts","Date sale price ends","Tax status","Tax class","In stock?",Stock,"Backorders allowed?","Sold individually?","Weight (lbs)","Length (in)","Width (in)","Height (in)","Allow customer reviews?","Purchase note","Sale price","Regular price",Categories,Tags,"Shipping class",Images,"Download limit","Download expiry days",Parent,"Grouped products",Upsells,Cross-sells,"External URL","Button text",Position,"Attribute 1 name","Attribute 1 value(s)","Attribute 1 visible","Attribute 1 global","Attribute 2 name","Attribute 2 value(s)","Attribute 2 visible","Attribute 2 global","Meta: _wpcom_is_markdown","Download 1 name","Download 1 URL","Download 2 name","Download 2 URL"';

$fields_array = explode(',', $fields);

var_dump(wooaioie_get_fields());

//echo "<pre>";
//foreach ($fields_array as $field_item) {
//    $key = str_replace('"', '', $field_item);
//    $key = str_replace(':', '', $key);
//    $key = str_replace('_', ' ', $key);
//    $key = str_replace('  ', ' ', $key);
//    $key = str_replace(' ', '_', $key);
//    $key = str_replace('(', '', $key);
//    $key = str_replace(')', '', $key);
//    $key = str_replace('?', '', $key);
//    $key = strtolower($key);
//    echo "  '".$key."' => '".$field_item."',".PHP_EOL;
//}
//echo "</pre>";

$products = array(
    '44,variable,woo-vneck-tee,"V-Neck T-Shirt",1,1,visible,"This is a variable product.","Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",,,taxable,,1,,0,0,.5,24,1,2,1,,,,"Clothing > Tshirts",,,"https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/vneck-tee-2.jpg, https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/vnech-tee-green-1.jpg, https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/vnech-tee-blue-1.jpg",,,,,,,,,0,Color,"Blue, Green, Red",1,1,Size,"Large, Medium, Small",1,1,1,,,,',
    '45,variable,woo-hoodie,Hoodie,1,0,visible,"This is a variable product.","Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",,,taxable,,1,,0,0,1.5,10,8,3,1,,,,"Clothing > Hoodies",,,"https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-2.jpg, https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-blue-1.jpg, https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-green-1.jpg, https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-with-logo-2.jpg",,,,,,,,,0,Color,"Blue, Green, Red",1,1,Logo,"Yes, No",1,0,1,,,,',
    '46,simple,woo-hoodie-with-logo,"Hoodie with Logo",1,0,visible,"This is a simple product.","Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",,,taxable,,1,,0,0,2,10,6,3,1,,,45,"Clothing > Hoodies",,,https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-with-logo-2.jpg,,,,,,,,,0,Color,Blue,1,1,,,,,1,,,,',
    '47,simple,woo-tshirt,T-Shirt,1,0,visible,"This is a simple product.","Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",,,taxable,,1,,0,0,.8,8,6,1,1,,,18,"Clothing > Tshirts",,,https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/tshirt-2.jpg,,,,,,,,,0,Color,Gray,1,1,,,,,1,,,,',
    '48,simple,woo-beanie,Beanie,1,0,visible,"This is a simple product.","Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",,,taxable,,1,,0,0,.2,4,5,.5,1,,18,20,"Clothing > Accessories",,,https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/beanie-2.jpg,,,,,,,,,0,Color,Red,1,1,,,,,1,,,,',
);

foreach ($products as $product) {
    var_dump(str_getcsv($product, ','));
}
