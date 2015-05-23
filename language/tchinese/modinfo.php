<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2008-03-25
// $Id:$
// ------------------------------------------------------------------------- //

define("_MI_TADCBOX_NAME","即時留言簿");
define("_MI_TADCBOX_AUTHOR","tad");
define("_MI_TADCBOX_CREDITS","TNC");
define("_MI_TADCBOX_DESC","此模組提供一個小巧的即時留言區塊");
define("_MI_TADCBOX_ADMENU1", "留言管理");
define("_MI_TADCBOX_TEMPLATE_DESC1", "tadcbox_index_tpl.html的樣板檔。");
define("_MI_TADCBOX_BNAME1","即時留言簿");
define("_MI_TADCBOX_BDESC1","會產生一個即時留言簿區塊");


define("_MI_TADCBOX_NEED_LOGIN","<b>登入始能留言</b>");
define("_MI_TADCBOX_NEED_LOGIN_DESC","強制只有註冊登入才能留言，訪客無法留言。");
define("_MI_TADCBOX_AUTO_ID","<b>自動帳號辨識</b>");
define("_MI_TADCBOX_AUTO_ID_DESC","登入者會強制以其帳號為發言ID，未登入者會強制在帳號前加入「訪客」的字串");
define("_MI_TADCBOX_INPUT_MIN","<b>每則留言字數下限</b>");
define("_MI_TADCBOX_INPUT_MIN_DESC","至少要寫多少字？例如：至少要寫 5 個中文字，則填入5。");
define("_MI_TADCBOX_INPUT_MAX","<b>每則留言字數上限</b>");
define("_MI_TADCBOX_INPUT_MAX_DESC","最多不能超過多少字？例如：最多只能寫 100 個中文字，則填入100。");
define("_MI_TADCBOX_ALLOW_HTML","<b>使否允許使用HTML語法？</b>");
define("_MI_TADCBOX_ALLOW_HTML_DESC","開啟的話，風險較大...");
define("_MI_TADCBOX_SECURITY_IMAGES","<b>是否使用圖形認證？</b>");
define("_MI_TADCBOX_SECURITY_IMAGES_DESC","留言時，是否需要使用圖形認證，以防止一些留言機器人來濫發垃圾留言。");
define("_MI_TADCBOX_NO_NEED_CHK","<b>哪些群組不需要認證？</b>");
define("_MI_TADCBOX_NO_NEED_CHK_DESC","哪些群組不需要使用圖形認證就可以發言？");
define("_MI_TADCBOX_COL1_COLOR","<b>色系1 的文字顏色</b>");
define("_MI_TADCBOX_COL1_COLOR_DESC","第1 種色系的留言文字顏色，請用 #000000 或 rgb(0,0,0) 等CSS顏色格式");
define("_MI_TADCBOX_COL1_BGCOLOR","<b>色系1 的背景顏色</b>");
define("_MI_TADCBOX_COL1_BGCOLOR_DESC","第1 種色系的留言背景顏色，請用 #000000 或 rgb(0,0,0) 等CSS顏色格式");
define("_MI_TADCBOX_COL2_COLOR","<b>色系2 的文字顏色</b>");
define("_MI_TADCBOX_COL2_COLOR_DESC","第2 種色系的留言文字顏色，請用 #000000 或 rgb(0,0,0) 等CSS顏色格式");
define("_MI_TADCBOX_COL2_BGCOLOR","<b>色系2 的背景顏色</b>");
define("_MI_TADCBOX_COL2_BGCOLOR_DESC","第2 種色系的留言背景顏色，請用 #000000 或 rgb(0,0,0) 等CSS顏色格式");
define("_MI_TADCBOX_WORDWRAP","<b>是否啟動強制換行功能？</b>");
define("_MI_TADCBOX_WORDWRAP_DESC","此功能將可以有效對付過長的英文連續字串，但在某些中英文夾雜的情況下可能會有切字不正確的情況產生。");

define("_MI_TADCBOX_OPT1","歡迎使用即時留言板！\n期待您的留言喔！");


/** v 1.6 **/
define("_MI_TADCBOX_SMILE_NUM","<b>表情圖數量？</b>");
define("_MI_TADCBOX_SMILE_NUM_DESC","設定表情圖一次出現數量");
?>
