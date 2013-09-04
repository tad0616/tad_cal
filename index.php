<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2011-11-03
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header.php";
$xoopsOption['template_main'] = "tad_cal_index_tpl.html";
include_once XOOPS_ROOT_PATH."/header.php";
/*-----------function區--------------*/

function fullcalendar($cate_sn=0){
  global $xoopsUser,$xoopsModuleConfig,$isAdmin,$xoopsTpl;

  if(empty($xoopsModuleConfig['eventShowMode']))$xoopsModuleConfig['eventShowMode']='eventClick';
  if(empty($xoopsModuleConfig['eventTheme']))$xoopsModuleConfig['eventTheme']='ui-tooltip-blue';

  $style=make_style();
  
  if(empty($cate_sn))$cate_sn=0;

  $eventDrop=$del_js=$eventAdd="";
  if($xoopsUser){
    //先抓分類下拉選單
    $get_tad_cal_cate_menu_options=get_tad_cal_cate_menu_options($cate_sn);
    if($isAdmin){
    	if(empty($get_tad_cal_cate_menu_options)){
        $cate=_MD_TADCAL_NEW_CATE._TAD_FOR."<input name='new_cate_title' id='new_cate_title' value='"._MD_TADCAL_NEW_CALENDAR."'>";
      }else{
        $cate=_MD_TADCAL_CATE_SN._TAD_FOR."<select name='cate_sn' id='cate_sn' size=1 >{$get_tad_cal_cate_menu_options}</select>";
      }
      
      //快速新增功能
      $eventAdd="selectable: true,
			selectHelper: true,
			select: function(start, end, allDay) {
        var promptBox = \""._MD_TADCAL_TITLE._TAD_FOR."<input type='text' id='eventTitle' name='eventTitle' value='' /><br>$cate\";

      	function mycallbackform(v,m,f){
        	if(v != undefined){
          	calendar.fullCalendar('renderEvent',
  						{
  							title: f.eventTitle,
  							start: start,
  							end: end,
  							allDay: allDay
  						},
  						true // make the event 'stick'
  					);

            $.post('event.php', {op: 'insert_tad_cal_event', fc_start: start.getTime(), fc_end: end.getTime(), title: f.eventTitle, cate_sn: f.cate_sn, new_cate_title: f.new_cate_title},function(){
              location.href='index.php';
            });
        	}
        }

        function mysubmitfunc(v,m,f){
        	an = m.children('#eventTitle');

        	if(f.eventTitle == ''){
        		an.css('border','solid #ff0000 1px');
        		return false;
        	}
        	return true;
        }

        $.prompt(promptBox,{
        	callback: mycallbackform,
          submit: mysubmitfunc,
          zIndex: 99999,
	        buttons: { Ok:true }
        });
        $('#eventTitle').focus();

        $('#eventTitle').keypress(function(event) {
          if (event.keyCode == '13') {
             $('#jqi_state0_buttonOk').click();
           }
        });
				calendar.fullCalendar('unselect');
			},
      ";

      //拖曳搬移功能
      $eventDrop="editable:true,
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
        var startTime=event.start.getTime();
        $.post('event.php', {op: 'ajax_update_date', dayDelta: dayDelta , minuteDelta: minuteDelta  , sn: event.id },function(data){
          alert(data);
        });
      },
      ";
    }
    
  }
	
 
  
  $xoopsTpl->assign('eventDrop' , $eventDrop);
  $xoopsTpl->assign('eventAdd' , $eventAdd);
  $xoopsTpl->assign('style_css' , $style['css']);
  $xoopsTpl->assign('cate_sn' , $cate_sn);
  $xoopsTpl->assign('eventShowMode' , $xoopsModuleConfig['eventShowMode']);
  $xoopsTpl->assign('eventTheme' , $xoopsModuleConfig['eventTheme']);
  $xoopsTpl->assign('style_mark' , $style['mark']);
  $xoopsTpl->assign('my_counter' , my_counter());
  
  
}

function make_style(){
  global $xoopsDB;

  //取得目前使用者可讀的群組
	$ok_cate_arr=chk_cate_power('enable_group');
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


//自動更新
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

/*-----------執行動作判斷區----------*/
$op=(empty($_REQUEST['op']))?"":$_REQUEST['op'];
$cate_sn=(empty($_REQUEST['cate_sn']))?"":intval($_REQUEST['cate_sn']);
$sn=(empty($_REQUEST['sn']))?"":intval($_REQUEST['sn']);


switch($op){

	default:
	fullcalendar($cate_sn);
	break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "isAdmin" , $isAdmin) ;

include_once XOOPS_ROOT_PATH.'/footer.php';
?>