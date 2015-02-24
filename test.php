<?php
include_once "header.php";

require_once 'class/google/Google_Client.php';
require_once 'class/google/contrib/Google_CalendarService.php';

const CLIENT_ID = '254265660934-5k573s5j9geaugun1ia6oj57jpua63qb.apps.googleusercontent.com';
const SERVICE_ACCOUNT_NAME  = '254265660934-5k573s5j9geaugun1ia6oj57jpua63qb@developer.gserviceaccount.com';
const MY_EMAIL  = 'tad0616@gmail.com';
const KEY_FILE = 'tadcal-56fe2c6636d0.p12';

$client = new Google_Client();
$client->setClientId(CLIENT_ID);
$client->setApplicationName("tad cal");
$client->setUseObjects(true); //IF USING SERVICE ACCOUNT (YES)


if (isset($_SESSION['token'])) {
$client->setAccessToken($_SESSION['token']);
}


$key = file_get_contents(KEY_FILE);
$client->setAssertionCredentials(new Google_AssertionCredentials(
SERVICE_ACCOUNT_NAME, 'https://www.google.com/calendar/feeds/tad0616@gmail.com/private/full/',
$key)
);



$cal = new Google_CalendarService($client);
$calList = $cal->calendarList->listCalendarList();

print "<h1>Calendar List</h1><pre>" . print_r($calList, true) . "</pre>";


?>