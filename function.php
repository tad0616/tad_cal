<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2011-11-03
// $Id:$
// ------------------------------------------------------------------------- //
//引入TadTools的函式庫
if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php")){
 redirect_header("http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50",3, _TAD_NEED_TADTOOLS);
}
include_once XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php";


/********************* 自訂函數 *********************/
//自動新增分類
function create_cate($title='',$sort='',$handle='',$enable_group='',$enable_upload_group='1',$google_id='',$google_pass=''){
  global $xoopsDB;

  $myts =& MyTextSanitizer::getInstance();
  $title=$myts->addSlashes($title);
  if(empty($sort))$sort=tad_cal_cate_max_sort();

  $sql = "insert into ".$xoopsDB->prefix("tad_cal_cate")."
  (`cate_title` , `cate_sort` , `cate_enable` , `cate_handle` , `enable_group` , `enable_upload_group` , `google_id` , `google_pass`, `cate_color`)
  values('{$title}' , '{$sort}' , '1' , '{$handle}' , '{$enable_group}' , '{$enable_upload_group}' , '{$google_id}' , '{$google_pass}','rgb(0,0,0)')";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  //取得最後新增資料的流水編號
  $cate_sn=$xoopsDB->getInsertId();

  //自動給顏色碼
  $color=num2color($cate_sn);
  $sql="update ".$xoopsDB->prefix("tad_cal_cate")." set `cate_bgcolor`='{$color}' where `cate_sn`='$cate_sn'";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  return $cate_sn;
}


//自動取得tad_cal_cate的最新排序
function tad_cal_cate_max_sort(){
  global $xoopsDB;
  $sql = "select max(`cate_sort`) from ".$xoopsDB->prefix("tad_cal_cate");
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  list($sort)=$xoopsDB->fetchRow($result);
  return ++$sort;
}



//以流水號取得某筆tad_cal_cate資料
function get_tad_cal_cate($cate_sn=""){
  global $xoopsDB;
  if(empty($cate_sn))return;
  $sql = "select * from ".$xoopsDB->prefix("tad_cal_cate")." where cate_sn='$cate_sn'";
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  $data=$xoopsDB->fetchArray($result);
  return $data;
}


//自動取得顏色
function num2color($cate_sn=''){
  $R=$G=$B=255;
  $m=ceil($cate_sn/3);
  $n=$cate_sn % 3;
  $degree=intval($cate_sn)*10*$m;

  $cor=array("R","G","B");
  ${$cor[$n]}-=$degree;

  return "rgb({$R},{$G},{$B})";
}


//存入重複日期
//$start='20111104T190000',$rule='RRULE:FREQ=WEEKLY;UNTIL=20111230;INTERVAL=2;BYDAY=FR'
function rrule($sn='',$recurrence='',$allDay=null){
  global $xoopsDB,$xoopsUser;
  include_once XOOPS_ROOT_PATH."/modules/tad_cal/class/rrule.php";
  include_once XOOPS_ROOT_PATH."/modules/tad_cal/class/ical.php";

  if(empty($sn) or empty($recurrence))return;

  $ical=new ical();
  $ical->parse($recurrence);
  $rrule_array=$ical->get_all_data();

  foreach($rrule_array['']['RRULE'] as $key=>$val){
    $all[]="{$key}={$val}";
  }
  $rrule= 'RRULE:'.implode(";",$all);
  $start=substr(str_replace(":","",str_replace("-","",date("c",$rrule_array['']['DTSTART']['unixtime']))),0,15);
  $endTime=$rrule_array['']['DTEND']['unixtime']-$rrule_array['']['DTSTART']['unixtime'];

//die($start."====".$rrule);
  $rule = new RRule($start , $rrule);
  $i=0;
  while($date = $rule->GetNext()){
    if($i>300)break;
    $new_date=$date->Render();
    if(empty($new_date))continue;
    $end=date("Y-m-d H:i",strtotime($new_date)+$endTime);
    $allday=is_null($allDay)?isAllDay($new_date,$end):$allDay;
    $allDate[]="('{$sn}','{$new_date}','{$end}','{$allday}')";
    $i++;
  }
  $sql_data=implode(",",$allDate);
  if(empty($sql_data))return;

  $sql = "delete from ".$xoopsDB->prefix("tad_cal_repeat")." where `sn`='{$sn}'";

  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  $sql = "insert into ".$xoopsDB->prefix("tad_cal_repeat")."
  (`sn` , `start` , `end` , `allday`)
  values{$sql_data}";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
}



//判斷是否全天事件
function isAllDay($start="",$end=""){
  if(strlen($start)==10)return 1;

  $st=strtotime($start)%86400;
  $en=strtotime($end)%86400;
  $allday=(empty($st) and empty($en))?1:0;
  return $allday;
}

//取得tad_cal_cate行事曆選單的選項（單層選單）
function get_tad_cal_cate_menu_options($default_cate_sn="0"){
  global $xoopsDB,$xoopsModule;

  //取得目前使用者可編輯的行事曆
  $edit_cate_arr=chk_cate_power('enable_upload_group');
  $all_ok_cate=implode(",",$edit_cate_arr);
  $and_ok_cate=empty($all_ok_cate)?"cate_sn='0'":"cate_sn in($all_ok_cate)";


  $sql = "select cate_sn,cate_title from ".$xoopsDB->prefix("tad_cal_cate")." where $and_ok_cate order by `cate_sort`";
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  $total=$xoopsDB->getRowsNum($result);
  if(empty($total))return;

  $option="";
  while(list($cate_sn,$cate_title)=$xoopsDB->fetchRow($result)){
    $selected=($cate_sn==$default_cate_sn)?"selected=selected":"";
    $option.="<option value=$cate_sn $selected>{$cate_title}</option>";

  }
  return $option;
}

function setTimezoneByOffset($offset){
  $testTimestamp = time();
    date_default_timezone_set('UTC');
    $testLocaltime = localtime($testTimestamp,true);
    $testHour = $testLocaltime['tm_hour'];


$abbrarray = timezone_abbreviations_list();
foreach ($abbrarray as $abbr)
{
    //echo $abbr."<br>";
  foreach ($abbr as $city)
  {
            date_default_timezone_set($city['timezone_id']);
            $testLocaltime     = localtime($testTimestamp,true);
            $hour                     = $testLocaltime['tm_hour'];
            $testOffset =  $hour - $testHour;
            if($testOffset == $offset)
            {
                return true;
            }
  }
}
return false;
}



//判斷某人在哪些類別中有觀看或發表(enable_upload_group)的權利
function chk_cate_power($kind="enable_group"){
  global $xoopsDB,$xoopsUser,$xoopsModule,$isAdmin;
  if(!empty($xoopsUser)){
    if($isAdmin){
      //$ok_cat[]="0";
    }
    $user_array=$xoopsUser->getGroups();
  }else{
    $user_array=array(3);
    $isAdmin=0;
  }

  $sql = "select `cate_sn`,`{$kind}`,`cate_enable` from ".$xoopsDB->prefix("tad_cal_cate")."";
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  while(list($cate_sn,$power,$cate_enable)=$xoopsDB->fetchRow($result)){
    if(empty($cate_sn))continue;
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


//取得tad_cal_cate所有資料陣列
function get_tad_cal_cate_all(){
  global $xoopsDB;
  $sql = "select * from ".$xoopsDB->prefix("tad_cal_cate");
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  while($data=$xoopsDB->fetchArray($result)){
    $cate_sn=$data['cate_sn'];
    $data_arr[$cate_sn]=$data;
  }
  return $data_arr;
}


//全部同步
function tad_cal_all_sync(){
  global $xoopsDB,$xoopsModule;

  $sql = "select cate_sn from ".$xoopsDB->prefix("tad_cal_cate")." where `cate_handle`!=''";
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  while(list($cate_sn)=$xoopsDB->fetchRow($result)){
    import_google($cate_sn);
  }
}

//取得行事曆名稱陣列
function get_cal_array(){
  global $xoopsDB;
  $sql = "select cate_sn,cate_title from ".$xoopsDB->prefix("tad_cal_cate")."";
  $result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  while(list($cate_sn , $cate_title)=$xoopsDB->fetchRow($result)){
    $arr[$cate_sn]=$cate_title;
  }
  return $arr;
}


//匯入事件
function import_google($cate_sn=""){
  global $xoopsDB,$xoopsUser;
  if( !ini_get('safe_mode') ){
    set_time_limit(0);
  }

  //取得使用者編號
  $uid=($xoopsUser)?$xoopsUser->getVar('uid'):"";

  $sql = "select * from ".$xoopsDB->prefix("tad_cal_cate")." where cate_sn='$cate_sn'";
  $result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  $all=$xoopsDB->fetchArray($result);

  //以下會產生這些變數： $cate_sn , $cate_title , $cate_sort , $cate_enable , $cate_handle , $enable_group , $enable_upload_group , $google_id , $google_pass
  foreach($all as $k=>$v){
    $$k=$v;
  }

  include_once XOOPS_ROOT_PATH."/modules/tad_cal/class/gcalendar.class.php";
  $gmail=new GCalendar($google_id,$google_pass);

  $gmail->authenticate();

  $Calendars=$gmail->getOwnCalendars();

  $Events=$gmail->getEvents($cate_handle);


  if(empty($Events['data']['items'])){
    //redirect_header(XOOPS_URL,3, _MD_TADCAL_GOOGLE_EMPTY_EVENT);
  }
  //echo "<h3>{$cal['title']} ({$cal['handle']}) 共 {$Events['data']['totalResults']} 筆資料</h3>";

  $myts =& MyTextSanitizer::getInstance();
  $now=date('Y-m-d H:i:s',time());

  foreach($Events['data']['items'] as $k1=>$v1){

    $alternateLink=isset($v1['alternateLink'])?$myts->addSlashes($v1['alternateLink']):"";
    $title=isset($v1['title'])?$myts->addSlashes($v1['title']):"";
    $location=isset($v1['location'])?$myts->addSlashes($v1['location']):"";
    $details=isset($v1['details'])?$myts->addSlashes($v1['details']):"";
    $etag=isset($v1['etag'])?$myts->addSlashes($v1['etag']):"";
    $id=isset($v1['id'])?$myts->addSlashes($v1['id']):"";
    $recurrence=isset($v1['recurrence'])?$myts->addSlashes($v1['recurrence']):"";
    $sequence=isset($v1['sequence'])?$myts->addSlashes($v1['sequence']):"";
    $kind=isset($v1['kind'])?$myts->addSlashes($v1['kind']):"";

    //$start=date('Y-m-d H:i:s',xoops_getUserTimestamp(strtotime($v1['when'][0]['start'])));
    //$end=date('Y-m-d H:i:s',xoops_getUserTimestamp(strtotime($v1['when'][0]['end'])));

    //start=2011-10-01 end=2011-10-02
    //start=2011-11-03T08:00:00.000+08:00     end=2011-11-03T17:00:00.000+08:00
    $allday=isAllDay($v1['when'][0]['start'],$v1['when'][0]['end']);


    $sql = "insert into ".$xoopsDB->prefix("tad_cal_event")."
    (`title` , `start` , `end` , `recurrence` , `location` , `kind` , `details` , `etag` , `id` , `sequence` , `uid` , `cate_sn` , `allday` , `tag` , `last_update`)
    values('{$title}' , '{$v1['when'][0]['start']}' , '{$v1['when'][0]['end']}' , '{$recurrence}' , '{$location}' , '{$kind}' , '{$details}' , '{$etag}' , '{$id}' , '{$sequence}' , '{$uid}' , '{$cate_sn}' , '{$allday}' , '{$tag}' , '{$now}') ON DUPLICATE KEY UPDATE `title`='{$title}' , `start`='{$v1['when'][0]['start']}' , `end`='{$v1['when'][0]['end']}' , `recurrence`='{$recurrence}' , `location`='{$location}' , `kind`='{$kind}' , `details`='{$details}' , `etag`='{$etag}' , `id`='{$id}' , `sequence`='{$sequence}' , `uid`= '{$uid}' , `cate_sn`='{$cate_sn}' , `allday`='{$allday}' , `tag`='{$tag}' , `last_update`='{$now}'";
    $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

    //取得最後新增資料的流水編號
    $sn=$xoopsDB->getInsertId();

    //重複事件
    rrule($sn,$recurrence,$allday);
  }

}

?>