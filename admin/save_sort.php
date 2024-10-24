<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';

/*-----------function區--------------*/
// 關閉除錯訊息
$xoopsLogger->activated = false;

$updateRecordsArray = Request::getVar('tr', [], null, 'array', 4);

$sort = 1;
foreach ($updateRecordsArray as $recordIDValue) {
    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_cate') . '` SET `cate_sort`=? WHERE `cate_sn`=?';
    Utility::query($sql, 'ii', [$sort, $recordIDValue]) or die(_TAD_SORT_FAIL . ' (' . date('Y-m-d H:i:s') . ')');

    $sort++;
}

echo _TAD_SORTED . "(" . date("Y-m-d H:i:s") . ")";
