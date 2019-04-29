<?php
/*-----------引入檔案區--------------*/
require_once 'header.php';
require_once '../function.php';

/*-----------function區--------------*/

$updateRecordsArray = $_POST['tr'];

$sort = 1;
foreach ($updateRecordsArray as $recordIDValue) {
    $sql = 'update ' . $xoopsDB->prefix('tad_cal_cate') . " set `cate_sort`='{$sort}' where `cate_sn`='{$recordIDValue}'";
    $xoopsDB->queryF($sql) or die('Save Sort Fail! (' . date('Y-m-d H:i:s') . ')');
    $sort++;
}

echo 'Save Sort OK! (' . date('Y-m-d H:i:s') . ')';
