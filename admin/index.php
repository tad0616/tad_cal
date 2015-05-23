<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2008-03-25
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "../../../include/cp_header.php";
include_once "../function.php";

/*-----------function區--------------*/

//列出所有tad_cbox資料
function list_tad_cbox(){
	global $xoopsDB,$xoopsModule,$xoopsUser,$xoopsModuleConfig;
 $MDIR=$xoopsModule->getVar('dirname');
	$sql = "select * from ".$xoopsDB->prefix("tad_cbox")." order by post_date desc";

	//getPageBar($原sql語法, 每頁顯示幾筆資料, 最多顯示幾個頁數選項);
   $PageBar=getPageBar($sql,20,10);
   $bar=$PageBar['bar'];
   $sql=$PageBar['sql'];

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());


	//判斷是否對該模組有管理權限，  若空白
  if ($xoopsUser) {
    $module_id = $xoopsModule->getVar('mid');
    $isAdmin=$xoopsUser->isAdmin($module_id);
  }else{
    $isAdmin=false;

	}

  $cbox_root_msg_color=(empty($_SESSION['cbox_root_msg_color']))?"#E5ECC7":$_SESSION['cbox_root_msg_color'];

  if($isAdmin){
		$del_js="
		<script>
		function delete_tad_cbox_func(sn){
			var sure = window.confirm('"._BP_DEL_CHK."');
			if (!sure)	return;
			location.href=\"{$_SERVER['PHP_SELF']}?mode={$_GET['mode']}&op=delete_tad_cbox&sn=\" + sn;
		}
		</script>";
	}else{
    $del_js="";
	}

	$data="
	$del_js
  <style>
	.triangle-border {
  	border:5px solid $cbox_root_msg_color;
  }

  .triangle-border:before {
    border-color:$cbox_root_msg_color transparent;
  }
	</style>
	<div class='cbox'>
	<table id='cbox_show_tbl'>
	<tr><td class=bar>$bar</td></tr>";
	$i=2;

	while(list($sn,$publisher,$msg,$post_date,$ip,$only_root,$root_msg)=$xoopsDB->fetchRow($result)){

	  $bgcss=($i%2)?"color:{$xoopsModuleConfig['col1_color']};background-color:{$xoopsModuleConfig['col1_bgcolor']}":"color:{$xoopsModuleConfig['col2_color']};background-color:{$xoopsModuleConfig['col2_bgcolor']}";

	  $post_date=date("Y-m-d H:i:s",xoops_getUserTimestamp(strtotime($post_date)));

	  if($only_root=='1' and !$isAdmin){
			$msg="<font class='lock_msg'>"._MA_TADCBOX_LOCK_MSG."</font>";
		}

    $tool=($isAdmin)?"<img src='".XOOPS_URL."/modules/tad_cbox/images/del2.gif' width=12 height=12 align=bottom hspace=2 onClick=\"delete_tad_cbox_func($sn)\">":"";

		$msg=str_replace("[s","<img src='".XOOPS_URL."/modules/tad_cbox/images/smiles/s",$msg);
		$msg=str_replace(".gif]",".gif' hspace=2 align='absmiddle'>",$msg);

		if(!empty($root_msg)){
      $root_msg=str_replace("[s","<img src='".XOOPS_URL."/modules/tad_cbox/images/smiles/s",$root_msg);
			$root_msg=str_replace(".gif]",".gif' hspace=2 align='absmiddle'>",$root_msg);

		  $root="
        <div class='triangle-border' style='line-height:150%;'>
        $root_msg
        </div>
      <div style='clear:both;'></div>";
		}else{
			$root="";
		}



		$data.="<tr>
		<td style='{$bgcss}'>
		<div class='cbox_date'><font class='cbox_ip'>{$ip}</font> | {$post_date} {$tool}</div> {$root}
		<div class='cbox_publisher'>{$publisher}</div>: {$msg}</td>
		</tr>";
		$i++;
	}
	$data.="
	<tr><td class=bar>$bar</td></tr>
	</table></div>";
	return $data;
}




/*-----------執行動作判斷區----------*/
$op = (!isset($_REQUEST['op']))? "main":$_REQUEST['op'];

switch($op){
	//刪除資料
	case "delete_tad_cbox";
	delete_tad_cbox($_GET['sn']);
	header("location: {$_SERVER['PHP_SELF']}?mode={$_GET['mode']}");
	break;

	default:
	$main=list_tad_cbox();
	break;
}

/*-----------秀出結果區--------------*/
xoops_cp_header();
echo "<link rel='stylesheet' type='text/css' media='screen' href='../module.css' />";
admin_toolbar(0);
echo $main;
xoops_cp_footer();

?>
