<h1><{$smarty.const._MA_TADCAL_CATE_FORM}></h1>

<div class="tad-cal-cate-form">
    <form action="main.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal" role="form">


        <div class="card tad-cal-cate-form__card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="cate_title"><{$smarty.const._MA_TADCAL_CATE_TITLE}></label>
                        <input type="text" name="cate_title" class="form-control validate[required]" value="<{$cate_title|default:''|escape:'html'}>" id="cate_title" required>
                    </div>

                    <fieldset class="col-md-1">
                        <legend class="form-label fw-semibold"><{$smarty.const._MA_TADCAL_CATE_ENABLE}></legend>
                        <div class="tad-cal-cate-form__options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cate_enable" id="cate_enable1" value="1" <{$cate_enable1|default:''}>>
                                <label class="form-check-label" for="cate_enable1"><{$smarty.const._YES}></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cate_enable" id="cate_enable0" value="0" <{$cate_enable0|default:''}>>
                                <label class="form-check-label" for="cate_enable0"><{$smarty.const._NO}></label>
                            </div>
                        </div>
                    </fieldset>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cate_bgcolor"><{$smarty.const._MA_TADCAL_CATE_BGCOLOR}></label>
                        <div class="input-group">
                            <input type="text" name="cate_bgcolor" title="cate_bgcolor" id="cate_bgcolor" class="form-control color-picker" value="<{$cate_bgcolor|default:''|escape:'html'}>" data-hex="true">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cate_color"><{$smarty.const._MA_TADCAL_CATE_COLOR}></label>
                        <div class="input-group">
                            <input type="text" name="cate_color" title="cate_color" id="cate_color" class="form-control color-picker" value="<{$cate_color|default:''|escape:'html'}>" data-hex="true">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="enable_group"><{$smarty.const._MA_TADCAL_ENABLE_GROUP}></label>
                        <{$enable_group|default:''}>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="enable_upload_group"><{$smarty.const._MA_TADCAL_ENABLE_UPLOAD_GROUP}></label>
                        <{$enable_upload_group|default:''}>
                    </div>
                </div>
            </div>
        </div>

        <div class="card tad-cal-cate-form__card mb-3">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="fa fa-calendar-days me-2" aria-hidden="true"></i><{$smarty.const._MA_TADCAL_GOOGLE_SYNC}></h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="google_calendar_id"><{$smarty.const._MA_TADCAL_GOOGLE_CALENDAR_ID}></label>
                        <input type="text" name="google_calendar_id" id="google_calendar_id" title="google_calendar_id" class="form-control" value="<{$google_calendar_id|default:'primary'|escape:'html'}>">
                        <div class="form-text"><{$smarty.const._MA_TADCAL_GOOGLE_CALENDAR_ID_DESC}></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="google_initial_sync"><{$smarty.const._MA_TADCAL_GOOGLE_INITIAL_SYNC}></label>
                        <select name="google_initial_sync" id="google_initial_sync" title="google_initial_sync" class="form-select">
                            <option value="keep_local_only" <{if $google_initial_sync|default:'keep_local_only' eq 'keep_local_only'}>selected<{/if}>><{$smarty.const._MA_TADCAL_GOOGLE_KEEP_LOCAL}></option>
                            <option value="replace_with_google" <{if $google_initial_sync|default:'keep_local_only' eq 'replace_with_google'}>selected<{/if}>><{$smarty.const._MA_TADCAL_GOOGLE_REPLACE_LOCAL}></option>
                            <option value="merge_and_upload" <{if $google_initial_sync|default:'keep_local_only' eq 'merge_and_upload'}>selected<{/if}>><{$smarty.const._MA_TADCAL_GOOGLE_MERGE}></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="tad-cal-cate-form__actions">
            <input type="hidden" name="cate_sort" value="<{$cate_sort|default:''}>" id="cate_sort">
            <input type="hidden" name="cate_sn" value="<{$cate_sn|default:''}>">
            <input type="hidden" name="op" value="<{$next_op|default:''}>">
            <button type="submit" class="btn btn-primary px-4"><i class="fa fa-floppy-disk me-1" aria-hidden="true"></i><{$smarty.const._TAD_SAVE}></button>
        </div>
    </form>
</div>

<{if $cate_sn|default:false}>
    <div class="card tad-cal-cate-form__card tad-cal-cate-form__sync-card mt-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-2"><i class="fa fa-cloud-arrow-up me-2" aria-hidden="true"></i><{$smarty.const._MA_TADCAL_GOOGLE_SYNC}></h2>
                    <p class="mb-1">
                        <span class="text-muted"><{$smarty.const._MA_TADCAL_GOOGLE_STATUS}>：</span>
                        <{if $google_connected|default:false}>
                            <span class="badge bg-success"><{$smarty.const._MA_TADCAL_GOOGLE_CONNECTED}></span>
                        <{else}>
                            <span class="badge bg-secondary"><{$smarty.const._MA_TADCAL_GOOGLE_DISCONNECTED}></span>
                        <{/if}>
                    </p>
                    <{if $google_last_sync|default:false}>
                        <small class="text-muted"><{$smarty.const._MA_TADCAL_LAST_UPDATE}>：<{$google_last_sync|escape:'html'}></small>
                    <{/if}>
                </div>
                <div class="tad-cal-cate-form__sync-actions">
                    <a class="btn btn-primary d-inline-block" href="<{$xoops_url}>/modules/tad_cal/google_sync.php?op=connect&amp;cate_sn=<{$cate_sn}>"><{$smarty.const._MA_TADCAL_GOOGLE_CONNECT}></a>
                    <{if $google_connected|default:false}>
                        <form action="<{$xoops_url}>/modules/tad_cal/google_sync.php" method="post" class="d-inline-block">
                            <input type="hidden" name="op" value="sync">
                            <input type="hidden" name="cate_sn" value="<{$cate_sn}>">
                            <input type="hidden" name="csrf" value="<{$google_admin_token}>">
                            <{if $google_confirm_replace|default:false}>
                                <label class="text-danger small">
                                    <input type="checkbox" name="confirm_replace" value="1" required>
                                    <{$smarty.const._MA_TADCAL_GOOGLE_CONFIRM_REPLACE}>
                                </label>
                            <{/if}>
                            <button type="submit" class="btn btn-success"><{$smarty.const._MA_TADCAL_GOOGLE_SYNC_NOW}></button>
                        </form>
                        <form action="<{$xoops_url}>/modules/tad_cal/google_sync.php" method="post" class="d-inline-block">
                            <input type="hidden" name="op" value="disconnect">
                            <input type="hidden" name="cate_sn" value="<{$cate_sn}>">
                            <input type="hidden" name="csrf" value="<{$google_admin_token}>">
                            <button type="submit" class="btn btn-outline-danger"><{$smarty.const._MA_TADCAL_GOOGLE_DISCONNECT}></button>
                        </form>
                    <{/if}>
                </div>
            </div>
        </div>
    </div>
<{/if}>
