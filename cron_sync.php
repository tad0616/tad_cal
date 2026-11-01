<?php
if ('cli' !== PHP_SAPI) {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/preloads/autoloader.php';

$moduleHandler = xoops_getHandler('module');
$module = $moduleHandler->getByDirname('tad_cal');
if (!$module) {
    fwrite(STDERR, "Unable to load the tad_cal module.\n");
    exit(1);
}

$configHandler = xoops_getHandler('config');
$configs = $configHandler->getConfigsByCat(0, $module->getVar('mid'));
$xoopsModuleConfig = [];
foreach ($configs as $config) {
    $xoopsModuleConfig[$config->getVar('conf_name')] = $config->getVar('conf_value');
}

$sql = 'SELECT `cate_sn` FROM `' . $xoopsDB->prefix('tad_cal_google_calendar') . '` WHERE `refresh_token` <> \'\'';
$result = $xoopsDB->query($sql);
if (!$result) {
    fwrite(STDERR, "Unable to query linked Google calendars: {$xoopsDB->error()}\n");
    exit(1);
}

$hasErrors = false;
while (list($cateSn) = $xoopsDB->fetchRow($result)) {
    try {
        \XoopsModules\Tad_cal\GoogleCalendarSync::syncCategory((int) $cateSn);
        fwrite(STDOUT, "Calendar {$cateSn}: synchronized.\n");
    } catch (RuntimeException $error) {
        $hasErrors = true;
        fwrite(STDERR, "Calendar {$cateSn}: {$error->getMessage()}\n");
    }
}

exit($hasErrors ? 1 : 0);
