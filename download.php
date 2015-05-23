<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2011-11-03
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header.php";
$xoopsOption['template_main'] = "tad_cal_download_tpl.html";
include_once XOOPS_ROOT_PATH."/header.php";
/*-----------function區--------------*/

function tad_cal_download(){
  global $xoopsUser,$xoopsModuleConfig,$isAdmin,$xoopsTpl;
  
  //先抓分類下拉選單
  $get_tad_cal_cate_menu_options=get_tad_cal_cate_menu_options();
  
  $xoopsTpl->assign('get_tad_cal_cate_menu_options' , $get_tad_cal_cate_menu_options);
 
}


/*-----------執行動作判斷區----------*/
$op=(empty($_REQUEST['op']))?"":$_REQUEST['op'];
$cate_sn=(empty($_REQUEST['cate_sn']))?"":intval($_REQUEST['cate_sn']);
$sn=(empty($_REQUEST['sn']))?"":intval($_REQUEST['sn']);


switch($op){

  
	default:
	tad_cal_download();
	break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "isAdmin" , $isAdmin) ;

include_once XOOPS_ROOT_PATH.'/footer.php';
?>