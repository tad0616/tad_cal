<script type="text/javascript">
$(document).ready(function(){
  var calendar = $("#full_calendar_block").fullCalendar({
    theme: true,
    firstDay:<{$block.firstDay}>,
    locale: navigator.language,
    locale: window.navigator.userLanguage || window.navigator.language,
    buttonText:{today:"<{$smarty.const._MB_TADCAL_TODAY}>"},
    header: {
      left: "prev,today,next",
      right: "title"
    },
    <{$block.eventAdd}>
    <{$block.eventDrop}>
    events: function(start, end, timezone ,callback) {
      $.getJSON("<{$xoops_url}>/modules/tad_cal/get_event.php",
      {
        <{if $block.cate_sn|default:false}>
        start: start.format(),
        end: end.format(),
        cate_sn: <{$block.cate_sn}>
        <{else}>
        start: start.format(),
        end: end.format()
        <{/if}>
      },
      function(result) {
        callback(result);
      });
    },
      <{$block.eventShowMode}>: function(event) {
        if (event.rel) {
          $(this).qtip({
           content: {
            // 設定載入中圖片
            text: "<img class='throbber' src='<{$xoops_url}>/modules/tad_cal/images/loading.gif' alt='Loading...'>",
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


function delete_tad_cal_event_func(sn){
  var sure = window.confirm("<{$smarty.const._TAD_DEL_CONFIRM}>");
  if (!sure)  return;
  location.href="<{$xoops_url}>/modules/tad_cal/index.php?op=delete_tad_cal_event&sn=" + sn;
}
</script>

<div id="full_calendar_block" style="margin-top:20px;"></div>
<div style="margin:10px auto;width:auto;"><{$block.style_mark}></div>

<div style="text-align:right;">
    <a href="<{$xoops_url}>/modules/tad_cal" class="btn btn-sm btn-xs btn-info"><{$smarty.const._MB_TADCAL_TO_INDEX}></a>
</div>