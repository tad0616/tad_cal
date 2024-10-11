<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tad_cal\Tools;
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$xoopsOption['template_main'] = 'tad_cal_download.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';
/*-----------function區--------------*/

function tad_cal_download()
{
    global $xoopsTpl;

    //先抓分類下拉選單
    $get_tad_cal_cate_menu_options = Tools::get_tad_cal_cate_menu_options();

    $xoopsTpl->assign('get_tad_cal_cate_menu_options', $get_tad_cal_cate_menu_options);
    $ym = date('Y-m');
    $d = date('t');
    $xoopsTpl->assign('start', "{$ym}-01");
    $xoopsTpl->assign('end', "{$ym}-{$d}");
}

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');
$sn = Request::getInt('sn');

switch ($op) {
    default:
        tad_cal_download();
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('now_op', $op);
$xoTheme->addScript('modules/tadtools/My97DatePicker/WdatePicker.js');

require_once XOOPS_ROOT_PATH . '/footer.php';
