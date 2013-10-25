<?php
include_once "header.php";

/* 連資料庫檢查 */
echo get_event();

//取得事件
function get_event(){
  global $xoopsDB,$xoopsUser,$xoopsModuleConfig;

  $cate_sn = intval($_REQUEST['cate_sn']);

  //取得目前使用者可讀的群組
  $ok_cate_arr=chk_cate_power('enable_group');
  $all_ok_cate=implode(",",$ok_cate_arr);
  $and_ok_cate=empty($all_ok_cate)?"and cate_sn='0'":"and cate_sn in($all_ok_cate)";
  $and_ok_cate2=empty($all_ok_cate)?"and a.sn='0'":"and b.cate_sn in($all_ok_cate)";

  $even_start=date("Y-m-d H:i",$_REQUEST['start']/1000);
  $even_end=($_REQUEST['end']=="0000-00-00 00:00")?"":date("Y-m-d H:i",$_REQUEST['end']/1000);

  $and_cate_sn=empty($cate_sn)?"":"and `cate_sn` = '$cate_sn'";
  $and_cate_sn2=empty($cate_sn)?"":"and b.`cate_sn` = '$cate_sn'";

  //抓出事件
  $sql = "select * from ".$xoopsDB->prefix("tad_cal_event")." where `start` >= '$even_start' and `end` <= '$even_end' $and_ok_cate $and_cate_sn order by `start` , `sequence`";
  //die($sql);

  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  $i=0;
  while($all=$xoopsDB->fetchArray($result)){
  //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }
    if(!empty($recurrence))continue;

    $allDay=($allday=='1')?true:false;

    //正確顯示行事曆事件條
    $startTime=userTimeToServerTime(strtotime($start));
      $endTime=userTimeToServerTime(strtotime($end));
      if(empty($endTime))$endTime=$startTime+86400;

    //計算應顯示數字用
    $day=ceil(($endTime-$startTime)/86400);
    if(empty($day))$day=1;

    $start=date('Y-m-d H:i',$startTime);
    $end=date('Y-m-d H:i',$endTime);

    if($allDay){
      $endTime=strtotime($end)-86400;
    }

    //避免截掉半個中文字
    $title_num=$xoopsModuleConfig['title_num'] * 3 * $day;

    $event_title=xoops_substr(strip_tags($title), 0, $title_num);

    $myEvents[$i]['id']=$sn;
    $myEvents[$i]['title']="{$event_title}";
    //$myEvents[$i]['url']="event.php?sn=$sn";
    $myEvents[$i]['rel']="event.php?op=view&sn=$sn";
    $myEvents[$i]['start']=$startTime;
    if(!empty($end))$myEvents[$i]['end']=$endTime;
    $myEvents[$i]['allDay']=$allDay;
    $myEvents[$i]['className']="my{$cate_sn}";

    $i++;
  }

  //抓出重複事件
  $sql = "select a.*,b.title,b.cate_sn from ".$xoopsDB->prefix("tad_cal_repeat")." as a join ".$xoopsDB->prefix("tad_cal_event")." as b on a.sn=b.sn where a.`start` >= '$even_start' and a.`end` <= '$even_end' $and_ok_cate2 $and_cate_sn2 order by a.`start`";
//die($sql);
  $result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  while($all=$xoopsDB->fetchArray($result)){
  //以下會產生這些變數： $sn , $title , $start , $end , $recurrence , $location , $kind , $details , $etag , $id , $sequence , $uid , $cate_sn
    foreach($all as $k=>$v){
      $$k=$v;
    }

    $allDay=($allday=='1')?true:false;

    //正確顯示行事曆事件條
    $DBstartTime=strtotime($start);
    $startTime=userTimeToServerTime(strtotime($start));
      $endTime=userTimeToServerTime(strtotime($end));
      if(empty($endTime))$endTime=$startTime+86400;

    //計算應顯示數字用
    $day=ceil(($endTime-$startTime)/86400);
    if(empty($day))$day=1;

    $start=date('Y-m-d H:i',$startTime);
    $end=date('Y-m-d H:i',$endTime);

    if($allDay){
      $endTime=strtotime($end)-86400;
    }

    //避免截掉半個中文字
    $title_num=$xoopsModuleConfig['title_num'] * 3 * $day;
    //if(empty($title_num))$title_num=21;



    $event_title=xoops_substr(strip_tags($title), 0, $title_num);

    $myEvents[$i]['id']=$sn;
    $myEvents[$i]['title']="* {$event_title}";
    //$myEvents[$i]['url']="event.php?sn=$sn&stamp=$startTime";
    $myEvents[$i]['rel']="event.php?op=view&sn=$sn&stamp=$DBstartTime";
    $myEvents[$i]['start']=$startTime;
    if(!empty($end))$myEvents[$i]['end']=$endTime;
    $myEvents[$i]['allDay']=$allDay;
    $myEvents[$i]['className']="my{$cate_sn}";

    $i++;
  }


  return json_encode($myEvents);
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
