<?php

use XoopsModules\Tad_cal\Utility;

function xoops_module_uninstall_tad_cal(&$module) {
  GLOBAL $xoopsDB;
  $date=date("Ymd");

  rename(XOOPS_ROOT_PATH."/uploads/tad_cal",XOOPS_ROOT_PATH."/uploads/tad_cal_bak_{$date}");

  return true;
}
