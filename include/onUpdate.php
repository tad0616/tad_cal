<?php

use XoopsModules\Tad_cal\Utility;

function xoops_module_update_tad_cal(&$module, $old_version)
{
    global $xoopsDB;

    if (Utility::chk_uid()) {
        Utility::chk_chk1();
    }

    //if(!Utility::chk_chk1()) Utility::go_update1();

    Utility::chk_tad_cal_block();
    return true;
}

