<?php

use XoopsModules\Tadtools\Utility;

if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
function xoops_module_install_tad_cal(&$module)
{
    Utility::mk_dir(XOOPS_ROOT_PATH . '/uploads/tad_cal');

    return true;
}
