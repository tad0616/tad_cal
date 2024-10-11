<?php
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tad_cal\Tools;

xoops_loadLanguage('main', 'tadtools');

/********************* 自訂函數 ********************
 * @param string $title
 * @param string $sort
 * @param string $handle
 * @param string $enable_group
 * @param string $enable_upload_group
 * @param string $google_id
 * @param string $google_pass
 * @return int
 */
//自動新增分類
function create_cate($title = '', $sort = '', $handle = '', $enable_group = '', $enable_upload_group = '1', $google_id = '', $google_pass = '')
{
    global $xoopsDB;

    if (empty($sort)) {
        $sort = tad_cal_cate_max_sort();
    }

    $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_cal_cate') . '`
    (`cate_title`, `cate_sort`, `cate_enable`, `cate_handle`, `enable_group`, `enable_upload_group`, `google_id`, `google_pass`, `cate_color`)
    VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?)';
    Utility::query($sql, 'sisssssss', [$title, $sort, $handle, $enable_group, $enable_upload_group, $google_id, $google_pass, 'rgb(0,0,0)']) or Utility::web_error($sql, __FILE__, __LINE__);

    //取得最後新增資料的流水編號
    $cate_sn = $xoopsDB->getInsertId();

    //自動給顏色碼
    $color = num2color($cate_sn);
    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_cate') . '` SET `cate_bgcolor`=? WHERE `cate_sn`=?';
    Utility::query($sql, 'si', [$color, $cate_sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    return $cate_sn;
}

//自動取得tad_cal_cate的最新排序
function tad_cal_cate_max_sort()
{
    global $xoopsDB;
    $sql = 'SELECT MAX(`cate_sort`) FROM `' . $xoopsDB->prefix('tad_cal_cate') . '`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    list($sort) = $xoopsDB->fetchRow($result);

    return ++$sort;
}

//以流水號取得某筆tad_cal_cate資料
function get_tad_cal_cate($cate_sn = '')
{
    global $xoopsDB;
    if (empty($cate_sn)) {
        return;
    }

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_cate') . '` WHERE `cate_sn` =?';
    $result = Utility::query($sql, 'i', [$cate_sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    $data = $xoopsDB->fetchArray($result);

    return $data;
}

//自動取得顏色
function num2color($cate_sn = '')
{
    $R = $G = $B = 255;
    $m = ceil($cate_sn / 3);
    $n = $cate_sn % 3;
    $degree = (int) $cate_sn * 10 * $m;

    $cor = ['R', 'G', 'B'];
    ${$cor[$n]} -= $degree;

    return "rgb({$R},{$G},{$B})";
}

function setTimezoneByOffset($offset)
{
    $testTimestamp = time();
    date_default_timezone_set('UTC');
    $testLocaltime = localtime($testTimestamp, true);
    $testHour = $testLocaltime['tm_hour'];

    $abbrarray = timezone_abbreviations_list();
    foreach ($abbrarray as $abbr) {
        //echo $abbr."<br>";
        foreach ($abbr as $city) {
            date_default_timezone_set($city['timezone_id']);
            $testLocaltime = localtime($testTimestamp, true);
            $hour = $testLocaltime['tm_hour'];
            $testOffset = $hour - $testHour;
            if ($testOffset == $offset) {
                return true;
            }
        }
    }

    return false;
}

//取得tad_cal_cate所有資料陣列
function get_tad_cal_cate_all()
{
    global $xoopsDB;
    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_cate') . '`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    while (false !== ($data = $xoopsDB->fetchArray($result))) {
        $cate_sn = $data['cate_sn'];
        $data_arr[$cate_sn] = $data;
    }

    return $data_arr;
}

//取得行事曆名稱陣列
function get_cal_array()
{
    global $xoopsDB;
    $sql = 'SELECT `cate_sn`, `cate_title` FROM `' . $xoopsDB->prefix('tad_cal_cate') . '`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    while (list($cate_sn, $cate_title) = $xoopsDB->fetchRow($result)) {
        $arr[$cate_sn] = $cate_title;
    }

    return $arr;
}

//抓出事件陣列
function get_events($even_start = '', $even_end = '', $cate_sn_arr = [], $show_type = '', $dl_type = '', $todo_tag = '')
{
    global $xoopsDB;

    //取得目前使用者可讀的群組
    $ok_cate_arr = Tools::chk_tad_cal_cate_power('enable_group');

    if (!empty($cate_sn_arr)) {
        foreach ($cate_sn_arr as $cate_sn) {
            if (in_array($cate_sn, $ok_cate_arr)) {
                $ok_arr[] = $cate_sn;
            }
        }
    } else {
        $ok_arr = $ok_cate_arr;
    }

    $params = [];
    $conditions = ["1"]; // 基本條件

    // 可讀類別判別
    if (!empty($ok_arr)) {
        $all_ok_cate = implode(',', array_map('intval', $ok_arr)); // 確保類別為整數
        $conditions[] = "cate_sn IN ($all_ok_cate)";
    } else {
        $conditions[] = "cate_sn = '0'";
    }

    if (!empty($even_start)) {
        $conditions[] = "`start` >= ?";
        $params[] = $even_start;
    }

    if (!empty($even_end)) {
        $conditions[] = "`end` <= ?";
        $params[] = $even_end;
    }

    if (!empty($todo_tag)) {
        $conditions[] = "`tag` = ?";
        $params[] = $todo_tag;
    }

    // 抓出事件
    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE ' . implode(' AND ', $conditions) . ' ORDER BY `start`, `sequence`';

    $result = Utility::query($sql, str_repeat('s', count($params)), $params) or Utility::web_error($sql, __FILE__, __LINE__);

    $i = 0;
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
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
    $params = [];
    $conditions = ["1"]; // 基本條件

    if (!empty($even_start)) {
        $conditions[] = "a.`start` >= ?";
        $params[] = $even_start;
    }

    if (!empty($even_end)) {
        $conditions[] = "a.`end` <= ?";
        $params[] = $even_end;
    }

    if (!empty($todo_tag)) {
        $conditions[] = "b.`tag` = ?";
        $params[] = $todo_tag;
    }

    // 抓出重複事件
    $sql = 'SELECT a.*, b.title, b.cate_sn FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` AS a JOIN `' . $xoopsDB->prefix('tad_cal_event') . "` AS b ON a.sn = b.sn WHERE " . implode(' AND ', $conditions) . " $and_ok_cate2 ORDER BY a.`start`";

    $result = Utility::query($sql, str_repeat('s', count($params)), $params) or Utility::web_error($sql, __FILE__, __LINE__);

    while (false !== ($all = $xoopsDB->fetchArray($result))) {
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
