<?php
namespace XoopsModules\Tad_cal;

use XoopsModules\Tadtools\Utility;

/**
 * Class Tools
 */
class Tools
{
    //取得session
    public static function get_session($force = false)
    {
        global $xoopsUser;
        if ($xoopsUser) {

            //判斷是否對該模組有管理權限
            if (!isset($_SESSION['tad_cal_adm']) or $force) {
                $_SESSION['tad_cal_adm'] = isset($xoopsUser) && \is_object($xoopsUser) ? $xoopsUser->isAdmin() : false;
            }

            //目前登入者
            if (!isset($_SESSION['now_user']) or $force) {
                $_SESSION['now_user'] = ($xoopsUser) ? $xoopsUser->toArray() : [];
            }
        }
    }

    //存入重複日期
    //$start='20111104T190000',$rule='RRULE:FREQ=WEEKLY;UNTIL=20111230;INTERVAL=2;BYDAY=FR'
    public static function rrule($sn = '', $recurrence = '', $allDay = null)
    {
        global $xoopsDB;
        require_once XOOPS_ROOT_PATH . '/modules/tad_cal/class/RRule.php';
        require_once XOOPS_ROOT_PATH . '/modules/tad_cal/class/Ical.php';

        if (empty($sn) or empty($recurrence)) {
            return;
        }
        // die($recurrence);
        $ical = new Ical();
        $ical->parse($recurrence);
        $rrule_array = $ical->get_all_data();
        // die('[rrule_array]' . var_export($rrule_array));

        foreach ($rrule_array['']['RRULE'] as $key => $val) {
            $all[] = "{$key}={$val}";
        }
        $rrule = 'RRULE:' . implode(';', $all);
        // die($rrule_array['']['DTSTART']['unixtime']);
        $start = mb_substr(str_replace(':', '', str_replace('-', '', date('c', $rrule_array['']['DTSTART']['unixtime']))), 0, 15);
        $endTime = $rrule_array['']['DTEND']['unixtime'] - $rrule_array['']['DTSTART']['unixtime'];

        // die($start . "====" . $rrule);
        // echo "<p>start= $start</p>";
        // echo "<p>rrule= $rrule</p>";
        $rule = new \XoopsModules\Tad_cal\RRule($start, $rrule);
        $i = 0;
        while ($date = $rule->GetNext()) {
            if ($i > 300) {
                break;
            }

            $new_date = $date->Render();
            // echo "<p>new_date= $new_date</p>";
            if (empty($new_date)) {
                continue;
            }

            $end = date('Y-m-d H:i', strtotime($new_date) + $endTime);
            $allday = null === $allDay ? self::isAllDay($new_date, $end) : $allDay;
            $allDate[] = "('{$sn}', '{$new_date}', '{$end}', '{$allday}')";
            $i++;
        }
        // exit;
        $sql_data = implode(',', $allDate);
        if (empty($sql_data)) {
            return;
        }

        $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` WHERE `sn`=?';
        Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);

        $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_cal_repeat') . '` (`sn`, `start`, `end`, `allday`) VALUES ' . $sql_data;
        Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    }

    //判斷是否全天事件
    public static function isAllDay($start = '', $end = '')
    {
        if (10 == mb_strlen($start)) {
            return 1;
        }

        $st = strtotime($start) % 86400;
        $en = strtotime($end) % 86400;
        $allday = (empty($st) and empty($en)) ? 1 : 0;

        return $allday;
    }

    //取得tad_cal_cate行事曆選單的選項（單層選單）
    public static function get_tad_cal_cate_menu_options($default_cate_sn = '0')
    {
        global $xoopsDB;

        //取得目前使用者可編輯的行事曆
        $edit_cate_arr = self::chk_tad_cal_cate_power('enable_upload_group');
        $all_ok_cate = implode(',', $edit_cate_arr);
        $and_ok_cate = empty($all_ok_cate) ? "`cate_sn`=0" : "`cate_sn` IN ($all_ok_cate)";

        $sql = 'SELECT `cate_sn`, `cate_title` FROM `' . $xoopsDB->prefix('tad_cal_cate') . '` WHERE ' . $and_ok_cate . ' ORDER BY `cate_sort`';
        $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        $total = $xoopsDB->getRowsNum($result);
        if (empty($total)) {
            return;
        }

        $option = '';
        while (list($cate_sn, $cate_title) = $xoopsDB->fetchRow($result)) {
            $selected = ($cate_sn == $default_cate_sn) ? 'selected=selected' : '';
            $option .= "<option value=$cate_sn $selected>{$cate_title}</option>";
        }

        return $option;
    }

    //判斷某人在哪些類別中有觀看或發表(enable_upload_group)的權利
    public static function chk_tad_cal_cate_power($kind = 'enable_group')
    {
        global $xoopsDB, $xoopsUser;
        $ok_cat = [];
        if (!empty($xoopsUser)) {
            if (isset($_SESSION['tad_cal_adm']) && $_SESSION['tad_cal_adm']) {
                //$ok_cat[]="0";
            }
            $user_array = $xoopsUser->getGroups();
        } else {
            $user_array = [3];
        }

        $sql = 'SELECT `cate_sn`, `' . $kind . '`, `cate_enable` FROM `' . $xoopsDB->prefix('tad_cal_cate') . '`';
        $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        while (list($cate_sn, $power, $cate_enable) = $xoopsDB->fetchRow($result)) {
            if (empty($cate_sn)) {
                continue;
            }

            if (empty($cate_enable)) {
                continue;
            }

            if (isset($_SESSION['tad_cal_adm']) && $_SESSION['tad_cal_adm'] or empty($power)) {
                $ok_cat[] = $cate_sn;
            } else {
                $power_array = explode(',', $power);
                foreach ($power_array as $gid) {
                    if (in_array($gid, $user_array)) {
                        $ok_cat[] = $cate_sn;
                        break;
                    }
                }
            }
        }

        return $ok_cat;
    }

    public static function style_mark()
    {
        global $xoopsDB;

        //取得目前使用者可讀的群組
        $ok_cate_arr = self::chk_tad_cal_cate_power('enable_group');
        $all_ok_cate = implode(',', $ok_cate_arr);
        $and_ok_cate = empty($all_ok_cate) ? "cate_sn='0'" : "cate_sn in($all_ok_cate)";

        //抓出現有google行事曆
        $sql = 'SELECT `cate_sn`,`cate_title`,`cate_bgcolor`,`cate_color` FROM `' . $xoopsDB->prefix('tad_cal_cate') . '` WHERE ' . $and_ok_cate . ' ORDER BY `cate_sort`';
        $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        $mark = '';
        while (list($cate_sn, $cate_title, $cate_bgcolor, $cate_color) = $xoopsDB->fetchRow($result)) {
            $mark .= "
                <a href='" . XOOPS_URL . "/modules/tad_cal/index.php?cate_sn={$cate_sn}'  class='cate_mark' style='background: {$cate_bgcolor}; color: {$cate_color};'>$cate_title</a>";
        }

        return $mark;
    }

}
