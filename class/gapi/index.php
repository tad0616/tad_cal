<?php


/* Base code provided by Sarah Bailey. Case Western Reserve University, Cleveland OH. scb89@case.edu. */


//TO DEBUG UNCOMMENT THESE LINES
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

//INCLUDE THE GOOGLE API PHP CLIENT LIBRARY FOUND HERE https://github.com/google/google-api-php-client. DOWNLOAD IT AND PUT IT ON YOUR WEBSERVER IN THE ROOT FOLDER.
include('autoload.php');

//SET THE DEFAULT TIMEZONE SO PHP DOESN'T COMPLAIN. SET TO YOUR LOCAL TIME ZONE.
date_default_timezone_set('Asia/Taipei');

//TELL GOOGLE WHAT WE'RE DOING
$client = new Google_Client();
$client->setApplicationName("My Calendar"); //DON'T THINK THIS MATTERS
$client->setDeveloperKey('AIzaSyCk7fQpA3WRB3NtyYFTxrGB9wu484qDdsY'); //GET AT AT DEVELOPERS.GOOGLE.COM
$cal = new Google_Service_Calendar($client);
//THE CALENDAR ID, FOUND IN CALENDAR SETTINGS. IF YOUR CALENDAR IS THROUGH GOOGLE APPS, YOU MAY NEED TO CHANGE THE CENTRAL SHARING SETTINGS. THE CALENDAR FOR THIS SCRIPT MUST HAVE ALL EVENTS VIEWABLE IN SHARING SETTINGS.
$calendarId = 'tad0616@gmail.com';
//TELL GOOGLE HOW WE WANT THE EVENTS
$params = array(
  'singleEvents' => true, //CAN'T USE TIME MIN WITHOUT THIS, IT SAYS TO TREAT RECURRING EVENTS AS SINGLE EVENTS
  'orderBy' => 'startTime',
  'timeMin' => date(DateTime::ATOM),//ONLY PULL EVENTS STARTING TODAY
  
);
$events = $cal->events->listEvents($calendarId);

echo '<ol>';
while (true) {
    foreach ($events->getItems() as $event) {
        echo '<li>title: '.$event->summary;
        echo '<ul>';
        echo '<li>start: '.$event->start->dateTime.'</li>';
        echo '<li>end: '.$event->end->dateTime.'</li>';
        echo '<li>recurrence: ';
        if (is_array($event->recurrence)) {
            echo '<ul>';
            foreach ($event->recurrence as $key => $value) {
                echo "<li>{$key} => {$value}</li>";
            }
            echo '</ul>';
        }
        echo '</li>';
        echo '<li>recurringEventId: '.$event->recurringEventId.'</li>';

        echo '<li>location: '.$event->location.'</li>';
        echo '<li>kind: '.$event->kind.'</li>';
        echo '<li>details: '.$event->description.'</li>';
        echo '<li>etag: '.$event->etag.'</li>';
        echo '<li>id: '.$event->id.'</li>';
        echo '<li>sequence: '.$event->sequence.'</li>';
        echo "</ul>";
        echo "</li>";
    }
    $pageToken = $events->getNextPageToken();
    if ($pageToken) {
        $optParams = array('pageToken' => $pageToken);
        $events = $cal->events->listEvents($calendarId, $optParams);
    } else {
        break;
    }
}

echo '</ul>';

/*
foreach ($events->getItems() as $event) {

    $eventDateStr = $event->start->dateTime;

    if(empty($eventDateStr)){
      $eventDateStr = $event->start->date;
    }

    $temp_timezone = $event->start->timeZone;

    if (!empty($temp_timezone)) {
      $timezone = new DateTimeZone($temp_timezone); //GET THE TIME ZONE
    } else {
      $timezone = new DateTimeZone("Asia/Taipei");
    }

    $eventdate = new DateTime($eventDateStr,$timezone);

    // $newyear = $eventdate->format("Y");
    // $newmonth = $eventdate->format("m");//CONVERT REGULAR EVENT DATE TO LEGIBLE MONTH
    // $newday = $eventdate->format("d");//CONVERT REGULAR EVENT DATE TO LEGIBLE DAY


    //echo "{$newyear}-{$newmonth}-{$newday}:";

    //echo '<li>alternateLink: '.$event->htmlLink.'</li>';
    echo '<li>title: '.$event->summary.'</li>';
    echo '<li>start: '.$event->start->datetime.'</li>';
    echo '<li>end: '.$event->end->datetime.'</li>';
    echo '<li>recurrence: '.$event->recurrence.'</li>';
    echo '<li>recurringEventId: '.$event->recurringEventId.'</li>';

    echo '<li>location: '.$event->location.'</li>';
    echo '<li>kind: '.$event->kind.'</li>';
    echo '<li>details: '.$event->description.'</li>';
    echo '<li>etag: '.$event->etag.'</li>';
    echo '<li>id: '.$event->id.'</li>';
    echo '<li>sequence: '.$event->sequence.'</li>';
    echo "<hr>";
}
*/





  
