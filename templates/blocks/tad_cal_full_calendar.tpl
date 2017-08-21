
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
<script type='text/javascript' src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/gcal.js'></script>

<style type="text/css">
<{$block.style_css}>
</style>

<script type="text/javascript">
$(document).ready(function(){

  var calendar = $("#full_calendar_block").fullCalendar({

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
    },

    eventDrop: function(event, delta, revertFunc) {
       var sn = event.id;
       var title = event.title;
       var start = event.start.format();
       var end = (event.end == null) ? start : event.end.format();
       u$.ajax({
          url: '<{$xoops_url}>/modules/tad_cal/event.php',
          data: 'op=ajax_update_date&sn='+sn+'&start='+start+'&end='+end+'&eventid='+id,
          type: 'POST',
          dataType: 'json',
          success: function(response){
            if(response.status != 'success')
            revertFunc();
          },
          error: function(e){
            revertFunc();
            alert('Error processing your request: '+e.responseText);
          }
          });
    },
    eventDragStop: function (event, jsEvent, ui, view) {
       if (isElemOverDiv()) {
         var con = confirm('Are you sure to delete this event permanently?');
         if(con == true) {
            $.ajax({
              url: '<{$xoops_url}>/modules/tad_cal/event.php',
              data: 'type=remove&eventid='+event.id,
              type: 'POST',
              dataType: 'json',
              success: function(response){
                if(response.status == 'success')
                  $('#calendar').fullCalendar('removeEvents');
                  $('#calendar').fullCalendar('addEventSource', JSON.parse(json_events));
              },
              error: function(e){
              alert('Error processing your request: '+e.responseText);
              }
           });
          }
        }
    },
  });
});



</script>

<div id="full_calendar_block" style="margin-top:20px;"></div>
<div style="margin:10px auto;width:auto;"><{$block.style_mark}></div>
