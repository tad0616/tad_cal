<?php
include_once "header.php";

$dates = dates_range($_POST['start'], $_POST['end']);

$myts       = MyTextSanitizer::getInstance();
$sitename   = $myts->addSlashes($xoopsConfig['sitename']);
$page_title = "{$sitename} {$_POST['start']}~{$_POST['end']}" . _MD_TADCAL_SIMPLE_CAL;
$filename   = str_replace(" ", "", $page_title);

require_once XOOPS_ROOT_PATH . "/modules/tadtools/PHPWord.php";
$PHPWord = new PHPWord();
$PHPWord->setDefaultFontSize(9); //設定預設字型大小
$sectionStyle = array('orientation' => 'portrait', 'marginTop' => 900, 'marginLeft' => 900, 'marginRight' => 900, 'marginBottom' => 900);

$cw = array(_MD_TADCAL_SU, _MD_TADCAL_MO, _MD_TADCAL_TU, _MD_TADCAL_WE, _MD_TADCAL_TH, _MD_TADCAL_FR, _MD_TADCAL_SA);

$section = $PHPWord->createSection($sectionStyle);

$fontStyle = array('color' => '000000', 'size' => 16, 'bold' => true);
$PHPWord->addTitleStyle(1, $fontStyle);
$section->addTitle($page_title, 1);
$contentfontStyle = array('color' => '000000', 'size' => 9, 'bold' => false);

$styleTable    = array('borderColor' => '000000', 'borderSize' => 6, 'cellMargin' => 50);
$styleFirstRow = array('bgColor' => 'CFCFCF'); //首行樣式
$PHPWord->addTableStyle('myTable', $styleTable, $styleFirstRow); //建立表格樣式
$table = $section->addTable('myTable'); //建立表格

$cellStyle = array('valign' => 'center'); //儲存格樣式（設定項：valign、textDirection、bgColor、borderTopSize、borderTopColor、borderLeftSize、borderLeftColor、borderRightSize、borderRightColor、borderBottomSize、borderBottomColor）
$paraStyle = array('align' => 'center');
$headStyle = array('bold' => true);

//取得目前使用者可讀的群組
$ok_cate_arr = chk_tad_cal_cate_power('enable_group');

if (!empty($_POST['cate_sn'])) {
    foreach ($_POST['cate_sn'] as $cate_sn) {
        if (in_array($cate_sn, $ok_cate_arr)) {
            $ok_arr[] = $cate_sn;
        }

    }
} else {
    $ok_arr = $ok_cate_arr;
}

//標題行
if ( $_POST['dl_type'] == "all_week"  ) {
    //週簡曆
    $table->addRow(); //新增一列
    $cellStyle = array('bgColor' => 'FFFFFF');
    $table->addCell(500, $cellStyle)->addText('週', $headStyle, $paraStyle); //新增一格
    $table->addCell(500, $cellStyle)->addText('月', $headStyle, $paraStyle); //新增一格
    for ($w=0 ; $w<=6 ;$w++){
        if ($w == 0 or $w == 6) {
            $cellStyle = array('bgColor' => 'FEE9E7');
            $table->addCell(500, $cellStyle)->addText($cw[$w], $headStyle, $paraStyle); //新增一格
        } else {
            $cellStyle = array('bgColor' => 'FFFFFF');
            $table->addCell(500, $cellStyle)->addText($cw[$w], $headStyle, $paraStyle); //新增一格
        }
    }

    $cellStyle = array('bgColor' => 'FFFFFF');
    $table->addCell(600, $cellStyle)->addText('註', $headStyle, $paraStyle);
    $table->addCell(8000, $cellStyle)->addText(_MD_TADCAL_SIMPLE_EVENT, $headStyle, $paraStyle);

}else    {
    //每日簡例
    $table->addRow(); //新增一列
    $table->addCell(1500, $cellStyle)->addText(_MD_TADCAL_SIMPLE_DATE, $headStyle, $paraStyle);
    $table->addCell(700, $cellStyle)->addText(_MD_TADCAL_WEEK, $headStyle, $paraStyle);
    if ($_POST['show_type'] == "separate") {
        $cates   = get_cal_array();
        $cal_num = sizeof($ok_arr);
        $width   = round(11200 / $cal_num);
        foreach ($ok_arr as $cate_sn) {
            $table->addCell($width, $cellStyle)->addText($cates[$cate_sn], $headStyle, $paraStyle);
        }
    } else {
        $table->addCell(11200, $cellStyle)->addText(_MD_TADCAL_SIMPLE_EVENT, $headStyle, $paraStyle);
    }
}

$all_ok_cate  = implode(",", $ok_arr);
$and_ok_cate  = empty($all_ok_cate) ? "and cate_sn='0'" : "and cate_sn in($all_ok_cate)";
$and_ok_cate2 = empty($all_ok_cate) ? "and a.sn='0'" : "and b.cate_sn in($all_ok_cate)";

$even_start = $_REQUEST['start'];
$even_end   = $_REQUEST['end'];

//抓出事件
$sql = "select * from " . $xoopsDB->prefix("tad_cal_event") . " where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate  order by `start` , `sequence`";
//die($sql);

$result = $xoopsDB->query($sql) or web_error($sql);
$i      = 0;
while ($all = $xoopsDB->fetchArray($result)) {
//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach ($all as $k => $v) {
        $$k = $v;
    }
    if (!empty($recurrence)) {
        continue;
    }

    $start = substr($start, 0, 10);

    if ($_POST['show_type'] == "separate") {
        $all_event[$start][$cate_sn][$sn] = $title;
    } else {
        $all_event[$start][$sn] = $title;
    }
}

//抓出重複事件
$sql = "select a.*,b.title,b.cate_sn from " . $xoopsDB->prefix("tad_cal_repeat") . " as a join " . $xoopsDB->prefix("tad_cal_event") . " as b on a.sn=b.sn where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2  order by a.`start`";
//die($sql);
$result = $xoopsDB->queryF($sql) or web_error($sql);

while ($all = $xoopsDB->fetchArray($result)) {
//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach ($all as $k => $v) {
        $$k = $v;
    }

    $start = substr($start, 0, 10);
    if ($_POST['show_type'] == "separate") {
        $all_event[$start][$cate_sn][$sn] = $title;
    } else {
        $all_event[$start][$sn] = $title;
    }
}

//die(var_export($all_event));
if ( $_POST['dl_type'] == "all_week"  ) {
    //周簡曆

    //取得開始位置是星期 ? 補空
    $w = date('w', strtotime($_POST['start']))  ;
    $m = date('n', strtotime($_POST['start']))  ;

    $week_num=1 ;

    if ($w>0 ) {
        $table->addRow(); //新增一列

        $cellStyle = array('bgColor' => 'FFFFFF');
        $table->addCell(500, $cellStyle)->addText($week_num, null, $paraStyle); //新增一格
        $table->addCell(500, $cellStyle)->addText($m, null, $paraStyle); //新增一格
        $m_pre =$m ;
        $week_num ++ ;

        //補由星期日開始
        for ($a_s = 0 ; $a_s< $w ; $a_s++) {
            if ($a_s == 0 or $a_s == 6) {
                $cellStyle = array('bgColor' => 'FEE9E7');
                $table->addCell(500, $cellStyle)->addText('', null, $paraStyle); //新增一格
            } else {
                $cellStyle = array('bgColor' => 'FFFFFF');
                $table->addCell(500, $cellStyle)->addText('', null, $paraStyle); //新增一格
            }
        }
    }

    $cell = "";
    //有資料
    foreach ($dates as $start) {

        $w = date('w', strtotime($start));
        $d = date('j',  strtotime($start));

        if ($w == 0 ) {
            //新增一列
            $table->addRow(); //新增一列
            $m = date('n',  strtotime($start) + 6*24*60*60 );
            $cellStyle = array('bgColor' => 'FFFFFF');
            $table->addCell(500, $cellStyle)->addText($week_num, null, $paraStyle); //新增一格
            if ($m<>$m_pre)
                $show_m = $m ;
            else
                $show_m ='' ;
            $table->addCell(500, $cellStyle)->addText($show_m, null, $paraStyle); //新增一格

            $m_pre =$m ;
            $week_num ++ ;
        }

        $vacation_fg = false ;
        $exam_fg = false ;
        //事件
        $arr = $all_event[$start];
        foreach ($arr as $sn => $title) {
            $cell[] = "($d)" .$title;

            //格子顏色(特定日期)
            $pos = strpos($title, '放假');
                if ($pos !== false)
                $vacation_fg = true  ;

            $pos = strpos($title, '補假');
            if ($pos !== false)
                $vacation_fg = true  ;

            $pos = strpos($title, '評量');
                if ($pos !== false)
                    $exam_fg = true  ;
        }
        $content = implode("  、  ", $cell);

        if ($w == 0 or $w == 6 or  $vacation_fg ) {
            $cellStyle = array('bgColor' => 'FEE9E7');
            $table->addCell(500, $cellStyle)->addText($d, null, $paraStyle); //新增一格

        } else {
            if ($exam_fg)
                $cellStyle = array('bgColor' => 'acf2a8');
            else
                $cellStyle = array('bgColor' => 'FFFFFF');
            $table->addCell(500, $cellStyle)->addText($d, null, $paraStyle); //新增一格
        }

        //列出本周事件
        if ($w==6)  {
            $cellStyle = array('bgColor' => 'FFFFFF');
            $table->addCell(600, $cellStyle)->addText('', null, $paraStyle); //新增一格
            $table->addCell(8000, $cellStyle)->addText($content); //新增一格
            $cell = "";
            $content ="" ;
        }

    }
    //最後補滿星期每格
    for ($lw=$w+1 ; $lw<=6 ;$lw++) {
        if ($lw == 0 or $lw == 6) {
            $cellStyle = array('bgColor' => 'FEE9E7');
            $table->addCell(500, $cellStyle)->addText('', null, $paraStyle); //新增一格
        } else {
            $cellStyle = array('bgColor' => 'FFFFFF');
            $table->addCell(500, $cellStyle)->addText('', null, $paraStyle); //新增一格
        }
    }
    //最後列出最後事件
    if ($w<>6){
        $cellStyle = array('bgColor' => 'FFFFFF');
        $table->addCell(600, $cellStyle)->addText('', null, $paraStyle); //新增一格
        $table->addCell(8000, $cellStyle)->addText($content); //新增一格
    }

}else {
    // --- 依每日呈現
    foreach ($dates as $start) {

        if ($_POST['dl_type'] == "only_event" and !isset($all_event[$start])) {
            continue;
        } else {
            $arr = $all_event[$start];
        }
        $w = date('w', strtotime($start));

        if ($w == 0 or $w == 6) {
            $cellStyle = array('bgColor' => 'FEE9E7');
        } else {
            $cellStyle = array('bgColor' => 'FFFFFF');
        }

        $table->addRow(); //新增一列
        $table->addCell(1500, $cellStyle)->addText($start, null, $paraStyle); //新增一格
        $table->addCell(700, $cellStyle)->addText($cw[$w], null, $paraStyle); //新增一格

        if ($_POST['show_type'] == "separate") {
            foreach ($ok_arr as $cate_sn) {
                $cell = "";
                foreach ($arr[$cate_sn] as $sn => $title) {
                    $cell[] = $title;
                }
                $content = implode("\n", $cell);
                $table->addCell($width, $cellStyle)->addText($content);
            }
        } else {

            $cell = "";
            foreach ($arr as $sn => $title) {
                $cell[] = $title;
            }
            $content = implode("\n", $cell);

            $table->addCell(11200, $cellStyle)->addText($content); //新增一格
        }

    }
}

header('Content-Type: application/vnd.ms-word');
header("Content-Disposition: attachment;filename={$filename}.docx");
header('Cache-Control: max-age=0');
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$objWriter->save('php://output');

function dates_range($date1, $date2)
{
    if ($date1 < $date2) {
        $dates_range[] = $date1;
        $date1         = strtotime($date1);
        $date2         = strtotime($date2);
        while ($date1 != $date2) {
            $date1         = mktime(0, 0, 0, date("m", $date1), date("d", $date1) + 1, date("Y", $date1));
            $dates_range[] = date('Y-m-d', $date1);
        }
    }
    return $dates_range;
}
