<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;

class ExcelController extends Controller
{
    public function __construct()
    {
        ini_set('display_errors','1');
        error_reporting(E_ALL);
        date_default_timezone_set('Asia/Taipei');
    }

    // 先將當月份的原始檔案整理成同一份 csv
    public function integration_csv()
    {
        // 要整理資料的檔案們 - 同月份資料
        $file_list = [
            '202104_1',
            '202104_2',
            '202104_3',
        ];

        // 準備 write 使用的物件
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        foreach($file_list as $file_name) 
        {
            $filename = public_path('csv/'.$file_name.'.csv');
            $reader = new CsvReader();
            $reader->setDelimiter(',')
                ->setEnclosure('"')
                ->setSheetIndex(0);
    
            $spreadsheetFromCSV = $reader->load($filename);
            $origin_data = $spreadsheetFromCSV->getActiveSheet()->toArray();
    
            // 從第一行資料判斷電號的總筆數
            $first_data = array_shift($origin_data);
            $point_count = count($first_data) - 2;
    
            // 取電號區塊資料
            $point_array = array_slice($origin_data, 0, $point_count);
            // 取除了電號資料以外的區塊 (目標為用來取月初月底的使用量)        
            $used_array = array_slice($origin_data, $point_count + 1);
            
            $start_date_position = null;
            $end_date_position = null;
            foreach($used_array as $key => $used_data)
            {
                // 月初第一天前一列的資料為 Date
                if($used_data[0] === 'Date')
                {
                    $start_date_position = $key + 1;
                }
                // 月底的後一列資料內容包含 End of Report
                if(strpos($used_data[0], 'End of Report'))
                {
                    $end_date_position = $key - 1;
                }
            }
    
            $start_date_array = $used_array[$start_date_position];
            $end_date_array = $used_array[$end_date_position];

            $now_row = $spreadsheet->getActiveSheet()->getHighestRow() - 1;
            foreach($point_array as $key => $point_data)
            {
                $spreadsheet->getActiveSheet()->setCellValue('A1', '電號');
                $spreadsheet->getActiveSheet()->setCellValue('B1', '使用時間');
                $spreadsheet->getActiveSheet()->setCellValue('C1', $start_date_array[0]);
                $spreadsheet->getActiveSheet()->setCellValue('D1', $end_date_array[0]);
                $spreadsheet->getActiveSheet()->setCellValue('E1', '各檔案序號');
    
                $position = $key + 2 + $now_row;
                $cell_electric_number = 'A' . $position;  // 電號
                $cell_use_tiem = 'B' . $position;  // 使用時間
                $cell_start_degree = 'C' . $position;  // 使用時間
                $cell_end_degree = 'D' . $position;  // 使用時間
                $cell_sequence = 'E' . $position;  // 使用時間
    
                $spreadsheet->getActiveSheet()->setCellValue($cell_electric_number, $point_data[1]);
                $spreadsheet->getActiveSheet()->setCellValue($cell_use_tiem, $point_data[3]);
                $spreadsheet->getActiveSheet()->setCellValue($cell_start_degree, $start_date_array[$key+2]);
                $spreadsheet->getActiveSheet()->setCellValue($cell_end_degree, $end_date_array[$key+2]);
                $spreadsheet->getActiveSheet()->setCellValue($cell_sequence, $file_name.'_'.($key+1));
            }
        }

        $integration_path = public_path('csv/integration/');
        if(!is_dir($integration_path))
        {
            mkdir($integration_path, 0777, true);
        }
        $save_file_name = 'integration-' . date("Y-m-d-His");
        $writer = new CsvWriter($spreadsheet);
        $writer->setDelimiter(',')
            ->setEnclosure('"')
            ->setSheetIndex(0);        
        $writer->save($integration_path.$save_file_name.'.csv');
        echo 'save success ' . $save_file_name;
    }

    public function detail_csv($file_name)
    {
        if(!$file_name) 
        {
            echo 'fail';
            return;
        }
        $file_name = public_path('csv/integration/'.$file_name.'.csv');
        if(!is_file($file_name))
        {
            echo 'no file';
            return;
        }
        $reader = new CsvReader();
            $reader->setDelimiter(',')
                ->setEnclosure('"')
                ->setSheetIndex(0);
    
        $spreadsheetFromCSV = $reader->load($file_name);
        $origin_data = $spreadsheetFromCSV->getActiveSheet()->toArray();

        $result = [];
        foreach($origin_data as $data)
        {
            if(preg_match('/(\w+)\.KWH\((\w+)\)/i', $data[0], $output_array))
            {
                $data['number'] = $output_array[1];
                $result[$output_array[2]][] = $data;
            }

            if(preg_match('/^C\.BTU-(\w+[\-]?\w+?)\.-?KWH$/i', $data[0], $output_array))
            {
                $data['number'] = 'special';
                $result[$output_array[1]][] = $data;
            }
        }

        // 準備 write 使用的物件
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        $spreadsheet->getActiveSheet()->setCellValue('A1', '房號(進駐廠商)');
        $spreadsheet->getActiveSheet()->setCellValue('B1', '開關編號');
        $spreadsheet->getActiveSheet()->setCellValue('C1', '月初度數');
        $spreadsheet->getActiveSheet()->setCellValue('D1', '月底度數');

        foreach($result as $vender_id => $integration_data)
        {
            $now_row = $spreadsheet->getActiveSheet()->getHighestRow() - 1;
            foreach($integration_data as $key => $data)
            {
                $position = $key + 2 + $now_row;
                $cell_vender_id = 'A' . $position;  // 房號(進駐廠商)
                $cell_electric_number = 'B' . $position;  // 開關編號
                $cell_start_degree = 'C' . $position;  // 月初度數
                $cell_end_degree = 'D' . $position;  // 月底度數

                $electric_number = ($data['number'] === 'special') ? $data[0] : $data['number'];
    
                $spreadsheet->getActiveSheet()->setCellValue($cell_vender_id, $vender_id);
                $spreadsheet->getActiveSheet()->setCellValue($cell_electric_number, $electric_number);
                $spreadsheet->getActiveSheet()->setCellValue($cell_start_degree, $data[2]);
                $spreadsheet->getActiveSheet()->setCellValue($cell_end_degree, $data[3]);
            }
        }

        $result_path = public_path('csv/result/');
        if(!is_dir($result_path))
        {
            mkdir($result_path, 0777, true);
        }
        $save_file_name = 'result-' . date("Y-m-d-His");
        $writer = new CsvWriter($spreadsheet);
        $writer->setDelimiter(',')
            ->setEnclosure('"')
            ->setSheetIndex(0);        
        $writer->save($result_path.$save_file_name.'.csv');
        echo 'save success ' . $save_file_name;
    }

    // sample
    public function index()
    {
        $helper = new Sample();
        if ($helper->isCli()) {
            $helper->log('This example should only be run from a Web Browser' . PHP_EOL);

            return;
        }
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Hello')
            ->setCellValue('B2', 'world!')
            ->setCellValue('C1', 'Hello')
            ->setCellValue('D2', 'world!');

        // Miscellaneous glyphs, UTF-8
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A4', 'Miscellaneous glyphs')
            ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Simple');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
