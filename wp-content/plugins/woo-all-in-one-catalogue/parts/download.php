<?php
require WOOAIOCATALOGUE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$catalogue_tree = wooaioc_get_product_categories_tree();
$catalogue_page_title = __('Catalogue', 'woo-all-in-one-catalogue') . ' ' . date('d-m-Y', time());

$spreadsheet = new Spreadsheet();

$spreadsheet->getProperties()->setCreator('Jhon Doe')
    ->setTitle('Tutorial Excel With Php')
    ->setSubject('This Subject of Tutorial')
    ->setDescription('This Description of Tutorial');

$row = 1;
$spreadsheet->getActiveSheet()->setTitle($catalogue_page_title);
$spreadsheet->setActiveSheetIndex(0);
$spreadsheet->getActiveSheet()->setCellValue('A' . $row, $catalogue_page_title)
            ->mergeCells('A'.$row.':E'.$row);
$row++;

foreach ($catalogue_tree as $catalogue_item) {
    $result = wooaioc_add_row_catalogue_item($catalogue_item, $spreadsheet, $row);
    $spreadsheet = $result['spreadsheet'];
    $row = $result['row'];
}

$writer = new Xlsx($spreadsheet);

header('Content-Type: Application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="downloadExcel.xlsx"');
$writer->save('php://output');
