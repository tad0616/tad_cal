<?php
include_once 'header.php';
$myts = MyTextSanitizer::getInstance();

$show_type = $myts->addSlashes($_POST['show_type']);
$dl_type = $myts->addSlashes($_POST['dl_type']);
$sitename = $myts->addSlashes($xoopsConfig['sitename']);

$page_width = 14400;
if ('all_week' === $dl_type) {
    $week_width = 400;
    $other_width = $page_width - ($week_width) * 8;
} else {
    $date_width = 1400;
    $week_width = 700;
    $other_width = $page_width - $date_width - $week_width;
}

$page_title = "{$sitename} {$_POST['start']}~{$_POST['end']}" . _MD_TADCAL_SIMPLE_CAL;
$filename = str_replace(' ', '', $page_title);

require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/autoload.php';
$phpWord = new \PhpOffice\PhpWord\PhpWord();

$phpWord->setDefaultFontName('標楷體'); //設定預設字型
$phpWord->setDefaultFontSize(9); //設定預設字型大小
$section = $phpWord->addSection(); //建立一個區域
$sectionStyle = $section->getStyle();
$sectionStyle->setMarginTop(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5));
$sectionStyle->setMarginLeft(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.2));
$sectionStyle->setMarginRight(\PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.2));

$TitleStyle = ['color' => '000000', 'size' => 16, 'bold' => true];
$cellStyle = ['valign' => 'center'];
$fontStyle = ['color' => '000000', 'size' => 10, 'bold' => false];
$headStyle = ['bold' => true];

$paraStyle = ['align' => 'center', 'valign' => 'center'];
$left_paraStyle = ['align' => 'left', 'valign' => 'center'];
$right_paraStyle = ['align' => 'right', 'valign' => 'center'];

$phpWord->addTitleStyle(1, $TitleStyle, $paraStyle);
$section->addTitle($page_title, 1);

$styleTable = ['borderColor' => '000000', 'borderSize' => 6, 'cellMargin' => 80];
$styleFirstRow = ['bgColor' => 'CFCFCF']; //首行樣式
$phpWord->addTableStyle('myTable', $styleTable, $styleFirstRow); //建立表格樣式
$table = $section->addTable('myTable'); //建立表格

$cw = [_MD_TADCAL_SU, _MD_TADCAL_MO, _MD_TADCAL_TU, _MD_TADCAL_WE, _MD_TADCAL_TH, _MD_TADCAL_FR, _MD_TADCAL_SA];
$cm_arr = explode(',', _MD_TADCAL_MONTH_STR);
$i = 1;
foreach ($cm_arr as $month) {
    $cm[$i] = $month;
    $i++;
}
// die(var_export($cm));
if ('all_week' === $dl_type) {
    word_by_month();
} else {
    word_by_date();
}

$filename = iconv('UTF-8', 'Big5', $filename);
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
header('Cache-Control: max-age=0');
header('Content-Type: application/vnd.ms-word');
header("Content-Disposition: attachment;filename={$filename}.docx");
$objWriter->save('php://output');

//日期範圍中有多少日期
function dates_range($date1, $date2)
{
    if ($date1 < $date2) {
        $dates_range[] = $date1;
        $date1 = strtotime($date1);
        $date2 = strtotime($date2);
        while ($date1 != $date2) {
            $date1 = mktime(0, 0, 0, date('m', $date1), date('d', $date1) + 1, date('Y', $date1));
            $dates_range[] = date('Y-m-d', $date1);
        }
    }

    return $dates_range;
}

//日期範圍中有多少月份
function months_range($date1, $date2)
{
    $months_range = [];
    $start = $month = strtotime($date1);
    $end = strtotime($date2);
    while ($month < $end) {
        $months_range[] = date('Y-m', $month);
        $month = strtotime('+1 month', $month);
    }
    // die(var_export($months_range));
    return $months_range;
}

//製作一個月的簡曆
function mk_month_cell($year, $month, $events, $cates)
{
    global $xoopsDB, $dl_type, $cm, $cw, $table, $paraStyle, $headStyle, $cellStyle, $show_type, $week_width, $other_width;
    //垂直合併
    $cellRowSpan = ['vMerge' => 'restart', 'valign' => 'center'];
    $cellRowSpanTop = ['vMerge' => 'restart', 'valign' => 'top'];
    //不顯示表格
    $cellRowContinue = ['vMerge' => 'continue'];
    //水平合併
    $cellColSpan = ['gridSpan' => 2, 'valign' => 'center'];
    //水平置中
    $cellHCentered = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
    //垂直置中
    $cellVCentered = ['valign' => 'center'];
    //垂直靠上
    $cellVToped = ['valign' => 'top'];
    $blankStyle = ['bgColor' => 'FFFFFF'];

    $start_ts = strtotime("{$year}-{$month}-01");
    //取得開始位置是星期 ? 補空
    //0-6
    $w = date('w', $start_ts);
    $m = date('n', $start_ts);
    $t = date('t', $start_ts);

    //看第一天是哪一天 先做第一排的空白格
    $table->addRow(); //新增一列
    $cellStyle = ['bgColor' => 'FFFFFF'];
    $table->addCell($week_width, $cellRowSpan)->addText($cm[$m], null, $paraStyle); //新增一格
    if ($w > 0) {
        //補由星期日開始
        for ($a_s = 0; $a_s < $w; $a_s++) {
            $table->addCell($week_width, $blankStyle)->addText('', null, $paraStyle); //新增一格
        }
    }

    $line = 1;
    for ($i = 1; $i <= $t; $i++) {
        $w = date('w', strtotime("{$year}-{$month}-{$i}"));
        $color = (0 == $w or 6 == $w) ? 'FEE9E7' : 'FFFFFF';
        $cellStyle = ['bgColor' => $color];

        if (0 == $w and $i > 1) {
            $table->addRow(); //新增一列
            $table->addCell(null, $cellRowContinue);
            $line++;
        }
        $table->addCell($week_width, $cellStyle)->addText($i, null, $paraStyle); //新增一格

        //在最後一格之後填入事件
        if (1 == $line and 6 == $w) {
            if ('separate' === $show_type) {
                $cellStyle = ['bgColor' => $color];
                $cal_num = count($cates);
                $width = round($other_width / $cal_num);
                foreach ($cates as $cate_sn => $cate_title) {
                    $tableCell = $table->addCell($width, $cellRowSpanTop);
                    foreach ($events[$cate_sn] as $sn => $item) {
                        $tableCell->addText($item);
                    }
                }
            } else {
                $tableCell = $table->addCell(12300, $cellRowSpanTop);
                foreach ($events as $sn => $item) {
                    $tableCell->addText($item);
                }
            }
        } elseif ($line > 1 and 6 == $w) {
            if ('separate' === $show_type) {
                foreach ($cates as $cate_sn => $cate_title) {
                    $table->addCell(null, $cellRowContinue);
                }
            } else {
                $table->addCell(null, $cellRowContinue);
            }
        }
    }

    //最後補滿星期每格
    for ($lw = $w + 1; $lw <= 6; $lw++) {
        $table->addCell($week_width, $blankStyle)->addText('', null, $paraStyle); //新增一格
    }

    if ('separate' === $show_type) {
        $cellStyle = ['bgColor' => $color];
        foreach ($cates as $cate_sn => $cate_title) {
            $table->addCell(null, $cellRowContinue);
        }
    } else {
        $table->addCell(null, $cellRowContinue);
    }
}

//下載周曆
function word_by_month()
{
    global $xoopsDB, $dl_type, $cm, $cw, $table, $paraStyle, $headStyle, $cellStyle, $show_type, $week_width, $other_width;

    $cates = get_cal_array();

    //週簡曆的標題列
    $table->addRow(); //新增一列

    //製作左邊星期別
    $cellStyle = ['bgColor' => 'FFFFFF'];
    $table->addCell($week_width, $cellStyle)->addText('月', $headStyle, $paraStyle); //新增一格
    for ($w = 0; $w <= 6; $w++) {
        if (0 == $w or 6 == $w) {
            $cellStyle = ['bgColor' => 'FEE9E7'];
            $table->addCell($week_width, $cellStyle)->addText($cw[$w], $headStyle, $paraStyle); //新增一格
        } else {
            $cellStyle = ['bgColor' => 'FFFFFF'];
            $table->addCell($week_width, $cellStyle)->addText($cw[$w], $headStyle, $paraStyle); //新增一格
        }
    }

    //製作右邊類別
    $cellStyle = ['bgColor' => 'FFFFFF'];

    if ('separate' === $show_type) {
        $cal_num = count($cates);
        $width = round($other_width / $cal_num);
        foreach ($cates as $cate_sn => $cate_title) {
            $table->addCell($width, $cellStyle)->addText($cates[$cate_sn], $headStyle, $paraStyle);
        }
    } else {
        $table->addCell($other_width, $cellStyle)->addText(_MD_TADCAL_SIMPLE_EVENT, $headStyle, $paraStyle);
    }

    $all_event = get_events($_POST['start'], $_POST['end'], $_POST['cate_sn'], $show_type, $dl_type);

    $months = months_range($_POST['start'], $_POST['end']);

    foreach ($months as $ym) {
        list($year, $month) = explode('-', $ym);
        mk_month_cell($year, $month, $all_event[$month], $cates);
    }
}

//僅下載有事件的日期、下載所有日期
function word_by_date()
{
    global $xoopsDB, $dl_type, $cm, $cw, $table, $paraStyle, $headStyle, $cellStyle, $show_type, $date_width, $week_width, $other_width;

    $dates = dates_range($_POST['start'], $_POST['end']);

    //標題行

    //每日簡曆
    $table->addRow(); //新增一列
    $table->addCell($date_width, $cellStyle)->addText(_MD_TADCAL_SIMPLE_DATE, $headStyle, $paraStyle);
    $table->addCell($week_width, $cellStyle)->addText(_MD_TADCAL_WEEK, $headStyle, $paraStyle);

    if ('separate' === $show_type) {
        $cates = get_cal_array();
        $cal_num = count($cates);
        $width = round($other_width / $cal_num);
        foreach ($cates as $cate_sn => $care_title) {
            $table->addCell($width, $cellStyle)->addText($cates[$cate_sn], $headStyle, $paraStyle);
        }
    } else {
        $table->addCell($other_width, $cellStyle)->addText(_MD_TADCAL_SIMPLE_EVENT, $headStyle, $paraStyle);
    }

    $all_event = get_events($_POST['start'], $_POST['end'], $_POST['cate_sn'], $show_type, $dl_type);

    // --- 依每日呈現
    foreach ($dates as $start) {
        if ('only_event' === $dl_type and !isset($all_event[$start])) {
            continue;
        }
        $arr = $all_event[$start];

        $w = date('w', strtotime($start));

        if (0 == $w or 6 == $w) {
            $cellStyle = ['bgColor' => 'FEE9E7'];
        } else {
            $cellStyle = ['bgColor' => 'FFFFFF'];
        }

        $table->addRow(); //新增一列
        $table->addCell($date_width, $cellStyle)->addText($start, null, $paraStyle); //新增一格
        $table->addCell($week_width, $cellStyle)->addText($cw[$w], null, $paraStyle); //新增一格

        if ('separate' === $show_type) {
            foreach ($cates as $cate_sn => $cate_title) {
                $tableCell = $table->addCell($width, $cellStyle);
                foreach ($arr[$cate_sn] as $sn => $item) {
                    $tableCell->addText($item);
                }
            }
        } else {
            $tableCell = $table->addCell(12300, $cellStyle);
            foreach ($arr as $sn => $item) {
                $tableCell->addText($item);
            }
        }
    }
}

//抓出事件陣列
function get_events($even_start, $even_end, $cate_sn_arr, $show_type, $dl_type)
{
    global $xoopsDB;

    //取得目前使用者可讀的群組
    $ok_cate_arr = chk_tad_cal_cate_power('enable_group');

    if (!empty($cate_sn_arr)) {
        foreach ($cate_sn_arr as $cate_sn) {
            if (in_array($cate_sn, $ok_cate_arr)) {
                $ok_arr[] = $cate_sn;
            }
        }
    } else {
        $ok_arr = $ok_cate_arr;
    }

    //可讀類別判別
    $all_ok_cate = implode(',', $ok_arr);
    $and_ok_cate = empty($all_ok_cate) ? "and cate_sn='0'" : "and cate_sn in($all_ok_cate)";
    $and_ok_cate2 = empty($all_ok_cate) ? "and a.sn='0'" : "and b.cate_sn in($all_ok_cate)";

    //抓出事件
    $sql = 'select * from ' . $xoopsDB->prefix('tad_cal_event') . " where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate  order by `start` , `sequence`";

    $result = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);
    $i = 0;
    while ($all = $xoopsDB->fetchArray($result)) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }
        if (!empty($recurrence)) {
            continue;
        }

        $start = mb_substr($start, 0, 10);
        if ('all_week' === $dl_type) {
            list($y, $m, $d) = explode('-', $start);
            if ('separate' === $show_type) {
                $all_event[$m][$cate_sn][$sn] = "{$d}){$title}";
            } else {
                $all_event[$m][$sn] = "{$d}){$title}";
            }
        } else {
            if ('separate' === $show_type) {
                $all_event[$start][$cate_sn][$sn] = $title;
            } else {
                $all_event[$start][$sn] = $title;
            }
        }
    }

    //抓出重複事件
    $sql = 'select a.*,b.title,b.cate_sn from ' . $xoopsDB->prefix('tad_cal_repeat') . ' as a join ' . $xoopsDB->prefix('tad_cal_event') . " as b on a.sn=b.sn where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2  order by a.`start`";
    //die($sql);
    $result = $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);

    while ($all = $xoopsDB->fetchArray($result)) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        $start = mb_substr($start, 0, 10);
        if ('all_week' === $dl_type) {
            list($y, $m, $d) = explode('-', $start);
            if ('separate' === $show_type) {
                $all_event[$m][$cate_sn][$sn] = "{$d}){$title}";
            } else {
                $all_event[$m][$sn] = "{$d}){$title}";
            }
        } else {
            if ('separate' === $show_type) {
                $all_event[$start][$cate_sn][$sn] = $title;
            } else {
                $all_event[$start][$sn] = $title;
            }
        }
    }

    return $all_event;
}
