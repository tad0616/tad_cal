<?php
use Xmf\Request;
use XoopsModules\Tad_cal\GoogleCalendarSync;

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/preloads/autoloader.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$op = Request::getString('op');
$cateSn = Request::getInt('cate_sn');
if ('' === $op && (Request::getString('code') || Request::getString('error'))) {
    $op = 'callback';
}
if ('callback' === $op && !$cateSn && !empty($_SESSION['tad_cal_google_oauth']['cate_sn'])) {
    $cateSn = (int) $_SESSION['tad_cal_google_oauth']['cate_sn'];
}

if (!$xoopsUser || !$xoopsModule || !$xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    http_response_code(403);
    exit('需要行事曆模組管理員權限。');
}

if ('callback' === $op) {
    if (Request::getString('error')) {
        redirect_header(XOOPS_URL . '/modules/tad_cal/admin/main.php', 3, 'Google 授權未完成，請確認是否允許 tad_cal 存取行事曆。');
        exit;
    }

    try {
        $cateSn = GoogleCalendarSync::completeAuthorization(Request::getString('code'), Request::getString('state'));
        redirect_header(XOOPS_URL . '/modules/tad_cal/admin/main.php?op=tad_cal_cate_form&cate_sn=' . $cateSn, 3, 'Google 行事曆已連結。請按「立即同步」完成首次同步。');
    } catch (RuntimeException $error) {
        redirect_header(XOOPS_URL . '/modules/tad_cal/admin/main.php?op=tad_cal_cate_form&cate_sn=' . $cateSn, 5, $error->getMessage());
    }
    exit;
}

if ('connect' === $op) {
    try {
        header('Location: ' . GoogleCalendarSync::authorizeUrl($cateSn));
    } catch (RuntimeException $error) {
        redirect_header(XOOPS_URL . '/modules/tad_cal/admin/main.php?op=tad_cal_cate_form&cate_sn=' . $cateSn, 5, $error->getMessage());
    }
    exit;
}

$csrf = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
if ('POST' !== $_SERVER['REQUEST_METHOD'] || empty($_SESSION['tad_cal_google_admin_token']) ||
    !hash_equals($_SESSION['tad_cal_google_admin_token'], $csrf)) {
    http_response_code(403);
    exit('請重新載入行事曆管理頁面後再操作。');
}

try {
    if ('sync' === $op) {
        $status = GoogleCalendarSync::getCalendarStatus($cateSn);
        if ('replace_with_google' === $status['initial_sync'] && empty($status['last_sync']) &&
            (!isset($_POST['confirm_replace']) || '1' !== $_POST['confirm_replace'])) {
            throw new RuntimeException('首次同步會刪除本分類既有 tad_cal 事件。請勾選確認後再執行。');
        }
        GoogleCalendarSync::syncCategory(
            $cateSn,
            isset($_POST['confirm_replace']) && '1' === $_POST['confirm_replace']
        );
        $message = '雙向同步完成。';
    } elseif ('disconnect' === $op) {
        GoogleCalendarSync::disconnect($cateSn);
        $message = 'Google 行事曆已解除連結；兩邊事件均保留。';
    } else {
        http_response_code(400);
        exit('無效的同步操作。');
    }
    redirect_header(XOOPS_URL . '/modules/tad_cal/admin/main.php?op=tad_cal_cate_form&cate_sn=' . $cateSn, 3, $message);
} catch (RuntimeException $error) {
    redirect_header(XOOPS_URL . '/modules/tad_cal/admin/main.php?op=tad_cal_cate_form&cate_sn=' . $cateSn, 5, $error->getMessage());
}
