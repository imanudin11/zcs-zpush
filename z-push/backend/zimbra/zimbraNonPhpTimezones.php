<?php
/***********************************************
* File          :   zimbraNonPhpTimezones.php
* Revision      :   1 (12-Dec-2015)
* Project       :   Z-Push Zimbra Backend
*                   http://sourceforge.net/projects/zimbrabackend
* Description   :   Translation table for non-recognized timezone names to PHP timezone names in the Z-Push Zimbra Backend.
*
* Credits       :   Vincent Sherwood
************************************************

To add support for a new timezone, simply copy the template line below and paste it into the array code below. 
Then change the 'logged timezone' string to whatever name (changed to all lowercase if necessary) is logged to the z-push debug log file, 
and change the 'php timezone' to the name (using correct mixed case) of the closest matching timezone you can find on the official php 
timezones list (http://php.net/manual/en/timezones.php) bearing in mind some countries/cities within a region may have different daylight
savings time rules.


$tzLookupList['logged timezone (lowercase)'] = 'php region/timezone (Mixed/Case)';

************************************************/

$tzLookupList['western european'] = 'Europe/London';
$tzLookupList['western european time'] = 'Europe/London';
