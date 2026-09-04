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
  var calendar = new FullCalendar.Calendar(document.getElementById("full_calendar_block"), {
    initialView: "dayGridMonth",
    firstDay: <{$block.firstDay}>,
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText: {today: "<{$smarty.const._MB_TADCAL_TODAY}>"},
    headerToolbar: {left: "prev,today,next", center: "title", right: ""},
    <{$block.eventAdd}>
    <{$block.eventDrop}>
    events: function(fetchInfo, successCallback, failureCallback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_event.php", {
        start: fetchInfo.startStr,
        end: fetchInfo.endStr
        <{if $block.cate_sn|default:false}>, cate_sn: <{$block.cate_sn}><{/if}>
      }, successCallback).fail(failureCallback);
    },
    <{$block.eventShowMode}>: function(clickInfo) {
      var event = clickInfo.event;
      var eventUrl = event.extendedProps.rel;
      if (eventUrl) {
        clickInfo.jsEvent.preventDefault();
        showCalendarQtip(clickInfo.el, event.title, eventUrl);
        return false;
      }
    }
  });
  calendar.render();
});
</script><div id="full_calendar_block" style="margin-top:20px;"></div>
<div style="margin:10px auto;width:auto;"><{$block.style_mark}></div>

<div style="text-align:right;">
    <a href="<{$xoops_url}>/modules/tad_cal" class="btn btn-sm btn-xs btn-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>
<{if $block.eventAdd|default:''}>
<div class="modal fade" id="tadCalBlockEventModal" tabindex="-1" aria-labelledby="tadCalBlockEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tadCalBlockEventModalLabel"><{$smarty.const._MB_TADCAL_TITLE}></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><{$smarty.const._CANCEL}></button>
        <button type="submit" class="btn btn-primary"><{$smarty.const._SUBMIT}></button>
      </div>
    </form>
  </div>
</div>
<{/if}>
