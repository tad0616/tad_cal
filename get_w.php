<?php
require_once dirname(dirname(__DIR__)) . '/mainfile.php';

$start = (string) $_POST['day'];
$week = getWeekOfTheMonth(strtotime($start));
$wk = date('w', strtotime($start));

$xoopsLogger->activated = false;

echo sprintf(_MD_TADCAL_MONTH_REPEATDAY, $week, $wk);

//算一下該日是該月的第幾週
function getWeekOfTheMonth($dateTimestamp = '')
{
    $weekNum = date('W', $dateTimestamp) - date('W', strtotime(date('Y-m-01', $dateTimestamp))) + 1;

    return $weekNum;
}
