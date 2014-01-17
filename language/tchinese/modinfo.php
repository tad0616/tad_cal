<?php
include_once XOOPS_ROOT_PATH."/modules/tadtools/language/{$xoopsConfig['language']}/modinfo_common.php";

define("_MI_TADCAL_NAME","行事曆");
define("_MI_TADCAL_AUTHOR","行事曆");
define("_MI_TADCAL_CREDITS","tad");
define("_MI_TADCAL_DESC","可結合Google行事曆的XOOPS行事曆模組");
define("_MI_TADCAL_ADMENU1", "行事曆管理");
define("_MI_TADCAL_BNAME1","小行事曆");
define("_MI_TADCAL_BDESC1","小行事曆(tad_cal_calendar)");
define("_MI_TADCAL_BNAME2","近期事項");
define("_MI_TADCAL_BDESC2","近期事項(tad_cal_list)");

define("_MI_TADCAL_EVENTSHOWMODE","事件顯示模式");
define("_MI_TADCAL_EVENTSHOWMODE_DESC","設定行事曆的事件什麼時後下要顯示");
define("_MI_TADCAL_CONF0_OPT1","點選事件時");
define("_MI_TADCAL_CONF0_OPT2","滑鼠移過時自動顯示事件");
define("_MI_TADCAL_EVENTTHEME","事件視窗佈景");
define("_MI_TADCAL_EVENTTHEME_DESC","設定事件呈現的視窗外觀");
define("_MI_TADCAL_CONF1_OPT1","太陽黃");
define("_MI_TADCAL_CONF1_OPT2","破曉白");
define("_MI_TADCAL_CONF1_OPT3","夜半黑");
define("_MI_TADCAL_CONF1_OPT4","夕陽紅");
define("_MI_TADCAL_CONF1_OPT5","天空藍");
define("_MI_TADCAL_CONF1_OPT6","青草綠");
define("_MI_TADCAL_TITLE_NUM","事件標題字數");
define("_MI_TADCAL_TITLE_NUM_DESC","呈現在行事曆中的標題字數上限");
define("_MI_TADCAL_QUICK_ADD","是否開啟快速新增事件功能？");
define("_MI_TADCAL_QUICK_ADD_DESC","若選「是」，則點選行事曆日期時，會出現新增事件選單。");
define("_MI_TADCAL_SYNC_CONUT","行事曆每被執行幾次就自動同步遠端行事曆");
define("_MI_TADCAL_SYNC_CONUT_DESC","為取代麻煩的排程功能，內建一個計數器，計算行事曆首頁被執行的次數，每到一定次數就自動執行遠端行事曆同步。（設0表示不啟用此功能）<br>數字越小，越常同步。同步時，會需要幾秒鐘時間。");
?>