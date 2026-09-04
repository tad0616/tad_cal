<?php
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
use XoopsModules\Tad_cal\Tools;
if (!class_exists('XoopsModules\Tad_cal\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/tad_cal/preloads/autoloader.php';
}

//區塊主函式 (大行事曆(tad_cal_full_calendar))
function tad_cal_full_calendar($options)
{
    global $xoopsUser, $xoTheme;

    $TadCalModuleConfig = Utility::getXoopsModuleConfig('tad_cal');

    $jquery_path = Utility::get_jquery(true); //一般只要此行即可

    $block['jquery_path'] = $jquery_path;

    if (empty($TadCalModuleConfig['eventShowMode'])) {
        $TadCalModuleConfig['eventShowMode'] = 'eventClick';
    }

    if (empty($TadCalModuleConfig['eventTheme'])) {
        $TadCalModuleConfig['eventTheme'] = 'ui-tooltip-blue';
    }

    $style_mark = Tools::style_mark();

    if (empty($cate_sn)) {
        $cate_sn = 0;
    }

    $eventDrop = '';
    if ($xoopsUser) {
        if (isset($_SESSION['tad_cal_adm']) && $_SESSION['tad_cal_adm']) {
            $eventAdd = 'selectable: true,

            select: function(info) {
              var start = info.start;
              var end = info.end;
                var eventFormHtml = ' . json_encode(_MB_TADCAL_TITLE . ": <input type='text' class='form-control' id='eventTitle' name='eventTitle' value='' required><br>{$cate}<br><div class='form-check'><input type='checkbox' class='form-check-input' id='eventTag' name='eventTag' value='todo'><label class='form-check-label' for='eventTag'>" . _MB_TADCAL_TODO_LIST . "</label></div>", JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';

                function saveEvent(fields) {
                    calendar.addEvent({
                        title: fields.eventTitle,
                        start: start,
                        end: end,
                        allDay: info.allDay
                    });
                    $.post(' . json_encode(XOOPS_URL . '/modules/tad_cal/event.php') . ', {
                        op: "insert_tad_cal_event",
                        start: start.toISOString(),
                        end: end.toISOString(),
                        allday: info.allDay ? "1" : "0",
                        fc: "1",
                        title: fields.eventTitle,
                        cate_sn: fields.cate_sn,
                        tag: fields.eventTag,
                        new_cate_title: fields.new_cate_title
                    }, function() {
                        calendar.refetchEvents();
                    });
                }

                var modalElement = document.getElementById("tadCalBlockEventModal");
                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                var eventForm = modalElement.querySelector("form");
                eventForm.querySelector(".modal-body").innerHTML = eventFormHtml;
                eventForm.onsubmit = function(event) {
                    event.preventDefault();
                    var titleInput = eventForm.querySelector("#eventTitle");
                    if (!titleInput.value.trim()) {
                        titleInput.classList.add("is-invalid");
                        titleInput.focus();
                        return;
                    }
                    titleInput.classList.remove("is-invalid");
                    saveEvent({
                        eventTitle: titleInput.value,
                        eventTag: eventForm.querySelector("#eventTag").checked ? "todo" : "",
                        cate_sn: eventForm.querySelector("#cate_sn") ? eventForm.querySelector("#cate_sn").value : "",
                        new_cate_title: eventForm.querySelector("#new_cate_title") ? eventForm.querySelector("#new_cate_title").value : ""
                    });
                    modal.hide();
                };
                modal.show();
                setTimeout(function() {
                    eventForm.querySelector("#eventTitle").focus();
                }, 300);
                calendar.unselect();
            },
            ';

            //拖曳搬移功能
            $eventDrop = "
            //editable:true,
            editable:false,
            eventDrop: function(info) {
                $.post('" . XOOPS_URL . "/modules/tad_cal/event.php', {op: 'ajax_update_date', delta: info.delta.asSeconds(), sn: info.event.id },function(data){
                alert(data);
                });
            },
            ";
        }
    }

    $block['eventDrop']     = $eventDrop;
    $block['eventAdd']      = $eventAdd;
    $block['cate_sn']       = $cate_sn;
    $block['eventShowMode'] = $TadCalModuleConfig['eventShowMode'];
    $block['eventTheme']    = $TadCalModuleConfig['eventTheme'];
    $block['style_mark']    = $style_mark;
    $block['firstDay']      = $TadCalModuleConfig['cal_start'];

    Utility::add_migrate();

    $xoTheme->addStylesheet('modules/tad_cal/css/module.css?t=20260904');
    $xoTheme->addStylesheet('modules/tadtools/jquery.qtip_2/jquery.qtip.min.css');

    $xoTheme->addScript('modules/tadtools/moment/moment-with-locales.min.js');
    $xoTheme->addScript('modules/tadtools/fullcalendar6/index.global.js');
    $xoTheme->addScript('modules/tadtools/jquery.qtip_2/jquery.qtip.min.js');
    $SweetAlert = new SweetAlert();
    $SweetAlert->render("delete_tad_cal_event_func", XOOPS_URL . "/modules/tad_cal/event.php?op=delete_tad_cal_event&sn=", 'sn');
    return $block;
}
