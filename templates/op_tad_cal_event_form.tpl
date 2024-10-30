
<{if $mode!="ajax"}>
<h2><{$smarty.const._MD_TADCAL_EVENT_FORM}></h2>
<{/if}>

<script type="text/javascript">
$().ready(function() {
<{$show_week_repeat|default:''}>
<{$show_month_repeat|default:''}>
month_repeat_chk();

$("#freq_select").change(function(){
    var freq_select_val=$("#freq_select").val();
    if(freq_select_val=="DAILY"){
    $("#week_repeat").hide();
    $("#month_repeat").hide();
    $("#interval_unit").text("<{$smarty.const._MD_TADCAL_D}>");
    }else if(freq_select_val=="WEEKLY"){
    $("#week_repeat").show();
    $("#month_repeat").hide();
    $("#interval_unit").text("<{$smarty.const._MD_TADCAL_W}>");
    }else if(freq_select_val=="MONTHLY"){
    $("#week_repeat").hide();
    $("#month_repeat").show();
    $("#interval_unit").text("<{$smarty.const._MD_TADCAL_M}>");
    var theday=$("#start_allday").val();
    }else if(freq_select_val=="YEARLY"){
    $("#week_repeat").hide();
    $("#month_repeat").hide();
    $("#interval_unit").text("<{$smarty.const._MD_TADCAL_Y}>");
    }
});


<{$show_repeat_box|default:''}>
$("#repeat_checkbox").change(function(){
    if($("input#repeat_checkbox:checked").length){
    $("#repeat_box").show();
    }else{
    $("#repeat_box").hide();
    }
});

//切換全日事件時要處理的相關動作
var bymonthday_start=$("#start").val();
$("#bymonthday").text(bymonthday_start.substr(8,2));
<{$show_allday_date|default:''}>
$("#allday").change(function(){
    if($("input#allday:checked").length){
    var bymonthday_start=$("#start_allday").val();
    $("#start_allday").val($("#start").val().substr(0,10));
    $("#end_allday").val($("#end").val().substr(0,10));
    $("#start_allday").show().attr("disabled",false);
    $("#end_allday").show().attr("disabled",false);
    $("#start").hide().attr("disabled","disabled");
    $("#end").hide().attr("disabled","disabled");
    }else{
    var bymonthday_start=$("#start").val();
    var start_time=$("#start").val().substr(10,6);
    if(start_time=="")start_time=" 00:00";
    var end_time=$("#end").val().substr(10,6);
    if(end_time=="")end_time=" 00:00";
    $("#start").val($("#start_allday").val().substr(0,10) + start_time);
    $("#end").val($("#end_allday").val().substr(0,10)+end_time);
    $("#start").show().attr("disabled",false);
    $("#end").show().attr("disabled",false);
    $("#start_allday").hide().attr("disabled","disabled");
    $("#end_allday").hide().attr("disabled","disabled");
    }
    $("#bymonthday").text(bymonthday_start.substr(8,2));
});


});

//檢查結束日期
function check_end(){
var date_long=$("#long").val();
if($("input#allday:checked").length){
    var chk_start=$("#start_allday").val();
    $("#end_allday").val(date("Y-m-d",strtotime(chk_start)*1 + date_long*1));
}else{
    var chk_start=$("#start").val();
    $("#end").val(date("Y-m-d H:i",strtotime(chk_start)*1 + date_long*1));
}
}

//更新月重複選項
function month_repeat_chk(){
var theday=$("#start_allday").val();
$.post("<{$xoops_url}>/modules/tad_cal/get_w.php", { day: theday} , function(data) {
    $("#get_w").text(data);
});
$("#bymonthday").text($("#start_allday").val().substr(8,2));
}

//更新結束日期
function update_long(){
if($("input#allday:checked").length){
    var chk_start=$("#start_allday").val();
    var chk_end=$("#end_allday").val();
}else{
    var chk_start=$("#start").val();
    var chk_end=$("#end").val();
}
$("#long").val(strtotime(chk_end)*1 - strtotime(chk_start)*1);
}
</script>


<form action="<{$xoops_url}>/modules/tad_cal/event.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal" role="form">
    <!--事件標題-->
    <div class="form-group row mb-3">
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MD_TADCAL_TITLE}>
        </label>
        <div class="col-sm-10">
        <input type="text" name="title" title="title" value="<{$title|default:''}>" id="title" class="validate[required] form-control">
        </div>
    </div>

    <!--事件起始時間-->
    <div class="form-group row mb-3">
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MD_TADCAL_TIME}>
        </label>

        <!--開始時間-->
        <div class="col-sm-3">
        <input type="text" name="start" title="start_allday" value="<{$start_allday|default:''}>" id="start_allday" class="validate[required] form-control" onClick="WdatePicker({dateFmt:'yyyy-MM-dd' , startDate:'%y-%M-%d'})" onChange="check_end();month_repeat_chk();">

        <input type="text" name="start" title="start" value="<{$start|default:''}>" id="start" class="validate[required] form-control" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm' , startDate:'%y-%M-%d %H:%m'})" onChange="$('#bymonthday').text($('#start').val().substr(8,2));check_end();">
        </div>

        <!--結束時間-->
        <div class="col-sm-3">
        <input type="text" name="end" title="end_allday" value="<{$end_allday|default:''}>" id="end_allday" onClick="WdatePicker({dateFmt:'yyyy-MM-dd' , startDate:'%y-%M-%d'})" onChange="update_long();" class="form-control">

        <input type="text" name="end" title="end" value="<{$end|default:''}>" id="end" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm' , startDate:'%y-%M-%d %H:%m'})" onChange="update_long();" class="form-control">
        </div>
        <div class="col-sm-4">
        <!--全日事件-->
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="allday" id="allday" value="1" <{$chk_allday_1|default:''}>>
            <label class="form-check-label" for="allday"><{$smarty.const._MD_TADCAL_ALLDAY}></label>
        </div>

        <!--重複事件選項-->
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="repeat" id="repeat_checkbox" value="1" <{$repeat_1|default:''}>>
            <label class="form-check-label" for="repeat_checkbox"><{$smarty.const._MD_TADCAL_RECURRENCE}></label>
        </div>
        </div>
    </div>

    <!--重複事件設定框-->
    <div class="slert slert-success" id="repeat_box">

        <div  class="row">
        <div class="col-sm-7">

            <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-right text-sm-end control-label">
                <{$smarty.const._MD_TADCAL_REPEAT}><{$smarty.const._TAD_FOR}>
            </label>
            <div class="col-sm-8">
                <select name="FREQ" title='FREQ' id="freq_select" class="form-select">
                <option value="DAILY" <{$chk_DAILY|default:''}>><{$smarty.const._MD_TADCAL_REPEAT_DAILY}></option>
                <option value="WEEKLY" <{$chk_WEEKLY|default:''}>><{$smarty.const._MD_TADCAL_REPEAT_WEEKLY}></option>
                <option value="MONTHLY" <{$chk_MONTHLY|default:''}>><{$smarty.const._MD_TADCAL_REPEAT_MONTHLY}></option>
                <option value="YEARLY" <{$chk_YEARLY|default:''}>><{$smarty.const._MD_TADCAL_REPEAT_YEARLY}></option>
                </select>
            </div>
            </div>

            <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-right text-sm-end control-label">
                <{$smarty.const._MD_TADCAL_INTERVAL}><{$smarty.const._TAD_FOR}>
            </label>
            <div class="col-sm-7">
                <select name="INTERVAL" title='INTERVAL' class="form-select">
                <{$INTERVAL_OPT|default:''}>
                </select>
            </div>
            <div class="col-sm-1">
                <span id="interval_unit"><{$repeat_unit|default:''}></span>
            </div>
            </div>


            <div id="week_repeat" class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-right text-sm-end control-label">
                <{$smarty.const._MD_TADCAL_WEEK_REPEAT}><{$smarty.const._TAD_FOR}>
            </label>
            <div class="col-sm-9">
                <{$week_repeat_col|default:''}>
            </div>
            </div>


            <div id="month_repeat" class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-right text-sm-end control-label">
                <{$smarty.const._MD_TADCAL_MONTH_REPEAT}><{$smarty.const._TAD_FOR}>
            </label>
            <div class="col-sm-9">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="month_repeat" id="BYMONTHDAY" value="BYMONTHDAY" <{$RRULE_BYDAY|default:''}>>
                    <label class="form-check-label" for="BYMONTHDAY"><{$bymonthday|default:''}></label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="month_repeat" id="BYDAY" value="BYDAY">
                    <label class="form-check-label" for="BYDAY"><span id="get_w"></span></label>
                </div>
            </div>
            </div>
        </div>

        <div class="col-sm-5">


            <div class="form-group row mb-3">
            <div class="col-sm-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="END" id="none" value="none" <{$ENDType_none|default:''}>>
                    <label class="form-check-label" for="none"><{$smarty.const._MD_TADCAL_NEVER_END}></label>
                </div>
            </div>
            </div>

            <div class="form-group row mb-3">
            <div class="col-sm-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="END" id="count" value="count" <{$ENDType_count|default:''}>>
                    <label class="form-check-label" for="count"><{$smarty.const._MD_TADCAL_END_COUNT1}></label>
                </div>
            </div>
            <div class="col-sm-5">
                <input type="text" name="COUNT" title="COUNT" class="form-control" value="<{$RRULE_COUNT|default:''}>" onChange="$('#count').attr('checked','checked'); if(this.value=="")this.value=10;">
            </div>
            <div class="col-sm-4">
                <{$smarty.const._MD_TADCAL_END_COUNT2}>
            </div>
            </div>


            <div class="form-group row mb-3">
            <div class="col-sm-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="END" id="until" value="until" <{$ENDType_until|default:''}>>
                    <label class="form-check-label" for="until"><{$smarty.const._MD_TADCAL_END_UNTIL1}></label>
                </div>
            </div>
            <div class="col-sm-5">
                <input type="text" name="UNTIL" title="UNTIL" class="form-control"  value="<{$UNTIL|default:''}>" onClick="$('#until').attr('checked' , 'checked');WdatePicker({dateFmt:'yyyy-MM-dd' , startDate:'%y-%M-%d'})">
            </div>
            <div class="col-sm-4">
                <{$smarty.const._MD_TADCAL_END_UNTIL2}>
            </div>
            </div>

        </div>
        </div>
    </div>


    <div class="form-group row mb-3">
        <!--所屬行事曆-->
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$of_cate_title|default:''}>
        </label>
        <div class="col-sm-4">
        <{$cate_col|default:''}>
        </div>
        <!--事件地點-->
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MD_TADCAL_LOCATION}>
        </label>
        <div class="col-sm-4">
        <input type="text" name="location" title="location" class="form-control" value="<{$location|default:''}>" id="location" >
        </div>
    </div>


    <!--事件內容-->
    <div class="form-group row mb-3">
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MD_TADCAL_DETAILS}>
        </label>
        <div class="col-sm-10">
        <textarea name="details" class="form-control" rows=4 id="details"><{$details|default:''}></textarea>
        </div>
    </div>

    <div class="form-check-inline checkbox-inline">
        <label class="form-check-label">
            <input class="form-check-input" type="checkbox" name="tag" value="todo" <{if $tag=="todo"}>checked<{/if}>>
            <{$smarty.const._MD_TADCAL_TODO_LIST}>
        </label>
    </div>


    <div class="text-center">
        <input type="hidden" id="long" value="<{$long|default:''}>">
        <!--事件etag-->
        <input type="hidden" name="etag" value="<{$etag|default:''}>">

        <!--事件id-->
        <input type="hidden" name="id" value="<{$id|default:''}>">

        <!--事件編號-->
        <input type="hidden" name="sn" value="<{$sn|default:''}>">

        <!--事件排序-->
        <input type="hidden" name="sequence" size="2" value="<{$sequence|default:''}>" id="sequence">
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i>  <{$smarty.const._TAD_SAVE}></button>
    </div>
</form>