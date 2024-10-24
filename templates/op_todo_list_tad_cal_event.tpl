
<{if $smarty.get.op=="todo_list_ok"}>
    <h2><{$smarty.const._MD_TADCAL_TODOLIST_DONE}></h2>
<{else}>
    <h2><{$smarty.const._MD_TADCAL_TODOLIST}></h2>
<{/if}>
<table class="table table-striped table-hover">
<tr>
    <th><{$smarty.const._MD_TADCAL_START}></th>
    <th><{$smarty.const._MD_TADCAL_TITLE}></th>
    <th><{$smarty.const._MD_TADCAL_LOCATION}></th>
    <th><{$smarty.const._MD_TADCAL_CATE_SN}></th>
    <th><{$smarty.const._TAD_FUNCTION}></th>
</tr>
<tbody>
<{foreach from=$all_content item=cal}>
    <tr>
        <td style='font-size:0.75rem;font-family:arial;'><{$cal.date}></td>
        <td>
            <{include file="$xoops_rootpath/modules/tad_cal/templates/sub_todo.tpl" tag=$cal.tag sn=$cal.sn}>
            <a href='<{$xoops_url}>/modules/tad_cal/event.php?sn=<{$cal.sn}>'><{$cal.re}><{$cal.title}></a>
            <div style='color:gray;font-size:0.75rem;'><{$cal.details}></div>
        </td>
        <td><{$cal.location}></td>
        <td><{$cal.cate_title}></td>
        <{$cal.fun}>
    </tr>
<{/foreach}>
</tbody>
</table>

<a href='<{$xoops_url}>/modules/tad_cal/event.php?op=tad_cal_event_form&tag=todo'  class='btn btn-info'><{$smarty.const._TAD_ADD_EVENT}></a>
<{if $smarty.get.op=="todo_list_ok"}>
    <a href='<{$xoops_url}>/modules/tad_cal/excel.php?tag=todo-ok'  class='btn btn-success'><{$smarty.const._MD_TADCAL_EXCEL_DONE}></a>
<{else}>
    <a href='<{$xoops_url}>/modules/tad_cal/excel.php?tag=todo'  class='btn btn-success'><{$smarty.const._MD_TADCAL_EXCEL}></a>
<{/if}>
<{$bar|default:''}>
