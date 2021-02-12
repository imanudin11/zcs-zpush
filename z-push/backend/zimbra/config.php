<?php
/***********************************************
* File        :   config.php
* Project     :   Z-Push
* Description :   BackendZimbra configuration file
*
* Created     :   21.03.2016
* Modified    :   15.12.2016 - Added ZIMBRA_SSL_VERIFYHOST & ZIMBRA_SSL_VERIFYPEER
*                 07.10.2017 - Added ZIMBRA_DISABLE_BIRTHDAY_SYNC
*
* Copyright   :   Vincent Sherwood
************************************************/

    // **********************
    // BackendZimbra settings
    // **********************

    // The ZIMBRA_URL should be set to the value of the setting publicURL on the zimbra 
    // server configuration. This should allow requests to the mail server to be properly 
    // routed/proxied as appropriate by zimbra. This is particularly important for 
    // attachments/images to be able to be viewed on the synced devices.
	// Assuming that z-push is not running on the same IP address as zimbra, if you are
	// running zimbra 8.0 or later, you will need to whitelist the IP Address of the z-push
	// server to avoid connection issues. See http://wiki.zimbra.com/wiki/DoSFilter
	// for 8.0.0-8.0.2 see http://www.zimbra.com/forums/announcements/60397-zcs-dosfilter-workaround-zcs-8-0-1-8-0-2-a.html
    // Note that there should be no trailing slash (/) on the end of the ZIMBRA_URL setting
    // and if using an IP address see setting ZIMBRA_DISABLE_URL_OVERRIDE below
    // To configure the ZimbraBackend uncomment the appropriate line below and customize as
    // required.
//    define('ZIMBRA_URL', 'http://zimbraServerName');
    define('ZIMBRA_URL', 'https://localhost');
//    define('ZIMBRA_URL', 'http://127.0.0.1');  
//    define('ZIMBRA_URL', 'https://127.0.0.1');  

    // By default the zimbra backend does not enforce certificate validation for connections 
    // to zimbra as many people run zimbra FOSS using self-signed certificates. If verification 
    // is required uncomment the following options and set the values to true as required. 
    // These flags were added for PHP 5.6 and later compatability as the PHP default changed to
    // enforcing validation for cURL/stream contexts. If these flags are not set or are set to
    // false the behaviour of the zimbra backend will be as it was prior to that PHP change.	
//  define('ZIMBRA_SSL_VERIFYPEER', true);
//  define('ZIMBRA_SSL_VERIFYHOST', true);

    // When using external LDAP authentication zimbra can send a redirect page when the user
    // attempts to login. In this case for z-push to work it is necessary to enable the option
    // ZIMBRA_URL_ALLOW_REDIRECT. Simply uncomment the line below to enable it.	
//	define('ZIMBRA_URL_ALLOW_REDIRECT', true); 

    // The default behaviour of the ZimbraBackend is to override whatever URL is provided in
    // the ZIMBRA_URL setting above with the value of publicURL returned from zimbra when the
    // user logon is processed. If for any reason this needs to be overridden for your 
    // environment such as you want Z-Push to connect to an internal IP Address directly
    // you can disable this behaviour by specifying the setting ZIMBRA_DISABLE_URL_OVERRIDE to
    // true. Simply uncomment the line below to disable the behaviour. 
//	define('ZIMBRA_DISABLE_URL_OVERRIDE', true); 

    // On many zimbra FOSS sites the server is stopped each night to facilitate the running of
    // a cold sync final backup. This typically means that zimbra can be unavailable for a
    // number of minutes. The setting ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR attempts to mitigate
    // the potential issues with locking out accounts/dropping data from devices/etc. by 
    // holding open login sessions and retrying the authentication a number of times at 60
    // second intervals. The default setting allows for 5 minutes of downtime. 
	define('ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR',2);

    // If the ZimbraBackend is being setup to use the old style XML configuration files
    // a folder must be specified where these files will reside. The ZIMBRA_USER_DIR 
    // setting specifies the name fo the folder which is expected to reside in the root 
    // z-push folder. It will default to the name 'zimbra' if this setting is not defined.
    // For any user who requires a configuration file, the file must be named for the 
    // user's login name with a .xml extension. 
//	define('ZIMBRA_USER_DIR', 'zimbra');

    // There is also the option of providing a global configuration file to be used by all 
	// users by specifying the setting ZIMBRA_USER_XML_DEFAULT. The option below can be
    // uncommented - and the filename can be changed if desired.
    // Certain options such as sendasname, sendasemail, username, etc cannot be specified 
    // in a default file.	
//	define('ZIMBRA_USER_XML_DEFAULT','default.xml');

    // For the many devices that only support a single folder of each type for Contacts,
    // Calendar, Tasks, and Notes the ZimbraBackend includes the ability to 'Virtually 
    // Include' the data from the user's other folders into their main folder for syncing.
    // As far as the device is concerned the Contacts/Appointments/etc will all have come 
    // from that one main folder for each item type. This Virtual functionality is enabled
    // by default below. It can be disabled for any folder type by commenting out the 
    // appropriate setting    
	define('ZIMBRA_VIRTUAL_CONTACTS',true);
	define('ZIMBRA_VIRTUAL_APPOINTMENTS',true);
	define('ZIMBRA_VIRTUAL_TASKS',true);
	define('ZIMBRA_VIRTUAL_NOTES',true);

    // The following settings are system-wide switches to prevent syncing of different 
    // folder types. These settings will override any other settings for that folder type.
//    define('ZIMBRA_DISABLE_MESSAGES',true); 
//    define('ZIMBRA_DISABLE_CONTACTS',true); 
//    define('ZIMBRA_DISABLE_APPOINTMENTS',true); 
//    define('ZIMBRA_DISABLE_TASKS',true); 
//    define('ZIMBRA_DISABLE_NOTES',true); 
//    define('ZIMBRA_DISABLE_DOCUMENTS',true);      

    // Some devices, such as all Apple devices, allow multiple folders of each type to be 
    // synced with devices. If it is desired to prevent all users from syncing multiple 
    // folders (other than emails) then each folder type can have that capability disabled
    // by uncommenting the appropriate line from the settings below and setting it to true.
//    define('ZIMBRA_DISABLE_MULTI_CALENDARS',true);
//    define('ZIMBRA_DISABLE_MULTI_TASK_LISTS',true);
//    define('ZIMBRA_DISABLE_MULTI_TASK_LISTS',true); 
//    define('ZIMBRA_DISABLE_MULTI_NOTE_LISTS',true); 


    // Zimbra by default captured email addresses the user's have written to in a folder
    // named 'Emailed Contacts'. Allowing this folder to sync to the devices can result in 
    // many duplicate email contacts appearing on the device. For this reason this setting 
    // ZIMBRA_IGNORE_EMAILED_CONTACTS is true by default. To disable it uncomment the line
    // below and set it to false
//	define('ZIMBRA_IGNORE_EMAILED_CONTACTS',true);

    // The setting ZIMBRA_SYNC_CONTACT_PICTURES controls whether z-push is allowed to sync 
    // contact pictures to the device. The default setting is false. To enable it simply
    // uncomment the next line and set it to true
	define('ZIMBRA_SYNC_CONTACT_PICTURES', true); 

	
    // For zimbra users who deal with internal character encodings the default encoding 
    // detection setting can be overridden. Uncomment the line below and adjust the
    // set of encodings as necessary being careful about the sequence in which they are 
    // specified. See http://php.net/manual/en/function.mb-detect-encoding.php for
    // details aying particular attention to notes on detection order. If the line below
    // is commented out the default setting of 'ASCII, UTF-8, ISO-8859-1, ISO-8859-15'
    // is used.
//	  define('ZIMBRA_MB_DETECT_ORDER', 'ASCII, UTF-8, ISO-8859-1, ISO-8859-15' );

    // With SmartFolders user's have the possibility to define alternative email addresses
    // from which to send emails. To ensure that they only send them from one of their 
    // configured aliases you can set ZIMBRA_ENFORCE_VALID_EMAIL to true. With this setting
    // in place the backend will limit the user to the list of aliases configured in zimbra
	define('ZIMBRA_ENFORCE_VALID_EMAIL', true);

    // SmartFolders is a feature to allow the user to limit the folders that will sync to 
    // their device, as well as to set certain configuration settings through the use of
    // special folder names. ZIMBRA_SMART_FOLDERS defaults to true if the setting is not
    // configured. Note that it must be set to false if the system is to be configured to
    // use XML configuration files. 
    // The primary feature is the ability to limit folders to be synchronized. If the user
    // ends a folder name with a '-' character that folder and any sub-folders will not be
    // announced to the device. If the user ends a folder name with a '.' the folder itself 
    // will be announced to the device but it's sub-folders will not. The user simply renames
    // the folder through the zimbra web client for the change to take effect.
    // See the INSTALL file for the additional configuration options available.	
	define('ZIMBRA_SMART_FOLDERS',true);

    // The local cache is a list of the folders on the system, together with a list of the 
    // metadata for the items in each synced folder. It is populated by calls to the zimbra 
    // backend and then saved for use by subsequent requests from the device to try to minimise
    // the number of zimbra calls z-push needs to make. ZIMBRA_LOCAL_CACHE is enabled by default
    // but can be disabled by uncommenting the next line and setting it to false.
//    define('ZIMBRA_LOCAL_CACHE', true);

    // The ZIMBRA_LOCAL_CACHE when enabled gets refreshed periodically just in case any change 
    // was missed by the ChangesSink process. The default lifetime of the folder content lists
    // in the cache is one hour (3600 seconds). To change the default value, uncomment the next
    // line and change the value as appropriate. 
//    define('ZIMBRA_LOCAL_CACHE_LIFETIME', 3600);


    // By default most devices send meeting invitation replies directly to the organizer in 
    // addition to responding to the server. When a response is sent to the server it can 
    // include a flag to tell the server to generate a response to the organizer on behalf of
    // the attendee. The setting ZIMBRA_SERVER_INVITE_REPLY controls whether the server will 
    // send a reply for the attendee. The default setting is false which leaves it to the 
    // device to send the replies as otherwise meeting organizers would generally get duplicate
    // replies. If your devices need the server to send replies for them then simply uncomment 
    // the next line and set it to true.
//    define('ZIMBRA_SERVER_INVITE_REPLY', true);

    // Some older android clients had issues with syncing birthdays which would result in 
    // constant contact sync loops and battery drain. A User Agent check was added to the code
    // to only sync birthdays to Apple and Nokia devices at the time. Newer releases of android
    // clients no longer have this issue so the limiting code has been removed. A new setting 
    // ZIMBRA_DISABLE_BIRTHDAY_SYNC has been added to allow for the sync to be turned off if
    // any devices in your environment are still experience issues. To disable the syncing of
    // birthdays, uncomment the next line and set it to true.
//    define('ZIMBRA_DISABLE_BIRTHDAY_SYNC', false);
	
    // In addition to z-push logging, the ZimbraBackend has some additional detailed logging for
    // SOAP requests to zimbra sand the responses back from the server. There is also some 
    // extended logging available for the folder selection functions to help with debugging.
    // Note that in order for the logging to appear it is necessary to have the z-push log level
    // set to DEBUG or higher. 
    // Possible values for ZIMBRA_DEBUG are:
    // true - zimbra additional logging is enabled - calls from all users will be logged 
    // false - zimbra additional logging is disabled (default)
    // 'setup' - only the additional logging in the folder selection functions is enabled
    // 'username' - zimbra additional logging is enabled for one user - username
    // 'user1,user2,user3,etc' - zimbra additional logging is enabled for the list of users
//    define('ZIMBRA_DEBUG',true);
//    define('ZIMBRA_DEBUG','setup');
//    define('ZIMBRA_DEBUG','username');
//    define('ZIMBRA_DEBUG','vincents@itsolutions.ie,joeb');
//    define('ZIMBRA_DEBUG','vincents@itsolutions.ie,vincents');
//    define('ZIMBRA_DEBUG','vincents');
//    define('ZIMBRA_DEBUG','itsadmin@itsolutions.ie');
	
    // ZIMBRA_HTML is a legacy setting that enabled HTML emails on Apple devices (an any others
    // that advertized mimesupport) when using ActiveSync protocol level 2.5 or lower. Newer
    // protocol levels allow the client to specify which body types they will accept so this 
    // setting is not relevant for them. It should be left set to true in case any very old  
    // devices are being synced
	define('ZIMBRA_HTML',true);
	
	// In case Function Overload is being detected for mbstring functions we set the define
	// to the overload level so that we can handle binary data properly...
	define('MBSTRING_OVERLOAD', (extension_loaded('mbstring') ? ini_get('mbstring.func_overload') : false));
