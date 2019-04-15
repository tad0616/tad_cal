<?php
xoops_loadLanguage('modinfo_common', 'tadtools');

define('_MI_TADCAL_NAME', 'Tad Calendar');
define('_MI_TADCAL_AUTHOR', 'Tad');
define('_MI_TADCAL_CREDITS', 'Tad');
define('_MI_TADCAL_DESC', 'XOOPS Calendar module that can be combined with Google calendar');
define('_MI_TADCAL_ADMENU1', 'Calendar Mgmt');
define('_MI_TADCAL_BNAME1', 'Small calendar');
define('_MI_TADCAL_BDESC1', 'Small calendar (tad_cal_calendar)');
define('_MI_TADCAL_BNAME2', 'Recent events');
define('_MI_TADCAL_BDESC2', 'Recent events(tad_cal_list)');
define('_MI_TADCAL_BNAME3', 'Big calendar');
define('_MI_TADCAL_BDESC3', 'Big calendar (tad_cal_full_calendar)');

define('_MI_TADCAL_EVENTSHOWMODE', 'Event Display Mode');
define('_MI_TADCAL_EVENTSHOWMODE_DESC', 'What time is set after the events calendar to be displayed under');
define('_MI_TADCAL_CONF0_OPT1', 'Click event');
define('_MI_TADCAL_CONF0_OPT2', 'Mouse moved out of date automatically displays the event');
define('_MI_TADCAL_EVENTTHEME', 'Event Windows scenery');
define('_MI_TADCAL_EVENTTHEME_DESC', 'Set the event presented Windows look and feel');
define('_MI_TADCAL_CONF1_OPT1', 'Sun yellow');
define('_MI_TADCAL_CONF1_OPT2', 'Dawn White');
define('_MI_TADCAL_CONF1_OPT3', 'Midnight black');
define('_MI_TADCAL_CONF1_OPT4', 'Sunset red');
define('_MI_TADCAL_CONF1_OPT5', 'Sky Blue');
define('_MI_TADCAL_CONF1_OPT6', 'Green grass');
define('_MI_TADCAL_TITLE_NUM', 'Event title words');
define('_MI_TADCAL_TITLE_NUM_DESC', 'Presented in the calendar title Limit');
define('_MI_TADCAL_QUICK_ADD', 'Whether to open quickly add events function?');
define('_MI_TADCAL_QUICK_ADD_DESC', 'If you choose "Yes", then when you click on the calendar date, there will be a new events menu.');
define('_MI_TADCAL_SYNC_CONUT', 'Is executed several times per calendar automatically synchronize remote calendar');
define('_MI_TADCAL_SYNC_CONUT_DESC', 'To replace the troublesome scheduling capabilities, built-in counter, the number of calendar page is executed calculations, each to a certain number of times to automatically perform remote calendar synchronization. (setting of 0 indicates this feature is not enabled ) <br> smaller the number, the more often synchronization sync, it will take a few seconds. ');

define('_MI_CAL_START', 'Calendar start week');
define('_MI_CAL_START_DESC', 'Chosen to start the day before or on Monday as the first day of the week');
define('_MI_TADCAL_SU', 'Sunday');
define('_MI_TADCAL_MO', 'Monday');

define('_MI_TADCAL_DIRNAME', basename(dirname(dirname(__DIR__))));
define('_MI_TADCAL_HELP_HEADER', __DIR__ . '/help/helpheader.tpl');
define('_MI_TADCAL_BACK_2_ADMIN', 'Back to Administration of ');

//help
define('_MI_TADCAL_HELP_OVERVIEW', 'Overview');
