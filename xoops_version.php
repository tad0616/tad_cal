<?php
$modversion = [];
global $xoopsConfig;

//---模組基本資訊---//
$modversion['name'] = _MI_TADCAL_NAME;
// $modversion['version'] = 3.5;
$modversion['version'] = $_SESSION['xoops_version'] >= 20511 ? '4.0.0-Stable' : '4.0';
$modversion['description'] = _MI_TADCAL_DESC;
$modversion['author'] = _MI_TADCAL_AUTHOR;
$modversion['credits'] = _MI_TADCAL_CREDITS;
$modversion['help'] = 'page=help';
$modversion['license'] = 'GNU GPL 2.0';
$modversion['license_url'] = 'www.gnu.org/licenses/gpl-2.0.html/';
$modversion['image'] = $xoopsConfig['language'] == 'tchinese_utf8' ? 'images/logo_tw.png' : 'images/logo.png';
$modversion['dirname'] = basename(__DIR__);

//---模組狀態資訊---//
$modversion['release_date'] = '2024-12-12';
$modversion['module_website_url'] = 'https://tad0616.net/';
$modversion['module_website_name'] = _MI_TAD_WEB;
$modversion['module_status'] = 'release';
$modversion['author_website_url'] = 'https://tad0616.net/';
$modversion['author_website_name'] = _MI_TAD_WEB;
$modversion['min_php'] = 5.4;
$modversion['min_xoops'] = '2.5.10';

//---paypal資訊---//
$modversion['paypal'] = [
    'business' => 'tad0616@gmail.com',
    'item_name' => 'Donation : ' . _MI_TAD_WEB,
    'amount' => 0,
    'currency_code' => 'USD',
];

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
$modversion['templates'] = [
    ['file' => 'tad_cal_index.tpl', 'description' => 'tad_cal_index.tpl'],
    ['file' => 'tad_cal_admin.tpl', 'description' => 'tad_cal_admin.tpl'],
    ['file' => 'tad_cal_event.tpl', 'description' => 'tad_cal_event.tpl'],
];

//---區塊設定 (索引為固定值，若欲刪除區塊記得補上索引，避免區塊重複)---//
$modversion['blocks'] = [
    1 => [
        'file' => 'tad_cal_calendar.php',
        'name' => _MI_TADCAL_BNAME1,
        'description' => _MI_TADCAL_BDESC1,
        'show_func' => 'tad_cal_calendar',
        'template' => 'tad_cal_calendar.tpl',
    ],
    2 => [
        'file' => 'tad_cal_list.php',
        'name' => _MI_TADCAL_BNAME2,
        'description' => _MI_TADCAL_BDESC2,
        'show_func' => 'tad_cal_list',
        'template' => 'tad_cal_list.tpl',
        'edit_func' => 'tad_cal_list_edit',
        'options' => '7',
    ],
    3 => [
        'file' => 'tad_cal_full_calendar.php',
        'name' => _MI_TADCAL_BNAME3,
        'description' => _MI_TADCAL_BDESC3,
        'show_func' => 'tad_cal_full_calendar',
        'template' => 'tad_cal_full_calendar.tpl',
    ],
];

//---偏好設定---//
$modversion['config'] = [
    [
        'name' => 'eventShowMode',
        'title' => '_MI_TADCAL_EVENTSHOWMODE',
        'description' => '_MI_TADCAL_EVENTSHOWMODE_DESC',
        'formtype' => 'select',
        'valuetype' => 'text',
        'default' => 'eventClick',
        'options' => [_MI_TADCAL_CONF0_OPT1 => 'eventClick', _MI_TADCAL_CONF0_OPT2 => 'eventMouseover'],
    ],
    [
        'name' => 'eventTheme',
        'title' => '_MI_TADCAL_EVENTTHEME',
        'description' => '_MI_TADCAL_EVENTTHEME_DESC',
        'formtype' => 'select',
        'valuetype' => 'text',
        'default' => 'ui-tooltip-blue',
        'options' => [
            _MI_TADCAL_CONF1_OPT1 => 'ui-tooltip',
            _MI_TADCAL_CONF1_OPT2 => 'ui-tooltip-light',
            _MI_TADCAL_CONF1_OPT3 => 'ui-tooltip-dark',
            _MI_TADCAL_CONF1_OPT4 => 'ui-tooltip-red',
            _MI_TADCAL_CONF1_OPT5 => 'ui-tooltip-blue',
            _MI_TADCAL_CONF1_OPT6 => 'ui-tooltip-green',
        ],
    ],
    [
        'name' => 'title_num',
        'title' => '_MI_TADCAL_TITLE_NUM',
        'description' => '_MI_TADCAL_TITLE_NUM_DESC',
        'formtype' => 'textbox',
        'valuetype' => 'int',
        'default' => '7',
    ],
    [
        'name' => 'quick_add',
        'title' => '_MI_TADCAL_QUICK_ADD',
        'description' => '_MI_TADCAL_QUICK_ADD_DESC',
        'formtype' => 'yesno',
        'valuetype' => 'int',
        'default' => '1',
    ],
    [
        'name' => 'use_social_tools',
        'title' => '_MI_SOCIALTOOLS_TITLE',
        'description' => '_MI_SOCIALTOOLS_TITLE_DESC',
        'formtype' => 'yesno',
        'valuetype' => 'int',
        'default' => '1',
    ],
    [
        'name' => 'cal_start',
        'title' => '_MI_CAL_START',
        'description' => '_MI_CAL_START_DESC',
        'formtype' => 'select',
        'valuetype' => 'int',
        'default' => '1',
        'options' => [_MI_TADCAL_SU => '0', _MI_TADCAL_MO => '1'],
    ],
];
