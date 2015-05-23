<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2011-11-03
// $Id:$
// ------------------------------------------------------------------------- //

//區塊主函式 (待辦事項(tad_cal_list))
function tad_cal_list($options){
	global $xoopsDB,$xoopsUser;

  //取得目前使用者可讀的群組
	$ok_cate_arr=chk_cate_power_block('enable_group');
  $all_ok_cate=implode(",",$ok_cate_arr);
  $and_ok_cate=empty($all_ok_cate)?"and cate_sn='0'":"and cate_sn in($all_ok_cate)";
  $and_ok_cate2=empty($all_ok_cate)?"and a.sn='0'":"and b.cate_sn in($all_ok_cate)";

	$even_start=date("Y-m-d 00:00:00");
	$even_end=date("Y-m-d 23:59:59",strtotime("+{$options[0]} days"));

	//抓出事件
	$sql = "select * from ".$xoopsDB->prefix("tad_cal_event")." where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate order by `start` , `sequence`";
	//die($sql);

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	$i=0;
	while($all=$xoopsDB->fetchArray($result)){
	//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }
    if(!empty($recurrence))continue;
    $start=substr($start,0,10);
    $all_event[$start][$sn]=$title;
	}

	//抓出重複事件
	$sql = "select a.*,b.title,b.cate_sn from ".$xoopsDB->prefix("tad_cal_repeat")." as a join ".$xoopsDB->prefix("tad_cal_event")." as b on a.sn=b.sn where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2 order by a.`start`";
//die($sql);
	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	while($all=$xoopsDB->fetchArray($result)){
	//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }

    $start=substr($start,0,10);
    $all_event[$start][$sn]=$title;
	}
	
	$block="<ul style='margin:0px;padding:0px;'>";
	foreach($all_event as $start=>$arr){
    $block.="<dt>{$start}</dt>";
  	foreach($arr as $sn=>$title){
      $block.="<li><a href='".XOOPS_URL."/modules/tad_cal/event.php?sn={$sn}'>$title</a></li>";
    }
  }
  $block.="</ul>";

	return $block;
}

//區塊編輯函式
function tad_cal_list_edit($options){

	$form="
	"._MB_TADCAL_TAD_CAL_LIST_EDIT_BITEM0."
	<INPUT type='text' name='options[0]' value='{$options[0]}'>
	";
	return $form;
}



if(!function_exists('chk_cate_power_block')){
  //判斷某人在哪些類別中有觀看或發表(enable_upload_group)的權利
  function chk_cate_power_block($kind="enable_group"){
  	global $xoopsDB,$xoopsUser,$isAdmin;

  	if(!empty($xoopsUser)){
  		if($isAdmin){
  	    $ok_cat[]="0";
  		}
  		$user_array=$xoopsUser->getGroups();
  	}else{
      $user_array=array(3);
      $isAdmin=0;
  	}

  	$sql = "select `cate_sn`,`{$kind}`,`cate_enable` from ".$xoopsDB->prefix("tad_cal_cate")."";
  	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  	while(list($cate_sn,$power,$cate_enable)=$xoopsDB->fetchRow($result)){
      if(empty($cate_enable))continue;
  	  if($isAdmin or empty($power)){
        $ok_cat[]=$cate_sn;
  		}else{
  			$power_array=explode(",",$power);
  			foreach($power_array as $gid){
  				if(in_array($gid,$user_array)){
  					$ok_cat[]=$cate_sn;
  					break;
  				}
  			}
  		}
  	}

  	return $ok_cat;
  }

}
?>