<?php
use XoopsModules\Tadtools\Utility;

if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}

//區塊主函式 (小行事曆(tad_cal_calendar))
function tad_cal_calendar($options)
{
    global $xoTheme;

    $moduleHandler = xoops_getHandler('module');
    $xoopsModule = $moduleHandler->getByDirname('tad_cal');
    $configHandler = xoops_getHandler('config');
    $module_id = $xoopsModule->getVar('mid');
    $xoopsModuleConfig = $configHandler->getConfigsByCat(0, $module_id);

    Utility::get_jquery(true); //一般只要此行即可
    Utility::add_migrate();

    $block['firstDay'] = $xoopsModuleConfig['cal_start'];

    $xoTheme->addStylesheet('modules/tad_cal/css/module.css');
    $xoTheme->addStylesheet('modules/tadtools/fullcalendar/redmond/theme.css');
    $xoTheme->addStylesheet('modules/tadtools/fullcalendar/fullcalendar.css');
    $xoTheme->addStylesheet('modules/tadtools/jquery.qtip_2/jquery.qtip.min.css');

    $xoTheme->addScript('modules/tadtools/moment/moment-with-locales.min.js');
    $xoTheme->addScript('modules/tad_cal/class/jquery-impromptu.6.2.3.min.js');
    $xoTheme->addScript('modules/tadtools/fullcalendar/fullcalendar.js');
    $xoTheme->addScript('modules/tadtools/fullcalendar/gcal.js');
    $xoTheme->addScript('modules/tadtools/jquery.qtip_2/jquery.qtip.min.js');

    return $block;
}
