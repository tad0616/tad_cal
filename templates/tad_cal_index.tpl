<{$toolbar}>


<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="module.css" >
<style type="text/css">
<{$style_css}>
</style>
<link rel="stylesheet" type="text/css" href="class/jquery-impromptu.css">
<script src="class/jquery-impromptu.js" type="text/javascript"></script>


<link rel='stylesheet' href='class/fullcalendar/fullcalendar.css' />
<script src='class/fullcalendar/moment.min.js'></script>
<script src='class/fullcalendar/fullcalendar.js'></script>
<script src='class/fullcalendar/locale-all.js'></script>


<link rel="stylesheet" type="text/css" href="class/qtip/jquery.qtip.min.css" />
<script src="class/qtip/jquery.qtip.min.js" type="text/javascript"></script>
=======
<link rel="stylesheet" type="text/css" href="module.css" />
<link rel="stylesheet" type="text/css" href="class/fullcalendar/redmond/theme.css" />
<link rel="stylesheet" type="text/css" href="class/fullcalendar/fullcalendar.3.4.0.min.css">
<style type="text/css">
<{$style_css}>
</style>
<script src="class/moment/moment-with-locales.2.18.1.min.js" type="text/javascript"></script>
<script src="class/jquery-impromptu.6.2.3.min.js" type="text/javascript"></script>
<script src="class/fullcalendar/fullcalendar.3.4.0.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="class/qtip/jquery.qtip.3.0.3.min.css" />
<script src="class/qtip/jquery.qtip.3.0.3.min.js" type="text/javascript"></script>
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c
<script type="text/javascript" src="<{$xoops_url}>/modules/tadtools/My97DatePicker/WdatePicker.js"></script>

<script type='text/javascript' src='class/fullcalendar/gcal.js'></script>

<script type="text/javascript">
$(document).ready(function(){
  var currentMousePos = {
      x: -1,
      y: -1
  };

  jQuery(document).on("mousemove", function (event) {
     currentMousePos.x = event.pageX;
     currentMousePos.y = event.pageY;
  });

  var calendar = $("#calendar").fullCalendar({

    firstDay:<{$firstDay}>,
<<<<<<< HEAD
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
          url: 'get_event.php', // use the `url` property
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
    // eventClick: function(event, jsEvent, view) {
    //    var title = prompt('Event Title:', event.title, { buttons: { Ok: true, Cancel: false} });
    //    if (title){
    //    event.title = title;
    //    $.ajax({
    //      url: 'event.php',
    //      data: 'type=changetitle&title='+title+'&eventid='+event.id,
    //      type: 'POST',
    //      dataType: 'json',
    //      success: function(response){
    //        if(response.status == 'success')
    //        $('#calendar').fullCalendar('updateEvent',event);
    //      },
    //      error: function(e){
    //        alert('Error processing your request: '+e.responseText);
    //      }
    //    });
    //    }
    // },
    eventDrop: function(event, delta, revertFunc) {
       var sn = event.id;
       var title = event.title;
       var start = event.start.format();
       var end = (event.end == null) ? start : event.end.format();
       u$.ajax({
          url: 'event.php',
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
=======
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText:{today:"<{$smarty.const._MD_TADCAL_TODAY}>"},
    header: {
      left: "prev,today,next",
      right: "title"
    },
    <{$eventAdd}>
    <{$eventDrop}>
    events: function(start, end, timezone, callback) {
      $.getJSON("get_event.php",
      {
        start: start.format(),
        end: end.format(),
        cate_sn: <{$cate_sn}>
      },
      function(result) {
        callback(result);
      });
>>>>>>> b8a82cc8ac6df837a8d26094054e90115ffd5e6c
    },
    eventDragStop: function (event, jsEvent, ui, view) {
       if (isElemOverDiv()) {
         var con = confirm('Are you sure to delete this event permanently?');
         if(con == true) {
            $.ajax({
              url: 'event.php',
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


function delete_tad_cal_event_func(sn){
  var sure = window.confirm("<{$smarty.const._TAD_DEL_CONFIRM}>");
  if (!sure)	return;
  location.href="index.php?op=delete_tad_cal_event&sn=" + sn;
}
</script>
<{if $cate.cate_title}><h1><{$cate.cate_title}></h1><{/if}>
<div id="calendar" style="margin-top:20px;"></div>
<div style="margin:10px auto;width:auto;"><{$style_mark}></div>
<{$my_counter}>