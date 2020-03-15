<?php
/**
 * @var $content
 */
// echo $content;

$categories_tree = wooaioc_get_categories_tree();
$products = wooaioc_get_products();

header('Content-Type: text/xml');
//$dom = new DOMDocument('1.0', "UTF-8");
//header_remove('Link');
//
//$dom->formatOutput = true;
//echo $dom->saveXML();
ob_start();
echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
echo "\t".'<!DOCTYPE yml_catalog SYSTEM "shops.dtd">'.PHP_EOL;
echo "\t\t".'<yml_catalog date="'.date('Y-m-d H:i',time()).'">'.PHP_EOL;
echo "\t\t".'<shop>'.PHP_EOL;
echo "\t\t\t".'<name>Prime-X</name>'.PHP_EOL;
echo "\t\t\t".'<company>Prime-X inc.</company>'.PHP_EOL;
echo "\t\t\t".'<url>http://www.abc.ua/</url>'.PHP_EOL;
echo "\t\t\t".'<currencies>'.PHP_EOL;
echo "\t\t\t\t".'<currency id="UAH" rate="1"/>'.PHP_EOL;
echo "\t\t\t".'</currencies>'.PHP_EOL;

// Show categories - Start
if (!empty($categories_tree)) {
    echo "\t\t\t".'<categories>'.PHP_EOL;

    foreach ($categories_tree as $category) {
        wooaioc_display_xml_category_item($category);
    }

    echo "\t\t\t".'</categories>'.PHP_EOL;
}
// Show categories - End

// Show products - Start
if (!empty($products)) {
    echo "\t\t\t".'<offers>'.PHP_EOL;

    foreach ($products as $product) {
        wooaioc_display_xml_product_item($product);
    }
    echo "\t\t\t".'</offers>'.PHP_EOL;
}
// Show products - End

echo "\t\t".'</shop>'.PHP_EOL;
echo "\t\t".'</yml_catalog>';
echo ob_get_clean();