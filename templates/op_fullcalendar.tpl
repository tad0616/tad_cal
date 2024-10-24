<h2 class="sr-only visually-hidden">Calendar</h2>

<script type="text/javascript">
$(document).ready(function(){

  var calendar = $("#calendar").fullCalendar({
    theme: true,
    firstDay:<{$firstDay|default:''}>,
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText:{today:"<{$smarty.const._MD_TADCAL_TODAY}>"},
    header: {
      left: "prev,today,next",
      right: "title"
      // right: 'month,agendaWeek,agendaDay,listWeek'
    },
    <{$eventAdd|default:''}>
    <{$eventDrop|default:''}>
    events: function(start, end, timezone, callback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_event.php",
      {
        start: start.format(),
        end: end.format(),
        cate_sn: <{$cate_sn|default:''}>
      },
      function(result) {
        callback(result);
      });
    },
      <{$eventShowMode|default:''}>: function(event) {
        if (event.rel) {
          $(this).qtip({
           content: {
            // 設定載入中圖片
            text: "<img class='throbber' src='images/loading.gif' alt='Loading...'>",
            ajax: {
              url: event.rel //載入指定之連結
            },
            title: {
             text: event.title, //給予標題文字
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
    }
  });
});
</script>


<{if $cate.cate_title|default:false}><h1><{$cate.cate_title}></h1><{/if}>
<div id="calendar" style="margin-top:20px;"></div>
<div style="margin:10px auto;width:auto;"><{$style_mark|default:''}></div>
