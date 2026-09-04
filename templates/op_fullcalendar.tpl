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
  var calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
    initialView: "dayGridMonth",
    firstDay: <{$firstDay|default:''}>,
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText: {today: "<{$smarty.const._MD_TADCAL_TODAY}>"},
    headerToolbar: {left: "prev,today,next", center: "title", right: ""},
    <{$eventAdd|default:''}>
    <{$eventDrop|default:''}>
    events: function(fetchInfo, successCallback, failureCallback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_event.php", {
        start: fetchInfo.startStr,
        end: fetchInfo.endStr,
        cate_sn: <{$cate_sn|default:''}>
      }, successCallback).fail(failureCallback);
    },
    <{$eventShowMode|default:''}>: function(clickInfo) {
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
</script><{if $cate.cate_title|default:false}><h1><{$cate.cate_title}></h1><{/if}>
<div id="calendar" style="margin-top:20px;"></div>
<div style="margin:10px auto;width:auto;"><{$style_mark|default:''}></div>

<{if $eventAdd|default:''}>
<div class="modal fade" id="tadCalEventModal" tabindex="-1" aria-labelledby="tadCalEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tadCalEventModalLabel"><{$smarty.const._MD_TADCAL_TITLE}></h5>
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
