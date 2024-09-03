<?php
use XoopsModules\Tadtools\Utility;

if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}

//區塊主函式 (大行事曆(tad_cal_full_calendar))
function tad_cal_full_calendar($options)
{
    global $xoopsUser, $xoopsTpl, $xoTheme;

    $moduleHandler = xoops_getHandler('module');
    $xoopsModule = $moduleHandler->getByDirname('tad_cal');
    $configHandler = xoops_getHandler('config');
    $module_id = $xoopsModule->getVar('mid');
    $xoopsModuleConfig = $configHandler->getConfigsByCat(0, $module_id);

    require_once XOOPS_ROOT_PATH . '/modules/tad_cal/function_block.php';

    $jquery_path = Utility::get_jquery(true); //一般只要此行即可

    $block['jquery_path'] = $jquery_path;

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

    $eventDrop = $del_js = '';
    if ($xoopsUser) {
        //先抓分類下拉選單
        $get_tad_cal_cate_menu_options = get_tad_cal_cate_menu_options($cate_sn);
        if ($_SESSION['tad_cal_adm']) {
            if (empty($get_tad_cal_cate_menu_options)) {
                $cate = _MB_TADCAL_NEW_CATE . ": <input name='new_cate_title' title='new_cate_title' id='new_cate_title' value='" . _MB_TADCAL_NEW_CALENDAR . "'>";
            } else {
                $cate = _MB_TADCAL_CATE_SN . ": <select name='cate_sn' title='cate_sn' id='cate_sn' size=1 >{$get_tad_cal_cate_menu_options}</select>";
            }

            //快速新增功能
            $eventAdd = 'selectable: true,
            selectHelper: true,
            select: function(start, end) {
                var promptBox = "' . _MB_TADCAL_TITLE . ": <input type='text' id='eventTitle' name='eventTitle' value=''><br>{$cate}<br><input type='checkbox' id='eventTag' name='eventTag' value='todo'>" . _MB_TADCAL_TODO_LIST . "\";

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

                        $.post('" . XOOPS_URL . "/modules/tad_cal/event.php', {op: 'insert_tad_cal_event', start: start.format(), end: end.format(), allday: start.hasTime() ? '0' : '1', fc: '1', title: f.eventTitle, cate_sn: f.cate_sn, tag: f.eventTag, new_cate_title: f.new_cate_title},function(data){
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
                $.post('" . XOOPS_URL . "/modules/tad_cal/event.php', {op: 'ajax_update_date', delta: delta.asSeconds(), sn: event.id },function(data){
                alert(data);
                });
            },
            ";
        }
    }

    $block['eventDrop'] = $eventDrop;
    $block['eventAdd'] = $eventAdd;
    $block['cate_sn'] = $cate_sn;
    $block['eventShowMode'] = $xoopsModuleConfig['eventShowMode'];
    $block['eventTheme'] = $xoopsModuleConfig['eventTheme'];
    $block['style_mark'] = $style_mark;
    $block['my_counter'] = my_counter();
    $block['firstDay'] = $xoopsModuleConfig['cal_start'];

    Utility::add_migrate();

    $xoTheme->addStylesheet('modules/tad_cal/css/module.css');
    $xoTheme->addStylesheet('modules/tadtools/fullcalendar/redmond/theme.css');
    $xoTheme->addStylesheet('modules/tadtools/fullcalendar/fullcalendar.css');
    $xoTheme->addStylesheet('modules/tadtools/jquery.qtip_2/jquery.qtip.min.css');

    $xoTheme->addScript('modules/tadtools/moment/moment-with-locales.min.js');
    $xoTheme->addScript('modules/tad_cal/class/jquery-impromptu.6.2.3.min.js');
    $xoTheme->addScript('modules/tadtools/fullcalendar/fullcalendar.js');
    $xoTheme->addScript('modules/tadtools/fullcalendar/gcal.js');
    $xoTheme->addScript('modules/tadtools/jquery.qtip_2/jquery.qtip.min.js');

    return $block;
}
