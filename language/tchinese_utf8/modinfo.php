<?php
xoops_loadLanguage('modinfo_common', 'tadtools');

define('_MI_TADCAL_NAME', '行事曆');
define('_MI_TADCAL_AUTHOR', '行事曆');
define('_MI_TADCAL_CREDITS', 'tad');
define('_MI_TADCAL_DESC', '可結合Google行事曆的XOOPS行事曆模組');
define('_MI_TADCAL_GOOGLE_CLIENT_ID', 'Google OAuth 用戶端 ID');
define('_MI_TADCAL_GOOGLE_CLIENT_ID_DESC', '從 Google Cloud Console 建立 OAuth 用戶端 ID，並設定 tad_cal/google_sync.php 為授權重新導向 URI。');
define('_MI_TADCAL_GOOGLE_CLIENT_SECRET', 'Google OAuth 用戶端密鑰');
define('_MI_TADCAL_GOOGLE_CLIENT_SECRET_DESC', 'Google Cloud Console 提供的 OAuth 用戶端密鑰。請勿放入程式碼或公開版本控制。');
define('_MI_TADCAL_ADMENU1', '行事曆管理');
define('_MI_TADCAL_BNAME1', '小行事曆');
define('_MI_TADCAL_BDESC1', '小行事曆(tad_cal_calendar)');
define('_MI_TADCAL_BNAME2', '近期事項');
define('_MI_TADCAL_BDESC2', '近期事項(tad_cal_list)');
define('_MI_TADCAL_BNAME3', '大行事曆');
define('_MI_TADCAL_BDESC3', '大行事曆(tad_cal_full_calendar)');

define('_MI_TADCAL_EVENTSHOWMODE', '事件顯示模式');
define('_MI_TADCAL_EVENTSHOWMODE_DESC', '設定行事曆的事件什麼時後下要顯示');
define('_MI_TADCAL_CONF0_OPT1', '點選事件時');
define('_MI_TADCAL_CONF0_OPT2', '滑鼠移過時自動顯示事件');
define('_MI_TADCAL_EVENTTHEME', '事件視窗佈景');
define('_MI_TADCAL_EVENTTHEME_DESC', '設定事件呈現的視窗外觀');
define('_MI_TADCAL_CONF1_OPT1', '太陽黃');
define('_MI_TADCAL_CONF1_OPT2', '破曉白');
define('_MI_TADCAL_CONF1_OPT3', '夜半黑');
define('_MI_TADCAL_CONF1_OPT4', '夕陽紅');
define('_MI_TADCAL_CONF1_OPT5', '天空藍');
define('_MI_TADCAL_CONF1_OPT6', '青草綠');
define('_MI_TADCAL_TITLE_NUM', '事件標題字數');
define('_MI_TADCAL_TITLE_NUM_DESC', '呈現在行事曆中的標題字數上限');
define('_MI_TADCAL_QUICK_ADD', '是否開啟快速新增事件功能？');
define('_MI_TADCAL_QUICK_ADD_DESC', '若選「是」，則點選行事曆日期時，會出現新增事件選單。');

define('_MI_CAL_START', '行事曆起始星期');
define('_MI_CAL_START_DESC', '選擇以星前日或是星期一當作每週的第一天');
define('_MI_TADCAL_SU', '星期日');
define('_MI_TADCAL_MO', '星期一');

define('_MI_TADCAL_DIRNAME', basename(dirname(dirname(__DIR__))));
define('_MI_TADCAL_HELP_HEADER', __DIR__ . '/help/helpheader.tpl');
define('_MI_TADCAL_BACK_2_ADMIN', '管理');

//help
define('_MI_TADCAL_HELP_OVERVIEW', '概要');
