<?php
/*-----------引入檔案區--------------*/
include_once "header.php";
$xoopsOption['template_main'] = "tad_cal_index.tpl";
include_once XOOPS_ROOT_PATH . "/header.php";
/*-----------function區--------------*/

function fullcalendar($cate_sn = 0)
{
    global $xoopsUser, $xoopsModuleConfig, $isAdmin, $xoopsTpl;

    if (empty($xoopsModuleConfig['eventShowMode'])) {
        $xoopsModuleConfig['eventShowMode'] = 'eventClick';
    }

    if (empty($xoopsModuleConfig['eventTheme'])) {
        $xoopsModuleConfig['eventTheme'] = 'ui-tooltip-blue';
    }

    $style = make_style();

    if (empty($cate_sn)) {
        $cate_sn = 0;
    }

    $eventDrop = $del_js = $eventAdd = "";
    if ($xoopsUser) {
        //先抓分類下拉選單
        $get_tad_cal_cate_menu_options = get_tad_cal_cate_menu_options($cate_sn);
        if ($isAdmin) {
            if (empty($get_tad_cal_cate_menu_options)) {
                $cate = _MD_TADCAL_NEW_CATE . _TAD_FOR . "<input name='new_cate_title' id='new_cate_title' value='" . _MD_TADCAL_NEW_CALENDAR . "'>";
            } else {
                $cate = _MD_TADCAL_CATE_SN . _TAD_FOR . "<select name='cate_sn' id='cate_sn' size=1 >{$get_tad_cal_cate_menu_options}</select>";
            }

            //快速新增功能
            $eventAdd = "selectable: true,
      selectHelper: true,
      select: function(start, end, allDay) {
        var promptBox = \"" . _MD_TADCAL_TITLE . _TAD_FOR . "<input type='text' id='eventTitle' name='eventTitle' value='' /><br>$cate\";

        function mycallbackform(e,v,m,f){
          if(v != undefined){

            calendar.fullCalendar('renderEvent',
              {
                title: f.eventTitle,
                start: start,
                end: end,
                allDay: allDay
              },
              false // make the event 'stick'
            );


            $.post('event.php', {op: 'insert_tad_cal_event', fc_start: start.valueOf(), fc_end: end.valueOf(), title: f.eventTitle, cate_sn: f.cate_sn, new_cate_title: f.new_cate_title},function(){
              calendar.fullCalendar('refetchEvents');
            });
          }
        }

        function mysubmitfunc(e,v,m,f){
          an = m.children('#eventTitle');

          if(f.eventTitle == ''){
            an.css('border','solid #ff0000 1px');
            return false;
          }
          mycallbackform(e,v,m,f);
          return true;
        }

        $.prompt(promptBox,{
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
            $eventDrop = "editable:true,
      eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
        var startTime=event.start.valueOf();
        $.post('event.php', {op: 'ajax_update_date', dayDelta: dayDelta , minuteDelta: minuteDelta  , sn: event.id },function(data){
          alert(data);
        });
      },
      ";
        }

    }
    $xoopsTpl->assign('timezone', date('e'));
    $xoopsTpl->assign('eventDrop', $eventDrop);
    $xoopsTpl->assign('eventAdd', $eventAdd);
    $xoopsTpl->assign('style_css', $style['css']);
    $xoopsTpl->assign('cate_sn', $cate_sn);
    $xoopsTpl->assign('eventShowMode', $xoopsModuleConfig['eventShowMode']);
    $xoopsTpl->assign('eventTheme', $xoopsModuleConfig['eventTheme']);
    $xoopsTpl->assign('style_mark', $style['mark']);
    $xoopsTpl->assign('my_counter', my_counter());

    $xoopsTpl->assign('firstDay', $xoopsModuleConfig['cal_start']);
    $xoopsTpl->assign('cate', get_tad_cal_cate($cate_sn));

}

/*-----------執行動作判斷區----------*/
include_once $GLOBALS['xoops']->path('/modules/system/include/functions.php');
$op      = system_CleanVars($_REQUEST, 'op', '', 'string');
$cate_sn = system_CleanVars($_REQUEST, 'cate_sn', 0, 'int');
$sn      = system_CleanVars($_REQUEST, 'sn', 0, 'int');

switch ($op) {

    default:
        fullcalendar($cate_sn);
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("toolbar", toolbar_bootstrap($interface_menu));
$xoopsTpl->assign("isAdmin", $isAdmin);

include_once XOOPS_ROOT_PATH . '/footer.php';
