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
                    <td style="background-color:<{$cal.cate_bgcolor|escape:'html'}>; color:<{$cal.cate_color|escape:'html'}>;">
                    <img src="../images/date.png" title="<{$cal.cate_title|escape:'html'}>" alt="<{$cal.cate_title|escape:'html'}>" style="margin-right:4px;" align="absmiddle">
                    <{$cal.cate_title|escape:'html'}>
                    </td>
                    <td class="c"><{$cal.counter}></td>
                    <td class="c"><{$cal.enable}></td>
                    <td class="c"><{$cal.g_txt}></td>
                    <td class="c"><{$cal.gu_txt}></td>
                    <td class="c"><{$cal.last}></td>
                    <td>
                        <form action="main.php" method="post" class="d-inline" onsubmit="return confirm('<{$smarty.const._TAD_DEL|escape:'javascript'}>?');">
                            <input type="hidden" name="op" value="delete_tad_cal_cate">
                            <input type="hidden" name="cate_sn" value="<{$cal.cate_sn}>">
                            <{$delete_token_html|default:''}>
                            <button type="submit" class="btn btn-sm btn-danger" aria-label="<{$smarty.const._TAD_DEL|escape:'html'}>"><i class="fa fa-times" aria-hidden="true"></i></button>
                        </form>
                        <a href="main.php?op=tad_cal_cate_form&cate_sn=<{$cal.cate_sn}>" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}></a>
                    </td>
                </tr>
            <{/foreach}>
        </tbody>
    </table>

    <div class="bar">
        <{$bar|default:''}>

        <a href="main.php?op=tad_cal_cate_form" class="btn btn-lg btn-primary"><i class="fa fa-square-plus" aria-hidden="true"></i> <{$smarty.const._TAD_ADD_CAL}></a>
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
        <a href="main.php?op=tad_cal_cate_form" class="btn btn-lg btn-primary"><i class="fa fa-square-plus" aria-hidden="true"></i> <{$smarty.const._TAD_ADD_CAL}></a>
    </div>
<{/if}>