<h2 class="sr-only visually-hidden">Calendar word</h2>

<form action="word.php" method="post" id="myForm" class="form-horizontal" role="form">

  <div class="form-group row mb-3">
    <!--事件起始時間-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
      <{$smarty.const._MD_TADCAL_TIME}>
    </label>
    <!--開始時間-->
    <div class="col-sm-2">
      <input type="text" name="start" title="start" value="<{$start|default:''}>" id="start_allday" class="validate[required] form-control" onClick="WdatePicker({dateFmt:'yyyy-MM-dd' , startDate:'%y-%M-%d'})">
    </div>
    <!--結束時間-->
    <div class="col-sm-2">
      <input type="text" name="end" title="end" value="<{$end|default:''}>" id="end_allday" onClick="WdatePicker({dateFmt:'yyyy-MM-dd' , startDate:'%y-%M-%d'})"  class="form-control">
    </div>
    <!--列出類型-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
      <{$smarty.const._MD_TADCAL_DL_TYPE}>
    </label>
    <div class="col-sm-4">
      <label class="radio">
        <input type="radio" name="dl_type" value="only_event" id="only_event" checked><{$smarty.const._MD_TADCAL_ONLY_EVENT}>
      </label>
      <label class="radio">
        <input type="radio" name="dl_type" value="all_date" id="all_date"><{$smarty.const._MD_TADCAL_ALL_DATE}>
      </label>
      <label class="radio">
        <input type="radio" name="dl_type" value="all_week" id="all_week"><{$smarty.const._MD_TADCAL_ALL_WEEK}>
      </label>
    </div>
  </div>

  <div class="form-group row mb-3">
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
      <{$smarty.const._MD_TADCAL_CATE_SN}>
    </label>
    <div class="col-sm-4">
      <select name='cate_sn[]' title='cate_sn' id='cate_sn' size=5 class="form-control" multiple>
        <{$get_tad_cal_cate_menu_options|default:''}>
      </select>
    </div>
    <!--列出類型-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
      <{$smarty.const._MD_TADCAL_SHOW_TYPE}>
    </label>
    <div class="col-sm-4">
      <label class="radio">
        <input type="radio" name="show_type" value="separate" id="separate" ><{$smarty.const._MD_TADCAL_SEPARATE}>
      </label>
      <label class="radio">
        <input type="radio" name="show_type" value="merge" id="merge" checked><{$smarty.const._MD_TADCAL_MERGE}>
      </label>

      <div>
        <input type="hidden" name="op" value="download_now">
        <button type="submit" class="btn btn-primary"><{$smarty.const._MD_TADCAL_SMNAME3}></button>
      </div>
    </div>
  </div>
</form>
