<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2008-03-25
// $Id: function.php,v 1.1 2008/04/04 07:10:34 tad Exp $
// ------------------------------------------------------------------------- //

//引入TadTools的函式庫
if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php")){
 redirect_header("http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50",3, _TAD_NEED_TADTOOLS);
}
include_once XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php";

//刪除tad_cbox某筆資料資料
function delete_tad_cbox($sn=""){
	global $xoopsDB,$xoopsUser,$xoopsModule;
	//判斷是否對該模組有管理權限，  若空白
  if ($xoopsUser) {
    $module_id = $xoopsModule->getVar('mid');
    $isAdmin=$xoopsUser->isAdmin($module_id);
  }else{
    $isAdmin=false;

	}
	if($isAdmin){
		$sql = "delete from ".$xoopsDB->prefix("tad_cbox")." where sn='$sn'";
		$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	}
}



/********************* 預設函數 *********************/
//圓角文字框
function div_3d($title="",$main="",$kind="raised",$style=""){
	$main="<table style='width:auto;{$style}'><tr><td>
	<div class='{$kind}'>
	<h1>$title</h1>
	<b class='b1'></b><b class='b2'></b><b class='b3'></b><b class='b4'></b>
	<div class='boxcontent'>
 	$main
	</div>
	<b class='b4b'></b><b class='b3b'></b><b class='b2b'></b><b class='b1b'></b>
	</div>
	</td></tr></table>";
	return $main;
}

if(!function_exists("is_utf8")){
	//判斷字串是否為utf8
	function  is_utf8($str)  {
	    $i=0;
	    $len  =  strlen($str);

	    for($i=0;$i<$len;$i++)  {
	        $sbit  =  ord(substr($str,$i,1));
	        if($sbit  <  128)  {
	            //本字節為英文字符，不與理會
	        }elseif($sbit  >  191  &&  $sbit  <  224)  {
	            //第一字節為落於192~223的utf8的中文字(表示該中文為由2個字節所組成utf8中文字)，找下一個中文字
	            $i++;
	        }elseif($sbit  >  223  &&  $sbit  <  240)  {
	            //第一字節為落於223~239的utf8的中文字(表示該中文為由3個字節所組成的utf8中文字)，找下一個中文字
	            $i+=2;
	        }elseif($sbit  >  239  &&  $sbit  <  248)  {
	            //第一字節為落於240~247的utf8的中文字(表示該中文為由4個字節所組成的utf8中文字)，找下一個中文字
	            $i+=3;
	        }else{
	            //第一字節為非的utf8的中文字
	            return  0;
	        }
	    }
	    //檢查完整個字串都沒問體，代表這個字串是utf8中文字
	    return  1;
	}
}

?>
