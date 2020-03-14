<?php
require WOOAIOCATALOGUE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$catalogue_tree = get_transient('wooaiocatalogue_catalogue_tree');

if (empty($catalogue_tree)) {
    $catalogue_tree = wooaioc_get_product_categories_tree();
}

$catalogue_page_title = __('Catalogue', 'woo-all-in-one-catalogue') . ' ' . date('d-m-Y', time());

$spreadsheet = new Spreadsheet();

$columns = wooaioc_get_columns_catalogue_item();
$columns_letters = array_keys($columns);
$first_letter = $columns_letters[0];
$last_letter = end($columns_letters);
reset($columns_letters);

$spreadsheet->getProperties()->setCreator('Jhon Doe')
    ->setTitle('Tutorial Excel With Php')
    ->setSubject('This Subject of Tutorial')
    ->setDescription('This Description of Tutorial');

$row = 1;
$spreadsheet->getActiveSheet()->setTitle($catalogue_page_title);
$spreadsheet->setActiveSheetIndex(0);

foreach ($columns as $letter => $column) {
    $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setWidth($column['width']);
}

$spreadsheet->getActiveSheet()->setCellValue($first_letter . $row, $catalogue_page_title)->mergeCells($first_letter.$row.':'.$last_letter.$row);
$row++;

foreach ($catalogue_tree as $catalogue_item) {
    $result = wooaioc_add_row_catalogue_item($catalogue_item, $spreadsheet, $row);
    $spreadsheet = $result['spreadsheet'];
    $row = $result['row'];
    $spreadsheet->getActiveSheet()->mergeCells($first_letter.$row.':'.$last_letter.$row);
    $row++;
}

$writer = new Xlsx($spreadsheet);

header('Content-Type: Application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="catalogue_'.date('YmdHis', time()).'.xlsx"');
$writer->save('php://output');
