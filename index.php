<?php
use Xmf\Request;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tad_cal\Tools;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$xoopsOption['template_main'] = 'tad_cal_index.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
$op      = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');
$sn      = Request::getInt('sn');

switch ($op) {

//刪除資料
    case 'delete_tad_cal_event':
        delete_tad_cal_event($sn);
        header('location: ' . XOOPS_URL . '/modules/tad_cal/index.php');
        exit;

    default:
        fullcalendar($cate_sn);
        $op = 'fullcalendar';
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoTheme->addStylesheet('modules/tad_cal/css/module.css?t=20260904');
$xoTheme->addStylesheet('modules/tadtools/jquery.qtip_2/jquery.qtip.min.css');

$xoTheme->addScript('modules/tadtools/moment/moment-with-locales.min.js');
$xoTheme->addScript('modules/tadtools/fullcalendar6/index.global.js');
$xoTheme->addScript('modules/tadtools/jquery.qtip_2/jquery.qtip.min.js');
$xoTheme->addScript('modules/tadtools/My97DatePicker/WdatePicker.js');

$SweetAlert = new SweetAlert();
$SweetAlert->render("delete_tad_cal_event_func", "event.php?op=delete_tad_cal_event&sn=", 'sn');
require_once XOOPS_ROOT_PATH . '/footer.php';

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

    $style_mark = Tools::style_mark();

    if (empty($cate_sn)) {
        $cate_sn = 0;
    }

    $eventDrop = $del_js = $eventAdd = '';
    if ($xoopsUser) {
//先抓分類下拉選單
        $get_tad_cal_cate_menu_options = Tools::get_tad_cal_cate_menu_options($cate_sn);
        if ($_SESSION['tad_cal_adm']) {
            if (empty($get_tad_cal_cate_menu_options)) {
                $cate = _MD_TADCAL_NEW_CATE . _TAD_FOR . "<input name='new_cate_title' title='new_cate_title' id='new_cate_title' value='" . _MD_TADCAL_NEW_CALENDAR . "'>";
            } else {
                $cate = _MD_TADCAL_CATE_SN . _TAD_FOR . "<select name='cate_sn' title='cate_sn' id='cate_sn' size=1 >{$get_tad_cal_cate_menu_options}</select>";
            }

//快速新增功能
            $eventAdd = 'selectable: true,

select: function(info) {
var start = info.start;
var end = info.end;
var eventFormHtml = ' . json_encode(_MD_TADCAL_TITLE . _TAD_FOR . "<input type='text' class='form-control' id='eventTitle' name='eventTitle' value='' required><br>$cate<br><div class='form-check'><input type='checkbox' class='form-check-input' id='eventTag' name='eventTag' value='todo'><label class='form-check-label' for='eventTag'>" . _MD_TADCAL_TODO_LIST . "</label></div>", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';

function mycallbackform(e,v,m,f){
if(v != undefined){

calendar.addEvent(
{
title: f.eventTitle,
start: start,
end: end,
allDay: info.allDay
},
false // make the event \'stick\'
);


$.post(\'event.php\', {op: \'insert_tad_cal_event\', start: start.toISOString(), end: end.toISOString(), allday: info.allDay ? \'1\' : \'0\', fc: \'1\', title: f.eventTitle, cate_sn: f.cate_sn, tag: f.eventTag, new_cate_title: f.new_cate_title},function(data){
console.log(start.toISOString()+\'-\'+end.toISOString());
calendar.refetchEvents();
});
}
}

var modalElement = document.getElementById(\'tadCalEventModal\');
var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
var eventForm = modalElement.querySelector(\'form\');
eventForm.querySelector(\'.modal-body\').innerHTML = eventFormHtml;
eventForm.onsubmit = function(event) {
event.preventDefault();
var titleInput = eventForm.querySelector(\'#eventTitle\');
if (!titleInput.value.trim()) {
titleInput.classList.add(\'is-invalid\');
titleInput.focus();
return;
}
titleInput.classList.remove(\'is-invalid\');
var fields = {
eventTitle: titleInput.value,
eventTag: eventForm.querySelector(\'#eventTag\').checked ? \'todo\' : \'\',
cate_sn: eventForm.querySelector(\'#cate_sn\') ? eventForm.querySelector(\'#cate_sn\').value : \'\',
new_cate_title: eventForm.querySelector(\'#new_cate_title\') ? eventForm.querySelector(\'#new_cate_title\').value : \'\'
};
mycallbackform(event, true, modalElement, fields);
modal.hide();
};
modal.show();
setTimeout(function() {
eventForm.querySelector(\'#eventTitle\').focus();
}, 300);
calendar.unselect();
},
';

//拖曳搬移功能
            $eventDrop = "
//editable:true,
editable:false,
eventDrop: function(info) {
$.post('event.php', {op: 'ajax_update_date', delta: info.delta.asSeconds(), sn: info.event.id },function(data){
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
