<{if $all_cal|default:false}>

    <table class="table table-striped table-hover">
        <tr>
            <th><{$smarty.const._MA_TADCAL_CATE_TITLE}></th>
            <th><{$smarty.const._MA_TADCAL_CATE_EVENTS_AMOUNT}></th>
            <th><{$smarty.const._MA_TADCAL_CATE_ENABLE}></th>
            <th><{$smarty.const._MA_TADCAL_ENABLE_GROUP}></th>
            <th><{$smarty.const._MA_TADCAL_ENABLE_UPLOAD_GROUP}></th>
            <th><{$smarty.const._MA_TADCAL_LAST_UPDATE}></th>
            <th><{$smarty.const._TAD_FUNCTION}></th>
        </tr>
        <tbody id="sort">
            <{foreach from=$all_cal item=cal}>
                <tr id="tr_<{$cal.cate_sn}>">
                    <td style="background-color:<{$cal.cate_bgcolor}>; color:<{$cal.cate_color}>;">
                    <img src="../images/date.png" title="<{$cal.cate_title}>" alt="<{$cal.cate_title}>" style="margin-right:4px;" align="absmiddle">
                    <{$cal.cate_title}>
                    </td>
                    <td class="c"><{$cal.counter}></td>
                    <td class="c"><{$cal.enable}></td>
                    <td class="c"><{$cal.g_txt}></td>
                    <td class="c"><{$cal.gu_txt}></td>
                    <td class="c"><{$cal.last}></td>
                    <td>
                        <a href="javascript:delete_tad_cal_cate_func(<{$cal.cate_sn}>);" class="btn btn-sm btn-danger"><i class="fa fa-times" aria-hidden="true"></i></a>
                        <a href="main.php?op=tad_cal_cate_form&cate_sn=<{$cal.cate_sn}>" class="btn btn-sm btn-warning"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}></a>
                    </td>
                </tr>
            <{/foreach}>
        </tbody>
    </table>

    <div class="bar">
        <{$bar|default:''}>

        <a href="main.php?op=tad_cal_cate_form" class="btn btn-lg btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i> <{$smarty.const._TAD_ADD_CAL}></a>
    </div>


    <script language="JavaScript">
        $().ready(function(){
            $("#sort").sortable({ opacity: 0.6, cursor: "move", update: function() {
                var order = $(this).sortable("serialize") + "&action=updateRecordsListings";
                $.post("save_sort.php", order, function(theResponse){
                    $("#save_msg").html(theResponse);
                });
            }
            });
        });
    </script>
<{else}>
    <div class="bar">
        <a href="main.php?op=tad_cal_cate_form" class="btn btn-lg btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i> <{$smarty.const._TAD_ADD_CAL}></a>
    </div>
<{/if}>