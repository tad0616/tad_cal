<?php
use Xmf\Request;

require_once __DIR__ . '/header.php';

$show_type = Request::getString('show_type');
$dl_type = Request::getString('dl_type');
$start = Request::getString('start');
$end = Request::getString('end');
$cate_sn = Request::getInt('cate_sn');
$tag = Request::getString('tag', 'todo');

$myts = \MyTextSanitizer::getInstance();
$sitename = addslashes($xoopsConfig['sitename']);

$todo_title = $tag == "todo-ok" ? _MD_TADCAL_TODOLIST_DONE : _MD_TADCAL_TODOLIST;

$excel_title = "{$sitename}-{$todo_title}";
$filename = str_replace(' ', '', $excel_title);

$all_todo_event = get_events('', '', [], '', '', $tag);
$excel_data_arr = [];
if ($all_todo_event) {
    foreach ($all_todo_event as $date => $events) {
        foreach ($events as $sn => $title) {
            $data[_MD_TADCAL_SIMPLE_DATE] = mk_arr(15, $date, true);
            $data[_MD_TADCAL_SIMPLE_EVENT] = mk_arr(45, $title, false);
        }
        $excel_data_arr[] = $data;
    }
}

require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel.php'; //引入 PHPExcel 物件庫
require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php'; //引入PHPExcel_IOFactory 物件庫
$objPHPExcel = new PHPExcel(); //實體化Excel
//----------內容-----------//
$objPHPExcel->setActiveSheetIndex(0); //設定預設顯示的工作表
$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
$objActSheet->setTitle($todo_title); //設定標題
$objPHPExcel->createSheet(); //建立新的工作表，上面那三行再來一次，編號要改

//設定預設工作表中一個儲存格的外觀
$head_style1 = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => '000000'],
        'size' => 16,
        'name' => 'PMingLiU',
    ],
    'alignment' => [
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => ['rgb' => 'ffe0c9'],
    ],
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$head_style2 = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => '000000'],
        'size' => 12,
        'name' => 'PMingLiU',
    ],
    'alignment' => [
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => ['rgb' => 'CFF4FC'],
    ],
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

if ($excel_data_arr) {
    $all_title = $excel_data_arr ? array_keys($excel_data_arr[0]) : [];

    $col = 0;
    $row = 1;
    $objActSheet->mergeCellsByColumnAndRow($col, $row, count($all_title) - 1, 1);
    $objActSheet->setCellValueByColumnAndRow($col, $row, $todo_title);

    $row++;
    foreach ($all_title as $title) {
        $objActSheet->getColumnDimensionByColumn($col)->setWidth($excel_data_arr[0][$title]['width']);
        $objActSheet->setCellValueByColumnAndRow($col, $row, $title);
        $col++;
    }

    $row++;
    foreach ($excel_data_arr as $apply_data) {
        $col = 0;
        foreach ($apply_data as $title => $data) {
            if ($data['type']) {
                if ($data['type'] == 'text') {
                    $type = PHPExcel_Cell_DataType::TYPE_STRING;
                } elseif ($data['type'] == 'number') {
                    $type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
                } elseif ($data['type'] == 'formula') {
                    $type = PHPExcel_Cell_DataType::TYPE_FORMULA;
                }

                $coordinate = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
                $objActSheet->setCellValueExplicit($coordinate, $data['value'], $type);
            } else {
                $objActSheet->setCellValueByColumnAndRow($col, $row, $data['value']);
            }

            if ($data['c']) {
                $center_col[$col] = $col;
            }

            $col++;
        }
        $row++;
    }

    $col--;

    $objActSheet->getStyleByColumnAndRow(0, 1, $col, 1)->applyFromArray($head_style1);
    $objActSheet->getStyleByColumnAndRow(0, 2, $col, 2)->applyFromArray($head_style2);

    $content_style = [
        'font' => [
            'bold' => false,
            'color' => ['rgb' => '000000'],
            'size' => 11,
            'name' => 'PMingLiU',
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    $row--;

    $objActSheet->getStyleByColumnAndRow(0, 3, $col, $row)->applyFromArray($content_style);
    $objActSheet->getStyleByColumnAndRow(0, 3, $col, $row)->getAlignment()->setWrapText(true);
    foreach ($center_col as $col) {
        $objActSheet->getStyleByColumnAndRow($col, 3, $col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }
} else {
    $objActSheet->mergeCellsByColumnAndRow(0, 1, 1, 1);
    $objActSheet->setCellValueByColumnAndRow(0, 1, $excel_title);
    $objActSheet->getStyleByColumnAndRow(0, 1, 0, 1)->applyFromArray($head_style1);
    $objActSheet->mergeCellsByColumnAndRow(0, 2, 1, 2);
    $objActSheet->setCellValueByColumnAndRow(0, 2, '尚無內容');
    $objActSheet->getStyleByColumnAndRow(0, 2, 0, 2)->applyFromArray($head_style2);
}
// $excel_title = (_CHARSET === 'UTF-8') ? iconv('UTF-8', 'Big5', $excel_title) : $excel_title;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename={$excel_title}.xlsx");
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->setPreCalculateFormulas(false);
$objWriter->save('php://output');
exit;

function num2alpha($n)
{
    for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n % 26 + 0x41) . $r;
    }

    return $r;
}

function mk_arr($width, $value, $center = true, $type = '')
{
    return ['width' => $width, 'value' => $value, 'c' => $center, 'type' => $type];
}
