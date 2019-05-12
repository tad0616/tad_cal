<?php
xoops_loadLanguage('admin_common', 'tadtools');

if (!defined('_TAD_NEED_TADTOOLS')) {
define('_TAD_NEED_TADTOOLS', "This module needs TadTools module. You can download TadTools from <a href='http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50' target='_blank'>Tad's web</a>.");
}
define('_MA_TADCAL_CATE_TITLE', 'Calendar Title');
define('_MA_TADCAL_CATE_SORT', 'Calendar sorting');
define('_MA_TADCAL_CATE_ENABLE', 'Is enabled');
define('_MA_TADCAL_ENABLE_GROUP', 'Group allowed to view');
define('_MA_TADCAL_ENABLE_UPLOAD_GROUP', 'Group allowed to submit events');
define('_MA_TADCAL_GOOGLE_ID', 'sign into Google Account');
define('_MA_TADCAL_GOOGLE_ID_NOTE', '(complete Email)');
define('_MA_TADCAL_GOOGLE_PASS', 'login Google password');
define('_MA_TADCAL_CATE_FORM', 'Calendar settings');
define('_MA_TADCAL_CATE_GFORM', 'Import Google Calendar');
define('_MA_TADCAL_CATE_GTITLE', 'Google calendar name');
define('_MA_TADCAL_CATE_GNUM', 'Number of events');
define('_MA_TADCAL_ALL_OK', 'Open to all');
define('_MA_TADCAL_CURL_NEED', 'Your system does not currently support curl function, in order to use Google Calendar set of related functions. <br> Linux systems install php5-curl kit as long as you can. <br> under PHP in Windows under setting an example (php directory Uniform Server in "UniServer\\usr\\local\\php"; XAMPP was "xampp\\php"): <ol> <li> open php.ini php directory, will "extension=php_curl.dll" remove the semicolon before. </li> <li> copy ssleay32.dll and libeay32.dll php directory to the next Windows\\system32. </li> <li> the php directory extensions\\php_curl.dll copied to the Windows\\system32 </li> <li> restart Apache. </li> </ol> ');
define('_TAD_ADD_CAL', 'New Calendar');
define('_MA_TADCAL_NEW_CALENDAR', 'Website Calendar');
define('_TAD_ADD_GOOGLE', 'Import Google Calendar');
define('_MA_TADCAL_GOOGLE_IMPORT', 'Event synchronization');
define('_MA_TADCAL_CATE_EVENTS_AMOUNT', 'Number of events');
define('_MA_TADCAL_GOOGLE_IMPORT_OK', 'Event synchronization is complete!');
define('_MA_TADCAL_GOOGLE_EXIST', 'already exists');
define('_MA_TADCAL_CATE_COLOR', 'Text color');
define('_MA_TADCAL_CATE_BGCOLOR', 'Background color');
define('_MA_TADCAL_LAST_UPDATE', 'Last updated');
define('_MA_TADCAL_ALL_SYNC', 'Full sync');
define('_MA_TADCAL_NEXT', 'Next');
define('_MA_TADCAL_NO_GOOGLE_CAL', 'No Google Calendar');
define('_MA_TADCAL_FUNCTION_CLOSED', 'This feature is temporarily closed');
