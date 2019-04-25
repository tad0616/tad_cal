<?php

use XoopsModules\Tad_cal\Update;

function xoops_module_update_tad_cal(&$module, $old_version)
{
    global $xoopsDB;

    if (Update::chk_uid()) {
        Update::chk_chk1();
    }

    Update::chk_tad_cal_block();

    return true;
}
