<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$xoopsOption['template_main'] = 'tad_cal_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';
/*-----------function區--------------*/

function fullcalendar($cate_sn = 0)
{
    global $xoopsUser, $xoopsModuleConfig, $xoopsTpl;

    Utility::get_jquery();

    if (empty($xoopsModuleConfig['eventShowMode'])) {
        $xoopsModuleConfig['eventShowMode'] = 'eventClick';
    }

    if (empty($xoopsModuleConfig['eventTheme'])) {
        $xoopsModuleConfig['eventTheme'] = 'ui-tooltip-blue';
    }

    $style_mark = style_mark();

    if (empty($cate_sn)) {
        $cate_sn = 0;
    }

    $eventDrop = $del_js = $eventAdd = '';
    if ($xoopsUser) {
        //先抓分類下拉選單
        $get_tad_cal_cate_menu_options = get_tad_cal_cate_menu_options($cate_sn);
        if ($_SESSION['tad_cal_adm']) {
            if (empty($get_tad_cal_cate_menu_options)) {
                $cate = _MD_TADCAL_NEW_CATE . _TAD_FOR . "<input name='new_cate_title' title='new_cate_title' id='new_cate_title' value='" . _MD_TADCAL_NEW_CALENDAR . "'>";
            } else {
                $cate = _MD_TADCAL_CATE_SN . _TAD_FOR . "<select name='cate_sn' title='cate_sn' id='cate_sn' size=1 >{$get_tad_cal_cate_menu_options}</select>";
            }

            //快速新增功能
            $eventAdd = 'selectable: true,
            selectHelper: true,
            select: function(start, end) {
              var promptBox = "' . _MD_TADCAL_TITLE . _TAD_FOR . "<input type='text' id='eventTitle' name='eventTitle' value=''><br>$cate<br><input type='checkbox' id='eventTag' name='eventTag' value='todo'>" . _MD_TADCAL_TODO_LIST . "\";

              function mycallbackform(e,v,m,f){
                if(v != undefined){

                  calendar.fullCalendar('renderEvent',
                    {
                      title: f.eventTitle,
                      start: start,
                      end: end,
                      allDay: !start.hasTime()
                    },
                    false // make the event 'stick'
                  );


                  $.post('event.php', {op: 'insert_tad_cal_event', start: start.format(), end: end.format(), allday: start.hasTime() ? '0' : '1', fc: '1', title: f.eventTitle, cate_sn: f.cate_sn, tag: f.eventTag, new_cate_title: f.new_cate_title},function(data){
                    console.log(start.format()+'-'+end.format());
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
            $eventDrop = "
            //editable:true,
            editable:false,
            eventDrop: function(event,delta,revertFunc) {
              $.post('event.php', {op: 'ajax_update_date', delta: delta.asSeconds(), sn: event.id },function(data){
                alert(data);
              });
            },
            ";
        }
    }

    $xoopsTpl->assign('eventDrop', $eventDrop);
    $xoopsTpl->assign('eventAdd', $eventAdd);
    $xoopsTpl->assign('style_css', isset($style['css']) ? $style['css'] : '');
    $xoopsTpl->assign('cate_sn', $cate_sn);
    $xoopsTpl->assign('eventShowMode', $xoopsModuleConfig['eventShowMode']);
    $xoopsTpl->assign('eventTheme', $xoopsModuleConfig['eventTheme']);
    $xoopsTpl->assign('style_mark', $style_mark);

    $xoopsTpl->assign('firstDay', $xoopsModuleConfig['cal_start']);
    $xoopsTpl->assign('cate', get_tad_cal_cate($cate_sn));
    Utility::add_migrate();
}

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');
$sn = Request::getInt('sn');

switch ($op) {
    default:
        fullcalendar($cate_sn);
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu));
$xoTheme->addStylesheet('modules/tad_cal/css/module.css');
$xoTheme->addStylesheet('modules/tadtools/fullcalendar/redmond/theme.css');
$xoTheme->addStylesheet('modules/tadtools/fullcalendar/fullcalendar.css');
$xoTheme->addStylesheet('modules/tadtools/jquery.qtip_2/jquery.qtip.min.css');

$xoTheme->addScript('modules/tadtools/moment/moment-with-locales.min.js');
$xoTheme->addScript('modules/tad_cal/class/jquery-impromptu.6.2.3.min.js');
$xoTheme->addScript('modules/tadtools/fullcalendar/fullcalendar.js');
$xoTheme->addScript('modules/tadtools/fullcalendar/gcal.js');
$xoTheme->addScript('modules/tadtools/jquery.qtip_2/jquery.qtip.min.js');
$xoTheme->addScript('modules/tadtools/My97DatePicker/WdatePicker.js');

require_once XOOPS_ROOT_PATH . '/footer.php';
