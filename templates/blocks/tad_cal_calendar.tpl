
<{$block.jquery_path}>


<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/module.css">
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tadtools/fullcalendar/redmond/theme.css">
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tadtools/fullcalendar/fullcalendar.css">

<script src="<{$xoops_url}>/modules/tadtools/moment/moment-with-locales.min.js" type="text/javascript"></script>
<script src="<{$xoops_url}>/modules/tad_cal/class/jquery-impromptu.6.2.3.min.js" type="text/javascript"></script>
<script src="<{$xoops_url}>/modules/tadtools/fullcalendar/fullcalendar.js" type="text/javascript"></script>
<script src="<{$xoops_url}>/modules/tadtools/fullcalendar/fullcalendar/gcal.js" type="text/javascript" ></script>

<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tadtools/jquery.qtip_2/jquery.qtip.min.css">
<script src="<{$xoops_url}>/modules/tadtools/jquery.qtip_2/jquery.qtip.min.js" type="text/javascript"></script>

<style type="text/css">
#block_calendar *{
 font-size:11px;
 line-height:120%;
}

.blockevent,
.fc-agenda .blockevent .fc-event-time,
.blockevent a {
    text-align:center;
    font-weight:bold;
}
</style>

<script type="text/javascript">
$(document).ready(function(){
  var block_calendar = $("#block_calendar").fullCalendar({
    theme: true,
    locale: navigator.language,
    firstDay:<{$block.firstDay}>,
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText:{today:"<{$smarty.const._MB_TADCAL_TODAY}>"},
    timezone: '<{$block.timezone}>',
    height: 'auto',
    header: {
      left: "prev,today,next",
      center: "",
      right: "title"
    },
    events: function(start, end, timezone, callback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_block_event.php",
      {
        start: start.format(),
        end: end.format()
      },
      function(result) {
        callback(result);
      });
    },
    eventClick: function(event) {

        var moment = event.start;

        var event_month= event.start.get('month')*1 + 1;

          $(this).qtip({
           content: {
            // 設定載入中圖片
            text: "<img class='throbber' src='<{$xoops_url}>/modules/tad_cal/images/loading.gif' alt='Loading...' />",
            ajax: {
              url: "<{$xoops_url}>/modules/tad_cal/get_block_event.php?op=title&start=" + moment.format()
            },
            title: {
             text: event.show_date, //給予標題文字
             button: true
            }
           },
           position: {
              at: "top center", // 提示位置
              my: "bottom center",
              viewport: $(window), //確保提示在畫面上
              effect: false, // 取消動畫
              adjust: {
               target: $(document),
               resize: true // Can be ommited (e.g. default behaviour)
              }
           },
           show: {
            event: "false",
            ready: true, // ... but show the tooltip when ready
            solo: true // 一次只秀出一個提示
           },
           hide: "unfocus",
           style: {
            classes: "ui-tooltip-shadow ui-tooltip-rounded"
           }
          })
          return false;
    }
  });
});
</script>

<div id="block_calendar"></div>
<div style="text-align:right;">
  <a href="<{$xoops_url}>/modules/tad_cal" class="label label-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>