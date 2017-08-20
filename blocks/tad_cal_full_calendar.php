<?php
//區塊主函式 (大行事曆(tad_cal_full_calendar))
function tad_cal_full_calendar($options)
{
    global $xoopsUser, $xoopsTpl;

    $modhandler        = xoops_gethandler('module');
    $xoopsModule       = &$modhandler->getByDirname("tad_cal");
    $config_handler    = xoops_gethandler('config');
    $module_id         = $xoopsModule->getVar('mid');
    $xoopsModuleConfig = &$config_handler->getConfigsByCat(0, $module_id);

    if ($xoopsUser) {
        $isAdmin = $xoopsUser->isAdmin($module_id);
    } else {
        $isAdmin = false;
    }

    //引入TadTools的函式庫
    if (!file_exists(XOOPS_ROOT_PATH . "/modules/tadtools/tad_function.php")) {
        redirect_header("http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50", 3, _TAD_NEED_TADTOOLS);
    }
    include_once XOOPS_ROOT_PATH . "/modules/tadtools/tad_function.php";
    include_once XOOPS_ROOT_PATH . "/modules/tad_cal/function_block.php";

    $jquery_path = get_jquery(true); //一般只要此行即可

    $block['jquery_path'] = $jquery_path;

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

    $eventDrop = $del_js = "";
    if ($xoopsUser) {
        //先抓分類下拉選單
        $get_tad_cal_cate_menu_options = get_tad_cal_cate_menu_options($cate_sn);
        if ($isAdmin) {
            if (empty($get_tad_cal_cate_menu_options)) {
                $cate = _MB_TADCAL_NEW_CATE . _TAD_FOR . "<input name='new_cate_title' id='new_cate_title' value='" . _MB_TADCAL_NEW_CALENDAR . "'>";
            } else {
                $cate = _MB_TADCAL_CATE_SN . _TAD_FOR . "<select name='cate_sn' id='cate_sn' size=1 >{$get_tad_cal_cate_menu_options}</select>";
            }

            //快速新增功能
            $eventAdd = "selectable: true,
      selectHelper: true,
      select: function(start, end, allDay) {
        var promptBox = \"" . _MB_TADCAL_TITLE . _TAD_FOR . "<input type='text' id='eventTitle' name='eventTitle' value='' /><br>$cate\";

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
            $.post('" . XOOPS_URL . "/modules/tad_cal/event.php', {op: 'insert_tad_cal_event', fc_start: start.valueOf(), fc_end: end.valueOf(), title: f.eventTitle, cate_sn: f.cate_sn, new_cate_title: f.new_cate_title},function(){
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

    $block['eventDrop']     = $eventDrop;
    $block['eventAdd']      = $eventAdd;
    $block['style_css']     = $style['css'];
    $block['cate_sn']       = $cate_sn;
    $block['eventShowMode'] = $xoopsModuleConfig['eventShowMode'];
    $block['eventTheme']    = $xoopsModuleConfig['eventTheme'];
    $block['style_mark']    = $style['mark'];
    $block['my_counter']    = my_counter();
    $block['firstDay']      = $xoopsModuleConfig['cal_start'];
    return $block;
}
