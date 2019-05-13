<?php
use XoopsModules\Tadtools\Utility;

require_once __DIR__ . '/header.php';

/* 連資料庫檢查 */
header('Content-type: application/json');
echo get_event();

//取得事件
function get_event()
{
    global $xoopsDB, $xoopsUser, $xoopsModuleConfig;

    $cate_sn = (int) $_REQUEST['cate_sn'];

    //取得目前使用者可讀的群組
    $ok_cate_arr = chk_tad_cal_cate_power('enable_group');
    $all_ok_cate = implode(',', $ok_cate_arr);
    $and_ok_cate = empty($all_ok_cate) ? "and a.`cate_sn`='0'" : "and a.`cate_sn` in($all_ok_cate)";
    $and_ok_cate2 = empty($all_ok_cate) ? "and a.sn='0'" : "and b.cate_sn in($all_ok_cate)";

    $even_start = $_REQUEST['start'] ? date('Y-m-d', strtotime($_REQUEST['start'])) : date('Y-m-d H:i:s');
    $even_end = $_REQUEST['end'] ? date('Y-m-d', strtotime($_REQUEST['end'])) : date('Y-m-t H:i:s');
    // $even_end   = ($_REQUEST['end'] == "0000-00-00 00:00") ? date("Y-m-t H:i:s") : $_REQUEST['end'];

    $and_cate_sn = empty($cate_sn) ? '' : "and a.`cate_sn` = '$cate_sn'";
    $and_cate_sn2 = empty($cate_sn) ? '' : "and b.`cate_sn` = '$cate_sn'";

    //抓出事件
    $sql = 'select a.*, b.`cate_bgcolor`, b.`cate_color` from ' . $xoopsDB->prefix('tad_cal_event') . ' as a
    join ' . $xoopsDB->prefix('tad_cal_cate') . " as b on a.`cate_sn`=b.`cate_sn`
    where ((a.`start` >= '$even_start' and a.`end` <= '$even_end') or (a.`start` <= '$even_end' and a.`end` > '$even_end') or (a.`start` <= '$even_start' and a.`end` > '$even_start')) $and_ok_cate $and_cate_sn
    order by a.`start` , a.`sequence`";

    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    $i = 0;
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }
        if (!empty($recurrence)) {
            continue;
        }

        $allDay = ('1' == $allday) ? true : false;

        $startTime = strtotime($start);
        $endTime = strtotime($end);
        if (empty($endTime)) {
            $endTime = $startTime + 86400;
        }

        //計算應顯示數字用
        $day = ceil(($endTime - $startTime) / 86400);
        if (empty($day)) {
            $day = 1;
        }

        // 轉換成使用者的時區
        $start = date('Y-m-d H:i:s', xoops_getUserTimestamp($startTime));
        $end = date('Y-m-d H:i:s', xoops_getUserTimestamp($endTime));

        //避免截掉半個中文字
        $title_num = $xoopsModuleConfig['title_num'] * 3 * $day;

        $event_title = xoops_substr(strip_tags($title), 0, $title_num);

        $myEvents[$i]['id'] = $sn;
        $myEvents[$i]['title'] = ($event_title);
        $myEvents[$i]['url'] = XOOPS_URL . "/modules/tad_cal/event.php?sn=$sn";
        $myEvents[$i]['rel'] = XOOPS_URL . "/modules/tad_cal/event.php?op=view&sn=$sn";
        $myEvents[$i]['start'] = $start;
        if (!empty($end)) {
            $myEvents[$i]['end'] = $end;
        }

        $myEvents[$i]['allDay'] = $allDay;
        $myEvents[$i]['className'] = "my{$cate_sn}";
        $myEvents[$i]['color'] = $cate_bgcolor;
        $myEvents[$i]['textColor'] = $cate_color;

        $i++;
    }

    //抓出重複事件
    $sql = 'select a.*, b.`title`, b.`cate_sn`, c.`cate_bgcolor`, c.`cate_color`
    from ' . $xoopsDB->prefix('tad_cal_repeat') . ' as a
    join ' . $xoopsDB->prefix('tad_cal_event') . ' as b on a.`sn` = b.`sn`
    join ' . $xoopsDB->prefix('tad_cal_cate') . " as c on b.`cate_sn` = c.`cate_sn`
    where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2 $and_cate_sn2
    order by a.`start`";
    // die($sql);
    $result = $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        $allDay = ('1' == $allday) ? true : false;

        //正確顯示行事曆事件條
        $DBstartTime = strtotime($start);
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        if (empty($endTime)) {
            $endTime = $startTime + 86400;
        }

        //計算應顯示數字用
        $day = ceil(($endTime - $startTime) / 86400);
        if (empty($day)) {
            $day = 1;
        }

        // 轉換成使用者的時區
        if (!$allDay) {
            $start = date('Y-m-d H:i:s', xoops_getUserTimestamp($startTime));
            $end = date('Y-m-d H:i:s', xoops_getUserTimestamp($endTime));
        }

        //避免截掉半個中文字
        $title_num = $xoopsModuleConfig['title_num'] * 3 * $day;
        //if(empty($title_num))$title_num=21;

        $event_title = xoops_substr(strip_tags($title), 0, $title_num);

        $myEvents[$i]['id'] = $sn;
        $myEvents[$i]['title'] = "* {$event_title}";
        //$myEvents[$i]['url']="event.php?sn=$sn&stamp=$startTime";
        $myEvents[$i]['rel'] = XOOPS_URL . "/modules/tad_cal/event.php?op=view&sn=$sn&stamp=$DBstartTime";
        $myEvents[$i]['start'] = $start;
        if (!empty($end)) {
            $myEvents[$i]['end'] = $end;
        }

        $myEvents[$i]['allDay'] = $allDay;
        $myEvents[$i]['className'] = "my{$cate_sn}";
        $myEvents[$i]['color'] = $cate_bgcolor;
        $myEvents[$i]['textColor'] = $cate_color;

        $i++;
    }

    return json_encode($myEvents, JSON_UNESCAPED_UNICODE);
}
