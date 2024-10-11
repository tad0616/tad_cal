<?php
use XoopsModules\Tadtools\Utility;
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';

/*-----------function區--------------*/

$updateRecordsArray = $_POST['tr'];

$sort = 1;
foreach ($updateRecordsArray as $recordIDValue) {
    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_cate') . '` SET `cate_sort`=? WHERE `cate_sn`=?';
    Utility::query($sql, 'ii', [$sort, $recordIDValue]) or die('Save Sort Fail! (' . date('Y-m-d H:i:s') . ')');

    $sort++;
}

echo 'Save Sort OK! (' . date('Y-m-d H:i:s') . ')';
