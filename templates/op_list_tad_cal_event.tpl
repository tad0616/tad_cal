
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
        <td><a href='<{$xoops_url}>/modules/tad_cal/event.php?sn=<{$cal.sn}>'><{$cal.re}><{$cal.title}></a><div style='color:gray;font-size:0.75rem;'><{$cal.details}></div></td>
        <td><{$cal.location}></td>
        <td><{$cal.cate_title}></td>
        <{$cal.fun}>
    </tr>
<{/foreach}>
</tbody>
</table>
<a href='<{$xoops_url}>/modules/tad_cal/event.php?op=tad_cal_event_form'  class='btn btn-info'><{$smarty.const._TAD_ADD_EVENT}></a>
<{$bar|default:''}>
