<?php
use XoopsModules\Tadtools\Utility;

if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}

//區塊主函式 (小行事曆(tad_cal_calendar))
function tad_cal_calendar($options)
{
    global $xoTheme;

    $TadCalModuleConfig = Utility::getXoopsModuleConfig('tad_cal');

    Utility::get_jquery(true); //一般只要此行即可
    Utility::add_migrate();

    $block['firstDay'] = $TadCalModuleConfig['cal_start'];

    $xoTheme->addStylesheet('modules/tad_cal/css/module.css?t=20260904');
    $xoTheme->addStylesheet('modules/tadtools/jquery.qtip_2/jquery.qtip.min.css');

    $xoTheme->addScript('modules/tadtools/moment/moment-with-locales.min.js');
    $xoTheme->addScript('modules/tadtools/fullcalendar6/index.global.js');
    $xoTheme->addScript('modules/tadtools/jquery.qtip_2/jquery.qtip.min.js');
    return $block;
}
