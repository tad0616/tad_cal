
<h1>
<{include file="$xoops_rootpath/modules/tad_cal/templates/sub_todo.tpl"}>
<{$title|default:''}>
</h1>

<div class="row">
    <div class="col-sm-6">
    <img src='<{$xoops_url}>/modules/tad_cal/images/date.png' alt='<{$smarty.const._MD_TADCAL_TIME}>'>
    <{$date|default:''}>
    </div>

    <div class="col-sm-6">
    <{$location_img|default:''}>
    <{$location|default:''}>
    </div>
</div>

<div class="alert alert-info">
  <{$details|default:''}>
</div>
<div style='text-align:right;font-size:0.75rem;color:gray;'><{$cate_title|default:''}> / Posted by <{$uid_name|default:''}></div>

<{$fun|default:''}>

<div><{$push_url|default:''}></div>