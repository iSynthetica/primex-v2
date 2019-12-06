<?php
require WOOAIOCATALOGUE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

$spreadsheet->getProperties()->setCreator('Jhon Doe')
    ->setTitle('Tutorial Excel With Php')
    ->setSubject('This Subject of Tutorial')
    ->setDescription('This Description of Tutorial');

$spreadsheet->getActiveSheet()->setTitle("This Part 1 of tutorial excel");
$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('A1','Utopian.io Rewarding Open Source')
    ->setCellValue('A2','The First in the world');

$writer = new Xlsx($spreadsheet);

header('Content-Type: Application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="downloadExcel.xlsx"');
$writer->save('php://output');
