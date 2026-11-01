<?php
namespace XoopsModules\Tad_cal;

use RuntimeException;
use XoopsModules\Tadtools\Utility;

class GoogleCalendarSync
{
    private static $accessTokens = [];

    public static function authorizeUrl($cateSn)
    {
        global $xoopsModuleConfig;

        self::assertConfigured();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $state = bin2hex(random_bytes(24));
        $_SESSION['tad_cal_google_oauth'] = [
            'state' => $state,
            'cate_sn' => (int) $cateSn,
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $xoopsModuleConfig['google_client_id'],
            'redirect_uri' => self::redirectUri(),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public static function completeAuthorization($code, $state)
    {
        global $xoopsModuleConfig;

        self::assertConfigured();
        $oauthState = isset($_SESSION['tad_cal_google_oauth']) ? $_SESSION['tad_cal_google_oauth'] : [];
        unset($_SESSION['tad_cal_google_oauth']);
        if (empty($oauthState['state']) || !hash_equals($oauthState['state'], $state) || empty($oauthState['cate_sn'])) {
            throw new RuntimeException('Google OAuth state 驗證失敗，請重新連結行事曆。');
        }
        $cateSn = (int) $oauthState['cate_sn'];

        $token = self::httpRequest('https://oauth2.googleapis.com/token', 'POST', [
            'code' => $code,
            'client_id' => $xoopsModuleConfig['google_client_id'],
            'client_secret' => $xoopsModuleConfig['google_client_secret'],
            'redirect_uri' => self::redirectUri(),
            'grant_type' => 'authorization_code',
        ], false);
        if (empty($token['refresh_token'])) {
            throw new RuntimeException('Google 未提供 refresh token，請先移除 tad_cal 授權後再重新連結。');
        }

        $calendar = self::calendarConfig($cateSn);
        if (!$calendar) {
            throw new RuntimeException('找不到此行事曆分類，請返回後台重新設定。');
        }

        self::saveCalendar(
            $cateSn,
            $calendar['calendar_id'],
            self::encrypt($token['refresh_token']),
            $calendar['sync_token'],
            $calendar['initial_sync'],
            $calendar['last_sync']
        );

        return $cateSn;
    }

    public static function saveCalendarSettings($cateSn, $calendarId, $initialSync)
    {
        $allowedModes = ['keep_local_only', 'replace_with_google', 'merge_and_upload'];
        if (!in_array($initialSync, $allowedModes, true)) {
            throw new RuntimeException('無效的首次同步策略。');
        }
        if ('' === trim($calendarId)) {
            throw new RuntimeException('Google 行事曆 ID 不可空白；主行事曆請填 primary。');
        }
        if (strlen(trim($calendarId)) > 255) {
            throw new RuntimeException('Google 行事曆 ID 不可超過 255 個字元。');
        }

        $calendar = self::calendarConfig($cateSn);
        $refreshToken = $calendar ? $calendar['refresh_token'] : '';
        $syncToken = $calendar ? $calendar['sync_token'] : '';
        $lastSync = $calendar ? $calendar['last_sync'] : '';
        if ($calendar && $calendar['calendar_id'] !== trim($calendarId)) {
            global $xoopsDB;

            $table = $xoopsDB->prefix('tad_cal_google_event');
            Utility::query("DELETE FROM `{$table}` WHERE `cate_sn`=?", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
            $syncToken = '';
            $lastSync = '';
        }
        self::saveCalendar($cateSn, trim($calendarId), $refreshToken, $syncToken, $initialSync, $lastSync);
    }

    public static function getCalendarStatus($cateSn)
    {
        $calendar = self::calendarConfig($cateSn);
        if (!$calendar) {
            return ['calendar_id' => 'primary', 'initial_sync' => 'keep_local_only', 'connected' => false, 'last_sync' => ''];
        }

        return [
            'calendar_id' => $calendar['calendar_id'],
            'initial_sync' => $calendar['initial_sync'],
            'connected' => !empty($calendar['refresh_token']),
            'last_sync' => $calendar['last_sync'],
        ];
    }

    public static function disconnect($cateSn)
    {
        global $xoopsDB;

        $calendarTable = $xoopsDB->prefix('tad_cal_google_calendar');
        $eventTable = $xoopsDB->prefix('tad_cal_google_event');
        Utility::query("DELETE FROM `{$eventTable}` WHERE `cate_sn`=?", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
        Utility::query("DELETE FROM `{$calendarTable}` WHERE `cate_sn`=?", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
    }

    public static function deleteCategory($cateSn)
    {
        global $xoopsDB;

        $sql = 'SELECT `sn` FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `cate_sn`=?';
        $result = Utility::query($sql, 'i', [$cateSn]) or Utility::web_error($sql, __FILE__, __LINE__);
        while (list($sn) = $xoopsDB->fetchRow($result)) {
            self::deleteLocalEvent($sn);
        }

        self::disconnect($cateSn);
    }

    public static function syncCategory($cateSn, $confirmReplace = false)
    {
        global $xoopsDB;

        $calendar = self::calendarConfig($cateSn);
        if (!$calendar || empty($calendar['refresh_token']) || empty($calendar['calendar_id'])) {
            throw new RuntimeException('此分類尚未完成 Google 行事曆授權或設定。');
        }

        $isFirstSync = empty($calendar['last_sync']);
        $replaceLocal = $isFirstSync && 'replace_with_google' === $calendar['initial_sync'];
        if ($isFirstSync) {
            if ($replaceLocal && !$confirmReplace) {
                throw new RuntimeException('首次同步會刪除本分類既有 tad_cal 事件。請在管理介面確認後執行。');
            } elseif ('keep_local_only' === $calendar['initial_sync']) {
                $sql = 'SELECT `sn`, `last_update` FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `cate_sn`=?';
                $result = Utility::query($sql, 'i', [$cateSn]) or Utility::web_error($sql, __FILE__, __LINE__);
                while ($row = $xoopsDB->fetchArray($result)) {
                    self::saveMapping($cateSn, $row['sn'], null, null, $row['last_update']);
                }
            } else {
                self::pushUnmappedLocalEvents($cateSn, false);
            }
        } else {
            self::pushUnmappedLocalEvents($cateSn, true);
        }

        $pageToken = '';
        $nextSyncToken = $calendar['sync_token'];
        $initialEvents = [];
        do {
            $params = ['showDeleted' => 'true', 'maxResults' => 2500];
            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }
            if ($nextSyncToken) {
                $params['syncToken'] = $nextSyncToken;
            }

            try {
                $response = self::apiRequest(
                    $calendar,
                    'GET',
                    self::eventsUrl($calendar) . '?' . http_build_query($params)
                );
            } catch (RuntimeException $error) {
                if (!$nextSyncToken || false === strpos($error->getMessage(), 'HTTP 410')) {
                    throw $error;
                }
                self::saveCalendar($cateSn, $calendar['calendar_id'], $calendar['refresh_token'], '', $calendar['initial_sync'], $calendar['last_sync']);
                throw new RuntimeException('Google 同步 token 已失效；已重設同步狀態，請再次執行同步。');
            }

            foreach (isset($response['items']) ? $response['items'] : [] as $googleEvent) {
                if ($replaceLocal) {
                    self::validateGoogleEvent($googleEvent);
                    $initialEvents[] = $googleEvent;
                } else {
                    self::applyGoogleEvent($cateSn, $googleEvent);
                }
            }
            $pageToken = isset($response['nextPageToken']) ? $response['nextPageToken'] : '';
            if (!$pageToken && !empty($response['nextSyncToken'])) {
                $nextSyncToken = $response['nextSyncToken'];
            }
        } while ($pageToken);

        if (!$nextSyncToken) {
            throw new RuntimeException('Google API 未回傳同步 token，請稍後重試。');
        }

        if ($replaceLocal) {
            self::clearLocalCategory($cateSn);
            foreach ($initialEvents as $googleEvent) {
                self::applyGoogleEvent($cateSn, $googleEvent);
            }
        }

        self::saveCalendar(
            $cateSn,
            $calendar['calendar_id'],
            $calendar['refresh_token'],
            $nextSyncToken,
            $calendar['initial_sync'],
            date('Y-m-d H:i:s')
        );
    }

    public static function syncLocalEvent($sn)
    {
        global $xoopsDB;

        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=?';
        $result = Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);
        $event = $xoopsDB->fetchArray($result);
        if (!$event) {
            return;
        }

        $calendar = self::calendarConfig($event['cate_sn']);
        if (!$calendar || empty($calendar['refresh_token']) || empty($calendar['last_sync'])) {
            return;
        }

        $mapping = self::mappingByLocal($event['cate_sn'], $sn);
        if ($mapping) {
            self::saveMapping($event['cate_sn'], $event['sn'], $mapping['google_event_id'], $mapping['google_updated'], $mapping['local_baseline'], 1);
            if (!empty($mapping['google_event_id'])) {
                try {
                    $googleEvent = self::apiRequest(
                        $calendar,
                        'GET',
                        self::eventsUrl($calendar) . '/' . rawurlencode($mapping['google_event_id'])
                    );
                } catch (RuntimeException $error) {
                    if (!self::isMissingEventError($error)) {
                        throw $error;
                    }
                    self::deleteLocalFromGoogle($event['cate_sn'], $sn);
                    self::deleteMapping($event['cate_sn'], $mapping['google_event_id']);
                    return;
                }

                if (isset($googleEvent['status']) && 'cancelled' === $googleEvent['status']) {
                    self::deleteLocalFromGoogle($event['cate_sn'], $sn);
                    self::deleteMapping($event['cate_sn'], $mapping['google_event_id']);
                    return;
                }

                if (!empty($googleEvent['updated']) && strtotime($googleEvent['updated']) > strtotime($event['last_update'])) {
                    self::updateLocalFromGoogle($sn, $event['cate_sn'], $googleEvent);
                    self::saveMapping(
                        $event['cate_sn'],
                        $sn,
                        $mapping['google_event_id'],
                        self::mysqlDate($googleEvent['updated']),
                        self::mysqlDate($googleEvent['updated'])
                    );
                    return;
                }
            }
        }
        self::pushEvent($calendar, $event, $mapping);
    }

    public static function deleteLocalEvent($sn)
    {
        global $xoopsDB;

        $sql = 'SELECT `cate_sn` FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=?';
        $result = Utility::query($sql, 'i', [$sn]) or Utility::web_error($sql, __FILE__, __LINE__);
        list($cateSn) = $xoopsDB->fetchRow($result);
        if (!$cateSn) {
            return;
        }

        $mapping = self::mappingByLocal($cateSn, $sn);
        if ($mapping && !empty($mapping['google_event_id'])) {
            $calendar = self::calendarConfig($cateSn);
            if ($calendar && !empty($calendar['refresh_token'])) {
                try {
                    self::apiRequest(
                        $calendar,
                        'DELETE',
                        self::eventsUrl($calendar) . '/' . rawurlencode($mapping['google_event_id'])
                    );
                } catch (RuntimeException $error) {
                    if (!self::isMissingEventError($error)) {
                        throw $error;
                    }
                }
            }
        }

        $table = $xoopsDB->prefix('tad_cal_google_event');
        Utility::query("DELETE FROM `{$table}` WHERE `cate_sn`=? AND `local_sn`=?", 'ii', [$cateSn, $sn]) or Utility::web_error('', __FILE__, __LINE__);
    }

    private static function applyGoogleEvent($cateSn, $googleEvent)
    {
        global $xoopsDB;

        if (empty($googleEvent['id'])) {
            return;
        }
        $mapping = self::mappingByGoogle($cateSn, $googleEvent['id']);
        if (isset($googleEvent['status']) && 'cancelled' === $googleEvent['status']) {
            if (!empty($googleEvent['recurringEventId'])) {
                throw new RuntimeException('Google 重複事件的單次例外目前無法同步；請先還原該例外或改為單一事件。');
            }
            if ($mapping && !empty($mapping['local_sn'])) {
                self::deleteLocalFromGoogle($cateSn, $mapping['local_sn']);
            }
            self::deleteMapping($cateSn, $googleEvent['id']);
            return;
        }

        if (!empty($googleEvent['recurringEventId'])) {
            throw new RuntimeException('Google 重複事件的單次例外目前無法同步；請先還原該例外或改為單一事件。');
        }

        if ($mapping && !empty($mapping['local_sn'])) {
            $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=?';
            $result = Utility::query($sql, 'i', [$mapping['local_sn']]) or Utility::web_error($sql, __FILE__, __LINE__);
            $localEvent = $xoopsDB->fetchArray($result);
            if (!$localEvent) {
                self::deleteMapping($cateSn, $googleEvent['id']);
                $mapping = null;
            } else {
                $remoteUpdated = isset($googleEvent['updated']) ? strtotime($googleEvent['updated']) : 0;
                $localUpdated = strtotime($localEvent['last_update']);
                if ($remoteUpdated <= $localUpdated) {
                    $calendar = self::calendarConfig($cateSn);
                    self::pushEvent($calendar, $localEvent, $mapping);
                    return;
                }
                self::updateLocalFromGoogle($localEvent['sn'], $cateSn, $googleEvent);
                self::saveMapping($cateSn, $localEvent['sn'], $googleEvent['id'], self::mysqlDate($googleEvent['updated']), $localEvent['last_update']);
                return;
            }
        }

        $localSn = self::insertLocalFromGoogle($cateSn, $googleEvent);
        self::saveMapping(
            $cateSn,
            $localSn,
            $googleEvent['id'],
            self::mysqlDate(isset($googleEvent['updated']) ? $googleEvent['updated'] : ''),
            date('Y-m-d H:i:s')
        );
    }

    private static function validateGoogleEvent($googleEvent)
    {
        if (!empty($googleEvent['recurringEventId'])) {
            throw new RuntimeException('Google 重複事件的單次例外目前無法同步；請先還原該例外或改為單一事件。');
        }
        if (!isset($googleEvent['status']) || 'cancelled' !== $googleEvent['status']) {
            self::localFieldsFromGoogle($googleEvent);
        }
    }

    private static function pushUnmappedLocalEvents($cateSn, $includeBaselined)
    {
        global $xoopsDB;

        $sql = 'SELECT e.*, g.`local_sn` AS `mapped_local_sn`, g.`google_event_id`, g.`sync_pending` FROM `' . $xoopsDB->prefix('tad_cal_event') . '` AS e
            LEFT JOIN `' . $xoopsDB->prefix('tad_cal_google_event') . '` AS g ON g.`cate_sn`=? AND g.`local_sn`=e.`sn`
            WHERE e.`cate_sn`=? AND (g.`local_sn` IS NULL OR g.`sync_pending`=1)';
        $result = Utility::query($sql, 'ii', [$cateSn, $cateSn]) or Utility::web_error($sql, __FILE__, __LINE__);
        $calendar = self::calendarConfig($cateSn);
        while ($event = $xoopsDB->fetchArray($result)) {
            if (!empty($event['mapped_local_sn']) || $includeBaselined || 'merge_and_upload' === $calendar['initial_sync']) {
                $mapping = !empty($event['mapped_local_sn']) ? ['google_event_id' => $event['google_event_id']] : null;
                self::pushEvent($calendar, $event, $mapping);
            }
        }
    }

    private static function pushEvent($calendar, $event, $mapping)
    {
        $googleEvent = self::toGoogleEvent($event);
        if ($mapping && !empty($mapping['google_event_id'])) {
            $method = 'PATCH';
            $url = self::eventsUrl($calendar) . '/' . rawurlencode($mapping['google_event_id']);
        } else {
            $method = 'POST';
            $url = self::eventsUrl($calendar);
            $googleEvent['id'] = self::googleEventId($event['sn']);
        }

        try {
            $response = self::apiRequest($calendar, $method, $url, $googleEvent);
        } catch (RuntimeException $error) {
            if ('PATCH' === $method && self::isMissingEventError($error)) {
                self::deleteLocalFromGoogle($event['cate_sn'], $event['sn']);
                self::deleteMapping($event['cate_sn'], $mapping['google_event_id']);
                return;
            }
            if ('POST' !== $method || false === strpos($error->getMessage(), 'HTTP 409')) {
                throw $error;
            }
            $response = self::apiRequest(
                $calendar,
                'GET',
                self::eventsUrl($calendar) . '/' . rawurlencode($googleEvent['id'])
            );
        }
        self::saveMapping(
            $event['cate_sn'],
            $event['sn'],
            $response['id'],
            self::mysqlDate(isset($response['updated']) ? $response['updated'] : ''),
            $event['last_update']
        );
    }

    private static function googleEventId($localSn)
    {
        return 'tadcal' . base_convert((string) $localSn, 10, 32);
    }

    private static function toGoogleEvent($event)
    {
        $googleEvent = [
            'summary' => $event['title'],
            'location' => $event['location'],
            'description' => $event['details'],
        ];

        if (!empty($event['recurrence'])) {
            $ical = new Ical();
            $ical->parse($event['recurrence']);
            $data = $ical->get_all_data();
            if (empty($data['']['DTSTART']['unixtime']) || empty($data['']['DTEND']['unixtime']) || empty($data['']['RRULE'])) {
                throw new RuntimeException('無法轉換此重複事件規則至 Google 行事曆。');
            }
            if ('1' == $event['allday']) {
                $googleEvent['start'] = ['date' => date('Y-m-d', $data['']['DTSTART']['unixtime'])];
                $googleEvent['end'] = ['date' => date('Y-m-d', $data['']['DTEND']['unixtime'])];
            } else {
                $timezone = isset($data['']['DTSTART']['TZID']) ? $data['']['DTSTART']['TZID'] : date_default_timezone_get();
                $tz = self::timezone($timezone);
                $start = new \DateTime('@' . $data['']['DTSTART']['unixtime']);
                $end = new \DateTime('@' . $data['']['DTEND']['unixtime']);
                $start->setTimezone($tz);
                $end->setTimezone($tz);
                $googleEvent['start'] = ['dateTime' => $start->format(\DateTime::RFC3339), 'timeZone' => $timezone];
                $googleEvent['end'] = ['dateTime' => $end->format(\DateTime::RFC3339), 'timeZone' => $timezone];
            }
            $rules = [];
            foreach ($data['']['RRULE'] as $name => $value) {
                $rules[] = "{$name}={$value}";
            }
            $googleEvent['recurrence'] = ['RRULE:' . implode(';', $rules)];
        } elseif ('1' == $event['allday']) {
            $googleEvent['start'] = ['date' => date('Y-m-d', strtotime($event['start']))];
            $googleEvent['end'] = ['date' => date('Y-m-d', strtotime($event['end']))];
        } else {
            $googleEvent['start'] = ['dateTime' => date('c', strtotime($event['start']))];
            $googleEvent['end'] = ['dateTime' => date('c', strtotime($event['end']))];
        }

        return $googleEvent;
    }

    private static function insertLocalFromGoogle($cateSn, $googleEvent)
    {
        global $xoopsDB;

        $fields = self::localFieldsFromGoogle($googleEvent);
        $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_cal_event') . '`
            (`title`,`start`,`end`,`recurrence`,`location`,`kind`,`details`,`etag`,`id`,`sequence`,`uid`,`cate_sn`,`allday`,`tag`,`last_update`)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        Utility::query($sql, 'sssssssssiiisss', [
            $fields['title'], $fields['start'], $fields['end'], $fields['recurrence'],
            $fields['location'], '', $fields['details'], '', '', 0, 0,
            $cateSn, $fields['allday'], '', $fields['last_update'],
        ]) or Utility::web_error($sql, __FILE__, __LINE__);
        $sn = $xoopsDB->getInsertId();

        $updateSql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_event') . '` SET `id`=? WHERE `sn`=?';
        Utility::query($updateSql, 'ii', [$sn, $sn]) or Utility::web_error($updateSql, __FILE__, __LINE__);
        if (!empty($fields['recurrence'])) {
            Tools::rrule($sn, $fields['recurrence'], $fields['allday']);
        }

        return $sn;
    }

    private static function updateLocalFromGoogle($sn, $cateSn, $googleEvent)
    {
        global $xoopsDB;

        $fields = self::localFieldsFromGoogle($googleEvent);
        $sql = 'UPDATE `' . $xoopsDB->prefix('tad_cal_event') . '`
            SET `title`=?,`start`=?,`end`=?,`recurrence`=?,`location`=?,`details`=?,`etag`=?,`cate_sn`=?,`allday`=?,`last_update`=?
            WHERE `sn`=?';
        Utility::query($sql, 'sssssssiisi', [
            $fields['title'], $fields['start'], $fields['end'], $fields['recurrence'],
            $fields['location'], $fields['details'], isset($googleEvent['etag']) ? $googleEvent['etag'] : '',
            $cateSn, $fields['allday'], $fields['last_update'], $sn,
        ]) or Utility::web_error($sql, __FILE__, __LINE__);
        Utility::query('DELETE FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` WHERE `sn`=?', 'i', [$sn]) or Utility::web_error('', __FILE__, __LINE__);
        if (!empty($fields['recurrence'])) {
            Tools::rrule($sn, $fields['recurrence'], $fields['allday']);
        }
    }

    private static function localFieldsFromGoogle($googleEvent)
    {
        $start = isset($googleEvent['start']) ? $googleEvent['start'] : [];
        $end = isset($googleEvent['end']) ? $googleEvent['end'] : [];
        if (empty($start) || empty($end)) {
            throw new RuntimeException('Google 事件缺少開始或結束時間，無法同步。');
        }
        $allDay = !empty($start['date']);
        if ($allDay) {
            $startDate = $start['date'];
            $endDate = isset($end['date']) ? $end['date'] : $startDate;
            $startDateTime = strtotime($startDate);
            $endDateTime = strtotime($endDate);
            if (false === $startDateTime || false === $endDateTime) {
                throw new RuntimeException('Google 全天事件的日期格式無效，無法同步。');
            }
            $dtStart = 'DTSTART;VALUE=DATE:' . date('Ymd', $startDateTime);
            $dtEnd = 'DTEND;VALUE=DATE:' . date('Ymd', $endDateTime);
        } else {
            $startValue = isset($start['dateTime']) ? $start['dateTime'] : '';
            $endValue = isset($end['dateTime']) ? $end['dateTime'] : $startValue;
            $startDateTime = strtotime($startValue);
            $endDateTime = strtotime($endValue);
            if (false === $startDateTime || false === $endDateTime) {
                throw new RuntimeException('Google 事件的日期時間格式無效，無法同步。');
            }
            $timezone = date_default_timezone_get();
            $tz = self::timezone($timezone);
            $startLocal = new \DateTime('@' . $startDateTime);
            $endLocal = new \DateTime('@' . $endDateTime);
            $startLocal->setTimezone($tz);
            $endLocal->setTimezone($tz);
            $dtStart = 'DTSTART;TZID=' . $timezone . ':' . $startLocal->format('Ymd\THis');
            $dtEnd = 'DTEND;TZID=' . $timezone . ':' . $endLocal->format('Ymd\THis');
        }

        $recurrence = '';
        if (!empty($googleEvent['recurrence'])) {
            $rules = [];
            foreach ($googleEvent['recurrence'] as $rule) {
                if (0 === strpos($rule, 'RRULE:')) {
                    $rules[] = $rule;
                } else {
                    throw new RuntimeException('Google 重複事件包含 tad_cal 尚不支援的 recurrence 規則：' . $rule);
                }
            }
            if ($rules) {
                $recurrence = $dtStart . "\n" . $dtEnd . "\n" . implode("\n", $rules);
            }
        }

        $updated = isset($googleEvent['updated']) ? strtotime($googleEvent['updated']) : time();
        return [
            'title' => isset($googleEvent['summary']) ? $googleEvent['summary'] : '(未命名事件)',
            'start' => $recurrence ? '0000-00-00 00:00:00' : date($allDay ? 'Y-m-d' : 'Y-m-d H:i:s', $startDateTime),
            'end' => $recurrence ? '0000-00-00 00:00:00' : date($allDay ? 'Y-m-d' : 'Y-m-d H:i:s', $endDateTime),
            'recurrence' => $recurrence,
            'location' => isset($googleEvent['location']) ? $googleEvent['location'] : '',
            'details' => isset($googleEvent['description']) ? $googleEvent['description'] : '',
            'allday' => $allDay ? 1 : 0,
            'last_update' => date('Y-m-d H:i:s', $updated),
        ];
    }

    private static function deleteLocalFromGoogle($cateSn, $sn)
    {
        global $xoopsDB;

        Utility::query(
            'DELETE FROM `' . $xoopsDB->prefix('tad_cal_event') . '` WHERE `sn`=? AND `cate_sn`=?',
            'ii',
            [$sn, $cateSn]
        ) or Utility::web_error('', __FILE__, __LINE__);
        Utility::query('DELETE FROM `' . $xoopsDB->prefix('tad_cal_repeat') . '` WHERE `sn`=?', 'i', [$sn]) or Utility::web_error('', __FILE__, __LINE__);
    }

    private static function clearLocalCategory($cateSn)
    {
        global $xoopsDB;

        $eventTable = $xoopsDB->prefix('tad_cal_event');
        $repeatTable = $xoopsDB->prefix('tad_cal_repeat');
        $mappingTable = $xoopsDB->prefix('tad_cal_google_event');
        Utility::query("DELETE FROM `{$repeatTable}` WHERE `sn` IN (SELECT `sn` FROM `{$eventTable}` WHERE `cate_sn`=?)", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
        Utility::query("DELETE FROM `{$eventTable}` WHERE `cate_sn`=?", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
        Utility::query("DELETE FROM `{$mappingTable}` WHERE `cate_sn`=?", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
    }

    private static function saveMapping($cateSn, $localSn, $googleId, $googleUpdated, $localBaseline, $syncPending = 0)
    {
        global $xoopsDB;

        $table = $xoopsDB->prefix('tad_cal_google_event');
        $sql = "INSERT INTO `{$table}` (`cate_sn`,`local_sn`,`google_event_id`,`google_updated`,`local_baseline`,`sync_pending`)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE `local_sn`=VALUES(`local_sn`),`google_event_id`=VALUES(`google_event_id`),
            `google_updated`=VALUES(`google_updated`),`local_baseline`=VALUES(`local_baseline`),`sync_pending`=VALUES(`sync_pending`)";
        Utility::query($sql, 'iisssi', [
            $cateSn,
            $localSn ? $localSn : null,
            $googleId,
            $googleUpdated ? $googleUpdated : null,
            $localBaseline ? $localBaseline : null,
            $syncPending,
        ]) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    private static function mappingByLocal($cateSn, $localSn)
    {
        global $xoopsDB;

        $table = $xoopsDB->prefix('tad_cal_google_event');
        $result = Utility::query("SELECT * FROM `{$table}` WHERE `cate_sn`=? AND `local_sn`=?", 'ii', [$cateSn, $localSn]) or Utility::web_error('', __FILE__, __LINE__);
        return $xoopsDB->fetchArray($result);
    }

    private static function mappingByGoogle($cateSn, $googleId)
    {
        global $xoopsDB;

        $table = $xoopsDB->prefix('tad_cal_google_event');
        $result = Utility::query("SELECT * FROM `{$table}` WHERE `cate_sn`=? AND `google_event_id`=?", 'is', [$cateSn, $googleId]) or Utility::web_error('', __FILE__, __LINE__);
        return $xoopsDB->fetchArray($result);
    }

    private static function deleteMapping($cateSn, $googleId)
    {
        global $xoopsDB;

        $table = $xoopsDB->prefix('tad_cal_google_event');
        Utility::query("DELETE FROM `{$table}` WHERE `cate_sn`=? AND `google_event_id`=?", 'is', [$cateSn, $googleId]) or Utility::web_error('', __FILE__, __LINE__);
    }

    private static function calendarConfig($cateSn)
    {
        global $xoopsDB;

        $table = $xoopsDB->prefix('tad_cal_google_calendar');
        $result = Utility::query("SELECT * FROM `{$table}` WHERE `cate_sn`=?", 'i', [$cateSn]) or Utility::web_error('', __FILE__, __LINE__);
        return $xoopsDB->fetchArray($result);
    }

    private static function saveCalendar($cateSn, $calendarId, $refreshToken, $syncToken, $initialSync, $lastSync)
    {
        global $xoopsDB;

        $table = $xoopsDB->prefix('tad_cal_google_calendar');
        $sql = "INSERT INTO `{$table}` (`cate_sn`,`calendar_id`,`refresh_token`,`sync_token`,`initial_sync`,`last_sync`)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE `calendar_id`=VALUES(`calendar_id`),`refresh_token`=VALUES(`refresh_token`),
            `sync_token`=VALUES(`sync_token`),`initial_sync`=VALUES(`initial_sync`),`last_sync`=VALUES(`last_sync`)";
        Utility::query($sql, 'isssss', [$cateSn, $calendarId, $refreshToken, $syncToken, $initialSync, $lastSync ? $lastSync : null]) or Utility::web_error($sql, __FILE__, __LINE__);
    }

    private static function eventsUrl($calendar)
    {
        return 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($calendar['calendar_id']) . '/events';
    }

    private static function apiRequest($calendar, $method, $url, $body = null)
    {
        $key = $calendar['cate_sn'];
        if (!isset(self::$accessTokens[$key])) {
            global $xoopsModuleConfig;

            self::assertConfigured();
            $response = self::httpRequest('https://oauth2.googleapis.com/token', 'POST', [
                'client_id' => $xoopsModuleConfig['google_client_id'],
                'client_secret' => $xoopsModuleConfig['google_client_secret'],
                'refresh_token' => self::decrypt($calendar['refresh_token']),
                'grant_type' => 'refresh_token',
            ], false);
            if (empty($response['access_token'])) {
                throw new RuntimeException('Google OAuth 更新授權失敗。');
            }
            self::$accessTokens[$key] = $response['access_token'];
        }

        $headers = ['Authorization: Bearer ' . self::$accessTokens[$key]];
        if (null !== $body) {
            $headers[] = 'Content-Type: application/json';
        }
        return self::httpRequest($url, $method, $body, true, $headers);
    }

    private static function httpRequest($url, $method, $body = null, $json = true, $headers = [])
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL 擴充未啟用，無法連接 Google Calendar API。');
        }

        $curl = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if (null !== $body) {
            $options[CURLOPT_POSTFIELDS] = $json ? json_encode($body) : http_build_query($body);
        }
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (false === $response) {
            throw new RuntimeException('連線 Google API 失敗：' . $curlError);
        }
        $decoded = '' === $response ? [] : json_decode($response, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded) && isset($decoded['error']['message']) ? $decoded['error']['message'] : $response;
            throw new RuntimeException("Google API 回應 HTTP {$status}：{$message}");
        }
        if (!is_array($decoded) && '' !== $response) {
            throw new RuntimeException('Google API 回傳了無法解析的資料。');
        }

        return is_array($decoded) ? $decoded : [];
    }

    private static function assertConfigured()
    {
        global $xoopsModuleConfig;

        if (empty($xoopsModuleConfig['google_client_id']) || empty($xoopsModuleConfig['google_client_secret'])) {
            throw new RuntimeException('請先在 tad_cal 模組偏好設定填入 Google OAuth 用戶端 ID 與密鑰。');
        }
    }

    private static function redirectUri()
    {
        return XOOPS_URL . '/modules/tad_cal/google_sync.php';
    }

    private static function encrypt($value)
    {
        if (!function_exists('openssl_encrypt') || !defined('XOOPS_DB_PASS')) {
            throw new RuntimeException('需要 OpenSSL 與 XOOPS 資料庫密碼設定，才能安全儲存 Google 授權。');
        }
        $key = hash('sha256', XOOPS_DB_PASS, true);
        $iv = random_bytes(12);
        $tag = '';
        $cipherText = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (false === $cipherText) {
            throw new RuntimeException('無法加密 Google refresh token。');
        }

        return base64_encode($iv . $tag . $cipherText);
    }

    private static function decrypt($value)
    {
        if (!function_exists('openssl_decrypt') || !defined('XOOPS_DB_PASS')) {
            throw new RuntimeException('需要 OpenSSL 與 XOOPS 資料庫密碼設定，才能讀取 Google 授權。');
        }
        $raw = base64_decode($value, true);
        if (false === $raw || strlen($raw) < 28) {
            throw new RuntimeException('Google 授權資料格式錯誤，請重新連結行事曆。');
        }
        $plainText = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', hash('sha256', XOOPS_DB_PASS, true), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if (false === $plainText) {
            throw new RuntimeException('無法解密 Google 授權資料，請重新連結行事曆。');
        }

        return $plainText;
    }

    private static function mysqlDate($date)
    {
        $timestamp = $date ? strtotime($date) : false;
        return false === $timestamp ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private static function timezone($name)
    {
        if (!in_array($name, timezone_identifiers_list(), true)) {
            throw new RuntimeException('不支援的時區：' . $name);
        }

        return new \DateTimeZone($name);
    }

    private static function isMissingEventError(RuntimeException $error)
    {
        return false !== strpos($error->getMessage(), 'HTTP 404') || false !== strpos($error->getMessage(), 'HTTP 410');
    }
}
