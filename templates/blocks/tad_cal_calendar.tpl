
<{$block.jquery_path}>

<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/module.css">

<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/jquery-impromptu.css">
<script src="<{$xoops_url}>/modules/tad_cal/class/jquery-impromptu.js" type="text/javascript"></script>


<link rel='stylesheet' href='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.css' />
<script src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/moment.min.js'></script>
<script src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.js'></script>
<script src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/locale-all.js'></script>


<link rel="stylesheet" type="text/css" href="class/qtip/jquery.qtip.min.css" />
<script src="<{$xoops_url}>/modules/tad_cal/class/qtip/jquery.qtip.min.js" type="text/javascript"></script>

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

<script type="text/javascript">
$(document).ready(function(){

  var calendar = $("#block_calendar").fullCalendar({

    firstDay:<{$block.firstDay}>,
    header: {
      left: 'prev,today,next',
      center: 'title',
      right: 'month,agendaWeek,agendaDay,listWeek'
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
        if (event.url) {
            window.open(event.url);
            return false;
        }
    }

  });
});



</script>

<div id="block_calendar"></div>
<div style="text-align:right;">
  <a href="<{$xoops_url}>/modules/tad_cal" class="label label-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>