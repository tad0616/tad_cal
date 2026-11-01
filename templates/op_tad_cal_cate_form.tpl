<h1><{$smarty.const._MA_TADCAL_CATE_FORM}></h1>
<form action="main.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal" role="form">
    <div class="form-group row mb-3">
    <!--行事曆標題-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MA_TADCAL_CATE_TITLE}>
    </label>
    <div class="col-sm-4">
        <input type="text" title="cate_title" name="cate_title" class="form-control" value="<{$cate_title|default:''}>" id="cate_title" class="validate[required]">
    </div>
    <!--是否啟用-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MA_TADCAL_CATE_ENABLE}>
    </label>
    <div class="col-sm-4">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="cate_enable" id="cate_enable1" value="1" <{$cate_enable1|default:''}>>
            <label class="form-check-label" for="cate_enable1"><{$smarty.const._YES}></label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="cate_enable" id="cate_enable1" value="0" <{$cate_enable0|default:''}>>
            <label class="form-check-label" for="cate_enable0"><{$smarty.const._NO}></label>
        </div>
    </div>
    </div>

    <div class="form-group row mb-3">
    <!--背景色-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MA_TADCAL_CATE_BGCOLOR}>
    </label>
    <div class="col-sm-4">
        <div class="input-group">
            <input type="text" name="cate_bgcolor" title="cate_bgcolor" id="cate_bgcolor" class="form-control color-picker" value="<{$cate_bgcolor|default:''}>" data-hex="true">
        </div>
    </div>
    <!--文字色-->
    <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
        <{$smarty.const._MA_TADCAL_CATE_COLOR}>
    </label>
    <div class="col-sm-4">
        <div class="input-group">
            <input type="text" name="cate_color" title="cate_color" id="cate_color" class="form-control color-picker" value="<{$cate_color|default:''}>" data-hex="true">
        </div>
    </div>
    </div>



    <div class="form-group row mb-3">
        <!--可讀群組-->
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
            <{$smarty.const._MA_TADCAL_ENABLE_GROUP}>
        </label>
        <div class="col-sm-4">
            <{$enable_group|default:''}>
        </div>
        <!--可寫群組-->
        <label class="col-sm-2 col-form-label text-sm-right text-sm-end control-label">
            <{$smarty.const._MA_TADCAL_ENABLE_UPLOAD_GROUP}>
        </label>
        <div class="col-sm-4">
            <{$enable_upload_group|default:''}>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label class="col-sm-2 col-form-label"><{$smarty.const._MA_TADCAL_GOOGLE_CALENDAR_ID}></label>
        <div class="col-sm-4">
            <input type="text" name="google_calendar_id" title="google_calendar_id" class="form-control" value="<{$google_calendar_id|default:'primary'}>">
            <small class="form-text text-muted"><{$smarty.const._MA_TADCAL_GOOGLE_CALENDAR_ID_DESC}></small>
        </div>
        <label class="col-sm-2 col-form-label"><{$smarty.const._MA_TADCAL_GOOGLE_INITIAL_SYNC}></label>
        <div class="col-sm-4">
            <select name="google_initial_sync" title="google_initial_sync" class="form-control form-select">
                <option value="keep_local_only" <{if $google_initial_sync|default:'keep_local_only' eq 'keep_local_only'}>selected<{/if}>><{$smarty.const._MA_TADCAL_GOOGLE_KEEP_LOCAL}></option>
                <option value="replace_with_google" <{if $google_initial_sync|default:'keep_local_only' eq 'replace_with_google'}>selected<{/if}>><{$smarty.const._MA_TADCAL_GOOGLE_REPLACE_LOCAL}></option>
                <option value="merge_and_upload" <{if $google_initial_sync|default:'keep_local_only' eq 'merge_and_upload'}>selected<{/if}>><{$smarty.const._MA_TADCAL_GOOGLE_MERGE}></option>
            </select>
        </div>
    </div>

    <div class="text-center">
        <!--行事曆排序-->
        <input type="hidden" name="cate_sort" value="<{$cate_sort|default:''}>" id="cate_sort">
        <!--行事曆編號-->
        <input type="hidden" name="cate_sn" value="<{$cate_sn|default:''}>">
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i>  <{$smarty.const._TAD_SAVE}></button>
    </div>
</form>

<{if $cate_sn|default:false}>
    <div class="card mt-3">
        <div class="card-body">
            <h4><{$smarty.const._MA_TADCAL_GOOGLE_SYNC}></h4>
            <p><{$smarty.const._MA_TADCAL_GOOGLE_STATUS}>：
                <{if $google_connected|default:false}><{$smarty.const._MA_TADCAL_GOOGLE_CONNECTED}><{else}><{$smarty.const._MA_TADCAL_GOOGLE_DISCONNECTED}><{/if}>
                <{if $google_last_sync|default:false}>（<{$smarty.const._MA_TADCAL_LAST_UPDATE}>：<{$google_last_sync}>）<{/if}>
            </p>
            <a class="btn btn-primary" href="<{$xoops_url}>/modules/tad_cal/google_sync.php?op=connect&amp;cate_sn=<{$cate_sn}>"><{$smarty.const._MA_TADCAL_GOOGLE_CONNECT}></a>
            <{if $google_connected|default:false}>
                <form action="<{$xoops_url}>/modules/tad_cal/google_sync.php" method="post" class="d-inline">
                    <input type="hidden" name="op" value="sync">
                    <input type="hidden" name="cate_sn" value="<{$cate_sn}>">
                    <input type="hidden" name="csrf" value="<{$google_admin_token}>">
                    <{if $google_confirm_replace|default:false}>
                        <label class="text-danger">
                            <input type="checkbox" name="confirm_replace" value="1" required>
                            <{$smarty.const._MA_TADCAL_GOOGLE_CONFIRM_REPLACE}>
                        </label>
                    <{/if}>
                    <button type="submit" class="btn btn-success"><{$smarty.const._MA_TADCAL_GOOGLE_SYNC_NOW}></button>
                </form>
                <form action="<{$xoops_url}>/modules/tad_cal/google_sync.php" method="post" class="d-inline">
                    <input type="hidden" name="op" value="disconnect">
                    <input type="hidden" name="cate_sn" value="<{$cate_sn}>">
                    <input type="hidden" name="csrf" value="<{$google_admin_token}>">
                    <button type="submit" class="btn btn-outline-danger"><{$smarty.const._MA_TADCAL_GOOGLE_DISCONNECT}></button>
                </form>
            <{/if}>
        </div>
    </div>
<{/if}>