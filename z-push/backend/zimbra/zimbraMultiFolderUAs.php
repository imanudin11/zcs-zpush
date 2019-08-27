<?php
/***********************************************
* File          :   zimbraMultiFolderUAs.php
* Revision      :   5 (11-Oct-2017)
* Project       :   Z-Push Zimbra Backend
*                   http://sourceforge.net/projects/zimbrabackend
* Description   :   Per UserAgent string configuration for multi-folder support in the Z-Push Zimbra Backend.
*
* Credits       :   Vincent Sherwood
************************************************

To add support for a new device, simply copy the template line below and paste it into the array code below. Then change the 'ua' string and true/false values as appropriate.

For the 'ua' string, a partial string can be used as in the case of 'Apple' which matches all apple devices (Apple-iPhone3C1/1104.257,Apple-iPad2C1/1208.143,etc) regardless of 
firmware revision and 'Android/5' which matches to all Android/5 standard email clients (Android/5.0.2-EAS-2.0, etc)

It is OK to re-order the lines below according to the devices in use in your installation. It is best to have the devices in order of popularity - starting with the most popular. 

$multiFolderList[] = array( 'ua'=>'NewDevice',         'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );

************************************************/

$multiFolderList[] = array( 'ua'=>'Apple',             'message'=>true,  'contact'=>true,  'appointment'=>true,  'task'=>true,  'note'=>true  );
$multiFolderList[] = array( 'ua'=>'Android-SAMSUNG',   'message'=>true,  'contact'=>true,  'appointment'=>true,  'task'=>false, 'note'=>false  );
$multiFolderList[] = array( 'ua'=>'Android/7',         'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );
$multiFolderList[] = array( 'ua'=>'Android/6',         'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );
$multiFolderList[] = array( 'ua'=>'Android/5',         'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );
$multiFolderList[] = array( 'ua'=>'Android/4.4',       'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );
$multiFolderList[] = array( 'ua'=>'Nine',              'message'=>true,  'contact'=>true,  'appointment'=>true,  'task'=>true,  'note'=>true  );
$multiFolderList[] = array( 'ua'=>'Outlook',           'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>true  );
$multiFolderList[] = array( 'ua'=>'WindowsMail',       'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );
$multiFolderList[] = array( 'ua'=>'MSFT-WP/10',        'message'=>true,  'contact'=>false, 'appointment'=>true,  'task'=>true,  'note'=>false  );

