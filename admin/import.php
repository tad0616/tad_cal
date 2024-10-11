<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tad_cal\Tools;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';

/*-----------function區--------------*/

//匯入事件
function import_google($cate_sn = '')
{
    global $xoopsDB, $xoopsUser;
    if (!ini_get('safe_mode')) {
        set_time_limit(0);
    }

    $client_id = '254265660934-4m4mon8fms910dokh93o3spp77k0ahtr.apps.googleusercontent.com';
    //$client_secret = '0ROGDfIJB1Q3RBFioMc0Bqig';
    $client_secret = '254265660934-4m4mon8fms910dokh93o3spp77k0ahtr@developer.gserviceaccount.com';
    $redirect_uri = XOOPS_URL . '/modules/tad_cal/admin/import.php';

    require XOOPS_ROOT_PATH . '/modules/tad_cal/class/gapi/autoload.php';

    $client = new Google_Client();
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri($redirect_uri);
    // $client->setAccessType('offline');
    // $client->setApprovalPrompt('force');
    $client->setScopes('https://www.googleapis.com/auth/userinfo.email');
    $client->setScopes('https://www.googleapis.com/auth/userinfo.profile');
    $client->setScopes('https://www.googleapis.com/auth/calendar');
    // $client->setScopes("https://www.googleapis.com/auth/calendar.readonly");
    $client->setApplicationName('Tad Cal');
    $client->setDeveloperKey('AIzaSyCk7fQpA3WRB3NtyYFTxrGB9wu484qDdsY');

    if (!isset($_SESSION['import_google'])) {
        $_SESSION['import_google'] = $cate_sn;
    } else {
        $cate_sn = $_SESSION['import_google'];
    }

    $cal = new Google_Service_Calendar($client);

    if (isset($_GET['code'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['token'] = $client->getAccessToken();
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL) . "?cate_sn={$cate_sn}");
        exit;
    }

    if (isset($_SESSION['token'])) {
        // if($client->isAccessTokenExpired()) {
        //   $authUrl = $client->createAuthUrl();
        //   header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        //   exit;
        // }

        $client->setAccessToken($_SESSION['token']);
    }

    if ($client->getAccessToken()) {
        //取得使用者編號
        $uid = ($xoopsUser) ? $xoopsUser->uid() : '';

        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_cate') . '` WHERE `cate_sn`=?';
        $result = Utility::query($sql, 'i', [$cate_sn]) or Utility::web_error($sql, __FILE__, __LINE__);
        $all = $xoopsDB->fetchArray($result);

        //以下會產生這些變數： $cate_sn , $cate_title , $cate_sort , $cate_enable , $cate_handle , $enable_group , $enable_upload_group , $google_id , $google_pass
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        $now = date('Y-m-d H:i:s', time());

        date_default_timezone_set('Asia/Taipei');
        $calendarId = str_replace('%40', '@', $cate_handle);
        $events = $cal->events->listEvents($calendarId);

        while (true) {
            foreach ($events->getItems() as $event) {
                $title = $event->summary;
                $start = $event->start->dateTime;
                $end = $event->end->dateTime;
                $recurrence = is_array($event->recurrence) ? implode('', $event->recurrence) : '';
                $location = $event->location;
                $kind = $event->kind;
                $details = $event->description;
                $etag = $event->etag;
                $id = $event->id;
                $sequence = $event->sequence;
                $allday = Tools::isAllDay($start, $end);
                $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_cal_event') . '`
                (`title`, `start`, `end`, `recurrence`, `location`, `kind`, `details`, `etag`, `id`, `sequence`, `uid`, `cate_sn`, `allday`, `tag`, `last_update`)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE `title`=?, `start`=?, `end`=?, `recurrence`=?, `location`=?, `kind`=?, `details`=?, `etag`=?, `id`=?, `sequence`=?, `uid`=?, `cate_sn`=?, `allday`=?, `tag`=?, `last_update`=?';
                Utility::query($sql, 'sssssssssiiissssssssssssiiisss', [$title, $start, $end, $recurrence, $location, $kind, $details, $etag, $id, $sequence, $uid, $cate_sn, $allday, $tag, $now, $title, $start, $end, $recurrence, $location, $kind, $details, $etag, $id, $sequence, $uid, $cate_sn, $allday, $tag, $now]) or Utility::web_error($sql, __FILE__, __LINE__);

                //取得最後新增資料的流水編號
                $sn = $xoopsDB->getInsertId();
                //重複事件
                Tools::rrule($sn, $recurrence, $allday);
            }
            // $pageToken = $events->getNextPageToken();
            // if ($pageToken) {
            //   $optParams = array('pageToken' => $pageToken);
            //   $events = $cal->events->listEvents($calendarId, $optParams);
            // } else {
            //   break;
            // }
        }
        $now = date('Y-m-d H:i:s');
        $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `cate_sn` = ? AND `last_update` < ?';
        Utility::query($sql, 'is', [$cate_sn, $now]) or Utility::web_error($sql, __FILE__, __LINE__);

        if (isset($_SESSION['import_google'])) {
            unset($_SESSION['import_google']);
        }
    } else {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }
}
/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$cate_sn = Request::getInt('cate_sn');
$sn = Request::getInt('sn');
import_google($cate_sn);
