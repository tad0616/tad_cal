<?php
use XoopsModules\Tad_cal\Tools;
require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/function.php';

Tools::get_session();
$interface_menu[_TAD_TO_MOD] = 'index.php';

if ($xoopsUser) {
    $interface_menu[_MD_TADCAL_SMNAME2] = 'event.php';
    $interface_menu[_MD_TADCAL_SMNAME3] = 'download.php';
    if ($_GET['op'] == 'todo_list') {
        $interface_menu[_MD_TADCAL_SMNAME5] = 'event.php?op=todo_list_ok';
    } else {
        $interface_menu[_MD_TADCAL_TODOLIST] = 'event.php?op=todo_list';
    }

}

if ($_SESSION['tad_cal_adm']) {
    $interface_menu[_TAD_TO_ADMIN] = 'admin/main.php';
}
