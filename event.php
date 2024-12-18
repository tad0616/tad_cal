<?php
use Xmf\Request;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tad_cal\Tools;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$xoopsOption['template_main'] = 'tad_cal_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');
$sn = Request::getInt('sn');
$stamp = Request::getInt('stamp');
$tag = Request::getString('tag');

switch ($op) {
    case "todo_status_todo":
        update_tad_cal_todo_event($sn, "todo");
        header("location: {$_SERVER['HTTP_REFERER']}");
        exit;

    case "todo_status_todo_ok":
        update_tad_cal_todo_event($sn, "todo-ok");
        header("location: {$_SERVER['HTTP_REFERER']}");
        exit;

    //ajax_update_date
    case 'ajax_update_date':

        die(ajax_update_date($sn));

    //新增資料
    case 'insert_tad_cal_event':
        insert_tad_cal_event();
        header('location: ' . XOOPS_URL . '/modules/tad_cal/index.php');
        exit;

    //更新資料
    case 'update_tad_cal_event':
        update_tad_cal_event($sn);
        header('location: ' . XOOPS_URL . '/modules/tad_cal/index.php');
        exit;

    //輸入表格
    case 'tad_cal_event_form':
        tad_cal_event_form($sn, '', $tag);
        break;

    //刪除資料
    case 'delete_tad_cal_event':
        delete_tad_cal_event($sn);
        header('location: ' . XOOPS_URL . '/modules/tad_cal/index.php');
        exit;

    case 'view':
        show_simple_event($sn, $stamp);
        break;

    case 'list':
        list_tad_cal_event();
        break;

    case 'todo_list':
        list_tad_cal_event("todo");
        $op = "todo_list_tad_cal_event";
        break;

    case 'todo_list_ok':
        list_tad_cal_event("todo-ok");
        $op = "todo_list_tad_cal_event";
        break;

    //預設動作
    default:
        if (empty($sn)) {
            tad_cal_event_form();
            $op = "tad_cal_event_form";
        } else {
            show_one_tad_cal_event($sn, $stamp);
            $op = "show_one_tad_cal_event";
        }
        break;
}

/*-----------秀出結果區--------------*/
$SweetAlert = new SweetAlert();
$SweetAlert->render("delete_tad_cal_event_func", "event.php?op=delete_tad_cal_event&sn=", 'sn');
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('now_op', $op);

require_once XOOPS_ROOT_PATH . '/footer.php';

/*-----------function區--------------*/

function toServerTime($time)
{
    global $xoopsConfig, $xoopsUser;
    if ($xoopsUser) {
        $timeoffset = $xoopsUser->getVar('timezone_offset');
    } else {
        $timeoffset = $xoopsConfig['default_TZ'];
    }
    $usertimestamp = (int) $time + ((float) $xoopsConfig['server_TZ'] - $timeoffset) * 3600;

    return $usertimestamp;
}

//tad_cal_event編輯表單 $mode=ajax
function tad_cal_event_form($sn = '', $mode = '', $def_tag = '')
{
    global $xoTheme, $xoopsUser, $xoopsTpl;
    require_once XOOPS_ROOT_PATH . '/modules/tad_cal/class/Ical.php';
    if (!$xoopsUser) {
        redirect_header(XOOPS_URL . '/modules/tad_cal/index.php', 3, _MD_TADCAL_NEED_LOGIN);
        exit;
    }

    //抓取預設值
    if (!empty($sn)) {
        $DBV = get_tad_cal_event($sn);
    } else {
        $DBV = [];
    }

    //預設值設定

    //設定「sn」欄位預設值
    $sn = (!isset($DBV['sn'])) ? $sn : $DBV['sn'];

    //設定「title」欄位預設值
    $title = (!isset($DBV['title'])) ? '' : $DBV['title'];

    //設定「start」欄位預設值
    $start = (!isset($DBV['start'])) ? date('Y-m-d H:00') : mb_substr($DBV['start'], 0, 16);

    //設定「end」欄位預設值
    $end = (!isset($DBV['end'])) ? date('Y-m-d H:00') : mb_substr($DBV['end'], 0, 16);

    //設定「recurrence」欄位預設值
    $recurrence = (!isset($DBV['recurrence'])) ? '' : $DBV['recurrence'];
    $repeat = empty($recurrence) ? 0 : 1;

    //設定「location」欄位預設值
    $location = (!isset($DBV['location'])) ? '' : $DBV['location'];

    //設定「kind」欄位預設值
    $kind = (!isset($DBV['kind'])) ? 'event' : $DBV['kind'];

    //設定「details」欄位預設值
    $details = (!isset($DBV['details'])) ? '' : $DBV['details'];

    //設定「etag」欄位預設值
    $etag = (!isset($DBV['etag'])) ? '' : $DBV['etag'];

    //設定「id」欄位預設值
    $id = (!isset($DBV['id'])) ? '' : $DBV['id'];

    //設定「sequence」欄位預設值
    $sequence = (!isset($DBV['sequence'])) ? tad_cal_event_max_sort() : $DBV['sequence'];

    //設定「uid」欄位預設值
    $user_uid = ($xoopsUser) ? $xoopsUser->uid() : '';
    $uid = (!isset($DBV['uid'])) ? $user_uid : $DBV['uid'];

    //設定「cate_sn」欄位預設值
    $cate_sn = (!isset($DBV['cate_sn'])) ? '' : $DBV['cate_sn'];

    //設定「allday」欄位預設值
    $allday = (!isset($DBV['allday'])) ? '1' : $DBV['allday'];

    //設定「tag」欄位預設值
    $tag = (!isset($DBV['tag'])) ? $def_tag : $DBV['tag'];

    $op = (empty($sn)) ? 'insert_tad_cal_event' : 'update_tad_cal_event';
    //$op="replace_tad_cal_event";

    $get_tad_cal_cate_menu_options = Tools::get_tad_cal_cate_menu_options($cate_sn);
    if (empty($get_tad_cal_cate_menu_options)) {
        if ($_SESSION['tad_cal_adm']) {
            $of_cate_title = _MD_TADCAL_NEW_CATE;
            $cate_col = "<input name='new_cate_title' id='new_cate_title' value='" . _MD_TADCAL_NEW_CALENDAR . "' class='span4'>";
        } else {
            redirect_header(XOOPS_URL . '/modules/tad_cal/index.php', 3, _MD_TADCAL_NO_POWER);
            exit;
        }
    } else {
        $of_cate_title = _MD_TADCAL_CATE_SN;
        $cate_col = "
        <select name='cate_sn' title='cate_sn' size=1 class='form-control form-select'>
        {$get_tad_cal_cate_menu_options}
        </select>";
    }

    $xoopsTpl->assign('of_cate_title', $of_cate_title);
    $xoopsTpl->assign('cate_col', $cate_col);

    //紀錄目前結束-開始的時間差
    $long = strtotime($end) - strtotime($start);

    $rrule_array = [];
    $rrule_arr = [];
    if (!empty($recurrence)) {
        $ical = new \XoopsModules\Tad_cal\Ical();
        $ical->parse($recurrence);
        $rrule_array = $ical->get_all_data();
        $rrule_arr = $rrule_array[''];
        /*
    die(var_export($rrule_arr));
    $rrule_arr['DTSTART']['VALUE']='DATE';
    $rrule_arr['DTSTART']['unixtime']='1320336000';
    $rrule_arr['DTEND']['VALUE']='DATE';
    $rrule_arr['DTEND']['unixtime']='1320422400';
    $rrule_arr['RRULE']['FREQ']='DAILY';
    $rrule_arr['RRULE']['COUNT']='10';
    $rrule_arr['RRULE']['INTERVAL']='10';
    $rrule_arr['RRULE']['BYDAY']='SU,SA',
     */
    } else {
        $rrule_arr['DTSTART']['VALUE'] = '';
        $rrule_arr['DTSTART']['unixtime'] = '';
        $rrule_arr['DTEND']['VALUE'] = '';
        $rrule_arr['DTEND']['unixtime'] = '';
        $rrule_arr['RRULE']['FREQ'] = '';
        $rrule_arr['RRULE']['COUNT'] = '';
        $rrule_arr['RRULE']['INTERVAL'] = '';
        $rrule_arr['RRULE']['BYDAY'] = '';
        $rrule_arr['RRULE']['UNTIL'] = '';
    }

    if ($repeat) {
        $format = ($allday) ? 'Y-m-d' : 'Y-m-d H:i';
        $start = date($format, $rrule_arr['DTSTART']['unixtime']);
        $end = date($format, $rrule_arr['DTEND']['unixtime']);
    }

    //重複事件用的
    $INTERVAL_OPT = '';
    for ($i = 1; $i <= 30; $i++) {
        $selected = (isset($rrule_arr['RRULE']['INTERVAL']) and $i == $rrule_arr['RRULE']['INTERVAL']) ? 'selected' : '';
        $INTERVAL_OPT .= "<option value='$i' $selected>$i</option>";
    }

    $weekday = ['SU' => _MD_TADCAL_SU, 'MO' => _MD_TADCAL_MO, 'TU' => _MD_TADCAL_TU, 'WE' => _MD_TADCAL_WE, 'TH' => _MD_TADCAL_TH, 'FR' => _MD_TADCAL_FR, 'SA' => _MD_TADCAL_SA];
    $week_repeat_col = '';
    $warr = (isset($rrule_arr['RRULE']['FREQ']) and 'WEEKLY' === $rrule_arr['RRULE']['FREQ']) ? explode(',', $rrule_arr['RRULE']['BYDAY']) : [mb_strtoupper(mb_substr(date('D', strtotime($start)), 0, 2))];

    foreach ($weekday as $en => $ch) {
        $checked = (in_array($en, $warr)) ? 'checked' : '';
        $week_repeat_col .= "
        <label class='checkbox-inline'>
          <input type='checkbox' name='BYDAY[]' value='{$en}' id='{$en}' $checked> {$ch}
        </label> ";
    }

    //$rrule_arr['RRULE']['BYDAY']

    if (!empty($rrule_arr['RRULE']['UNTIL'])) {
        $ENDType = 'until';
    } elseif (!empty($rrule_arr['RRULE']['COUNT'])) {
        $ENDType = 'count';
    } else {
        $ENDType = 'none';
    }

    if (isset($rrule_arr['RRULE']['FREQ']) and 'YEARLY' === $rrule_arr['RRULE']['FREQ']) {
        $repeat_unit = _MD_TADCAL_Y;
    } elseif (isset($rrule_arr['RRULE']['FREQ']) and 'MONTHLY' === $rrule_arr['RRULE']['FREQ']) {
        $repeat_unit = _MD_TADCAL_M;
        $week = getWeekOfTheMonth(strtotime($start));
        $wk = date('w', strtotime($start));
    } elseif (isset($rrule_arr['RRULE']['FREQ']) and 'WEEKLY' === $rrule_arr['RRULE']['FREQ']) {
        $repeat_unit = _MD_TADCAL_W;
    } else {
        $repeat_unit = _MD_TADCAL_D;
    }

    $UNTIL = ($allday) ? $rrule_arr['RRULE']['UNTIL'] : date('Y-m-d', strtotime($rrule_arr['RRULE']['UNTIL']));

    $xoopsTpl->assign('chk_DAILY', Utility::chk($rrule_arr['RRULE']['FREQ'], 'DAILY', 1, 'selected'));
    $xoopsTpl->assign('chk_WEEKLY', Utility::chk($rrule_arr['RRULE']['FREQ'], 'WEEKLY', 0, 'selected'));
    $xoopsTpl->assign('chk_MONTHLY', Utility::chk($rrule_arr['RRULE']['FREQ'], 'MONTHLY', 0, 'selected'));
    $xoopsTpl->assign('chk_YEARLY', Utility::chk($rrule_arr['RRULE']['FREQ'], 'YEARLY', 0, 'selected'));
    $xoopsTpl->assign('INTERVAL_OPT', $INTERVAL_OPT);
    $xoopsTpl->assign('repeat_unit', $repeat_unit);
    $xoopsTpl->assign('week_repeat_col', $week_repeat_col);
    $xoopsTpl->assign('RRULE_BYDAY', Utility::chk($rrule_arr['RRULE']['BYDAY']));
    $xoopsTpl->assign('bymonthday', sprintf(_MD_TADCAL_BYMONTHDAY, "<span id='bymonthday'></span>"));
    $xoopsTpl->assign('ENDType_none', Utility::chk($ENDType, 'none'));
    $xoopsTpl->assign('ENDType_count', Utility::chk($ENDType, 'count'));
    $xoopsTpl->assign('ENDType_until', Utility::chk($ENDType, 'until'));
    $xoopsTpl->assign('RRULE_COUNT', $rrule_arr['RRULE']['COUNT']);
    $xoopsTpl->assign('UNTIL', $UNTIL);

    $FormValidator = new FormValidator('#myForm', true);
    $FormValidator->render();

    $start_allday = mb_substr($start, 0, 10);
    if ($allday) {
        $end_allday = date('Y-m-d', strtotime($end) - 86400);
        if (strtotime($end_allday) < strtotime($start_allday)) {
            $end_allday = $start_allday;
        }
    } else {
        $end_allday = mb_substr($end, 0, 10);
    }
    $show_repeat_box = ($repeat) ? "$('#repeat_box').show();" : "$('#repeat_box').hide();";
    $show_week_repeat = ('WEEKLY' === $rrule_arr['RRULE']['FREQ']) ? "$('#week_repeat').show();" : "$('#week_repeat').hide();";
    $show_month_repeat = ('MONTHLY' === $rrule_arr['RRULE']['FREQ']) ? "$('#month_repeat').show();" : "$('#month_repeat').hide();";
    $show_allday_date = ($allday) ? "$('#start').hide().attr('disabled','disabled');\n$('#end').hide().attr('disabled','disabled');" : "$('#start_allday').hide().attr('disabled','disabled');\n$('#end_allday').hide().attr('disabled','disabled');";

    $xoopsTpl->assign('next_op', $op);
    $xoopsTpl->assign('sequence', $sequence);
    $xoopsTpl->assign('id', $id);
    $xoopsTpl->assign('etag', $etag);
    $xoopsTpl->assign('details', $details);
    $xoopsTpl->assign('location', $location);
    $xoopsTpl->assign('chk_allday_1', Utility::chk($allday, '1'));
    $xoopsTpl->assign('chk_repeat_1', Utility::chk($repeat, '1'));
    $xoopsTpl->assign('end', $end);
    $xoopsTpl->assign('end_allday', $end_allday);
    $xoopsTpl->assign('start', $start);
    $xoopsTpl->assign('start_allday', $start_allday);
    $xoopsTpl->assign('title', $title);
    $xoopsTpl->assign('show_allday_date', $show_allday_date);
    $xoopsTpl->assign('show_repeat_box', $show_repeat_box);
    $xoopsTpl->assign('mode', $mode);
    $xoopsTpl->assign('show_week_repeat', $show_week_repeat);
    $xoopsTpl->assign('show_month_repeat', $show_month_repeat);
    $xoopsTpl->assign('cate_col', $cate_col);
    $xoopsTpl->assign('op', 'tad_cal_event_form');
    $xoopsTpl->assign('sn', $sn);
    $xoopsTpl->assign('tag', $tag);

    $xoTheme->addScript('modules/tadtools/My97DatePicker/WdatePicker.js');
    $xoTheme->addScript('modules/tad_cal/class/date.js');
    $xoTheme->addScript('modules/tad_cal/class/strtotime.js');

}

//自動取得tad_cal_event的最新排序
function tad_cal_event_max_sort()
{
    global $xoopsDB;
    $sql = 'SELECT MAX(`sequence`) FROM `' . $xoopsDB->prefix('tad_cal_event') . '`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    list($sort) = $xoopsDB->fetchRow($result);

    return ++$sort;
}

//新增資料到tad_cal_event中
function insert_tad_cal_event()
{
    global $xoopsDB, $xoopsUser;

    $cate_sn = (int) $_POST['cate_sn'];

    //取得使用者編號
    $uid = ($xoopsUser) ? $xoopsUser->uid() : 0;

    $title = (string) $_POST['title'];
    $location = (string) $_POST['location'];
    $kind = (string) $_POST['kind'];
    $details = (string) $_POST['details'];
    $etag = (string) $_POST['etag'];
    $tag = (string) $_POST['tag'];
    $id = (string) $_POST['id'];
    $sequence = (int) $_POST['sequence'];
    $new_cate_title = (string) $_POST['new_cate_title'];

    //若無分類編號，那就是要新增行事曆
    if (empty($cate_sn)) {
        if (empty($new_cate_title)) {
            $new_cate_title = _MD_TADCAL_NEW_CALENDAR;
        }

        $cate_sn = create_cate($new_cate_title);
    }

    $allDay = !empty($_POST['fc_start']) ? 1 : $_POST['allday'];

    if ('1' == $allDay) {
        $start = date('Y-m-d', strtotime($_POST['start']));
        if (empty($_POST['end'])) {
            $_POST['end'] = (string) $_POST['start'];
        }

        $end = strtotime($_POST['end']) + (empty($_POST['fc']) ? 86400 : 0);
        $end = date('Y-m-d', $end);
        $rrule_start = str_replace('-', '', $start);
        $rrule_end = str_replace('-', '', $end);
        $DTSTART = "DTSTART;VALUE=DATE:{$rrule_start}\nDTEND;VALUE=DATE:{$rrule_end}\n";
    } else {
        $start = date('Y-m-d H:i', strtotime($_POST['start']));
        if (empty($_POST['end'])) {
            $_POST['end'] = (string) $_POST['start'];
        }

        $end = date('Y-m-d H:i', strtotime($_POST['end']));
        $rrule_start = mb_substr(str_replace(':', '', str_replace('-', '', date('c', $start))), 0, 15);
        $rrule_end = mb_substr(str_replace(':', '', str_replace('-', '', date('c', $end))), 0, 15);
        $TZ = date('e'); //Asia/Taipei
        $DTSTART = "DTSTART;TZID={$TZ}:{$rrule_start}\nDTEND;TZID={$TZ}:{$rrule_end}\n";
    }

    // Conver to user timezone
    if ('1' != $allDay) {
        $start = date('Y-m-d H:i:s', toServerTime(strtotime($start)));
        $end = date('Y-m-d H:i:s', toServerTime(strtotime($end)));
    }

    $recurrence = '';
    if ('1' == $_POST['repeat']) {
        //FREQ=(YEARLY|MONTHLY|WEEKLY|DAILY) UNTIL= COUNT= INTERVAL= BYDAY= BYMONTHDAY= BYSETPOS= WKST= BYYEARDAY= BYWEEKNO= BYMONTH=
        if ($_POST['INTERVAL'] > 1) {
            $INTERVAL = "INTERVAL={$_POST['INTERVAL']};";
        }

        $BYDAY = $BYMONTHDAY = '';
        if ('WEEKLY' === $_POST['FREQ']) {
            $BYDAY = 'BYDAY=' . implode(',', $_POST['BYDAY']) . ';';
        } elseif ('MONTHLY' === $_POST['FREQ']) {
            if ('BYMONTHDAY' === $_POST['month_repeat']) {
                //算一下起始日期是當月的第幾天
                $BYMONTHDAY = 'BYMONTHDAY=' . date('j', strtotime($start)) . ';';
            } elseif ('BYDAY' === $_POST['month_repeat']) {
                //算一下起始日期是當月第幾週的星期幾
                $startTime = strtotime($start);
                $BYDAY = 'BYDAY=' . getWeekOfTheMonth($startTime) . mb_strtoupper(mb_substr(date('D', $startTime), 0, 2)) . ';';
            }
        }

        $COUNT = $UNTIL = '';
        if ('count' === $_POST['END']) {
            $counter = empty($_POST['COUNT']) ? 1 : (int) $_POST['COUNT'];
            $COUNT = "COUNT={$counter};";
        } elseif ('until' === $_POST['END']) {
            if ('1' == $allDay) {
                $UNTIL = date('Y-m-d', strtotime($_POST['UNTIL']));
                $untilday = str_replace('-', '', $UNTIL);
            } else {
                $UNTIL = date('Y-m-d H:i:s', strtotime($_POST['UNTIL']));
                $untilday = mb_substr(str_replace(':', '', str_replace('-', '', date('c', $UNTIL))), 0, 15);
            }
            $UNTIL = "UNTIL={$untilday};";
        }

        $recurrence = mb_substr("{$DTSTART}RRULE:FREQ={$_POST['FREQ']};{$COUNT}{$UNTIL}{$INTERVAL}{$BYDAY}{$BYMONTHDAY}", 0, -1);
        $start = $end = '0000-00-00 00:00:00';
    }

    $last_update = date('Y-m-d H:i:s');

    $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_cal_event') . '`
    (`title`, `start`, `end`, `recurrence`, `location`, `kind`, `details`, `etag`, `id`, `sequence`, `uid`, `cate_sn`, `allday`, `tag`, `last_update`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    Utility::query($sql, 'sssssssssiiisss', [$title, $start, $end, $recurrence, $location, $kind, $details, $etag, $id, $sequence, $uid, $cate_sn, $allDay, $tag, $last_update]) or Utility::web_error($sql, __FILE__, __LINE__);

    //取得最後新增資料的流水編號
    $sn = $xoopsDB->getInsertId();

    //更新 id
    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_event') . '` SET `id` = ? WHERE `sn` = ?';
    Utility::query($sql, 'ii', [$sn, $sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    //重複事件
    Tools::rrule($sn, $recurrence, $allDay);

    return $sn;
}

//更新tad_cal_event某一筆資料
function update_tad_cal_todo_event($sn = '', $todo = null)
{
    global $xoopsDB;

    if (!is_null($todo)) {
        $tag = $todo;
    } else {
        $tag = '';
    }

    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_event') . '` SET `tag` = ? WHERE `sn` = ?';
    Utility::query($sql, 'si', [$tag, $sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    return $sn;
}

//更新tad_cal_event某一筆資料
function update_tad_cal_event($sn = '')
{
    global $xoopsDB, $xoopsUser;
    //die(var_export($_POST));
    //取得使用者編號
    $uid = ($xoopsUser) ? $xoopsUser->uid() : '';
    $tag = (string) $_POST['tag'];

    $allDay = !empty($_POST['fc_start']) ? 1 : $_POST['allday'];

    if ('1' == $allDay) {
        $start = date('Y-m-d', strtotime($_POST['start']));
        if (empty($_POST['end'])) {
            $_POST['end'] = (string) $_POST['start'];
        }

        $end = strtotime($_POST['end']) + (empty($_POST['fc']) ? 86400 : 0);
        $end = date('Y-m-d', $end);
        $rrule_start = str_replace('-', '', $start);
        $rrule_end = str_replace('-', '', $end);
        $DTSTART = "DTSTART;VALUE=DATE:{$rrule_start}\nDTEND;VALUE=DATE:{$rrule_end}\n";
    } else {
        $start = date('Y-m-d H:i', strtotime($_POST['start']));
        if (empty($_POST['end'])) {
            $_POST['end'] = (string) $_POST['start'];
        }

        $end = date('Y-m-d H:i', strtotime($_POST['end']));
        $rrule_start = mb_substr(str_replace(':', '', str_replace('-', '', date('c', strtotime($start)))), 0, 15);
        $rrule_end = mb_substr(str_replace(':', '', str_replace('-', '', date('c', strtotime($end)))), 0, 15);
        $TZ = date('e'); //Asia/Taipei
        $DTSTART = "DTSTART;TZID={$TZ}:{$rrule_start}\nDTEND;TZID={$TZ}:{$rrule_end}\n";
    }

    // Conver to user timezone
    if ('1' != $allDay) {
        $start = date('Y-m-d H:i:s', toServerTime(strtotime($start)));
        $end = date('Y-m-d H:i:s', toServerTime(strtotime($end)));
    }
    //die("{$start}:00={$DTSTART}=".date("c",strtotime("{$start}:00")));
    $recurrence = '';
    if ('1' == $_POST['repeat']) {
        //FREQ=(YEARLY|MONTHLY|WEEKLY|DAILY) UNTIL= COUNT= INTERVAL= BYDAY= BYMONTHDAY= BYSETPOS= WKST= BYYEARDAY= BYWEEKNO= BYMONTH=
        if ($_POST['INTERVAL'] > 1) {
            $INTERVAL = "INTERVAL={$_POST['INTERVAL']};";
        }

        $BYDAY = $BYMONTHDAY = '';
        if ('WEEKLY' === $_POST['FREQ']) {
            $BYDAY = 'BYDAY=' . implode(',', $_POST['BYDAY']) . ';';
        } elseif ('MONTHLY' === $_POST['FREQ']) {
            if ('BYMONTHDAY' === $_POST['month_repeat']) {
                //算一下起始日期是當月的第幾天
                $BYMONTHDAY = 'BYMONTHDAY=' . date('j', strtotime($start)) . ';';
            } elseif ('BYDAY' === $_POST['month_repeat']) {
                //算一下起始日期是當月第幾週的星期幾
                $startTime = strtotime($start);
                $BYDAY = 'BYDAY=' . getWeekOfTheMonth($startTime) . mb_strtoupper(mb_substr(date('D', $startTime), 0, 2)) . ';';
            }
        }

        $COUNT = $UNTIL = '';
        if ('count' === $_POST['END']) {
            $counter = empty($_POST['COUNT']) ? 1 : (int) $_POST['COUNT'];
            $COUNT = "COUNT={$counter};";
        } elseif ('until' === $_POST['END']) {
            if ('1' == $allDay) {
                $UNTIL = date('Y-m-d', strtotime($_POST['UNTIL']));
                $untilday = str_replace('-', '', $UNTIL);
            } else {
                $UNTIL = date('Y-m-d 23:59:59', strtotime($_POST['UNTIL']));
                $untilday = mb_substr(str_replace(':', '', str_replace('-', '', date('c', strtotime($UNTIL)))), 0, 15);
            }
            $UNTIL = "UNTIL={$untilday};";
        }

        $recurrence = mb_substr("{$DTSTART}RRULE:FREQ={$_POST['FREQ']};{$COUNT}{$UNTIL}{$INTERVAL}{$BYDAY}{$BYMONTHDAY}", 0, -1);
        $start = $end = '0000-00-00 00:00:00';
    }

    $last_update = date('Y-m-d H:i:s');
    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_event') . '` SET `title`=?, `start`=?, `end`=?, `recurrence`=?, `location`=?, `kind`=?, `details`=?, `etag`=?, `id`=?, `sequence`=?, `uid`=?, `cate_sn`=?, `allday`=?, `tag`=?, `last_update`=? WHERE `sn`=?';
    Utility::query($sql, 'sssssssssiiisssi', [
        $_POST['title'],
        $start,
        $end,
        $recurrence,
        $_POST['location'],
        $_POST['kind'],
        $_POST['details'],
        $_POST['etag'],
        $_POST['id'],
        $_POST['sequence'],
        $uid,
        $_POST['cate_sn'],
        $allDay,
        $tag,
        $last_update,
        $sn,
    ]) or Utility::web_error($sql, __FILE__, __LINE__);

    //重複事件
    Tools::rrule($sn, $recurrence, $allDay);

    return $sn;
}

//列出所有tad_cal_event資料
function list_tad_cal_event($tag = "")
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $cate = get_tad_cal_cate_all();

    $now_uid = ($xoopsUser) ? $xoopsUser->uid() : 0;

    //取得目前使用者可讀的群組
    $ok_cate_arr = Tools::chk_tad_cal_cate_power('enable_group');
    $all_ok_cate = implode(',', $ok_cate_arr);
    $and_ok_cate = empty($all_ok_cate) ? "cate_sn='0'" : "a.cate_sn in($all_ok_cate)";
    $and_tag = $tag != "" ? "and a.`tag`='$tag'" : "";

    //可編輯的行事曆
    $edit_cate_arr = Tools::chk_tad_cal_cate_power('enable_upload_group');

    $show_function = count($edit_cate_arr) ? 1 : 0;

    //$sql = "select * from ".$xoopsDB->prefix("tad_cal_event")."  where $and_ok_cate order by start desc , sequence";

    $now = date('Y-m-d H:i:s', xoops_getUserTimestamp(time()));
    $sql = 'select a.*,b.start as re_start,b.end as re_end from ' . $xoopsDB->prefix('tad_cal_event') . ' as a left join ' . $xoopsDB->prefix('tad_cal_repeat') . " as b on a.sn=b.sn and b.start < '{$now}'  where $and_ok_cate $and_tag order by b.start desc,a.start desc , a.sequence";

    //getPageBar($原sql語法, 每頁顯示幾筆資料, 最多顯示幾個頁數選項);
    $PageBar = Utility::getPageBar($sql, 20, 10);
    $bar = $PageBar['bar'];
    $sql = $PageBar['sql'];
    $total = $PageBar['total'];

    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    $all_content = [];

    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        //避免截掉半個中文字
        $details = xoops_substr(strip_tags($details), 0, 60);

        $fun = ($show_function and ($_SESSION['tad_cal_adm'] or $now_uid == $uid)) ? "
        <td>
        <a href='{$_SERVER['PHP_SELF']}?op=tad_cal_event_form&sn=$sn' class='btn btn-sm btn-warning'>" . _TAD_EDIT . "</a>
        <a href=\"javascript:delete_tad_cal_event_func($sn);\" class='btn btn-sm btn-danger'>" . _TAD_DEL . '</a>
        </td>' : '';

        $re = ('0000-00-00 00:00:00' === $start) ? '*' : '';
        $start = ('0000-00-00 00:00:00' === $start) ? $re_start : $start;
        $end = ('0000-00-00 00:00:00' === $end) ? $re_end : $end;

        $date = show_date($start, $end, $allday);

        $all_content[$i]['date'] = $date;
        $all_content[$i]['sn'] = $sn;
        $all_content[$i]['re'] = $re;
        $all_content[$i]['title'] = $title;
        $all_content[$i]['details'] = $details;
        $all_content[$i]['location'] = $location;
        $all_content[$i]['cate_title'] = $cate[$cate_sn]['cate_title'];
        $all_content[$i]['tag'] = $tag;
        $all_content[$i]['fun'] = $fun;
        $i++;
    }

    $xoopsTpl->assign('op', 'list_tad_cal_event');
    $xoopsTpl->assign('bar', $bar);
    $xoopsTpl->assign('add', $add);
    $xoopsTpl->assign('all_content', $all_content);
}

//以流水號取得某筆tad_cal_event資料
function get_tad_cal_event($sn = '')
{
    global $xoopsDB;
    if (empty($sn)) {
        return;
    }

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=?';
    $result = Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    $data = $xoopsDB->fetchArray($result);

    return $data;
}

//刪除tad_cal_event某筆資料資料
function delete_tad_cal_event($sn = '')
{
    global $xoopsDB, $xoopsUser;
    $uid = $xoopsUser->uid();
    $andUID = ($_SESSION['tad_cal_adm']) ? '' : 'AND `uid` = ?';
    $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn` = ? ' . $andUID;
    $params = ($_SESSION['tad_cal_adm']) ? [$sn] : [$sn, $uid];

    if (Utility::query($sql, 'i' . (($_SESSION['tad_cal_adm']) ? '' : 'i'), $params)) {
        $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` WHERE `sn` = ?';
        Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    } else {
        Utility::web_error($sql, __FILE__, __LINE__);
    }
}

//以流水號秀出某筆tad_cal_event資料內容
function show_one_tad_cal_event($sn = '', $stamp = '')
{
    global $xoopsDB, $xoopsUser, $xoopsModuleConfig, $xoopsTpl;

    $xoopsTpl->assign('op', 'show_one_tad_cal_event');

    if (empty($sn)) {
        return;
    }
    $sn = (int) $sn;

    $now_uid = ($xoopsUser) ? $xoopsUser->uid() : 0;

    $cate = get_tad_cal_cate_all();
    if (!empty($stamp)) {
        $date = date('Y-m-d H:i', $stamp);
        $sql = 'SELECT a.*, b.`title`, b.`recurrence`, b.`location`, b.`kind`, b.`details`, b.`etag`, b.`id`, b.`sequence`, b.`uid`, b.`cate_sn`, b.`tag`, b.`last_update` FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` AS a JOIN `' . $xoopsDB->prefix('tad_cal_event') . '` AS b ON a.`sn`=b.`sn` WHERE a.`sn`=? AND a.`start`=?';
        $result = Utility::query($sql, 'is', [$sn, $date]) or Utility::web_error($sql, __FILE__, __LINE__);

    } else {
        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn` = ?';
        $result = Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    }
    $all = $xoopsDB->fetchArray($result);

    //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach ($all as $k => $v) {
        $$k = $v;
    }

    $date = show_date($start, $end, $allday, '~');

    //以uid取得使用者名稱
    $uid_name = \XoopsUser::getUnameFromId($uid, 1);
    if (empty($uid_name)) {
        $uid_name = \XoopsUser::getUnameFromId($uid, 0);
    }

    $details = nl2br($details);

    $location_img = empty($location) ? '' : "<img src='" . XOOPS_URL . "/modules/tad_cal/images/location.png' alt='" . _MD_TADCAL_LOCATION . "'>";

    $details = empty($details) ? $title : $details;

    //可編輯的行事曆
    $edit_cate_arr = Tools::chk_tad_cal_cate_power('enable_upload_group');
    $show_function = count($edit_cate_arr) ? 1 : 0;
    $fun = ($show_function and ($_SESSION['tad_cal_adm'] or $now_uid == $uid)) ? "
    <div style='text-align:right;margin-top:10px;'>
    <a href='{$_SERVER['PHP_SELF']}?op=tad_cal_event_form&sn=$sn' class='link_button_r' style='padding:4px;font-size:0.75rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/edit.png' style='margin-right:4px;' align='absmiddle'>" . _TAD_EDIT . "</a>
    <a href=\"javascript:delete_tad_cal_event_func($sn);\" class='link_button_r' style='padding:4px;font-size:0.75rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/delete.png' style='margin-right:4px;' align='absmiddle'>" . _TAD_DEL . '</a>
    </div>' : '';

    $push_url = Utility::push_url($xoopsModuleConfig['use_social_tools']);

    $xoopsTpl->assign('date', $date);
    $xoopsTpl->assign('location_img', $location_img);
    $xoopsTpl->assign('location', $location);
    $xoopsTpl->assign('details', $details);
    $xoopsTpl->assign('cate_title', $cate[$cate_sn]['cate_title']);
    $xoopsTpl->assign('uid_name', $uid_name);

    $xoopsTpl->assign('title', $title);
    $xoopsTpl->assign('push_url', $push_url);
    $xoopsTpl->assign('fun', $fun);
    $xoopsTpl->assign('tag', $tag);
}

//以流水號秀出某筆tad_cal_event資料內容
function show_simple_event($sn = '', $stamp = '')
{
    global $xoopsDB, $xoopsUser, $xoopsLogger;

    if (empty($sn)) {
        return;
    }
    $sn = (int) $sn;

    $now_uid = ($xoopsUser) ? $xoopsUser->uid() : 0;

    $cate = get_tad_cal_cate_all();

    if (!empty($stamp)) {
        $date = date('Y-m-d H:i', $stamp);
        $sql = 'SELECT a.*, b.`title`, b.`recurrence`, b.`location`, b.`kind`, b.`details`, b.`etag`, b.`id`, b.`sequence`, b.`uid`, b.`cate_sn`, b.`tag`, b.`last_update` FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` AS a JOIN `' . $xoopsDB->prefix('tad_cal_event') . '` AS b ON a.`sn`=b.`sn` WHERE a.`sn`=? AND a.`start`=?';
        $result = Utility::query($sql, 'is', [$sn, $date]) or Utility::web_error($sql, __FILE__, __LINE__);
    } else {
        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=?';
        $result = Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    $all = $xoopsDB->fetchArray($result);

    //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach ($all as $k => $v) {
        $$k = $v;
    }

    $date = show_date($start, $end, $allday, '~');
    //以uid取得使用者名稱
    $uid_name = \XoopsUser::getUnameFromId($uid, 1);
    if (empty($uid_name)) {
        $uid_name = \XoopsUser::getUnameFromId($uid, 0);
    }

    $details = nl2br($details);

    $show_location = empty($location) ? '' : "<tr>
  <td nowrap style='font-size:0.813rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/location.png' alt='" . _MD_TADCAL_LOCATION . "' style='margin-right:4px;' align='absmiddle'>{$location}</td>
  </tr>";

    $details = empty($details) ? $title : $details;

    //可編輯的行事曆
    $edit_cate_arr = Tools::chk_tad_cal_cate_power('enable_upload_group');
    $show_function = count($edit_cate_arr) ? 1 : 0;

    $stamp = strtotime($start);
    $andStamp = !empty($recurrence) ? "&stamp={$stamp}" : '';

    $page = "<a href='" . XOOPS_URL . "/modules/tad_cal/event.php?sn={$sn}{$andStamp}' class='link_button_r' style='padding:4px;font-size:0.75rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/event.png' style='margin-right:4px;' align='absmiddle'>" . _MD_TADCAL_EVENT_PAGE . '</a>';

    $fun = ($show_function and ($_SESSION['tad_cal_adm'] or $now_uid == $uid)) ? "
    <div style='margin-top:5px;'>
    <a href='{$_SERVER['PHP_SELF']}?op=tad_cal_event_form&sn=$sn' class='link_button_r' style='padding:4px;font-size:0.75rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/edit.png' style='margin-right:4px;' align='absmiddle'>" . _TAD_EDIT . "</a>
    $page
    <a href=\"javascript:delete_tad_cal_event_func($sn);\" style='padding:4px;font-size:0.75rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/delete.png' style='margin-right:4px;' align='absmiddle'></a>
    </div>" : "<div style='margin-top:5px;'>$page</div>";

    $data = "
  <table id='tbl' summary='list_table'>
  <tr>
  <td nowrap style='font-family:Arial;font-size:0.813rem;'><img src='" . XOOPS_URL . "/modules/tad_cal/images/date.png' alt='" . _MD_TADCAL_TIME . "' style='margin-right:4px;' align='absmiddle'> {$date}</td>
  </tr>

  {$show_location}

  <tr><td style='background-color:#FFFFEE;'><div style='margin:15px;font-size:0.75rem;line-height:1.5;color:#202020;'>{$details}</div></td></tr>
  <tr><td><div style='text-align:right;font-size:0.75rem;color:gray;'>{$cate[$cate_sn]['cate_title']} / Posted by {$uid_name}</div></td></tr>
  <tr><td style='background-color:transparent;border-bottom:none;'>$fun</td></tr>
  </table>
  ";
    header('HTTP/1.1 200 OK');
    $xoopsLogger->activated = false;

    die($data);
}

//算一下該日是該月的第幾週
function getWeekOfTheMonth($dateTimestamp = '')
{
    $weekNum = date('W', $dateTimestamp) - date('W', strtotime(date('Y-m-01', $dateTimestamp))) + 1;

    return $weekNum;
}

//日期顯示整理
function show_date($start = null, $end = null, $allday = '0', $mark = '<br>')
{
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    if (empty($endTime)) {
        $endTime = $startTime + 86400;
    }

    if ($allday) {
        $endTime -= 86400;
        $start = date('Y-m-d', $startTime);
        $end = date('Y-m-d', $endTime);
    } else {
        $start = date('Y-m-d H:i', $startTime);
        $end = date('Y-m-d H:i', $endTime);
    }

    if ($start == $end) {
        return $start;
    }

    return "$start $mark $end";
}

//搬移時，更新日期
function ajax_update_date($sn = '')
{
    global $xoopsDB;
    //新增或減少的秒數
    $delta = (int) $_POST['delta'];

    //抓出事件原有資料
    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=?';
    $result = Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    if (!$result) {
        sprintf(_MD_TADCAL_MOVE_ERROR, $xoopsDB->error());
    }
    $i = 0;
    $all = $xoopsDB->fetchArray($result);
    //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach ($all as $k => $v) {
        $$k = $v;
    }

    if (!empty($recurrence)) {
        die(_MD_TADCAL_REPEAT_CANT_MOVE);
    }
    /*
    $ical=new \XoopsModules\Tad_cal\Ical();
    $ical->parse($recurrence);
    $rrule_array=$ical->get_all_data();
    $rrule_arr=$rrule_array[''];

    //新的開始日期時間戳記
    $new_start=$rrule_arr['DTSTART']['unixtime'] + $dayDelta * 86400 + $minuteDelta * 60;
    //計算新的結束日期
    $new_end=$rrule_arr['DTEND']['unixtime'] + $dayDelta * 86400 + $minuteDelta * 60;

     */

    //新的開始日期時間戳記
    $new_start = strtotime($start) + $delta;
    //計算新的結束日期
    $new_end = strtotime($end) + $delta;

    if ($allday) {
        $start = date('Y-m-d', $new_start);
        $end = date('Y-m-d', $new_end);
        //$rrule_start=str_replace("-","",$start);
        //$rrule_end=str_replace("-","",$end);
        //$DTSTART="DTSTART;VALUE=DATE:{$rrule_start}\nDTEND;VALUE=DATE:{$rrule_end}\n";
    } else {
        $start = date('Y-m-d H:i', $new_start);
        $end = date('Y-m-d H:i', $new_end);
        //$rrule_start=substr(str_replace(":","",str_replace("-","",date("c",$start))),0,15);
        //$rrule_end=substr(str_replace(":","",str_replace("-","",date("c",$end))),0,15);
        //$TZ=date('e'); //Asia/Taipei
        //$DTSTART="DTSTART;TZID={$TZ}:{$rrule_start}\nDTEND;TZID={$TZ}:{$rrule_end}\n";
    }

    if (!empty($recurrence)) {
        $recurrence_arr = explode("\n", $recurrence);
        $recurrence = "{$DTSTART}{$recurrence_arr[2]}";
        $start = $end = '0000-00-00 00:00:00';
    }

    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_event') . '` SET `start` =?, `end` =?, `recurrence`=? WHERE `sn` =?';
    Utility::query($sql, 'sssi', [$start, $end, $recurrence, $sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    //重複事件
    //rrule($sn,$recurrence);
    return _MD_TADCAL_MOVE_OK;
}
