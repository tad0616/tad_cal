<script type="text/javascript">
function showCalendarQtip(target, title, url) {
  var element = target && target.nodeType ? target : target && target[0];
  if (!element) return;
  $(element).qtip({
    content: {
      text: "<img class=\"throbber\" src=\"images/loading.gif\" alt=\"Loading...\">",
      ajax: {url: url},
      title: {text: title, button: true}
    },
    position: {my: "bottom center", at: "top center"},
    show: {event: false, ready: true, solo: true},
    hide: "unfocus",
    style: {classes: "ui-tooltip-shadow ui-tooltip-rounded"}
  });
}
</script><script type="text/javascript">
$(document).ready(function() {
  var block_calendar = new FullCalendar.Calendar(document.getElementById("block_calendar"), {
    initialView: "dayGridMonth",
    locale: window.navigator.userLanguage || window.navigator.language,
    firstDay: <{$block.firstDay}>,
    buttonText: {today: "<{$smarty.const._MB_TADCAL_TODAY}>"},
    height: "auto",
    headerToolbar: {left: "prev,today,next", center: "title", right: ""},
    dayHeaderContent: function(arg) {
      return [<{$smarty.const._MB_TADCAL_WEEK}>][arg.date.getDay()];
    },
    dayCellContent: function(arg) {
      return String(arg.date.getDate());
    },
    events: function(fetchInfo, successCallback, failureCallback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_block_event.php", {
        start: fetchInfo.startStr,
        end: fetchInfo.endStr
      }, successCallback).fail(failureCallback);
    },
    eventClick: function(clickInfo) {
      clickInfo.jsEvent.preventDefault();
      var event = clickInfo.event;
      showCalendarQtip(clickInfo.el, event.extendedProps.show_date, "<{$xoops_url}>/modules/tad_cal/get_block_event.php?op=title&start=" + event.start.toISOString());
      return false;
    }
  });
  block_calendar.render();
});
</script><div id="block_calendar"></div>
<div style="text-align:right;">
    <a href="<{$xoops_url}>/modules/tad_cal" class="btn btn-sm btn-xs btn-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>
