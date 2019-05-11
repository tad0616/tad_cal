<link href="<{$xoops_url}>/modules/tadtools/css/font-awesome/css/font-awesome.css" rel="stylesheet">
<div class="container-fluid">
  <{if $op=="tad_cal_cate_form"}>
    <h1><{$smarty.const._MA_TADCAL_CATE_FORM}></h1>

    <script type="text/javascript" src="<{$xoops_url}>/modules/tadtools/mColorPicker/javascripts/mColorPicker.js" charset="UTF-8"></script>
    <script type="text/javascript">
      $("#color").mColorPicker({
        imageFolder: "<{$xoops_url}>/modules/tadtools/mColorPicker/images/"
      });
    </script>

    <form action="main.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal" role="form">
      <div class="form-group">
        <!--行事曆標題-->
        <label class="col-sm-2 control-label">
          <{$smarty.const._MA_TADCAL_CATE_TITLE}>
        </label>
        <div class="col-sm-4">
          <input type="text" name="cate_title" class="form-control" value="<{$cate_title}>" id="cate_title" class="validate[required]">
        </div>
        <!--是否啟用-->
        <label class="col-sm-2 control-label">
          <{$smarty.const._MA_TADCAL_CATE_ENABLE}>
        </label>
        <div class="col-sm-4">
          <label class="radio-inline">
            <input type="radio" name="cate_enable" id="cate_enable" value="1" <{$cate_enable1}>><{$smarty.const._YES}>
          </label>
          <label class="radio-inline">
            <input type="radio" name="cate_enable" id="cate_enable" value="0" <{$cate_enable0}>><{$smarty.const._NO}>
          </label>
        </div>
      </div>

      <div class="form-group">
        <!--背景色-->
        <label class="col-sm-2 control-label">
          <{$smarty.const._MA_TADCAL_CATE_BGCOLOR}>
        </label>
        <div class="col-sm-4">
          <input type="text" name="cate_bgcolor" id="cate_bgcolor" class="form-control" value="<{$cate_bgcolor}>" data-text="hidden" data-hex="true">
        </div>
        <!--文字色-->
        <label class="col-sm-2 control-label">
          <{$smarty.const._MA_TADCAL_CATE_COLOR}>
        </label>
        <div class="col-sm-4">
          <input type="text" name="cate_color" id="cate_color" class="form-control" value="<{$cate_color}>" data-text="hidden" data-hex="true">
        </div>
      </div>



      <div class="form-group">
        <!--可讀群組-->
        <label class="col-sm-2 control-label">
          <{$smarty.const._MA_TADCAL_ENABLE_GROUP}>
        </label>
        <div class="col-sm-4">
          <{$enable_group}>
        </div>
        <!--可寫群組-->
        <label class="col-sm-2 control-label">
          <{$smarty.const._MA_TADCAL_ENABLE_UPLOAD_GROUP}>
        </label>
        <div class="col-sm-4">
          <{$enable_upload_group}>
        </div>
      </div>


      <{if $cate_handle}>

        <div class="form-group">
          <!--google帳號-->
          <label class="col-sm-2 control-label">
            <{$smarty.const._MA_TADCAL_GOOGLE_ID}>
          </label>
          <div class="col-sm-4">
            <input type="text" name="google_id" value="<{$google_id}>" class="form-control" id="google_id" ><{$smarty.const._MA_TADCAL_GOOGLE_ID_NOTE}>
          </div>
          <!--google密碼-->
          <label class="col-sm-2 control-label">
            <{$smarty.const._MA_TADCAL_GOOGLE_PASS}>
          </label>
          <div class="col-sm-4">
            <input type="password" name="google_pass" value="<{$google_pass}>" class="form-control" id="google_pass" >
          </div>
        </div>
      <{/if}>



      <div class="text-center">
        <!--遠端行事曆-->
        <input type="hidden" name="cate_handle" value="<{$cate_handle}>">
        <!--行事曆排序-->
        <input type="hidden" name="cate_sort" value="<{$cate_sort}>" id="cate_sort">
        <!--行事曆編號-->
        <input type="hidden" name="cate_sn" value="<{$cate_sn}>">
        <input type="hidden" name="op" value="<{$next_op}>">
        <button type="submit" class="btn btn-primary"><{$smarty.const._TAD_SAVE}></button>
      </div>
    </form>

  <{elseif $op=="link_to_google"}>

    <h1><{$smarty.const._MA_TADCAL_CATE_GFORM}></h1>
    <{if $all}>
      <form action="main.php" method="post" id="myForm" enctype="multipart/form-data">
        <table class="table table-striped table-hover">
        <tr>
        <th colspan=2><{$smarty.const._MA_TADCAL_CATE_GTITLE}></th><th><{$smarty.const._MA_TADCAL_CATE_GNUM}></th></tr>

        <{foreach item=cal from=$all}>
          <{if $cal.in_array}>
            <tr>
              <td class="c"><{$smarty.const._MA_TADCAL_GOOGLE_EXIST}></td>
              <td><img src="../images/google.png" title="<{$cal.cate_handle}>" alt="<{$cal.cate_handle}>" style="margin-right:4px;" align="absmiddle"><{$cal.cate_title}></td>
              <td style="text-align:right;"><{$cal.totalResults}></td>
            </tr>
          <{else}>
            <tr>
              <td class="c"><input type="checkbox" name="handle[<{$cal.j}>]" value="<{$cal.handle}>"></td>
              <td><input type="text" name="title[<{$cal.j}>]" value="<{$cal.cal_title}>" size=30></td>
              <td style="text-align:right;"><{$cal.totalResults}></td>
            </tr>
          <{/if}>
        <{/foreach}>
      <tr><td class="bar" colspan="3">
      <input type="hidden" name="google_id" value="<{$id}>">
      <input type="hidden" name="google_pass" value="<{$pass}>">
      <input type="hidden" name="op" value="save_google">
      <input type="submit" value="<{$smarty.const._MA_TADCAL_NEXT}>"></td></tr>
      </table>
      </form>
    <{/if}>
  <{elseif $op=="tad_cal_add_gcal_form"}>
    <{if !$curl_init}>
      <div style="border:1px solid gray;color:#990000;padding:10px;margin:10px;"><{$smarty.const._MA_TADCAL_CURL_NEED}></div>
    <{else}>
      <form action="main.php" method="post" id="myForm2" enctype="multipart/form-data">
        <table>
          <!--google帳號-->
          <tr><td><{$smarty.const._MA_TADCAL_GOOGLE_ID}></td>
          <td><input type="text" name="google_id" size="30" id="google_id" ><{$smarty.const._MA_TADCAL_GOOGLE_ID_NOTE}></td></tr>

          <!--google密碼-->
          <tr><td><{$smarty.const._MA_TADCAL_GOOGLE_PASS}></td>
          <td><input type="password" name="google_pass" size="30" id="google_pass" ></td></tr>
          <tr><td class="bar" colspan="2">

          <input type="hidden" name="op" value="link_to_google">
          <input type="submit" value="<{$smarty.const._MA_TADCAL_NEXT}>"></td></tr>
        </table>
      </form>
    <{/if}>
  <{else}>
    <{if $all_content==""}>

      <a href="main.php?op=tad_cal_cate_form" class="btn btn-lg btn-primary"><img src="../images/date_big.png" title="<{$smarty.const._TAD_ADD_CAL}>" alt="<{$smarty.const._TAD_ADD_CAL}>"><{$smarty.const._TAD_ADD_CAL}></a>

      <a href="main.php?op=tad_cal_add_gcal_form"  class="btn btn-lg disabled btn-primary"><img src="../images/google_big.png" title="<{$smarty.const._TAD_ADD_GOOGLE}>" alt="<{$smarty.const._TAD_ADD_GOOGLE}>" align="left"><{$smarty.const._TAD_ADD_GOOGLE}><br><{$smarty.const._MA_TADCAL_FUNCTION_CLOSED}></a>

    <{else}>

      <{$jquery}>

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

        function delete_tad_cal_cate_func(cate_sn){
        var sure = window.confirm("<{$smarty.const._TAD_DEL_CONFIRM}>");
        if (!sure)  return;
        location.href="main.php?op=delete_tad_cal_cate&cate_sn=" + cate_sn;
      }
      </script>

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
      <{foreach item=cal from=$all_content}>
        <tr id="tr_<{$cal.cate_sn}>">
          <td style="background-color:<{$cal.cate_bgcolor}>;color:<{$cal.cate_color}>;">
          <{if $cal.cate_handle==""}>
            <img src="../images/date.png" title="<{$cal.cate_title}>" alt="<{$cal.cate_title}>" style="margin-right:4px;" align="absmiddle">
          <{else}>
            <img src="../images/google.png" title="<{$cal.cate_handle}>" alt="<{$cal.cate_handle}>" style="margin-right:4px;" align="absmiddle">
          <{/if}>
          <{$cal.cate_title}>
          </td>
          <td class="c"><{$cal.counter}></td>
          <td class="c"><{$cal.enable}></td>
          <td class="c"><{$cal.g_txt}></td>
          <td class="c"><{$cal.gu_txt}></td>
          <td class="c"><{$cal.last}></td>
          <td>
          <a href="javascript:delete_tad_cal_cate_func(<{$cal.cate_sn}>);"><img src="../images/delete.png" title="<{$smarty.const._TAD_DEL}>" alt="<{$smarty.const._TAD_DEL}>" align="absmiddle" style="margin-right:4px;"></a>

          <a href="main.php?op=tad_cal_cate_form&cate_sn=<{$cal.cate_sn}>" class="btn btn-xs btn-warning"><img src="../images/edit.png" title="<{$smarty.const._TAD_EDIT}>" alt="<{$smarty.const._TAD_EDIT}>" align="absmiddle" style="margin-right:4px;"><{$smarty.const._TAD_EDIT}></a>

          <{if $cal.cate_handle!=""}>
            <a href="import.php?cate_sn=<{$cal.cate_sn}>" class="btn btn-xs btn-info"><img src="../images/sync.png" title="<{$smarty.const._MA_TADCAL_GOOGLE_IMPORT}>" alt="<{$smarty.const._MA_TADCAL_GOOGLE_IMPORT}>" align="absmiddle" style="margin-right:4px;"><{$smarty.const._MA_TADCAL_GOOGLE_IMPORT}></a>
          <{/if}>

          </td>
        </tr>
      <{/foreach}>
      <tr>
      <td colspan=10 class="bar">

      <a href="main.php?op=tad_cal_cate_form" class="btn btn-xs btn-info"><img src="../images/date.png" title="<{$smarty.const._TAD_ADD_CAL}>" alt="<{$smarty.const._TAD_ADD_CAL}>" style="margin-right:4px;" align="absmiddle"><{$smarty.const._TAD_ADD_CAL}></a>

      <a href="main.php?op=tad_cal_all_sync"  class="btn btn-xs btn-info"><img src="../images/sync.png" title="<{$smarty.const._MA_TADCAL_ALL_SYNC}>" alt="<{$smarty.const._MA_TADCAL_ALL_SYNC}>" style="margin-right:4px;" align="absmiddle"><{$smarty.const._MA_TADCAL_ALL_SYNC}></a>

      <!--a href="main.php?op=tad_cal_add_gcal_form"  class="btn btn-xs btn-info"><img src="../images/google.png" title="<{$smarty.const._TAD_ADD_GOOGLE}>" alt="<{$smarty.const._TAD_ADD_GOOGLE}>" style="margin-right:4px;" align="absmiddle"><{$smarty.const._TAD_ADD_GOOGLE}></a-->

      <{$bar}>
      </td></tr>
      </tbody>
      </table>
    <{/if}>
  <{/if}>
</div>