<?php

function xoops_module_uninstall_tad_cal(&$module)
{
    global $xoopsDB;
    $date = date('Ymd');

    rename(XOOPS_ROOT_PATH . '/uploads/tad_cal', XOOPS_ROOT_PATH . "/uploads/tad_cal_bak_{$date}");

    return true;
}
