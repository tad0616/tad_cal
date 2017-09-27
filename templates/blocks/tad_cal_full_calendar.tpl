
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
<script type='text/javascript' src='<{$xoops_url}>/modules/tad_cal/class/fullcalendar/gcal.js'></script>
=======
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/module.css" />
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/fullcalendar/redmond/theme.css" />
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.3.4.0.min.css">
<script src="<{$xoops_url}>/modules/tad_cal/class/moment/moment-with-locales.2.18.1.min.js" type="text/javascript"></script>
<script src="<{$xoops_url}>/modules/tad_cal/class/jquery-impromptu.6.2.3.min.js" type="text/javascript"></script>
<script src="<{$xoops_url}>/modules/tad_cal/class/fullcalendar/fullcalendar.3.4.0.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<{$xoops_url}>/modules/tad_cal/class/qtip/jquery.qtip.3.0.3.min.css" />
<script src="<{$xoops_url}>/modules/tad_cal/class/qtip/jquery.qtip.3.0.3.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<{$xoops_url}>/modules/tadtools/My97DatePicker/WdatePicker.js"></script>
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c

<style type="text/css">
<{$block.style_css}>
</style>

<script type="text/javascript">
$(document).ready(function(){

  var calendar = $("#full_calendar_block").fullCalendar({
<<<<<<< HEAD

    firstDay:<{$block.firstDay}>,
=======
    theme: true,
    locale: navigator.language,
    firstDay:<{$block.firstDay}>,
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText:{today:"<{$smarty.const._MB_TADCAL_TODAY}>"},
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c
    header: {
      left: 'prev,today,next',
      center: 'title',
      right: 'month,agendaWeek,agendaDay,listWeek'
    },
<<<<<<< HEAD
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
=======
    <{$block.eventAdd}>
    <{$block.eventDrop}>
    events: function(start, end, timezone ,callback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_event.php",
      {
        start: start.format(),
        end: end.format(),
        cate_sn: <{$block.cate_sn}>
      },
      function(result) {
        callback(result);
      });
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c
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
