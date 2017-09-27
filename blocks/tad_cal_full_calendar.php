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

    $cate_sn = empty($options[0]) ? 0 : $options[0];

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
            select: function(start, end) {
              var promptBox = \"" . _MB_TADCAL_TITLE . _TAD_FOR . "<input type='text' id='eventTitle' name='eventTitle' value='' /><br>$cate\";

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
                  $.post('" . XOOPS_URL . "/modules/tad_cal/event.php', {op: 'insert_tad_cal_event', start: start.format(), end: end.format(), fc: '1', allday: start.hasTime() ? '0' : '1', title: f.eventTitle, cate_sn: f.cate_sn, new_cate_title: f.new_cate_title},function(){
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
            eventDrop: function(event,delta,revertFunc) {
              $.post('" . XOOPS_URL . "/modules/tad_cal/event.php', {op: 'ajax_update_date', delta: delta.asSeconds(), sn: event.id },function(data){
                alert(data);
              });
            },
            ";

        }

    }

    $block['eventDrop']     = $eventDrop;
    $block['eventAdd']      = $eventAdd;
    $block['cate_sn']       = $cate_sn;
    $block['eventShowMode'] = $xoopsModuleConfig['eventShowMode'];
    $block['eventTheme']    = $xoopsModuleConfig['eventTheme'];

    if ($options[1] == 'only_selected') {
        $style               = make_style($cate_sn);
        $block['style_mark'] = $style['mark'];
    } elseif ($options[1] == 'none') {
        $style               = make_style();
        $block['style_mark'] = '';
    } else {
        $style               = make_style();
        $block['style_mark'] = $style['mark'];
    }
    $block['style_css'] = $style['css'];

    $block['my_counter'] = my_counter();
    $block['firstDay']   = $xoopsModuleConfig['cal_start'];
    return $block;
}

//區塊編輯函式
function tad_cal_full_calendar_edit($options)
{

    $options0_1 = ($options[0] == "1") ? "checked" : "";
    $options0_0 = ($options[0] == "0") ? "checked" : "";
    $option     = block_cal_cate($options[0]);

    $seled1_0 = ($options[1] == "") ? "selected" : "";
    $seled1_1 = ($options[1] == "none") ? "selected" : "";
    $seled1_2 = ($options[1] == "only_selected") ? "selected" : "";

    $form = "
    {$option['js']}

    " . _MB_TADCAL_SHOW_CATE . "
      {$option['form']}
      <INPUT type='hidden' name='options[0]' id='bb' value='{$options[0]}'><br>
      " . _MB_TADCAL_SHOW_CATE_TYPE . "
      <select name='options[1]'>
        <option $seled1_0 value=''>" . _MB_TADCAL_SHOW_ALL . "</option>
        <option $seled1_1 value='none'>" . _MB_TADCAL_SHOW_NONE . "</option>
        <option $seled1_2 value='only_selected'>" . _MB_TADCAL_SHOW_ONLY_SELECTED . "</option>
      </select><br>
    ";

    return $form;
}

//取得所有類別標題
if (!function_exists("block_cal_cate")) {
    function block_cal_cate($selected = "")
    {
        global $xoopsDB;

        if (!empty($selected)) {
            $sc = explode(",", $selected);
        }

        $js = "<script>
            function bbv(){
              i=0;
              var arr = new Array();";

        $sql    = "select cate_sn,cate_title from " . $xoopsDB->prefix("tad_cal_cate") . " where cate_enable='1' order by cate_sort";
        $result = $xoopsDB->query($sql);
        $option = "";
        while (list($cate_sn, $cate_title) = $xoopsDB->fetchRow($result)) {

            $js .= "if(document.getElementById('c{$cate_sn}').checked){
               arr[i] = document.getElementById('c{$cate_sn}').value;
               i++;
              }";
            $ckecked = (in_array($cate_sn, $sc)) ? "checked" : "";
            $option .= "<span style='white-space:nowrap;'><input type='checkbox' id='c{$cate_sn}' value='{$cate_sn}' class='bbv' onChange=bbv() $ckecked><label for='c{$cate_sn}'>$cate_title</label></span> ";
        }

        $js .= "document.getElementById('bb').value=arr.join(',');
    }
    </script>";

        $main['js']   = $js;
        $main['form'] = $option;
        return $main;
    }
}
