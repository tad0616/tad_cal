<?php
use XoopsModules\Tad_cal\Tools;
if (!class_exists('XoopsModules\Tad_cal\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/tad_cal/preloads/autoloader.php';
}

Tools::get_session();
$interface_menu[_MD_TADCAL_NEW_CALENDAR] = 'index.php';
$interface_icon[_MD_TADCAL_NEW_CALENDAR] = "fa-calendar";

if (isset($xoopsUser)) {
    $interface_menu[_MD_TADCAL_SMNAME2] = 'event.php';
    $interface_icon[_MD_TADCAL_SMNAME2] = "fa-calendar-plus";

    $interface_menu[_MD_TADCAL_SMNAME3] = 'download.php';
    $interface_icon[_MD_TADCAL_SMNAME3] = "fa-download";

    if ($_GET['op'] == 'todo_list') {
        $interface_menu[_MD_TADCAL_SMNAME5] = 'event.php?op=todo_list_ok';
        $interface_icon[_MD_TADCAL_SMNAME5] = "fa-calendar-check";
    } else {
        $interface_menu[_MD_TADCAL_TODOLIST] = 'event.php?op=todo_list';
        $interface_icon[_MD_TADCAL_TODOLIST] = "fa-list";
    }

}
