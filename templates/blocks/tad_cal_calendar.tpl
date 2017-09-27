
<{$block.jquery_path}>

<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/module.css">

<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/jquery-impromptu.css">
<script src="<{$xoops_url}>/modules/tad_cal/class/jquery-impromptu.js" type="text/javascript"></script>


<link rel='stylesheet' href='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.css' />
<script src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/moment.min.js'></script>
<script src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.js'></script>
<script src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/locale-all.js'></script>


<link rel="stylesheet" type="text/css" href="class/qtip/jquery.qtip.min.css" />
<script src="<{$xoops_url}>/modules/tad_cal/class/qtip/jquery.qtip.min.js" type="text/javascript"></script>
=======
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.3.4.0.min.css">
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/fullcalendar/redmond/block.css" />
<script src="<{$xoops_url}>/modules/tad_cal/class/moment/moment-with-locales.2.18.1.min.js" type="text/javascript"></script>
<script src="<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.3.4.0.min.js" type="text/javascript"></script>
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c

<style type="text/css">
#block_calendar *{
 font-size:10px;
 line-height:120%;
}

.blockevent,
.fc-agenda .blockevent .fc-event-time,
.blockevent a {
    text-align:center;
    font-weight:bold;
    background-color:#6699CC; /* background color */
    color: white;           /* text color */
}
</style>

<<<<<<< HEAD
<script type="text/javascript">
$(document).ready(function(){

  var calendar = $("#block_calendar").fullCalendar({

    firstDay:<{$block.firstDay}>,
    header: {
      left: 'prev,today,next',
      center: 'title',
      right: 'month,agendaWeek,agendaDay,listWeek'
=======
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/qtip/jquery.qtip.3.0.3.min.css" />
<script src="<{$xoops_url}>/modules/tad_cal/class/qtip/jquery.qtip.3.0.3.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
  $("#block_calendar").fullCalendar({
    theme: true,
    locale: window.navigator.userLanguage || window.navigator.language,
    firstDay:<{$block.firstDay}>,
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
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c
    },
    navLinks: true, // can click day/week names to navigate views
    editable: true,
    droppable: true,
    selectable: true,
    // eventLimit: true, // allow "more" link when too many events
    locale : 'zh-tw',
    googleCalendarApiKey: 'AIzaSyCaZ3uPmTzibuAXMD8c8_zJRYgRslvKIuc',
    eventSources: [
        {
          googleCalendarId: 'tad0616@gmail.com'
        },
        {
          url: '<{$xoops_url}>/modules/tad_cal/get_event.php', // use the `url` property
          color: 'yellow',    // an option!
          textColor: 'black'  // an option!
        }
    ],
    eventClick: function(event) {
<<<<<<< HEAD
        if (event.url) {
            window.open(event.url);
            return false;
        }
=======
        var event_month= event.start.get('month')*1 + 1;
        $(this).qtip({
         content: {
          text: "<img class='throbber' src='<{$xoops_url}>/modules/tad_cal/images/loading.gif' alt='Loading...' />",
          ajax: {
            url: "<{$xoops_url}>/modules/tad_cal/get_block_event.php?op=title&start=" + event.start.valueOf()
          },
          title: {
           text: "" + event.start.get('year') + "-" + event_month +"-" + event.start.get('date'),
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
          classes: "ui-tooltip-blue ui-tooltip-shadow ui-tooltip-rounded"
         }
      })
      return false;

>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c
    }

  });
});



</script>

<div id="block_calendar"></div>
<div style="text-align:right;">
  <a href="<{$xoops_url}>/modules/tad_cal" class="label label-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>