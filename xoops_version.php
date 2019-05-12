<?php
$modversion = [];

global $xoopsConfig;

//---模組基本資訊---//
$modversion['name'] = _MI_TADCAL_NAME;
$modversion['version'] = 2.89;
$modversion['description'] = _MI_TADCAL_DESC;
$modversion['author'] = _MI_TADCAL_AUTHOR;
$modversion['credits'] = _MI_TADCAL_CREDITS;
$modversion['help'] = 'page=help';
$modversion['license'] = 'GNU GPL 2.0';
$modversion['license_url'] = 'www.gnu.org/licenses/gpl-2.0.html/';
$modversion['image'] = "images/logo_{$xoopsConfig['language']}.png";
$modversion['dirname'] = basename(__DIR__);

//---模組狀態資訊---//
$modversion['release_date'] = '2019/05/10';
$modversion['module_website_url'] = 'https://tad0616.net/';
$modversion['module_website_name'] = _MI_TAD_WEB;
$modversion['module_status'] = 'release';
$modversion['author_website_url'] = 'https://tad0616.net/';
$modversion['author_website_name'] = _MI_TAD_WEB;
$modversion['min_php'] = 5.4;
$modversion['min_xoops'] = '2.5';

//---paypal資訊---//
$modversion['paypal'] = [];
$modversion['paypal']['business'] = 'tad0616@gmail.com';
$modversion['paypal']['item_name'] = 'Donation : ' . _MI_TAD_WEB;
$modversion['paypal']['amount'] = 0;
$modversion['paypal']['currency_code'] = 'USD';

//---啟動後台管理界面選單---//
$modversion['system_menu'] = 1; //---資料表架構---//
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables'][1] = 'tad_cal_cate';
$modversion['tables'][2] = 'tad_cal_event';
$modversion['tables'][3] = 'tad_cal_repeat';

//---安裝設定---//
$modversion['onInstall'] = 'include/onInstall.php';
$modversion['onUpdate'] = 'include/onUpdate.php';
$modversion['onUninstall'] = 'include/onUninstall.php';

//---管理介面設定---//
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

//---使用者主選單設定---//
$modversion['hasMain'] = 1;

//---樣板設定---//
$i = 1;
$modversion['templates'][$i]['file'] = 'tad_cal_index.tpl';
$modversion['templates'][$i]['description'] = 'tad_cal_index.tpl';

$i++;
$modversion['templates'][$i]['file'] = 'tad_cal_event.tpl';
$modversion['templates'][$i]['description'] = 'tad_cal_event.tpl';

$i++;
$modversion['templates'][$i]['file'] = 'tad_cal_adm_main.tpl';
$modversion['templates'][$i]['description'] = 'tad_cal_adm_main.tpl';

$i++;
$modversion['templates'][$i]['file'] = 'tad_cal_download.tpl';
$modversion['templates'][$i]['description'] = 'tad_cal_download.tpl';

//---區塊設定---//
$modversion['blocks'][1]['file'] = 'tad_cal_calendar.php';
$modversion['blocks'][1]['name'] = _MI_TADCAL_BNAME1;
$modversion['blocks'][1]['description'] = _MI_TADCAL_BDESC1;
$modversion['blocks'][1]['show_func'] = 'tad_cal_calendar';
$modversion['blocks'][1]['template'] = 'tad_cal_calendar.tpl';

$modversion['blocks'][2]['file'] = 'tad_cal_list.php';
$modversion['blocks'][2]['name'] = _MI_TADCAL_BNAME2;
$modversion['blocks'][2]['description'] = _MI_TADCAL_BDESC2;
$modversion['blocks'][2]['show_func'] = 'tad_cal_list';
$modversion['blocks'][2]['template'] = 'tad_cal_list.tpl';
$modversion['blocks'][2]['edit_func'] = 'tad_cal_list_edit';
$modversion['blocks'][2]['options'] = '7';

$modversion['blocks'][3]['file'] = 'tad_cal_full_calendar.php';
$modversion['blocks'][3]['name'] = _MI_TADCAL_BNAME3;
$modversion['blocks'][3]['description'] = _MI_TADCAL_BDESC3;
$modversion['blocks'][3]['show_func'] = 'tad_cal_full_calendar';
$modversion['blocks'][3]['template'] = 'tad_cal_full_calendar.tpl';

$modversion['config'][0]['name'] = 'eventShowMode';
$modversion['config'][0]['title'] = '_MI_TADCAL_EVENTSHOWMODE';
$modversion['config'][0]['description'] = '_MI_TADCAL_EVENTSHOWMODE_DESC';
$modversion['config'][0]['formtype'] = 'select';
$modversion['config'][0]['valuetype'] = 'text';
$modversion['config'][0]['default'] = 'eventClick';
$modversion['config'][0]['options'] = [_MI_TADCAL_CONF0_OPT1 => 'eventClick', _MI_TADCAL_CONF0_OPT2 => 'eventMouseover'];

$modversion['config'][1]['name'] = 'eventTheme';
$modversion['config'][1]['title'] = '_MI_TADCAL_EVENTTHEME';
$modversion['config'][1]['description'] = '_MI_TADCAL_EVENTTHEME_DESC';
$modversion['config'][1]['formtype'] = 'select';
$modversion['config'][1]['valuetype'] = 'text';
$modversion['config'][1]['default'] = 'ui-tooltip-blue';
$modversion['config'][1]['options'] = [_MI_TADCAL_CONF1_OPT1 => 'ui-tooltip', _MI_TADCAL_CONF1_OPT2 => 'ui-tooltip-light', _MI_TADCAL_CONF1_OPT3 => 'ui-tooltip-dark', _MI_TADCAL_CONF1_OPT4 => 'ui-tooltip-red', _MI_TADCAL_CONF1_OPT5 => 'ui-tooltip-blue', _MI_TADCAL_CONF1_OPT6 => 'ui-tooltip-green'];

$modversion['config'][2]['name'] = 'title_num';
$modversion['config'][2]['title'] = '_MI_TADCAL_TITLE_NUM';
$modversion['config'][2]['description'] = '_MI_TADCAL_TITLE_NUM_DESC';
$modversion['config'][2]['formtype'] = 'textbox';
$modversion['config'][2]['valuetype'] = 'int';
$modversion['config'][2]['default'] = '7';

$modversion['config'][3]['name'] = 'quick_add';
$modversion['config'][3]['title'] = '_MI_TADCAL_QUICK_ADD';
$modversion['config'][3]['description'] = '_MI_TADCAL_QUICK_ADD_DESC';
$modversion['config'][3]['formtype'] = 'yesno';
$modversion['config'][3]['valuetype'] = 'int';
$modversion['config'][3]['default'] = '1';

$modversion['config'][4]['name'] = 'sync_conut';
$modversion['config'][4]['title'] = '_MI_TADCAL_SYNC_CONUT';
$modversion['config'][4]['description'] = '_MI_TADCAL_SYNC_CONUT_DESC';
$modversion['config'][4]['formtype'] = 'textbox';
$modversion['config'][4]['valuetype'] = 'int';
$modversion['config'][4]['default'] = '100';

$modversion['config'][5]['name'] = 'facebook_comments_width';
$modversion['config'][5]['title'] = '_MI_FBCOMMENT_TITLE';
$modversion['config'][5]['description'] = '_MI_FBCOMMENT_TITLE_DESC';
$modversion['config'][5]['formtype'] = 'yesno';
$modversion['config'][5]['valuetype'] = 'int';
$modversion['config'][5]['default'] = '1';

$modversion['config'][6]['name'] = 'use_social_tools';
$modversion['config'][6]['title'] = '_MI_SOCIALTOOLS_TITLE';
$modversion['config'][6]['description'] = '_MI_SOCIALTOOLS_TITLE_DESC';
$modversion['config'][6]['formtype'] = 'yesno';
$modversion['config'][6]['valuetype'] = 'int';
$modversion['config'][6]['default'] = '1';

$modversion['config'][7]['name'] = 'cal_start';
$modversion['config'][7]['title'] = '_MI_CAL_START';
$modversion['config'][7]['description'] = '_MI_CAL_START_DESC';
$modversion['config'][7]['formtype'] = 'select';
$modversion['config'][7]['valuetype'] = 'int';
$modversion['config'][7]['default'] = '1';
$modversion['config'][7]['options'] = [_MI_TADCAL_SU => '0', _MI_TADCAL_MO => '1'];
