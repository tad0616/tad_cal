<?php
include_once "header.php";

/* 連資料庫檢查 */
$op = (!isset($_REQUEST['op']))? "":$_REQUEST['op'];
if($op=='title'){
  echo get_event_title($_REQUEST['start']);
}else{
  echo get_event_num();
}

//取得事件
function get_event_num(){
	global $xoopsDB,$xoopsUser;

  //取得目前使用者可讀的群組
	$ok_cate_arr=chk_cate_power('enable_group');
  $all_ok_cate=implode(",",$ok_cate_arr);
  $and_ok_cate=empty($all_ok_cate)?"and cate_sn='0'":"and cate_sn in($all_ok_cate)";
  $and_ok_cate2=empty($all_ok_cate)?"and a.sn='0'":"and b.cate_sn in($all_ok_cate)";

	$even_start=date("Y-m-d H:i",$_REQUEST['start']/1000);
	$even_end=($_REQUEST['end']=="0000-00-00 00:00")?"":date("Y-m-d H:i",$_REQUEST['end']/1000);

	//抓出事件
	$sql = "select * from ".$xoopsDB->prefix("tad_cal_event")." where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate order by `start` , `sequence`";
	//die($sql);

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	while($all=$xoopsDB->fetchArray($result)){
	//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }
    if(!empty($recurrence))continue;

    $startTime=strtotime($start);
    $key=date('Y-m-d',$startTime);

    $event_arr[$key]++;
    $Time_arr[$key]=userTimeToServerTime($startTime);
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

    $startTime=strtotime($start);
    $key=date('Y-m-d',$startTime);

    $event_arr[$key]++;
    $Time_arr[$key]=userTimeToServerTime($startTime);
	}

  $i=0;
  foreach($event_arr as $start=>$title ){
  	$myEvents[$i]['id']=$i;
  	$myEvents[$i]['title']="{$title}";
  	$myEvents[$i]['start']=$Time_arr[$start];
    $myEvents[$i]['allDay']=1;
    $myEvents[$i]['className']="blockevent";
    $i++;
  }

	return json_encode($myEvents);
}


//取得事件標題即連結
function get_event_title($start=""){
	global $xoopsDB,$xoopsUser,$xoopsConfig;

  //取得目前使用者可讀的群組
	$ok_cate_arr=chk_cate_power('enable_group');
  $all_ok_cate=implode(",",$ok_cate_arr);
  $and_ok_cate=empty($all_ok_cate)?"and cate_sn='0'":"and cate_sn in($all_ok_cate)";
  $and_ok_cate2=empty($all_ok_cate)?"and a.sn='0'":"and b.cate_sn in($all_ok_cate)";

  setTimezoneByOffset($xoopsConfig['default_TZ']);

	$even_start=date("Y-m-d 00:00:00",$start/1000);
	$even_end=date("Y-m-d 23:59:59",$start/1000);

	//抓出事件
	$sql = "select * from ".$xoopsDB->prefix("tad_cal_event")." where ((`start` >= '$even_start' and `start` <= '$even_end') or (`end` > '$even_start' and `end` <= '$even_end')) $and_ok_cate order by `start` , `sequence`";
	//die($sql);

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	while($all=$xoopsDB->fetchArray($result)){
	//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }
    if(!empty($recurrence))continue;

    $title_arr[$sn]=$title;
	}

	//抓出重複事件
	$sql = "select a.*,b.title,b.cate_sn from ".$xoopsDB->prefix("tad_cal_repeat")." as a join ".$xoopsDB->prefix("tad_cal_event")." as b on a.sn=b.sn where ((a.`start` >= '$even_start' and a.`start` <= '$even_end') or (a.`end` > '$even_start' and a.`end` <= '$even_end')) $and_ok_cate2 order by a.`start`";
//die($sql);
	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	while($all=$xoopsDB->fetchArray($result)){
	//以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }

    $title_arr[$sn]=$title;
	}

  $all="
  <ul>";
  foreach($title_arr as $sn=>$title ){
  	$all.="<li><a href='".XOOPS_URL."/modules/tad_cal/event.php?sn=$sn' style='text-decoration:none;color:#202020;'>{$title}</a></li>";
  }
  $all.="</ul>";

	return $all;
}


if(!function_exists('json_encode')){
function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
}
}

  

?>
