<?php
use Xmf\Request;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\MColorPicker;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
/*-----------引入檔案區--------------*/
$xoopsOption['template_main'] = 'tad_cal_admin.tpl';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');
$sn = Request::getInt('sn');

switch ($op) {
    //新增資料
    case 'insert_tad_cal_cate':
        $cate_sn = insert_tad_cal_cate();
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //更新資料
    case 'update_tad_cal_cate':
        update_tad_cal_cate($cate_sn);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //輸入表格
    case 'tad_cal_cate_form':
        tad_cal_cate_form($cate_sn);
        break;

    //刪除資料
    case 'delete_tad_cal_cate':
        delete_tad_cal_cate($cate_sn);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //預設動作
    default:
        list_tad_cal_cate();
        $op = 'list_tad_cal_cate';
        break;

}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
require_once __DIR__ . '/footer.php';

/*-----------function區--------------*/
//tad_cal_cate編輯表單
function tad_cal_cate_form($cate_sn = '')
{
    global $xoopsTpl;
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    //抓取預設值
    if (!empty($cate_sn)) {
        $DBV = get_tad_cal_cate($cate_sn);
    } else {
        $DBV = [];
    }

    //預設值設定

    //設定「cate_sn」欄位預設值
    $cate_sn = (!isset($DBV['cate_sn'])) ? '' : $DBV['cate_sn'];

    //設定「cate_title」欄位預設值
    $cate_title = (!isset($DBV['cate_title'])) ? _MA_TADCAL_NEW_CALENDAR : $DBV['cate_title'];

    //設定「cate_sort」欄位預設值
    $cate_sort = (!isset($DBV['cate_sort'])) ? tad_cal_cate_max_sort() : $DBV['cate_sort'];

    //設定「cate_enable」欄位預設值
    $cate_enable = (!isset($DBV['cate_enable'])) ? '' : $DBV['cate_enable'];

    //設定「cate_handle」欄位預設值
    $cate_handle = '';

    //設定「enable_group」欄位預設值
    $enable_group = (!isset($DBV['enable_group'])) ? '' : explode(',', $DBV['enable_group']);

    //設定「enable_upload_group」欄位預設值
    $enable_upload_group = (!isset($DBV['enable_upload_group'])) ? ['1'] : explode(',', $DBV['enable_upload_group']);

    //設定「google_id」欄位預設值
    $google_id = '';

    //設定「google_pass」欄位預設值
    $google_pass = '';

    //設定「cate_bgcolor」欄位預設值
    $cate_bgcolor = (!isset($DBV['cate_bgcolor'])) ? 'rgb(120,177,255)' : $DBV['cate_bgcolor'];

    //設定「cate_color」欄位預設值
    $cate_color = (!isset($DBV['cate_color'])) ? 'rgb(255,255,255)' : $DBV['cate_color'];

    $op = (empty($cate_sn)) ? 'insert_tad_cal_cate' : 'update_tad_cal_cate';

    //可見群組
    $SelectGroup_name = new \XoopsFormSelectGroup('', 'enable_group', false, $enable_group, 3, true);
    $SelectGroup_name->addOption('', _MA_TADCAL_ALL_OK, false);
    $SelectGroup_name->setExtra('class="span12 form-control"');
    $enable_group = $SelectGroup_name->render();

    //可上傳群組
    $SelectGroup_name = new \XoopsFormSelectGroup('', 'enable_upload_group', false, $enable_upload_group, 3, true);
    $SelectGroup_name->setExtra('class="span12 form-control"');
    $enable_upload_group = $SelectGroup_name->render();

    $FormValidator = new FormValidator('#myForm', true);
    $FormValidator->render();
    $MColorPicker = new MColorPicker('.color-picker');
    $MColorPicker->render('bootstrap');

    $xoopsTpl->assign('next_op', $op);
    $xoopsTpl->assign('cate_sn', $cate_sn);
    $xoopsTpl->assign('enable_upload_group', $enable_upload_group);
    $xoopsTpl->assign('enable_group', $enable_group);
    $xoopsTpl->assign('cate_enable1', Utility::chk($cate_enable, '1', '1'));
    $xoopsTpl->assign('cate_enable0', Utility::chk($cate_enable, '0'));
    $xoopsTpl->assign('cate_sort', $cate_sort);
    $xoopsTpl->assign('cate_color', $cate_color);
    $xoopsTpl->assign('cate_bgcolor', $cate_bgcolor);
    $xoopsTpl->assign('cate_title', $cate_title);
}

//新增資料到tad_cal_cate中
function insert_tad_cal_cate()
{
    global $xoopsDB;

    if (empty($_POST['enable_group']) or in_array('', $_POST['enable_group'])) {
        $enable_group = '';
    } else {
        $enable_group = implode(',', $_POST['enable_group']);
    }

    if (empty($_POST['enable_upload_group'])) {
        $enable_upload_group = '1';
    } else {
        $enable_upload_group = implode(',', $_POST['enable_upload_group']);
    }

    if (empty($_POST['cate_bgcolor'])) {
        $_POST['cate_bgcolor'] = '#78b1ff';
    }
    if (empty($_POST['cate_color'])) {
        $_POST['cate_color'] = '#ffffff';
    }

    $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_cal_cate') . '` (`cate_title`, `cate_sort`, `cate_enable`, `cate_handle`, `enable_group`, `enable_upload_group`, `google_id`, `google_pass`, `cate_bgcolor`, `cate_color`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    Utility::query($sql, 'sissssssss', [$_POST['cate_title'], $_POST['cate_sort'], $_POST['cate_enable'], '', $enable_group, $enable_upload_group, '', '', $_POST['cate_bgcolor'], $_POST['cate_color']]) or Utility::web_error($sql, __FILE__, __LINE__);

    //取得最後新增資料的流水編號
    $cate_sn = $xoopsDB->getInsertId();

    return $cate_sn;
}

//更新tad_cal_cate某一筆資料
function update_tad_cal_cate($cate_sn = '')
{
    global $xoopsDB;

    if (empty($_POST['enable_group']) or in_array('', $_POST['enable_group'])) {
        $enable_group = '';
    } else {
        $enable_group = implode(',', $_POST['enable_group']);
    }

    if (empty($_POST['enable_upload_group'])) {
        $enable_upload_group = '1';
    } else {
        $enable_upload_group = implode(',', $_POST['enable_upload_group']);
    }
    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_cate') . '` SET
    `cate_title` = ?,
    `cate_sort` = ?,
    `cate_enable` = ?,
    `enable_group` = ?,
    `enable_upload_group` = ?,
    `cate_bgcolor` = ?,
    `cate_color` = ?
    WHERE `cate_sn` = ?';

    Utility::query($sql, 'sisssssi', [
        $_POST['cate_title'],
        $_POST['cate_sort'],
        $_POST['cate_enable'],
        $enable_group,
        $enable_upload_group,
        $_POST['cate_bgcolor'],
        $_POST['cate_color'],
        $cate_sn,
    ]) or Utility::web_error($sql, __FILE__, __LINE__);

    return $cate_sn;
}

//列出所有tad_cal_cate資料
function list_tad_cal_cate()
{
    global $xoopsDB, $xoopsTpl;

    $SweetAlert = new SweetAlert();
    $SweetAlert->render("delete_tad_cal_cate_func", "main.php?op=delete_tad_cal_cate&cate_sn=", 'cate_sn');

    //取得資料數
    $sql = 'SELECT COUNT(*), `cate_sn`, MAX(`last_update`) FROM `' . $xoopsDB->prefix('tad_cal_event') . '` GROUP BY `cate_sn`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    while (list($count, $cate_sn, $last_update) = $xoopsDB->fetchRow($result)) {
        $counter[$cate_sn] = $count;
        $last[$cate_sn] = $last_update;
    }

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_cate') . '` ORDER BY `cate_sort`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    $all_cal = [];
    $i = 0;
    // $last        = "";
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        $g_txt = Utility::txt_to_group_name($enable_group, _MA_TADCAL_ALL_OK);
        $gu_txt = Utility::txt_to_group_name($enable_upload_group, _MA_TADCAL_ALL_OK);

        $enable = ('1' == $cate_enable) ? _YES : _NO;

        if (empty($counter[$cate_sn])) {
            $counter[$cate_sn] = 0;
        }

        $all_cal[$i]['cate_sn'] = $cate_sn;
        $all_cal[$i]['gu_txt'] = $gu_txt;
        $all_cal[$i]['g_txt'] = $g_txt;
        $all_cal[$i]['enable'] = $enable;
        $all_cal[$i]['counter'] = $counter[$cate_sn];
        $all_cal[$i]['cate_title'] = $cate_title;
        $all_cal[$i]['cate_color'] = $cate_color;
        $all_cal[$i]['cate_bgcolor'] = $cate_bgcolor;
        $i++;
    }

    $xoopsTpl->assign('all_cal', $all_cal);
}

//刪除tad_cal_cate某筆資料資料
function delete_tad_cal_cate($cate_sn = '')
{
    global $xoopsDB;
    $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_cal_cate') . '` WHERE `cate_sn`=?';
    Utility::query($sql, 'i', [$cate_sn]) or Utility::web_error($sql, __FILE__, __LINE__);

    $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `cate_sn`=?';
    Utility::query($sql, 'i', [$cate_sn]) or Utility::web_error($sql, __FILE__, __LINE__);

}
