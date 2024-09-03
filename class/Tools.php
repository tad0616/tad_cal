<?php
namespace XoopsModules\Tad_cal;

use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\Wcag;

/**
 * Class Tools
 */
class Tools
{

    // 變數過濾
    public static function filter($key, $value, $mode = "read", $filter_arr = [])
    {
        $myts = \MyTextSanitizer::getInstance();

        if (isset($filter_arr['pass']) && in_array($key, $filter_arr['pass'])) {
            return $value;
        }

        if ($mode == 'write' && in_array($key, $filter_arr['json'])) {
            $value = json_encode($value, 256);
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (isset($filter_arr['json']) and is_array($filter_arr['json']) and in_array($key, $filter_arr['json'])) {
                    $v = self::filter($k, $v, $mode, $filter_arr);
                } else {
                    $v = self::filter($key, $v, $mode, $filter_arr);
                }
                $value[$k] = $v;
            }
        } else {
            if (isset($filter_arr['int']) && in_array($key, $filter_arr['int'], true)) {
                $value = (int) $value;
            } elseif (isset($filter_arr['html']) && in_array($key, $filter_arr['html'], true)) {
                if ($mode == 'edit') {
                    $value = $myts->htmlSpecialChars($value);
                } else {
                    $value = ($mode == 'write') ? $xoopsDB->escape(Wcag::amend(trim($value))) : $myts->displayTarea($value, 1, 1, 1, 1, 0);
                }
            } elseif (isset($filter_arr['text']) && in_array($key, $filter_arr['text'], true)) {
                if ($mode == 'edit') {
                    $value = $myts->htmlSpecialChars($value);
                } else {
                    $value = ($mode == 'write') ? $xoopsDB->escape(trim($value)) : $myts->displayTarea($value, 0, 0, 0, 1, 1);
                }
            } elseif (isset($filter_arr['json']) && in_array($key, $filter_arr['json'], true)) {

                if ($mode == 'write') {
                    $value = $xoopsDB->escape(trim($value));
                } else {
                    $value = json_decode($value, true);
                    foreach ($value as $k => $v) {
                        $value[$k] = self::filter($k, $v, $mode);
                    }
                }

            } elseif (!isset($filter_arr['pass']) || !in_array($key, $filter_arr['pass'], true)) {
                if ($mode == 'edit') {
                    $value = $myts->htmlSpecialChars($value);
                } else {
                    $value = ($mode == 'write') ? $xoopsDB->escape(trim($value)) : $myts->htmlSpecialChars($value);
                }
            }
        }

        return $value;
    }

    // 產生 token
    public static function token_form($mode = 'assign')
    {
        global $xoopsTpl;
        include_once XOOPS_ROOT_PATH . "/class/xoopsformloader.php";
        $token = new \XoopsFormHiddenToken();
        $token_form = $token->render();
        if ($mode == 'assign') {
            $xoopsTpl->assign("token_form", $token_form);
        } else {
            return $token_form;
        }
    }

    // 取得資料庫條件
    public static function get_and_where($where_arr = '', $prefix = '')
    {
        global $xoopsDB;
        if (is_array($where_arr)) {
            $and_where_arr = '';
            foreach ($where_arr as $col => $value) {
                $and_where_arr .= !is_string($col) ? " and {$value}" : " and {$prefix}`{$col}` = '" . $xoopsDB->escape($value) . "'";
            }
        } else {
            $and_where_arr = $where_arr;
        }
        return $and_where_arr;
    }

    // 取得資料庫顯示欄位
    public static function get_view_col($view_cols = [], $prefix = '')
    {
        if (empty($view_cols)) {
            $view_col = $prefix . '*';
        } elseif (is_array($view_cols)) {
            $view_col = $prefix . '`' . implode("`, `{$prefix}", $view_cols) . '`';
        } else {
            $view_col = $view_cols;
        }
        return $view_col;
    }

    // 取得資料庫排序條件
    public static function get_order($order_arr = [], $prefix = '')
    {
        $before_sql = $order_sql = $after_sql = '';
        $before_items = $order_items = $after_items = [];
        if ($order_arr) {
            foreach ($order_arr as $col => $asc) {
                if ($col === 'before order') {
                    $before_items[] = $asc;
                } elseif ($col === 'after order') {
                    $after_items[] = $asc;
                } elseif (!is_string($col) or empty($col)) {
                    $order_items[] = $asc;
                } else {
                    $order_items[] = "{$prefix}`{$col}` $asc";
                }
            }

            $before_sql = empty($before_items) ? '' : implode(',', $before_items);
            $order_sql = empty($order_items) ? '' : "order by " . implode(',', $order_items);
            $after_sql = empty($after_items) ? '' : implode(',', $after_items);
        }
        return "$before_sql $order_sql $after_sql";
    }

    // 過濾所有資料
    public static function filter_all_data($filter, $data, $filter_arr)
    {
        if ($filter) {
            foreach ($data as $key => $value) {
                $data[$key] = Tools::filter($key, $value, $filter, $filter_arr);
            }
        }
        return $data;
    }

    //取得session
    public static function get_session($force = false)
    {
        global $xoopsUser;

        if ($xoopsUser) {

            //判斷是否對該模組有管理權限
            if (!isset($_SESSION['tad_cal_adm']) or $force) {
                $_SESSION['tad_cal_adm'] = ($xoopsUser) ? $xoopsUser->isAdmin() : false;
            }

            // if (!isset($_SESSION['tad_cal_manager']) or $force) {
            //     $_SESSION['tad_cal_manager'] = Utility::power_chk('tad_cal_post', 1);
            // }

            //目前登入者
            if (!isset($_SESSION['now_user']) or $force) {
                $_SESSION['now_user'] = ($xoopsUser) ? $xoopsUser->toArray() : [];
            }

            // if (!isset($_SESSION['all_enable_tad_cal']) or $force) {
            //     $all_tad_cal = Tad_cal_main::get_all(['enable' => '1'], [], [], [], 'ofsn');
            //     $_SESSION['all_enable_tad_cal'] = array_keys($all_tad_cal);
            //     foreach ($all_tad_cal as $ofsn => $tad_cal) {
            //         unset($tad_cal['content']);
            //         if ($xoopsUser) {
            //             if ($tad_cal['uid'] == $_SESSION['now_user']['uid']) {
            //                 $_SESSION['my_form'][$ofsn] = $ofsn;
            //             }
            //         }
            //         $_SESSION['tad_cal'][$ofsn] = $tad_cal;
            //     }
            // }
        }
    }

    // 權限檢查
    public static function chk_is_adm($other = '', $id = '', $file = '', $line = '', $mode = '')
    {
        $id = (int) $id;
        $file = str_replace('\\', '/', $file);
        if ($_SESSION['tad_cal_adm']) {
            return true;
        } elseif (($other != '' && $_SESSION[$other]) || strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
            if (!empty($id) && $_SESSION[$other]) {
                if (in_array($id, $_SESSION[$other]) || $id == $_SESSION[$other]) {
                    return true;
                } else {
                    if ($mode == 'return') {
                        return false;
                    } else {
                        redirect_header('index.php', 3, "您對筆資料 ($id) 無操作權限 {$file} ($line)");
                    }
                }
            }
        } else {
            if ($mode == 'return') {
                return false;
            } else {
                redirect_header('index.php', 3, "無操作權限 {$file} ($line)");
            }

        }
    }

    // 取得所有群組
    public static function get_group()
    {
        global $xoopsDB;
        $sql = "select `groupid`,`name` from " . $xoopsDB->prefix("groups") . "";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__, true);
        $groups = [];
        while (list($group_id, $name) = $xoopsDB->fetchRow($result)) {
            $groups[$group_id] = $name;
        }
        return $groups;
    }

    // 建立群組
    public static function mk_group($name = "")
    {
        global $xoopsDB;
        $sql = "select groupid from " . $xoopsDB->prefix("groups") . " where `name`='$name'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__, true);

        list($group_id) = $xoopsDB->fetchRow($result);

        if (empty($group_id)) {
            $sql = "insert into " . $xoopsDB->prefix("groups") . " (`name`) values('{$name}')";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__, true);

            //取得最後新增資料的流水編號
            $group_id = $xoopsDB->getInsertId();
        }
        return $group_id;
    }

    // 將某人加入群組
    public static function add_user_to_group($uid, $group_id)
    {
        global $xoopsDB;
        $sql = "replace into " . $xoopsDB->prefix("groups_users_link") . " (`groupid`, `uid`) values('$group_id','$uid')";
        $xoopsDB->queryF($sql) or die($sql);
    }

    // 將某人移出群組
    public static function del_user_from_group($uid, $group_id)
    {
        global $xoopsDB;
        $sql = "delete from " . $xoopsDB->prefix("groups_users_link") . " where `groupid`='$group_id' and `uid`='$uid'";
        $xoopsDB->queryF($sql) or die($sql);
    }

    // uid 轉姓名
    public static function get_name_by_uid($uid)
    {
        $uid_name = \XoopsUser::getUnameFromId($uid, 1);
        if (empty($uid_name)) {
            $uid_name = \XoopsUser::getUnameFromId($uid, 0);
        }

        return $uid_name;
    }

    // 儲存排序
    public static function sort_store($item_arr, $table, $sort_col = 'sort', $primary = 'ofsn')
    {
        global $xoopsDB;
        $sort = 1;
        foreach ($item_arr as $primary_keys) {
            list($ofsn) = explode('-', $primary_keys);
            $sql = "update `" . $xoopsDB->prefix($table) . "` set `{$sort_col}`='{$sort}' where `{$primary}`='{$ofsn}'";
            $xoopsDB->queryF($sql) or die('排序失敗！ (' . date("Y-m-d H:i:s") . ')');
            $sort++;
        }
        echo "排序完成！ (" . date("Y-m-d H:i:s") . ")";
    }

}
