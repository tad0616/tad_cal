
<script>
function delete_tad_cal_event_func(sn){
    var sure = window.confirm("<{$smarty.const._TAD_DEL_CONFIRM}>");
    if (!sure)  return;
    location.href="<{$xoops_url}>/modules/tad_cal/event.php?op=delete_tad_cal_event&sn=" + sn;
}
</script>

<h1>
<{include file="$xoops_rootpath/modules/tad_cal/templates/sub_todo.tpl"}>
<{$title}>
</h1>

<div class="row">
    <div class="col-sm-6">
    <img src='<{$xoops_url}>/modules/tad_cal/images/date.png' alt='<{$smarty.const._MD_TADCAL_TIME}>'>
    <{$date}>
    </div>

    <div class="col-sm-6">
    <{$location_img}>
    <{$location}>
    </div>
</div>

<div class="alert alert-info">
  <{$details}>
</div>
<div style='text-align:right;font-size:0.75rem;color:gray;'><{$cate_title}> / Posted by <{$uid_name}></div>

<{$fun}>

<div><{$push_url}></div>
<div><{$facebook_comments}></div>