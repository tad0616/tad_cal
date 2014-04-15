<?php

if(!function_exists("make_style")){
  function make_style(){
    global $xoopsDB;

    //取得目前使用者可讀的群組
    $ok_cate_arr=chk_tad_cal_cate_power('enable_group');
    $all_ok_cate=implode(",",$ok_cate_arr);
    $and_ok_cate=empty($all_ok_cate)?"cate_sn='0'":"cate_sn in($all_ok_cate)";


    //抓出現有google行事曆
    $sql = "select `cate_sn`,`cate_title`,`cate_bgcolor`,`cate_color` from ".$xoopsDB->prefix("tad_cal_cate")." where $and_ok_cate order by `cate_sort`";
    $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
    $main['css']=$main['mark']="";
    while(list($cate_sn,$cate_title,$cate_bgcolor,$cate_color)=$xoopsDB->fetchRow($result)){
      //$color=num2color($cate_sn);
      $main['css'].="
        /* {$cate_sn} */
        .my{$cate_sn},
        .fc-agenda .my{$cate_sn} .fc-event-time,
        .my{$cate_sn} a {
            background-color: {$cate_bgcolor}; /* background color */
            color: {$cate_color};           /* text color */
        }

        .fc-event{$cate_sn},
        .fc-agenda .fc-event{$cate_sn} .fc-event-time,
        .fc-event{$cate_sn} a {
          background-color: {$cate_bgcolor}; /* default BACKGROUND color */
          color: {$cate_color};           /* default TEXT color */
        }
      ";

      $main['mark'].="
      <span class='cate_mark' style='border:1px solid {$cate_bgcolor}; border-left:16px solid {$cate_bgcolor};'><a href='index.php?cate_sn={$cate_sn}'>$cate_title</a></span>";
    }


    return $main;
  }
}


//判斷某人在哪些類別中有觀看或發表(enable_upload_group)的權利
if(!function_exists("chk_tad_cal_cate_power")){
  function chk_tad_cal_cate_power($kind="enable_group"){
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
}




//取得tad_cal_cate行事曆選單的選項（單層選單）
if(!function_exists("get_tad_cal_cate_menu_options")){
  function get_tad_cal_cate_menu_options($default_cate_sn="0"){
    global $xoopsDB,$xoopsModule;

    //取得目前使用者可編輯的行事曆
    $edit_cate_arr=chk_tad_cal_cate_power('enable_upload_group');
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
}



//自動更新
if(!function_exists("my_counter")){
  function my_counter(){
    global $xoopsModuleConfig,$isAdmin;
    if($xoopsModuleConfig['sync_conut']=='0'){
      return;
    }elseif(is_null(($xoopsModuleConfig['sync_conut'])) or $xoopsModuleConfig['sync_conut']==''){
      $sync_conut=100;
    }else{
      $sync_conut=intval($xoopsModuleConfig['sync_conut']);
    }

    $data=XOOPS_ROOT_PATH."/uploads/tad_cal_count.txt";
    if(file_exists($data)){
      $fp=fopen($data,"r");
      $old_count=fread($fp,filesize($data));
      $new_count=$old_count+1;
      fclose($fp);
    }else{
      $new_count=1;
    }

    $times=$new_count % $sync_conut;
    if($times==0) tad_cal_all_sync();
    $show_times=$sync_conut-$times;

    $fp=fopen($data,"w");
    fwrite($fp,$new_count);
    fclose($fp);

    $main=($isAdmin)?"<div class='sync_text'>".sprintf(_MD_TADCAL_SYNC_COUNT,$show_times)."</div>":"";

    return $main;
  }
}



//全部同步
if(!function_exists("tad_cal_all_sync")){
  function tad_cal_all_sync(){
    global $xoopsDB,$xoopsModule;

    $sql = "select cate_sn from ".$xoopsDB->prefix("tad_cal_cate")." where `cate_handle`!=''";
    $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

    while(list($cate_sn)=$xoopsDB->fetchRow($result)){
      import_google($cate_sn);
    }
  }
}



//匯入事件
if(!function_exists("import_google")){
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
}


//判斷是否全天事件
if(!function_exists("isAllDay")){
  function isAllDay($start="",$end=""){
    if(strlen($start)==10)return 1;

    $st=strtotime($start)%86400;
    $en=strtotime($end)%86400;
    $allday=(empty($st) and empty($en))?1:0;
    return $allday;
  }
}



//存入重複日期
//$start='20111104T190000',$rule='RRULE:FREQ=WEEKLY;UNTIL=20111230;INTERVAL=2;BYDAY=FR'
if(!function_exists("rrule")){
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
}


?>