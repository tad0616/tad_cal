<?php
/*-----------引入檔案區--------------*/
include_once "header.php";
$xoopsOption['template_main'] = "tad_cal_download.tpl";
include_once XOOPS_ROOT_PATH . "/header.php";
/*-----------function區--------------*/

function tad_cal_download()
{
    global $xoopsUser, $xoopsModuleConfig, $isAdmin, $xoopsTpl;

    //先抓分類下拉選單
    $get_tad_cal_cate_menu_options = get_tad_cal_cate_menu_options();

    $xoopsTpl->assign('get_tad_cal_cate_menu_options', $get_tad_cal_cate_menu_options);
    $ym = date("Y-m");
    $d  = date("t");
    $xoopsTpl->assign('start', "{$ym}-01");
    $xoopsTpl->assign('end', "{$ym}-{$d}");
}

/*-----------執行動作判斷區----------*/
include_once $GLOBALS['xoops']->path('/modules/system/include/functions.php');
$op      = system_CleanVars($_REQUEST, 'op', '', 'string');
$cate_sn = system_CleanVars($_REQUEST, 'cate_sn', 0, 'int');
$sn      = system_CleanVars($_REQUEST, 'sn', 0, 'int');

switch ($op) {

    default:
        tad_cal_download();
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("toolbar", toolbar_bootstrap($interface_menu));
$xoopsTpl->assign("isAdmin", $isAdmin);

include_once XOOPS_ROOT_PATH . '/footer.php';
