<?php
$GLOBALS['revision'] = "69"; // Used to output the script version to the debug log
/***********************************************
* File          :   zimbra.php
* Revision      :   69 (8-Jan-2020)
* Project       :   Z-Push Zimbra Backend
*                   https://sourceforge.net/projects/zimbrabackend
* Description   :   A backend for Z-Push to use with the Zimbra Collaboration Suite,
*                   including the Open Source Edition.
*
* Copyright     :   Vincent Sherwood
*                   Grant Nosbush
*                   Mathias Kolb
*                   Julien Laurent
*
* Changes       :   Changes Made To Revision 69: z-push-2 version ONLY
*                     - Added descriptive WARN message for unavailable shared folder
*                     - In isZimbraObjectInSyncInterval treat no response as false
*                     - Fix processing of zimbraMailAlias to handle string if exactly one alias
*                     - Fix ChangeMessage to strip input Categories from shared folder items
*                     - Renamed constructor function of mime.php, mimePart.php and z_RTF.php
*                     - Added hash to Primary folder stats to improve virtual folder change detection
*                     - Added logic to clear cache on Logoff where folder changes are detected
*                     - Updated comment and removed extra debug logging from recent fixes
*
*                   Changes Made To Revision 68: z-push-2 version ONLY
*                     - Rename BackendSearchZimbra constructor for PHP 7+ compatability
*                     - Add third parameter to definition of GetGALSearchResults() for Z-Push 2.4
*                     - Added " around $zimbraFolderId for Task/Note in GetNextMessageBlock
*
*                   Changes Made To Revision 67: z-push-2 version ONLY
*                     - Allow for iPxx device meeting modification request with shadow data
*                     - Changed ZIMBRA_DEBUG logging of folder setup to only fire on word 'setup'
*                     - Move GetMailboxSearchResults() Log line to avoid warning on no folder Id
*                     - Tidy up logging in GetMailboxSearchResults()
*                     - Add debug logging of unidentified incoming Timezone in ChangeMessage()
*                     - unset $preModAppt->recurrence->premodtype after using it to fix type
*                     - Log error and return false if GetMsgResponse is not present in response
*                     - Add config.php option ZIMBRA_DISABLE_BIRTHDAY_SYNC
*                     - Don't allow difference in dtstamp of appointment exceptions cause an issue
*                     - Keep original Organizer for Tasks in ChangeMessage
*                     - Check for service.PROXY_ERROR in Login and ChangesSink in multi-server setup 
*                     - If no Change Token is returned from CreateWaitSet then delay and return
*                     - Updated comment on FakeOutbox
*
*                   Changes Made To Revision 66: z-push-2 version ONLY
*                     - Use zimbraHttpStreamWrapper class to output MIME body
*                     - Log ERROR if setting "zimbraAttachmentsBlocked" is "TRUE" 
*                     - Fix passing header on GetFolder for shared folders generates new session
*                     - Replace split() with explode() for PHP 7 compatability
*                     - Refactored GetMessageList() to improve efficiency and reduce memory needed
*                     - Added config.php options ZIMBRA_SSL_VERIFYPEER and ZIMBRA_SSL_VERIFYHOST
*                     - Added SSL Verify Peer and Host options to zimbraHttpStreamWrapper class 
*
*                   Changes Made To Revision 65: z-push-2 version ONLY
*                     - Added zimbraHttpStreamWrapper class to provide attachment length to streamer
*                     - If inv[0] of a Calendar item is an Exception then don't treat it as one
*                     - Fixed issue with timezone identification where DST is not observed
*                     - Fixed issue with population of Timezone object in function GetTz()
*                     - Added handling of Company Main Phone field and nickName
*                     - Added readonly parameter to Setup() for z-push 2.3 support
*                     - Updated GetInvIDFromMsgID to retrieve InvID from Message for exceptions
*                     - Commented out some debug logging
*                     - Incorrect variable name used in unlink command - Check for orphaned files
*
*                   Changes Made To Revision 64: z-push-2 version ONLY
*                     - Correct path to backend multi-folder support file for autodiscover
*                     - Identify character encoding of Attachment name and re-use when forwarding
*                     - Remove closing PHP tag from files 
*                     - Remove unused experimental function CustomRowCmp
*                     - Set X-Forwarded-For header to Request::$RemoteAddr if available
*                     - Added _ua string 'MSFT-WP/10' to the zimbraMultiFolderUAs.php file
*                     - Save folder permissions in folder array/cache
*                     - If shared calendar with write permision do not output Organizer 
*                     - Added _ua string 'Android/6' to the zimbraMultiFolderUAs.php file
*                     - Add try/catch around call to ZPush::GetDeviceManager()->GetUserAgent()
*                     - Support new names for Z-Push official ResolveRecipients classes
*                     - Added code to handle SyncBaseBody as a stream for Z-Push 2.3 and later
*                     - For deleted recurring meetings set meetingstatus to null - don't unset it
*                     - For recurring meetings do not output meetingstatus on exceptions in AS 2.5
*                     - Updated Out-Of-Office to handle different External messages
*                     - Handle both Autodiscover and Browser Tests when setting the _ua string
*                     - Allow a default user XML file to be used for all users
*                     - Add support for category changing to messages and to _cachedMessageLists
*                     - Renamed constructor function from BackendZimbra to __construct
*                     - Removed //IGNORE//TRANSLIT from $params array in SendMail
*                     - Added new config setting 'ZIMBRA_URL_ALLOW_REDIRECT' for OPEN LDAP auth
*                     - Added SmartFolders flag to cache to detect changes and invalidate cache 
*                     - Added stats field to _folders for use by new FolderStats functionality
*                     - Added extra GetFolder calls to get data on Shared Folders for FolderStats
*                     - Some minor config file settings tidy-up
*
*                   Changes Made To Revision 63: z-push-2 version ONLY
*                     - Ensure meetingstatus is output correctly for main and exceptions
*                     - Only output attendeestatus to the meeting organizer
*                     - Change 'Android/5.0' to 'Android/5' as a multi-folder capable _ua string
*                       in order to match Android/5.1 also 
*                     - Since z-push 2.2.2 the class StringStreamWrapper has been moved and 
*                       pre-included from index.php - Check before including it from old location
*                     - Use new exception constant SyncCollections::HIERARCHY_CHANGED
*                     - Trap additional HTML errors in SoapRequest to prevent removing content
*                     - Add checking for HTML errors in functions Logon and ChangesSink
*                     - Fix ChangeFolder function so create/rename/delete/move all work
*                     - Changed SendMail filter to keep the original body in more cases
*                     - Add neg="1" flag to incoming appointment alarms
*                     - Check for Request class in Logon function before setting client variables
*                     - Removed reference to Request class from function SoapRequest
*                     - Reworked Multi-Folder support adding a new configuration file
*                     - Check for existence of DiffState::RowCmp before calling it
*                     - Use DeviceManager function GetUserAgent if available
*
*                   Changes Made To Revision 62: z-push-2 version ONLY
*                     - Add 'Android/5.0' as a multi-folder capable _ua string
*                     - Report actual UserAgent, partial DeviceID, and IP Address in headers 
*                     - Remove X-Mailer-Connector header from SendMail
*                     - Output meetingstatus on Exceptions too
*                     - Output recurrence type 1 for zimbra "DAI"ly appointments that are weekly
*                     - Added X-Forwarded-For HTTP header to CURL options
*                     - Wrap subject with htmlspecialchars in 3 places it was overlooked
*                     - Add check for class ZPushAutodiscover to initial host version check
*                     - Additional check added to functions GetAttachmentData and
*                       ItemOperationsGetAttachmentData to allow for Sub-Folder of Shared folder
*                     - Disable document access if Class SyncDocumentLibraryDocument does not exist
*                     - Fix in MakeXMLTree for case where tag has no attributes
*                     - Add required use of new curl_file_create function for PHP 5.5 and later
*                     - Output the Organizer Name/Email on Appointments if available
*
*                   Changes Made To Revision 61: z-push-2 version ONLY
*                     - Remove forcing CURLOPT_SSLVERSION to 3 (to avoid SSLv3 POODLE issue)
*                     - Fix SendMailSenderFix email address for condition where no from header
*                       and full email address used as username
*                     - Throw exception on SOAP FAULT - service.AUTH_EXPIRED to force re-auth
*                     - Fix initialization of _userFolderTypeActive based on GetInfoResponse
*                     - Add ZIMBRA_DISABLE_DOCUMENTS setting to Config File notes below
*                     - Added function GetUserDetails needed for AutoDiscover feature 
*                     - Fix check for zimbraPrefFromDisplay and zimbraPrefFromAddress
*                     - Use configured zimbraPrefFromAddress as sender email address if different 
*                       from account name
*                     - Fix clear SendAsNameOverride, SendAsEmailOverride, ServerInviteReply from 
*                       cache
*
*
*                   Changes Made To Earlier Revisions:
*                       See "Release Notes.txt"
*
*
* Config File   :   === Replace ===
*                   $BACKEND_PROVIDER = "BackendZimbra";
*
*                   === Add the appropriate directives from below as required for your setup ===
*                   === Url to access zimbra server - no trailing slash ===
*                   The ZIMBRA_URL should be set to the value of the setting publicURL on the zimbra 
*                   server configuration. This should allow requests to the mail server to be properly 
*                   routed/proxied as appropriate by zimbra.
*
*                   define('ZIMBRA_URL', 'http[s]://<zimbra url>');
*
*                   By default the zimbra backend does not enforce certificate validation for connections 
*                   to zimbra as many people run zimbra FOSS using self-signed certificates. If verification 
*                   is required set the following options to true. 
*                   define('ZIMBRA_SSL_VERIFYPEER', false);
*                   define('ZIMBRA_SSL_VERIFYHOST', false);
*
*                   By default, if the configured ZIMBRA_URL does not match the zimbraPublicURL then
*                   it will be overridden by the zimbraPublicURL. If there is some overriding reason
*                   to prevent this from happening (I can't think of one) then this can be disabled 
*                   by adding the directive 
*                   define('ZIMBRA_DISABLE_URL_OVERRIDE', true); 
*                   to the zimbra backend config.php file
*
*                   === If you intend to use user.XML files
*                   define('ZIMBRA_USER_DIR', 'zimbra');
*                   AND - As of Release 57 - you will need to disable SmartFolders (see below)
*                   define('ZIMBRA_SMART_FOLDERS',false);
*
*                   === To enable sync of contact pictures ===
*                   define('ZIMBRA_SYNC_CONTACT_PICTURES', true);
*
*                   === To enable virtual contacts/appointments/tasks ===
*                   ActiveSync clients by default only allows a single Calendar, Contacts folder, 
*                   and Tasks folder to sync to the device. Apple have found a way around this and
*                   natively support multiple folders. If you enable VIRTUAL folders with these 
*                   these directives, then all appointments from other calendars for example will 
*                   be virtually included in the primary Calendar so they will sync to the device. 
*                   On zimbra they will remain separate. There is no way on the device to distinguish
*                   which appointments came from which Calendar folder. 
*                   define('ZIMBRA_VIRTUAL_CONTACTS',true);
*                   define('ZIMBRA_VIRTUAL_APPOINTMENTS',true);
*                   define('ZIMBRA_VIRTUAL_TASKS',true);
*                   define('ZIMBRA_VIRTUAL_NOTES',true);
*
*                   === To prevent sync of the "emailed contacts" folder
*                   define('ZIMBRA_IGNORE_EMAILED_CONTACTS',true);
*
*                   === To enable HTML email for MIME supporting devices - mainly Apple iPxxx ===
*                   define('ZIMBRA_HTML',true);
*
*                   === To ensure that the device uses a valid zimbra email address for the account ===
*                   define('ZIMBRA_ENFORCE_VALID_EMAIL',true);
*
*                   NOTE: As of Release 57 - Smart Folders are now ENABLED BY DEFAULT
*                   === To DISABLE the Smart Folders feature ===
*                   NOTE: Disabling SmartFolders will ALLOW user.XML processing
*                         It will also rename/resequence folder ID's in the state file for
*                         the device - so some resyncing will occur
*                   define('ZIMBRA_SMART_FOLDERS',false); 
*
*                   === To Disable Sync of any Folder Type System-wide ===
*                   Add the appropriate definition from this block, and set it true
*                   NOTE: Devices will need a full-resync to remove the unwanted content
*                   define('ZIMBRA_DISABLE_MESSAGES',true); 
*                   define('ZIMBRA_DISABLE_CONTACTS',true); 
*                   define('ZIMBRA_DISABLE_APPOINTMENTS',true); 
*                   define('ZIMBRA_DISABLE_TASKS',true); 
*                   define('ZIMBRA_DISABLE_NOTES',true); 
*                   define('ZIMBRA_DISABLE_DOCUMENTS',true); 
*
*                   === To Enable Login Retries When Host Cannot Be Contacted ===
*                   If, for example, you shutdown zimbra to perform maintenance or backups
*                   and you find your clients (android in particular) are just giving up 
*                   too quickly on trying to reconnect, enable this setting and set it to
*                   whatever value is appropriate for your environment.
*                   A setting of 5 means zimbra.php will make 5 retry attempts, each one
*                   following a 60 second sleep. If all 6 attempts fail an error will be
*                   returned to the phone. The phone should then retry a number of times 
*                   itself. Monitor thread usage to make sure you don't run too many. 
*                   define('ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR',5);
*
*                   === To Enable Debug Information for ALL users ===
*                   This will output the Folder Lists from Setup() and the SOAP Requests/Responses
*                   define('ZIMBRA_DEBUG',true); 
*
*                   === To Enable Debug Information for selected user(s) ===
*                   define('ZIMBRA_DEBUG','user1,user2'); 
*
*                   === To Disable Debug Information ===
*                   define('ZIMBRA_DEBUG',false); 
*                   or comment out the line entirely
*
*                   === To Define a Custom MB String Detect Order ===
*                   In case the default mb_detect_order of 'ASCII, UTF-8, ISO-8859-1, ISO-8859-15'
*                   does not work for your region (eg Japan) you can override it with this setting
*                   adding your own encodings in the appropriate sequence. See PHP documentation for
*                   mb_detect_order for details. Be careful of the sequence of charsets.
*                   define('ZIMBRA_MB_DETECT_ORDER','ASCII, ISO-2022-JP, UTF-8, ISO-8859-1, ISO-8859-15'); 
*
*
*                   === FOR z-push-2 ONLY - Local Message Cache ===
*                   For z-push-2 added local caching of MessageLists to reduce server load on
*                   initial sync where the same unchanged lists will be queried repeatedly 
*                   within a short space of time. The default is for this feature to be enabled
*                   with each folder cache having a lifetime of 3600 seconds.
*                   
*                   This feature is enabled by default - but can be disabled by adding a new 
*                   config.php directive 
*                   define('ZIMBRA_LOCAL_CACHE', false);
*                   
*                   The cache for each folder has a default lifetime of 3600 seconds. This can 
*                   be adjusted	by adding an additional directive
*                   define('ZIMBRA_LOCAL_CACHE_LIFETIME', 300);
*
*
*                   === To Enable Sending of Calendar Invites/Replies ===
*                   For z-push-2 added a configuration parameter to specify if the server should
*                   send Calendar Invites/Replies. As most modern devices now send the invitations
*                   and replies directly to the addressees, this is assumed to be false. In order to 
*                   change the setting, add the following parameter to the zimbra backend config.php 
*                   and set the value to true. This parameter replaces the old logic that made the 
*                   decision based on the client being Apple or not. 
*                   (Note: With SmartFolders this setting can be overridden per device - see below)
*                   define('ZIMBRA_SERVER_INVITE_REPLY', true);
*
*
* Feature Disable:  Several users have asked over time if it is possible to turn off Email sync or
*                   Calendar sync for an entire installation. Up to now it has only been possible
*                   through a hack to force all users to read a particular XML file.
*                   
*                   This release makes it possible to turn off individual folder types through the 
*                   config file. Enabling any of these switches will turn off that folder type (for 
*                   example email) for every user of the system. It will prevent all users from 
*                   syncing their email to their mobile devices.
*                   
*                   The default setting remains that these switches are either not present - or are 
*                   set to false - in which case all folder types will be available for synching.
*                   
*                   === To Disable Sync of any Folder Type System-wide ===
*                   Add the appropriate definition from this block to config.php, and set it true
*                   NOTE: Devices will need a full-resync to remove the unwanted content
*                   define('ZIMBRA_DISABLE_MESSAGES',true); 
*                   define('ZIMBRA_DISABLE_CONTACTS',true); 
*                   define('ZIMBRA_DISABLE_APPOINTMENTS',true); 
*                   define('ZIMBRA_DISABLE_TASKS',true); 
*                   define('ZIMBRA_DISABLE_NOTES',true); 
*                   define('ZIMBRA_DISABLE_DOCUMENTS',true); 
*                   
*                   === To Disable the breaking out of Calendars and Contact Groups on devices ===
*                   Prior to Release 57 it was only Apple devices that were known to support the display 
*                   of multiple zimbra calendars - each with it's own colouring. 
*                   Release 57 now also enabled multi Calendar/Task folder support for Outlook 2013 and
*                   the Windows 8+ WindowsMail client. These clients do not support multiple Contact
*                   groups though - so virtual contact still need to be used for them.
*                   By default, if an Apple device is identified, Calendars/Contacts & Tasks in custom
*                   folders will be passed to the device in separate calendars/folders. This allows the
*                   device user to select what they want to display. 
*                   In order to prevent devices synching many different folders, the administrator can 
*                   disable the support of multiple calendars and/or multiple contact folder using these 
*                   configuration settings. 
*                   define('ZIMBRA_DISABLE_MULTI_CALENDARS',true); 
*                   define('ZIMBRA_DISABLE_MULTI_CONTACT_GROUPS',true); 
*                   define('ZIMBRA_DISABLE_MULTI_TASK_LISTS',true); 
*                   define('ZIMBRA_DISABLE_MULTI_NOTE_LISTS',true); 
*                   Note that if any 'Multi' feature is disabled, the behaviour of the backend will return to 
*                   that which was in place before the breakout ability was added. i.e. all items will get
*                   virtually included in the primary folder (assuming virtual support is turned on)
*
*                   === To Disable the syncing of birthday fields to devices ===
*                   Some older android clients had issues with syncing birthdays which would result in 
*                   constant contact sync loops and battery drain. A User Agent check was added to the code
*                   to only sync birthdays to Apple and Nokia devices at the time. Newer releases of android
*                   clients no longer have this issue so the limiting code has been removed. A new setting 
*                   ZIMBRA_DISABLE_BIRTHDAY_SYNC has been added to allow for the sync to be turned off if
*                   any devices in your environment are still experience issues. To disable the syncing of
*                   birthdays set the config.php directive 
*                   define('ZIMBRA_DISABLE_BIRTHDAY_SYNC', true);
*
* SmartFolders:     Starting from Release 57 - SmartFolders is enabled by default. You must specifically
*                   disable the feature if you wish to use XML files.
*                   
*                   Historically in the zimbra backend, there has been the ability to manipulate the 
*                   number and types of folders that could be synced for an individual user through the  
*                   use of user.XML configuration files. While this worked well, it meant that the system 
*                   administrator had to be involved in every change for every user. 
*                   
*                   The introduction of SmartFolders as an alternative to XML files made it easy for users
*                   to manipulate their syncing content themselves by simply renaming folders in the Zimbra
*                   Web Client.
*                   
*                   Note that SmartFolders and XML files are mutually exclusive on the server. As of Release
*                   57 SmartFolders MUST BE DISABLED in order to allow the use of user.XML files. 
*                   
*                   To DISABLE the feature set ZIMBRA_SMART_FOLDERS to false in config.php
*                   
*                   When enabled, the final character in a folders name can take on special meanings as 
*                   follows :-
*                    "-" Do not include this folder or any sub-folders thereof
*                    "." Include this folder - But do not include any sub-folders thereof
*                   
*                   So for example you might have a top level folder called "Archive-" into which you  
*                   move all old folders that you do not want to be able to see from your device.
*                   
*                   Or you might have a "ToBeFiled." folder that contains a number of child folders used
*                   for longer term storage of reference emails. Naming it with a period (.) on the end
*                   will allow you to see that folder on your phone - so you can move emails in there to
*                   clear your Inbox - and then when you get to your desktop you can file all the emails
*                   into the appropriate child folders. 
*                   
*                   NOTE: As of Release 57 - Smart Folders are now ENABLED BY DEFAULT
*                   === To DISABLE the Smart Folders feature ===
*                   NOTE: Disabling SmartFolders will ALLOW user.XML processing
*                         It will also rename/resequence folder ID's in the state file for
*                         the device - so some resynching will occur
*                   define('ZIMBRA_SMART_FOLDERS', false); 
*                   
*                   User control of Folder types
*                   ----------------------------
*                   NOTE: z-push does not do hierarchy resync properly at this time - so if a user wishes 
*                   to make changes to their sync rules using the following directives, they should first
*                   remove the sync account from their phone - then make the changes - then re-add the 
*                   account to their phone. 
*                   
*                   When SmartFolders are configured, in instances where individual users wish to disable 
*                   particular folder types from syncing to their devices, a special top-level folder 
*                   structure can be used to configure those options. This provides the major filtering
*                   capability that would have been available through user.XML files. 
*                   
*                   If a folder named '*SyncConfig*' is found, the system will use any child folders in
*                   that folder to configure options. 
*                   
*                   The names of the sub-folders will be interpreted as configuration directives.
*                   
*                   These should have the format of 
*                   <folder type>&<setting=value>[&<setting=value>& ...]
*                   
*                   <folder type> can be any one of message, contact, appointment, task, note and 
*                   <setting=value> can be any one of 
*                   active=true/false - active=false will disable sync of that folder type
*                   virtual=true/false - virtual=false will turn of sync of additional folders (not
*                                        applicable for message type)
*                   primary=FolderName - setting primary will override the default primary folder for
*                                        that content type (Inbox, Contacts, Calendar, Tasks)
*                   
*                   So, for example, you could have 
*                   
*                   *SyncConfig* 
*                      message&active=false            to disable email sync
*                      task&active=true&virtual=false  to limit the task sync to the default primary folder
*                      appointment&active=true&virtual=false&primary=WorkCalendar  to limit 
*                                                       appointment sync to the WorkCalendar folder
*                      
*                   The *SyncConfig* folder, and it's contents will never be synced to the phone so
*                   long as ZIMBRA_SMART_FOLDERS is set to true.
*                   
*                   User control of Mobile Sender Name
*                   ----------------------------------
*                   In case the user's display name contains non-ascii characters, and it gets corrupted
*                   when sending emails from the device, a sendasname directive can be used to provide a
*                   MIME-encoded Sender Name to be used by the backend.
*
*                   To get the correctly formatted name, send yourself an email through the web
*                   client, and then right-click on the received email and Show Original. Look for
*                   the From: header and copy the name from there. Don't include the email address
*                   part - or any surrounding "" marks. Note that the encoding zimbra uses will most
*                   likely be utf-8 rather than ISO-8859-1 as seen in the following example
*
*                   For example 
*
*                   *SyncConfig* 
*                      sendasname&=?ISO-8859-1?Q?Andr=E9?= Pirard 
*                   The example above is taken from the rfc2047 document, and appears in a CC:
*                   CC: =?ISO-8859-1?Q?Andr=E9?= Pirard <PIRARD@vm1.ulg.ac.be>
*                   For reference: http://www.faqs.org/rfcs/rfc2047.html
*
*                   User control of Mobile Sender Email Address
*                   -------------------------------------------
*                   In case the user wishes to override their default email address when sending emails
*                   from the device, a sendasemail directive can be used to provide an alternative 
*                   email address
*
*                   For example 
*
*                   *SyncConfig* 
*                      sendasemail&john.doe@whatever.com
*
*                   Note: If ZIMBRA_ENFORCE_VALID_EMAIL is true, this address will be ignored if it
*                         has not been defined as a valid alias for the user on the zimbra server
*
*
*                   User control of Server Originated Calendar Invites/Replies
*                   ----------------------------------------------------------
*                   In case the user wishes to override the server setting for sending Calendar 
*                   Invites/Replies from the server they can add a serverinvitereply directive. 
*                   The user can then turn on or off the setting for one or more devices by 
*                   specifying each deviceID and a true/false value
*
*                   For example 
*
*                   *SyncConfig* 
*                      serverinvitereply&deviceid1=true&deviceid2=false
*
*                   It is only necessary to list devices for which the user wishes to override the 
*                   server default setting
*
*
* User Files    :   NOTE THAT USER FILES ARE MUTUALLY EXCLUSIVE FROM SMART FOLDERS
*
*                   The user files are to be saved in the location specified for ZIMBRA_USER_DIR.
*                   This must be a subdirectory for the base directory.  Each user file must be named
*                   using the user's user ID (not their email address) from Zimbra and have the file
*                   extension .xml.  Make sure the files have read permission.
*
*                   WARNING: Before changing the content of a User's XML file, set the sync schedule to
*                   manual on the device, or remove the profile entirely. And when finished, if the
*                   profile was not first removed then request a full-resync of the client before
*                   changing back to an automatic sync schedule.
*                   Explanation: User files effectively fool the device into thinking that some folders
*                   on the server that the device would normally see do not exist - AND/OR - that some
*                   folders that the device would not normally see are actually available to it. As a
*                   result, changing the contents of the XML file should not be done without doing a 
*                   full resync of the device. In the absence of doing this problems can occur that
*                   could lead to the loss of data on the server.
*                   For example - removing an include for a folder will make the device think the 
*                   folder has been deleted, and will trigger a delete for the client. This delete will
*                   cause a change-in-state on the client that will trigger a parallel delete on the
*                   server on the next sync. This will then delete the real server copy of that folder
*                   which would result in the loss of data.
*                   Making the changes offline, followed by a full-resync eliminates this risk.
*
*                   SendAsName and SendAsEmail are used for the organizer of appointments.
*
*                   Multiple profiles are supported for a single user.  For example, if a user has two
*                   devices and wants one set of folders and one particular calendar on one device and
*                   a different set of folders and a different calendar on the other, the user can
*                   accomplish this by using profiles.  To determine which profile the device users,
*                   specify the profile ID in the domain when setting up the account (See also roaming/
*                   timezone setting below).  Z-Push doesn't care what value is entered for a domain.
*                   If the ID is left blank for a profile, this is assumed to be the default.
*
*                   For each component, only use exclude or include, not both.  If exclude is used,
*                   all folders are included except those marked as excluded.  If include is used,
*                   only those folders specified are included.  The preference is to specify folders
*                   by their ID but since this isn't easy to do, name is acceptable.  However, it must
*                   be the same way througout: don't specify some folders by ID and others by name. If
*                   recursive is set to true, all child folders will be included/excluded.
*
*                   WARNING: If any folder names have an ampersand in them, you must use &amp;
*
*                   <zimbrabackend user="">
*                       <profile id="" usehtml="false" sendasname="" sendasemail="" timezone="">
*                           <message active="true|false">
*                               [ <include [id=""|name=""] [recursive="true|false"]/> | <exclude [id=""|name=""] [recursive="true|false"]/> ... ]
*                               [ <searchfolder [id=""|name=""] [recursive="true|false"]/> ... ]
*                           </message>
*                           <contact active="true|false" virtual="true|false">
*                               <primary [id=""|name=""]/>
*                               [ <include [id=""|name=""] [recursive="true|false"]/> | <exclude [id=""|name=""] [recursive="true|false"]/> ... ]
*                           </contact>
*                           <appointment active="true|false" virtual="true|false">
*                               <primary [id=""|name=""]/>
*                               [ <include [id=""|name=""] [recursive="true|false"]/> | <exclude [id=""|name=""] [recursive="true|false"]/> ... ]
*                           </appointment>
*                           <task active="true|false" virtual="true|false">
*                               <primary [id=""|name=""]/>
*                               [ <include [id=""|name=""] [recursive="true|false"]/> | <exclude [id=""|name=""] [recursive="true|false"]/> ... ]
*                           </task>
*                       </profile>
*                   </zimbrabackend>
*
*                   Release 64 added a new capability - to define a global settings file to be used by
*                   default for all users. For example adding the new setting
*                   define('ZIMBRA_USER_XML_DEFAULT','default.xml');
*                   to the zimbra config.php file will apply the settings in default.xml to every user
*                   unless they have a specific file created for their account. 
*                   Note that sendasname and sendasemail are not supported in the global file. Also, note
*                   that if a specific file is created for the user it will be used instead of the default 
*                   file - the settings are not additive.
*
* Timezones     :   To handle the situation where a user is not in the same timezone as the zimbra server
*                   whether just roaming, or on a permanent basis, the user can specify their current 
*                   timezone in the domain field when setting up the account (see also profile identifier
*                   above). Refer to the php timezones list (http://www.php.net/manual/en/timezones.php)
*                   Specify the preferred timezone in the domain field using @Region/City. 
*                   Note: If used in conjunction with the profile identifier, specify the combined
*                   information as profileId@Region/City
*
* Zimbra 5      :   The timezone handling built into this PHP script is for the new standard timezone
* Compatability :   definitions used by zimbra 6 - details as listed above. However, there are still some
*                   people using zimbra 5.0.xx and that version used a completely different methodology
*                   for handling timezones. Zimbra 5 shipped with a file called timezones.ics located in 
*                   /opt/zimbra/conf/ - this file contains definitions of all the usable timezones. 
*
*                   In order to provide compatability between this script and zimbra 5, a new lookup file
*                   has been added for 5.0 users called v5timezone.xml, which should be copied to the 
*                   z-push/backend/ folder with the zimbra.php script. The format of the file is
*                   <zimbratimezones>
*                   	<timezone	id="Europe/Dublin"	v5id="(GMT) Greenwich Mean Time - Dublin / Edinburgh / Lisbon / London" />
*                   	<timezone	id="Europe/London"	v5id="(GMT) Greenwich Mean Time - Dublin / Edinburgh / Lisbon / London" />
*                      	<timezone	id="Europe/Paris"	v5id="(GMT+01.00) Brussels / Copenhagen / Madrid / Paris" />
*                   </zimbratimezones>
*                   where id="" is the zimbra 6 timezone, and v5id="" is the timezones.ics equivalent 
*                   from zimbra 5.
*
*                   These are the only 3 timezones added to the shipped version of the file. If you want 
*                   add timezones for your particular region, please refer to both timezones.ics on your 
*                   zimbra 5 server, and http://www.php.net/manual/en/timezones.php to map the additional 
*                   timezones. If people want to post their timezones up on a thread on the support forum
*                   on sourceforge, we can possibly add additional ones to the shipped file going forward.
*
*                   In order to use the compatability fix, it must be possible for this script to find
*                   your preferred timezone in v6 format. So, it must be defined in the @Region/City 
*                   format in your domain field (see above) or in the Region/City format in a user.XML file
*                   (see above) or it must be possible to lookup your default in the v5timezone.xml file
*                   using your v5 preferred timezone as returned by zimbra. 
*
*                   Please search the log file for "NOT FOUND in v5timezone.xml" after turning on z-push
*                   with v5 in order to make sure you have defined all the timezones you need.
*
* RTF fields    :   To handle RTF fields in appointment/task/contact where no Body field is received
*                   zimbra.php requires z_RTF.php include file from as12 branch of z-push SVN to decode
*                   the Compressed RTF stream. This situation can commonly occur with non-English clients
*
* PHP.ini File  :   === Replace ===
*                   max_execution_time = 120      (Maximum execution time of each script, in seconds)
*                   memory_limit = 256M           (Maximum amount of memory a script may consume)
*
* Dependencies  :   php-curl
*
* References    :   http://www.plymouth.edu/webapp/code/zimbra.class.phps
*
* This file is distributed under GPL v2.
* Consult LICENSE file for details
************************************************/

// Basic version checking to ensure users do not try to use the 1.5 backend with z-push-2, or the z-push-2 backend with 1.5
// For z-push-2 we make sure Request class exists, or throw an error if it doesn't. For 1.5 we make sure Request class does not exist.
if (!class_exists("Request") && !class_exists("ZPushAutodiscover")) {
    ZLog::Write(LOGLEVEL_FATAL,  "FATAL: Zimbra Backend Release " . $GLOBALS['revision'] . " only works with z-push 2.n.n - Please verify you have matching z-push and zimbra backend versions" );
    return false;
}

require_once('backend/zimbra/config.php');
include_once('lib/default/diffbackend/diffbackend.php');
include_once('include/mimeDecode.php');
require_once('include/z_RFC822.php');
include_once('lib/default/backend.php');
include_once('backend/zimbra/z_RTF.php');
require_once('backend/zimbra/mime.php');
include_once('backend/zimbra/zimbraHttpStreamWrapper.php');

if ((defined('ZIMBRA_DETECT_RUSSIAN')) && (ZIMBRA_DETECT_RUSSIAN === true)) {
    if (!file_exists(BASE_PATH . "/include/" . "a.charset.php")) {
        ZLog::Write(LOGLEVEL_ERROR,  ' ' );
        ZLog::Write(LOGLEVEL_ERROR,  'ERROR: ZIMBRA_DETECT_RUSSIAN defined as TRUE but REQUIRED include file <z-push base dir>/include/a.charset.php IS NOT FOUND' );
        ZLog::Write(LOGLEVEL_ERROR,  '       You MUST either TURN OFF ZIMBRA_DETECT_RUSSIAN in config.php - OR - Place the include file in the /include folder' );
        ZLog::Write(LOGLEVEL_ERROR,  '       The file a.charset.php can be found in SVN at https://sourceforge.net/projects/zimbrabackend/files/misc/ ' );
        ZLog::Write(LOGLEVEL_ERROR,  ' ' );
	} else include_once('a.charset.php');
}

class BackendZimbra extends BackendDiff {

    public $_accountName;
    public $_accountRestURL;
    private $mainUser;
    private $defaultstore;
    protected $store;
    private $storeName;

    public $_user;
    public $_protocolversion;

    protected $_connected = false;
    protected $_timestamp;
    public $_folders = array();
    public $_documentLibraries = array();
    public $_documentLibrariesPathToIdIndex = array();

//    protected $_devidToIndex = array();
    protected $_idToIndex = array();

    protected $_virtual = array();
    protected $_primary = array();
    protected $_firstNote = false;
    protected $_password = "";
    protected $_wasteID = false;
    protected $_ua;
    protected $_deviceIdForMailboxLog;        
    protected $_xFwdForForMailboxLog;        
	protected $_zimbraId = "";
	protected $_waitSetId = "";
	protected $_highestSeqKnown = "";

    protected $_changetoken = "";
    protected $_pingtokenOne = "";
    protected $_pingtimeOne = "";

    protected $_pingVirtualChanges = array( 'message'=>false, 'contact'=>false, 'appointment'=>false, 'task'=>false, 'document'=>false, 'wiki'=>false, 'note'=>false  );

    protected $_usertags = array();

    protected $_linkOwners = array();
		
    protected $_smartFolders = true;
    protected $_userFolderTypeActive = array( 'message'=>true, 'contact'=>true, 'appointment'=>true, 'task'=>true, 'document'=>true, 'wiki'=>false, 'note'=>true );
    protected $_deviceMultiFolderSupport = array( 'message'=>true, 'contact'=>false, 'appointment'=>false, 'task'=>false, 'document'=>true, 'wiki'=>false, 'note'=>false );
    protected $_serverInviteReply = false;
    protected $_ignoreEmailedContacts = true;
    protected $_disableBirthdaySync = false;

    protected $_localCache = true;
    protected $_localCacheLifetime = 3600; // default 1 hour - override in config.php if required
    protected $_clearCacheOnLogoff = false;
    protected $_saveCacheOnLogoff = false;
    protected $_cacheSupports = array('read','replied','forwarded','categories');
    protected $_shareIndex = array();

    protected $_soapDelayMicroSeconds = 0; // default no delay between SoapRequests (milliseconds)

    // ActiveSync => Zimbra
    protected $_contactMapping =
     array ('anniversary' => 'anniversary',         // ,anniversary_custom
            'assistantname' => 'assistantName',
            'assistnamephonenumber' => 'assistantPhone',
            'business2phonenumber' => 'workPhone2',
            'businesscity' => 'workCity',
            'businesscountry' => 'workCountry',
            'businessfaxnumber' => 'workFax',
            'businessphonenumber' => 'workPhone',    //,companyPhone
            'businesspostalcode' => 'workPostalCode',
            'businessstate' => 'workState',
            'businessstreet' => 'workStreet',
            'carphonenumber' => 'carPhone',
            'companymainphone' => 'companyPhone',
            'children' => 'Children',
            'companyname' => 'company',
            'department' => 'department',
            'email1address' => 'email',
            'email2address' => 'email2',
            'email3address' => 'email3',
            'firstname' => 'firstName',
            'home2phonenumber' => 'homePhone2',
            'homecity' => 'homeCity',
            'homecountry' => 'homeCountry',
            'homefaxnumber' => 'homeFax',
            'homephonenumber' => 'homePhone',
            'homepostalcode' => 'homePostalCode',
            'homestate' => 'homeState',
            'homestreet' => 'homeStreet',
            'imaddress' => 'imAddress1',
            'imaddress2' => 'imAddress2',
            'imaddress3' => 'imAddress3',
            'jobtitle' => 'jobTitle',
            'lastname' => 'lastName',
            'middlename' => 'middleName',
            'nickname' => 'nickName',
            'mobilephonenumber' => 'mobilePhone',
            'othercity' => 'otherCity',
            'othercountry' => 'otherCountry',
            'otherpostalcode' => 'otherPostalCode',
            'otherstate' => 'otherState',
            'otherstreet' => 'otherStreet',
            'pagernumber' => 'pager',
            'radiophonenumber' => 'otherPhone',
            'spouse' => 'spouse',                  //,spouse_custom
            'suffix' => 'nameSuffix',
            'title' => 'title',
            'webpage' => 'workURL',                // ,homeURL,otherURL     
            'birthday' => 'birthday',              // birthday_custom
            'picture' => 'image',
            'body' => 'notes'
           );


    /**
     * Constructor of the Zimbra Backend
     *
     * @access public
     */
    public function __construct() {
//        $this->session = false;
        $this->store = false;
        $this->storeName = false;
        $this->notifications = true;
        $this->changesSink = false;
        $this->changesSinkFolders = array();
        $this->changesSinkStores = array();
    }


    /**
     * Indicates which AS version is supported by the backend.
     * By default AS version 2.5 (ASV_25) is returned (Z-Push 1 standard).
     * Subclasses can overwrite this method to set another AS version
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        return ZPush::ASV_14; 
    }

    /** Logon
     */
    public function Logon($username, $domain, $password) {


        if (class_exists("Request")) {
            $this->_ua = Request::GetUserAgent();
            if (Request::GetDeviceID()) {
                if (is_callable(array('DeviceManager','GetUserAgent'))) {
                    try {
                        // Device Manager will have stored the UA from the initial Settings command (if not a browser test)
                        $this->_ua = ZPush::GetDeviceManager()->GetUserAgent();
                    } catch (Exception $e) {
                        ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . "FatalNotImplementedException: IGNORE THIS ERROR if performing BROWSER TEST ");
                        // Likely a browser test or possibly an initial sync - UA best guess already set from Request
                    }
                }
                $this->_deviceIdForMailboxLog = '(...'.substr(Request::GetDeviceID(),-6).')';
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . "No Device ID available - Likely a Browser Test ...");
                $this->_deviceIdForMailboxLog = '(Browser Test)';        
            }
            $this->_protocolversion = Request::GetProtocolVersion();
            $this->_xFwdForForMailboxLog = Request::GetRemoteAddr();
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . "_xFwdForForMailboxLog ..." . $this->_xFwdForForMailboxLog);
        } else {
            $this->_ua = "Autodiscover";
            $this->_protocolversion = "N/A";
            $this->_deviceIdForMailboxLog = "(Autodiscover)";        
            $this->_xFwdForForMailboxLog = $_SERVER['REMOTE_ADDR'];
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'START Logon { username [' . $username . '] - domain [' . $domain . '] - password <hidden> - php ['.phpversion().'] - zpzb [' . $GLOBALS['revision'] .'] - ua ['.$this->_ua.'] - as ['.$this->_protocolversion.'] }' );

        $this->mainUser = $username;
 
        // Confirm PHP-CURL Installed; If Not, Exit
        if (!function_exists('curl_init')) {
            ZLog::Write(LOGLEVEL_FATAL, 'Zimbra->Logon(): ' . "FATAL: Zimbra Backend Requires PHP-CURL");
            return false;
        }

        if (function_exists("DiffState::RowCmp")) {
            // Detect required change of z-push/lib/default/diffbackend/diffstate.php function RowCmp() - Test should return 0
            $a["id"] = $b["id"] = 123456;
            if (DiffState::RowCmp( $a, $b) !== 0) {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . "ERROR: Zimbra Backend required change to z-push/lib/default/diffbackend/diffstate.php function RowCmp() is missing - see INSTALL notes for fix");
            }
        }

        // If SSL Verification is configured then use the setting from the config.php file
        if (defined('ZIMBRA_SSL_VERIFYPEER')) {
            $this->_sslVerifyPeer = $this->ToBool(ZIMBRA_SSL_VERIFYPEER);
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'ZIMBRA_SSL_VERIFYPEER is configured - Using ['.ZIMBRA_SSL_VERIFYPEER.']'  );
        } else $this->_sslVerifyPeer = false;
        if (defined('ZIMBRA_SSL_VERIFYHOST')) {
            $this->_sslVerifyHost = $this->ToBool(ZIMBRA_SSL_VERIFYHOST) ? 2 : 0;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'ZIMBRA_SSL_VERIFYHOST is configured - Using ['.ZIMBRA_SSL_VERIFYHOST.']'  );
        } else $this->_sslVerifyHost = false;


        // Confirm ZIMBRA_URL Defined
        if (defined('ZIMBRA_URL')) {
            $this->_publicURL = ZIMBRA_URL;
        } else $this->_publicURL = '';
        if (empty($this->_publicURL)) {
            ZLog::Write(LOGLEVEL_FATAL, 'Zimbra->Logon(): ' . "FATAL: Zimbra URL Not Defined in config.php - see INSTALL notes for details");
            return false;
        }
        
        if (defined('ZIMBRA_MB_DETECT_ORDER')) {
            $this->_mbDetectOrder = ZIMBRA_MB_DETECT_ORDER;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'ZIMBRA_MB_DETECT_ORDER override for default detection order - Using ['.$this->_mbDetectOrder.']'  );
        } else {
            $this->_mbDetectOrder = 'ASCII, UTF-8, ISO-8859-1, ISO-8859-15'; // Default mb_detect_order for ZIMBRA use
        }

        if (defined('ZIMBRA_HTML')) {
            $this->_useHTML = $this->ToBool(ZIMBRA_HTML);
        } else $this->_useHTML = false;

        // The default Multi-Folder support capability is set here. 
        $multiFolderSupport = array( 'ua'=>'DEFAULT',        'message'=>false, 'contact'=>false, 'appointment'=>false, 'task'=>false, 'note'=>false  );
        // Specific overrides must be added to the file backend/zimbra/zimbraMultiFolderUAs.php for additional supported UA strings to allow devices to grab multiple folders in a non-virtualized way
        $multiFolderSupportFile = str_replace('autodiscover/', '', BASE_PATH) . 'backend/zimbra/zimbraMultiFolderUAs.php';
        if (file_exists($multiFolderSupportFile)) {
            $multiFolderList = array();
            include_once($multiFolderSupportFile);
            if (defined('ZIMBRA_DEBUG')) {
                if (stripos(ZIMBRA_DEBUG, 'setup') !== false) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Multi-Folder List:' . print_r($multiFolderList, true) );
                }
            }
            $maxUAs = sizeof($multiFolderList);
            for ($i=0;$i<$maxUAs;$i++) {
                if (stripos($this->_ua, $multiFolderList[$i]['ua']) !== false) {
                    $multiFolderSupport = $multiFolderList[$i];
                    break;
                }
            }
        } else ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'Multi-Folder Support CONFIGURATION FILE NOT FOUND. Please ensure you have installed the file ['. $multiFolderSupportFile . '] ' );


        // NOTE: Multi-Mail Folder syncing is assumed available by default.
        $mfsText = 'Multi-Folder support configured using ['.$multiFolderSupport['ua'].'] with settings ';
        if (!$multiFolderSupport['appointment']) {
            $mfsText .= 'Calendar [NOT SUPPORTED]';
        } elseif (!defined('ZIMBRA_DISABLE_MULTI_CALENDARS') or (ZIMBRA_DISABLE_MULTI_CALENDARS !== true)) {
            $mfsText .= 'Calendar [SUPPORTED]';
            $this->_deviceMultiFolderSupport['appointment'] = true;
        } else {
            $mfsText .= 'Calendar [GLOBALLY DISABLED]';
        }
        if (!$multiFolderSupport['contact']) {
            $mfsText .= ', Contacts [NOT SUPPORTED]';
        } elseif (!defined('ZIMBRA_DISABLE_MULTI_CONTACT_GROUPS') or (ZIMBRA_DISABLE_MULTI_CONTACT_GROUPS !== true)) {
            $mfsText .= ', Contacts [SUPPORTED]';
            $this->_deviceMultiFolderSupport['contact'] = true;
        } else {
            $mfsText .= ', Contacts [GLOBALLY DISABLED]';
        }
        if (!$multiFolderSupport['task']) {
            $mfsText .= ', Tasks [NOT SUPPORTED]';
        } elseif (!defined('ZIMBRA_DISABLE_MULTI_TASK_LISTS') or (ZIMBRA_DISABLE_MULTI_TASK_LISTS !== true)) {
            $mfsText .= ', Tasks [SUPPORTED]';
            $this->_deviceMultiFolderSupport['task'] = true;
        } else {
            $mfsText .= ', Tasks [GLOBALLY DISABLED]';
        }
        if (!$multiFolderSupport['note']) {
            $mfsText .= ', Notes [NOT SUPPORTED]';
        } elseif (!defined('ZIMBRA_DISABLE_MULTI_NOTE_LISTS') or (ZIMBRA_DISABLE_MULTI_NOTE_LISTS !== true)) {
            $mfsText .= ', Notes [SUPPORTED]';
            $this->_deviceMultiFolderSupport['note'] = true;
        } else {
            $mfsText .= ', Notes [GLOBALLY DISABLED]';
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  $mfsText );


        if (defined('ZIMBRA_SMART_FOLDERS')) {
            $this->_smartFolders = ($this->ToBool(ZIMBRA_SMART_FOLDERS) === true);
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  "Setting _smartFolders to true BY DEFAULT - add  define('ZIMBRA_SMART_FOLDERS',false); to the zimbra config.php file to use XML files"); 
            $this->_smartFolders = true;
        }

        if (defined('ZIMBRA_SERVER_INVITE_REPLY')) {
            $this->_serverInviteReply = ($this->ToBool(ZIMBRA_SERVER_INVITE_REPLY) === true);
            if ($this->_serverInviteReply) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Setting _serverInviteReply for server to ['.$this->_serverInviteReply.']'); 
            }
        }

        if (defined('ZIMBRA_IGNORE_EMAILED_CONTACTS')) {
            $this->_ignoreEmailedContacts = ($this->ToBool(ZIMBRA_IGNORE_EMAILED_CONTACTS) === true);
            if (!$this->_ignoreEmailedContacts) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Setting _ignoreEmailedContacts for server to [false]'); 
            }
        }

        if (defined('ZIMBRA_DISABLE_BIRTHDAY_SYNC')) {
            $this->_disableBirthdaySync = ($this->ToBool(ZIMBRA_DISABLE_BIRTHDAY_SYNC) === true);
            if ($this->_disableBirthdaySync) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Setting _disableBirthdaySync to [true]'); 
            }
        }

        if (!class_exists("Request")) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'No Request Class available - DISABLING Local Cache - Likely an Autodiscover Request ...' );
            $this->_localCache = false;
        } elseif (Request::GetDeviceID() == '') {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'No Device ID available - DISABLING Local Cache - Likely a Browser Test ...' );
            $this->_localCache = false;
        } elseif (Request::GetDeviceID() == 'validate') {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Device ID "validate" - DISABLING Local Cache - Android User Auth Test ...' );
            $this->_localCache = false;
        } else {
            if (defined('ZIMBRA_LOCAL_CACHE')) {
                $this->_localCache = ($this->ToBool(ZIMBRA_LOCAL_CACHE) !== false);
            }

            if ($this->_localCache) {
                if (defined('ZIMBRA_LOCAL_CACHE_LIFETIME')) {
                    if (intval(ZIMBRA_LOCAL_CACHE_LIFETIME) > 60) {
                        $this->_localCacheLifetime = intval(ZIMBRA_LOCAL_CACHE_LIFETIME);
                    }
                }
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Local Cache ENABLED with Lifetime ['.$this->_localCacheLifetime.'] seconds' );
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Local Cache DISABLED' );
            }
        }


        // Define Some Class Variables
        $this->_username = $username;
        $this->_domain = $domain;
//        $this->_password = $password;    // Fix to replace symbols in user passwords.
        $this->_password = str_replace(array("&", "\"", ">", "<" ), array("&amp;", "&quot;", "&gt;", "&lt;" ), $password);
        $this->_addresses = array();


        // Initialize Curl
        $this->_curl = curl_init();
        $this->_soapURL = $this->_publicURL.'/service/soap/';
        curl_setopt($this->_curl, CURLOPT_URL, $this->_soapURL);
        curl_setopt($this->_curl, CURLOPT_POST,           true);
        if (defined('ZIMBRA_URL_ALLOW_REDIRECT') && ($this->ToBool(ZIMBRA_URL_ALLOW_REDIRECT) === true)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ZIMBRA_URL_ALLOW_REDIRECT is TRUE in config.php - Redirection is ALLOWED' );
            curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($this->_curl, CURLOPT_POSTREDIR, 3);
        }
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
        // curl_setopt($this->_curl, CURLOPT_SSLVERSION, 3 ); Removed as it prevented negotiation of TLS - needed to avoid SSLv3 POODLE issue
        $http_header = array();
        $http_header[] = 'X-Forwarded-For: ' . $this->_xFwdForForMailboxLog;
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $http_header);

        // Check For Timezones In Domain Field ('@Region/City' Expected; Overrides Anything Else)
        $this->_tzFromDomain = false;
        $timezoneAt = strpos($domain, '@', 0);
        if ($domain != "" && $timezoneAt !== false) {
            $userTz = substr($domain, ($timezoneAt+1));
            $tempTz = date_default_timezone_get();
            if (date_default_timezone_set($userTz) === true) {
                $this->_tzFromDomain = true;
                $this->_tz = $userTz;
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'Provided Timezone ['.$userTz.'] is NOT VALID: Check list at (http://www.php.net/manual/en/timezones.php) ' );
            }
            date_default_timezone_set( $tempTz );
            $domain = substr( $domain, 0, $timezoneAt );
            $this->_domain = $domain;
        }

        // Login To Zimbra
		if ($this->_smartFolders) {
            $header  = '<context xmlns="urn:zimbra">
                            <session />
                            <notify  seq="0" />
                            <format type="js" /> 
                            <userAgent name="'.$this->_ua.$this->_deviceIdForMailboxLog.' devip='.$this->_xFwdForForMailboxLog.' ZPZB" version="'.$GLOBALS['revision'].'" />
                        </context>';
            $returnJSON = true;
        } else { // Not updating old XML code to use JSON
            $header  = '<context xmlns="urn:zimbra">
                            <session />
                            <notify  seq="0" />
                            <userAgent name="'.$this->_ua.$this->_deviceIdForMailboxLog.' devip='.$this->_xFwdForForMailboxLog.' ZPZB" version="'.$GLOBALS['revision'].'" />
                        </context>';
            $returnJSON = false;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ReturnJSON = false');		   
        }
        $body    = '<AuthRequest xmlns="urn:zimbraAccount">
                        <account by="name">'.$this->_username.'</account>
                        <password>'.$this->_password.'</password>
                        <attrs><attr name="uid"/></attrs>
                        <prefs><pref name="zimbraPrefTimeZoneId"/></prefs>
                    </AuthRequest>';


        $response = $this->SoapRequest($body, $header, true, $returnJSON);

        if (!$response) {
            if (isset($this->_soapError) && (($this->_soapError == 'CURL.7') || (substr($this->_soapError, 0, 4) == 'HTML') || (substr($this->_soapError, 0, 19) == 'service.PROXY_ERROR'))) {
                if (defined('ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR')) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR defined, and set to ['.ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR.']' );
                    if (ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR !== false) {
                        $retries = intval(ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR);
                        if ($retries > 10) $retries = 10; // Max of 10 times = 10 minute long session ??
                        if ($retries > 0) {
                            for ($i=0;$i<$retries;$i++) {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Host Connect Error: Retry in 60 seconds ...' );
                                $waited = sleep(60);
                                $response = $this->SoapRequest($body, $header, true, $returnJSON);
                                if ($response) break;
                                if (($this->_soapError != 'CURL.7') && (substr($this->_soapError, 0, 4) != 'HTML') && (substr($this->_soapError, 0, 19) != 'service.PROXY_ERROR')) break;
                            }
                        }
                    }
                }
            }
        }

        if (!$response) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'END Logon - Proxy Error { connected = false }');
            throw new ServiceUnavailableException("Access denied. Proxy unable to reach user mailbox server");
            return false;
        }

        if($response) {
            $this->_connected = true;

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'Settings: '. $this->_changetoken .':'.	$this->_sessionid  .':'. $this->_uid .':'. $this->_tz .':'. $this->_connected  .':'. $this->_authtoken);		

            $this->_paths = array();
            $this->_ids = array();

            $usingFolderCache = false;

            if ($this->_localCache) {
                $rebuildCache = false;

                $this->InitializePermanentStorage();
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage:' . print_r( $this->permanentStorage, true ) );

                $retries = 0;
                while (!($this->permanentStorage instanceof StateObject) && ($retries < 5)) {
                    unset($this->permanentStorage);
                    $retries += 1;
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage IS NOT A StateObject - Delay & Re-Read it in case of file contention - Retry ' . $retries . '/5 !' );
                    $microSecs = 250000; // Quarter-of-a-second
                    usleep( $microSecs );
                    $this->InitializePermanentStorage();
                }

                if ($this->permanentStorage instanceof StateObject ) {
                    $usingFolderCache = true;

                    $this->_cachedMessageLists = $this->permanentStorage->GetCachedMessageLists();
                    if (!isset($this->_cachedMessageLists) or !is_array($this->_cachedMessageLists)) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> CachedMessageLists is NOT an array - Recreate it !' );
                        $this->_cachedMessageLists = array();
                        $this->_saveCacheOnLogoff = true;
                    }
                    $this->_cacheChangeToken = $this->permanentStorage->GetCacheChangeToken();
                    if (isset($this->permanentStorage->SendAsNameOverride)) {
                        $this->_sendAsNameOverride = $this->permanentStorage->GetSendAsNameOverride();
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> _sendAsNameOverride ['.$this->_sendAsNameOverride.']' );
                    }
                    if (isset($this->permanentStorage->SendAsEmailOverride)) {
                        $this->_sendAsEmailOverride = $this->permanentStorage->GetSendAsEmailOverride();
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> _sendAsEmailOverride ['.$this->_sendAsEmailOverride.']' );
                    }
                    if (isset($this->permanentStorage->ServerInviteReply)) {
                        $this->_serverInviteReplyOverride = $this->permanentStorage->GetServerInviteReply();
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> _serverInviteReply ['.$this->_serverInviteReplyOverride.']' );
						$this->_serverInviteReply = $this->_serverInviteReplyOverride;
                    }
                    if (!isset($this->permanentStorage->CacheSupports) || ($this->_cacheSupports != $this->permanentStorage->GetCacheSupports())) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> CacheSupports not set or not up-to-date - Invalidate cached message lists!' );
                        $this->_cachedMessageLists = array();
                        $this->_saveCacheOnLogoff = true;
                    }
                    if (!isset($this->permanentStorage->SmartFolders) || ($this->_smartFolders != $this->permanentStorage->GetSmartFolders())) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> SmartFolders not set or not up-to-date - Rebuild Cache!' );
                        $rebuildCache = true;
                    }
                    $this->_folders = $this->permanentStorage->GetCachedFolders();
                    $this->_virtual = $this->permanentStorage->GetCachedVirtual();
                    $this->_primary = $this->permanentStorage->GetCachedPrimary();
                    $this->_documentLibraries = $this->permanentStorage->GetCachedDocumentLibraries();
                    $this->_documentLibrariesPathToIdIndex = $this->permanentStorage->GetCachedDocumentLibrariesPathToIdIndex();
                    if (!isset($this->_folders) or !is_array($this->_folders) or !isset($this->_virtual) or !is_array($this->_virtual) or 
                        !isset($this->_primary) or !is_array($this->_primary) or !isset($this->_documentLibraries) or !is_array($this->_documentLibraries) or
                        !isset($this->_documentLibrariesPathToIdIndex) or !is_array($this->_documentLibrariesPathToIdIndex)) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage -> CachedFolders/CachedVirtual/CachedPrimary/CachedDocumentLibraries is NOT an array - Rebuild Cache !' );
                        $rebuildCache = true;
                    }
                } else {
                     $rebuildCache = true;
                }

                if ($rebuildCache) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Permanent Storage Cache needs to be rebuilt - Recreate it !' );
                    $this->_cachedMessageLists = array();
                    $this->_cachedMessageLists['changed'] = true;
                    $this->_cacheChangeToken = 'NotSet';
                    $this->_folders = array();
                    $this->_documentLibraries = array();
                    $this->_documentLibrariesPathToIdIndex = array();
                    $usingFolderCache = false;
                    $this->_saveCacheOnLogoff = true;

                    $this->permanentStorage = new StateObject();
                    $this->permanentStorage->SetCacheTime( time() );
                    $this->permanentStorage->SetCacheSupports( $this->_cacheSupports );
                    $this->permanentStorage->SetSmartFolders( $this->_smartFolders );

                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Save Empty Cache in permanentStorage'  );
                    $this->SaveStorages();
                }

                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Local Cache Initialized !' );
            } else {
                $this->_cacheChangeToken = 'NotSet';
            }

    		if ( $this->_smartFolders ) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  "Smart Folders ENABLED - User Profile XML files will be ignored" );

                $contents = json_decode($response, true);
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'SmartFolders AuthResponse: '. print_r( $contents, true ), false );

                $this->_authtoken = $contents['Body']['AuthResponse']['authToken'][0]['_content'];
                $this->_uid = $contents['Body']['AuthResponse']['attrs']['_attrs']['uid'];
                $this->_tz = isset($contents['Body']['AuthResponse']['prefs']['_attrs']['zimbraPrefTimeZoneId']) ? $contents['Body']['AuthResponse']['prefs']['_attrs']['zimbraPrefTimeZoneId'] : 'UTC';

                // Set the session ID as a CURL cookie. This is required to traverse an nginx proxy and get subsequent
                // requests passed to the correct Mail Store for the user account
                curl_setopt($this->_curl, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN='.$this->_authtoken);

                // If nginx proxy in place, the AuthRequest will only return a session if it was lucky enough to hit the
                // right Mail Store for the mail user account. Otherwise, a NoOpRequest is required to open the session
                //  and return the session refresh block to populate the _folders array

                if (isset($contents['Body']['AuthResponse']['session'])) {
                    // Got a session - We're good to go
                    $this->_changetoken = ( isset($contents['Header']['context']['change']['token']) ? $contents['Header']['context']['change']['token'] : 'Unavailable' );
                    $this->_sessionid = $contents['Body']['AuthResponse']['session']['id'];

                } else {
                    // No session - Must be an nginx proxy - need a NoOpRequest
                    $header  = '<context xmlns="urn:zimbra">
                                    <session />
                                    <authToken>'.$this->_authtoken.'</authToken>
                                    <notify  seq="0" />
                                    <format type="js" /> 
                                    <userAgent name="'.$this->_ua.$this->_deviceIdForMailboxLog.' devip='.$this->_xFwdForForMailboxLog.' ZPZB" version="'.$GLOBALS['revision'].'" />
                                </context>';

                    $body = '<NoOpRequest xmlns="urn:zimbraMail" />';

                    $response = $this->SoapRequest($body, $header, true, $returnJSON);

                    // In multi-server environment it is possible to get an AuthToken from any Proxy but then get a proxy error
                    // requesting a session if the user's particular MTA server is not available at the time - such as during a backup
                    // Repeat the re-try loop handing here if there is a service.PROXY_ERROR
                    if (!$response) {
                        if (isset($this->_soapError) && (($this->_soapError == 'CURL.7') || (substr($this->_soapError, 0, 4) == 'HTML') || (substr($this->_soapError, 0, 19) == 'service.PROXY_ERROR'))) {
                            if (defined('ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR')) {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR defined, and set to ['.ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR.']' );
                                if (ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR !== false) {
                                    $retries = intval(ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR);
                                    if ($retries > 10) $retries = 10; // Max of 10 times = 10 minute long session ??
                                    if ($retries > 0) {
                                        for ($i=0;$i<$retries;$i++) {
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Proxy Connect Error: Retry in 60 seconds ...' );
                                            $waited = sleep(60);
                                            $response = $this->SoapRequest($body, $header, true, $returnJSON);
                                            if ($response) break;
                                            if (($this->_soapError != 'CURL.7') && (substr($this->_soapError, 0, 4) != 'HTML') && (substr($this->_soapError, 0, 19) != 'service.PROXY_ERROR')) break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!$response) {
                        ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'END Logon - Proxy Error { connected = false }');
                        throw new ServiceUnavailableException("Access denied. Proxy unable to reach user mailbox server");
                        return false;
                    }


                    $contents = json_decode($response, true);
//                    ZLog::Write(LOGLEVEL_DEBUG,  "NoOpResponse: ".print_r( $contents, true) , false );

                    $this->_changetoken = ( isset($contents['Header']['context']['change']['token']) ? $contents['Header']['context']['change']['token'] : 'Unavailable' );
                    if ( isset($contents['Header']['context']['session']['id']) ) {
                        $this->_sessionid = $contents['Header']['context']['session']['id'];
                    } else {
                        ZLog::Write(LOGLEVEL_DEBUG,  "NoOpResponse: ".print_r( $contents, true) , false );
                        ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'END Logon - Proxy Error { connected = false }');
                        throw new AuthenticationRequiredException("Access denied. Proxy unable to initiate a session on user mailbox server");
                        return false;
                    }
                }
                if ($this->_cacheChangeToken == $this->_changetoken) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  "Permanent Storage -> CacheChangeToken hasn't changed - Using _folders cache !" );
                } else {
                    $this->_folders = array();
                    $this->_virtual = array();
                    $this->_primary = array();
                    $this->_documentLibraries = array();
                    unset($this->_sendAsNameOverride);
                    unset($this->_sendAsEmailOverride);
                    unset($this->_serverInviteReplyOverride);
                    $this->_saveCacheOnLogoff = true;
                    $usingFolderCache = false;
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  "Permanent Storage -> CacheChangeToken [".$this->_cacheChangeToken."] has changed [".$this->_changetoken."] - Couldn't use _folders cache !" );
                }

                // Get user preferences				
                $this->GetUserInfo();

                if (!$usingFolderCache) {

                    $user = array();

                    $this->GetZimbraSmartFolders($user, $contents);                  // Get Smart Folders
                    // Finished with $user array - so clear it
                    unset($user);

                    if (!isset($this->_primary['note']) && $this->_firstNote) {
                        $this->_primary['note'] = $this->_firstNote;
                    }
                }

                if (isset($contents['Header']['context']['refresh']['tags']['tag'])) {
                    $this->_usertags = $contents['Header']['context']['refresh']['tags']['tag'];
                }

            } else {

//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'AuthResponse: response: '. print_r( $response, true ), false );
                $contents = $this->MakeXMLTree($response);
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'XML AuthResponse: contents: '. print_r( $contents, true ), false );

                $this->_authtoken = $this->ExtractAuthToken($response);
                $this->_uid = $contents['soap:Envelope'][0]['soap:Body'][0]['AuthResponse'][0]['attrs'][0]['attr'][0];
                $this->_tz = isset($contents['soap:Envelope'][0]['soap:Body'][0]['AuthResponse'][0]['prefs'][0]['pref'][0]) ? $contents['soap:Envelope'][0]['soap:Body'][0]['AuthResponse'][0]['prefs'][0]['pref'][0] : 'UTC';

                // Set the session ID as a CURL cookie. This is required to traverse an nginx proxy and get subsequent
                // requests passed to the correct Mail Store for the user account
                curl_setopt($this->_curl, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN='.$this->_authtoken);

                // If nginx proxy in place, the AuthRequest will only return a session if it was lucky enough to hit the
                // right Mail Store for the mail user account. Otherwise, a NoOpRequest is required to open the session
                //  and return the session refresh block to populate the _folders array

                if (isset($contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['change'][0]['token'])) {
                    // Got a session - We're good to go
                    $this->_changetoken = $contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['change'][0]['token'];
                    $this->_sessionid = $this->ExtractSessionID($response);

                } else {
                    // No session - Must be an nginx proxy - need a NoOpRequest
                    $header  = '<context xmlns="urn:zimbra">
                                    <session />
                                    <authToken>'.$this->_authtoken.'</authToken>
                                    <notify  seq="0" />
                                    <userAgent name="'.$this->_ua.$this->_deviceIdForMailboxLog.' devip='.$this->_xFwdForForMailboxLog.' ZPZB" version="'.$GLOBALS['revision'].'" />
                                </context>';

                    $body = '<NoOpRequest xmlns="urn:zimbraMail" />';

                    $response = $this->SoapRequest($body, $header, true, $returnJSON);


                    // In multi-server environment it is possible to get an AuthToken from any Proxy but then get a proxy error
                    // requesting a session if the user's particular MTA server is not available at the time - such as during a backup
                    // Repeat the re-try loop handing here if there is a service.PROXY_ERROR
                    if (!$response) {
                        if (isset($this->_soapError) && (($this->_soapError == 'CURL.7') || (substr($this->_soapError, 0, 4) == 'HTML') || (substr($this->_soapError, 0, 19) == 'service.PROXY_ERROR'))) {
                            if (defined('ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR')) {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR defined, and set to ['.ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR.']' );
                                if (ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR !== false) {
                                    $retries = intval(ZIMBRA_RETRIES_ON_HOST_CONNECT_ERROR);
                                    if ($retries > 10) $retries = 10; // Max of 10 times = 10 minute long session ??
                                    if ($retries > 0) {
                                        for ($i=0;$i<$retries;$i++) {
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Proxy Connect Error: Retry in 60 seconds ...' );
                                            $waited = sleep(60);
                                            $response = $this->SoapRequest($body, $header, true, $returnJSON);
                                            if ($response) break;
                                            if (($this->_soapError != 'CURL.7') && (substr($this->_soapError, 0, 4) != 'HTML') && (substr($this->_soapError, 0, 19) != 'service.PROXY_ERROR')) break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!$response) {
                        ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'END Logon - Proxy Error { connected = false }');
                        throw new ServiceUnavailableException("Access denied. Proxy unable to reach user mailbox server");
                        return false;
                    }

                    $contents = $this->MakeXMLTree($response);
//                    ZLog::Write(LOGLEVEL_DEBUG,  "NoOpResponse: ".print_r( $contents, true) , false );

                    $this->_changetoken = ( isset($contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['change'][0]['token']) ? $contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['change'][0]['token'] : 'Unavailable' );
                    if ( isset($contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['session'][0]) ) {
                        $this->_sessionid = $contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['session'][0];
                    } else {
                        ZLog::Write(LOGLEVEL_DEBUG,  "NoOpResponse: ".print_r( $contents, true) , false );
                        ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'END Logon - Proxy Error { connected = false }');
                        throw new AuthenticationRequiredException("Access denied. Proxy unable to initiate a session on user mailbox server");
                        return false;
                    }
                }

                if ($this->_cacheChangeToken == $this->_changetoken) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  "Permanent Storage -> CacheChangeToken hasn't changed - Using _folders cache !" );
                } else {
                    $this->_folders = array();
                    $this->_virtual = array();
                    $this->_primary = array();
                    $this->_documentLibraries = array();
                    unset($this->_sendAsNameOverride);
                    unset($this->_sendAsEmailOverride);
                    unset($this->_serverInviteReplyOverride);
                    $this->_saveCacheOnLogoff = true;
                    $usingFolderCache = false;
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  "Permanent Storage -> CacheChangeToken [".$this->_cacheChangeToken."] has changed [".$this->_changetoken."] - couldn't use cache !" );
                }
                // Get user preferences				
                $this->GetUserInfo();

                if (!$usingFolderCache) {

                    // Load User File (if exists)
                    $user = $this->GetUserProfileXML();

                    // Using Criteria, Determine Which Folders To Include
                    $this->GetZimbraFolders($user, $response);                  // Get Local Folders

                    $this->GetZimbraSearchFolders($user);               // Check For Any Search Folders

                    // Finished with $user array - so clear it
                    unset($user);
                }

                if (isset($contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['refresh'][0]['tags'][0]['tag'])) {
                    $this->_usertags = $contents['soap:Envelope'][0]['soap:Header'][0]['context'][0]['refresh'][0]['tags'][0]['tag'];
                }
			
            }

            /* If the Outbox does not appear in the Folder Hierarchy from the server, many phones 
               just leaves outgoing emails sitting in the Outbox folder on the phone. Even though
               zimbra does not have an Outbox to sync - we present a fake outbox to enable sending
                   
               It NEEDS to have include=1 and virtual=0 in order to get returned to the phone
               as part of the Folder Hierarchy - See the following if statement in FetFolderList()
               [ if ($this->_folders[$i]->include == 1 && $this->_folders[$i]->virtual == 0) { ]
               Then in GetFolder() we recognise the name Outbox - and set the folder type to 
               SYNC_FOLDER_TYPE_OUTBOX. Hopefully this is enough to fool the phone.
            */
            if (!$usingFolderCache) {

                $j = count($this->_folders);
                $this->_folders[$j] = new stdClass();
                $this->_folders[$j]->id = -1; 
                $this->_folders[$j]->devid = 'FakeOutbox';
                $this->_folders[$j]->name = 'Outbox';
                $this->_folders[$j]->path = 'Outbox';
                $this->_folders[$j]->parentid = '0';
                $this->_folders[$j]->view = 'message';
                $this->_folders[$j]->include = 1;
                $this->_folders[$j]->virtual = 0;
                $this->_folders[$j]->primary = 0;
                $this->_folders[$j]->external = 0;
                $this->_folders[$j]->recursive = 0;
                $this->_folders[$j]->linkid = '';
                $this->_folders[$j]->search = '';
                $this->_folders[$j]->i4ms = '';
                $this->_folders[$j]->perm = '';
                $this->_folders[$j]->owner = '';
                $this->_folders[$j]->stats = $this->_folders[$j]->name . "-" . strval(time());

                // Confirm Each Type Has A Primary
                $check = array();
                for ($i=0;$i<count($this->_folders);$i++) {
                    if ($this->_folders[$i]->include == 1 && !isset($check[$this->_folders[$i]->view]['first'])) {
                        $check[$this->_folders[$i]->view]['first'] = $i;
                    }
                    if ($this->_folders[$i]->include == 1 && $this->_folders[$i]->primary == 1) {
                        $check[$this->_folders[$i]->view]['found'] = 1;
                    }
                }
                if (!isset($check['message']['found'])) {
                    if(isset($check['message']['first'])) {
                        $this->_folders[$check['message']['first']]->primary = 1;
                        $this->_primary['message'] = $this->_folders[$check['message']['first']]->devid;
                    } else {
                        $this->_primary['message'] = "";
                    }
                }
                if (!isset($check['contact']['found'])) {
                    if(isset($check['contact']['first'])) {
                        $this->_folders[$check['contact']['first']]->primary = 1;
                        $this->_primary['contact'] = $this->_folders[$check['contact']['first']]->devid;
                    } else {
                        $this->_primary['contact'] = "";
                    }
                }
                if (!isset($check['appointment']['found'])) {
                    if(isset($check['appointment']['first'])) {
                        $this->_folders[$check['appointment']['first']]->primary = 1;
                        $this->_primary['appointment'] = $this->_folders[$check['appointment']['first']]->devid;
                    } else {
                        $this->_primary['appointment'] = "";
                    }
                }
                if (!isset($check['task']['found'])) {
                    if(isset($check['task']['first'])) {
                        $this->_folders[$check['task']['first']]->primary = 1;
                        $this->_primary['task'] = $this->_folders[$check['task']['first']]->devid;
                    } else {
                        $this->_primary['task'] = "";
                    }
                }
                if (!isset($check['note']['found'])) {
                    if(isset($check['note']['first'])) {
                        $this->_folders[$check['note']['first']]->primary = 1;
                        $this->_primary['note'] = $this->_folders[$check['note']['first']]->devid;
                    } else {
                        $this->_primary['note'] = "";
                    }
                }
            }

            // If there are shared folders included we will need to gather additional information for them
            // Start creating a soap request to gather the information. After we process the list we will see if we need it.			
            $soap ='<BatchRequest xmlns="urn:zimbra">';
            $initialCount = count($this->_folders);             
            for ($i=0;$i<$initialCount;$i++) {
                if (strtolower($this->_folders[$i]->id) == '3') {
                    $this->_wasteID = $this->_folders[$i]->devid;
                }
//                $this->_devidToIndex[$this->_folders[$i]->devid] = $i;
                if ($this->_folders[$i]->linkid != '') {
                    $this->_idToIndex[$this->_folders[$i]->linkid] = $i;
                    // If we have a share to a folder - we get a local Id for the folder - but zimbra notifies on the remote user's folder name - so index by both
                    if ($this->_folders[$i]->linkid != $this->_folders[$i]->id) {
                        $this->_idToIndex[$this->_folders[$i]->id] = $i;
                    }
                    $ownerFolder = explode( ":", $this->_folders[$i]->linkid);
                    if (!isset( $_shareOwners) || !in_array( $ownerFolder[0], $_shareOwners)) {
                        $_shareOwners[] = $ownerFolder[0];
                        $soap .= '<GetFolderRequest visible="1" xmlns="urn:zimbraMail">
                                      <folder  l="' . $ownerFolder[0] . ':1"/>
                                  </GetFolderRequest>';
                    }
                } else {
                    $this->_idToIndex[$this->_folders[$i]->id] = $i;
                }
            }
            $soap .= '</BatchRequest>';

            // If there are shared folders listed we need to make an additional call to populate the data for those that is not returned in the refresh block
            if (isset( $_shareOwners)) {
                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, true, $returnJSON);

                $remoteFolderResponse = json_decode($response, true);
                unset( $response );

                if (isset( $remoteFolderResponse['Body']['BatchResponse']['GetFolderResponse'][0]) ) {
                    $remoteFolderList = $remoteFolderResponse['Body']['BatchResponse']['GetFolderResponse'];
                    unset( $remoteFolderResponse );

                    //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'RemoteFolderList: '. print_r( $remoteFolderList, true ), false );

                    $accounts = count($remoteFolderList);
                    //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'Accounts: '. $accounts );

                    for ($a=0;$a<$accounts;$a++) {
                        if (!isset($remoteFolderList[$a]['folder'])) {
                            ZLog::Write(LOGLEVEL_WARN, 'Zimbra->Logon(): ' .  'A folder shared with this user is not accessible. If it is permanently unavailable it should be removed from the Web client' );
                        } else {
                            $topLevelFolders = count($remoteFolderList[$a]['folder'][0]['folder']);
                            //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'TopLevelFolders: '. $topLevelFolders );

                            for ($i=0;$i<$topLevelFolders;$i++) {
                                //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Process Folder ['.$remoteFolderList[$a]['folder'][0]['folder'][$i]['name'].']');
                                $this->ProcessZimbraSharedFolderRecursive($remoteFolderList[$a]['folder'][0]['folder'][$i] );
                            }
                        }
                    }
                }
            }

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'Primary Folders - Addressbook ['. $this->_primary['contact'] .'] - Calendar ['. $this->_primary['appointment'] .'] - Task ['. $this->_primary['task'] .'] - Note ['. $this->_primary['note'] . '] ');

            // Moved after shared folders setup for Virtual Folder Notifications fix
            $this->_cacheChangeToken = $this->_changetoken;

        } else {
            $this->_connected = false;
        }

        if ($this->_connected) {
            $folder_count = count($this->_folders);
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' . 'END Logon { connected = true; uid = ' . $this->_uid . ' - ' . $folder_count . ' Folders Loaded }');
            return true;
        } else {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->Logon(): ' . 'END Logon { connected = false }');
            throw new AuthenticationRequiredException("Access denied. User credentials are invalid");
            return false;
        }
    } // end Logon


    /** Logoff
     *   Called before shutting down the request to close the auth session
     */
    public function Logoff() {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' . 'START Logoff');

        try {
            if ($this->_localCache) {
                //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Folder Cache at LOGOFF:  ['.print_r($this->_cachedMessageLists, true).']', false );

                $cacheUpdated = isset($this->_cachedMessageLists['changed']);
                unset( $this->_cachedMessageLists['changed'] );
                $cacheCleaned = false;

                // Cleanup expired cachedMessageLists on Logoff. Gets rid of stale and deleted folders from the cache file.
                if (isset($this->_cachedMessageLists)) {
                    foreach ($this->_cachedMessageLists as $folderid => $foldercache) {
                        $cacheAge = time() - $this->_cachedMessageLists[$folderid]['cachetime'];
                        if ($cacheAge >= $this->_localCacheLifetime) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Clear EXPIRED ('.$cacheAge.'s old) cache for folder ['.$folderid.']' );
                            unset( $this->_cachedMessageLists[$folderid] );
                            $cacheCleaned = true;
                        }
                    }
                }

                if ($this->_clearCacheOnLogoff) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Setting CacheClearedOnLogoff' );
                    $this->_cacheChangeToken = "CacheClearedOnLogoff";
                    $this->_saveCacheOnLogoff = true;
                }
				
                if ($this->_saveCacheOnLogoff || $cacheUpdated || $cacheCleaned) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Cache changed in this session - SAVE it' );

                    $this->permanentStorage->SetCacheChangeToken( $this->_cacheChangeToken );
                    if (isset($this->_sendAsNameOverride)) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Saving SendAsNameOverride to Cache ['.$this->_sendAsNameOverride.']' );
                        $this->permanentStorage->SetSendAsNameOverride( $this->_sendAsNameOverride );
                    } elseif (isset($this->permanentStorage->SendAsNameOverride)) { 
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Clearing SendAsNameOverride from Cache' );
                        unset( $this->permanentStorage->SendAsNameOverride );
                    }
                    if (isset($this->_sendAsEmailOverride)) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Saving SendAsEmailOverride to Cache ['.$this->_sendAsEmailOverride.']' );
                        $this->permanentStorage->SetSendAsEmailOverride( $this->_sendAsEmailOverride );
                    } elseif (isset($this->permanentStorage->SendAsEmailOverride)) { 
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Clearing SendAsEmailOverride from Cache' );
                        unset( $this->permanentStorage->SendAsEmailOverride );
                    }
                    if (isset($this->_serverInviteReplyOverride)) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Saving ServerInviteReplyOverride to Cache ['.$this->_serverInviteReplyOverride.']' );
                        $this->permanentStorage->SetServerInviteReply( $this->_serverInviteReplyOverride );
                    } elseif (isset($this->permanentStorage->ServerInviteReply)) { 
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'Clearing ServerInviteReply from Cache' );
                        unset( $this->permanentStorage->ServerInviteReply );
                    }
                    $this->permanentStorage->SetCachedFolders( $this->_folders );
                    $this->permanentStorage->SetCachedVirtual( $this->_virtual );
                    $this->permanentStorage->SetCachedPrimary( $this->_primary );
                    $this->permanentStorage->SetCachedDocumentLibraries( $this->_documentLibraries );
                    $this->permanentStorage->SetCachedDocumentLibrariesPathToIdIndex( $this->_documentLibrariesPathToIdIndex );
                    $this->permanentStorage->SetCachedMessageLists( $this->_cachedMessageLists );
                    $this->permanentStorage->SetCacheSupports( $this->_cacheSupports );
                    $this->permanentStorage->SetSmartFolders( $this->_smartFolders );

                    $this->SaveStorages();
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' .  'No change in Cache in this session - SKIP saving it' );
                }
                unset( $this->permanentStorage );
            }

            for ($i=0;$i<count($this->_folders);$i++) {
                unset( $this->_folders[$i] );
            }
            $this->_folders = NULL;
            $this->_localCache = NULL;
            $this->_cachedMessageLists = NULL;
            $this->_virtual['contact'] = NULL;
            $this->_virtual['appointment'] = NULL;
            $this->_virtual['task'] = NULL;
            $this->_virtual['note'] = NULL;

//            $this->_devidToIndex = NULL;
            $this->_idToIndex = NULL;

            unset( $this->_folders );
            unset( $this->_localCache );
            unset( $this->_cachedMessageLists );
            unset( $this->_virtual['contact'] );
            unset( $this->_virtual['appointment'] );
            unset( $this->_virtual['task'] );
            unset( $this->_virtual['note'] );

//            unset( $this->_devidToIndex );
            unset( $this->_idToIndex );

            if ($this->_connected) {
                $soap = '<EndSessionRequest xmlns="urn:zimbraAccount" logoff="1"/>';
                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false ,$returnJSON);
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' . 'EndSession response: '. print_r( $response, true), false );
            }
        }
        catch (Exception $ex) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' . 'Caught Exception: '. $ex->getMessage() );
        }

        $this->ReportMemoryUsage( 'END Logoff' );
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logoff(): ' . 'END Logoff');
    } // end Logoff


    /** GetUserInfo
     *   Called immediately after AuthRequest to get initial user data
     */
    public function GetUserInfo() {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' . 'START GetUserInfo');
			
        // Get Additional User Information
        // Do not use $response here as it still contains
        $soap = '<GetInfoRequest sections="mbox,prefs,attrs,idents,dsrcs,children" xmlns="urn:zimbraAccount"></GetInfoRequest>';

        $returnJSON = true;
        $getInfoResponse = $this->SoapRequest($soap, false ,false, $returnJSON);
        if($getInfoResponse) {
            $contents = json_decode($getInfoResponse, true);
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' .  print_r( $contents, true ), false );
            unset( $getInfoResponse );

            $this->_zimbraId = $contents['Body']['GetInfoResponse']['id'];
            $this->_zimbraVersion = substr( $contents['Body']['GetInfoResponse']['version'], 0, 3 );
            if ($this->_zimbraVersion == '5.0') {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' .  'zimbra Version: '. $this->_zimbraVersion . ' - WATCH FOR DATE SYNCHRONIZATION ISSUES, AND UPDATE v5timezone.xml AS NEEDED' );

                if (!$this->_tzFromDomain) {
                    if ($this->_tz != 'UTC') {
                        $v6tz = $this->LookupV5Timezone( "", $this->_tz);
                        if ($v6tz !== false) {
                            $this->_tz = $v6tz;
                        } else {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' .  'User Preferred TimeZone ['.$this->_tz.'] NOT FOUND in v5timezone.xml - DATE SYNCHRONIZATION ISSUES MAY OCCUR' );
                        }
                    }
                }
            }
				
            $this->_accountName = $contents['Body']['GetInfoResponse']['name'];
            $this->_accountRestURL = $contents['Body']['GetInfoResponse']['rest'];
            $this->_sendAsEmail = $contents['Body']['GetInfoResponse']['name'];
            $this->_addresses[] = $this->_sendAsEmail;

            $configUsesSSL = (strtolower(substr( $this->_publicURL, 0, 5) == "https")); 
            $zimbraPublicURL = $contents['Body']['GetInfoResponse']['publicURL'];
            $zimbraSoapURL = $contents['Body']['GetInfoResponse']['soapURL'];

            $publicUrlMatch = (($configUsesSSL && ( str_replace(":443", "", $zimbraPublicURL) == str_replace(":443", "", $this->_publicURL) ) ) || 
                               (!$configUsesSSL && ( str_replace(":80", "", $zimbraPublicURL) == str_replace(":80", "", $this->_publicURL) ) ));
            if ( !$publicUrlMatch ) {
                if (!defined('ZIMBRA_DISABLE_URL_OVERRIDE') || $this->ToBool( ZIMBRA_DISABLE_URL_OVERRIDE ) != "true" ) {
                    ZLog::Write(LOGLEVEL_WARN, 'Zimbra->GetUserInfo(): ' . '  Overriding ZIMBRA_URL ['.$this->_publicURL.'] from config.php with ZIMBRA publicURL ['. $zimbraPublicURL .']' );
                    $this->_publicURL = $zimbraPublicURL;
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' . '  ZIMBRA_DISABLE_URL_OVERRIDE set to "true" - ZIMBRA_URL will NOT be overridden' );
                }
            }

            $soapUrlMatch = (($configUsesSSL && ( str_replace(":443", "", $zimbraSoapURL) == str_replace(":443", "", $this->_soapURL) ) ) || 
                             (!$configUsesSSL && ( str_replace(":80", "", $zimbraSoapURL) == str_replace(":80", "", $this->_soapURL) ) ));
            if ( !$soapUrlMatch ) {
                if (!defined('ZIMBRA_DISABLE_URL_OVERRIDE') || $this->ToBool( ZIMBRA_DISABLE_URL_OVERRIDE ) != "true" ) {
                    ZLog::Write(LOGLEVEL_WARN, 'Zimbra->GetUserInfo(): ' . '  Overriding  Logon URL ['.$this->_soapURL.'] with ZIMBRA soapURL [' . $zimbraSoapURL . ']' );
                    $this->_soapURL = $zimbraSoapURL;
                    curl_setopt($this->_curl, CURLOPT_URL, $this->_soapURL);
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' . '  ZIMBRA_DISABLE_URL_OVERRIDE set to "true" - Logon/Soap URL will NOT be overridden' );
                }
            }
            if ( stripos($zimbraSoapURL, $zimbraPublicURL ) === false) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' . 'ZIMBRA soapURL ['. $zimbraSoapURL .'] does NOT begin with ZIMBRA publicURL ['. $zimbraPublicURL .']' );
            }

            $subset = $contents['Body']['GetInfoResponse']['prefs']['_attrs'];

            if (isset($subset['zimbraPrefCalendarAutoAddInvites']) && $this->ToBool($subset['zimbraPrefCalendarAutoAddInvites'])) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' . 'ZIMBRA User Preference setting "webclient->Preferences->Calendar>General->[ ] Automatically add received appointments to calendar is checked" - This can cause duplication of appointments on the client! ' );
            }

            $subset = $contents['Body']['GetInfoResponse']['attrs']['_attrs'];

            if (isset($subset['zimbraAttachmentsBlocked']) && $this->ToBool($subset['zimbraAttachmentsBlocked'])) {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->GetUserInfo(): ' . 'ZIMBRA configuration is blocking the download of attachments (including MIME messages to iOS/Outlook) - Check Configure->Global Settings->Attachments->Attachments cannot be viewed regardless of COS -or- Configure->Class of service->[Class name]->Advanced->Attachment settings->Disable attachment viewing from web mail ui ' );
                $this->_attachmentsBlocked = true;
            }

            if (isset($subset['displayName'])) {
                $this->_sendAsName = $subset['displayName'];
            } elseif (isset($subset['cn'])) {
                $this->_sendAsName = $subset['cn'];
            }
            if (isset($subset['zimbraMailAlias'])) {
                if (is_array($subset['zimbraMailAlias'])) {
                    $aliases = count($subset['zimbraMailAlias']);
                    for ($i=0;$i<$aliases;$i++) {
                        $this->_addresses[] = $subset['zimbraMailAlias'][$i];
                    }
                } else {
                    $this->_addresses[] = $subset['zimbraMailAlias'];
                }
            }

            // protected $_userFolderTypeActive = array( 'message'=>true, 'contact'=>true, 'appointment'=>true, 'task'=>true, 'document'=>false, 'wiki'=>false, 'note'=>true );
            $this->_userFolderTypeActive['message'] = (isset($subset['zimbraFeatureMailEnabled']) ? $this->ToBool($subset['zimbraFeatureMailEnabled']) : $this->_userFolderTypeActive['message'] );
            $this->_userFolderTypeActive['contact'] = (isset($subset['zimbraFeatureContactsEnabled']) ? $this->ToBool($subset['zimbraFeatureContactsEnabled']) : $this->_userFolderTypeActive['contact'] );
            $this->_userFolderTypeActive['appointment'] = (isset($subset['zimbraFeatureCalendarEnabled']) ? $this->ToBool($subset['zimbraFeatureCalendarEnabled']) : $this->_userFolderTypeActive['appointment'] );
            $this->_userFolderTypeActive['task'] = (isset($subset['zimbraFeatureTasksEnabled']) ? $this->ToBool($subset['zimbraFeatureTasksEnabled']) : $this->_userFolderTypeActive['task'] );
            $this->_userFolderTypeActive['document'] = (isset($subset['zimbraFeatureBriefcasesEnabled']) ? $this->ToBool($subset['zimbraFeatureBriefcasesEnabled']) : $this->_userFolderTypeActive['document'] );

            if (!class_exists("SyncDocumentLibraryDocument")) {
                ZLog::Write(LOGLEVEL_DEBUG,  'Zimbra->GetUserInfo(): ' .  "Class SyncDocumentLibraryDocument does not exist - Setting this->_userFolderTypeActive['document'] = false" );
                $this->_userFolderTypeActive['document'] = false;  // Cannot handle documents without z-push having the relevant class(es) built-in
            }

//            $this->_userFolderTypeActive['wiki'] = (isset($subset['zimbraFeatureWikiEnabled']) ? $this->ToBool($subset['zimbraFeatureWikiEnabled']) : $this->_userFolderTypeActive['wiki'] );
            $this->_userFolderTypeActive['note'] = (isset($subset['zimbraFeatureTasksEnabled']) ? $this->ToBool($subset['zimbraFeatureTasksEnabled']) : $this->_userFolderTypeActive['note'] );

            $subset = $contents['Body']['GetInfoResponse']['prefs']['_attrs'];
            if (isset($subset['zimbraPrefFromDisplay'])) {
                $this->_sendAsName = $subset['zimbraPrefFromDisplay'];
            }
            if (isset($subset['zimbraPrefFromAddress']) && (trim($subset['zimbraPrefFromAddress']) != "") && ($subset['zimbraPrefFromAddress'] != $this->_accountName)) {
                $this->_sendAsEmail = $subset['zimbraPrefFromAddress'];
            }

            $subset = $contents['Body']['GetInfoResponse']['identities']['identity'];
            $identities = count($subset);
            for ($i=0;$i<$identities;$i++) {
                if (isset($subset[$i]['_attrs']['zimbraPrefFromAddress'])) {
					if (isset($subset[$i]['_attrs']['zimbraPrefFromDisplay'])) {
                        $this->_identities[$subset[$i]['_attrs']['zimbraPrefFromAddress']] = $subset[$i]['_attrs']['zimbraPrefFromDisplay'];
					}
                }
            }
            //ZLog::Write(LOGLEVEL_DEBUG,  'ZIMBRA identities ' . print_r( $this->_identities, true ) );
/*
$timezone = new DateTimeZone($this->_tz);
$offset   = $timezone->getOffset(new DateTime);

debugLog( "Offset =" . print_r( $offset, true ) );

$endTime = time();
$startTime = date_sub($endTime, date_interval_create_from_date_string('2 years'));
$transitions = $timezone->getTransitions($startTime,$endTime);
debugLog( "Transitions =" . print_r( $transitions, true ) , false );
debugLog( "Transitions Slice=" . print_r(array_slice($transitions, 0, 3)));
*/
            unset( $subset );
            unset( $contents );
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserInfo(): ' . 'END GetUserInfo');
    }


    /**
     * Setup the backend to work on a specific store or checks ACLs there.
     * If only the $store is submitted, all Import/Export/Fetch/Etc operations should be
     * performed on this store (switch operations store).
     * If the ACL check is enabled, this operation should just indicate the ACL status on
     * the submitted store, without changing the store for operations.
     * For the ACL status, the currently logged on user MUST have access rights on
     *  - the entire store - admin access if no folderid is sent, or
     *  - on a specific folderid in the store (secretary/full access rights)
     *
     * The ACLcheck MUST fail if a folder of the authenticated user is checked!
     *
     * @param string        $store              target store, could contain a "domain\user" value
     * @param boolean       $checkACLonly       if set to true, Setup() should just check ACLs
     * @param string        $folderid           if set, only ACLs on this folderid are relevant
     *
     * @access public
     * @return boolean
     */
    public function Setup($store, $checkACLonly = false, $folderid = false, $readonly = false) {
        if (defined('ZIMBRA_DEBUG')) {
            if (stripos(ZIMBRA_DEBUG, 'setup') !== false) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Setup(): ' . 'START Setup { store = ' . $store . '; checkACLonly = ' . $checkACLonly . '; folderid = ' . $folderid . '; readonly = ' . $readonly . ' }');
            }
        }

        list($userid, $domain) = Utils::SplitDomainUser($store);

        if (!isset($this->mainUser)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Setup(): ' .  'MAIN USER NOT SET - RETURNING! ');
            return false;
        }

        if ($store != false) {

            /* If any other folders are to be used (by z-push-2 for instance) they should be 
               added here. 
            */

            if (defined('ZIMBRA_DEBUG')) {
                if (stripos(ZIMBRA_DEBUG, 'setup') !== false) {
                    for ($f=0;$f<count($this->_folders);$f++) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Setup(): ' . "[" . $f . "] id=" . $this->_folders[$f]->id . "; devid=" . $this->_folders[$f]->devid . "; name=" . $this->_folders[$f]->name . "; parentid=" . $this->_folders[$f]->parentid . "; view=" . $this->_folders[$f]->view . "; include=" . $this->_folders[$f]->include . "; virtual=" . $this->_folders[$f]->virtual . "; primary=" . $this->_folders[$f]->primary . "; external=" . $this->_folders[$f]->external . "; recursive=" . $this->_folders[$f]->recursive . "; linkid=" . $this->_folders[$f]->linkid . "; owner=" . $this->_folders[$f]->owner . "; search=" . $this->_folders[$f]->search . "; i4ms=" . $this->_folders[$f]->i4ms . "; perm=" . $this->_folders[$f]->perm . "; path=" . $this->_folders[$f]->path);
                    }
                }
            }
            $this->store = $store;

        } else {

            $userid = $this->mainUser;
            $store = $this->store;
        }

        if ($userid === false) {
            $userid = $this->mainUser;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Setup(): ' .  '$userid was FALSE - setting $userid = ['.$userid.']' );
        }

        // This is a special case. A user will get it's entire folder structure by the foldersync by default.
        // The ACL check is executed when an additional folder is going to be sent to the mobile.
        // Configured that way the user could receive the same folderid twice, with two different names.
        if ($this->mainUser == $userid && $checkACLonly && $folderid) {
            ZLog::Write(LOGLEVEL_DEBUG, "Zimbra->Setup(): Checking ACLs for folder of the users defaultstore. Fail is forced to avoid folder duplications on mobile.");
            return false;
        }

        $folder_count = count($this->_folders);

        if ($this->mainUser == $userid && $checkACLonly ) {
            ZLog::Write(LOGLEVEL_DEBUG, "Zimbra->Setup(): Checking ACLs for main user - Return TRUE.");
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Setup(): ' . 'END Setup {TRUE} { ' . $folder_count . ' Folders in Store }');
            return true;
        }

        $this->store = $store;
        $this->storeName = $store;

        if (defined('ZIMBRA_DEBUG')) {
            if (stripos(ZIMBRA_DEBUG, 'setup') !== false) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Setup(): ' . 'END Setup {TRUE} { ' . $folder_count . ' Folders in Store }');
            }
        }

        return true;
    } // end Setup


    /** GetZimbraSmartFolders
     *   Function exclusively used by function Setup to process folders and determine which to include.  Code removed from
     *   Setup function and placed in own due to shared (linked) folders.  Setup first calls this function and gets all folders
     *   for user's local mailbox.  This does include shared folders but only the parent.  The Setup function then loops through
     *   calls this function for each parent shared folder to get any children.
     */
    function GetZimbraSmartFolders( &$user, &$contents) {

        if (defined('ZIMBRA_DEBUG')) {
            $debugSetup = (stripos(ZIMBRA_DEBUG, 'setup') !== false);
        } else $debugSetup = false;

            // Defaulting the 'primary' 'identifier' to 'id' as it will work across language differences. 
            // If a user makes a change using a directive, we will change the appropriate identifier to 'name'
            $user['message'][0]['active'] = ($this->_userFolderTypeActive['message'] ? 'true' : 'false' );
            $user['message'][0]['virtual'] = 'false';
            $user['message'][0]['primary'][0]['identifier'] = 'id';
            $user['message'][0]['primary'][0]['id'] = '2';  // Inbox (in English)
            $user['contact'][0]['active'] = ($this->_userFolderTypeActive['contact'] ? 'true' : 'false' );
            $user['contact'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_CONTACTS') ? (($this->ToBool(ZIMBRA_VIRTUAL_CONTACTS)) ? 'true' : 'false') : 'true' );
            $user['contact'][0]['primary'][0]['identifier'] = 'id';
            $user['contact'][0]['primary'][0]['id'] = '7';  // Calendar (in English)
            $user['appointment'][0]['active'] = ($this->_userFolderTypeActive['appointment'] ? 'true' : 'false' );
            $user['appointment'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_APPOINTMENTS') ? (($this->ToBool(ZIMBRA_VIRTUAL_APPOINTMENTS)) ? 'true' : 'false') : 'true' );
            $user['appointment'][0]['primary'][0]['identifier'] = 'id';
            $user['appointment'][0]['primary'][0]['id'] = '10';  // Contacts (in English)
            $user['task'][0]['active'] = ($this->_userFolderTypeActive['task'] ? 'true' : 'false' );
            $user['task'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_TASKS') ? (($this->ToBool(ZIMBRA_VIRTUAL_TASKS)) ? 'true' : 'false') : 'true' );
            $user['task'][0]['primary'][0]['identifier'] = 'id';
            $user['task'][0]['primary'][0]['id'] = '15';  // Tasks (in English)
            // Added document to accomodate clients that can download Documents from SharePoint - eg. Galaxy S4
            $user['document'][0]['active'] = ($this->_userFolderTypeActive['document'] ? 'true' : 'false' );
            $user['document'][0]['virtual'] = 'false';
            $user['document'][0]['primary'][0]['identifier'] = 'id';
            $user['document'][0]['primary'][0]['id'] = '16';  // Briefcase (in English)
            // Added wiki to accomodate servers that run, or once ran, version 5.0.x of zimbra
            $user['wiki'][0]['active'] = ($this->_userFolderTypeActive['wiki'] ? 'true' : 'false' );
            $user['wiki'][0]['virtual'] = 'false';
            $user['wiki'][0]['primary'][0]['identifier'] = 'id';
            $user['wiki'][0]['primary'][0]['id'] = '16';  // Briefcase (in English)
            // Added note to accomodate clients that can send Sticky Notes - eg. iOS7
            $user['note'][0]['active'] = ($this->_userFolderTypeActive['note'] ? 'true' : 'false' );
            $user['note'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_NOTES') ? (($this->ToBool(ZIMBRA_VIRTUAL_NOTES)) ? 'true' : 'false') : 'true' );
            $user['note'][0]['primary'][0]['identifier'] = 'id';
            $user['note'][0]['primary'][0]['id'] = '0';  // Notes (in English) - Need to match folder name and set Folder ID

            $this->_virtual['contact'] = array();
            $this->_virtual['appointment'] = array();
            $this->_virtual['task'] = array();
            $this->_virtual['note'] = array();

            $this->_primary['message'] = "";
            $this->_primary['contact'] = "";
            $this->_primary['appointment'] = "";
            $this->_primary['task'] = "";
            $this->_primary['note'] = "";

            $linked_folder_id = 0;
            $parentid = '0';
            $parentpath = "user_root";


            $folders = array();
            $totalfolders = 0;
            $totallinks = 0;
            $totalsearches = 0;
            if (isset($contents['Header']['context']['refresh']['folder'][0]['folder'])) {
                $folders = $contents['Header']['context']['refresh']['folder'][0]['folder'];
                $totalfolders = count($folders);
            }
            $links = array();
            if (isset($contents['Header']['context']['refresh']['folder'][0]['link'])) {
                $links = $contents['Header']['context']['refresh']['folder'][0]['link'];
                $totallinks = count($links);
            }
            $searches = array();
            if (isset($contents['Header']['context']['refresh']['folder'][0]['search'])) {
                $searches = $contents['Header']['context']['refresh']['folder'][0]['search'];
                $totalsearches = count($searches);
            }

            if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Check if Folder ['.$contents['Header']['context']['refresh']['folder'][0]['name'].'] has children ? - Folders ['.$totalfolders.'] - Links ['.$totallinks.'] - Searches ['.$totalsearches.']' );

            // First - Check for folder *SyncConfig* to see if any SmartFolder Directives included - It will only be processed from 'user_root' 'folder' list.
            for ($i=0;$i<$totalfolders;$i++) {
                // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                if (strtolower($folders[$i]['name']) == "*syncconfig*") {
                    if (isset($folders[$i]['folder'])) {
                        $directives = count($folders[$i]['folder']);
                    } else {
                        $directives = 0;
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Found *SyncConfig* folder with ['.$directives.'] Directives' );
                    for ($j=0;$j<$directives;$j++) {
                        $directive = $folders[$i]['folder'][$j]['name'];
                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Directive ['.$j.'] = ['.$directive.']' );
                        $parts = explode( '&', $directive);
                        switch (strtolower($parts[0])) {
                            case 'message':
                            case 'contact':
                            case 'appointment':
                            case 'task':
                            case 'note':
                                for ($k=1;$k<count($parts);$k++) {
                                    $setting = explode( '=', $parts[$k]);
                                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Process Setting ['.$setting[0].'] with value ['.$setting[1].']' );
                                    if (strtolower($setting[0]) == 'primary') {
                                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Setting user['.$parts[0]."][0]['primary'] to [".$setting[1].']'); 
                                        $user[$parts[0]][0]['primary'][0]['identifier'] = 'name';   
                                        $user[$parts[0]][0]['primary'][0]['name'] = $setting[1];   
                                    }
                                    // Cannot make 'message' virtual								
                                    if ((strtolower($setting[0]) == 'virtual') && ($parts[0] != 'message')) {
                                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Setting user['.$parts[0]."][0]['virtual'] to [".(($this->ToBool($setting[1]) ? 'true' : 'false')).']'); 
                                        $user[$parts[0]][0]['virtual'] = (($this->ToBool($setting[1]) ? 'true' : 'false'));     
                                    }
                                    if (strtolower($setting[0]) == 'active') {
                                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Setting user['.$parts[0]."][0]['active'] to [".(($this->ToBool($setting[1]) ? 'true' : 'false')).']'); 
                                        $user[$parts[0]][0]['active'] = (($this->ToBool($setting[1])) ? 'true' : 'false');
                                    }
                                }    
                                break;
                            case 'sendasname':
                                $this->_sendAsNameOverride = substr( $directive, 11 );
                                break;
                            case 'sendasemail':
                                $sendAsEmail = substr( $directive, 12 );
                                if (!defined('ZIMBRA_ENFORCE_VALID_EMAIL')) {
                                    $this->_enforcevalidemail = 'false';
                                } else {
                                    $this->_enforcevalidemail = ZIMBRA_ENFORCE_VALID_EMAIL;
                                }
                                if ($this->ToBool($this->_enforcevalidemail) === true) {
                                    for ($k=0;$k<count($this->_addresses);$k++) {
                                        if (strtolower($sendAsEmail) == strtolower($this->_addresses[$k])) {
                                            $this->_sendAsEmailOverride = $sendAsEmail;
                                            break;
                                        }
                                    }
                                } else {
                                    $this->_sendAsEmailOverride = $sendAsEmail;
                                }
                                break;
                            case 'serverinvitereply':
                                for ($k=1;$k<count($parts);$k++) {
                                    $setting = explode( '=', $parts[$k]);
                                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Process Setting _serverInviteReplyOverride for DeviceID ['.$setting[0].'] with value ['.$setting[1].']' );
                                    if (strtolower($setting[0]) == Request::GetDeviceID()) {
                                        $this->_serverInviteReplyOverride = ($this->ToBool($setting[1]) === true);
                                        $this->_serverInviteReply = $this->_serverInviteReplyOverride;
                                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Setting _serverInviteReplyOverride for device to ['.$this->_serverInviteReplyOverride.']'); 
                                    }
                                }    
                                break;
                            default:
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Directive type ['.$parts[0].'] is INVALID' );
                        }
                        unset($parts);
                    }
                    unset($directives);
                }
            }

            if (defined('ZIMBRA_DISABLE_MESSAGES') && (ZIMBRA_DISABLE_MESSAGES === true)) { $user['message'][0]['active'] = 'false'; }
            if (defined('ZIMBRA_DISABLE_CONTACTS') && (ZIMBRA_DISABLE_CONTACTS === true)) { $user['contact'][0]['active'] = 'false'; }
            if (defined('ZIMBRA_DISABLE_APPOINTMENTS') && (ZIMBRA_DISABLE_APPOINTMENTS === true)) { $user['appointment'][0]['active'] = 'false'; }
            if (defined('ZIMBRA_DISABLE_TASKS') && (ZIMBRA_DISABLE_TASKS === true)) { $user['task'][0]['active'] = 'false'; }
            if (defined('ZIMBRA_DISABLE_NOTES') && (ZIMBRA_DISABLE_NOTES === true)) { $user['note'][0]['active'] = 'false'; }
            if (defined('ZIMBRA_DISABLE_DOCUMENTS') && (ZIMBRA_DISABLE_DOCUMENTS === true)) { $user['document'][0]['active'] = 'false'; }

/*
            // Update $_userFolderTypeActive with derived settings. System configs override user directives.
            foreach ( $this->_userFolderTypeActive as $key=>$value ) {
                if ($user[$key][0]['active'] == 'false') {
                    $this->_userFolderTypeActive[$key] = false;
                }
            }
*/
            // Override User settings with Zimbra account attribute disables.
            foreach ( $this->_userFolderTypeActive as $key=>$value ) {
                if ($value == false) {
                    $user[$key][0]['active'] = 'false';
                }
            }

            unset( $key );
            unset( $value );
            for ($i=0;$i<$totalfolders;$i++) {
                // Default folder "view" to message - as older zimbra releases did not populate the view property
                if (!isset($folders[$i]['view']) || $folders[$i]['view'] == "") {
                    $folders[$i]['view'] = "message";
                }
                if ($folders[$i]['view'] == "task" && strtolower(substr($folders[$i]['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
                    $folders[$i]['view'] = "note";
                    $user[$folders[$i]['view']][0]['active'] = 'true';
                    if (strtolower($folders[$i]['name']) == "notes") {
                        $user[$folders[$i]['view']][0]['primary'][0]['id'] = $folders[$i]['id'];
                    }
                }

                // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                //                         Also, don't include *SyncConfig* folder or "Emailed Contacts" if they are configured for exclusion.
                $smartFolderExcluded = ((substr($folders[$i]['name'],-1) == "-") || (strtolower($folders[$i]['name']) == "*syncconfig*") || (($this->_ignoreEmailedContacts) && ($folders[$i]['id'] == '13')));
                $primaryFolder = (isset($user[$folders[$i]['view']][0]['primary']) && isset($user[$folders[$i]['view']][0]['primary'][0]['identifier']) &&
                                  (($user[$folders[$i]['view']][0]['primary'][0]['identifier'] == 'id') && ($user[$folders[$i]['view']][0]['primary'][0]['id'] == $folders[$i]['id'])) ||
                                  (($user[$folders[$i]['view']][0]['primary'][0]['identifier'] == 'name') && ($user[$folders[$i]['view']][0]['primary'][0]['name'] == $folders[$i]['name'])));
                $virtualExcluded = ($this->_deviceMultiFolderSupport[$folders[$i]['view']] === false) && ($primaryFolder === false) && ($this->ToBool($user[$folders[$i]['view']][0]['virtual']) !== true);

                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  "Is Folder [".$folders[$i]['name']."] SmartExcluded ? [".$smartFolderExcluded."] Primary ? [".$primaryFolder."] VirtualExcluded ? [".$virtualExcluded."]" );

                if ( (!$smartFolderExcluded) &&
                     (!$virtualExcluded)) {
                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Process Folder ['.$folders[$i]['name'].']');

                    $this->ProcessZimbraSmartFolderRecursive($user, "folder", $parentid, $parentpath, $folders[$i] );
                }
            }
            unset($folders);

            for ($i=0;$i<$totallinks;$i++) {
                // Default folder "view" to message - as older zimbra releases did not populate the view property
                if (!isset($links[$i]['view']) || $links[$i]['view'] == "") {
                    $links[$i]['view'] = "message";
                }
                if ($links[$i]['view'] == "task" && strtolower(substr($links[$i]['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
                    $links[$i]['view'] = "note";
                    $user[$links[$i]['view']][0]['active'] = 'true';
                    if (strtolower($links[$i]['name']) == "notes") {
                        $user[$links[$i]['view']][0]['primary'][0]['id'] = $links[$i]['id'];
                    }
                }
                // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                $smartFolderExcluded = (substr($links[$i]['name'],-1) == "-");
                $primaryFolder = (isset($user[$links[$i]['view']][0]['primary']) && isset($user[$links[$i]['view']][0]['primary'][0]['identifier']) &&
                                  (($user[$links[$i]['view']][0]['primary'][0]['identifier'] == 'id') && ($user[$links[$i]['view']][0]['primary'][0]['id'] == $links[$i]['id'])) ||
                                  (($user[$links[$i]['view']][0]['primary'][0]['identifier'] == 'name') && ($user[$links[$i]['view']][0]['primary'][0]['name'] == $links[$i]['name'])));
                $virtualExcluded = ($this->_deviceMultiFolderSupport[$links[$i]['view']] === false) && ($primaryFolder === false) && ($this->ToBool($user[$links[$i]['view']][0]['virtual']) !== true);

                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  "Is Link Folder [".$links[$i]['name']."] SmartExcluded ? [".$smartFolderExcluded."] Primary ? [".$primaryFolder."] VirtualExcluded ? [".$virtualExcluded."]" );

                if ( (!$smartFolderExcluded) &&
                     (!$virtualExcluded)) {
                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Process Link ['.$links[$i]['name'].']');

                    $this->ProcessZimbraSmartFolderRecursive($user, "link", $parentid, $parentpath, $links[$i] );
                }
            }
            unset($links);

            for ($i=0;$i<$totalsearches;$i++) {
                // Default folder "view" to message - as older zimbra releases did not populate the view property
                if (!isset($searches[$i]['view']) || $searches[$i]['view'] == "") {
                    $searches[$i]['view'] = "message";
                }
                if ($searches[$i]['view'] == "task" && strtolower(substr($searches[$i]['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
                    $searches[$i]['view'] = "note";
                    $user[$searches[$i]['view']][0]['active'] = 'true';
                    if (strtolower($searches[$i]['name']) == "notes") {
                        $user[$searches[$i]['view']][0]['primary'][0]['id'] = $searches[$i]['id'];
                    }
                }
                // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                $smartFolderExcluded = (substr($searches[$i]['name'],-1) == "-");
                $primaryFolder = (isset($user[$searches[$i]['view']][0]['primary']) && isset($user[$searches[$i]['view']][0]['primary'][0]['identifier']) &&
                                  (($user[$searches[$i]['view']][0]['primary'][0]['identifier'] == 'id') && ($user[$searches[$i]['view']][0]['primary'][0]['id'] == $searches[$i]['id'])) ||
                                  (($user[$searches[$i]['view']][0]['primary'][0]['identifier'] == 'name') && ($user[$searches[$i]['view']][0]['primary'][0]['name'] == $searches[$i]['name'])));
                $virtualExcluded = ($this->_deviceMultiFolderSupport[$searches[$i]['view']] === false) && ($primaryFolder === false) && ($this->ToBool($user[$searches[$i]['view']][0]['virtual']) !== true);

                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  "Is Search Folder [".$searches[$i]['name']."] SmartExcluded ? [".$smartFolderExcluded."] Primary ? [".$primaryFolder."] VirtualExcluded ? [".$virtualExcluded."]" );

                if ( (( $searches[$i]['types'] == "message" ) || ( $searches[$i]['types'] == "conversation" )) &&
                     (!$smartFolderExcluded) &&
                     (!$virtualExcluded)) {
                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSmartFolders(): ' .  'Process Search ['.$searches[$i]['name'].']');

                    $this->ProcessZimbraSmartFolderRecursive($user, "search", $parentid, $parentpath, $searches[$i] );
                }
            }
            unset($searches);

        return true;
    } // end GetZimbraSmartFolders


    function ProcessZimbraSmartFolderRecursive(&$user, $type, $parentid, $parentpath, $folder ) {

        if (defined('ZIMBRA_DEBUG')) {
            $debugSetup = (stripos(ZIMBRA_DEBUG, 'setup') !== false);
        } else $debugSetup = false;

        $j = count($this->_folders);

        $this->_folders[$j] = new stdClass();
        $this->_folders[$j]->id = $folder['id'];
        $folderName = str_replace("'","",str_replace("\"","",$folder['name']));
        $this->_folders[$j]->name = $folderName;
        $this->_folders[$j]->parentid = $parentid;

        // Create Path String (String Of Complete Folder Path Using Names; Delimited By A Slash)
        if ($parentpath == "user_root") {
            $this->_folders[$j]->path = $folderName;
        } else {
            $this->_folders[$j]->path = $parentpath .'/'. $folderName;
        }

        // Save i4ms flag for checking if AlterPingChanges use
        if (isset($folder['i4ms'])) {
            $this->_folders[$j]->i4ms = $folder['i4ms'];
        } else {
            $this->_folders[$j]->i4ms = "";
        }

        if (isset($folder['s'])) {
            $this->_folders[$j]->s = $folder['s'];
        } else {
            $this->_folders[$j]->s = "";
        }

        if (isset($folder['n'])) {
            $this->_folders[$j]->n = $folder['n'];
        } else {
            $this->_folders[$j]->n = "";
        }

        if (isset($folder['perm'])) {
            $this->_folders[$j]->perm = $folder['perm'];
        } else {
            $this->_folders[$j]->perm = "";
        }

        if (isset($folder['zid'])) {
            if (isset($folder['owner'])) {
                if (!isset($this->_shareOwners[$folder['zid']])) {
                    $this->_shareOwners[$folder['zid']] = $folder['owner'];
                }
            } else {
                ZLog::Write(LOGLEVEL_WARN, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' . 'Possible orphaned share - Folder ['.$folderName.'] has zid ['.$folder['zid'].'] but no owner - Folder should be removed from the Web client' );
            }
        }

        // If Linked Folder (ID contains colon), Use a Different Folder ID Format
        if (strrpos($folder['id'],':') !== false) {
            $parts = explode(":",$folder['id']);
            $zid = $parts[0];
            $rid = $parts[1];
        } else {
            if (isset($folder['zid'])) {
                $zid = $folder['zid'];
                $rid = $folder['rid'];
            } 
        }

        if (isset($folder['view']) && $folder['view'] <> '') {
            $this->_folders[$j]->view = $folder['view'];
        } else {
            $folder['view'] = "message";
            $this->_folders[$j]->view = "message";
        }

        if ($folder['view'] == "task" && strtolower(substr($folder['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
            $folder['view'] = "note";
            $user[$folder['view']][0]['active'] = 'true';
            if (strtolower($folder['name']) == "notes") {
                $user[$folder['view']][0]['primary'][0]['id'] = $folder['id'];
            }
        }

        // Add a 'N' after the folder designation and before the folder Id for use in Hierarchy Change Detection
        $noteFlag = ($folder['view'] == "note" ? 'N' : '' );
        if (isset($zid)) {
            if (isset($this->_linkOwners[$zid])) {
                $owner = $this->_linkOwners[$zid];
            } else {
                // First Owner - Count will be zero, second owner - count will be one, etc.
                $this->_linkOwners[$zid] = count($this->_linkOwners);
                $owner = $this->_linkOwners[$zid];
            }

            if ($type == 'search') {
                $this->_folders[$j]->devid = 'SL'.$noteFlag.$owner.'-'.$rid;
            } else {
                $this->_folders[$j]->devid = 'FL'.$noteFlag.$owner.'-'.$rid;
            }
            $this->_folders[$j]->linkid = $zid.":".$rid;
            if (isset($this->_shareOwners[$zid])) {
                $this->_folders[$j]->owner = $this->_shareOwners[$zid];
            } else {
                $this->_folders[$j]->owner = "";
            }
        } else {
            if ($type == 'search') {
                $this->_folders[$j]->devid = 's'.$noteFlag.$folder['id'];
            } else {
                $this->_folders[$j]->devid = 'f'.$noteFlag.$folder['id'];
            }
            $this->_folders[$j]->linkid = "";
            $this->_folders[$j]->owner = "";
        }

        $this->_folders[$j]->include = (($this->ToBool($user[$folder['view']][0]['active']) === true) ? 1 : 0);
        $this->_folders[$j]->recursive = 1;

        if (isset($folder['perm'])) {
            $this->_folders[$j]->perm = $folder['perm'];
        } else {
            $this->_folders[$j]->perm = '';
        }

        if ($this->_folders[$j]->view == "document") {
            // Need document folders active to allow building up list of Briefcase folders for Sharepoint access.
            // But once they are identified, we do not want them output to the device.			
            $this->_folders[$j]->include = 0; 

            $documentLibrary = array();
            $documentLibrary['longid'] = $folder['id'];
            if (isset($folder['absFolderPath']) && ($this->_folders[$j]->linkid == '')) {
                $documentLibrary['linkid'] = $folder['absFolderPath'];
            } else {
                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  'absFolderPath not set OR shared folder - using derived path instead ['. '/' . $this->_folders[$j]->path .']');
                $documentLibrary['linkid'] = '/' . $this->_folders[$j]->path;
            }
            $documentLibrary['displayname'] = $folder['name'];
            $documentLibrary['isfolder'] = 1;
            $documentLibrary['creationdate'] = 0;
            $documentLibrary['lastmodifieddate'] = 0;
            $documentLibrary['ishidden'] = 0;
            $documentLibrary['contentlength'] = (isset( $folder['n'] ) ? $folder['n'] : 0 );
            $documentLibrary['contenttype'] = '';
            $documentLibrary['parentid'] = $folder['l'];
            if (isset($folder['owner'])) {
                $documentLibrary['ownerid'] = $folder['owner'];
            } else {
                $documentLibrary['ownerid'] = $this->_accountName;
            }
            $documentLibrary['zimbralinkid'] = $this->_folders[$j]->linkid;
            $this->_documentLibrariesPathToIdIndex[$documentLibrary['linkid']] = count($this->_documentLibraries);
            $this->_documentLibraries[] = $documentLibrary;
            unset( $documentLibrary );
        }

        // Flag Folders That Are Part Of External Accounts
        $this->_folders[$j]->external = 0;
        if (isset($folder['f'])) {
            $flags = $this->GetFlags($folder['f']);
            if ($flags["external"] == 1) {
                $this->_folders[$j]->external = 1;
            }
        }

        $this->_folders[$j]->stats = $this->_folders[$j]->name . "-" . (isset($folder['rev']) ? $folder['rev'] : "") . "-" . (isset($folder['ms']) ? $folder['ms'] : "") . "-" . (isset($folder['n']) ? $folder['n'] : "");
        $this->_folders[$j]->stats .= "-" . (isset($folder['s']) ? $folder['s'] : "") . "-" . (isset($folder['i4ms']) ? $folder['i4ms'] : "") . "-" . (isset($folder['i4next']) ? $folder['i4next'] : "") ;

        if ($type == 'search') {
            $this->_folders[$j]->search = $folder['query'];						
        } else {
            $this->_folders[$j]->search = '';   // Used Only For Search Folders
        }

  
        if ($this->_folders[$j]->include == 1 && (
            ($user[$this->_folders[$j]->view][0]['primary'][0]['identifier'] == 'id' && $user[$this->_folders[$j]->view][0]['primary'][0]['id'] == $this->_folders[$j]->id) ||
            ($user[$this->_folders[$j]->view][0]['primary'][0]['identifier'] == 'name' && $user[$this->_folders[$j]->view][0]['primary'][0]['name'] == $this->_folders[$j]->name)
         )) {
            if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  'Setting primary for ['.$this->_folders[$j]->view.'] to be ['.$this->_folders[$j]->name.']');
            $this->_folders[$j]->primary = 1;

            if(strtolower($this->_folders[$j]->view) == 'contact') {
                $this->_primary['contact'] = $this->_folders[$j]->devid;
            }elseif(strtolower($this->_folders[$j]->view) == 'appointment') {
                $this->_primary['appointment'] = $this->_folders[$j]->devid;
            }elseif(strtolower($this->_folders[$j]->view) == 'task') {
                $this->_primary['task'] = $this->_folders[$j]->devid;
            }elseif(strtolower($this->_folders[$j]->view) == 'note') {
                $this->_primary['note'] = $this->_folders[$j]->devid;
            }
        } else {
            $this->_folders[$j]->primary = 0;
        }

        // Process Virtual Folders
        if ($this->_folders[$j]->include == 1 &&
                $this->ToBool($user[$this->_folders[$j]->view][0]['virtual']) === true &&
                $this->_folders[$j]->primary == 0 &&
                ($this->_deviceMultiFolderSupport[$this->_folders[$j]->view] === false)) {
            $this->_folders[$j]->virtual = 1;
            if(strtolower($this->_folders[$j]->view) == 'contact') {
                $this->_virtual['contact'][] = $this->_folders[$j]->devid;
            }elseif(strtolower($this->_folders[$j]->view) == 'appointment') {
                $this->_virtual['appointment'][] = $this->_folders[$j]->devid;
            }elseif(strtolower($this->_folders[$j]->view) == 'task') {
                $this->_virtual['task'][] = $this->_folders[$j]->devid;
            }elseif(strtolower($this->_folders[$j]->view) == 'note') {
                $this->_virtual['note'][] = $this->_folders[$j]->devid;
            }
        } else {
            $this->_folders[$j]->virtual = 0;
        }

        // Smart Folder Handling - Don't include recurse into folders whose name ends with a "." character.
        $smartFolderNoRecursion = (substr($folder['name'],-1) == ".");
        if ($smartFolderNoRecursion) {
            if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Folder [".$folder['name']."] ends with DOT - Don't Recurse" );
            $this->_folders[$j]->recursive = 0;
        } else {
            if (isset($folder['folder'])) {
                $totalfolders = count($folder['folder']);
                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Folder [".$folder['name']."] has [".$totalfolders."] child folders" );
                for ($i=0;$i<$totalfolders;$i++) {
                    // Default folder "view" to message - as older zimbra releases did not populate the view property
                    if (!isset($folder['folder'][$i]['view']) || $folder['folder'][$i]['view'] == "") {
                        $folder['folder'][$i]['view'] = "message";
                    }
                    if ($folder['folder'][$i]['view'] == "task" && strtolower(substr($folder['folder'][$i]['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
                        $folder['folder'][$i]['view'] = "note";
                        $user[$folder['folder'][$i]['view']][0]['active'] = 'true';
                        if (strtolower($folder['folder'][$i]['name']) == "notes") {
                            $user[$folder['folder'][$i]['view']][0]['primary'][0]['id'] = $folder['folder'][$i]['id'];
                        }
                    }
                    // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                    $smartFolderExcluded = ((substr($folder['folder'][$i]['name'],-1) == "-") || (strtolower($folder['folder'][$i]['name']) == "*syncconfig*"));
                    $primaryFolder = (isset($user[$folder['folder'][$i]['view']][0]['primary']) && isset($user[$folder['folder'][$i]['view']][0]['primary'][0]['identifier']) &&
                                      (($user[$folder['folder'][$i]['view']][0]['primary'][0]['identifier'] == 'id') && ($user[$folder['folder'][$i]['view']][0]['primary'][0]['id'] == $folder['folder'][$i]['id'])) ||
                                      (($user[$folder['folder'][$i]['view']][0]['primary'][0]['identifier'] == 'name') && ($user[$folder['folder'][$i]['view']][0]['primary'][0]['name'] == $folder['folder'][$i]['name'])));
                    $virtualExcluded = ($this->_deviceMultiFolderSupport[$folder['folder'][$i]['view']] === false) && ($primaryFolder === false) && ($this->ToBool($user[$folder['folder'][$i]['view']][0]['virtual']) !== true);

                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Is Child Folder [".$folder['folder'][$i]['name']."] SmartExcluded ? [".$smartFolderExcluded."] Primary ? [".$primaryFolder."] VirtualExcluded ? [".$virtualExcluded."]" );

                    if ( (!$smartFolderExcluded) &&
                         (!$virtualExcluded) ) {
                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Process Child Folder [".$folder['folder'][$i]['name']."]" );

                        $this->ProcessZimbraSmartFolderRecursive($user, "folder", $this->_folders[$j]->devid, $this->_folders[$j]->path, $folder['folder'][$i] );
                    }
                }
            }

            if (isset($folder['link'])) {
                $totallinks = count($folder['link']);
                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Folder [".$folder['name']."] has [".$totallinks."] child links" );
                for ($i=0;$i<$totallinks;$i++) {
                    // Default folder "view" to message - as older zimbra releases did not populate the view property
                    if (!isset($folder['link'][$i]['view']) || $folder['link'][$i]['view'] == "") {
                        $folder['link'][$i]['view'] = "message";
                    }
                    if ($folder['link'][$i]['view'] == "task" && strtolower(substr($folder['link'][$i]['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
                        $folder['link'][$i]['view'] = "note";
                        $user[$folder['link'][$i]['view']][0]['active'] = 'true';
                        if (strtolower($folder['link'][$i]['name']) == "notes") {
                            $user[$folder['link'][$i]['view']][0]['primary'][0]['id'] = $folder['link'][$i]['id'];
                        }
                    }
                    // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                    $smartFolderExcluded = (substr($folder['link'][$i]['name'],-1) == "-");
                    $primaryFolder = (isset($user[$folder['link'][$i]['view']][0]['primary']) && isset($user[$folder['link'][$i]['view']][0]['primary'][0]['identifier']) &&
                                      (($user[$folder['link'][$i]['view']][0]['primary'][0]['identifier'] == 'id') && ($user[$folder['link'][$i]['view']][0]['primary'][0]['id'] == $folder['link'][$i]['id'])) ||
                                      (($user[$folder['link'][$i]['view']][0]['primary'][0]['identifier'] == 'name') && ($user[$folder['link'][$i]['view']][0]['primary'][0]['name'] == $folder['link'][$i]['name'])));
                    $virtualExcluded = ($this->_deviceMultiFolderSupport[$folder['link'][$i]['view']] === false) && ($primaryFolder === false) && ($this->ToBool($user[$folder['link'][$i]['view']][0]['virtual']) !== true);

                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Is Child Link [".$folder['link'][$i]['name']."] SmartExcluded ? [".$smartFolderExcluded."] Primary ? [".$primaryFolder."] VirtualExcluded ? [".$virtualExcluded."]" );

                    if ( (!$smartFolderExcluded) &&
                         (!$virtualExcluded) ) {
                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Process Child Link [".$folder['link'][$i]['name']."]" );

                        $this->ProcessZimbraSmartFolderRecursive($user, "link", $this->_folders[$j]->devid, $this->_folders[$j]->path, $folder['link'][$i] );
                    }
                }
            }

            if (isset($folder['search'])) {
                $totalsearches = count($folder['search']);
                if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Folder [".$folder['name']."] has [".$totalsearches."] child searches" );
                for ($i=0;$i<$totalsearches;$i++) {
                    // Default folder "view" to message - as older zimbra releases did not populate the view property
                    if (!isset($folder['search'][$i]['view']) || $folder['search'][$i]['view'] == "") {
                        $folder['search'][$i]['view'] = "message";
                    }
                    if ($folder['search'][$i]['view'] == "task" && strtolower(substr($folder['search'][$i]['name'],0,5)) == "notes" && $this->_userFolderTypeActive['note'] == true) {
                        $folder['search'][$i]['view'] = "note";
                        $user[$folder['search'][$i]['view']][0]['active'] = 'true';
                        if (strtolower($folder['search'][$i]['name']) == "notes") {
                            $user[$folder['search'][$i]['view']][0]['primary'][0]['id'] = $folder['search'][$i]['id'];
                        }
                    }
                    // Smart Folder Handling - Don't include folders whose name ends with a "-" character.
                    $smartFolderExcluded = (substr($folder['search'][$i]['name'],-1) == "-");
                    $primaryFolder = (isset($user[$folder['search'][$i]['view']][0]['primary']) && isset($user[$folder['search'][$i]['view']][0]['primary'][0]['identifier']) &&
                                      (($user[$folder['search'][$i]['view']][0]['primary'][0]['identifier'] == 'id') && ($user[$folder['search'][$i]['view']][0]['primary'][0]['id'] == $folder['search'][$i]['id'])) ||
                                      (($user[$folder['search'][$i]['view']][0]['primary'][0]['identifier'] == 'name') && ($user[$folder['search'][$i]['view']][0]['primary'][0]['name'] == $folder['search'][$i]['name'])));
                    $virtualExcluded = ($this->_deviceMultiFolderSupport[$folder['search'][$i]['view']] === false) && ($primaryFolder === false) && ($this->ToBool($user[$folder['search'][$i]['view']][0]['virtual']) !== true);

                    if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Is Child Search [".$folder['search'][$i]['name']."] SmartExcluded ? [".$smartFolderExcluded."] Primary ? [".$primaryFolder."] VirtualExcluded ? [".$virtualExcluded."]" );

                    if ( (( $folder['search'][$i]['types'] == "message" ) || ( $folder['search'][$i]['types'] == "conversation" )) &&
                         (!$smartFolderExcluded) &&
                         (!$virtualExcluded) ) {
                        if ($debugSetup) ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' .  "Process Child Search [".$folder['search'][$i]['name']."]" );

                        $this->ProcessZimbraSmartFolderRecursive($user, "search", $this->_folders[$j]->devid, $this->_folders[$j]->path, $folder['search'][$i] );
                    }
                }
            }

        }

    } // END ProcessZimbraSmartFolderRecursive


    function ProcessZimbraSharedFolderRecursive($folder ) {

        // If the folder exists in the index table then capture the required data
        if ( isset($this->_idToIndex[$folder['id']]) ) {  
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ID found: Folder ['. $folder['id'] .'] - Index ['. $this->_idToIndex[$folder['id']] .']' );
		
            // Save i4ms flag for checking if AlterPingChanges use
            if (isset($folder['i4ms'])) {
                $this->_folders[$this->_idToIndex[$folder['id']]]->i4ms = $folder['i4ms'];
            }

            if (isset($folder['s'])) {
                $this->_folders[$this->_idToIndex[$folder['id']]]->s = $folder['s'];
            }

            if (isset($folder['n'])) {
                $this->_folders[$this->_idToIndex[$folder['id']]]->n = $folder['n'];
            }

            $statsBefore = $this->_folders[$this->_idToIndex[$folder['id']]]->stats;

            $this->_folders[$this->_idToIndex[$folder['id']]]->stats = $this->_folders[$this->_idToIndex[$folder['id']]]->name . "-" . (isset($folder['rev']) ? $folder['rev'] : "") . "-" . (isset($folder['ms']) ? $folder['ms'] : "") . "-" . (isset($folder['n']) ? $folder['n'] : "");
            $this->_folders[$this->_idToIndex[$folder['id']]]->stats .= "-" . (isset($folder['s']) ? $folder['s'] : "") . "-" . (isset($folder['i4ms']) ? $folder['i4ms'] : "") . "-" . (isset($folder['i4next']) ? $folder['i4next'] : "") ;

            if ($statsBefore != $this->_folders[$this->_idToIndex[$folder['id']]]->stats) {
                $this->_saveCacheOnLogoff = true;
            }

        }

        if (isset($folder['folder'])) {
            $totalfolders = count($folder['folder']);
            for ($i=0;$i<$totalfolders;$i++) {
                $this->ProcessZimbraSharedFolderRecursive( $folder['folder'][$i] );
            }
        }

    } // END ProcessZimbraSharedFolderRecursive


    /** GetUserProfileXML
     *   Function exclusively used by function Setup to process user Profile XML files.
     */
    function GetUserProfileXML() {

        $user = array();
 
        if (defined('ZIMBRA_USER_DIR')) {
            $userDir = ZIMBRA_USER_DIR;
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'ZIMBRA_USER_DIR not defined - Using default "zimbra"');
            $userDir = 'zimbra';
        }

        $user_file = BASE_PATH . $userDir . "/" . $this->_uid . '.xml';
        $user_file_xml = '';
        $defaultXmlInUse = false;

        // If specific User XML file does not exist - check if a default one is being used
        if (!file_exists($user_file)) {
            if (defined('ZIMBRA_USER_XML_DEFAULT') && ('ZIMBRA_USER_XML_DEFAULT' != "")) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'ZIMBRA_USER_XML_DEFAULT is defined - Using default User XML settings');
                $user_file = BASE_PATH . $userDir . "/" . ZIMBRA_USER_XML_DEFAULT;
                $defaultXmlInUse = true;
            }
        }

        if (file_exists($user_file)) {
            $contents = array(); 

            $file = fopen($user_file,"r");
            while(!feof($file)) {
                $user_file_xml = $user_file_xml . fgets($file);
            }
            $contents = $this->MakeXMLTree($user_file_xml);

            $match = '';
            $default = ''; 
            if ((isset($contents['zimbrabackend'][0]['user']) && strtolower($contents['zimbrabackend'][0]['user']) == strtolower($this->_uid)) || ($defaultXmlInUse == true)) {
                if (isset($contents['zimbrabackend'][0]['profile'])) {
                    $count = count($contents['zimbrabackend'][0]['profile']);
                } else $count = 0;

                for ($i=0;$i<$count;$i++) {
                    if (   (!isset($contents['zimbrabackend'][0]['profile'][$i]['id']) )
                        || (isset($contents['zimbrabackend'][0]['profile'][$i]['id']) && ($contents['zimbrabackend'][0]['profile'][$i]['id'] == "") )
                    ) {
                        if ($default == '') { // This profile is the first one with a "" id
                            $default = $i;
                        } 
                    }
                    if (isset($contents['zimbrabackend'][0]['profile'][$i]['id']) && strtolower($contents['zimbrabackend'][0]['profile'][$i]['id']) == strtolower($this->_domain)) {
                        $match = $i;
                        break;
                    }
                }
            }
            if (is_numeric($match)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'Using Profile "'. $contents['zimbrabackend'][0]['profile'][$i]['id'] .'" For User "'. $contents['zimbrabackend'][0]['user'] .'"');
                $user = $contents['zimbrabackend'][0]['profile'][$match];
            } else if (is_numeric($default)) {
				ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'Using Default Profile For User "'. $contents['zimbrabackend'][0]['user'] .'"');
                $user = $contents['zimbrabackend'][0]['profile'][$default];
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'No Default Profile or Matching Profile "'. $this->_domain .'" Found For User "'. $contents['zimbrabackend'][0]['user'] .'" - Default Rules Will Apply');
            }
            fclose($file);
            unset( $contents);
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'No XML Profile File ['.$user_file.'] Found For User - Default Rules Will Apply');
        }


        // Set Folder Criteria Per User File or to Defaults If No User File
        if (isset($user['usehtml']) && $this->IsBool($user['usehtml'])) {
            $this->_useHTML = $this->ToBool($user['usehtml']);
        } else {
            $this->_useHTML = $this->ToBool(ZIMBRA_HTML);
        }

        if (!($defaultXmlInUse) && (isset($user['sendasemail']))) {
            if (!defined('ZIMBRA_ENFORCE_VALID_EMAIL')) {
                $this->_enforcevalidemail = 'false';
            } else {
                $this->_enforcevalidemail = ZIMBRA_ENFORCE_VALID_EMAIL;
            }
            if ($this->ToBool($this->_enforcevalidemail) === true) {
                for ($i=0;$i<=count($this->_addresses);$i++) {
                    if (strtolower($user['sendasemail']) == strtolower($this->_addresses[$i])) {
                        $this->_sendAsEmailOverride = $user['sendasemail'];
                        if (isset($user['sendasname'])) {
                            $this->_sendAsNameOverride = $user['sendasname'];
                        }
                        break;
                    }
                }
            } else {
                if (!empty($user['sendasemail'])) {
                    $this->_sendAsEmailOverride = $user['sendasemail'];
                    if (isset($user['sendasname'])) {
                        $this->_sendAsNameOverride = $user['sendasname'];
                    }                    
                }
            }
        }

        if (isset($user['timezone']) && !empty($user['timezone']) && $this->_tzFromDomain === false) {
            $tempTz = date_default_timezone_get();
            if (date_default_timezone_set($user['timezone']) === true) {
                $this->_tz = $user['timezone'];
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetUserProfileXML(): ' . 'Provided Timezone In User File NOT VALID: Check list at (http://www.php.net/manual/en/timezones.php): '.$user['timezone']);
            }
            date_default_timezone_set( $tempTz );
        }

        if (defined('ZIMBRA_DISABLE_MESSAGES') && (ZIMBRA_DISABLE_MESSAGES === true)) { 

            $user['message'][0]['active'] = 'false'; 

        } else {

            // Process Message Node
            if (!isset($user['message'][0]['active']) || !$this->IsBool($user['message'][0]['active'])) {
                $user['message'][0]['active'] = 'true';
            }

            if (isset($user['message'][0]['exclude'][0])) {
                $user['message'][0]['folders'][0]['filtermethod'][0] = 'exclude';
                $user['message'][0]['folders'][0]['list'] = $user['message'][0]['exclude'];
                if (isset($user['message'][0]['exclude'][0]['id'][0])) {
                    $user['message'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['message'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else if (isset($user['message'][0]['include'][0])) {
                $user['message'][0]['folders'][0]['filtermethod'][0] = 'include';
                $user['message'][0]['folders'][0]['list'] = $user['message'][0]['include'];
                if (isset($user['message'][0]['include'][0]['id'][0])) {
                    $user['message'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['message'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else {
                $user['message'][0]['folders'][0]['filtermethod'][0] = 'none';
            }
            $user['message'][0]['virtual'] = 'false';
            $user['message'][0]['primary'][0]['identifier'] = 'name';
            $user['message'][0]['primary'][0]['name'] = 'Inbox';
        }

        if (defined('ZIMBRA_DISABLE_CONTACTS') && (ZIMBRA_DISABLE_CONTACTS === true)) { 

            $user['contact'][0]['active'] = 'false'; 

        } else {

            // Process Contact Node
            if (!isset($user['contact'][0]['active']) || !$this->IsBool($user['contact'][0]['active'])) {
                $user['contact'][0]['active'] = 'true';
            }

            if (!isset($user['contact'][0]['virtual']) || !$this->IsBool($user['contact'][0]['virtual'])) {
                $user['contact'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_CONTACTS') ? (($this->ToBool(ZIMBRA_VIRTUAL_CONTACTS)) ? 'true' : 'false') : 'true' );
            }

            if (isset($user['contact'][0]['primary'][0]['id'])) {
                $user['contact'][0]['primary'][0]['identifier'] = 'id';
            } else if (isset($user['contact'][0]['primary'][0]['name'])) {
                $user['contact'][0]['primary'][0]['identifier'] = 'name';
            } else {
                $user['contact'][0]['primary'][0]['identifier'] = 'name';
                $user['contact'][0]['primary'][0]['name'] = 'Contacts';
            }

            if (isset($user['contact'][0]['exclude'])) {
                $user['contact'][0]['folders'][0]['filtermethod'][0] = 'exclude';
                $user['contact'][0]['folders'][0]['list'] = $user['contact'][0]['exclude'];
                if (isset($user['contact'][0]['exclude'][0]['id'][0])) {
                    $user['contact'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['contact'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else if (isset($user['contact'][0]['include'])) {
                $user['contact'][0]['folders'][0]['filtermethod'][0] = 'include';
                $user['contact'][0]['folders'][0]['list'] = $user['contact'][0]['include'];
                if (isset($user['contact'][0]['include'][0]['id'][0])) {
                    $user['contact'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['contact'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else {
                if ($this->_ignoreEmailedContacts) {
                    $user['contact'][0]['folders'][0]['filtermethod'][0] = 'exclude';
                    $user['contact'][0]['folders'][0]['identifier'][0] = 'name';
                    $user['contact'][0]['folders'][0]['list'][0]['name'] = 'Emailed Contacts';
                } else {
                    $user['contact'][0]['folders'][0]['filtermethod'][0] = 'none';
                }
            }
        }

        if (defined('ZIMBRA_DISABLE_APPOINTMENTS') && (ZIMBRA_DISABLE_APPOINTMENTS === true)) { 

            $user['appointment'][0]['active'] = 'false'; 

        } else {

            // Process Appointment Node
            if (!isset($user['appointment'][0]['active']) || !$this->IsBool($user['appointment'][0]['active'])) {
                $user['appointment'][0]['active'] = 'true';
            }

            if (!isset($user['appointment'][0]['virtual']) || !$this->IsBool($user['appointment'][0]['virtual'])) {
                $user['appointment'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_APPOINTMENTS') ? (($this->ToBool(ZIMBRA_VIRTUAL_APPOINTMENTS)) ? 'true' : 'false') : 'true' );
            }

            if (isset($user['appointment'][0]['primary'][0]['id'])) {
                $user['appointment'][0]['primary'][0]['identifier'] = 'id';
            } else if (isset($user['appointment'][0]['primary'][0]['name'])) {
                $user['appointment'][0]['primary'][0]['identifier'] = 'name';
            } else {
                $user['appointment'][0]['primary'][0]['identifier'] = 'name';
                $user['appointment'][0]['primary'][0]['name'] = 'Calendar';
            }

            if (isset($user['appointment'][0]['exclude'])) {
                $user['appointment'][0]['folders'][0]['filtermethod'][0] = 'exclude';
                $user['appointment'][0]['folders'][0]['list'] = $user['appointment'][0]['exclude'];
                if (isset($user['appointment'][0]['exclude'][0]['id'][0])) {
                    $user['appointment'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['appointment'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else if (isset($user['appointment'][0]['include'])) {
                $user['appointment'][0]['folders'][0]['filtermethod'][0] = 'include';
                $user['appointment'][0]['folders'][0]['list'] = $user['appointment'][0]['include'];
                if (isset($user['appointment'][0]['include'][0]['id'][0])) {
                    $user['appointment'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['appointment'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else {
                $user['appointment'][0]['folders'][0]['filtermethod'][0] = 'none';
            }
        }

        if (defined('ZIMBRA_DISABLE_TASKS') && (ZIMBRA_DISABLE_TASKS === true)) { 

            $user['task'][0]['active'] = 'false'; 

        } else {

            // Process Task Node
            if (!isset($user['task'][0]['active']) || !$this->IsBool($user['task'][0]['active'])) {
                $user['task'][0]['active'] = 'true';
            }

            if (!isset($user['task'][0]['virtual']) || !$this->IsBool($user['task'][0]['virtual'])) {
                $user['task'][0]['virtual'] = (defined('ZIMBRA_VIRTUAL_TASKS') ? (($this->ToBool(ZIMBRA_VIRTUAL_TASKS)) ? 'true' : 'false') : 'true' );
            }

            if (isset($user['task'][0]['primary'][0]['id'])) {
                $user['task'][0]['primary'][0]['identifier'] = 'id';
            } else if (isset($user['task'][0]['primary'][0]['name'])) {
                $user['task'][0]['primary'][0]['identifier'] = 'name';
            } else {
                $user['task'][0]['primary'][0]['identifier'] = 'name';
                $user['task'][0]['primary'][0]['name'] = 'Tasks';
            }

            if (isset($user['task'][0]['exclude'])) {
                $user['task'][0]['folders'][0]['filtermethod'][0] = 'exclude';
                $user['task'][0]['folders'][0]['list'] = $user['task'][0]['exclude'];
                if (isset($user['task'][0]['exclude'][0]['id'][0])) {
                    $user['task'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['task'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else if (isset($user['task'][0]['include'])) {
                $user['task'][0]['folders'][0]['filtermethod'][0] = 'include';
                $user['task'][0]['folders'][0]['list'] = $user['task'][0]['include'];
                if (isset($user['task'][0]['include'][0]['id'][0])) {
                    $user['task'][0]['folders'][0]['identifier'][0] = 'id';
                } else {
                    $user['task'][0]['folders'][0]['identifier'][0] = 'name';
                }
            } else {
                $user['task'][0]['folders'][0]['filtermethod'][0] = 'none';
            }
        }

        $user['document'][0]['active'] = 'true';
        $user['document'][0]['virtual'] = 'false';
        $user['document'][0]['primary'][0]['identifier'] = 'id';
        $user['document'][0]['primary'][0]['id'] = '16';  // Briefcase (in English)
        $user['document'][0]['folders'][0]['filtermethod'][0] = 'none';
        // Added wiki to accomodate servers that run, or once ran, version 5.0.x of zimbra
        $user['wiki'][0]['active'] = 'false';
        $user['wiki'][0]['virtual'] = 'false';
        $user['wiki'][0]['primary'][0]['identifier'] = 'id';
        $user['wiki'][0]['primary'][0]['id'] = '16';  // Briefcase (in English)
        $user['wiki'][0]['folders'][0]['filtermethod'][0] = 'none';
        // Added note to accomodate clients that can send Sticky Notes - eg. iOS7
        $user['note'][0]['active'] = 'true';
        $user['note'][0]['virtual'] = 'false';
        $user['note'][0]['primary'][0]['identifier'] = 'id';
        $user['note'][0]['primary'][0]['id'] = '0';  // Notes (in English) - Need to match folder name and set Folder ID
        $user['note'][0]['folders'][0]['filtermethod'][0] = 'none';

        if (defined('ZIMBRA_DISABLE_MESSAGES') && (ZIMBRA_DISABLE_MESSAGES === true)) { $user['message'][0]['active'] = 'false'; }
        if (defined('ZIMBRA_DISABLE_CONTACTS') && (ZIMBRA_DISABLE_CONTACTS === true)) { $user['contact'][0]['active'] = 'false'; }
        if (defined('ZIMBRA_DISABLE_APPOINTMENTS') && (ZIMBRA_DISABLE_APPOINTMENTS === true)) { $user['appointment'][0]['active'] = 'false'; }
        if (defined('ZIMBRA_DISABLE_TASKS') && (ZIMBRA_DISABLE_TASKS === true)) { $user['task'][0]['active'] = 'false'; }
        if (defined('ZIMBRA_DISABLE_NOTES') && (ZIMBRA_DISABLE_NOTES === true)) { $user['note'][0]['active'] = 'false'; }
        if (defined('ZIMBRA_DISABLE_DOCUMENTS') && (ZIMBRA_DISABLE_DOCUMENTS === true)) { $user['document'][0]['active'] = 'false'; }

        $this->_virtual['contact'] = array();
        $this->_virtual['appointment'] = array();
        $this->_virtual['task'] = array();
        $this->_virtual['note'] = array();

        $this->_primary['message'] = "";
        $this->_primary['contact'] = "";
        $this->_primary['appointment'] = "";
        $this->_primary['task'] = "";
        $this->_primary['note'] = "";

/*
        // Update $_userFolderTypeActive with derived settings. System configs override user directives.
        foreach ( $this->_userFolderTypeActive as $key=>$value ) {
            if ($user[$key][0]['active'] == 'false') {
                $this->_userFolderTypeActive[$key] = false;
            }
        }
*/
        // Override User settings with Zimbra account attribute disables.
        foreach ( $this->_userFolderTypeActive as $key=>$value ) {
            if ($value == false) {
                $user[$key][0]['active'] = 'false';
            }
        }
        unset( $key );
        unset( $value );

        return $user;
    } // end GetUserProfileXML


    /** GetZimbraFolders
     *   Function exclusively used by function Setup to process folders and determine which to include.  Code removed from
     *   Setup function and placed in own due to shared (linked) folders.  Setup first calls this function and gets all folders
     *   for user's local mailbox.  This does include shared folders but only the parent.  The Setup function then loops through
     *   calls this function for each parent shared folder to get any children.
     */
    function GetZimbraFolders(&$user, &$response) {


	$lastParentId = "";
	$lastParentDevId = "";
	$lastParentLinkId = ":";
	
        $syncConfigFolderId = "NotFound";
            $array = $this->MakeXMLTree($response, true);
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraFolders(): ' .  print_r($array, true), false );

            $total = count($array);

            $j = count($this->_folders);
            for ($i=0;$i<$total;$i++) {
                // added isset($array[$i]['l']) to next line to avoid trying to include notify folders in the folder list
				// Hopefully, there will be no more BAD Folder's listed below
                if ( isset($array[$i]['l']) && 
                     isset($array[$i]['id']) && 
                     ($array[$i]['tag'] == 'folder' || $array[$i]['tag'] == 'link') ) {

                    $this->_folders[$j] = new stdClass();
                    $this->_folders[$j]->id = $array[$i]['id'];

if (!isset($array[$i]['name'])) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraFolders(): ' .  'BAD Folder: '. print_r($array[$i], true) );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraFolders(): ' .  'SOAP response: '. $response );
}

                    $this->_folders[$j]->name = $array[$i]['name'];

                    if (isset($array[$i]['zid'])) {
                        if (isset($array[$i]['owner'])) {
                            if (!isset($this->_shareOwners[$array[$i]['zid']])) {
                                $this->_shareOwners[$array[$i]['zid']] = $array[$i]['owner'];
                            }
                        } else {
                            ZLog::Write(LOGLEVEL_WARN, 'Zimbra->ProcessZimbraSmartFolderRecursive(): ' . 'Possible orphaned share - Folder ['.$array[$i]['name'].'] has zid ['.$array[$i]['zid'].'] but no owner - Folder should be removed from the Web client' );
                        }
                    }

                    // Indicate Shared (Linked) Folders
                    if ($array[$i]['tag'] == 'link') {
                        $this->_folders[$j]->linkid = $array[$i]['zid'] . ":" . $array[$i]['rid'];
                    } else if (strrpos($array[$i]['id'],':') !== false) {
                        $this->_folders[$j]->linkid = $array[$i]['id'];
                    } else {
                        $this->_folders[$j]->linkid = "";
                    }

                    if ($this->_folders[$j]->linkid != "") {
                        $parts = explode(":",$this->_folders[$j]->linkid);
                    
                        if (isset($this->_shareOwners[$parts[0]])) {
                            $this->_folders[$j]->owner = $this->_shareOwners[$parts[0]];
                        } else {
                            $this->_folders[$j]->owner = "";
                        }
                    } else {
                        $this->_folders[$j]->owner = "";
                    }

                    if (isset($array[$i]['perm'])) {
                        $this->_folders[$j]->perm = $array[$i]['perm'];
                    } else {
                        $this->_folders[$j]->perm = '';
                    }

                    // If Linked Folder (ID contains colon), Use a Different Folder ID Format
                    if (strrpos($array[$i]['id'],':') === false) {
                        $this->_folders[$j]->devid = 'f' . $array[$i]['id'];
						if ($this->_folders[$j]->linkid != "") {
    						$lastParentId = $array[$i]['id'];
    						$lastParentDevId = $this->_folders[$j]->devid;
							$lastParentLinkId = $this->_folders[$j]->linkid;
						}
                    } else {
                        $parts = explode(":",$array[$i]['id']);
                        $this->_folders[$j]->devid = 'FL' . $parts[1];
                    }
                    if (isset($array[$i]['view']) && $array[$i]['view'] <> '') {
                        $this->_folders[$j]->view = $array[$i]['view'];
                    } else {
                        $this->_folders[$j]->view = "message";
                    }

					if ( $array[$i]['l'] == $lastParentLinkId) {
						$parent = $lastParentId;
					} else {
						$parent =  $array[$i]['l'];
					}
					
                    // Create Path String (String Of Complete Folder Path Using Names; Delimited By A Slash)
                    $this->_paths = $this->SetFolderPaths($this->_paths, $array[$i]['id'], $array[$i]['name'], $parent);
                    $this->_folders[$j]->path = $this->_paths[$array[$i]['id']];

                    // Set Parent ID
                    if (isset($this->_paths[$parent])) {
                        if (strtolower($this->_paths[$parent]) == "user_root") {
                            $this->_folders[$j]->parentid = '0';
                        } else {
							if ( $array[$i]['l'] == $lastParentLinkId) {
								$this->_folders[$j]->parentid = $lastParentDevId;
							} elseif (strrpos($array[$i]['l'],':') !== false) {
                                $parts = explode(":",$array[$i]['l']);
                                $this->_folders[$j]->parentid = 'FL' . $parts[1];
                            } else {
                                $this->_folders[$j]->parentid = 'f' . $array[$i]['l'];
                            }
                        }
                    } else {
                        $this->_folders[$j]->parentid = '0';
                    }

                    // Flag Folders That Are Part Of External Accounts
                    $this->_folders[$j]->external = 0;
                    if (isset($array[$i]['f'])) {
                        $flags = $this->GetFlags($array[$i]['f']);
                        if ($flags["external"] == 1) {
                            $this->_folders[$j]->external = 1;
                        }
                    }

                    // Save i4ms flag for checking if AlterPingChanges use
                    if (isset($array[$i]['i4ms'])) {
                        $this->_folders[$j]->i4ms = $array[$i]['i4ms'];
                    } else $this->_folders[$j]->i4ms = "";

                    if (isset($array[$i]['s'])) {
                        $this->_folders[$j]->s = $array[$i]['s'];
                    } else {
                        $this->_folders[$j]->s = "";
                    }

                    if (isset($array[$i]['n'])) {
                        $this->_folders[$j]->n = $array[$i]['n'];
                    } else {
                        $this->_folders[$j]->n = "";
                    }

                    // Check Whether To Include Or Not
                    $this->_folders[$j]->recursive = 1;
                    if (strtolower($this->_folders[$j]->name) == 'user_root') {
                        $this->_folders[$j]->include = 0;       // Ignore The User_Root Folder
                    } else if (strtolower($this->_folders[$j]->name) == '*syncconfig*') {
                        $this->_folders[$j]->include = 0;       // Ignore The *SyncConfig* Folder (used for SmartFolders)
                        $this->_folders[$j]->recursive = 0;
                        $syncConfigFolderId = $this->_folders[$j]->devid;
                    } else if ($this->_folders[$j]->parentid == $syncConfigFolderId) {
                        $this->_folders[$j]->include = 0;       // Ignore The *SyncConfig* Directive Folder(s) (used for SmartFolders)
                        $this->_folders[$j]->recursive = 0;
                    } else if (!isset($user[$this->_folders[$j]->view][0]['active'])) {
                        $this->_folders[$j]->include = 0;       // Ignore Anything Besides Message, Contact, Appointment, and Task
                    } else if ($this->ToBool($user[$this->_folders[$j]->view][0]['active']) === false) {
                        $this->_folders[$j]->include = 0;       // If Section (e.g. Calendar) Is Disabled, Ignore Any Of Those Folders
                    } else {
                        if ($user[$this->_folders[$j]->view][0]['folders'][0]['filtermethod'][0] == 'none') {
                            $this->_folders[$j]->include = 1;
                        } else {
                            $found = 0;
                            $items = $user[$this->_folders[$j]->view][0]['folders'][0]['list'];
                            $itemcount = count($items);

                            for ($k=0;$k<$itemcount;$k++) {
                                if ($user[$this->_folders[$j]->view][0]['folders'][0]['identifier'][0] == 'id' && $items[$k]['id'] == $this->_folders[$j]->id) {
                                    $found = 1;
                                    if (isset($items[$k]['recursive']) && $this->ToBool($items[$k]['recursive']) === false) {
                                        $this->_folders[$j]->recursive = 0;
                                    }
                                } else if ($user[$this->_folders[$j]->view][0]['folders'][0]['identifier'][0] == 'name' && $items[$k]['name'] == $this->_folders[$j]->name) {
                                    $found = 1;
                                    if (isset($items[$k]['recursive']) && $this->ToBool($items[$k]['recursive']) === false) {
                                        $this->_folders[$j]->recursive = 0;
                                    }
                                }
                            }

                            if (($user[$this->_folders[$j]->view][0]['folders'][0]['filtermethod'][0] == 'exclude' && $found == 1) ||
                                ($user[$this->_folders[$j]->view][0]['folders'][0]['filtermethod'][0] == 'include' && $found == 0)
                            ) {
                                $this->_folders[$j]->include = 0;
                            } else {
                                $this->_folders[$j]->include = 1;
                            }

                            // If not found, see if folder falls under another folder with recursive option set to true
                            if ($found == 0 && $this->_folders[$j]->parentid <> '0') {
                                $k = 0;
                                $index = $j;
                                while ($k <= 255) {
                                    $index = $this->GetFolderIndex($this->_folders[$index]->parentid);
                                    if (!empty($index)) {
                                        if ($this->_folders[$index]->recursive == 1) {
                                            $this->_folders[$j]->include = $this->_folders[$index]->include;
                                            $k = 255;
                                        } else if ($this->_folders[$index]->parentid == '0') {
                                            $k = 255;
                                        }
                                    } else {
                                        $k = 255;
                                    }
                                    $k = $k + 1;
                                }
                            }
                        }
                    }

                    if ($this->_folders[$j]->include == 1 && (
                        ($user[$this->_folders[$j]->view][0]['primary'][0]['identifier'] == 'id' && $user[$this->_folders[$j]->view][0]['primary'][0]['id'] == $this->_folders[$j]->id) ||
                        ($user[$this->_folders[$j]->view][0]['primary'][0]['identifier'] == 'name' && $user[$this->_folders[$j]->view][0]['primary'][0]['name'] == $this->_folders[$j]->name)
                    )) {
                        $this->_folders[$j]->primary = 1;

                        if(strtolower($this->_folders[$j]->view) == 'contact') {
                            $this->_primary['contact'] = $this->_folders[$j]->devid;
                        }elseif(strtolower($this->_folders[$j]->view) == 'appointment') {
                            $this->_primary['appointment'] = $this->_folders[$j]->devid;
                        }elseif(strtolower($this->_folders[$j]->view) == 'task') {
                            $this->_primary['task'] = $this->_folders[$j]->devid;
                        }elseif(strtolower($this->_folders[$j]->view) == 'note') {
                            $this->_primary['note'] = $this->_folders[$j]->devid;
                        }
                    } else {
                        $this->_folders[$j]->primary = 0;
                    }

                    if ($this->_folders[$j]->include == 1 && $this->_folders[$j]->primary == 0) {
                        if (($this->_deviceMultiFolderSupport[$this->_folders[$j]->view] === false) && ($this->ToBool($user[$this->_folders[$j]->view][0]['virtual']) === false)) {
                            $this->_folders[$j]->include = 0;
                            $this->_folders[$j]->virtual = 0;
                        }
                    }

                    $this->_folders[$j]->stats = $this->_folders[$j]->name . "-" . (isset($array[$i]['rev']) ? $array[$i]['rev'] : "") . "-" . (isset($array[$i]['ms']) ? $array[$i]['ms'] : "");
					$this->_folders[$j]->stats .= "-" . (isset($array[$i]['n']) ? $array[$i]['n'] : "") . "-" . (isset($array[$i]['s']) ? $array[$i]['s'] : "") . "-" . (isset($array[$i]['i4ms']) ? $array[$i]['i4ms'] : "") . "-" . (isset($array[$i]['i4next']) ? $array[$i]['i4next'] : "") ;

                    // Process Virtual Folders
                    if ($this->_folders[$j]->include == 1 &&
                        $this->ToBool($user[$this->_folders[$j]->view][0]['virtual']) === true &&
                        $this->_deviceMultiFolderSupport[$this->_folders[$j]->view] === false &&
                        $this->_folders[$j]->primary == 0
                    ) {
                        $this->_folders[$j]->virtual = 1;
                        if(strtolower($this->_folders[$j]->view) == 'contact') {
                            $this->_virtual['contact'][] = $this->_folders[$j]->devid;
                        }elseif(strtolower($this->_folders[$j]->view) == 'appointment') {
                            $this->_virtual['appointment'][] = $this->_folders[$j]->devid;
                        }elseif(strtolower($this->_folders[$j]->view) == 'task') {
                            $this->_virtual['task'][] = $this->_folders[$j]->devid;
                        }elseif(strtolower($this->_folders[$j]->view) == 'note') {
                            $this->_virtual['note'][] = $this->_folders[$j]->devid;
                        }
                    } else {
                        $this->_folders[$j]->virtual = 0;
                    }

                    $this->_folders[$j]->search = '';   // Used Only For Search Folders
                    $j = $j + 1;
                }
            }

            unset($array);
        return true;
    } // end GetZimbraFolders


    /** GetZimbraSearchFolders
     *
     */
    function GetZimbraSearchFolders(&$user) {
    
        if (!isset($user['message'][0]['searchfolder'])) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraSearchFolders(): ' .  'No "searchfolder" definitions in XML file - Search Folders are not synched !' );
            return true;
        }
		
        $f = count($this->_folders);
        
        $soap ='<GetSearchFolderRequest xmlns="urn:zimbraMail"/>';
        $response = $this->SoapRequest($soap);
        if($response) {
            $contents = $this->MakeXMLTree($response);
            if (isset($contents['soap:Envelope'][0]['soap:Body'][0]['GetSearchFolderResponse'][0]['search'])) {
	            $subset = $contents['soap:Envelope'][0]['soap:Body'][0]['GetSearchFolderResponse'][0]['search'];
	            $total = count($subset);
            } else {
                $total = 0;
            }

            // We only need subset now - so clear contents
            unset($contents);
			
            if ($total > 0) {
                $folders = $user['message'][0]['searchfolder'];
                if (count($folders) > 0) {
                    for ($i=0;$i<$total;$i++) {
                        for ($j=0;$j<count($folders);$j++) {
                            if (   (isset($folders[$j]['name']) && $subset[$i]['name'] == $folders[$j]['name'])
                                || (isset($folders[$j]['id']) && $subset[$i]['id'] == $folders[$j]['id']) )
                            {
                                $this->_folders[$f] = new stdClass();
                                $this->_folders[$f]->id = $subset[$i]['id'];
                                $this->_folders[$f]->name = $subset[$i]['name'];
                                $this->_folders[$f]->devid = 'f' . $subset[$i]['id'];
                                $this->_folders[$f]->view = 'message';

                                if (isset($this->_paths[$subset[$i]['l']])) {
                                    if (strtolower($this->_paths[$subset[$i]['l']]) == "user_root") {
                                        $this->_folders[$f]->parentid = '0';
                                    } else {
                                        $this->_folders[$f]->parentid = 'f' . $subset[$i]['l'];
                                    }
                                } else {
                                    $this->_folders[$f]->parentid = '0';
                                }

                                $this->_paths = $this->SetFolderPaths($this->_paths, $subset[$i]['id'], $subset[$i]['name'], $subset[$i]['l']);
                                $this->_folders[$f]->path = $this->_paths[$subset[$i]['id']];

                                if (isset($folders[$searchfolder[$j]]['recursive']) && $this->ToBool($folders[$searchfolder[$j]]['recursive']) === false) {
                                    $this->_folders[$f]->recursive = 0;
                                } else {
                                    $this->_folders[$f]->recursive = 1;
                                }                                
/* TODO - Is this needed ?
                    if (isset($array[$i]['s'])) {
                        $this->_folders[$j]->s = $array[$i]['s'];
                    } else {
                        $this->_folders[$j]->s = "";
                    }

                    if (isset($array[$i]['n'])) {
                        $this->_folders[$j]->n = $array[$i]['n'];
                    } else {
                        $this->_folders[$j]->n = "";
                    }
*/

                                $this->_folders[$f]->external = 0;
                                $this->_folders[$f]->include = 1;
                                $this->_folders[$f]->primary = 0;
                                $this->_folders[$f]->virtual = 0;
                                $this->_folders[$f]->search = $subset[$i]['query'];                
                                $f = $f + 1;
                            }
                        }
                    }
                }

                unset($subset);
            }
        }

        unset($response);
		
        return true;
    } // end GetZimbraSearchFolders


    /** SetFolderPaths
     *   Function exclusively used by function GetZimbraFolders to create a path string
     */
    function SetFolderPaths($array, $folderID, $folderName, $parentID) {
        if (strtolower($folderName) <> "user_root" && $array[$parentID] <> "" && strtolower($array[$parentID]) <> "user_root") {
            $array[$folderID] = $array[$parentID] . "/" . $folderName;
        } else {
            $array[$folderID] = $folderName;
        }
        return $array;
    } // end SetFolderPaths


    function GetHierarchyImporter() {
        return new ImportHierarchyChangesDiff($this);
    }

    public function GetImporter($folderid = false) {
        return new ImportChangesDiff($this, $folderid);
    }
 
    public function GetExporter($folderid = false) {
        return new ExportChangesDiff($this, $folderid);
    }

    public function GetSearchProvider() {
        return new BackendSearchZimbra($this);
    }
 

    function GetHierarchy() {
        $folders = array();

        $fl = $this->GetFolderList();
        foreach($fl as $f){
            $folders[] = $this->GetFolder($f['id']);
        }
        unset( $fl );
        unset( $f );

        return $folders;
    }

    /** GetFolderList
     *   Get a list of all folders
     */
    public function GetFolderList() {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderList(): ' . 'START GetFolderList');

        $folders = array();
        $j = 0;
        $folder_count = count($this->_folders);
        for ($i=0;$i<$folder_count;$i++) {
            $box = array();
            if ($this->_folders[$i]->include == 1 && $this->_folders[$i]->virtual == 0) {
                $j = $j + 1;
                $box['id'] = $this->_folders[$i]->devid;
                $box['parent'] = $this->_folders[$i]->parentid;
                $box['mod'] = $this->_folders[$i]->name;
                if ($this->_folders[$i]->view == 'note') {
                    $box['mod'] = 'N' . $box['mod'];
                }
                $folders[]=$box;
            }
            unset($box);
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderList(): ' . 'END GetFolderList { found ' . $j . ' folders }');
        return $folders;
    } // end GetFolderList


    /** GetFolder
     *   Returns an actual SyncFolder object with all the properties set. Folders
     *   have only a type, a name, a parent and a server ID.
     */
    function GetFolder($devid) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolder(): ' . 'START GetFolder { devid = ' . $devid . ' }');

        $folder = new SyncFolder();
        $folder->serverid = $devid;

        $index = $this->GetFolderIndex($devid);
        $devid = $this->_folders[$index]->devid;
        $folder->displayname = $this->_folders[$index]->name;
        $folder->parentid = $this->_folders[$index]->parentid;

        if (strtolower($this->_folders[$index]->view) == 'appointment') {
            if ($this->_folders[$index]->primary == 1) {
//                $folder->parentid = '0'; // Not sure if this has to be set ?
                $folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;
            } else { 
                $folder->type = SYNC_FOLDER_TYPE_USER_APPOINTMENT;
            }
        } else if (strtolower($this->_folders[$index]->view) == 'contact') {
            if ($this->_folders[$index]->primary == 1) {
                $folder->type = SYNC_FOLDER_TYPE_CONTACT;
            } else { 
                $folder->type = SYNC_FOLDER_TYPE_USER_CONTACT;
            }
        } else if (strtolower($this->_folders[$index]->view) == 'task') {
//            $folder->parentid = '0'; // Not sure if this has to be set ?
            if ($this->_folders[$index]->primary == 1) {
                $folder->type = SYNC_FOLDER_TYPE_TASK;
            } else { 
                $folder->type = SYNC_FOLDER_TYPE_USER_TASK;
            }
        } else if (strtolower($this->_folders[$index]->view) == 'message') {
            if (strtolower($this->_folders[$index]->name) == 'trash' && $this->_folders[$index]->parentid == '0' && $this->_folders[$index]->external == 0 && $this->_folders[$index]->linkid == '') {
//                $folder->parentid = '0';
                $folder->displayname = 'Trash';
                $folder->type = SYNC_FOLDER_TYPE_WASTEBASKET;
            } else if (strtolower($this->_folders[$index]->name) == 'inbox' && $this->_folders[$index]->parentid == '0' && $this->_folders[$index]->external == 0 && $this->_folders[$index]->linkid == '') {
//                $folder->parentid = '0';
                $folder->displayname = 'Inbox';
                $folder->type = SYNC_FOLDER_TYPE_INBOX;
            } else if (strtolower($this->_folders[$index]->name) == 'outbox' && $this->_folders[$index]->parentid == '0' && $this->_folders[$index]->external == 0 && $this->_folders[$index]->linkid == '') {
//                $folder->parentid = '0';
                $folder->displayname = 'Outbox';
                $folder->type = SYNC_FOLDER_TYPE_OUTBOX;
            } else if (strtolower($this->_folders[$index]->name) == 'drafts' && $this->_folders[$index]->parentid == '0' && $this->_folders[$index]->external == 0 && $this->_folders[$index]->linkid == '') {
//                $folder->parentid = '0';
                $folder->displayname = 'Drafts';
                $folder->type = SYNC_FOLDER_TYPE_DRAFTS;
            } else if (strtolower($this->_folders[$index]->name) == 'sent' && $this->_folders[$index]->parentid == '0' && $this->_folders[$index]->external == 0 && $this->_folders[$index]->linkid == '') {
//                $folder->parentid = '0';
                $folder->displayname = 'Sent';
                $folder->type = SYNC_FOLDER_TYPE_SENTMAIL;
            } else {
                $folder->type = SYNC_FOLDER_TYPE_USER_MAIL; //SYNC_FOLDER_TYPE_OTHER;
            }
        } else if (strtolower($this->_folders[$index]->view) == 'note') {
            if ($this->_folders[$index]->primary == 1) {
                $folder->type = SYNC_FOLDER_TYPE_NOTE;
            } else { 
                $folder->type = SYNC_FOLDER_TYPE_USER_NOTE;
            }
        }

        if(!isset($folder->parentid)) {
            $folder->parentid = '0';
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolder(): ' . 'END GetFolder { parentid = ' . $folder->parentid . '; displayname = ' . $folder->displayname . '; type = ' . $folder->type . ' }');
        return $folder;
    }  // end GetFolder


    /** StatFolder
     *   Return folder stats. This means you must return an associative array with the
     *   following properties:
     *   "id" => The server ID that will be used to identify the folder. It must be unique, and not too long
     *           How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
     *   "parent" => The server ID of the parent of the folder. Same restrictions as 'id' apply.
     *   "mod" => This is the modification signature. It is any arbitrary string which is constant as long as
     *            the folder has not changed. In practice this means that 'mod' can be equal to the folder name
     *            as this is the only thing that ever changes in folders. (the type is normally constant)
     */
    public function StatFolder($devid) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatFolder(): ' . 'START StatFolder { devid = ' . $devid . ' }');

        $stat = array();
        $index = $this->GetFolderIndex($devid);
        if ($index>=0) {
            $stat["id"] = $devid;
            $stat["mod"] = $this->_folders[$index]->name;  //$this->_folders[$index]->path;
            if ($this->_folders[$index]->view == 'note') {
                $stat["mod"] = "N" . $stat["mod"];  //Prepend Notes folder with N - so we can tell if hierarchy changes (task->note OR note-task)
            }
            $stat["parent"] = $this->_folders[$index]->parentid;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatFolder(): ' . 'END StatFolder { id = ' . $stat["id"] . '; parent = ' . $stat["parent"] . '; mod = ' . $stat["mod"] . ' }');
            return $stat;
        } else {
            $stat["id"] = $devid;
            $stat["mod"] = '';
            $stat["parent"] = '';
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatFolder(): ' . 'END StatFolder { ERROR id = ' . $stat["id"] . '; parent = ' . $stat["parent"] . '; mod = ' . $stat["mod"] . ' }');
            return $stat;
        }
    } // end StatFolder


    /** GetFolderIndex
     *   Function used by other functions to get the index in $this->_folders for the passed device folder ID (devid)
     */
    function GetFolderIndex($devid) {
        $folder_count = count($this->_folders);
        for ($i=0;$i<$folder_count;$i++) {
            if ($this->_folders[$i]->devid == $devid) {
                return $i;
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderIndex(): ' .  'Folder Not Found - FolderSync Required ' );
        $this->_clearCacheOnLogoff = true;
        if (defined('SyncCollections::HIERARCHY_CHANGED')) {
            throw new StatusException("Zimbra->GetFolderIndex(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
        } else {
            throw new StatusException("Zimbra->GetFolderIndex(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
        }
    } // end GetFolderIndex


    /** GetFolderIndexZimbraID
     *   Function used by other functions to get the index in $this->_folders for the passed Zimbra folder ID (id)
     */
    function GetFolderIndexZimbraID($id) {
        $folder_count = count($this->_folders);
        for ($i=0;$i<$folder_count;$i++) {
            if (($this->_folders[$i]->id == $id) || ($this->_folders[$i]->linkid == $id)) {
                return $i;
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderIndexZimbraID(): ' .  'Folder Not Found - FolderSync Required ' );
        $this->_clearCacheOnLogoff = true;
        if (defined('SyncCollections::HIERARCHY_CHANGED')) {
            throw new StatusException("Zimbra->GetFolderIndexZimbraID(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
        } else {
            throw new StatusException("Zimbra->GetFolderIndexZimbraID(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
        }
    } // end GetFolderIndexZimbraID


    /* Should return a wastebasket folder if there is one. This is used when deleting
     * items; if this function returns a valid folder ID, then all deletes are handled
     * as moves and are sent to your backend as a move. If it returns FALSE, then deletes
     * are always handled as real deletes and will be sent to your importer as a DELETE
     */
    public function GetWasteBasket() {
        return $this->_wasteID;
    } // end GetWasteBasket


    /**
     * Returns the email address and the display name of the user. Used by autodiscover.
     *
     * @param string        $username           The username
     *
     * @access public
     * @return Array
     */
    public function GetUserDetails($username) {
        ZLog::Write(LOGLEVEL_WBXML, sprintf("Zimbra->GetUserDetails for '%s'.", $username));
        $userDetails['emailaddress'] = (isset($this->_accountName) && $this->_accountName) ? $this->_accountName : false;
        $userDetails['fullname'] = (isset($this->_sendAsName) && $this->_sendAsName) ? $this->_sendAsName : false;
        return $userDetails;
    }


// TODO The next two aren't part of diffbackend.php so need to confirm if devices even support deleting and renaming of folders
    /* Creates or modifies a folder
     * "folderid" => id of the parent folder
     * "oldid" => if empty -> new folder created, else folder is to be renamed
     * "displayname" => new folder name (to be created, or to be renamed to)
     * "type" => folder type, ignored in IMAP
     *
     */
    public function ChangeFolder($folderid, $oldid, $displayname, $type){
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . 'START ChangeFolder { folderid = '.$folderid.'; oldid = '.$oldid.';  displayname = '.$displayname.'; type = '.$type.' }');

        if ($folderid == "0") {
            $zimbraParentFolderId = "1";
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "ROOT PARENT" );
        } else {
			$index = $this->GetFolderIndex($folderid);
			$zimbraParentFolderId = $this->_folders[$index]->id;
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'Parent ['.$zimbraParentFolderId.']' );
        }

        if ($oldid) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'OldID is set - Want to RENAME/Trash/Move' );

			$index = $this->GetFolderIndex($oldid);
			$zimbraFolderId = $this->_folders[$index]->id;
            $deviceParentId = $this->_folders[$index]->parentid;
			$view = $this->_folders[$index]->view;
			$foldername = $this->_folders[$index]->name;

            if ($deviceParentId == $folderid) {
                $soap ='<FolderActionRequest xmlns="urn:zimbraMail">
                        <action id="'.$zimbraFolderId.'" op="rename" l="'.$zimbraParentFolderId.'" name="'.$displayname.'" ></action> 
                        </FolderActionRequest>';
//	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'SOAP ['.$soap.']' );
                $returnJSON = true;
	    		$response = $this->SoapRequest($soap, false, false, $returnJSON);
		    	if($response) {
                    $array = json_decode($response, true);
//	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'ARRAY ['.print_r($array, true).']' );

				// Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
    				unset($response);
				
	    			if (isset($array['Body']['FolderActionResponse']['action']['id'])) {
		    			$postId = $array['Body']['FolderActionResponse']['action']['id'];
			    		$postOp = $array['Body']['FolderActionResponse']['action']['op'];
				    	if (($postId == $zimbraFolderId) && ($postOp == "rename")) {
					    	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "END ChangeFolder { Folder '".$foldername."' renamed to '".$displayname."'  }");
                            $this->_folders[$index]->name = $displayname;
//                        return $this->StatFolder( $oldid );
                            $this->ClearCache();
                            $this->_cacheChangeToken = "ForceRefresh";
                            $this->_saveCacheOnLogoff = true;
    						return true;
//    						return $this->StatFolder( $zimbraFolderId );
	    				}
		    		}
			    }
            } elseif ($this->GetWasteBasket() == $folderid) {
                $soap ='<FolderActionRequest xmlns="urn:zimbraMail">
                        <action id="'.$zimbraFolderId.'" op="trash"  ></action> 
                        </FolderActionRequest>';
//	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'SOAP ['.$soap.']' );
                $returnJSON = true;
	    		$response = $this->SoapRequest($soap, false, false, $returnJSON);
		    	if($response) {
                    $array = json_decode($response, true);
//	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'ARRAY ['.print_r($array, true).']' );

				// Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
    				unset($response);
				
	    			if (isset($array['Body']['FolderActionResponse']['action']['id'])) {
		    			$postId = $array['Body']['FolderActionResponse']['action']['id'];
			    		$postOp = $array['Body']['FolderActionResponse']['action']['op'];
				    	if (($postId == $zimbraFolderId) && ($postOp == "trash")) {
					    	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "END ChangeFolder { Folder '".$foldername."'  moved to 'Trash'  }");
                            $this->_folders[$index]->name = $displayname;
//                        return $this->StatFolder( $oldid );
                            $this->ClearCache();
                            $this->_cacheChangeToken = "ForceRefresh";
                            $this->_saveCacheOnLogoff = true;
    						return true;
//    						return $this->StatFolder( $zimbraFolderId );
	    				}
		    		}
			    } 
            } else {
                $soap ='<FolderActionRequest xmlns="urn:zimbraMail">
                        <action id="'.$zimbraFolderId.'" op="move" l="'.$zimbraParentFolderId.'" ></action> 
                        </FolderActionRequest>';
//	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'SOAP ['.$soap.']' );
                $returnJSON = true;
	    		$response = $this->SoapRequest($soap, false, false, $returnJSON);
		    	if($response) {
                    $array = json_decode($response, true);
//	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'ARRAY ['.print_r($array, true).']' );

				// Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
    				unset($response);
				
	    			if (isset($array['Body']['FolderActionResponse']['action']['id'])) {
		    			$postId = $array['Body']['FolderActionResponse']['action']['id'];
			    		$postOp = $array['Body']['FolderActionResponse']['action']['op'];
				    	if (($postId == $zimbraFolderId) && ($postOp == "move")) {
					    	ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "END ChangeFolder { Folder '".$foldername."' moved to '".$zimbraParentFolderId."'  }");
                            $this->_folders[$index]->name = $displayname;
//                        return $this->StatFolder( $oldid );
                            $this->ClearCache();
                            $this->_cacheChangeToken = "ForceRefresh";
                            $this->_saveCacheOnLogoff = true;
    						return true;
//    						return $this->StatFolder( $zimbraFolderId );
	    				}
		    		}
			    } 
			}
        } else {
			switch ($type) {
				case SYNC_FOLDER_TYPE_TASK:
				case SYNC_FOLDER_TYPE_USER_TASK:
					$view = 'task';
					break;
				case SYNC_FOLDER_TYPE_APPOINTMENT:
				case SYNC_FOLDER_TYPE_USER_APPOINTMENT:
					$view = 'appointment';
					break;
				case SYNC_FOLDER_TYPE_CONTACT:
				case SYNC_FOLDER_TYPE_USER_CONTACT:
					$view = 'contact';
					break;
				case SYNC_FOLDER_TYPE_USER_MAIL:
					$view = 'message';
					break;
				case SYNC_FOLDER_TYPE_JOURNAL:
				case SYNC_FOLDER_TYPE_USER_JOURNAL:
				case SYNC_FOLDER_TYPE_NOTE:
				case SYNC_FOLDER_TYPE_USER_NOTE:
				default:
					ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "END ChangeFolder { FAILED - Unsupported Type for Create (".$type.") }");
					return false;
			}

            $soap ='<CreateFolderRequest xmlns="urn:zimbraMail">
                        <folder name="'.$displayname.'" l="'.$zimbraParentFolderId.'" view="'.$view.'" ></folder> 
                    </CreateFolderRequest>';

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'SOAP ['.$soap.']' );
            $returnJSON = true;
			$response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' .  'ARRAY ['.print_r($array, true).']' );

                // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
                unset($response);
			
           		if (isset($array['Body']['CreateFolderResponse']['folder'][0]['id'])) {
					
                    $noteFlag = ($view == "note" ? 'N' : '' );
					
                    $newId = $array['Body']['CreateFolderResponse']['folder'][0]['id'];
                    $folder_count = count($this->_folders);
                    $this->_folders[$folder_count] = new stdClass();
                    $this->_folders[$folder_count]->id = $newId;
                    if (strrpos($newId,':') !== false) {
                        $parts = explode(":",$newId);
                        $zid = $parts[0];
                        $rid = $parts[1];
                        if (isset($this->_linkOwners[$zid])) {
                            $owner = $this->_linkOwners[$zid];
                        } else {
                            // First Owner - Count will be zero, second owner - count will be one, etc.
                            $this->_linkOwners[$zid] = count($this->_linkOwners);
                            $owner = $this->_linkOwners[$zid];
                        }

                        $this->_folders[$folder_count]->devid = 'FL'.$noteFlag.$owner.'-'.$rid;
                        $this->_folders[$folder_count]->linkid = $zid.":".$rid;
                        if (isset($this->_shareOwners[$zid])) {
                            $this->_folders[$folder_count]->owner = $this->_shareOwners[$zid];
                        } else {
                            $this->_folders[$folder_count]->owner = "";
                        }
			
                    } else {
                        $this->_folders[$folder_count]->devid = 'f'.$noteFlag.$newId;
                        $this->_folders[$folder_count]->linkid = "";
                        $this->_folders[$folder_count]->owner = "";
                    }
                    $this->_folders[$folder_count]->name = $displayname;
                    $this->_folders[$folder_count]->parentid = $folderid;
                    $this->_folders[$folder_count]->view = $view;
					
                    $this->ClearCache();
                    $this->_cacheChangeToken = "ForceRefresh";
                    $this->_saveCacheOnLogoff = true;

                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "END ChangeFolder { New folder '".$displayname."' created - id = ".$this->_folders[$folder_count]->devid." }");
                    return $this->StatFolder( $this->_folders[$folder_count]->devid );
                }
			}
        } 
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeFolder(): ' . "END ChangeFolder { FALSE }");
        return false;
    } // end ChangeFolder


    /*
     */
    public function DeleteFolder($id, $parentid){
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteFolder(): ' . 'START DeleteFolder { id = '.$id.'; parentid = '.$parentid.' }');

		$index = $this->GetFolderIndex($id);
		$zimbraFolderId = $this->_folders[$index]->id;
		$view = $this->_folders[$index]->view;
		$foldername = $this->_folders[$index]->name;

        $soap ='<FolderActionRequest xmlns="urn:zimbraMail">
                    <action id="'.$zimbraFolderId.'" op="trash" ></action> 
                </FolderActionRequest>';
        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);

            // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
            unset($response);
			
       		if (isset($array['Body']['FolderActionResponse']['action']['id'])) {
                $postId = $array['Body']['FolderActionResponse']['action']['id'];
                $postOp = $array['Body']['FolderActionResponse']['action']['op'];
				if (($postId == $zimbraFolderId) && ($postOp == "trash")) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteFolder(): ' . "END DeleteFolder { Folder '".$foldername."' moved to Trash }");
                    return true;
				}
            }
        }
		ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteFolder(): ' . "END DeleteFolder { FAILED TO MOVE Folder '".$foldername."' Trash }");
		return false;
    } // end DeleteFolder



    public function Fetch($folderid, $id, $contentparameters) {
// TODO: Why just 1MB?  Can this be increased?  What will happen if we increase it?
        // override truncation
        //    $contentparameters->SetTruncation(SYNC_TRUNCATION_ALL);
        $msg = $this->GetMessage($folderid, $id, $contentparameters); 
        if ($msg === false)
            throw new StatusException("Zimbra->Fetch('%s','%s'): Error, unable retrieve message from backend", SYNC_STATUS_OBJECTNOTFOUND);
        return $msg;
    }



    public function IsFolderCacheUpToDate($folderid, $folderMetaData, $cutoffdate, $zimbraCutOffDate) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' . 'START IsFolderCacheUpToDate { folderid = ' . $folderid . '; cutoffdate = ' . $cutoffdate . ' }');








        if (isset($this->_cachedMessageLists[$folderid]) && isset($this->_cachedMessageLists[$folderid]['cachetime'])) {
            $cacheAge = time() - $this->_cachedMessageLists[$folderid]['cachetime'];
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'Cache AGE ('.$cacheAge.'s old) Cached at ['.$this->_cachedMessageLists[$folderid]['cachetime'].'] Time is ['.time().']' );
            if ($cacheAge < $this->_localCacheLifetime) {
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'Cache AGE ('.$cacheAge.'s old) Less than ['.$this->_localCacheLifetime.'] ' );
                if (!isset($this->_cachedMessageLists[$folderid]['cutoffdate'])) {
                    $this->_cachedMessageLists[$folderid]['cutoffdate'] == '';
                }
                if ($this->_cachedMessageLists[$folderid]['cutoffdate'] == $zimbraCutOffDate) {
                    if ((($folderMetaData->i4ms != "") && ($this->_cachedMessageLists[$folderid]['i4ms'] == $folderMetaData->i4ms)) || 
                        (($folderMetaData->i4ms == "") && (($this->_cachedMessageLists[$folderid]['size'] == $folderMetaData->s) && ($this->_cachedMessageLists[$folderid]['count'] == $folderMetaData->n)))) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' . 'END IsFolderCacheUpToDate CACHED CONTACTS { items = '.$folderMetaData->n.'; size = '.$folderMetaData->s.'; i4ms = '.$folderMetaData->i4ms.'; }');
                        return true;
                    } else {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'Content CHANGED for folder ['.$folderid.']' );
                    }
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'CutOffDate CHANGED for folder ['.$folderid.']' );
                }
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'Cache EXPIRED ('.$cacheAge.'s old) for folder ['.$folderid.']' );
            }
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'CLEARING CACHE for folder ['.$folderid.']'  );
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->IsFolderCacheUpToDate(): ' .  'Cache NOT FOUND for folder ['.$folderid.'] ' );
        }
        unset( $this->_cachedMessageLists[$folderid] );

        return false;
    }


    public function UpdateFolderCache($folderid, $folderMetaData, $zimbraCutOffDate, &$output) {
        $this->_cachedMessageLists[$folderid] = array();
        $this->_cachedMessageLists[$folderid]['cutoffdate'] = $zimbraCutOffDate;
        $this->_cachedMessageLists[$folderid]['messaagelist'] = $output;
        $this->_cachedMessageLists[$folderid]['i4ms'] = $folderMetaData->i4ms;
        $this->_cachedMessageLists[$folderid]['size'] = $folderMetaData->s;
        $this->_cachedMessageLists[$folderid]['count'] = $folderMetaData->n;
        $this->_cachedMessageLists[$folderid]['cachetime'] = time();
        $this->_cachedMessageLists['changed'] = true;
    }


    public function GetNextMessageBlock($folderid, $zimbraFolderId, $cutoffdate, $zimbraCutOffDate, $folderMetaData, $view, $limit, $offset = 0, &$output, &$more) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetNextMessageBlock(): ' . 'START GetNextMessageBlock { folderid = ' . $folderid . '; cutoffdate = ' . $cutoffdate . '; offset = ' . $offset . ' }');

        switch ($view) {
            case 'message':

                $searchResponseSubset = 'm';
                if (empty($folderMetaData->search)) {
                    if ($cutoffdate != "0") {
                        $zimbraCutOffDate = strftime("%m/%d/%Y", $cutoffdate-86400);
                        $soap ='<SearchRequest xmlns="urn:zimbraMail" types="message" limit="'.$limit.'" offset="'.$offset.'">
                                    <query>inid:"'.$zimbraFolderId.'" AND after:"' . $zimbraCutOffDate . '"</query>
                                    <locale>en_US</locale>
                                </SearchRequest>';
                    } else {
                        $soap ='<SearchRequest xmlns="urn:zimbraMail" types="message" limit="'.$limit.'" offset="'.$offset.'">
                                    <query>inid:"'.$zimbraFolderId.'"</query>
                                </SearchRequest>';
                    }
                } else {
                    if ($cutoffdate != "0") {
                        $zimbraCutOffDate = strftime("%m/%d/%Y", $cutoffdate-86400);
                        $soap ='<SearchRequest xmlns="urn:zimbraMail" types="message" limit="'.$limit.'" offset="'.$offset.'">
                                    <query>(' . $folderMetaData->search . ') AND after:"' . $zimbraCutOffDate . '"</query>
                                    <locale>en_US</locale>
                                </SearchRequest>';
                    } else {
                        $soap ='<SearchRequest xmlns="urn:zimbraMail" types="message" limit="'.$limit.'" offset="'.$offset.'">
                                    <query>' . $folderMetaData->search . '</query>
                                </SearchRequest>';
                    }
                }

                break;

            case 'contact':

                $searchResponseSubset = 'cn';
                $soap ='<SearchRequest xmlns="urn:zimbraMail" types="contact" limit="'.$limit.'" offset="'.$offset.'">
                            <query>inid:"'.$zimbraFolderId.'"</query>
                        </SearchRequest>';

                break;

            case 'appointment':

                $searchResponseSubset = 'appt';
                // maximum 366 days in the future
                // TO DO - Where did 366 come from ?
                if ($cutoffdate != "0") {
                    $calExpandInstStart = strval($cutoffdate) ."000";
                    $calExpandInstEnd = strval(time() + (366*24*60*60)) ."000";
                    $soap ='<SearchRequest types="appointment" xmlns="urn:zimbraMail" limit="'.$limit.'" offset="'.$offset.'" '.
                                ' calExpandInstStart="'.$calExpandInstStart.'" calExpandInstEnd="'.$calExpandInstEnd.'" >
                                <query>inid:"'.$zimbraFolderId.'" </query>
                            </SearchRequest>';
                } else {
                    $calExpandInstStart = strval(time() - (366*24*60*60)) ."000";
                    $calExpandInstEnd = strval(time() + (366*24*60*60)) ."000";
                    $soap ='<SearchRequest types="appointment" xmlns="urn:zimbraMail" limit="'.$limit.'" offset="'.$offset.'" '.
                                ' calExpandInstStart="'.$calExpandInstStart.'" calExpandInstEnd="'.$calExpandInstEnd.'" >
                                <query>inid:"'.$zimbraFolderId.'"</query>
                            </SearchRequest>';
                }

                break;

            case 'task':

                $searchResponseSubset = 'task';
                $calExpandInstStart = strval(time() - (366*24*60*60)) ."000";
                $calExpandInstEnd = strval(time() + (366*24*60*60)) ."000";
                $soap ='<SearchRequest xmlns="urn:zimbraMail" types="task"  limit="'.$limit.'" offset="'.$offset.'" ';
                $soap .=     '    calExpandInstStart="'.$calExpandInstStart.'" calExpandInstEnd="'.$calExpandInstEnd.'" ';
                if ($cutoffdate == -1) { // Filter out Completed Tasks
                    $soap .= '    allowableTaskStatus="need,inprogress" ';
                }
                $soap .= '>
                            <query>inid:"'.$zimbraFolderId.'"</query>
                        </SearchRequest>';



                break;

            case 'note':

                $searchResponseSubset = 'task';
                $calExpandInstStart = strval(time() - (366*24*60*60)) ."000";
                $calExpandInstEnd = strval(time() + (366*24*60*60)) ."000";
                $soap ='<SearchRequest xmlns="urn:zimbraMail" types="task"  limit="'.$limit.'" offset="'.$offset.'" ';
                $soap .=     '    calExpandInstStart="'.$calExpandInstStart.'" calExpandInstEnd="'.$calExpandInstEnd.'" ';
                if ($cutoffdate == -1) { // Filter out Completed Tasks
                    $soap .= '    allowableTaskStatus="need,inprogress" ';
                }
                $soap .= '>
                            <query>inid:"'.$zimbraFolderId.'"</query>
                        </SearchRequest>';

                break;

            default:
                return $output;
                break;
        }

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {
            $array = json_decode($response, true);

            // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
            unset($response);

            //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetNextMessageBlock(): ' . 'ITEMS ARRAY');
            //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetNextMessageBlock(): ' . print_r($array,true));
            if (isset($array['Body']['SearchResponse'][$searchResponseSubset])) {
                $items = $array['Body']['SearchResponse'][$searchResponseSubset];
                $total = count($items);
            } else $total = 0;

            for ($i=0;$i<$total;$i++) {
                $item = array();
                $item["id"] = $items[$i]['id'];
                $item["mod"] = $this->fixMS( $items[$i]['d'] ) . ((isset($items[$i]['tn']) && ($items[$i]['tn'] != "")) ? hash( 'crc32',$items[$i]['tn'] ) : '');
                $item["flags"] = 1;

                switch ($view) {
                    case 'message':

						if (isset($items[$i]['f'])) {
							$flagString = $items[$i]['f'];
						} else {
							$flagString = "";
						}
						$flags = $this->GetFlags($flagString);
                        // Other than Read Flag - Other changes will be reflected in mod 
                        // Currently Flagged/Cleared, Replied, and Forwarded
                        $item["mod"] = $flags["flagRepFwd"] . $this->fixMS( $items[$i]['d'] ) . ((isset($items[$i]['tn']) && ($items[$i]['tn'] != "")) ? hash( 'crc32',$items[$i]['tn'] ) : '');
                        if ($flags["unread"] == 1) {
                            $item["flags"] = 0;         // Unread
                        }
                        break;

                    case 'contact':

                        // Find The Contact Type And Exclude Contact Groups
                        if (isset($items[$i]['_attrs']['type'])) {
                            if ($items[$i]['_attrs']['type'] == "group") {
                                continue;
                            }
                        }
                        break;

                    case 'appointment':

                        $item["mod"] = $items[$i]['md'] . ((isset($items[$i]['tn']) && ($items[$i]['tn'] != "")) ? hash( 'crc32',$items[$i]['tn'] ) : '');
                        break;

                    case 'task':
                    case 'note':
                        $item["id"] = $items[$i]['invId'];
                        break;

                    default:
                        break;
               }


                array_push($output, $item);
                unset($item);
            }

            // If More Then Limit, Loop Through
            $more = ((isset($array['Body']['SearchResponse']['more']) && $array['Body']['SearchResponse']['more'] == '1'));
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetNextMessageBlock(): ' . 'END GetNextMessageBlock ' . strtoupper($view) . 'S { count = ' . count($output) . ' }');
            return true;
        }
        else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetNextMessageBlock(): ' . 'END GetNextMessageBlock ' . strtoupper($view) . 'S { ERROR }');
            return false;
        }


    }

    /* Should return a list (array) of messages, each entry being an associative array
     * with the same entries as StatMessage(). This function should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * The cutoffdate is a date in the past, representing the date since which items should be shown.
     * This cutoffdate is determined by the user's setting of getting 'Last 3 days' of e-mail, etc. If
     * you ignore the cutoffdate, the user will not be able to select their own cutoffdate, but all
     * will work OK apart from that.
     */
    public function GetMessageList($folderid, $cutoffdate, $virtual = 0, $offset = 0) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' . 'START GetMessageList { folderid = ' . $folderid . '; cutoffdate = ' . $cutoffdate . '; virtual = ' . $virtual . '; offset = ' . $offset . ' }');

        $limit = 1000;
        $output = array();
        $index = $this->GetFolderIndex($folderid);
        $zimbraFolderId = $this->_folders[$index]->id;
        $view = $this->_folders[$index]->view;

        // If configuration has been changed since folder list was sent to the phone, and a folder type is now inactive
        // send back an empty list for the folder
        if (isset($this->_userFolderTypeActive[$view]) && ($this->_userFolderTypeActive[$view] == false)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' .  'END GetMessageList - Folder Type ['.$view.'] is now INACTIVE - FolderSync Required ' );
            $this->_clearCacheOnLogoff = true;
            if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                throw new StatusException("Zimbra->GetMessageList(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
            } else {
                throw new StatusException("Zimbra->GetMessageList(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
            }
            return $output;
        }

        // Return empty list for FakeOutbox
        if ( $this->_folders[$index]->id == -1) { 
            return $output;
        }








        
        // Always override CutOffDate for Contacts
        if ('contact' == $view) { 
                $cutoffdate = '0';
        }

        if ($cutoffdate  && ($cutoffdate != "0")) {
            $zimbraCutOffDate = strftime("%m/%d/%Y", $cutoffdate-86400);
        } else {
            $zimbraCutOffDate = "0";
        }

        if (($this->_localCache) && ($this->IsFolderCacheUpToDate($folderid, $this->_folders[$index], $cutoffdate, $zimbraCutOffDate))) {
            $output = $this->_cachedMessageLists[$folderid]['messaagelist'];
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' . 'END GetMessageList CACHED ' . strtoupper($view) . 'S { count = ' . count($output) . '; items = '.$this->_folders[$index]->n.'; size = '.$this->_folders[$index]->s.'; i4ms = '.$this->_folders[$index]->i4ms.'; }');
        } else {
            $offset = 0;
            $more = false;
            do {
                if (false == $this->GetNextMessageBlock($folderid, $zimbraFolderId, $cutoffdate, $zimbraCutOffDate, $this->_folders[$index], $view, $limit, $offset, $output, $more)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' . 'END GetNextMessageBlock ' . strtoupper($view) . 'S { ERROR }');
                    return false;
                }

                $offset += $limit;
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' . 'END GetNextMessageBlock ' . strtoupper($view) . 'S { count = ' . count($output) . '; }');
            } while (true == $more);

            if ($this->_localCache) {
                $this->UpdateFolderCache($folderid, $this->_folders[$index], $zimbraCutOffDate, $output);
            }
        }

        if ($view != 'message') {
            // Process Virtual Folders
            if ($virtual == 0) {
                $total = count($this->_virtual[$view]);
                if ($total > 0) {
                    for ($i=0;$i<$total;$i++) {
                        $items = $this->GetMessageList($this->_virtual[$view][$i], $cutoffdate, 1);
                        foreach($items as $item) {
                            array_push($output, $item);
                        }
                        unset( $items );
                        unset( $item );
                    }
                }
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' . 'END GetMessageList ' . strtoupper($view) . 'S { count = ' . count($output) . ' }');




//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessageList(): ' .  'Folder Cache at END GET MESSAGE LISTS:  ['.print_r($this->_cachedMessageLists, true).']', false );

        return $output;

    } // end GetMessageList


    /* StatMessage should return message stats, analogous to the folder stats (StatFolder). Entries are:
     * 'id'     => Server unique identifier for the message. Again, try to keep this short (under 20 chars)
     * 'flags'  => simply '0' for unread, '1' for read
     * 'mod'    => modification signature. As soon as this signature changes, the item is assumed to be completely
     *             changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
     *             time for this field, which will change as soon as the contents have changed.
     */
    public function StatMessage($folderid, $id) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatMessage(): ' . 'START StatMessage (fid = '.$folderid.' - id = '.$id.' )');

        if ($id == '') {
            return false;
        }

        $output = array();
        $index = $this->GetFolderIndex($folderid);
        $view = $this->_folders[$index]->view;
/*		
        if (!empty($this->_folders[$index]->linkid)) {
            list($owner, $folder) = explode(":", $this->_folders[$index]->linkid);
            if (!empty($id) && (strpos($id, ":") === false)) {
                $id = $owner .':'. $id;
            }
        }
*/

        switch ($view) {
            case 'message':
                $soap ='<GetMsgMetadataRequest xmlns="urn:zimbraMail">
                            <m ids="'.$id.'" />
                        </GetMsgMetadataRequest>';
                break;

            case 'contact':
                $soap ='<GetContactsRequest sync="1" xmlns="urn:zimbraMail">
                            <cn id="'.$id.'"/>
                        </GetContactsRequest>';
                break;

            case 'appointment':
                $soap ='<GetAppointmentRequest id="'.$id.'" sync="1" xmlns="urn:zimbraMail"/>';
                break;

            case 'task':
            case 'note':
                $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                            <m id="'.$id.'" >*</m>
                        </GetMsgRequest>';
                break;

        }

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {
            $array = json_decode($response, true);
            unset($response);
			
            switch ($view) {
                case 'message':
                    $item = $array['Body']['GetMsgMetadataResponse']['m'][0];

					if (isset($item['f'])) {
						$flagString = $item['f'];
					} else {
					    $flagString = "";
					}
                    $flags = $this->GetFlags($flagString);

                    $output["id"] = $item['id'];
                    // Other than Read Flag - Other changes will be reflected in mod 
                    // Currently Flagged/Cleared, Replied, and Forwarded
                    $output["mod"] = $flags["flagRepFwd"] . $this->fixMS( $item['d'] ) . ((isset($item['tn']) && ($item['tn'] != "")) ? hash( 'crc32',$item['tn'] ) : '');
                    if ($flags["unread"] == 1) {
                        $output["flags"] = 0;           // Unread
                    } else {
                        $output["flags"] = 1;           // Read
                    }

                    // Added for checking inSyncInterval
                    $output["syncdate"] = $this->fixMS($item['d']) / 1000 ;

                    unset( $array);
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatMessage(): ' . "END StatMessage MESSAGE");
                    return $output;

                case 'contact':
                    $item = $array['Body']['GetContactsResponse']['cn'][0];
                    $output["id"] = $item['id'];
                    $output["mod"] = $this->fixMS( $item['d'] ) . ((isset($item['tn']) && ($item['tn'] != "")) ? hash( 'crc32',$item['tn'] ) : '');
                    $output["flags"] = 1;
                    unset( $array);
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatMessage(): ' . "END StatMessage CONTACT");
                    return $output;

                case 'appointment':
                    $item = $array['Body']['GetAppointmentResponse']['appt'][0];
                    $output["id"] = $item['id'];
                    $output["mod"] = $item['md'] . ((isset($item['tn']) && ($item['tn'] != "")) ? hash( 'crc32',$item['tn'] ) : '');
                    $output["flags"] = 1;

                    // Added for checking inSyncInterval
                    $output["syncdate"] = $this->fixMS($item['d']) / 1000 ;

                    unset( $array);
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatMessage(): ' . "END StatMessage APPOINTMENT");
                    return $output;

                case 'task':
                case 'note':
                    $item = $array['Body']['GetMsgResponse']['m'][0];
                    $output["id"] = $item['id'];
                    $output["mod"] = $this->fixMS( $item['d'] ) . ((isset($item['tn']) && ($item['tn'] != "")) ? hash( 'crc32',$item['tn'] ) : '');
                    $output["flags"] = 1;
                    unset( $array);
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatMessage(): ' . "END StatMessage TASK");
                    return $output;
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->StatMessage(): ' . "END StatMessage UNKNOWN TYPE ??");
        return $output;
    }  // end StatMessage


    /* GetZimbraMessageBodies 
     */
    public function GetZimbraMessageBodies($folderid, $id, $needHtml, $needPlain, &$msg, &$plain, &$html, &$calendar, &$hasBodyTypes, &$attachments) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' . 'START GetZimbraMessageBodies { folderid = '.$folderid.'; id = '.$id.'; needHtml = '.$needHtml.'; needPlain = '.$needPlain.';  }');
        if ($id == '') {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' . 'END GetZimbraMessageBodies [FALSE] { $id == "" }');
            return false;
        }

        $soap ='<BatchRequest xmlns="urn:zimbra">';
            $soap .= '<GetMsgRequest xmlns="urn:zimbraMail" requestId="plain">
						<m id="'.$id.'" html="0" >
							<header n="date"/>
							<header n="from"/>
							<header n="to"/>
							<header n="cc"/>
							<header n="subject"/>
							<header n="message-id"/>
							<header n="content-type"/>
						</m>
					</GetMsgRequest>';
            $soap .= '<GetMsgRequest xmlns="urn:zimbraMail" requestId="html">
						<m id="'.$id.'" html="1" neuter="0" >
							<header n="date"/>
							<header n="from"/>
							<header n="to"/>
							<header n="cc"/>
							<header n="subject"/>
							<header n="message-id"/>
							<header n="content-type"/>
						</m>
					</GetMsgRequest>';
        $soap .= '</BatchRequest>';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {

            $array = json_decode($response, true);
            // ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' .  'Batch Bodies:' . print_r($array, true), false );

            unset($response); // We never use it again

            if (!isset($array['Body']['BatchResponse']['GetMsgResponse'])) {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->GetZimbraMessageBodies(): ' . 'END GetZimbraMessageBodies [FALSE] { $array[Body][BatchResponse][GetMsgResponse] is NOT SET for id = ' . $id . ' }');
                return false;
            }

            if ($array['Body']['BatchResponse']['GetMsgResponse'][0]['requestId'] == 'plain') {
                $plainResp = 0;
                $htmlResp = 1;
            } else {
                $plainResp = 1;
                $htmlResp = 0;
            }

            // Parse Plain Text body into $plain
            $msg = $array['Body']['BatchResponse']['GetMsgResponse'][$plainResp]['m'][0];

            $plain = "";
            $html = false;
            $calendar = "";
            $hasBodyTypes = array( 'plain' => false, 'html' => false, 'calendar' => false, 'ms-tnef' => false, 'signed' => false, 'pkcs7-signature' => false, 'pkcs7-mime' => false );
            $attachments = array();	

            $this->GetMpBodyRecursive($folderid, $id, $msg['mp'][0], $plain, $html, $calendar, $hasBodyTypes, $attachments);

            // Parse HTML body into $html
            $msg = $array['Body']['BatchResponse']['GetMsgResponse'][$htmlResp]['m'][0];

            $plain2 = false;
            $html = "";
            $calendar2 = false;
            $hasBodyTypes = array( 'plain' => false, 'html' => false, 'calendar' => false, 'ms-tnef' => false, 'signed' => false, 'pkcs7-signature' => false, 'pkcs7-mime' => false );
            $attachments2 = false;	

            $this->GetMpBodyRecursive($folderid, $id, $msg['mp'][0], $plain2, $html, $calendar2, $hasBodyTypes, $attachments2);

            unset($array);

            // Check if we got what we needed in $plain/$html - or otherwise manipulate results.
            if ($needPlain === true) {
                if (($hasBodyTypes['plain'] === true) && (trim($plain) != "")) {
                    $plain = preg_replace('/^[\n]2,|^[\t\s]*\n+/m',"\n",$plain);  // Strip out duplicate blank lines
                } elseif ($hasBodyTypes['html'] === true) {
                    $plain = $this->ScrubHtmlText($html);  // Strip out all HTML 
                }
                $plain = $this->cp1252_to_utf8( $plain ); // Euro Fix
            }
            if ($needHtml === true) {
                if (($hasBodyTypes['html'] === true) && (trim($html) != "")) {
                    if (strpos( strtolower($html), '<html' ) === false ) { 
                        // Nokia sends only Text email - need to top and tail it. MS Outlook does the same
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' .  'HTML has no <html> TAGS - Add wrapper' );
                        if (strpos( strtolower($html), '<body' ) === false ) { 
                            $html = '<body>'. $html .'</body>';
                        }
                        $html = '<html>'. $html .'</html>';
                    }
                } elseif ($hasBodyTypes['plain'] === true) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' .  'Need an HTML body - ADD WRAPPER to Plain Text' );
                    $html = '<html>'.
                            '<head>'.
                            '<meta name="Generator" content="Z-Push">'.
                            '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
                            '</head>'.
                            '<body>'. 
                            str_replace("\n","<BR>",str_replace("\r","", str_replace("\r\n","<BR>",$plain))).
                            '</body>'.
                            '</html>';
                }
                $html = $this->cp1252_to_utf8( $html ); // Euro Fix
            } 

            if (!$needPlain) $plain = "";
            if (!$needHtml) $html = "";

//<html xmlns="http://www.w3.org/TR/REC-html40">

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' . 'END GetZimbraMessageBodies [TRUE]');
            return true;
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetZimbraMessageBodies(): ' . 'END GetZimbraMessageBodies [FALSE]');
        return false;

    }


    /* GetMessage should return the actual SyncXXX object type. You may or may not use the '$folderid' parent folder
     * identifier here.
     * Note that mixing item types is illegal and will be blocked by the engine; ie returning an Email object in a
     * Tasks folder will not do anything. The SyncXXX objects should be filled with as much information as possible,
     * but at least the subject, body, to, from, etc.
     */
    public function GetMessage($folderid, $id, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . 'START GetMessage { folderid = '.$folderid.'; id = '.$id.'; contentparameters = (ARRAY) }');
//        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . 'START GetMessage { folderid = '.$folderid.'; id = '.$id.'; contentparameters = '.print_r($contentparameters, true).' }');
        $this->ReportMemoryUsage( 'GetMessage START' );
        if ($id == '') {
            return false;
        }

        if (empty($folderid)) { 
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'No FolderId - Calling GetItemRequest to identify Item type' );
            $soap ='<GetItemRequest xmlns="urn:zimbraMail">
                        <item id="'.$id.'" />
                    </GetItemRequest>';
            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);

            if($response) {
                $array = json_decode($response, true);
                unset($response); 
                $item = $array['Body']['GetItemResponse'];
                //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Item ['. print_r( $item, true ), false );
                if (isset($item['m'])) {
                    $view = 'message';
                } else if (isset($item['cn'])) {
                    $view = 'contact';
                } else if (isset($item['appt'])) {
                    $view = 'appointment';
                } else if (isset($item['task'])) {
                    $index = $this->GetFolderIndexZimbraID( $item['task'][0]['l'] );
					$folderid = $this->_folders[$index]->devid;
					if ($this->_folders[$index]->view == 'note') {
                        $view = 'note';
                    } else {
                        $view = 'task';
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Found FolderId ['. $folderid .'] has View ['. $view . ']');
                } else {
                    return false;
                }
                unset($array);
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Item type ['.$view.']' );
            }
        }

        $index = $this->GetFolderIndex($folderid);
        $zimbraFolderId = $this->_folders[$index]->id;
        $view = $this->_folders[$index]->view;
        $foldername = $this->_folders[$index]->name;
		$folderperm = (isset($this->_folders[$index]->perm) ? $this->_folders[$index]->perm : "");
        if (!empty($this->_folders[$index]->linkid)) {
            list($owner, $folder) = explode(":", $this->_folders[$index]->linkid);
            if (!empty($id) && (strpos($id, ":") === false)) {
                $id = $owner .':'. $id;
            }
        } 
        $contentclass = $contentparameters->GetContentClass();
        $filtertype = $contentparameters->GetFilterType();
        $rtftruncation = $contentparameters->GetRTFTruncation();
        $mimesupport = $contentparameters->GetMimeSupport();
        $mimetruncation = $contentparameters->GetMimeTruncation();
        $bodyPrefKeys = $contentparameters->GetBodyPreference();
        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Body Preferences ['.print_r( $bodyPrefKeys, true ).']' );

        if ($bodyPrefKeys) {
            $bodyPrefArray = array();
        } else {
            $bodyPrefArray = false;
        }

        for ($i=0;$i<count($bodyPrefKeys);$i++) {
            $bodyPreference = new BodyPreference();
            $bodyType = $bodyPrefKeys[$i];
            $bodyPreference = $contentparameters->BodyPreference($bodyType);
            $bodyPrefArray[$bodyType] = array();
            if ($bodyPreference->HasValues()) {
                if ($bodyPreference->GetTruncationSize()) $bodyPrefArray[$bodyType]["TruncationSize"] = $bodyPreference->GetTruncationSize();
                if ($bodyPreference->GetAllOrNone()) $bodyPrefArray[$bodyType]["allornone"] = $bodyPreference->GetAllOrNone();
                if ($bodyPreference->GetPreview()) $bodyPrefArray[$bodyType]["Preview"] = $bodyPreference->GetPreview();
            }
        }

/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'BodyPreference [
'.print_r( $bodyPrefArray, true ).']' );
*/

        // If MIME is requested - it will be retrieved below - so it does not influence the decision here
        // If pre AS12 - we only ever want "plain"
        // If AS12 or later we want whatever is asked for in BodyPreference 1 and/or 2

        if (Request::GetProtocolVersion() < 12) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Pre AS12 - Fetch Plain Body' );
            $needPlain = true;
            $needHtml = false;
        } else {
            $needHtml = (isset($bodyPrefArray[2]));
            $needPlain = (isset($bodyPrefArray[1]));
            if (!$needHtml) {
                $needPlain = true;  // Override in case only MIME requested - we have to fetch at least one for addressing/etc.
            }
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'AS12+ - Fetch Plain ['.$needPlain.'] HTML ['.$needHtml.']' );
        }

        $output = false;

        // From z-push 2.3 onwards - The Body will be passed as a Stream instead of a text string
        // For HTML and PLAIN bodies this will make no efficiency improvement for zimbra as we have to patch together the body parts to wrap in a stream so all data is already loaded
        if (!isset($this->_baseBodyIsStream)) {
            $this->_baseBodyIsStream = false;
            if (is_callable(array('Streamer','GetMapping'))) {
                if (Request::GetProtocolVersion() < 12) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Output SyncBaseBody AS < 12 Test '  );
                    $bodyTest = new SyncMail();
                    $mapping = $bodyTest->GetMapping();
                    $map = $mapping[SYNC_POOMMAIL_MIMEDATA];
                } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Output SyncBaseBody AS 12+ Test '  );
                    $bodyTest = new SyncBaseBody();
                    $mapping = $bodyTest->GetMapping();
                    $map = $mapping[SYNC_AIRSYNCBASE_DATA];
                }
                if (isset($map[SyncMail::STREAMER_TYPE]) && $map[SyncMail::STREAMER_TYPE] == SyncMail::STREAMER_TYPE_STREAM_ASPLAIN ) {
                    $this->_baseBodyIsStream = true;
                }
                unset( $bodyTest );
            }
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Output SyncBaseBody as a ' . (($this->_baseBodyIsStream) ? "STREAM" : "TEXT STRING") );
        }

		switch ($view) {
			case 'message':

                $plain = "";
                $html = "";
                $calendar = "";
                $hasBodyTypes = array( 'plain' => false, 'html' => false, 'calendar' => false, 'ms-tnef' => false, 'signed' => false, 'pkcs7-signature' => false, 'pkcs7-mime' => false );
				$msg = array();
                $attachments = array();	

                $response = $this->GetZimbraMessageBodies($zimbraFolderId, $id, $needHtml, $needPlain, $msg, $plain, $html, $calendar, $hasBodyTypes, $attachments);

                if ($response) {

                    $output = new SyncMail();

                    if ($hasBodyTypes['html'] === true) {
                        $output->nativebodytype = 2;
                    } else {
                        $output->nativebodytype = 1;
                    }
/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch PLAIN ['.$plain.']', false );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch HTML ['.$html.']', false );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch Calendar ['.$calendar.']', false );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch Attachments ['.print_r($attachments,true).'] ', false );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch hasBodyTypes ['.print_r($hasBodyTypes, true).']', false );

ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch Size plain['.strlen($plain).'] html['.strlen($html).'] MIME['.$msg['s'].'] AttachmentCount ['.count($attachments).'] ', false );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Batch MSG ['.print_r($msg, true).'] ', false );
*/
//debugLog( 'Zimbra->GetMessage(): ' .  'Batch MSG ['.print_r($msg, true).'] ', false );

                    $total = 0;
                    if (isset($msg['e'])) {
                        $total = count($msg['e']);
                    }
                    $from = ''; $to = ''; $cc = ''; $bcc = ''; $replyto = '';
                    for ($i=0;$i<$total;$i++) {

                    // Set $name = in order of preference - Personal Name, Display name, Email address
                        if (isset($msg['e'][$i]['p'])) {
                            $name = $msg['e'][$i]['p'];
                        } else {
                            $name = "";
                        }
                        $addr = $msg['e'][$i]['a'];
                        if (!empty($name)) {
                            $addr = "\"" . $name . "\" <" . $addr . ">";
                        }
                        
                        switch ($msg['e'][$i]['t']) {
                            case 'f':
                                $from .= empty($from) ? $addr : ", " . $addr;
                                break;
                            case 't':
                                $to .= empty($to) ? $addr : ", " . $addr;
                                break;
                            case 'c':
                                $cc .= empty($cc) ? $addr : ", " . $addr;
                                break;
                            case 'b':
                                $bcc .= empty($bcc) ? $addr : ", " . $addr;
                                break;
                            case 'r':
                                $replyto .= empty($replyto) ? $addr : ", " . $addr;
                                break;
                        }
                    }
                    //if (empty($replyto)) {
                    //    $replyto = $from;
                    //}

                    // Flags
                    if (isset($msg['f'])) {
                        $flagString = $msg['f'];
					} else {
					    $flagString = "";
					}
                    $flags = $this->GetFlags($flagString);

                    $output->importance = $flags["priority"];
                    $output->read = $flags["read"];
                    $output->flag = new SyncMailFlags();
                    if ($flags["flagged"] == 1) { 
                        $output->flag->flagstatus = 2;
                    } else {
                        $output->flag->flagstatus = 0;
                    }

                    if ($flags["replied"] == 1) {
                        $output->lastverbexecuted = AS_REPLYTOSENDER;
                    } elseif ($flags["forwarded"] == 1) {
                        $output->lastverbexecuted = AS_FORWARD;
                    }

                    // Conversation ID
                    if (isset($msg['cid'])) {
//                        $output->conversationid = $msg['cid'];
//                        $timestamp = gmdate("Y-m-d\TH:i:s\.000\Z" ,substr($msg['sd'], 0, 10));
//                        $output->conversationindex = $timestamp;
					} 

                    $output->datereceived = $this->Date4ActiveSync($msg['d'],'UTC',false); //UTCADJUST-NO
                    $output->messageclass = "IPM.Note";
                    if (isset($msg['su'])) {
                        $output->subject = $msg['su'];
                    } 

                    if ($to != "") $output->displayto = $to;   // TODO: What is the difference between 'displayto' and 'to'?
                    if ($to != "") $output->to = $to;
                    if ($cc != "") $output->cc = $cc;
                    if ($from != "") $output->from = $from;
                    if ($replyto != "") $output->reply_to = $replyto;



                    // Get the primary and secondary content type to detertmine if it is an S/MIME message
                    if (isset($msg['mp'][0]['ct'])) {
                        $contenttype = explode( '/', $msg['mp'][0]['ct']);
                        $ctp = $contenttype[0];
                        $cts = $contenttype[1];
                    } else {
                        $ctp = 'NOT-MULTIPART';
                    }
                    if (strtolower($ctp) == "multipart") {
                        switch(strtolower($cts)) {
                            case 'signed'	:
                                $output->messageclass = "IPM.Note.SMIME.MultipartSigned"; break;
                            default 	:
                                $output->messageclass = "IPM.Note";
                        }
                    }

                    // start AS12 Stuff (bodyPrefArray === false) case = old behaviour
                    if (Request::GetProtocolVersion() < 12) {

                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'No body preference' );
                        // Message Body - Plain 

                        if (isset($contentparameters->truncation)) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'ContentParameters->truncation is set' );
                            $truncation = $contentparameters->GetTruncation();
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Truncation ['.$truncation.']' );
                            $truncsize = Utils::GetTruncSize( $truncation );
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'TruncSize ['.$truncsize.']' );
                        } else {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'ContentParameters->truncation is NOT set' );
                        }

                        if(isset($truncsize) && (strlen($plain) > $truncsize)) {
                            $plain = Utils::Utf8_truncate($plain, $truncsize);
                            $output->bodytruncated = 1;
                        } else {
                            $output->bodytruncated = 0;
                        }

                        $output->bodysize = strlen($plain);
                        $output->body = $plain;

                        if (($mimesupport == 2 && $this->_useHTML) ||
                            ($mimesupport == 1 && $output->messageclass == "IPM.Note.SMIME.MultipartSigned")) { 
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . "MIME Body Preferred");

                            if (isset($this->_attachmentsBlocked) && $this->ToBool($this->_attachmentsBlocked)) {
                                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->GetMessage(): ' . 'ZIMBRA configuration is blocking the download of attachments (including MIME messages to iOS/Outlook) - Check Configure->Global Settings->Attachments->Attachments cannot be viewed regardless of COS -or- Configure->Class of service->[Class name]->Advanced->Attachment settings->Disable attachment viewing from web mail ui ' );
                            }

                            // For MIME - Just dump the raw message
                            if ($this->_baseBodyIsStream) {
                                $stats = $this->GetRawMessageStats( $id );
			
                                $output->mimedata = ZimbraHttpStreamWrapper::Open($this->_authtoken, $this->_publicURL, $id, "", $stats['download_content_length'], $this->_sslVerifyPeer, $this->_sslVerifyHost);
                                $output->mimesize = $stats['download_content_length'];
                            } else {
                                $output->mimedata = $this->GetRawMessage($id);
                                $output->mimesize = strlen($output->mimedata);
                            }

                            $output->mimetruncated = 0;

                            unset($output->body);
                            unset($output->bodysize);
                        }


                        $output->attachments = $attachments;
                        unset($attachments);

                    } else {
                        // AS12 or later

                        $output->asbody = new SyncBaseBody();

                        $output->contentclass="urn:content-classes:message";

                        // At this stage we should have valid data for Attachments, $plain and $html
						// Now we just need to figure out what to send to the client

                        // Attachments
                        if (count($attachments) > 0) {
                            $output->asattachments = array();
                        }
                        foreach($attachments as $attachment) {

                            $airSyncAttachment = new SyncBaseAttachment();

                            $airSyncAttachment->displayname = $attachment->displayname;
//                            $airSyncAttachment->filereference = $attachment->attname;  //File Reference
                            $airSyncAttachment->filereference = bin2hex( $attachment->attname );  //File Reference
                            $airSyncAttachment->method = $attachment->attmethod;
                            $airSyncAttachment->isinline = ($airSyncAttachment->method == 6); 
                            $airSyncAttachment->estimatedDataSize = $attachment->attsize;
                            $airSyncAttachment->contentid = $attachment->attoid;
                            if (!$airSyncAttachment->isinline) {
                                $airSyncAttachment->contentlocation = $attachment->attoid;
                            }
                            array_push($output->asattachments, $airSyncAttachment);

                            unset($airSyncAttachment);
                        }
                        unset($attachments);
                        unset($attachment);

//TO DO Revert to ||  ??
                        if ((isset($bodyPrefArray[4])) &&
                           ($mimesupport == 2 || ($mimesupport == 1 && $output->messageclass == "IPM.Note.SMIME.MultipartSigned"))) { 
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . "MIME Body Preferred");
                            $output->asbody->type = 4;

                            if (isset($this->_attachmentsBlocked) && $this->ToBool($this->_attachmentsBlocked)) {
                                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->GetMessage(): ' . 'ZIMBRA configuration is blocking the download of attachments (including MIME messages to iOS/Outlook) - Check Configure->Global Settings->Attachments->Attachments cannot be viewed regardless of COS -or- Configure->Class of service->[Class name]->Advanced->Attachment settings->Disable attachment viewing from web mail ui ' );
                            }

                            // For MIME - Just dump the raw message
                            if ($this->_baseBodyIsStream) {
                                $stats = $this->GetRawMessageStats( $id );
			
                                $output->asbody->data = ZimbraHttpStreamWrapper::Open($this->_authtoken, $this->_publicURL, $id, "", $stats['download_content_length'], $this->_sslVerifyPeer, $this->_sslVerifyHost);
                                $output->asbody->estimatedDataSize = $stats['download_content_length'];
                            } else {
                                $output->asbody->data = $this->GetRawMessage($id);
                                $output->asbody->estimatedDataSize = strlen($output->asbody->data);
                            }
// TO DO UNDO                            $output->asbody->estimatedDataSize = strlen($output->asbody->data);
                            $output->asbody->truncated = 0;

                            if (isset($bodyPrefArray[$output->asbody->type]['Preview']) && isset($msg['fr']) && trim($msg['fr']) != "") {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Got Preview Request' );
                                $preview = $msg['fr'];
			    				if (strlen($preview) > $bodyPrefArray[$output->asbody->type]['Preview']) {
                                    $preview = Utils::Utf8_truncate($preview, $bodyPrefArray[$output->asbody->type]['Preview']);
//TODO find previous word break and add ...					
                                }
						    	$output->asbody->preview = $preview;
                            }

						} else if (isset($bodyPrefArray[2])) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . "HTML Body Preferred!");
                            // Send HTML if requested 
                            // Text should already have been processed correctly into $html above
                            // so just check for truncation.

                            $output->asbody->type = 2;
                            $output->asbody->estimatedDataSize = strlen($html);

                            if (isset($bodyPrefArray[2]["TruncationSize"]) && ($output->asbody->estimatedDataSize > $bodyPrefArray[$output->asbody->type]["TruncationSize"])) {
                                $html = Utils::Utf8_truncate($html,$bodyPrefArray[$output->asbody->type]["TruncationSize"]);
                                $output->asbody->truncated = 1;
                            } else {
                                $output->asbody->truncated = 0;
                            }


                            if ($this->_baseBodyIsStream) {
                                if (!class_exists('StringStreamWrapper')) {
                                    include_once('include/stringstreamwrapper.php');
                                }
                                $output->asbody->data = StringStreamWrapper::Open($html);
                            } else {
                                $output->asbody->data = $html;
                            }

                            if (isset($bodyPrefArray[$output->asbody->type]['Preview']) && isset($msg['fr']) && trim($msg['fr']) != "") {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Got Preview Request' );
                                $preview = $msg['fr'];
			    				if (strlen($preview) > $bodyPrefArray[$output->asbody->type]['Preview']) {
                                    $preview = Utils::Utf8_truncate($preview, $bodyPrefArray[$output->asbody->type]['Preview']);
//TODO find previous word break and add ...					
                                }
						    	$output->asbody->preview = $preview;
                            }

                        } else {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . "Plaintext Body");
                            // Send Plaintext as Fallback or if original body is plaintext
                            // Text should already have been processed correctly into $plain above
                            // so just check for truncation.

                            $output->asbody->type = 1;
                            $output->asbody->estimatedDataSize = strlen($plain);
						
                            if (isset($bodyPrefArray[1]["TruncationSize"]) && ($output->asbody->estimatedDataSize > $bodyPrefArray[$output->asbody->type]["TruncationSize"])) {
                                $plain = Utils::Utf8_truncate($plain,$bodyPrefArray[$output->asbody->type]["TruncationSize"]);
                                $output->asbody->truncated = 1;
                            } else {
                                $output->asbody->truncated = 0;
                            }

                            if ($this->_baseBodyIsStream) {
                                if (!class_exists('StringStreamWrapper')) {
                                    include_once('include/stringstreamwrapper.php');
                                }
                                $output->asbody->data = StringStreamWrapper::Open($plain);
                            } else {
                                $output->asbody->data = $plain;
                            }

                            if (isset($bodyPrefArray[$output->asbody->type]['Preview']) && isset($msg['fr']) && trim($msg['fr']) != "") {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Got Preview Request' );
                                $preview = $msg['fr'];
			    				if (strlen($preview) > $bodyPrefArray[$output->asbody->type]['Preview']) {
                                    $preview = Utils::Utf8_truncate($preview, $bodyPrefArray[$output->asbody->type]['Preview']);
//TODO find previous word break and add ...					
                                }
						    	$output->asbody->preview = $preview;
                            }

                        }

                        // In case we have nothing for the body, send at least a blank... 
                        // dw2412 but only in case the body is not rtf!
//                        if ($output->asbody->type != 3 && (!isset($output->asbody->data) || strlen($output->asbody->data) == 0))
//                            $output->asbody->data = " ";
// NEED TO UNDO
                    } //end else Request::GetProtocolVersion()

                    $output->internetcpid = 65001;

				
                    // Message Has Tags - Sync them as Categories to Client
                    if (isset($msg['tn']) && (trim($msg['tn']) != "")) {
                        $output->categories = explode( ',', trim($msg['tn']) );
                    } elseif (isset($msg['t']) && (trim($msg['t']) != "")) {
                        $output->categories = $this->TagsToCategories( trim($msg['t']) );
                    }

                    // Meeting Request
                    // TO DO - figure out how client ends up making a message with an empty 'inv' block
                    if (isset($msg['inv'][0]) && isset($msg['inv'][0]['type']) && $msg['inv'][0]['type'] == 'appt') {

                        $output->meetingrequest = new SyncMeetingRequest();
                        
                            /* Instancetype - TODO - figure this out
                                0 = single appointment
                                1 = master recurring appointment
                                2 = single instance of recurring appointment 
                                3 = exception of recurring appointment
                            */
                        if (($this->_zimbraVersion == '5.0') && (isset($msg['inv'][0]['comp']))) {
                            $output->messageclass = "IPM.Schedule.Meeting.Request";
                            $output->meetingrequest->instancetype = 0;      // 'Instance' is always 0
                        } elseif (isset($msg['inv'][0]['comp'][0]['method']) && strtoupper($msg['inv'][0]['comp'][0]['method']) == 'REQUEST') {
                            $output->messageclass = "IPM.Schedule.Meeting.Request";
                            $output->meetingrequest->instancetype = 0;      // 'Instance' is always 0
                        } elseif (isset($msg['inv'][0]['comp'][0]['method']) && strtoupper($msg['inv'][0]['comp'][0]['method']) == 'REPLY') {
                            $output->messageclass = "IPM.Notification.Meeting";
                            if (isset($msg['inv'][0]['comp'][0]['at'][0]['ptst'])) {
                                switch  (strtoupper($msg['inv'][0]['comp'][0]['at'][0]['ptst'])) {
                                    case 'AC':
                                        $output->messageclass = "IPM.Schedule.Meeting.Resp.Pos";
                                        break;
                                    case 'TE':
                                        $output->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
                                        break;
                                    case 'DE':
                                        $output->messageclass = "IPM.Schedule.Meeting.Resp.Neg";
                                        break;
                                    default:
                                        $output->messageclass = "IPM.Notification.Meeting";
                                }
                            }
                        } elseif (isset($msg['inv'][0]['comp'][0]['method']) && strtoupper($msg['inv'][0]['comp'][0]['method']) == 'CANCEL') {
                            $output->messageclass = "IPM.Schedule.Meeting.Canceled";
                        } else {
                            $output->messageclass = "IPM.Notification.Meeting";
                        }
// Check md/ms here ?
                        if (isset($msg['inv'][0]['comp'][0]['d']) && (strlen($msg['inv'][0]['comp'][0]['d']) >= 10)) {
                            $output->meetingrequest->dtstamp = substr( $this->fixMS( $msg['inv'][0]['comp'][0]['d'] ), 0, 10);
                        }

                        $output->meetingrequest->globalobjid = base64_encode(Utils::getOLUidFromICalUid($msg['inv'][0]['comp'][0]['uid']));
 
                        // Deals with requests for exceptions.  Look @ ics.php for more info
                        //$output->meetingrequest->recurrenceid = $this->_getGMTTimeByTZ($basedate, $this->_getGMTTZ());  
                        
                        if (isset($msg['inv'][0]['tz'][0])) {
                            // $tzObject = $this->GetTz($msg['inv'][0]['tz'][$maxTz]);
                            // 2016-05-19 If TimeZone on appointment changed, it will store an array of the history. Use Start Time to determine the latest.
                            $maxTz = count($msg['inv'][0]['tz']) - 1;
                            $tzObject = $this->GetTz($msg['inv'][0]['tz'][0]);
                            for ($k=0;$k<=$maxTz;$k++) {
                                if (isset($msg['inv'][0]['comp'][0]['s'][0]['tz']) && ($msg['inv'][0]['comp'][0]['s'][0]['tz'] == $msg['inv'][0]['tz'][$k]['id']))  {
                                    $tzObject = $this->GetTz($msg['inv'][0]['tz'][$k]);
                                }
                            }
                            if ($this->_zimbraVersion == '5.0') {
                                $v6tz = $this->LookupV5Timezone( "", $tzObject['name']);
                                if ($v6tz !== false) {
                                    $tzObject['name'] = $v6tz;
                                } else {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Meeting Request Default TimeZone [".$tzObject['name']."] NOT FOUND in v5timezone.xml - APPOINTMENT WILL BE SYNCED WITH NO TIMEZONE NAME"  );
                                    $tzObject['name'] = "";
                                }
                            } 
                        } else {
                            $tzObject = $this->GetTzGmt();
                        }
                        $output->meetingrequest->timezone = base64_encode($this->GetTzSyncBlob($tzObject));

                        if(isset($msg['inv'][0]['comp'][0]['allDay'])) {
                            $output->meetingrequest->alldayevent = 1;

                            if(isset($msg['inv'][0]['comp'][0]['s'][0]['d'])) {
                                $output->meetingrequest->starttime = $this->Date4ActiveSync( $msg['inv'][0]['comp'][0]['s'][0]['d'], $this->_tz);
                            }

                            if(isset($msg['inv'][0]['comp'][0]['e'][0]['d'])) {
                                $output->meetingrequest->endtime = $this->Date4ActiveSync($msg['inv'][0]['comp'][0]['e'][0]['d'], $this->_tz) + 86400;
                            }                                                                                
                        } else {
                            $output->meetingrequest->alldayevent = 0;

                            if(isset($msg['inv'][0]['comp'][0]['s'][0]['d'])) {
                                if(isset($msg['inv'][0]['comp'][0]['s'][0]['tz'])) {
                                    if ($this->_zimbraVersion == '5.0') {
                                        $v6tz = $this->LookupV5Timezone( "", $msg['inv'][0]['comp'][0]['s'][0]['tz']);
                                        if ($v6tz !== false) {
                                            $tzName = $v6tz;
                                        } else {
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Meeting Request StartTime TimeZone [".$msg['inv'][0]['comp'][0]['s'][0]['tz']."] NOT FOUND in v5timezone.xml - APPOINTMENT START TIME WILL USE MEETING DEFAULT TIMEZONE"  );
                                            $tzName = $tzObject['name'];
                                        }
                                    } else {
                                        $tzName = $msg['inv'][0]['comp'][0]['s'][0]['tz'];
                                    }
                                } else {
                                    $tzName = $tzObject['name'];
                                }
                                $output->meetingrequest->starttime = $this->Date4ActiveSync($msg['inv'][0]['comp'][0]['s'][0]['d'], $tzName);
                            }

                            if(isset($msg['inv'][0]['comp'][0]['e'][0]['d'])) {
                                if(isset($msg['inv'][0]['comp'][0]['e'][0]['tz'])) {
                                    if ($this->_zimbraVersion == '5.0') {
                                        $v6tz = $this->LookupV5Timezone( "", $msg['inv'][0]['comp'][0]['e'][0]['tz']);
                                        if ($v6tz !== false) {
                                            $tzName = $v6tz;
                                        } else {
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Meeting Request EndTime TimeZone [".$msg['inv'][0]['comp'][0]['e'][0]['tz']."] NOT FOUND in v5timezone.xml - APPOINTMENT END TIME WILL USE MEETING DEFAULT TIMEZONE"  );
                                            $tzName = $tzObject['name'];
                                        }
                                    } else {
                                        $tzName = $msg['inv'][0]['comp'][0]['e'][0]['tz'];
                                    }
                                } else {
                                   $tzName = $tzObject['name'];
                                }
                                $output->meetingrequest->endtime = $this->Date4ActiveSync($msg['inv'][0]['comp'][0]['e'][0]['d'], $tzName);
                            }                                                    
                        }
                        
                        if(isset($msg['inv'][0]['comp'][0]['loc'])) {
                            $output->meetingrequest->location = w2ui($msg['inv'][0]['comp'][0]['loc']);
                        }

                        if (isset($msg['inv'][0]['comp'][0]['class']) && $msg['inv'][0]['comp'][0]['class'] == "CON") {
                            $output->meetingrequest->sensitivity = 2;
                        } else if (isset($msg['inv'][0]['comp'][0]['class']) && $msg['inv'][0]['comp'][0]['class'] == "PRI") {
                            $output->meetingrequest->sensitivity = 1;
                        } else {
                            $output->meetingrequest->sensitivity = 0;
                        }

                        // Reminder
                        if(isset($msg['inv'][0]['comp'][0]['alarm'][0]['trigger'][0]['rel'][0]['m'])) {
                            $output->meetingrequest->reminder = $msg['inv'][0]['comp'][0]['alarm'][0]['trigger'][0]['rel'][0]['m'];
                        }

                        // Organizer Name & Email
                        if(isset($msg['inv'][0]['comp'][0]['or']['a'])) {
                            if(isset($msg['inv'][0]['comp'][0]['or']['d'])) {
                                $output->meetingrequest->organizer = '"' . $msg['inv'][0]['comp'][0]['or']['d'] . '" <' . $msg['inv'][0]['comp'][0]['or']['a'] . '>';
                            } else {
                                $output->meetingrequest->organizer = $msg['inv'][0]['comp'][0]['or']['a'];
                            } 
                        } else {
                            if (strtoupper($msg['inv'][0]['comp'][0]['method']) == 'REPLY') {
                                $output->meetingrequest->organizer = $to;
                            } else {
                                $output->meetingrequest->organizer = $from;
                            }
                        }

                        $output->meetingrequest->responserequested = 1;
                        
                        /* Instancetype
                            0 = single appointment
                            1 = master recurring appointment
                            2 = single instance of recurring appointment 
                            3 = exception of recurring appointment
                        */
                        $output->meetingrequest->instancetype = 0;      // 'Instance' is always 0
                    }
                }    
                break;

			case 'contact':
                $soap ='<GetContactsRequest sync="1" xmlns="urn:zimbraMail">
                            <cn id="'.$id.'"/>
                        </GetContactsRequest>';

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);

                if($response) {


                    $array = json_decode($response, true);

 			        unset($response); // We never use it again

                    $item = $array['Body']['GetContactsResponse']['cn'][0];
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'CONTACT:'. print_r( $item, true ), false );
                    unset($array);

                    $output = new SyncContact();

                    // Contact Has Tags - Sync them as Categories to Client
                    if (isset($item['tn']) && (trim($item['tn']) != "")) {
                        $output->categories = explode( ',', trim($item['tn']) );
                    } elseif (isset($item['t']) && (trim($item['t']) != "")) {
                      $output->categories = $this->TagsToCategories( $item['t'] );
                    }
																					
                    /* TODO: Add a check to confirm $params is an array */
                    $param = array();
                    $param = $item['_attrs'];
                    $output->fileas = $item['fileAsStr'];

/* REMOVE Virtual Folders Categories - TAGs will be sync'ed as Categories
                    // For virtual folders, assign the folder name to the category
                    if (count($this->_virtual['contact']) > 0) {
                        $index = $this->GetFolderIndexZimbraID($item['l']);
                        if (!empty($index)) {
                            $output->categories = array($this->_folders[$index]->name);
                        }
                    }
*/

                    foreach ($this->_contactMapping as $k => $v) {
                        if ($k <> '' && $v <> '') {

                            if(isset($param[$v]) && ($k == 'birthday' || $k == 'anniversary') ) {
									// if no year stored year (--MM-DD) then add this year (or next year if date already passed) 
									$outDate = $param[$v];
									if (substr($outDate,0,2) == '--') {
									    $outDate = date('Y').substr($outDate,2,2).substr($outDate,5,2);
									    if ($outDate < date('Ymd')) {
									        $outDate = strval(intval(date('Y'))+1).substr($outDate,4,2).substr($outDate,6,2);
									    }
									} else {
									    $outDate = substr($outDate,0,4).substr($outDate,5,2).substr($outDate,8,2);
									}
                                    // Some older android clients have issues with birthday sync causing constant loops - config setting allows for disabling it
                                    if (($k == 'birthday') && ($this->_disableBirthdaySync)) {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "ZIMBRA_DISABLE_BIRTHDAY_SYNC set to true - SKIP BIRTHDAY" );
                                    } else {
                                        $output->$k = $this->Date4ActiveSync($outDate."T000000", "UTC");
                                    }
                            } else if ($k == 'picture' && defined('ZIMBRA_SYNC_CONTACT_PICTURES') && (ZIMBRA_SYNC_CONTACT_PICTURES == true) && array_key_exists($v, $param)) {

                                if (isset($param[$v]['part'])) {
                                    $image = $this->DownloadFromZimbra($this->_accountRestURL . '/?id='.$item['id'].'&part='.$param[$v]['part']);
                                    $output->$k = $image;
                                }

                            } else if($k == 'children') {
                                if (isset($param[$v]) ) {

									$children = explode(",",$param[$v]);

                                    $output->$k = $children;
                                }
                            } else if ($k == 'body') {
                                if (isset($param[$v]) ) {
                                    $notes = str_replace("\n","\r\n", str_replace("\r","",$param[$v]));
                                    if (Request::GetProtocolVersion() >= 12.0) {
                                        $output->asbody = new SyncBaseBody();
                                        $output->asbody->type = 1;
                                        $output->asbody->estimatedDataSize = strlen($notes);
                                        if (isset($bodyPrefArray[$output->asbody->type]["TruncationSize"]) && ($output->asbody->estimatedDataSize > $bodyPrefArray[$output->asbody->type]["TruncationSize"])) {
                                            $notes = Utils::Utf8_truncate($notes,$bodyPrefArray[$output->asbody->type]["TruncationSize"]);
                                            $output->asbody->truncated = 1;
                                        } else {
                                            $output->asbody->truncated = 0;
                                        }
                                        if ($this->_baseBodyIsStream) {
                                            if (!class_exists('StringStreamWrapper')) {
                                                include_once('include/stringstreamwrapper.php');
                                            }
                                            $output->asbody->data = StringStreamWrapper::Open($notes);
                                        } else {
                                            $output->asbody->data = $notes;
                                        }
                                    } else {
                                        $output->body = $notes;
                                        $output->bodytruncated = 0;
                                    }
                                }
                            } else {
                                $output->$k = '';
                                if (strrpos($v,',') === false) {
                                    if (isset($param[$v]) ) {
                                        $output->$k = $param[$v];
                                    }
                                } else {
                                    $v_vals = explode(",",$v);
                                    foreach ($v_vals as $v_val) {
                                        if (isset($param[$v_val]) ) {
                                              $output->$k = $param[$v_val];
                                            break;
                                        }
                                    }
                                    unset( $v_vals );
                                    unset( $v_val );
                               }
                            }
                        }
                    }
                    unset( $k );
                    unset( $v );
				}
                break;

			case 'appointment':
                $soap ='<GetAppointmentRequest id="'.$id.'" sync="1" includeContent="1" xmlns="urn:zimbraMail"/>';

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);

                if($response) {


                    $array = json_decode($response, true);

 			        unset($response); // We never use it again

                    $item = $array['Body']['GetAppointmentResponse']['appt'][0];
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): '.' APPT: ' .  print_r( $item, true ), false );
                    unset($array);

                    $parentindex = $this->GetFolderIndexZimbraID($item['l']);
                    $folderperm = (isset($this->_folders[$parentindex]->perm) ? $this->_folders[$parentindex]->perm : "");
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): '.' APPT: Parent Folder [' .  $item['l'] . '] - Perm [' . $folderperm . ']' );

                    $total = count($item['inv']);

                    // Timezone
                    if (isset($item['inv'][0]['tz'][0])) {
                        // $tzObject = $this->GetTz($item['inv'][0]['tz'][$maxTz]);
                        // 2016-05-19 If TimeZone on appointment changed, it will store an array of the history. Use Start Time to determine the latest.
                        $maxTz = count($item['inv'][0]['tz']) - 1;
                        $tzObject = $this->GetTz($item['inv'][0]['tz'][0]);
                        for ($k=0;$k<=$maxTz;$k++) {
                            if (isset($item['inv'][0]['comp'][0]['s'][0]['tz']) && ($item['inv'][0]['comp'][0]['s'][0]['tz'] == $item['inv'][0]['tz'][$k]['id']))  {
                                $tzObject = $this->GetTz($item['inv'][0]['tz'][$k]);
                            }
                        }
                        if ($this->_zimbraVersion == '5.0') {
                            $v6tz = $this->LookupV5Timezone( "", $tzObject['name']);
                            if ($v6tz !== false) {
                                $tzObject['name'] = $v6tz;
                            } else {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Appointment TimeZone [".$tzObject['name']."] NOT FOUND in v5timezone.xml - APPOINTMENT WILL BE SYNCED WITH NO TIMEZONE NAME"  );
                                $tzObject['name'] = "";
                            }
                        }
                    } else {
                        $tzObject = $this->GetTzGmt();
                    }
                    $tzName = $tzObject['name'];

                    $mainApp = new SyncAppointment();
                    $exceptions = array();

                    for($i=0; $i<$total; $i++) {

//                        Experimental fix for single exception to meeting series received as unique meeting - 20160428
//                        if(isset($item['inv'][$i]['comp'][0]['ex'])) {
                        if(($i > 0) && isset($item['inv'][$i]['comp'][0]['ex'])) {
                            $subApp = new SyncAppointmentException();
                            $bSupApp = true;
                        } else {
                            $subApp = $mainApp;
                            $bSupApp = false;
                        }


                        if ($bSupApp) { // Only include on exceptions - not on main appointment
                            if(isset($item['inv'][$i]['comp'][0]['exceptId'][0]['d'])) {
                                if(isset($item['inv'][$i]['comp'][0]['exceptId'][0]['tz'])) {
                                    if ($this->_zimbraVersion == '5.0') {
                                        $v6tz = $this->LookupV5Timezone( "", $item['inv'][$i]['comp'][0]['exceptId'][0]['tz']);
                                        if ($v6tz !== false) {
                                            $tzName = $v6tz;
                                        } else {
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Appointment StartTime TimeZone [".$item['inv'][$i]['comp'][0]['e'][0]['tz']."] NOT FOUND in v5timezone.xml - APPOINTMENT END TIME WILL USE APPOINTMENT TIMEZONE"  );
                                            $tzName = $tzObject['name'];
                                        }
                                    } else {
                                        $tzName = $item['inv'][$i]['comp'][0]['exceptId'][0]['tz'];
                                    }
                                } else {
                                    $tzName = $tzObject['name'];
                                }
                                $subApp->exceptionstarttime = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['exceptId'][0]['d'], $tzName);
                            }                                                    
                        }


                        // Single Exception deleted - Just flag deleted - Skip all other fields
                        if($bSupApp && isset($item['inv'][$i]['comp'][0]['status']) && $item['inv'][$i]['comp'][0]['status'] == 'CANC') {

                            $subApp->deleted = 1;
                            $subApp->meetingstatus = null; 

                        } else {
                            // Either the main appointment - or a MOVED exception

                            // e.g. 1275619811 (No TZ)
                            $subApp->dtstamp = substr( $this->fixMS( $item['inv'][$i]['comp'][0]['d'] ), 0, 10);
                            $subApp->zimbraInvId = $item['id'] . "-" . $item['inv'][$i]['id'];

                            // Appointment Has Tags - Sync them as Categories to Client
                            if (isset($item['tn']) && (trim($item['tn']) != "")) {
                                $subApp->categories = explode( ',', trim($item['tn']) );
                            } elseif (isset($item['t']) && (trim($item['t']) != "")) {
                                $subApp->categories = $this->TagsToCategories( $item['t'] );
                            }

                            if (!$bSupApp) { // Only include on main appointment - not on exceptions
                                $subApp->uid = $item['inv'][$i]['comp'][0]['uid'];

                                $subApp->zimbraMs = $item['ms'];
                                $subApp->zimbraRev = $item['rev'];
                                $subApp->zimbraCompNum = $item['inv'][$i]['compNum'];

                                // Organizer Name & Email
                                if (isset($item['inv'][$i]['comp'][0]['or']['d'])) {
// 2015-10-21 Don't set organizer if shared folder as it will prevent editing on some devices
                                    if ((strpos($folderid, '-') === false) || ((strpos($folderid, '-') !== false) && (strpos($folderperm, 'w') === false))) {
                                        $subApp->organizername = $item['inv'][$i]['comp'][0]['or']['d'];
                                    }
                                    $subApp->premodorganizername = $item['inv'][$i]['comp'][0]['or']['d'];
                                } else {
                                    $subApp->premodorganizername = "";
                                } 
                                if (isset($item['inv'][$i]['comp'][0]['or']['a'])) {
// 2015-10-21 Don't set organizer if shared folder as it will prevent editing on some devices
                                    if ((strpos($folderid, '-') === false) || ((strpos($folderid, '-') !== false) && (strpos($folderperm, 'w') === false))) {
                                        $subApp->organizeremail = $item['inv'][$i]['comp'][0]['or']['a'];
                                    }
                                    $subApp->premodorganizeremail = $item['inv'][$i]['comp'][0]['or']['a'];
                                } else {
                                    $subApp->premodorganizeremail = "";
                                } 
                                $subApp->isorganizer = (isset($item['inv'][$i]['comp'][0]['isOrg']) ? 1 : 0);
                                $isOrganizer = $subApp->isorganizer;

                                // Timezone
                                $subApp->timezone = base64_encode($this->GetTzSyncBlob($tzObject));

                                // Recurrence
                                if (isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0])) {
                                    $recurrence = new SyncRecurrence();

                                    /* SEC,MIN,HOU,DAI,WEE,MON,YEA
                                        DAI => 0
                                        WEE => 1
                                        MON => 2 or 3
                                        YEA => 5 or 6 (What does 4 mean?)
                                    */
                                    if($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['freq'] == "DAI") {
                                        $recurrence->type = '0';
                                    } else if($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['freq'] == "WEE") {
                                        $recurrence->type = '1';
                                    } else if($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['freq'] == "MON") {
                                        if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bymonthday'][0]['modaylist'][0])) {
                                            $recurrence->type = '2';
                                        } else {
                                            $recurrence->type = '3';
                                        }
                                    } else if($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['freq'] == "YEA") {
                                        if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bymonthday'][0]['modaylist'][0])) {
                                            $recurrence->type = '5';
                                        } else {
                                            $recurrence->type = '6';
                                        }
                                    }
                                    $recurrence->premodtype = $recurrence->type; 

                                    // Swapped these around, and added else - as spec says use only one, and use occurrences before until date
                                    if (isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['count'][0]['num'])) {
                                        $recurrence->occurrences = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['count'][0]['num'];
                                    } else if (isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['until'][0]['d'])) {
                                        // 20100827T045959Z
//                                        $recurrence->until = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['until'][0]['d'], $tzName);
                                        // Devices expect recurrence to stop with teh actual starttime of the last meeting.
                                        // Zimbra adds 235959Z instead. Try to patch together YYYYMMDDT from until, and HHMMSS from meeting starttime
                                        $deviceUntil = substr( $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['until'][0]['d'], 0, 9 ) . substr( $item['inv'][$i]['comp'][0]['s'][0]['d'], 9, 6 );
                                        $recurrence->until = $this->Date4ActiveSync($deviceUntil, $tzName);
                                    }

                                    if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['interval'][0]['ival'])) {
                                        $recurrence->interval = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['interval'][0]['ival'];
                                    } else {
                                       	  // Added setting interval to 1 because Nokia would discard appointments without it
                                    	  // TO DO - figure out if this is only a Nokia issue. 
                                    	  // 1 is default if not set in any case so should do no harm.
                                        $recurrence->interval = '1';
                                    }

                                    /*  $recurrence->dayofweek
                                         bitmask of days (1 == sunday, 64 == saturday)
                                         SU => 1
                                         MO => 2
                                         TU => 4
                                         WE => 8
                                         TH => 16
                                         FR => 32
                                         SA => 64
                                    */
                                    if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['byday'][0]['wkday'])) {
                                        $bitmask = 0;
                                        for( $j=0;$j<count($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['byday'][0]['wkday']);$j++ ) {
                                            $weekday = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['byday'][0]['wkday'][$j]['day'];
                                            switch($weekday) {
                                                case 'SU': $bitmask += 1; break;
                                                case 'MO': $bitmask += 2; break;
                                                case 'TU': $bitmask += 4; break;
                                                case 'WE': $bitmask += 8; break;
                                                case 'TH': $bitmask += 16; break;
                                                case 'FR': $bitmask += 32; break;
                                                case 'SA': $bitmask += 64; break;
                                            }
                                        }
                                        $recurrence->dayofweek = $bitmask;
                                        if ($recurrence->type == '0') {
                                            // Override type to 1 (WEE) in case zimbra has an original type of 0 (DAI) which will not be handled correctly by all devices
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'Appointment - recurrence type DAI (type 0) - being output as weekly (type 1) due to the presense of weekday bitmask ' );
                                            $recurrence->type = '1';
                                        } 
                                    } 

                                    if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bymonthday'][0]['modaylist'])) {
                                        $recurrence->dayofmonth = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bymonthday'][0]['modaylist'];
                                    }

                                    // Not sure where the appointment originated - but had one appointment
                                    // with this attribute (wkday ordwk)  set instead of the next 
                                    // one below (bysetpos poslist). Adding this rule check first
                                    // to allow the other to overrule if necessary
														
                                    if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['byday'][0]['wkday'][0]['ordwk'])) {
                                        $recurrence->weekofmonth = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['byday'][0]['wkday'][0]['ordwk'];
                                    }
    
                                    if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bysetpos'][0]['poslist'])) {
                                        $recurrence->weekofmonth = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bysetpos'][0]['poslist'];
                                    }

                                    // Activesync requires weekofmonth to be 5 for last week - zimbra uses -1
                                    if ($recurrence->weekofmonth == -1) {
                                      	$recurrence->weekofmonth = 5;
                                    }

                                    if(isset($item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bymonth'][0]['molist'])) {
                                        $recurrence->monthofyear = $item['inv'][$i]['comp'][0]['recur'][0]['add'][0]['rule'][0]['bymonth'][0]['molist'];
                                    }

                                    // Add fix for zimbra not storing all details for YEARLY appointment
                                    if (($recurrence->type == 6) && (!isset($recurrence->weekofmonth))) {
                                        if(isset($item['inv'][$i]['comp'][0]['s'][0]['d'])) {
                                          	$recurrence->type = 5;
                                          	$recurrence->dayofmonth = 0 + substr($item['inv'][$i]['comp'][0]['s'][0]['d'], 6, 2);
                                          	$recurrence->monthofyear = 0 + substr($item['inv'][$i]['comp'][0]['s'][0]['d'], 4, 2);
                                        }
                                    }

                                    $subApp->recurrence = $recurrence;
                                }

                                // Recurrence Exceptions on Main Appointment - zimbra CalDAV puts them here ?
                                if (isset($item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates'][0])) {
                                    for ($j=0; $j<count($item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates']); $j++) {
                                        if(isset($item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates'][0]['tz'])) {
                                            if ($this->_zimbraVersion == '5.0') {
                                                $v6tz = $this->LookupV5Timezone( "", $item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates'][0]['tz']);
                                                if ($v6tz !== false) {
                                                    $tzName = $v6tz;
                                                } else {
                                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Appointment StartTime TimeZone [".$item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates'][0]['tz']."] NOT FOUND in v5timezone.xml - APPOINTMENT END TIME WILL USE APPOINTMENT TIMEZONE"  );
                                                    $tzName = $tzObject['name'];
                                                }
                                            } else {
                                                $tzName = $item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates'][0]['tz'];
                                            }
                                        } else {
                                            $tzName = 'UTC';
                                        }
                                        $mainAppEx = new SyncAppointmentException();
                                        $mainAppEx->exceptionstarttime = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['recur'][0]['exclude'][0]['dates'][$j]['dtval'][0]['s'][0]['d'], $tzName);
                                        $mainAppEx->deleted = 1;
                                        array_push($exceptions, $mainAppEx); 
                                        unset($mainAppEx);
                                    }
                                }
    
                            }

                            if(isset($item['inv'][$i]['comp'][0]['allDay'])) {
                                $subApp->alldayevent = 1;

                                if (!$bSupApp) { // Only include Timezone on main appointment - not on exceptions
                                    if (!isset($this->_userTzObject)) {
	                                    $this->_userTzObject = $this->GetLocalTzObject( $zimbraFolderId, $this->_tz );
                                    }
                                    if ($this->_userTzObject !== false) {
                                        $subApp->timezone = base64_encode($this->GetTzSyncBlob($this->_userTzObject));
                                    }
                                }

                                // YYYYMMDD - No complications - just convert using the client's TimeZone
                                if(isset($item['inv'][$i]['comp'][0]['s'][0]['d'])) {
                                    $subApp->starttime = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['s'][0]['d'], $this->_tz); // $this->_tz
                                }

                                // YYYYMMDD - No complications - just convert using the client's TimeZone
                                // Only wrinkle - Zimbra stores the end date as the day the appointment actually ends
                                // ActiveSync wants the appointment to end at Midnight - or 00:00:00 on the next day
                                // So add 86400 seconds to the end date to push it over to the next calendar day.
                                if(isset($item['inv'][$i]['comp'][0]['e'][0]['d'])) {
                                    $subApp->endtime = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['e'][0]['d'], $this->_tz) + 86400;
                                }                                                                                
                            } else {
                                $subApp->alldayevent = 0;
                            
                                // 1275619811000 (No TZ; +3 Digits)                  
                                if(isset($item['inv'][$i]['comp'][0]['s'][0]['d'])) {
                                    if(isset($item['inv'][$i]['comp'][0]['s'][0]['tz'])) {
                                        if ($this->_zimbraVersion == '5.0') {
                                            $v6tz = $this->LookupV5Timezone( "", $item['inv'][$i]['comp'][0]['s'][0]['tz']);
                                            if ($v6tz !== false) {
                                                $tzName = $v6tz;
                                            } else {
                                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Appointment StartTime TimeZone [".$item['inv'][$i]['comp'][0]['s'][0]['tz']."] NOT FOUND in v5timezone.xml - APPOINTMENT START TIME WILL USE APPOINTMENT TIMEZONE"  );
                                                $tzName = $tzObject['name'];
                                            }
                                        } else {
                                            $tzName = $item['inv'][$i]['comp'][0]['s'][0]['tz'];
                                        }
                                    } else {
                                        $tzName = $tzObject['name'];
                                    }
                                    $subApp->starttime = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['s'][0]['d'], $tzName);
                                }

                                if(isset($item['inv'][$i]['comp'][0]['e'][0]['d'])) {
                                    if(isset($item['inv'][$i]['comp'][0]['e'][0]['tz'])) {
                                        if ($this->_zimbraVersion == '5.0') {
                                            $v6tz = $this->LookupV5Timezone( "", $item['inv'][$i]['comp'][0]['e'][0]['tz']);
                                            if ($v6tz !== false) {
                                                $tzName = $v6tz;
                                            } else {
                                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "Appointment StartTime TimeZone [".$item['inv'][$i]['comp'][0]['e'][0]['tz']."] NOT FOUND in v5timezone.xml - APPOINTMENT END TIME WILL USE APPOINTMENT TIMEZONE"  );
                                                $tzName = $tzObject['name'];
                                            }
                                        } else {
                                            $tzName = $item['inv'][$i]['comp'][0]['e'][0]['tz'];
                                        }
                                    } else {
                                        $tzName = $tzObject['name'];
                                    }
                                    $subApp->endtime = $this->Date4ActiveSync($item['inv'][$i]['comp'][0]['e'][0]['d'], $tzName);
                                }                                                    
                            }

/* REMOVE in favour of Tags <-> Categories sync'ing                       
                            // For virtual folders, assign the folder name to the category
                            if (count($this->_virtual['appointment']) > 0) {
                                $index = $this->GetFolderIndexZimbraID($item['l']);
                                if (!empty($index)) {
                                    $subApp->categories = array($this->_folders[$index]->name);
                                }
                            }
*/

                            // Appointment Has Tags - Sync them as Categories to Client
                            if (isset($item['tn']) && (trim($item['tn']) != "")) {
                                $subApp->categories = explode( ',', trim($item['tn']) );
                            } elseif (isset($item['t']) && (trim($item['t']) != "")) {
                                $subApp->categories = $this->TagsToCategories( $item['t'] );
                            }
						
                            if(isset($item['inv'][$i]['comp'][0]['name'])) {
//                                $subApp->subject = w2u($item['inv'][$i]['comp'][0]['name']);
// w2u breaking accented characters for European countries - not sure why it was added between rev 36 & 37 ?
                                $subApp->subject = $item['inv'][$i]['comp'][0]['name'];
                            }

                            if(isset($item['inv'][$i]['comp'][0]['loc']) && (trim($item['inv'][$i]['comp'][0]['loc']) != "")) {
//                                $subApp->location = w2u($item['inv'][$i]['comp'][0]['loc']);
// w2u breaking accented characters for European countries - not sure why it was added between rev 36 & 37 ?
                                $subApp->location = $item['inv'][$i]['comp'][0]['loc'];
                            }

                            /*  Free/Busy Status
                                 [fba="F|B|T|O"]
                                 F => 0
                                 B => 2
                                 T => 1
                                 O => 3
                            */
                            if (isset($item['inv'][$i]['comp'][0]['fba']) && $item['inv'][$i]['comp'][0]['fba'] == "O") {
                                $subApp->busystatus = 3;
                            } else if (isset($item['inv'][$i]['comp'][0]['fba']) && $item['inv'][$i]['comp'][0]['fba'] == "B") {
                                $subApp->busystatus = 2;
                            } else if (isset($item['inv'][$i]['comp'][0]['fba']) && $item['inv'][$i]['comp'][0]['fba'] == "T") {
                                $subApp->busystatus = 1;
                            } else {
                                $subApp->busystatus = 0;
                            }

                            /*  Sensitivity Status
                                 [class="PUB|PRI|CON"]
                                 PUB => 0 // Default
                                 PRI => 1
                                 CON => 2
                            */
                            if (isset($item['inv'][$i]['comp'][0]['class']) && $item['inv'][$i]['comp'][0]['class'] == "CON") {
                                $subApp->sensitivity = 2;
                            } else if (isset($item['inv'][$i]['comp'][0]['class']) && $item['inv'][$i]['comp'][0]['class'] == "PRI") {
                                $subApp->sensitivity = 1;
//                            } else {
//                                $subApp->sensitivity = 0;
                            }

    						if (isset($item['inv'][$i]['comp'][0]['desc'][0]['_content'])) {
                                $subApp->zimbraPlainNotes = $item['inv'][$i]['comp'][0]['desc'][0]['_content'];
                                $notes = str_replace("\n","\r\n", str_replace("\r","",$item['inv'][$i]['comp'][0]['desc'][0]['_content']));
                                if (Request::GetProtocolVersion() >= 12.0) {
                                    $subApp->asbody = new SyncBaseBody();
                                    $subApp->asbody->type = 1;
                                    $subApp->asbody->estimatedDataSize = strlen($notes);
                                    if (isset($bodyPrefArray[$subApp->asbody->type]["TruncationSize"]) && ($subApp->asbody->estimatedDataSize > $bodyPrefArray[$subApp->asbody->type]["TruncationSize"])) {
                                        $notes = Utils::Utf8_truncate($notes,$bodyPrefArray[$subApp->asbody->type]["TruncationSize"]);
                                        $subApp->asbody->truncated = 1;
                                    } else {
                                        $subApp->asbody->truncated = 0;
                                    }
                                    if ($this->_baseBodyIsStream) {
                                        if (!class_exists('StringStreamWrapper')) {
                                            include_once('include/stringstreamwrapper.php');
                                        }
                                        $subApp->asbody->data = StringStreamWrapper::Open($notes);
                                    } else {
                                        $subApp->asbody->data = $notes;
                                    }
                                } else {
                                    $subApp->body = $notes;
                                    $subApp->bodytruncated = 0;
                                }
                            }
	    					if (isset($item['inv'][$i]['comp'][0]['descHtml'][0]['_content'])) {
                                $subApp->zimbraHtmlNotes = $item['inv'][$i]['comp'][0]['descHtml'][0]['_content'];
                            }

                            // Reminder
                            if(isset($item['inv'][$i]['comp'][0]['alarm'][0]['trigger'][0]['rel'][0]['m'])) {
                                $subApp->reminder = $item['inv'][$i]['comp'][0]['alarm'][0]['trigger'][0]['rel'][0]['m'];
                            }

                            // Attendees
                            if (isset($item['inv'][$i]['comp'][0]['at'])) {
                                $cAttendees = count($item['inv'][$i]['comp'][0]['at']);
                            } else $cAttendees = 0;
                            if($cAttendees > 0) {
                                $subApp->attendees = array();

                                for($j=0; $j<$cAttendees; $j++) {
                                    $attendee = new SyncAttendee();

                                    if(isset($item['inv'][$i]['comp'][0]['at'][$j]['d'])) {
                                        $attendee->name = $item['inv'][$i]['comp'][0]['at'][$j]['d'];
                                    } else {
                                        $attendee->name = $item['inv'][$i]['comp'][0]['at'][$j]['a'];
                                    }

                                    $attendee->email = $item['inv'][$i]['comp'][0]['at'][$j]['a'];

                                    // Only output attendee status to organizer - but save it as preModAttendee in case it is needed for modifications
                                    $attendee->premodattendeestatus = 0;
                                    if (isset($item['inv'][$i]['comp'][0]['at'][$j]['ptst'])) {
                                        if ($item['inv'][$i]['comp'][0]['at'][$j]['ptst'] == 'NE') {
                                            $attendee->premodattendeestatus = 5;
                                        } else if ($item['inv'][$i]['comp'][0]['at'][$j]['ptst'] == 'AC') {
                                            $attendee->premodattendeestatus = 3;
                                        } else if ($item['inv'][$i]['comp'][0]['at'][$j]['ptst'] == 'TE') {
                                            $attendee->premodattendeestatus = 2;
                                        } else if ($item['inv'][$i]['comp'][0]['at'][$j]['ptst'] == 'DE') {
                                            $attendee->premodattendeestatus = 4;
                                        }
                                    }
/*
                    $myAddr = false;
                    for ($i=0;$i<count($this->_addresses);$i++) {
                        if (strtolower($attendee->email) == strtolower($this->_addresses[$i])) {
                            $myAddr = true;
                            break;
                        }
                    }
                                    if ($isOrganizer || $myAddr) {
*/
                                        $attendee->attendeestatus = $attendee->premodattendeestatus;
/*
                                    }
*/

                                    if (isset($item['inv'][$i]['comp'][0]['at'][$j]['role'])) {
	    								if ($item['inv'][$i]['comp'][0]['at'][$j]['role'] == 'REQ') {
		    								$attendee->attendeetype = 1;
			    						} else if ($item['inv'][$i]['comp'][0]['at'][$j]['role'] == 'OPT') {
				    						$attendee->attendeetype = 2;
					    				}
                                    }
                                    if (isset($item['inv'][$i]['comp'][0]['at'][$j]['cutype'])) {
	    								if ($item['inv'][$i]['comp'][0]['at'][$j]['cutype'] == 'RES') {
		    								$attendee->attendeetype = 3;
			    						}
                                    }

                                    // May need to address organizer again after moving to mainAppt only
                                    if(isset($attendee->name) && isset($attendee->email) && (!isset($subApp->organizeremail) || (isset($subApp->organizeremail) && $attendee->email != $subApp->organizeremail))) {
                                        array_push($subApp->attendees, $attendee);
                                    }
                                }
/*
                                    $attendee = new SyncAttendee();
                                    $attendee->name = $item['inv'][$i]['comp'][0]['or']['d'];
                                    $attendee->email = $item['inv'][$i]['comp'][0]['or']['a'];
                                    $attendee->attendeestatus = 3;
                                    $attendee->attendeetype = 1;
                                    array_push($subApp->attendees, $attendee);
*/
                            }

                            //XPROPS
                            if (isset($item['inv'][$i]['comp'][0]['xprop'])) {
                                $xprops = count($item['inv'][$i]['comp'][0]['xprop']);
                            } else $xprops = 0;

                            //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'xprops count ['.$xprops.'] ' );

                            for($j=0; $j<$xprops; $j++) {

	                            if(isset($item['inv'][$i]['comp'][0]['xprop'][$j]['name']) &&
	                                $item['inv'][$i]['comp'][0]['xprop'][$j]['name'] == 'X-CLIENT-UID') {
//	                                $subApp->uid = base64_decode($item['inv'][$i]['comp'][0]['xprop'][$j]['value']);
	                            } 
                      	    }

                            // Output meetingstatus on main appointment and exceptions too
                            if ((isset($subApp->attendees) && count($subApp->attendees) > 0) || 
                                ($bSupApp && !isset($subApp->attendees) && isset($mainApp->attendees) && count($mainApp->attendees) > 0)) {
                                if ($isOrganizer) {
                                    $subApp->meetingstatus = 1; // Set To 1 For Main Appt if user is the Organizer - leave unset for Exceptions (defaults to match main appt)
                                } else {
                                    $subApp->meetingstatus = 3; // Set To 3 For Main Appt if user is NOT the organizer - leave unset for Exceptions (defaults to match main appt)
                                }
                            } else {
                                $subApp->meetingstatus = 0; // Set To 0 For Main Appt if there are no attendees - It is an Appointment
                            }

                            if ($bSupApp && Request::GetProtocolVersion() < 12) {
                                $subApp->meetingstatus = null; // Meeting Status is not supported on exceptions for AS 2.5
                            }
                        } // END - not Exception deleted

                        if($bSupApp) {
                            array_push($exceptions, $subApp);
							unset($subApp);
                        } else {
                            $mainApp = $subApp;
                        }
                    }

                    // Save original folderid in case this is called from ChangeMessage
                    $mainApp->folderid = $item['l'];

                    // Only output exceptions array if not empty
                    if (count($exceptions) > 0) {
                         $mainApp->exceptions = $exceptions;
                    }
					unset($exceptions);

                    // Finally - Fix missing recurrence fields for appointments created on Web Client as Weekly/Monthly where the recurrence pattern 
                    // is not clicked to customise. Zimbra would omit saving the dayofweek/dayofmonth. But modifications to these appointments would 
                    // have the fields set so the recurrence pattern would never match which in turn would cause exceptions to get stripped 
                    if (isset($mainApp->recurrence) && ($mainApp->recurrence->type == 1) && !isset($mainApp->recurrence->dayofweek)) {
                        $bitmask = 0;
                        $weekday = strtoupper(substr(date( "D", $mainApp->starttime),0,2)) ;
                        switch($weekday) {
                            case 'SU': $bitmask += 1; break;
                            case 'MO': $bitmask += 2; break;
                            case 'TU': $bitmask += 4; break;
                            case 'WE': $bitmask += 8; break;
                            case 'TH': $bitmask += 16; break;
                            case 'FR': $bitmask += 32; break;
                            case 'SA': $bitmask += 64; break;
                        }
                        $mainApp->recurrence->dayofweek = $bitmask;
//debugLog( "Set DayOfWeek=" . $mainApp->recurrence->dayofweek );
                    } elseif (isset($mainApp->recurrence) && ($mainApp->recurrence->type == 3) && !isset($mainApp->recurrence->weekofmonth) && !isset($mainApp->recurrence->dayofweek)) {
                        $dayofmonth = intval( date( "j", $mainApp->starttime) );
                        $mainApp->recurrence->type = 2;
                        $mainApp->recurrence->dayofmonth = $dayofmonth;
//debugLog( "Set Type=" . $mainApp->recurrence->type . " & DayOfMonth=" . $mainApp->recurrence->dayofmonth);
                    }

                    $output = $mainApp;
//debugLog( "Appointment: " . print_r( $output, true), false );

					unset($mainApp);
				}
                break;

			case 'task':

                $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                           <m id="'.$id.'"></m>
                        </GetMsgRequest>';

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);

                if($response) {


                    $array = json_decode($response, true);

 			        unset($response); // We never use it again

                    $item = $array['Body']['GetMsgResponse']['m'][0];
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . 'TASK:' . print_r( $item, true ), false );

                    unset($array);

                    $output = new SyncTask();
                    $output->messageclass = "IPM.Task";

                    /*  Sensitivity Status
                        [class="PUB|PRI|CON"]
                        PUB => 0 // Default
                        PRI => 1
                        CON => 2
                    */

                    $output->subject = $item['inv'][0]['comp'][0]['name'];

                    if(isset($item['inv'][0]['comp'][0]['class']) && $item['inv'][0]['comp'][0]['class'] == "CON") {
                        $output->sensitivity = 2;
                        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'CONFIDENTIAL' );
                    } else if(isset($item['inv'][0]['comp'][0]['class']) && $item['inv'][0]['comp'][0]['class'] == "PRI") {
                        $output->sensitivity = 1;
                        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'PRIVATE' );
                    } else if(isset($item['inv'][0]['comp'][0]['class']) && $item['inv'][0]['comp'][0]['class'] == "PUB") {
                        $output->sensitivity = 0;
                        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'PUBLIC' );
                    } else {
                        $output->sensitivity = 0;
                        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  'UNKNOWN' );
                    }

                    if(isset($item['inv'][0]['comp'][0]['priority']) && $item['inv'][0]['comp'][0]['priority'] == "1") {
                        $output->importance = 2;
                    } else if(isset($item['inv'][0]['comp'][0]['priority']) && $item['inv'][0]['comp'][0]['priority'] == "9") {
                        $output->importance = 0;
                    } else {
                        $output->importance = 1;
                    }

                    if (isset($bodyPrefArray[2]) && isset($item['inv'][0]['comp'][0]['descHtml'][0]['_content'])) {
                        $notes = $item['inv'][0]['comp'][0]['descHtml'][0]['_content'];
                        $output->asbody = new SyncBaseBody();
                        $output->asbody->type = 2;
                        $output->asbody->estimatedDataSize = strlen($notes);
                        if (isset($bodyPrefArray[$output->asbody->type]["TruncationSize"]) && ($output->asbody->estimatedDataSize > $bodyPrefArray[$output->asbody->type]["TruncationSize"])) {
                            $notes = Utils::Utf8_truncate($notes,$bodyPrefArray[$output->asbody->type]["TruncationSize"]);
                            $output->asbody->truncated = 1;
                        } else {
                            $output->asbody->truncated = 0;
                        }
                        if ($this->_baseBodyIsStream) {
                            if (!class_exists('StringStreamWrapper')) {
                                include_once('include/stringstreamwrapper.php');
                            }
                            $output->asbody->data = StringStreamWrapper::Open($notes);
                        } else {
                            $output->asbody->data = $notes;
                        }

                    } elseif (isset($item['inv'][0]['comp'][0]['desc'][0]['_content'])) {
                        $notes = str_replace("\n","\r\n", str_replace("\r","",$item['inv'][0]['comp'][0]['desc'][0]['_content']));
                        if (Request::GetProtocolVersion() >= 12.0) {
                            $output->asbody = new SyncBaseBody();
                            $output->asbody->type = 1;
                            $output->asbody->estimatedDataSize = strlen($notes);
                            if (isset($bodyPrefArray[$output->asbody->type]["TruncationSize"]) && ($output->asbody->estimatedDataSize > $bodyPrefArray[$output->asbody->type]["TruncationSize"])) {
                                $notes = Utils::Utf8_truncate($notes,$bodyPrefArray[$output->asbody->type]["TruncationSize"]);
                                $output->asbody->truncated = 1;
                            } else {
                                $output->asbody->truncated = 0;
                            }
                            if ($this->_baseBodyIsStream) {
                                if (!class_exists('StringStreamWrapper')) {
                                    include_once('include/stringstreamwrapper.php');
                                }
                                $output->asbody->data = StringStreamWrapper::Open($notes);
                            } else {
                                $output->asbody->data = $notes;
                            }
                        } else {
                            $output->body = $notes;
                            $output->bodytruncated = 0;
                        }
                    }

                    $output->datecompleted = "";
                    if(isset($item['inv'][0]['comp'][0]['status']) && $item['inv'][0]['comp'][0]['status'] == "COMP") {
                        $output->complete = 1;
                        // Zimbra doesn't store an actual DateCompleted for tasks - so use the date of last change
                        // Assume the last change was to set it to 100% complete                    
                        if (isset($item['inv'][0]['comp'][0]['d'])) {
                            $output->datecompleted = $this->Date4ActiveSync( $item['inv'][0]['comp'][0]['d'],'UTC' );
                        }

                    } else {
                        $output->complete = 0;
                    }

                    $output->startdate = "";
                    $output->utcstartdate = "";
                    $output->duedate = "";
                    $output->utcduedate = "";

                    if (isset($item['inv'][0]['comp'][0]['s'][0]['d'])) {
                        $startdate = substr( $item['inv'][0]['comp'][0]['s'][0]['d'], 0, 8);
                        $output->utcstartdate = $this->Date4ActiveSync($startdate."T000000", $this->_tz);
//                        $output->utcstartdate = $this->GmtDate4ActiveSync($startdate."T000000");
                        $output->startdate = $this->Date4ActiveSync($startdate."T000000", "UTC");  
                    }

                    if (isset($item['inv'][0]['comp'][0]['e'][0]['d'])) {
                        $duedate = substr( $item['inv'][0]['comp'][0]['e'][0]['d'], 0, 8);
                        if (stripos($this->_ua, "MailforExchange") !== false) {
                            // Nokia sets the end time on tasks to be 23:59:00 on the due date
                            $output->utcduedate = $this->Date4ActiveSync($duedate."T235900", $this->_tz);
                            $output->duedate = $this->Date4ActiveSync($duedate."T235900", "UTC"); 
//                            $output->utcduedate = $this->GmtDate4ActiveSync($duedate."T235900");
                        } else {
                            // non-Nokia phones use 00:00:00
                            $output->utcduedate = $this->Date4ActiveSync($duedate."T000000", $this->_tz);
                            $output->duedate = $this->Date4ActiveSync($duedate."T000000", "UTC"); 
//                            $output->utcduedate = $this->GmtDate4ActiveSync($duedate."T000000");
                        }
                    }

                    // Added Reminder available in Zimbra 7.1 (maybe 7.0 too ?)
                    if (isset($item['inv'][0]['comp'][0]['alarm'][0]['trigger'][0]['abs'][0]['d'])) {
                        $remindertime = $item['inv'][0]['comp'][0]['alarm'][0]['trigger'][0]['abs'][0]['d'];
                        $output->reminderset = "1";
                        $output->remindertime = $this->Date4ActiveSync($remindertime, $this->_tz);
                    } else {
                        $output->reminderset = "";
                        $output->remindertime = ""; 
                    }

                    // Task Has Tags - Sync them as Categories to Client
                    if (isset($item['tn']) && (trim($item['tn']) != "")) {
                        $output->categories = explode( ',', trim($item['tn']) );
                    } elseif (isset($item['t']) && (trim($item['t']) != "")) {
                      $output->categories = $this->TagsToCategories( $item['t'] );
                    }

                    $output->recurrence = "";
                    $output->regenerate = "";
                    $output->deadoccur = "";
                    $output->rtf = "";
				}
                break;

            case 'note':

                $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                           <m id="'.$id.'"></m>
                        </GetMsgRequest>';

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);

                if($response) {

                    $array = json_decode($response, true);

 			        unset($response); // We never use it again

                    $item = $array['Body']['GetMsgResponse']['m'][0];
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . 'NOTE:' . print_r( $item, true ), false );

                    unset($array);

                    $output = new SyncNote();
                    $output->messageclass = "IPM.StickyNote";

                    if (isset($item['inv'][0]['comp'][0]['d'])) {
                        $output->lastmodified = $this->Date4ActiveSync($item['inv'][0]['comp'][0]['d'], "UTC");  
                    }

                    /*  Sensitivity Status
                        [class="PUB|PRI|CON"]
                        PUB => 0 // Default
                        PRI => 1
                        CON => 2
                    */

                    $output->subject = $item['inv'][0]['comp'][0]['name'];

                    if (isset($bodyPrefArray[2]) && isset($item['inv'][0]['comp'][0]['descHtml'][0]['_content'])) {
                        $notes = $item['inv'][0]['comp'][0]['descHtml'][0]['_content'];
                        $output->asbody = new SyncBaseBody();
                        $output->asbody->type = 2;
                        if ($this->_baseBodyIsStream) {
                            if (!class_exists('StringStreamWrapper')) {
                                include_once('include/stringstreamwrapper.php');
                            }
                            $output->asbody->data = StringStreamWrapper::Open($notes);
                        } else {
                            $output->asbody->data = $notes;
                        }

                    } elseif (isset($item['inv'][0]['comp'][0]['desc'][0]['_content'])) {
                        $notes = str_replace("\n","\r\n", str_replace("\r","",$item['inv'][0]['comp'][0]['desc'][0]['_content']));
                        $output->asbody = new SyncBaseBody();
                        $output->asbody->type = 1;
                        if ($this->_baseBodyIsStream) {
                            if (!class_exists('StringStreamWrapper')) {
                                include_once('include/stringstreamwrapper.php');
                            }
                            $output->asbody->data = StringStreamWrapper::Open($notes);
                        } else {
                            $output->asbody->data = $notes;
                        }
                    }

                    // Note Has Tags - Sync them as Categories to Client
                    if (isset($item['tn']) && (trim($item['tn']) != "")) {
                        $output->categories = explode( ',', trim($item['tn']) );
                    } elseif (isset($item['t']) && (trim($item['t']) != "")) {
                        $output->categories = $this->TagsToCategories( $item['t'] );
                    }

				}
                break;
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' . 'END GetMessage');
        return $output;
    }


    /** ReportMemoryUsage
     *
     *   This function is used to output the Current and Session Maximum Memory Usage for a session.
     */
    function ReportMemoryUsage( $marker='N/A' ) {

        if (function_exists("memory_get_peak_usage")) {
            $compPeakRealMemory = memory_get_peak_usage( true );
            $peakRealMemory = number_format($compPeakRealMemory);
            $peakAllocatedMemory = number_format(memory_get_peak_usage( false ));
        } else {
            $compPeakRealMemory = 0;
            $peakRealMemory = "Unavailable";
            $peakAllocatedMemory = "Unavailable";
        }
        if (function_exists("memory_get_usage")) {
            $currentRealMemory = number_format(memory_get_usage( true ));
            $currentAllocatedMemory = number_format(memory_get_usage( false ));
        } else {
            $currentRealMemory = "Unavailable";
            $currentAllocatedMemory = "Unavailable";
        }
        if ($compPeakRealMemory > 10485760) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ReportMemoryUsage(): ' . 'Over10M - MEMORY - REAL Now ('.$currentRealMemory.') Peak ['.$peakRealMemory.'] - ALLOCATED Now ('.$currentAllocatedMemory.') Peak ['.$peakAllocatedMemory.'] - '.$marker);
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ReportMemoryUsage(): ' . '          MEMORY - REAL Now ('.$currentRealMemory.') Peak ['.$peakRealMemory.'] - ALLOCATED Now ('.$currentAllocatedMemory.') Peak ['.$peakAllocatedMemory.'] - '.$marker);
        }
    }
	
    /** LookupV5Timezone
     *
     *   If we are running against a version 5 backend, we need to load a timezone translation table
     */
 
    function LookupV5Timezone( $tzName = "", $v5tzName = "" ) {
    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->LookupV5Timezone(): ' .  'START LookupV5Timezone $tzName=['.$tzName.']; $v5tzName=['.$v5tzName.'] ' );

        $result = false;

        if (!isset($this->_v5timezones)) {
            // Load v5timezone.XML File (if exists)
            $contents = array(); 
            $timezones = array();
            $tz_file = BASE_PATH . 'backend/v5timezone.xml';
            $tz_file_xml = '';

            if (file_exists($tz_file)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->LookupV5Timezone(): ' . 'Loading zimbra 5 compatability file - v5timezone.XML');
                $file = fopen($tz_file,"r");
                while(!feof($file)) {
                    $tz_file_xml = $tz_file_xml . fgets($file);
                }
                $contents = $this->MakeXMLTree($tz_file_xml);
                unset($tz_file_xml);
			
                if (isset($contents['zimbratimezones'][0])) {
                    $count = count($contents['zimbratimezones'][0]['timezone']);

                    for ($i=0;$i<$count;$i++) {
                        if ( isset($contents['zimbratimezones'][0]['timezone'][$i]['id'] ) && isset($contents['zimbratimezones'][0]['timezone'][$i]['v5id']) ) {
                            $timezones[] = array( 'id'=>$contents['zimbratimezones'][0]['timezone'][$i]['id'], 'v5id'=>$contents['zimbratimezones'][0]['timezone'][$i]['v5id'] );
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->LookupV5Timezone(): ' .  'TZ Map ['.$timezones[$i]['id'].'] <=> ['.$timezones[$i]['v5id'].'] ' );
                        }
                    }
                    $this->_v5timezones = array();
                    $this->_v5timezones = $timezones;
                    unset($timezones);
                }
                fclose($file);
                unset($contents);
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->LookupV5Timezone(): ' . 'No v5timezone.XML File Found - Appointment/Task/Meeting Request sync will probably fail');

                return $result;
            }
        }
	
        if (isset($tzName) && ($tzName != "")) {
            $count = count($this->_v5timezones);
            for ($i=0;$i<$count;$i++) {
                if ($this->_v5timezones[$i]['id'] == $tzName) {
                    $result = $this->_v5timezones[$i]['v5id'];
                    break;
                }
            }
        } else if (isset($v5tzName) && ($v5tzName != "")) {
            $count = count($this->_v5timezones);
            for ($i=0;$i<$count;$i++) {
                if ($this->_v5timezones[$i]['v5id'] == $v5tzName) {
                    $result = $this->_v5timezones[$i]['id'];
                    break;
                }
            }
        }
	
    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->LookupV5Timezone(): ' .  'END LookupV5Timezone - $result=['.$result.'] ' );
	return $result;
	
	} // end LookupV5Timezone
	
	
    /** TagsToCategories
	 *   If zimbra item has Tags attached, create an array of Category Names from the Taglist
	 */
	 function TagsToCategories( $taglist ) {
     //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->TagsToCategories(): ' .  'TagList ['.$taglist.'] ' );
	 
        $itemTags = explode(",",$taglist);
        $itemTagCount = count($itemTags);

        $userTagCount = count($this->_usertags);
        $itemTagNames = array();

        for ($i=0;$i<$itemTagCount;$i++) {
          $tagid = $itemTags[$i];
                        
          for ($j=0;$j<$userTagCount;$j++) {
            if ($tagid == $this->_usertags[$j]['id']) {
              $itemTagNames[] = $this->_usertags[$j]['name'];
              break;
            }
          }
        }

        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->TagsToCategories(): ' .  'TagNames ['.print_r( $itemTagNames, true).'] ' );
        return $itemTagNames;
		
	} // end TagsToCategories


    /** GetMpBodyRecursive
    *   Get all parts in the message with specified type and concatenate them together, unless the
    *   Content-Disposition is 'attachment', in which case the text is apparently an attachment
    */
    function GetMpBodyRecursive($folderid, $id, $mimepart, &$textbody, &$htmlbody, &$calendarbody, &$hasBodyTypes, &$attachments, $parent_ctp=false, $parent_cts=false) {

        if (defined('ZIMBRA_DEBUG')) {
            if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'START GetMpBodyRecursive folderid = '.$folderid.'; id = '.$id.'; mimepart = <hidden>; (return)body = <hidden>; parent_ctype = '.$parent_ctp.'/'.$parent_cts );
            }
        }

        if (isset($mimepart['ct'])) {
            $contenttype = explode( '/', $mimepart['ct']);
            // Ensure these are set to lowercase to simplify comparisons. They will be passed recursively to parent_ctp/s as lowercase too.
            $ctp = strtolower($contenttype[0]);
            $cts = strtolower($contenttype[1]);
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'No Content definition mimepart[ct]' );
        }

        if (!isset($ctp)) {
            return;
        }

        if ($ctp == 'text') {
			if ($cts == 'plain') {
				$hasBodyTypes['plain'] = true;
			} else if ($cts == 'html') {
				$hasBodyTypes['html'] = true;
			}
        }

        if (($ctp == 'text') && ($cts == 'calendar')) { 
            $hasBodyTypes['calendar'] = true;
            if ($calendarbody !== false) {
                $calendarbody .= $this->GetRawMessage($id, $mimepart['part']);
            }
        } elseif (($ctp == 'application') && ($cts == 'ms-tnef')) { 
            $hasBodyTypes['ms-tnef'] = true;
        }

/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  '
Part ['.$mimepart['part'].']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Parent P ['. $parent_ctp .']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Parent S ['. $parent_cts .']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Part P ['. $ctp .']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Part S ['. $cts .']' );
if (isset($mimepart['s'])) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Size ['. $mimepart['s'] .']' );
}
*/


        $matchingbodypart = (isset($mimepart['body']) && ($mimepart['body'] == '1') && ($ctp == 'text') && isset($mimepart['content']));  

        $inlineimage = $parent_ctp == "multipart" && 
                       ($parent_cts == "mixed" || $parent_cts == "related") && 
                       $ctp == "image" && 
                       (!isset($mimepart['cd']) || (isset($mimepart['cd']) && (strtolower($mimepart['cd']) != "attachment"))) &&
                       isset($mimepart['s']) && ($mimepart['s'] != "0");


//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Matching Part ? ['.$matchingbodypart.']');
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Inline Text ? ['.$inlinetext .']');
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Inline Image ? ['.$inlineimage .']');

        if (isset($mimepart['ci'])) {
            $myCi = substr(trim($mimepart['ci']),1,-1);
        } else {
            $myCi = $this->NewGUID();
        }

        // is this object a text message, then grab the message body
//        if ($matchingbodypart  || $inlinetext) {
        if ($matchingbodypart) {

            $partText = $mimepart['content'];

            // RUSSIAN ENCODING DETECTION
            if ((defined('ZIMBRA_DETECT_RUSSIAN')) && (ZIMBRA_DETECT_RUSSIAN === true) && (function_exists("charset_x_win"))) {
                $chenc = charset_x_win($partText);
                $outpt = $chenc[0];
//                $outenc = $chenc[1]; NOT NEEDED - OUTPUT WILL ALWAYS BE windows-1251
//                                     SUGGEST RETURN TO THE ORIGINAL a.charset.php THAT DOES NOT SUPPLY 2 OUTPUTS
                $partText = iconv("WINDOWS-1251", "UTF-8//IGNORE//TRANSLIT", $outpt);
            }
            if (function_exists("mb_detect_encoding")) {
                $sourceEncoding = mb_detect_encoding($partText, $this->_mbDetectOrder); // get encoding from body

                if (strtoupper($sourceEncoding) != "UTF-8" ) {
                    $partText = iconv( $sourceEncoding, "UTF-8//IGNORE//TRANSLIT", $partText);
                }
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'MB_DETECT_ENCODING UNAVAILABLE : SourceEncoding not set in headers - assume UTF-8');
            }

            if ($cts == "plain") {
                if ($textbody !== false) {
                    $textbody .= $partText;
                }
                if ($htmlbody !== false) {
                    $partText = str_replace("\n","<br>",$partText);
                    $origEndBodyTagStart = strpos( strtolower($htmlbody), '</body>' );
                    if ($origEndBodyTagStart === false) {
                        // No EndBody tag - just append
                        $htmlbody .= $partText;
                    } else {
                        $htmlToOrigEndBodyStart = substr( $htmlbody, 0, $origEndBodyTagStart );
                        $htmlFromOrigEndBodyStart = substr( $htmlbody, $origEndBodyTagStart );

                        $htmlbody = $htmlToOrigEndBodyStart . "<BR><BR>" . $partText . $htmlFromOrigEndBodyStart;
                    }
                }
            } else if ($cts == "html") {
                $htmlInlineImagesOnly = false; 
                if ($htmlbody !== false) {
                    $origEndBodyTagStart = strpos( strtolower($htmlbody), '</body>' );
                    if ($origEndBodyTagStart === false) {
                        // No EndBody tag - just append
                        $htmlbody .= $partText;
                    } else {
                        $htmlToOrigEndBodyStart = substr( $htmlbody, 0, $origEndBodyTagStart );
                        $htmlFromOrigEndBodyStart = substr( $htmlbody, $origEndBodyTagStart );

                        $htmlbody = $htmlToOrigEndBodyStart . "<BR><BR>" . $partText . $htmlFromOrigEndBodyStart;
                    }
                }
            }

        } else if ($inlineimage) {
            if (isset($mimepart['filename'])) {	
                $attname = $mimepart['filename'];
            } else {
                $attname = "image_" . $myCi . "." . $cts;
				$inlineimage = false;
            }

        }

        // if $attachments is false we ignore the attachments. if it is an array, we collect them
/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'mimepart(cd) ['.$mimepart['cd'].']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'mimepart(ci) ['.$mimepart['ci'].']' );
*/

        if (($attachments !== false) &&
            ((isset($mimepart['cd']) && (strtolower($mimepart['cd']) == "attachment" )) || 
			(($ctp != "multipart") && ($ctp != "text")) || 
			(isset($mimepart['body']) && ($mimepart['body'] == '1') && ($ctp == 'text') && !isset($mimepart['content'])) 
            ))
// Try to simplify - if not text - or if attachment (including text) - then attach it.
// Inline text would have already been handled above.
//            (isset($mimepart['cd']) && (strtolower($mimepart['cd']) == "inline") && ($ctp != "text")) ||
//            (isset($mimepart['ci']) && ($ctp == "image")) ||
//			($ctp == "application")))
             {

            if (defined('MAX_EMBEDDED_SIZE')) {
                $tooBigForInline = (floatval($mimepart['s']) > floatval(MAX_EMBEDDED_SIZE));
            } else {
                $tooBigForInline = false;
            }

            $attachment = new SyncAttachment();

            if (strtolower($mimepart['ct']) == 'message/rfc822') {
                if (isset( $mimepart['filename'] )) {
                    $attname = str_replace(':', '.', $mimepart['filename']);
                } else {
                    $attname = 'Unknown_message-rfc822';
                }
                $attname .= ".eml";
                $attachment->attsize = $mimepart['s'];
                $attachment->attmethod = 5;
/*
            // Test to see if attaching a calendar.ics file would help with Nokia. It actually hangs it ! Commenting Out
            } else if (strtolower($mimepart['ct']) == 'text/calendar') {
                $attname = $mimepart['filename'];
                $attachment->attsize = $mimepart['s'];
                $attachment->attmethod = 1;
*/
            } else {
                if (isset($mimepart['cd'])) {
                    if ((strtolower($mimepart['cd']) == "inline") && ($tooBigForInline == false) && ($inlineimage == false)) {
                        $attachment->attmethod = 6;
                    } else {
                        $attachment->attmethod = 1;
                    }
                } else {
                    if (($ctp == "image") && ($tooBigForInline == false) && ($inlineimage == false)) {
                        $attachment->attmethod = 6;
                    } else {
                        $attachment->attmethod = 1;
                    }
                }

                if ($inlineimage) {
                    if (!$tooBigForInline) { 
                        $attachment->attmethod = 6;
                    } else {
                        $attachment->attmethod = 1;
                    }
                }

                $attachment->attsize = $mimepart['s'];

                $nameEncoding = false;
                if (isset($mimepart['filename'])) {	
                    $attname = $mimepart['filename'];
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name ['.$attname.']');

                    if (($prefix = stripos($attname, '=?')) !== false) { 
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name Mime-Encoded ['.$attname.']');
                        $qPos = stripos($attname, '?q?', $prefix);
                        $bPos = stripos($attname, '?b?', $prefix);
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name qPos ['.$qPos.'], bPos ['.$bPos.']');
                        if (($qPos  !== false) || ($bPos !== false)) {
                            if (($suffix = stripos($attname, '?=', $prefix)) !== false) {
                                $encoding = substr( $attname, 2, ($qPos ? ($qPos - 2) : ($bPos - 2)));
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name Mime-Encoded using encoding ['.$encoding.']');
                                $resp = iconv_mime_decode($attname, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $encoding);
                                $attname = ($resp !== false) ? $resp : $attname;
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name Mime-Decoded as ? ['.$attname.']');
                            }
                        }
                    }
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name ['.$attname.']');
                    // Identify and save the character encoding for the filename
                    if (function_exists("mb_detect_encoding")) {
                        $nameEncoding = mb_detect_encoding($attname, $this->_mbDetectOrder); // get encoding from name text
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name Encoding Identified as ? ['.$nameEncoding.']');
                    } 

                } else {
                    if ($ctp == "image") {
                        $attname = "image_" . $myCi . "." . $cts;
                    } else  if ($ctp == "text") { 
                        if ($cts == "plain") {
                            $attname = "text_" . $myCi . "." . "txt";
                        } else {
                            $attname = $cts . "_" . $myCi . "." . $cts;
                        }
                    } else {
                        $attname = "attachment_" . $myCi . "." . $cts;
                    }
                }
                $attachment->nameencoding = ((!$nameEncoding) ? "UTF-8" : $nameEncoding );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Attachment Name Encoding ['.$attachment->nameencoding.']');
            }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'Too big for inline ['.$tooBigForInline.']' );

            $attachment->displayname = $attname;
            $attachment->attname = $folderid . ":" . $id . ":" . $mimepart['part'];
            if (isset($mimepart['ci'])) {
                $attachment->attoid = substr(trim($mimepart['ci']),1,-1);
            }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMpBodyRecursive(): ' .  'attoid ['.$attachment->attoid.']' );
            $attachment->contenttype = $mimepart['ct'];

            $attachments[] = $attachment;
            unset($attachment);
        } 

        // is this a multipart email and there are multiple parts in this object then also grab the contents
        if ($ctp == "multipart" && 
            isset($mimepart['mp']) && 
            is_array($mimepart['mp'])) 
            {
            // iterate through message parts
            foreach($mimepart['mp'] as $part) {
                $this->GetMpBodyRecursive($folderid, $id, $part, $textbody, $htmlbody, $calendarbody, $hasBodyTypes, $attachments, $ctp, $cts);
            }
            unset( $part );
        }
    } // end GetMpBodyRecursive


    function NewGUID() {
        $guidstr = "";
        for ($i=1;$i <= 16;$i++) {
            $b = (int)rand(0,0xff);
            if ($i == 7) { $b &= 0x0f; $b |= 0x40; } // version 4 (random)
            if ($i == 9) { $b &= 0x3f; $b |= 0x80; } // variant
            $guidstr .= sprintf("%02s", base_convert($b,10,16));
        }
        return $guidstr;
    }


    /** ScrubHtmlText
    *
    * Scrub all markup from HTML text to use as Plain text for email
    */
    function ScrubHtmlText( $scrubbed ) {

        $scrubbed = html_entity_decode($scrubbed,ENT_COMPAT,"UTF-8");  // Convert &nbsp; etc.
        // remove css-style tags
        $scrubbed = preg_replace("/<style.*?<\/style>/is", "", $scrubbed);
        // remove all other html
        $scrubbed = preg_replace("/<span[^>]*>/is","",$scrubbed);
        $scrubbed = preg_replace("/<\/span>/is","",$scrubbed);

        $scrubbed = preg_replace("/<br >/is","<br>",$scrubbed);
        $scrubbed = preg_replace("/<br\/>/is","<br>",$scrubbed);
        $scrubbed = preg_replace("/<\/div>/is","<br>",$scrubbed);
        $scrubbed = preg_replace("/<br>/is","\r\n",$scrubbed);
        $scrubbed = preg_replace("/<hr>/is","_________________________\r\n",$scrubbed);
        $scrubbed = strip_tags($scrubbed);
		
        $scrubbed = preg_replace('/^[\n]2,|^[\t\s]*\n+/m','',$scrubbed);  // Strip out duplicate blank lines left after stripping tags

        return $scrubbed;
    } // End ScrubHtmlText


    /** GetAllBodyRecursive
    *   Get all parts in the message with specified type and concatenate them together, unless the
    *   Content-Disposition is 'attachment', in which case the text is apparently an attachment
    *  
    *   2011-11-14: encoding fixed, comments added by kongregate/dwc <dwckongregate_x_googlemail.com>
    */
    function GetAllBodyRecursive($message, &$textbody, &$htmlbody, &$htmlInlineImagesOnly, &$textashtmlbody, &$hasBodyTypes, &$export_msg, $parent_ctp=false, $parent_cts=false) {

        if (defined('ZIMBRA_DEBUG')) {
            if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'START GetAllBodyRecursive message = <hidden>; (return)body = <hidden>; parent_ctype = '.$parent_ctp.'/'.$parent_cts );
            }
        }
	
        if (!isset($message->ctype_primary)) {
            $ctp = "";
            $cts = "";
            $ct = "";
        } else {
            $ctp = $message->ctype_primary;
            $cts = $message->ctype_secondary;
            $ct = trim($ctp)."/".trim($cts);
        }
/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Parent P ['. $parent_ctp .']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Parent S ['. $parent_cts .']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Part P ['. $ctp .']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Part S ['. $cts .']' );
*/

        $matchingbodypart = (($parent_ctp == "multipart" && ($parent_cts == "alternative" || $parent_cts == "mixed" || $parent_cts == "related")) || ($parent_ctp === false && $parent_cts === false)) && 
                            $ctp == "text" && 
                            (isset($message->body)) &&
                            (!isset($message->disposition) || (isset($message->disposition) && (strtolower($message->disposition) != "attachment")));  


        $inlinetext = $parent_ctp == "multipart" && 
                      $parent_cts != "alternative" && 
                      $ctp == "text" && 
                      isset($message->disposition) && (strtolower($message->disposition) == "inline") && 
                      isset($message->body);

        $inlineimage = $parent_ctp == "multipart" && 
                       ($parent_cts == "mixed" || $parent_cts == "related") && 
                       $ctp == "image" && 
                       isset($message->disposition) && (strtolower($message->disposition) == "inline") && 
                       isset($message->body);

        if (($ctp == 'text') && ($cts == 'calendar')) { 
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'HAS calendar BODY' );
            $hasBodyTypes['calendar'] = true;
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'CTypeParams:' . print_r( $message->ctype_parameters, true ) );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'BODY:'.  $message->body );

        } elseif (($ctp == 'application') && ($cts == 'ms-tnef')) { 
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'HAS ms-tnef BODY' );
            $hasBodyTypes['ms-tnef'] = true;
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'CTypeParams:' . print_r( $message->ctype_parameters, true ) );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'BODY:'.  $message->body );

        } elseif (($ctp == 'application') && ($cts == 'pkcs7-signature')) { 
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'HAS pkcs7-signature BODY' );
            $hasBodyTypes['pkcs7-signature'] = true;
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'CTypeParams:' . print_r( $message->ctype_parameters, true ) );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'BODY:'.  $message->body );

        } elseif (($ctp == "application") && ($cts == "pkcs7-mime")) {
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'HAS pkcs7-mime BODY' );
            $hasBodyTypes['pkcs7-mime'] = true;
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'CTypeParams:' . print_r( $message->ctype_parameters, true ) );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'BODY:'.  $message->body );

        } elseif (($ctp == "multipart") && ($cts == "signed")) {
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'HAS multipart/signed BODY' );
            $hasBodyTypes['signed'] = true;
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'CTypeParams:' . print_r( $message->ctype_parameters, true ) );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'BODY:'.  $message->body );

        } 


        $myCi = $this->NewGUID();

/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Matching Part ? ['.$matchingbodypart.']');
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Inline Text ? ['.$inlinetext .']');
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Inline Image ? ['.$inlineimage .']');
*/
		
        if(isset($message->d_parameters['filename']))
            $attname = $message->d_parameters['filename'];
        else if(isset($message->ctype_parameters['name']))
            $attname = $message->ctype_parameters['name'];
        else if(isset($message->headers['content-description']))
            $attname = $message->headers['content-description'];
        else {
            if (strcasecmp($ctp,"image") == 0) {
                $attname = "image_" . $myCi . "." . $message->ctype_secondary;
            } else  if (strcasecmp($ctp,"text") == 0) {
                if (strcasecmp($message->ctype_secondary,"plain") == 0) {
                    $attname = "text_" . $myCi . "." . "txt";
                } else {
                    $attname = $message->ctype_secondary . "_" . $myCi  . "." . $message->ctype_secondary;
                }
            } else {
                $attname = "attachment_" . $myCi . "." . $message->ctype_secondary;
            }
        }


        // is this object a text message, then grab the message body
        if ($matchingbodypart || ($inlinetext)) {

            // TODO - BuildXMLTree removes all "<" characters (and maybe others) - so retrieve raw message part for now
            // Need to see if BuildXMLTree can be fixed ?
            // If that is possible - then we could use the content field we already have. 

            if ($matchingbodypart || (($inlinetext) && (isset($message->body)))) {

                $partText = $message->body;

                // RUSSIAN ENCODING DETECTION
                if ((defined('ZIMBRA_DETECT_RUSSIAN')) && (ZIMBRA_DETECT_RUSSIAN === true) && (function_exists("charset_x_win"))) {
                    $chenc = charset_x_win($partText);
                    $outpt = $chenc[0];
//                    $outenc = $chenc[1]; NOT NEEDED - OUTPUT WILL ALWAYS BE windows-1251
//                                         SUGGEST RETURN TO THE ORIGINAL a.charset.php THAT DOES NOT SUPPLY 2 OUTPUTS
                    $partText = iconv("WINDOWS-1251", "UTF-8//IGNORE//TRANSLIT", $outpt);
                }
                if (function_exists("mb_detect_encoding")) {
                    $sourceEncoding = mb_detect_encoding($partText, $this->_mbDetectOrder); // get encoding from body
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'Source Encoding ['.$sourceEncoding.']' );

                    if (strtoupper($sourceEncoding) != "UTF-8" ) {
                        $partText = iconv( $sourceEncoding, "UTF-8//IGNORE//TRANSLIT", $partText);
                    }
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAllBodyRecursive(): ' .  'MB_DETECT_ENCODING UNAVAILABLE : SourceEncoding not set in headers - assume UTF-8');
                }

                if ($inlinetext) {
                    $htmlInlineImagesOnly = false;
                    if ($cts == "plain") {
                        $textbody .= $partText;
                        $textashtmlbody .= str_replace("\n","<br>",$partText);
                        $partText = str_replace("\n","<br>",$partText);
                        $origEndBodyTagEnd = strpos( strtolower($htmlbody), '</body>' );
                        if ($origEndBodyTagEnd === false) {
                            // No EndBody tag - just append
                            $htmlbody .= $partText;
                        } else {
                            $origEndBodyTagStart = strpos( strtolower($htmlbody), '</body>');

                            $htmlToOrigEndBodyStart = substr( $htmlbody, 0, $origEndBodyTagStart );
                            $htmlFromOrigEndBodyStart = substr( $htmlbody, $origEndBodyTagStart );

                            $htmlbody = $htmlToOrigEndBodyStart . "<BR><BR>" . $partText . $htmlFromOrigEndBodyStart;
                        }
                    } else if ($cts == "calendar") {
//                        $hasCalendar = true;
                        $hasBodyTypes['calendar'] = true;
                    } else if ($cts == "html") {
                        $origEndBodyTagEnd = strpos( strtolower($htmlbody), '</body>' );
                        if ($origEndBodyTagEnd === false) {
                            // No EndBody tag - just append
                            $htmlbody .= $partText;
                        } else {
                            $origEndBodyTagStart = strpos( strtolower($htmlbody), '</body>');

                            $htmlToOrigEndBodyStart = substr( $htmlbody, 0, $origEndBodyTagStart );
                            $htmlFromOrigEndBodyStart = substr( $htmlbody, $origEndBodyTagStart );

                            $htmlbody = $htmlToOrigEndBodyStart . "<BR><BR>" . $partText . $htmlFromOrigEndBodyStart;
                        }
                        $textashtmlbody .= $partText;
                        $partText = $this->ScrubHtmlText($partText);
                        $textbody .= $partText;
                    }
                } else {
                    if ($cts == "plain") {
                        $hasBodyTypes['plain'] = true;
                        $textbody .= $partText;
                        $textashtmlbody .= str_replace("\n","<br>",$partText);
                    } else if ($cts == "calendar") {
//                        $hasCalendar = true;
                        $hasBodyTypes['calendar'] = true;
                    } else if ($cts == "html") {
                        $hasBodyTypes['html'] = true;
                        $htmlInlineImagesOnly = false; 
                        $origEndBodyTagEnd = strpos( strtolower($htmlbody), '</body>' );
                        if ($origEndBodyTagEnd === false) {
                            // No EndBody tag - just append
                            $htmlbody .= $partText;
                        } else {
                            $origEndBodyTagStart = strpos( strtolower($htmlbody), '</body>');

                            $htmlToOrigEndBodyStart = substr( $htmlbody, 0, $origEndBodyTagStart );
                            $htmlFromOrigEndBodyStart = substr( $htmlbody, $origEndBodyTagStart );

                            $htmlbody = $htmlToOrigEndBodyStart . "<BR><BR>" . $partText . $htmlFromOrigEndBodyStart;
                        }
                    }
                }
            } 

        } else if ($inlineimage) {
            $textbody .= "\r\n<<".$attname.">>\r\n\r\n";
            if (isset($message->headers['content-id'])) {
                $textashtmlbody .= '<BR><img src="cid:'.substr(trim($message->headers['content-id']),1,-1).'"><BR>';
            } else {
//                $textashtmlbody .= '<BR>'.htmlspecialchars('<<'.$attname.'>>').'<BR>';
                $textashtmlbody .= '<BR><img src="cid:'.$myCi.'"><BR>';
            }

        }

//        if ((isset($message->disposition) && strtolower($message->disposition) == 'inline') ||
//            (isset($message->headers['content-id']) && (strcasecmp($ctp,"image") == 0))){
        if ($inlineimage) {
            $export_msg->addHTMLImage(	$message->body,
                                        trim($message->headers['content-type']),
                                        trim($attname),
                                        false,
                                        (isset($message->headers['content-id']) ? substr(trim($message->headers['content-id']),1,-1) : $myCi));

        } else if (isset($message->disposition) && strtolower($message->disposition) == 'attachment') {

            $export_msg->addAttachment(	$message->body,
                                        $ct,
                                        trim($attname),
                                        false,
                                        trim($message->headers['content-transfer-encoding']),
                                        trim($message->disposition),
                                        (isset($message->ctype_parameters['charset']) ? trim($message->ctype_parameters['charset']) : ""));

        } else if ((strcasecmp($ctp,"text") == 0) && (strcasecmp($message->ctype_secondary,"calendar") == 0)) {

            $export_msg->addAttachment(	$message->body,
                                        $ct,
                                        trim($attname),
                                        false,
                                        trim($message->headers['content-transfer-encoding']),
                                        "attachment",
                                        (isset($message->ctype_parameters['charset']) ? trim($message->ctype_parameters['charset']) : ""));
        }

        // is this a multipart email and there are multiple parts in this object then also grab the contents
        if(strcasecmp($ctp,"multipart") == 0 && 
            isset($message->parts) && 
            is_array($message->parts)) 
            {
            // iterate through message parts
            foreach($message->parts as $part) {
                $this->GetAllBodyRecursive($part, $textbody, $htmlbody, $htmlInlineImagesOnly, $textashtmlbody, $hasBodyTypes, $export_msg, $ctp, $cts);
            }
            unset( $part );
        }
    } // end GetAllBodyRecursive


    /*
     * This function is used to search for an replace Euro and other special characters from
     * ISO- that do not map directly to UTF-8
     */
    function cp1252_to_utf8($str) {
        $cp1252_map = array ("\xc2\x80" => "\xe2\x82\xac", 
                             "\xc2\x82" => "\xe2\x80\x9a", 
                             "\xc2\x83" => "\xc6\x92",     
                             "\xc2\x84" => "\xe2\x80\x9e", 
                             "\xc2\x85" => "\xe2\x80\xa6", 
                             "\xc2\x86" => "\xe2\x80\xa0", 
                             "\xc2\x87" => "\xe2\x80\xa1", 
                             "\xc2\x88" => "\xcb\x86",
                             "\xc2\x89" => "\xe2\x80\xb0",
                             "\xc2\x8a" => "\xc5\xa0", 
                             "\xc2\x8b" => "\xe2\x80\xb9",
                             "\xc2\x8c" => "\xc5\x92", 
                             "\xc2\x8e" => "\xc5\xbd", 
                             "\xc2\x91" => "\xe2\x80\x98",
                             "\xc2\x92" => "\xe2\x80\x99",
                             "\xc2\x93" => "\xe2\x80\x9c", 
                             "\xc2\x94" => "\xe2\x80\x9d",
                             "\xc2\x95" => "\xe2\x80\xa2", 
                             "\xc2\x96" => "\xe2\x80\x93", 
                             "\xc2\x97" => "\xe2\x80\x94", 
                             "\xc2\x98" => "\xcb\x9c",
                             "\xc2\x99" => "\xe2\x84\xa2",
                             "\xc2\x9a" => "\xc5\xa1", 
                             "\xc2\x9b" => "\xe2\x80\xba",
                             "\xc2\x9c" => "\xc5\x93", 
                             "\xc2\x9e" => "\xc5\xbe", 
                             "\xc2\x9f" => "\xc5\xb8");
        return strtr ( $str, $cp1252_map );
    } // end cp1252_to_utf8


    /*
     * This function recursivly scan for attachments in multipart emails.
     * It adds the required attachments to the export_msg structure in order
     * to build a complete mime format message.
     */
    function getHTMLAttachmentsRecursive($message,&$export_msg) {

        if(!isset($message->ctype_primary)) {
            $ctp = "";
            $ct = "";
        } else {
            $ctp = $message->ctype_primary;
            $ct = trim($message->ctype_primary)."/".trim($message->ctype_secondary);
        }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->getHTMLAttachmentsRecursive(): ' .  'Headers ['.print_r($message->headers, true).']' );
		
        if(isset($message->d_parameters['filename']))
            $attname = $message->d_parameters['filename'];
        else if(isset($message->ctype_parameters['name']))
            $attname = $message->ctype_parameters['name'];
        else if(isset($message->headers['content-description']))
            $attname = $message->headers['content-description'];
        else {
            if (strcasecmp($ctp,"image") == 0) {
                $attname = "unnamed image." . $message->ctype_secondary;
            } else  if (strcasecmp($ctp,"text") == 0) {
                if (strcasecmp($message->ctype_secondary,"plain") == 0) {
                    $attname = "unnamed text file." . "txt";
                } else {
                    $attname = "unnamed text file." . $message->ctype_secondary;
                }
            } else {
                $attname = "unnamed attachment." . $message->ctype_secondary;
            }
        }


        if ((isset($message->disposition) && strtolower($message->disposition) == 'inline') ||
            (isset($message->headers['content-id']) && (strcasecmp($ctp,"image") == 0))){

            $export_msg->addHTMLImage(	$message->body,
                                        trim($message->headers['content-type']),
                                        trim($attname),
                                        false,
                                        (isset($message->headers['content-id']) ? substr(trim($message->headers['content-id']),1,-1) : ""));

        } else if (isset($message->disposition) && strtolower($message->disposition) == 'attachment') {

            $export_msg->addAttachment(	$message->body,
                                        $ct,
                                        trim($attname),
                                        false,
                                        trim($message->headers['content-transfer-encoding']),
                                        trim($message->disposition),
                                        (isset($message->ctype_parameters['charset']) ? trim($message->ctype_parameters['charset']) : ""));

        } else if ((strcasecmp($ctp,"text") == 0) && (strcasecmp($message->ctype_secondary,"calendar") == 0)) {

            $export_msg->addAttachment(	$message->body,
                                        $ct,
                                        trim($attname),
                                        false,
                                        trim($message->headers['content-transfer-encoding']),
                                        "attachment",
                                        (isset($message->ctype_parameters['charset']) ? trim($message->ctype_parameters['charset']) : ""));
        }

        if(strcasecmp($ctp,"multipart")==0 && isset($message->parts) && is_array($message->parts)) {
           	foreach($message->parts as $part) {
//                $this->ReportMemoryUsage( 'RECURSIVE   Calling GetHTMLAttachmentsRecursive' );
               	$this->getHTMLAttachmentsRecursive($part,$export_msg);
//                $this->ReportMemoryUsage( 'RECURSIVE Back From GetHTMLAttachmentsRecursive' );
           	}
 		}
    }
    
	
    /**
     * GetLibraryDocument
     * Should return document data for the specified document.
     */
    function GetLibraryDocument($docname) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' . "START GetLibraryDocument { attname = '" . $docname . "' }");

        // Replace root folder 'zimbra' with the Account's restURL
        $zimbraPath = explode("\\", $docname);
        $zimbraPath[0] = $this->_accountRestURL;
        $downloadRest = implode("/", $zimbraPath);

            // hexOwnerPathRef contains an actual filename
            // Check if we got a valid path to a folder/a file/or an invalid path
            $searchfile = "";
            $searchFolder = array();
            $searchFolder = $zimbraPath;
            $searchFolder[0] = ''; // to remove zimbra/ZIMBRA/Zimbra... but leave the "/" at the start

            $folderParts = count($zimbraPath);

            // First check if entire remaining path is to a folder - in which case we will list it's contents.
            $searchPath = implode( "/", $searchFolder );

            // Next check if all but the last part point to a folder - in which case we will look for just one file - contained in the final part.
            $searchfile = $searchFolder[$folderParts-1];
            unset($searchFolder[$folderParts-1]);  // to remove the last part (possible filename) 
            $searchPath = implode( "/", $searchFolder );
            if (isset( $this->_documentLibrariesPathToIdIndex[$searchPath])) {
                $searchFolderIndex = $this->_documentLibrariesPathToIdIndex[$searchPath];
                $searchFolderId = $this->_documentLibraries[$searchFolderIndex]['longid'];
                $ownerid = $this->_documentLibraries[$searchFolderIndex]['ownerid'];
            } else {
                throw new StatusException("Zimbra->GetLibraryDocument(): Search contains a bad folder name! ", SYNC_SEARCHSTATUS_STORE_NOTFOUND);
            }

            // we're looking for  one matching file
//            $searchZimbra = str_replace( array( "~", "'", "!", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "?", "/", "{", "}", "[", "]", ";", ":", '"'), " ", $searchfile );
            $searchZimbra = $searchfile;

            $soap = '<SearchRequest xmlns="urn:zimbraMail"  types="document" sortBy="subjAsc" >
                      <query> inid:"'.$searchFolderId.'" "'.$searchZimbra.'" ';
            $soap .= '</query>
                     </SearchRequest>';
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' .  'SOAP ['.$soap.']' );

            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
			
            if($response) {
                $array = json_decode($response, true);
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' .  'DOCUMENT:' . print_r( $array, true ), false );

                unset($response);

                $items = $array['Body']['SearchResponse']; 
                $doc = false;

                if (!isset($items['doc'])) {
                    throw new StatusException("Zimbra->GetLibraryDocument(): Search contains a bad file name! ", SYNC_SEARCHSTATUS_STORE_NOTFOUND);
                } else {
                    for ($i=0;$i<count($items['doc']);$i++) {
                        if ($items['doc'][$i]['name'] == $searchfile) {
                            $doc = $items['doc'][$i];
                        }
                    }
                }

                $ownerPath = $this->_accountName . $searchPath ;
                $filename = $doc['name'];

                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' .  'FileName ['.$filename.']' );

            } else {
                throw new StatusException("Zimbra->GetLibraryDocument(): Search SOAP command failed! ", SYNC_SEARCHSTATUS_STORE_SERVERERROR);
            }



        $document = new SyncItemOperationsAttachment();

        $url = str_replace( " ", "%20", $downloadRest  );

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' .  'URL ['.$url.']' );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
        curl_setopt($ch, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN=' . $this->_authtoken);
        $http_header = array();
        $http_header[] = 'X-Forwarded-For: ' . $this->_xFwdForForMailboxLog;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        $fileContents = curl_exec($ch);
        curl_close($ch);

        if (isset($doc["ct"])) {
            if (strpos( $doc['ct'], 'x-zimbra-doc') !== false) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' .  'Overriding zimbra Doc with text/html' );
                $document->contenttype = 'text/html';
                $htmlPos = stripos( $fileContents, '<html' );
//                ZLog::Write(LOGLEVEL_DEBUG, 'htmlPos ['.$htmlPos.'] - FileContents ['.$fileContents.']' );
                if (($htmlPos !== false) and ($htmlPos > 0 )) {
                    $fileContents = substr( $fileContents, $htmlPos );
                }
                $fileContents .= chr(13) . chr(10) . chr(13) . chr(10);  // Add 2 additional lines of nothing to stop StringStreamWrapper from dropping part of closing tag
            } else {
                $document->contenttype = $doc['ct'];
            }
        } else {
            $document->contenttype = "application/octet-stream";
        }

//        ZLog::Write(LOGLEVEL_DEBUG,  'FileContents ['.$fileContents.']', false );

        if (!class_exists('StringStreamWrapper')) {
            include_once('include/stringstreamwrapper.php');
        }
        $document->data = StringStreamWrapper::Open($fileContents);

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetLibraryDocument(): ' . "END GetLibraryDocument { ". $document->contenttype ." }");
        return $document;

    } // end GetLibraryDocument


    /**
     * GetAttachmentData
     * Should return attachment data for the specified attachment. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment. So, you should
     * encode any information you need to find the attachment in that 'attname' property.
     */
    function GetAttachmentData($attname) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAttachmentData(): ' . "START GetAttachmentData { attname = '" . $attname . "' }");

        $attname = hex2bin( $attname );
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAttachmentData: ' .  'MIME ID: '. $attname );

        if (isset($this->_attachmentsBlocked) && $this->ToBool($this->_attachmentsBlocked)) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->GetAttachmentData(): ' . 'ZIMBRA configuration is blocking the download of attachments (including MIME messages to iOS/Outlook) - Check Configure->Global Settings->Attachments->Attachments cannot be viewed regardless of COS -or- Configure->Class of service->[Class name]->Advanced->Attachment settings->Disable attachment viewing from web mail ui ' );
        }


        /* Fix added to identify when the Attachment belongs to an email in a shared mailbox. 
           $id will be of the form 62635bda-ab05-42b2-a89b-9f12fc5de704:2048 - so we need to check for the extra colon 
           Additional fix added to identify when the Attachment belongs to an email in a sub folder of a shared mailbox. 
           $folderid will be of the form 62635bda-ab05-42b2-a89b-9f12fc5de704:2048 - so we need to check for an additional extra colon
        */
        $colons = substr_count( $attname, ":" );
        if ($colons == 2) { // Local folder
            list($folderid, $id, $part) = explode(":", $attname);
        } elseif ($colons == 3) { // Shared folder - top level
            list($folderid, $remoteUser, $remoteId, $part) = explode(":", $attname);
            $id = $remoteUser .':'. $remoteId;
        } elseif ($colons == 4) { // Shared folder - sub-folder
            list($folderUser, $folderid, $remoteUser, $remoteId, $part) = explode(":", $attname);
            $id = $remoteUser .':'. $remoteId;
        } else {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->GetAttachmentData(): ' . "END GetAttachmentData - BAD MIME ID: " . $attname  . " { false }");
            return false;
        }

        // We have enough information to get the data - but need to Get the message from zimbra to retrieve the Content type
		$parts = explode(".", $part);

        $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                    <m id="'.$id.'" html="0" neuter="0" ></m>
                </GetMsgRequest>';
        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {
            $array = json_decode($response, true);
			unset($response); // We never use it again
            $msg = $array['Body']['GetMsgResponse']['m'][0]['mp'][0];
            unset($array);

            foreach ($parts as $index) {
                $index -= 1;  // Parts are indexed 0 offset in the XML tree, but 1 offset in the attname
                $msg = $msg['mp'][$index];
            }
            
       		$attachment = new SyncItemOperationsAttachment();

            $stats = $this->GetRawMessageStats( $id, $part );
			
            $attachment->data = ZimbraHttpStreamWrapper::Open($this->_authtoken, $this->_publicURL, $id, $part, $stats['download_content_length'], $this->_sslVerifyPeer, $this->_sslVerifyHost);

		    $attachment->contenttype = trim($msg['ct']);
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAttachmentData: ' . "END GetAttachmentData { ContentType = ". $attachment->contenttype ."; length = ". $stats['download_content_length'] ." }");
            return $attachment;

        } else {

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetAttachmentData: ' . "END GetAttachmentData { false }");
            return false;
        }
    } // end GetAttachmentData




    /**
     * ItemOperationsGetAttachmentData
     * Should return attachment data for the specified attachment. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment. So, you should
     * encode any information you need to find the attachment in that 'attname' property.
     */
    function ItemOperationsGetAttachmentData($attname) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsGetAttachmentData(): ' . "START ItemOperationsGetAttachmentData { attname = '" . $attname . "' }");

        $attname = hex2bin( $attname );
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsGetAttachmentData(): ' .  'MIME ID: '. $attname );

        if (isset($this->_attachmentsBlocked) && $this->ToBool($this->_attachmentsBlocked)) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->ItemOperationsGetAttachmentData(): ' . 'ZIMBRA configuration is blocking the download of attachments (including MIME messages to iOS/Outlook) - Check Configure->Global Settings->Attachments->Attachments cannot be viewed regardless of COS -or- Configure->Class of service->[Class name]->Advanced->Attachment settings->Disable attachment viewing from web mail ui ' );
        }

        /* Fix added to identify when the Attachment belongs to an email in a shared mailbox. 
           $id will be of the form 62635bda-ab05-42b2-a89b-9f12fc5de704:2048 - so we need to check for the extra colon
           Additional fix added to identify when the Attachment belongs to an email in a sub folder of a shared mailbox. 
           $folderid will be of the form 62635bda-ab05-42b2-a89b-9f12fc5de704:2048 - so we need to check for an additional extra colon
        */
        $colons = substr_count( $attname, ":" );
        if ($colons == 2) { // Local folder
            list($folderid, $id, $part) = explode(":", $attname);
        } elseif ($colons == 3) { // Shared folder - top level
            list($folderid, $remoteUser, $remoteId, $part) = explode(":", $attname);
            $id = $remoteUser .':'. $remoteId;
        } elseif ($colons == 4) { // Shared folder - sub-folder
            list($folderUser, $folderid, $remoteUser, $remoteId, $part) = explode(":", $attname);
            $id = $remoteUser .':'. $remoteId;
        } else {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->ItemOperationsGetAttachmentData(): ' . "END ItemOperationsGetAttachmentData - BAD MIME ID: " . $attname  . " { false }");
            return false;
        }

        // We have enough information to get the data - but need to Get the message from zimbra to retrieve the Content type
		$parts = explode(".", $part);

        $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                    <m id="'.$id.'" html="0" neuter="0" ></m>
                </GetMsgRequest>';
        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {
            $array = json_decode($response, true);

			unset($response); // We never use it again

            $msg = $array['Body']['GetMsgResponse']['m'][0]['mp'][0];
            unset($array);

            foreach ($parts as $index) {
                $index -= 1;  // Parts are indexed 0 offset in the XML tree, but 1 offset in the attname
                $msg = $msg['mp'][$index];
            }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsGetAttachmentData(): ' .  'MSG ['.print_r($msg, true).']' );

            if (!class_exists('StringStreamWrapper')) {
                include_once('include/stringstreamwrapper.php');
            }

       		$attachment = new SyncItemOperationsAttachment();

            $data =  $this->GetRawMessage($id, $part);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsGetAttachmentData(): ' .  'Data ['.$data.']' );

            $attachment->data = StringStreamWrapper::Open($data);
//           $attachment->data = StringStreamWrapper::Open($this->GetRawMessage($id, $part));
//            $attachment->attsize = strlen($attachment->_data);
		    $attachment->contenttype = trim($msg['ct']);
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsGetAttachmentData(): ' . "END ItemOperationsGetAttachmentData { ". $attachment->contenttype ." }");
            $this->ReportMemoryUsage( 'END ItemOperationsGetAttachmentData' );
            return $attachment;

        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsGetAttachmentData(): ' . "END ItemOperationsGetAttachmentData { false }");
            return false;
        }
    } // end ItemOperationsGetAttachmentData


    /**
     * ItemOperationsFetchMailbox
     * Used to retrieve the complete Message for an item returned by a Mailbox Search
     */
    function ItemOperationsFetchMailbox($id, $searchbodypreference=false) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsFetchMailbox(): ' . "START ItemOperationsFetchMailbox { id = '" . $id . "'; searchbodypreference = '".$searchbodypreference."' }");

        // We have enough information to get the item - but need to Get the message from zimbra to retrieve the folder Id for GetMessage
        $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                    <m id="'.$id.'" html="0" neuter="0" ></m>
                </GetMsgRequest>';
        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {
            $array = json_decode($response, true);

			unset($response); // We never use it again

            $msg = $array['Body']['GetMsgResponse']['m'][0];
            unset($array);

            $index = $this->GetFolderIndexZimbraID($msg['l']);
            $folderid = $this->_folders[$index]->devid;
        }

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsFetchMailbox(): ' .  'SearchBodyPreference ['.print_r($searchbodypreference, true).']' );

        $msg = $this->GetMessage($folderid, $id, $searchbodypreference);

        if ($msg) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsFetchMailbox(): ' . "END ItemOperationsFetchMailbox { true }");
            return $msg;
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ItemOperationsFetchMailbox(): ' . "END ItemOperationsFetchMailbox { false }");
            return false;
        }
    } // end ItemOperationsFetchMailbox


    /**
     * EmptyFolder
     * Normally used to clear server mailbox space by deleting the contents of the Trash Folder
     * but can work on any folder obviously.
     */
    function EmptyFolder($folderid, $deletesubfolders=false) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->EmptyFolder(): ' . "START EmptyFolder { folderid = '" . $folderid . "'; deletesubfolders = '".$deletesubfolders."' }");

        if ($this->_localCache) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->EmptyFolder(): ' .  'EmptyFolder - CLEARING CACHE for folder ['.$folderid.']'  );
            unset($this->_cachedMessageLists[$folderid]);
            $this->_cachedMessageLists['changed'] = true;
        }

        $recursive = ($deletesubfolders ? 1 : 0);
        $index = $this->GetFolderIndex($folderid);
        $zimbraFolderId = $this->_folders[$index]->id;

        $soap ='<FolderActionRequest xmlns="urn:zimbraMail">
                    <action op="empty" id="'.$zimbraFolderId.'" recursive="'.$recursive.'" />
                </FolderActionRequest>';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);

        if($response) {
            $array = json_decode($response, true);

			unset($response); // We never use it again

            $action = $array['Body']['FolderActionResponse']['action'];
            unset($array);

        }
        if (isset($action['op']) && ($action['op'] == 'empty')) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->EmptyFolder(): ' . "END EmptyFolder { true }");
            return true;
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->EmptyFolder(): ' . "END EmptyFolder { false }");
            return false;
        }
    } // end EmptyFolder


    /** SetReadFlag
     *   This should change the 'read' flag of a message on disk. The $flags
     *   parameter can only be '1' (read) or '0' (unread). After a call to
     *   SetReadFlag(), GetMessageList() should return the message with the
     *   new 'flags' but should not modify the 'mod' parameter. If you do
     *   change 'mod', simply setting the message to 'read' on the PDA will trigger
     *   a full resync of the item from the server
     */
    public function SetReadFlag($folderid, $id, $flags, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetReadFlag(): ' . 'START SetReadFlag { folderid = ' . $folderid . '; id = ' . $id . '; flags = ' . $flags . ' [Read-1 or Unread-0] }');


        if ($this->_localCache) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetReadFlag(): ' .  'SetReadFlag - CLEARING CACHE for folder ['.$folderid.']'  );
            unset($this->_cachedMessageLists[$folderid]);
            $this->_cachedMessageLists['changed'] = true;
        }

        // check if the message is in the current syncinterval
//        if (!$this->isZimbraObjectInSyncInterval($folderid, $id, $contentParameters))
//            throw new StatusException(sprintf("Zimbra->DeleteMessage('%s'): Message is outside the sync interval and so far not deleted.", $id), SYNC_STATUS_OBJECTNOTFOUND);

        $index = $this->GetFolderIndex($folderid);
        $view = $this->_folders[$index]->view;

        if ($view == 'message') {
            if ($flags == 0) {
                // set as "Unseen" (unread)
                $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="!read"/>
                         </MsgActionRequest>';
            } else {
                // set as "Seen" (read)
                $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="read"/>
                         </MsgActionRequest>';
            }
            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);


            if($response) {
                $array = json_decode($response, true);
                unset($response); // We never use it again

                $action = $array['Body']['MsgActionResponse']['action'];
                unset($array);
            }
            if (isset($action['op']) && (($action['op'] == 'read') || ($action['op'] == '!read'))) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetReadFlag(): ' . "END SetReadFlag { true }");
                return true;
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetReadFlag(): ' . "END SetReadFlag { false }");
                return false;
            }
        } else {
            return false;
        }
    } // end SetReadFlag


    /** ChangeMessage
     *   A item has been changed or created on the phone
     */
    public function ChangeMessage($folderid, $id, $input, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'START ChangeMessage { folderid = ' . $folderid . '; id = ' . $id . '; input = SyncObject }');

        // check if the message is in the current syncinterval
        if (($id != "") && (!$this->isZimbraObjectInSyncInterval($folderid, $id, $contentParameters)))
            throw new StatusException(sprintf("Zimbra->DeleteMessage('%s'): Message is outside the sync interval and so far not deleted.", $id), SYNC_STATUS_OBJECTNOTFOUND);


        // When Zimbra moves an item from a folder owned by one UserID to a folder owned by another UserID
        // it assigns the item a new ID in the recipients mailbox. There is no way to return this updated ID
        // to this function - so if we allow the move to proceed, we end up with an incorrect item ID in the 
        // recipient folder. There is also no way to easily tell the client to refresh both folders if they 
        // are not both Ping'ed folders. Any attempt to perform a further action on the moved item will
        // generate a SOAP Error. SO, THE SAFEST APPROACH IS TO PREVENT MOVING ITEMS BETWEEN ACCOUNTS
        // All linked folders have an id of the form [Folder Type][Owner Identifier]-[Owner's Folder ID]
        // For example, FL0-2. All folders with no '-' in the folderId are owned by the synching user.


        if (strpos($folderid, '-') === false) {
            $folderOwner = 'ME';
        } else {
            $folderOwnerId = explode( '-', $folderid );
            $folderOwner = $folderOwnerId[0];
        }

        // Note: Check for '-' failing also caters for a new item where the id will be empty
        if (strpos($id, '-') === false) {
            $idOwner = 'ME';
        } else {
            $idOwnerId = explode( '-', $id );
            $idOwner = $idOwnerId[0];
        }

        // Only attempt to import TAGS/Categiries if the folder is owned by the user
        $inputtags = "";
        $inputattribute = "";
        if (isset($input->categories) and is_array($input->categories)) {
            if (('ME' == $folderOwner) && ('ME' == $idOwner)) {
                $inputTagIds = $this->CategoriesToTags($input->categories);
				
                $inputtags = implode( ",",$inputTagIds);
                $inputattribute = ' t="'.$inputtags.'" ';

            } else {
                ZLog::Write(LOGLEVEL_INFO, 'Zimbra->ChangeMessage(): ' . 'Shared folder - Stripping client supplied Categories from input item to prevent the update failing!' );
            }
        }

        if ($folderOwner != $idOwner) {
/*
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage { PREVENTED FROM MOVING ITEMS BETWEEN ACCOUNTS }');
            $clearCacheList[] = $this->changesSinkFolders[$mods[$i]['id']];
            $clearCacheList = array();
            $this->ClearCache( $clearCacheList );
            throw new StatusException("Zimbra->ChangeMessage(): PREVENTED FROM MOVING ITEMS BETWEEN ACCOUNTS ", SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR);
            return false;
*/
        }

        if ($this->_localCache) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'ChangeMessage - CLEARING CACHE for folder ['.$folderid.']'  );
            unset($this->_cachedMessageLists[$folderid]);
            $this->_cachedMessageLists['changed'] = true;
        }


        $index = $this->GetFolderIndex($folderid);
        $zimbraFolderId = $this->_folders[$index]->id;
        $view = $this->_folders[$index]->view;
        $folderpath = $this->_folders[$index]->path;
        if (isset($this->_folders[$index]->owner)) {
            $zimbraFolderOwner = $this->_folders[$index]->owner;
        } else {
            $zimbraFolderOwner = '';
        }

        switch ($view) {
            case 'message':
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'ChangeMessage CALLED for an EMAIL ! - updating "flag" or "categories"' );

/*
                if (isset($input->categories) and is_array($input->categories)) {
                    $msgTagIds = $this->CategoriesToTags($input->categories);
				
                    $msgtags = implode( ",",$msgTagIds);
                    $tagattribute = ' t="'.$msgtags.'" ';

                } else {
                    $msgtags = "";
                    $tagattribute = "";
                }
*/

                $stat = $this->StatMessage( $this->_folders[$index]->devid, $id );
//ZLog::Write(LOGLEVEL_DEBUG, 'Stat ['.print_r( $stat, true ) .']' );
                if (isset($input->flag->flagstatus) && ($input->flag->flagstatus == 2)) {
//ZLog::Write(LOGLEVEL_DEBUG, 'Setting Flag' );
                    $result = $this->SetMessageFlag($this->_folders[$index]->devid, $id, $input->flag->flagstatus);
                } elseif (isset($input->flag->flagstatus) && ($input->flag->flagstatus == 1)) {
//ZLog::Write(LOGLEVEL_DEBUG, 'Clearing Flag' );
                    $result = $this->SetMessageFlag($this->_folders[$index]->devid, $id, 0);
                } else {
//ZLog::Write(LOGLEVEL_DEBUG, 'UnSetting Flag' );
                    $result = $this->SetMessageFlag($this->_folders[$index]->devid, $id, 0);
                }

                // Modifying existing message - Cannot apply TAGS to shared items - so don't even try
//                if (strrpos($id,':') === false) {
                if ('ME' == $idOwner) {

                    $soap ='<ItemActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="update" t="'.$inputtags.'" />
                            </ItemActionRequest> ';
                    $returnJSON = true;
                    $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                    if($actionResponse) {
                        $actionArray = json_decode($actionResponse, true);
                        if (!isset($actionArray['Body']['ItemActionResponse']['action']['id']) or !isset($actionArray['Body']['ItemActionResponse']['action']['op'])) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage MESSAGE TAG update failed');
                        }
                    }
                    unset($actionResponse);
                }

                if ($result) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage MESSAGE (flag/categories) { true }');
                    return $this->StatMessage($this->_folders[$index]->devid, $id);
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage MESSAGE (flag/categories) { false }');
                    return false;
                }

                break;

            case 'contact':

/*
$supportedFields = ZPush::GetDeviceManager()->GetSupportedFields($folderid);
ZLog::Write(LOGLEVEL_DEBUG, 'Supported Fields: '. print_r( $supportedFields, true ) );
*/
                // TODO Does this have something to do with virtual contacts???
                //if(isset($input->categories)){
                //    $fid = $this->GetFolderID($input->categories[0]);             FYI - GetFolderID Removed

                //    // folder exists
                //    if($fid != 0) {
                //        $foldername = $input->categories[0];
                //        $zimbraFolderId = $fid;

                //        if($id <> '') {
                //            $this->MoveMessage($foldername,$id,$fid);
                //        }
                //    }
                //}

//                if (isset($input->categories) and (trim($input->categories) != "")) {
/*
                if (isset($input->categories) and is_array($input->categories)) {
                    $contactTagIds = $this->CategoriesToTags($input->categories);
				
                    $contacttags = implode( ",",$contactTagIds);
                    $tagattribute = ' t="'.$contacttags.'" ';

                } else {
                    $contacttags = "";
                    $tagattribute = "";
                }
*/

                if($id == '') {
                    $soap = '<CreateContactRequest xmlns="urn:zimbraMail" verbose="0"><cn l="'.$zimbraFolderId.'" '.$inputattribute.' >';
                } else {
                    $soap = '<ModifyContactRequest xmlns="urn:zimbraMail" verbose="0"><cn id="'.$id.'" >';
                }

                foreach ($this->_contactMapping as $k => $v) {
                    if ($k <> '' && $v <> '') {

                        // ActiveSync to Zimbra
                        if (strrpos($v,',') === false) {
                            if($k == 'birthday' || $k == 'anniversary') {
                                // Some older android clients have issues with birthday sync causing constant loops - config setting allows for disabling it
                                if (($k == 'birthday') && ($this->_disableBirthdaySync)) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMessage(): ' .  "ZIMBRA_DISABLE_BIRTHDAY_SYNC set to true - SKIP BIRTHDAY" );
                                } elseif (isset($input->$k)) {
									$inDate = $this->Date4Zimbra($input->$k, "UTC");
                                    if (substr($inDate,9,6) != "000000") {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Birthday/Anniversary received from device is NOT in UTC - try Local Timezone' );
                                        // This does not appear to be a UTC formatted date - so convert based on best guess timezone
                                        $inDate = $this->Date4Zimbra($input->$k, $this->_tz);
                                        if (substr($inDate,9,6) != "000000") {
                                            // This does not appear to be a Current TimeZone formatted date - Accept it and Log a warning
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'Birthday/Anniversary received from device is NOT in Local Timezone either - Converted as Local Timezone so day may shift !' );
                                        } else {
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'Birthday/Anniversary received from device APPEARS TO BE in Local Timezone either - Converted as Local Timezone !' );
                                        }
                                    } else {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'Birthday/Anniversary received from device in UTC - Converted as UTC !' );
                                    }
                                    $inDate = substr($inDate,0,4).'-'.substr($inDate,4,2).'-'.substr($inDate,6,2);
                                    $soap .= '<a n="'.$v.'">'.$inDate.'</a>';
                                }
                            } else if ($k == 'picture' && defined('ZIMBRA_SYNC_CONTACT_PICTURES') && ZIMBRA_SYNC_CONTACT_PICTURES == true && isset($input->$k) && strlen($input->$k) > 0  ) {
                                    $server_token = $this->UploadToZimbra($input->$k);
                                    $soap .= '<a n="'.$v.'" aid="'.$server_token.'" />';
                            } else if($k == 'children') {

                                if(isset($input->$k)){
									$children = implode(",",$input->$k);

									$soap .= '<a n="'.$v.'">'.htmlspecialchars($children).'</a>';
                                } else {
                                    $soap .= '<a n="'.$v.'"/>';
                                }
							
                            } else if($k == 'body') {

								// Normally we would expect the 'body' field to be populated on the incoming contact if there any notes
								// However, if there are non-standard characters contained in the notes on the device, it seems that 
								// the body field is blank, and an additional field 'rtf' (an rtf encoded notes field) is sent across
								// instead. We will look for a 'body' field first and only if we don't find it look for an 'rtf' field


                                $notes = "";
                                $notesType = 1;
                                if ((Request::GetProtocolVersion() >= 12)) {
                                    if (isset($input->asbody->data)) {
                        				if (is_resource($input->asbody->data)) {
                                            $notes = stream_get_contents($input->asbody->data);
                                            fclose($input->asbody->data);
                                            $notesType = $input->asbody->type;
                                        } else {
                                            $notes = $input->asbody->data;
                                            $notesType = $input->asbody->type;
                                        }
                                    }
                               } else if ((Request::GetProtocolVersion() < 12) && isset($input->body) && ($input->body != "")) {
                                    $notes = $input->body;
                                } else if(isset($input->rtf)) {
                                    if (class_exists('rtf', false)) {
                                        // start decode RTF if present
                                        $rtf_body = new rtf ();
                                        $rtf_body->loadrtf(base64_decode($input->rtf));
                                        $rtf_body->output("ascii");
                                        $rtf_body->parse();
                                        $notes = w2ui( $rtf_body->out );
                                        unset( $rtf_body );
                                    } else {
                                        $notes = "Missing include file z_RTF.php needed to Decode Notes";
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'RTF field could not be handled - '.$notes.' - See notes in zimbra.php' );
                                    }
                                }

                                if ($notes <> "") {  // Notes changed
                                    //plain text body unless type = 2 passed in.
                                    if ($notesType == 1) {                    
                                                   $soap .= '<a n="'.$v.'">'.htmlspecialchars($notes).'</a>';
                                    } else {
                                                   $soap .= '<a n="'.$v.'">'.htmlspecialchars($notes).'</a>';
                                    }
                                } else {
                                    if (isset($originalPlainNotes)) {
                                                   $soap .= '<a n="'.$v.'">'.$originalPlainNotes.'</a>';
                                    } elseif (isset($originalHtmlNotes)) {
                                                   $soap .= '<a n="'.$v.'">'.$originalHtmlNotes.'</a>';
                                    }
                                }
                            } else if (($k == 'email1address') || ($k == 'email2address') || ($k == 'email3address')) {

								// Android sends the Name along with the email address. Use the RFC822 parser to strip it
                                if(isset($input->$k)){
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Email Address ['.$input->$k.']' );
                                    $parser = new Mail_RFC822();
                                    $emailAddr = $this->parseAddr($parser->parseAddressList($input->$k));
					                unset($parser);
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Email Address ['.$emailAddr.']' );
                                    $soap .= '<a n="'.$v.'">'.htmlspecialchars($emailAddr).'</a>';
                                } else {
                                    $soap .= '<a n="'.$v.'"/>';
                                }
                            } else {
                                if(isset($input->$k)){
                                    $soap .= '<a n="'.$v.'">'.htmlspecialchars($input->$k).'</a>';
                                } else {
                                    $soap .= '<a n="'.$v.'"/>';
                                }
                            }

                        } else {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'v has a comma' );
						// TODO - I may need to revisit this as I'm not sure this is working as intended.  May need to record somewhere
                            //        which field in zimbra is mapped to the ActiveSync field.
                            if(isset($input->$k)){
                                $v_vals = explode(",",$v);
                                foreach ($v_vals as $v_val) {
                                    $soap .= '<a n="'.$v_val.'">'.$input->$k.'</a>';
								}
                                unset( $v_vals );
                                unset( $v_val );
                            }
                        }
                    }
                }
                unset( $k );
                unset( $v );

                if($id == '') {
                    $soap .= '</cn></CreateContactRequest>';
                } else {
                    $soap .= '</cn></ModifyContactRequest>';
                }

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);
                if($response) {
                    if ($id == '') {
                        $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CreateContact:' . print_r( $array, true ) );
                        unset($response);
                        $id = $array['Body']['CreateContactResponse']['cn'][0]['id'];
						unset($array);
                    } else {
                        // Modifying existing contact - Cannot apply TAGS to shared items - so don't even try
//                        if (strrpos($id,':') === false) {
                        if ('ME' == $idOwner) {
                            $soap ='<ContactActionRequest xmlns="urn:zimbraMail">
                                      <action id="'.$id.'" op="update" t="'.$inputtags.'" />
                                    </ContactActionRequest> ';
                            $returnJSON = true;
                            $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                            if($actionResponse) {
                                $actionArray = json_decode($actionResponse, true);
                                if (!isset($actionArray['Body']['ContactActionResponse']['action']['id']) or !isset($actionArray['Body']['ContactActionResponse']['action']['op'])) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage CONTACT TAG update failed');
                                }
                            }
                            unset($actionResponse);
                        }
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage CONTACT { true }');
                    return $this->StatMessage($this->_folders[$index]->devid, $id);
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage CONTACT { false }');
                    return false;
                }
                break;

            case 'appointment':

//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: ' . print_r( $input, true ), false );

                if (!$contentParameters) {
                    // For z-push 2 releases 2.0.7 and earlier Content Parameters are not being set before the call to ChangeMessage
                    // Set it here to prevent GetMessage from crashing with the following message
                    // PHP Fatal error:  Call to a member function GetContentClass() on a non-object
                    $contentParameters = new ContentParameters();
                    $contentParameters->SetContentClass('appointment');
//                    $contentParameters->SetFilterType();
//                    $contentParameters->SetRTFTruncation();
//                    $contentParameters->SetMimeSupport();
//                    $contentParameters->SetMimeTruncation();
                    $bodyPreference = new BodyPreference();
                    $bodyPrefArray = array();
                    $bodyPrefArray[1] = $bodyPreference;

                    $contentParameters->bodypref = $bodyPrefArray;
                }


//                if (isset($input->categories) and (trim($input->categories) != "")) {
/*
                if (isset($input->categories) and is_array($input->categories)) {
                    $apptTagIds = $this->CategoriesToTags($input->categories);
				
                    $appttags = implode( ",",$apptTagIds);
                    $tagattribute = ' t="'.$appttags.'" ';
                } else {
                    $appttags = "";
                    $tagattribute = "";
                }
*/

                $zimbraStrippedExceptions = false;
                if($id == '') {

                    if ($this->_serverInviteReply) {
                        $soap = '<CreateAppointmentRequest xmlns="urn:zimbraMail"><m l="'.$zimbraFolderId.'" '.$inputattribute.' d="'.$input->dtstamp.'000">';
                    } else {
                        $soap = '<SetAppointmentRequest xmlns="urn:zimbraMail" l="'.$zimbraFolderId.'" '.$inputattribute.'><default><m>';
                    }
                } else {
                    $preModAppt = $this->GetMessage($folderid, $id, $contentParameters); 
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'GetApptReq Original Appt: ' . print_r( $preModAppt, true ), false );

                    if (!isset($input->timezone)) {
                        $input->timezone = $preModAppt->timezone;
                    }
                    if (!isset($input->dtstamp) || ($input->dtstamp == 0)) {
                        $input->dtstamp = $preModAppt->dtstamp;
                    }
                    if (!isset($input->starttime)) {
                        $input->starttime = $preModAppt->starttime;
                    }
                    if (!isset($input->endtime)) {
                        $input->endtime = $preModAppt->endtime;
                    }

                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Checking if changes mean exceptions need to be stripped ! ' );
                    if (isset($input->recurrence) && !isset($preModAppt->recurrence)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence - does not exist on original ' );
                        $zimbraStrippedExceptions = true;
                    } elseif (!isset($input->recurrence) && isset($preModAppt->recurrence)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: no recurrence - Original does'  );
                        $zimbraStrippedExceptions = true;
                    } elseif ($input->starttime != $preModAppt->starttime) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: starttime changed' );
                        $zimbraStrippedExceptions = true;
                    } elseif ($input->endtime != $preModAppt->endtime) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: endtime changed' );
                        $zimbraStrippedExceptions = true;
                    }
					
                    if (isset($input->recurrence) && isset($preModAppt->recurrence)) {
                           ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence exists on both - Checking for/correcting known issues with patterns' );

                        if (!isset($input->recurrence->until) && !isset($preModAppt->recurrence->until) && !isset($input->recurrence->occurrences) && isset($preModAppt->recurrence->occurrences)) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence exists on both - Replacing empty input occurrences with preModAppt occurrences ' );

                            // Android not sending back occurrences - hack to fix it
                            $input->recurrence->occurrences = $preModAppt->recurrence->occurrences;
                        }

                        if (!isset($input->recurrence->interval) && isset($preModAppt->recurrence->interval) && ($preModAppt->recurrence->interval == 1)) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence exists on both - Interval OMITTED on input but set to 1 on preModAppt - Adding interval = 1 to input to avoid stripping exceptions ' );

                            // Some devices do not supply the default (1) value for interval
                            $input->recurrence->interval = $preModAppt->recurrence->interval;
                        }

                        if (isset($input->recurrence->interval) && isset($preModAppt->recurrence->interval) && ($input->recurrence->interval == $preModAppt->recurrence->interval)) {
                            if (isset($input->recurrence->dayofweek) && isset($preModAppt->recurrence->dayofweek) && ($input->recurrence->dayofweek == $preModAppt->recurrence->dayofweek)) {
                                if (isset($input->recurrence->type) && isset($preModAppt->recurrence->type) && ($input->recurrence->type == 1) && ($preModAppt->recurrence->type == 0)) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence exists on both - Interval & DayOfWeek MATCH - Replacing input Type 1 with preModAppt Type 0 to prevent stripping Exceptions from device "corrected" pattern ' );

                                    // Apple "corrects" the type for these appointments - hack to fix it so that zimbra will not treat it as a change of recurrence pattern
                                    // These come about when recurrence is set up as Daily in zimbra then customized to be Weekdays only. They should actually be weekly (type==1).
                                    $input->recurrence->type = $preModAppt->recurrence->type;
                                }
                            }
                            if (isset($input->recurrence->type) && isset($preModAppt->recurrence->type) && ($input->recurrence->type == 0) && ($preModAppt->recurrence->type == 0)) {
                                if (!isset($input->recurrence->dayofweek) && isset($preModAppt->recurrence->dayofweek)) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence exists on both - Interval & Type(0) MATCH - Replacing input DayOfWeek with preModAppt DayOfWeek to prevent stripping Exceptions from device "corrected" pattern ' );

                                    // Android "corrects" the DayOfWeek (by dropping it) for these appointments - hack to fix it so that zimbra will not treat it as a change of recurrence pattern
                                    // These come about when recurrence is set up as Daily in zimbra then customized to be Weekdays only. They should actually be weekly (type==1)
                                    $input->recurrence->dayofweek = $preModAppt->recurrence->dayofweek;
                                }
                            }
                        }

                        if (isset($input->recurrence->until) && isset($preModAppt->recurrence->until) && ($input->recurrence->until != $preModAppt->recurrence->until)) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence exists on both - Until date changed on input - was ['.$preModAppt->recurrence->until.'] - now ['.$input->recurrence->until.'] ' );

                            $msDifference = $preModAppt->recurrence->until - $input->recurrence->until;
                            if (($msDifference > 0) && ($msDifference < 86400)) { 
                                // Android not sending back time in Until field, it just sends the date - hack to fix it by keeping the original Until time. This avoids zimbra stripping the exceptions. 
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence Until changed by less than a full day ('.$msDifference.' secs.) Assuming android has dropped the time - Replacing input Until with preModAppt Until ' );
                                $input->recurrence->until = $preModAppt->recurrence->until;
                            }
                        }

                        if (isset($input->recurrence->type) && isset($preModAppt->recurrence->type) && ($input->recurrence->type == 1) && ($preModAppt->recurrence->type == 1) && isset($preModAppt->recurrence->premodtype) && ($preModAppt->recurrence->premodtype == 0)) {
                            // Override type back to 0 to match zimbra original. This is to avoid stripping exceptions
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence type 1 whereas ZIMRA original appointment is DAI (type 0) - Replacing input type with preModAppt->premodtype (0) ' );
                            $preModAppt->recurrence->type = 0;
                            $input->recurrence->type = 0;
                            // $preModAppt->recurrence->premodtype is no longer needed - remove it to prevent breaking the comparison
                            unset( $preModAppt->recurrence->premodtype );
                        }
if (is_string($input->recurrence->occurrences)) { ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'Input->recurrence->occurrences is a STRING'); }
if (is_integer($input->recurrence->occurrences)) { ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'Input->recurrence->occurrences is an INTEGER'); }
if (is_string($preModAppt->recurrence->occurrences)) { ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'preModAppt->recurrence->occurrences is a STRING'); }
if (is_integer($preModAppt->recurrence->occurrences)) { ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'preModAppt->recurrence->occurrences is an INTEGER'); }

                    }
                    if (isset($input->recurrence) && !$input->recurrence->equals( $preModAppt->recurrence )) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Input Appt: recurrence changed' );
                        $zimbraStrippedExceptions = true;
                    }
                    if ($zimbraStrippedExceptions) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Exceptions will be stripped by zimbra ! ' );
                    } else {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Exceptions will NOT be stripped by zimbra ! ' );
                    }

                    $invId = $preModAppt->zimbraInvId;

                    $soap = '<ModifyAppointmentRequest xmlns="urn:zimbraMail" ms="'.$preModAppt->zimbraMs.'" rev="'.$preModAppt->zimbraRev.'" id="'.$invId.'" comp="0">';
                    $soap .= '<m l="'.$preModAppt->folderid.'">';
                }

                $tzName = false;

                if(isset($input->timezone)) {
                    $tzObject = $this->GetTZFromSyncBlob(base64_decode($input->timezone));
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'incoming tzObject: '. print_r( $tzObject, true ) );

                    $dstoffset = -60*(intval($tzObject['bias'])+intval($tzObject['dstbias']));
                    $stdoffset = -60*(intval($tzObject['bias'])+intval($tzObject['stdbias']));

                    if (($dstoffset != $stdoffset) && function_exists("timezone_name_from_abbr")) {
                        $tzName = timezone_name_from_abbr("", $dstoffset, 1); // DST - Most accurate
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .   'Matched Timezone from timezone_name_from_abbr using DST ['  . $tzName . '] ' );
                    } else {
                        if (function_exists("timezone_abbreviations_list")) {
                            $timezone_abbreviations = timezone_abbreviations_list();
                            $possibleMatches = array();

                            while ($region = each($timezone_abbreviations) ) {

                                $count = sizeof($region['value']);
                                for ($i=0;$i < $count;$i++) {
                                    $tzListItem = $region['value'][$i];
                                    if (($tzListItem['dst'] === false) && ( $tzListItem['offset'] == $stdoffset )) {
                                        $possibleMatches[$tzListItem['timezone_id']]['std'] = 1; 
                                    }
                                }
                                for ($i=0;$i < $count;$i++) {
                                    $tzListItem = $region['value'][$i];
                                    if (($tzListItem['dst'] === true) && ( $tzListItem['offset'] == $dstoffset )) {
                                        if (in_array( $tzListItem['timezone_id'], $possibleMatches )) {
                                            $possibleMatches[$tzListItem['timezone_id']]['dst'] = 1; 
                                        } else {
                                            $possibleMatches[$tzListItem['timezone_id']]['std'] = 0; 
                                            $possibleMatches[$tzListItem['timezone_id']]['dst'] = 1; 
                                        }
                                    }
                                }
                            }

                            if (sizeof($possibleMatches) == 0) {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Incoming tzObject Timezone could not be identified: '. print_r( $tzObject, true ) );
                            }

                            if ($dstoffset == $stdoffset) {
                                foreach($possibleMatches as $name => $possibleMatch) {
                                    if (($possibleMatch['std'] == 1) && (!isset($possibleMatch['dst']))) {
                                        $tzName = $name;
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .   'Matched Timezone with STD-only timezone ['  . $tzName . '] ' );
                                        break;
                                    }
                                }
                            } else {
                                foreach($possibleMatches as $name => $possibleMatch) {
                                    if (($possibleMatch['std'] == 1)  && (isset($possibleMatch['dst']))&& ($possibleMatch['dst'] == 1)) {
                                        $tzName = $name;
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .   'Matched Timezone with STD-DST timezone ['  . $tzName . '] ' );
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                if (!$tzName) {
                    $tzName = $this->_tz;  // Best guess is User Preferred TZ
                }

                $soap .= '<inv';
                if ($id != '') {
                    $soap .= ' uid="'.$preModAppt->uid.'" ><comp ';
                } else {
                    $soap .= '><comp ';

                    // if creating a new appointment, and a UID is passed from the phone - Use it
                    if (isset($input->uid)) {
                        $soap .='uid="'.$input->uid.'" ';
                    }
                }

                if($id == '') {
                    if (!$this->_serverInviteReply) {
                    $soap .= ' d="'.$input->dtstamp.'000" ';
                    }
                }

                // If the phone is sending the invite directly, then we need to tell the server that they are sent.
                if (!$this->_serverInviteReply) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'Setting neverSent="0"' );
                    $soap .= ' neverSent="0" ';
                }

                /*  Free/Busy Status
                    fba="F|B|T|O"
                    F => 0
                    B => 2
                    T => 1
                    O => 3 // ?
                 */
                if (isset($input->busystatus)) {
                    if($input->busystatus == 0) {
                        $soap .= 'fb="F" ';
                    } else if($input->busystatus == 1) {
                        $soap .= 'fb="T" ';
                    } else if($input->busystatus == 2) {
                        $soap .= 'fb="B" ';
                    } else if($input->busystatus == 3) {
                        $soap .= 'fb="O" ';
                    }
                } else $soap .= 'fb="F" ';

                /*  Sensitivity Status
                    [class="PUB|PRI|CON"]
                    PUB => 0 // Default
                    PRI => 1
                    CON => 2
                */
                if (isset($input->sensitivity)) {
                    if($input->sensitivity == 0) {
                        $soap .= 'class="PUB" ';
                    } else if($input->sensitivity == 1) {
                        $soap .= 'class="PRI" ';
                    } else if($input->sensitivity == 2) {
                        $soap .= 'class="CON" ';
                    }
                } else $soap .= 'class="PUB" ';


                // allday
                if ((isset($input->alldayevent)) && ($input->alldayevent == 1)) {
                    $soap .= 'allDay="1" ';
                } else {
                    $soap .= 'allDay="0" ';
                } 

                // subject
                if(isset($input->subject)) {
                    $soap .= 'name="'.htmlspecialchars($input->subject).'" ';
                }

                // location
                if(isset($input->location)) {
                    $soap .= 'loc="'.htmlspecialchars($input->location).'" ';
                }

                // close <comp ... >
                $soap .= '> ';

                if ((isset($input->alldayevent)) && ($input->alldayevent == 1)) {
                	
	                //starttime
	                $starttime = $this->Date4Zimbra($input->starttime, $tzName);
	                $starttime = substr( $starttime, 0, 8);
	                $soap .= '<s d="'.$starttime.'"/> ';

	                //endtime
	                // As Zimbra stores both start and end as the same date (unlike ActiveSync) we need to subtract 1 day
	                $endtime = $this->Date4Zimbra($input->endtime - 86400,  $tzName);
	                $endtime = substr( $endtime, 0, 8 );
	                $soap .= '<e d="'.$endtime.'"/> ';
                } else {

                    if ($this->_zimbraVersion == '5.0' ) {
                        $v5tz = $this->LookupV5Timezone( $tzName, "");
                        if ($v5tz !== false) {
                            $v5tzAttribute = ' tz="'.$v5tz.'" ';
                        } else {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  "Incoming Meeting v6 TimeZone [".$tzName."] NOT FOUND in v5timezone.xml - APPOINTMENT START/END TIMES WILL BE SAVED TO ZIMBRA WITH NO SET TIMEZONE"  );
                            $v5tzAttribute = '';
                        }

                        //starttime
                        $soap .= '<s '.$v5tzAttribute.' d="'.$this->Date4Zimbra($input->starttime, $tzName).'"/> ';

                        //endtime
                        $soap .= '<e '.$v5tzAttribute.' d="'.$this->Date4Zimbra($input->endtime, $tzName).'"/> ';
                    } else {
						//starttime
						$soap .= '<s tz="'.$tzName.'" d="'.$this->Date4Zimbra($input->starttime, $tzName).'"/> ';
					
						//endtime
						$soap .= '<e tz="'.$tzName.'" d="'.$this->Date4Zimbra($input->endtime, $tzName).'"/> ';
                    }
                }  

                if($id == '') {
                    if (($folderOwner != 'ME') && ($zimbraFolderOwner <> '')) {
                        $soap .= '<or a="'.$zimbraFolderOwner.'" /> ';
                    } else {
                        $soap .= '<or a="'.$this->_sendAsEmail.'" d="'.$this->_sendAsName.'"/> ';
                    }
                } else {
                    //organizer - R47 - don't change if updating an existing appointment
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Modifying Appointment - Attempting to keep saved Organizer ['.$preModAppt->premodorganizername.'] - Email ['.$preModAppt->premodorganizeremail.']' );

                    if (isset($preModAppt->premodorganizeremail) && (trim($preModAppt->premodorganizeremail) != "")) {
                        $soap .= '<or a="'.$preModAppt->premodorganizeremail.'" ';
                        if (isset($preModAppt->premodorganizername) && (trim($preModAppt->premodorganizername) != "")) {
                            $soap .= ' d="'.$preModAppt->premodorganizername.'" ';
                        }
                        $soap .= ' /> ';
                    }
                }
               
                //attendees
                unset( $attendees );
                if(isset($input->attendees)) {
                    $attendees = $input->attendees;
                } elseif(isset($preModAppt->attendees)) {
                    $attendees = $preModAppt->attendees;
                }
                if (isset($attendees)) {
                    foreach($attendees as $attendee) {
                        if (isset($attendee->email)) {
                            $soap .= '<at a="'.$attendee->email.'" ';
                            if (isset($attendee->name)) {
                                $soap .= ' d="'.$attendee->name.'" ';
                            }

                            if(isset($attendee->type) && $attendee->type == 2) {
                                $soap .= 'role="OPT" ';
                            } else {
                                $soap .= 'role="REQ" ';
                            }
                            $soap .= 'ptst="NE" rsvp="1" />';
                        }
                    }
                    unset( $attendees );
                    unset( $attendee );
                }

                //recurrence
                if(isset($input->recurrence)) {
                    $recurrence = $input->recurrence;
                    $soap .= '<recur><add>';

                    /*   SEC,MIN,HOU,DAI,WEE,MON,YEA
                        DAI => 0
                        WEE => 1
                        MON => 2 or 3
                        YEA =>  5 or 6 (What does 4 mean?)
                    */
                    if($recurrence->type == 0) {
                        $soap .= '<rule freq="DAI">';
                    } elseif($recurrence->type == 1) {
                        $soap .= '<rule freq="WEE">';
                    } elseif( $recurrence->type == 2 ||  $recurrence->type == 3) {
                        $soap .= '<rule freq="MON">';
                    } elseif($recurrence->type == 5 || $recurrence->type == 6) {
                        $soap .= '<rule freq="YEA">';
                    }

                    // Swapped these around, as spec says use occurrences in preference to until
                    if(isset($recurrence->occurrences)) {
                        $soap .= '<count num="'.$recurrence->occurrences.'"/>';
                    } else if(isset($recurrence->until)) {
                        $soap .= '<until d="'.$this->Date4Zimbra($recurrence->until, $tzName).'"/>';                    
                    }

                    if(isset($recurrence->interval)) {
                        $soap .= '<interval ival="'.$recurrence->interval.'"/>';
                    }

                    if(isset($recurrence->dayofweek)) {
                        $soap .= '<byday>';
                        // SU
                        if($this->GetBit($recurrence->dayofweek, 1)) {
                            $soap .= '<wkday day="SU"/>';
                        }
                        // MO
                        if($this->GetBit($recurrence->dayofweek, 2)) {
                            $soap .= '<wkday day="MO"/>';
                        }
                        // TU
                        if($this->GetBit($recurrence->dayofweek, 3)) {
                            $soap .= '<wkday day="TU"/>';
                        }
                        // WE
                        if($this->GetBit($recurrence->dayofweek, 4)) {
                            $soap .= '<wkday day="WE"/>';
                        }
                        // TH
                        if($this->GetBit($recurrence->dayofweek, 5)) {
                            $soap .= '<wkday day="TH"/>';
                        }
                        // FR
                        if($this->GetBit($recurrence->dayofweek, 6)) {
                            $soap .= '<wkday day="FR"/>';
                        }
                        // SA
                        if($this->GetBit($recurrence->dayofweek, 7)) {
                            $soap .= '<wkday day="SA"/>';
                        }
                        $soap .= '</byday>';
                    }

                    if(isset($recurrence->dayofmonth)) {
                        $soap .= '<bymonthday modaylist="'.$recurrence->dayofmonth.'"/>';
                    }

                    if(isset($recurrence->weekofmonth)) {
                        // Activesync uses weekofmonth 5 for last week - zimbra uses -1
                        if ($recurrence->weekofmonth == 5) {
                            $soap .= '<bysetpos poslist="-1"/>';
                        } else {
                            $soap .= '<bysetpos poslist="'.$recurrence->weekofmonth.'"/>';
                        }
                    }

                    if(isset($recurrence->monthofyear)) {
                        $soap .= '<bymonth molist="'.$recurrence->monthofyear.'"/>';
                    }
                    $soap .= '</rule></add></recur>';
                }

                if(isset($input->reminder)){
                $soap .='<alarm action="DISPLAY">
                            <trigger>
                                <rel neg="1" m="'.$input->reminder.'" related="START"/>
                            </trigger>
                        </alarm> ';
                }

                // CLIENT UID
                if (isset($input->uid)) {
                  $soap .='<xprop name="X-CLIENT-UID" value="'.base64_encode($input->uid).'"/>';
                }
				
                // end <comp> & <inv>
                $soap .= '</comp></inv>';


                // email organizer/attendees only if _serverInviteReply is set to true
                // Almost all phones now properly send the invites directly themselves
                if ($this->_serverInviteReply) {
                    unset( $attendees );
                    if(isset($input->attendees)) {
                        $attendees = $input->attendees;
                    } elseif(isset($preModAppt->attendees)) {
                        $attendees = $preModAppt->attendees;
                    }
                    if (isset($attendees)) {
                        foreach($attendees as $attendee) {
                            if (isset($attendee->email)) {
                                $soap .= '<e a="'.$attendee->email.'" ';
                                if (isset($attendee->name)) {
                                    $soap .= ' p="'.$attendee->name.'" ';
                                }
                                $soap .= ' t="t" />';
                            }
                        }
                        unset( $attendees );
                        unset( $attendee );
                    }

                }

                // email originator
                if($id == '') {
                    $soap .= '<e a="'.$this->_sendAsEmail.'"
                                 p="'.$this->_sendAsName.'" t="f" />';
                } else {
                    //organizer - R47 - don't change if updating an existing appointment
//                    $soap .= '<e a="'.$preModAppt->organizeremail.'"
//                                 p="'.$preModAppt->organizername.'" t="f" />';
                    if (isset($preModAppt->premodorganizeremail) && (trim($preModAppt->premodorganizeremail) != "")) {
                        $soap .= '<e a="'.$preModAppt->premodorganizeremail.'" ';
                        if (isset($preModAppt->premodorganizername) && (trim($preModAppt->premodorganizername) != "")) {
                            $soap .= ' p="'.$preModAppt->premodorganizername.'" ';
                        }
                        $soap .= ' t="f" /> ';
                    }
                }

                //subject
                $soap .= '<su>'.htmlspecialchars($input->subject).'</su>';

                $notes = "";
                $notesType = 1;
                if ((Request::GetProtocolVersion() >= 12)) {
                    if (isset($input->asbody->data)) {
                        if (is_resource($input->asbody->data)) {
                            $notes = stream_get_contents($input->asbody->data);
                            fclose($input->asbody->data);
                            $notesType = $input->asbody->type;
                        } else {
                            $notes = $input->asbody->data;
                            $notesType = $input->asbody->type;
                        }
                    }

                } else if ((Request::GetProtocolVersion() < 12) && isset($input->body) && ($input->body != "")) {
                    $notes = $input->body;
                } else if(isset($input->rtf)) {
                    if (class_exists('rtf', false)) {
                        // start decode RTF if present
                        $rtf_body = new rtf ();
                        $rtf_body->loadrtf(base64_decode($input->rtf));
                        $rtf_body->output("ascii");
                        $rtf_body->parse();
                        $notes = w2ui( $rtf_body->out );
                        unset( $rtf_body );
                    } else {
                        $notes = "Missing include file z_RTF.php needed to Decode Notes";
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'RTF field could not be handled - '.$notes.' - See notes in zimbra.php' );
                    }
                }

                if ($notes <> "") {  // Notes changed
                    //plain text body unless type = 2 passed in.
                    if ($notesType == 1) {                    
                        $soap .= '<mp ct="text/plain"><content>'.htmlspecialchars($notes).'</content></mp>';
                    } else {
                        $soap .= '<mp ct="text/html"><content>'.htmlspecialchars($notes).'</content></mp>';
                    }
                } else {
                    if (isset($preModAppt->zimbraPlainNotes)) {
                        $soap .= '<mp ct="text/plain"><content>'.htmlspecialchars($preModAppt->zimbraPlainNotes).'</content></mp>';
                    } elseif (isset($preModAppt->zimbraHtmlNotes)) {
                        $soap .= '<mp ct="text/html"><content>'.htmlspecialchars($preModAppt->zimbraHtmlNotes).'</content></mp>';
                    }
                }

                //close <m>
                $soap .= '</m>';

                if($id == '') {
                    if ($this->_serverInviteReply) {
                        $soap .= '</CreateAppointmentRequest>';
                    } else {
                        $soap .= '</default></SetAppointmentRequest>';
                    }
                } else {
                    $soap .= '</ModifyAppointmentRequest>';
                }
//ZLog::Write(LOGLEVEL_DEBUG, 'Call SOAP' );
//ZLog::Write(LOGLEVEL_DEBUG, 'SOAP: '.$soap );

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);
                if($response) {
                    $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CreateUpdateBaseAppt:' . print_r( $array, true ) );
                    unset($response);
                    if ($id == '') {
                        if ($this->_serverInviteReply) {
                            $id = $array['Body']['CreateAppointmentResponse']['calItemId'];
                        } else {
                            $id = $array['Body']['SetAppointmentResponse']['calItemId'];
                        }
                        
						unset($array);
                    } else {
                        if (isset($array['Body']['ModifyAppointmentResponse']['ms'])) {
                            $preModAppt->zimbraMs = $array['Body']['ModifyAppointmentResponse']['ms'];
                        }
                        if (isset($array['Body']['ModifyAppointmentResponse']['rev'])) {
                            $preModAppt->zimbraRev = $array['Body']['ModifyAppointmentResponse']['rev'];
                        }
                        // Modifying existing appointment - Cannot apply TAGS to shared items - so don't even try
                        if (strrpos($id,':') === false) {
                            $soap ='<ItemActionRequest xmlns="urn:zimbraMail">
                                      <action id="'.$id.'" op="update" t="'.$inputtags.'" />
                                    </ItemActionRequest> ';
                            $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                            if($actionResponse) {
                                $actionArray = json_decode($actionResponse, true);
                                if (!isset($actionArray['Body']['ItemActionResponse']['action']['id']) or !isset($actionArray['Body']['ItemActionResponse']['action']['op'])) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage APPOINTMENT TAG update failed');
                                }
                            }
                            unset($actionResponse);
                        }
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { true }');
//                    return $this->StatMessage($this->_folders[$index]->devid, $id);
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'SOAP ['.$soap.']' );
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { false }');
                    return false;
                }

                if (!isset($preModAppt) || $zimbraStrippedExceptions || (!isset($input->exceptions) && !isset($preModAppt->exceptions))) {  // zimbra strips exceptions automatically in this case
                    return $this->StatMessage($this->_folders[$index]->devid, $id);
                }

                /* Base appointment updated !
                   Now try to handle exceptions - Adds/Changes/Deletions
                */

                // First make sure exceptions exists on both appointments - create empty array if necessary
                if (!isset($input->exceptions)) {
                    $input->exceptions = array();
                }
                if (!isset($preModAppt->exceptions)) {
                    $preModAppt->exceptions = array();
                }

                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Appt has/had exceptions - Need to check if they changed ...' );

                $except = new SyncAppointmentException();
                $preModExcept = new SyncAppointmentException();

                foreach ($input->exceptions as $i=>$except) {
                    $matchFound = false;
                    foreach ($preModAppt->exceptions as $j=>$preModExcept) {
                        
                        if ($except->exceptionstarttime == $preModExcept->exceptionstarttime) {
                            $matchFound = true;
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] ExceptionStartTime matches PreModExcept J ['.$j.'] at ['.$preModExcept->exceptionstarttime.']' );

                            // Allow for common differences in the objects ...
                            if (!isset($except->meetingstatus) && isset($preModExcept->meetingstatus)) { $except->meetingstatus = $preModExcept->meetingstatus; }
                            if (!isset($except->dtstamp) && isset($preModExcept->dtstamp)) { $except->dtstamp = $preModExcept->dtstamp; }
                            if ((isset($except->dtstamp) && isset($preModExcept->dtstamp)) && ($except->dtstamp != $preModExcept->dtstamp)) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): dtstamp values are different - PreMod [' . $preModExcept->dtstamp . '] - Input [ ' . $except->dtstamp . '] - Oerwriting incoming one to avoid being the cause of a difference' );
                                $except->dtstamp = $preModExcept->dtstamp;
                            }
                            if (isset($except->deleted) && !isset($preModExcept->deleted) && ($except->deleted == 0)) { $preModExcept->deleted = 0; }
                            if (!isset($except->deleted) && isset($preModExcept->deleted) && ($preModExcept->deleted == 0)) { $except->deleted = 0; }
                            if (isset($except->sensitivity) && !isset($preModExcept->sensitivity) && ($except->sensitivity == 0)) { $preModExcept->sensitivity = 0; }
                            if (!isset($except->sensitivity) && isset($preModExcept->sensitivity) && ($preModExcept->sensitivity == 0)) { $except->sensitivity = 0; }
                            if (isset($except->asbody) && isset($preModExcept->asbody)) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Both have asbody' );
                                if (isset($except->asbody->type) && isset($preModExcept->asbody->type) && ($except->asbody->type == $preModExcept->asbody->type)) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Both have asbody->type and they match' );
                                    if (isset($except->asbody->data) && isset($preModExcept->asbody->data) && is_resource($except->asbody->data) && is_resource($preModExcept->asbody->data)) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Both have asbody->data and they are resources' );
                                        $except->asbody->streamtext = str_replace( "\r\n", "\n", stream_get_contents($except->asbody->data));
                                        fclose($except->asbody->data);
                                        $except->asbody->data = null;
                                        $preModExcept->asbody->streamtext = str_replace( "\r\n", "\n", stream_get_contents($preModExcept->asbody->data));
                                        fclose($preModExcept->asbody->data);
                                        $preModExcept->asbody->data = null;
                                        if (trim($preModExcept->asbody->streamtext) == trim($except->asbody->streamtext)) {
                                            $except->asbody->streamtext = $preModExcept->asbody->streamtext;
                                            $except->asbody->estimatedDataSize = $preModExcept->asbody->estimatedDataSize = strlen($preModExcept->asbody->streamtext);
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Both have asbody->data and they are the SAME' );
                                        } else {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Both have asbody->data and they are DIFFERENT' );
                                        }
                                    } elseif (isset($except->asbody->data) && isset($preModExcept->asbody->data) && (!is_resource($except->asbody->data)) && (!is_resource($preModExcept->asbody->data)) && (trim(str_replace( "\r\n", "\n", $except->asbody->data)) == trim(str_replace( "\r\n", "\n", $preModExcept->asbody->data)))) {
                                        $except->asbody = $preModExcept->asbody;
                                    }

                                }
                            } elseif (!isset($except->asbody) && isset($preModExcept->asbody)) {
                                if (isset($preModExcept->asbody->data) && is_resource($preModExcept->asbody->data)) {
                                    $preModExcept->asbody->streamtext = str_replace( "\r\n", "\n", stream_get_contents($preModExcept->asbody->data));
                                    fclose($preModExcept->asbody->data);
                                    $preModExcept->asbody->data = null;
                                }
                                $except->asbody = $preModExcept->asbody;
                            }

//                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): PreModExcept->asbody: ' . print_r($preModExcept->asbody ,true), false );
//                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Except->asbody: ' . print_r($except->asbody ,true), false );
                            // Now compare them ...
                            if (!$except->equals($preModExcept)) {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] DOES NOT match PreModExcept J ['.$j.'] - Need to do mods here' );

                                // TODO - TO DO - Just duplicating code for the moment to get this working - need to revisit and tidy up.

                                if (isset($except->categories) and is_array($except->categories)) {
                                    $apptTagIds = $this->CategoriesToTags($except->categories);
				
                                    $appttags = implode( ",",$apptTagIds);
                                    $tagattribute = ' t="'.$appttags.'" ';
                                } else {
                                    $appttags = "";
                                    $tagattribute = "";
                                }

                                $invId = $preModExcept->zimbraInvId;

                                if (isset($except->deleted) && $except->deleted == 1) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] is a new deletion of an existing exception occurrence' );

                                    $soap  = '<CancelAppointmentRequest xmlns="urn:zimbraMail" id="'.$preModAppt->zimbraInvId.'" comp="'.$preModAppt->zimbraCompNum.'" >';
                                    $soap .= '<inst d="'.$this->Date4Zimbra($except->exceptionstarttime, $tzName).'" tz="'.$tzName.'" />';
                                    $soap .= '</CancelAppointmentRequest>';

                                    $returnJSON = true;
                                    $response = $this->SoapRequest($soap, false, false, $returnJSON);
                                    if($response) {
                                        $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CancelAppointmentRequest (Exception):' . print_r( $array, true ) );
                                        unset($response);
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { true }');
//                                        return $this->StatMessage($this->_folders[$index]->devid, $id);
                                    } else {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'SOAP ['.$soap.']' );
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { false }');
                                        return false;
                                    }
                                } else {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] is a new modification of an existing exception occurrence' );

                                    $soap = '<ModifyAppointmentRequest xmlns="urn:zimbraMail" id="'.$invId.'" comp="0">';
                                    $soap .= '<m l="'.$zimbraFolderId.'" >';
//                                    $soap .= '<inv uid="'.$preModAppt->uid.'"><comp ';
                                    $soap .= '<inv><comp ';

                                    /*  Free/Busy Status
                                        fba="F|B|T|O"
                                        F => 0
                                        B => 2
                                        T => 1
                                        O => 3 // ?
                                     */
                                    if (!isset($except->busystatus) && isset($preModAppt->busystatus)) { $except->busystatus = $preModAppt->busystatus; } 
                                    if (isset($except->busystatus)) {
                                        if($except->busystatus == 0) {
                                            $soap .= 'fb="F" ';
                                        } else if($except->busystatus == 1) {
                                            $soap .= 'fb="T" ';
                                        } else if($except->busystatus == 2) {
                                            $soap .= 'fb="B" ';
                                        } else if($except->busystatus == 3) {
                                            $soap .= 'fb="O" ';
                                        }
                                    } else $soap .= 'fb="F" ';

                                    /*  Sensitivity Status
                                        [class="PUB|PRI|CON"]
                                        PUB => 0 // Default
                                        PRI => 1
                                        CON => 2
                                    */
                                    if (isset($except->sensitivity)) {
                                        if($except->sensitivity == 0) {
                                            $soap .= 'class="PUB" ';
                                        } else if($except->sensitivity == 1) {
                                            $soap .= 'class="PRI" ';
                                        } else if($except->sensitivity == 2) {
                                            $soap .= 'class="CON" ';
                                        }
                                    } else $soap .= 'class="PUB" ';

                                    // allday
                                    if ((isset($except->alldayevent)) && ($except->alldayevent == 1)) {
                                        $soap .= 'allDay="1" ';
                                    } else {
                                        $soap .= 'allDay="0" ';
                                    } 

                                    // subject
                                    if(isset($except->subject)) {
                                        $soap .= 'name="'.htmlspecialchars($except->subject).'" ';
                                    }

                                    // location
                                    if(isset($except->location)) {
                                        $soap .= 'loc="'.htmlspecialchars($except->location).'" ';
                                    }

                                    // close <comp ... >
                                    $soap .= '> ';

                                    if ((isset($except->alldayevent)) && ($except->alldayevent == 1)) {
                	
                    	                //starttime
                    	                $starttime = $this->Date4Zimbra($except->starttime, $tzName);
                    	                $starttime = substr( $starttime, 0, 8);
	                                    $soap .= '<s d="'.$starttime.'"/> ';

	                                    //endtime
	                                    // As Zimbra stores both start and end as the same date (unlike ActiveSync) we need to subtract 1 day
	                                    $endtime = $this->Date4Zimbra($except->endtime - 86400,  $tzName);
	                                    $endtime = substr( $endtime, 0, 8 );
                    	                $soap .= '<e d="'.$endtime.'"/> ';
                                    } else {

                                        if ($this->_zimbraVersion == '5.0' ) {
                                            $v5tz = $this->LookupV5Timezone( $tzName, "");
                                            if ($v5tz !== false) {
                                                $v5tzAttribute = ' tz="'.$v5tz.'" ';
                                            } else {
                                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  "Incoming Meeting v6 TimeZone [".$tzName."] NOT FOUND in v5timezone.xml - APPOINTMENT START/END TIMES WILL BE SAVED TO ZIMBRA WITH NO SET TIMEZONE"  );
                                                $v5tzAttribute = '';
                                            }

                                            //starttime
                                            $soap .= '<s '.$v5tzAttribute.' d="'.$this->Date4Zimbra($except->starttime, $tzName).'"/> ';

                                            //endtime
                                            $soap .= '<e '.$v5tzAttribute.' d="'.$this->Date4Zimbra($except->endtime, $tzName).'"/> ';
                                        } else {
                    						//starttime
                    						$soap .= '<s tz="'.$tzName.'" d="'.$this->Date4Zimbra($except->starttime, $tzName).'"/> ';
					
                    						//endtime
                    						$soap .= '<e tz="'.$tzName.'" d="'.$this->Date4Zimbra($except->endtime, $tzName).'"/> ';
                                        }
                                    }  

                                    //organizer - R47 - don't change if updating an existing appointment
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Modifying Appointment - Attempting to keep saved Organizer ['.$preModAppt->premodorganizername.'] - Email ['.$preModAppt->premodorganizeremail.']' );

                                    if (isset($preModAppt->premodorganizeremail) && (trim($preModAppt->premodorganizeremail) != "")) {
                                        $soap .= '<or a="'.$preModAppt->premodorganizeremail.'" ';
                                        if (isset($preModAppt->premodorganizername) && (trim($preModAppt->premodorganizername) != "")) {
                                            $soap .= ' d="'.$preModAppt->premodorganizername.'" ';
                                        }
                                        $soap .= ' /> ';
                                    }

                                    //attendees
                                    unset( $attendees );
                                    if(isset($except->attendees)) {
                                        $attendees = $except->attendees;
                                    } elseif(isset($preModExcept->attendees)) {
                                        $attendees = $preModExcept->attendees;
                                    } elseif(isset($preModAppt->attendees)) {
                                        $attendees = $preModAppt->attendees;
                                    }
                                    if (isset($attendees)) {
                                        foreach($attendees as $attendee) {
                                            if (isset($attendee->email)) {
                                                $soap .= '<at a="'.$attendee->email.'" ';
                                                if (isset($attendee->name)) {
                                                    $soap .= ' d="'.$attendee->name.'" ';
                                                }

                                                if(isset($attendee->type) && $attendee->type == 2) {
                                                    $soap .= 'role="OPT" ';
                                                } else {
                                                    $soap .= 'role="REQ" ';
                                                }
                                                $soap .= 'ptst="NE" rsvp="1" />';
                                            }
                                        }
                                        unset( $attendees );
                                        unset( $attendee );
                                    }

                                    if(isset($except->reminder)){
                                    $soap .='<alarm action="DISPLAY">
                                                <trigger>
                                                    <rel neg="1" m="'.$except->reminder.'" related="START"/>
                                                </trigger>
                                            </alarm> ';
                                    }

                                    // end <comp> & <inv>
                                    $soap .= '</comp></inv>';


                                    // email organizer/attendees only if _serverInviteReply is set to true
                                    // Almost all phones now properly send the invites directly themselves
                                    if ($this->_serverInviteReply) {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  "Updating Appointment: _serverInviteReply IS true - so SEND invites from zimbra");
                                        if(isset($except->attendees)) {
                                            $attendees = $except->attendees;
                                            foreach($attendees as $attendee) {
                                                if (isset($attendee->email)) {
                                                    $soap .= '<e a="'.$attendee->email.'" ';
                                                    if (isset($attendee->name)) {
                                                        $soap .= ' p="'.$attendee->name.'" ';
                                                    }
                                                    $soap .= ' t="t" />';
                                                }
                                            }
                                            unset( $attendees );
                                            unset( $attendee );
                                        }

                                    }

                                    //organizer - R47 - don't change if updating an existing appointment
                                    $soap .= '<e a="'.$preModAppt->organizeremail.'"
                                                 p="'.$preModAppt->organizername.'" t="f" />';

                                    //subject
                                    $soap .= '<su>'.htmlspecialchars($except->subject).'</su>';

                                    $notes = "";
                                    $notesType = 1;
                                    if ((Request::GetProtocolVersion() >= 12)) {
                        				if (isset($except->asbody->streamtext)) {
                                            $notes = $except->asbody->streamtext;
                                            $notesType = $except->asbody->type;
                                        } elseif (isset($except->asbody->data)) {
                                            $notes = $except->asbody->data;
                                            $notesType = $except->asbody->type;
                                        }
                                    } else if ((Request::GetProtocolVersion() < 12) && isset($except->body) && ($except->body != "")) {
                                        $notes = $except->body;
                                    } else if(isset($except->rtf)) {
                                        if (class_exists('rtf', false)) {
                                            // start decode RTF if present
                                            $rtf_body = new rtf ();
                                            $rtf_body->loadrtf(base64_decode($except->rtf));
                                            $rtf_body->output("ascii");
                                            $rtf_body->parse();
                                            $notes = w2ui( $rtf_body->out );
                                            unset( $rtf_body );
                                        } else {
                                            $notes = "Missing include file z_RTF.php needed to Decode Notes";
                                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'RTF field could not be handled - '.$notes.' - See notes in zimbra.php' );
                                        }
                                    }

                                    if ($notes <> "") {  // Notes changed
                                        //plain text body unless type = 2 passed in.
                                        if ($notesType == 1) {                    
                                            $soap .= '<mp ct="text/plain"><content>'.htmlspecialchars($notes).'</content></mp>';
                                        } else {
                                            $soap .= '<mp ct="text/html"><content>'.htmlspecialchars($notes).'</content></mp>';
                                        }
                                    } else {
                                        if (isset($preModAppt->zimbraPlainNotes)) {
                                            $soap .= '<mp ct="text/plain"><content>'.htmlspecialchars($preModAppt->zimbraPlainNotes).'</content></mp>';
                                        } elseif (isset($preModAppt->zimbraHtmlNotes)) {
                                            $soap .= '<mp ct="text/html"><content>'.htmlspecialchars($preModAppt->zimbraHtmlNotes).'</content></mp>';
                                        }
                                    }

                                    //close <m>
                                    $soap .= '</m>';

                                    $soap .= '</ModifyAppointmentRequest>';
//ZLog::Write(LOGLEVEL_DEBUG, 'Call SOAP' );
//ZLog::Write(LOGLEVEL_DEBUG, 'SOAP (ModifyAppointmentException): '.$soap );

                                    $returnJSON = true;
                                    $response = $this->SoapRequest($soap, false, false, $returnJSON);
                                    if($response) {
                                        $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'ModApptException:' . print_r( $array, true ) );
                                        unset($response);
                                        if (isset($array['Body']['ModifyAppointmentResponse']['ms'])) {
                                            $preModAppt->zimbraMs = $array['Body']['ModifyAppointmentResponse']['ms'];
                                        }
                                        if (isset($array['Body']['ModifyAppointmentResponse']['rev'])) {
                                            $preModAppt->zimbraRev = $array['Body']['ModifyAppointmentResponse']['rev'];
                                        }
                                        // Modifying existing appointment - Cannot apply TAGS to shared items - so don't even try
                                        if (strrpos($id,':') === false) {
                                            $soap ='<ItemActionRequest xmlns="urn:zimbraMail">
                                                      <action id="'.$id.'" op="update" t="'.$appttags.'" />
                                                    </ItemActionRequest> ';
                                            $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                                            if($actionResponse) {
                                                $actionArray = json_decode($actionResponse, true);
                                                if (!isset($actionArray['Body']['ItemActionResponse']['action']['id']) or !isset($actionArray['Body']['ItemActionResponse']['action']['op'])) {
                                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage APPOINTMENT TAG update failed');
                                                }
                                            }
                                            unset($actionResponse);
                                        }
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { true }');
//                                        return $this->StatMessage($this->_folders[$index]->devid, $id);
                                    } else {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'SOAP ['.$soap.']' );
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { false }');
                                        return false;
                                    }
                                }   

// END duplicating for Modify
                            }
                            unset( $preModAppt->exceptions[$j] );
                        }
                    }

                    if (!$matchFound) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] DOES NOT exist in PreModExcepts - Need to add it here' );

                        if (isset($except->deleted) && $except->deleted == 1) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] is a new deletion of an occurrence' );

                            $soap  = '<CancelAppointmentRequest xmlns="urn:zimbraMail" id="'.$preModAppt->zimbraInvId.'" comp="'.$preModAppt->zimbraCompNum.'" >';
                            $soap .= '<inst d="'.$this->Date4Zimbra($except->exceptionstarttime, $tzName).'" tz="'.$tzName.'" />';
                            $soap .= '</CancelAppointmentRequest>';

                            $returnJSON = true;
                            $response = $this->SoapRequest($soap, false, false, $returnJSON);
                            if($response) {
                                $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CancelAppointmentRequest (Exception):' . print_r( $array, true ) );
                                unset($response);
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { true }');
//                                return $this->StatMessage($this->_folders[$index]->devid, $id);
                            } else {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'SOAP ['.$soap.']' );
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { false }');
                                return false;
                            }
                        } else {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): Input Except I ['.$i.'] is a new exception occurrence' );

//                            $soap  = '<CreateAppointmentExceptionRequest xmlns="urn:zimbraMail" id="'.$preModAppt->zimbraInvId.'" comp="'.$preModAppt->zimbraCompNum.'" ms="'.$preModAppt->zimbraMs.'" rev="'.$preModAppt->zimbraRev.'" >';
                            $soap  = '<CreateAppointmentExceptionRequest xmlns="urn:zimbraMail" id="'.$preModAppt->zimbraInvId.'" comp="'.$preModAppt->zimbraCompNum.'" >';
                            $soap .= '<m l="'.$zimbraFolderId.'" >';

                            $soap .= '<inv uid="'.$preModAppt->uid.'" >';
                            $soap .= '<comp ';

                            /*  Free/Busy Status
                                fba="F|B|T|O"
                                F => 0
                                B => 2
                                T => 1
                                O => 3 // ?
                             */
                            if (!isset($except->busystatus) && isset($preModAppt->busystatus)) { $except->busystatus = $preModAppt->busystatus; } 
                            if (isset($except->busystatus)) {
                                if($except->busystatus == 0) {
                                    $soap .= 'fb="F" ';
                                } else if($except->busystatus == 1) {
                                    $soap .= 'fb="T" ';
                                } else if($except->busystatus == 2) {
                                    $soap .= 'fb="B" ';
                                } else if($except->busystatus == 3) {
                                    $soap .= 'fb="O" ';
                                }
                            } else $soap .= 'fb="B" ';


                            /*  Sensitivity Status
                                [class="PUB|PRI|CON"]
                                PUB => 0 // Default
                                PRI => 1
                                CON => 2
                            */
                            if (isset($except->sensitivity)) {
                                if($except->sensitivity == 0) {
                                    $soap .= 'class="PUB" ';
                                } else if($except->sensitivity == 1) {
                                    $soap .= 'class="PRI" ';
                                } else if($except->sensitivity == 2) {
                                    $soap .= 'class="CON" ';
                                }
                            } else $soap .= 'class="PUB" ';


                            // allday
                            if ((isset($except->alldayevent)) && ($except->alldayevent == 1)) {
                                $soap .= 'allDay="1" ';
                            } else {
                                $soap .= 'allDay="0" ';
                            } 

                            // subject
                            if(isset($except->subject)) {
                                $soap .= 'name="'.htmlspecialchars($except->subject).'" ';
                            }

                            // location
                            if(isset($except->location)) {
                                $soap .= 'loc="'.htmlspecialchars($except->location).'" ';
                            }
				
                            // close <comp ... >
                            $soap .= '> ';
                            $soap .= '<exceptId d="'.$this->Date4Zimbra($except->exceptionstarttime, $tzName).'" tz="'.$tzName.'" />';

/* Not sure if I should just set myself - or try to keep parent ?
                            if($id == '') {
                                $soap .= '<or a="'.$this->_sendAsEmail.'" d="'.$this->_sendAsName.'"/> ';
                            } else {
                                //organizer - R47 - don't change if updating an existing appointment
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Modifying Appointment - Attempting to keep saved Organizer ['.$preModAppt->organizername.'] - Email ['.$preModAppt->organizeremail.']' );

                                if (isset($preModAppt->organizeremail) && (trim($preModAppt->organizeremail) != "")) {
                                    $soap .= '<or a="'.$preModAppt->organizeremail.'" ';
                                    if (isset($preModAppt->organizername) && (trim($preModAppt->organizername) != "")) {
                                        $soap .= ' d="'.$preModAppt->organizername.'" ';
                                    }
                                    $soap .= ' /> ';
                                }
                            }
*/
// Will try keeping Parent
                            if (isset($preModAppt->organizeremail) && (trim($preModAppt->organizeremail) != "")) {
                                $soap .= '<or a="'.$preModAppt->organizeremail.'" ';
                                if (isset($preModAppt->organizername) && (trim($preModAppt->organizername) != "")) {
                                    $soap .= ' d="'.$preModAppt->organizername.'" ';
                                }
                                $soap .= ' /> ';
                            }

                            if ((isset($except->alldayevent)) && ($except->alldayevent == 1)) {
                	
            	                //starttime
            	                $starttime = $this->Date4Zimbra($except->starttime, $tzName);
            	                $starttime = substr( $starttime, 0, 8);
            	                $soap .= '<s d="'.$starttime.'"/> ';

            	                //endtime
            	                // As Zimbra stores both start and end as the same date (unlike ActiveSync) we need to subtract 1 day
            	                $endtime = $this->Date4Zimbra($except->endtime - 86400,  $tzName);
            	                $endtime = substr( $endtime, 0, 8 );
            	                $soap .= '<e d="'.$endtime.'"/> ';
                            } else {

                                if ($this->_zimbraVersion == '5.0' ) {
                                    $v5tz = $this->LookupV5Timezone( $tzName, "");
                                    if ($v5tz !== false) {
                                        $v5tzAttribute = ' tz="'.$v5tz.'" ';
                                    } else {
                                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  "Incoming Meeting v6 TimeZone [".$tzName."] NOT FOUND in v5timezone.xml - APPOINTMENT START/END TIMES WILL BE SAVED TO ZIMBRA WITH NO SET TIMEZONE"  );
                                        $v5tzAttribute = '';
                                    }

                                    //starttime
                                    $soap .= '<s '.$v5tzAttribute.' d="'.$this->Date4Zimbra($except->starttime, $tzName).'"/> ';

                                    //endtime
                                    $soap .= '<e '.$v5tzAttribute.' d="'.$this->Date4Zimbra($except->endtime, $tzName).'"/> ';
                                } else {
	            					//starttime
	            					$soap .= '<s tz="'.$tzName.'" d="'.$this->Date4Zimbra($except->starttime, $tzName).'"/> ';
					
	            					//endtime
	            					$soap .= '<e tz="'.$tzName.'" d="'.$this->Date4Zimbra($except->endtime, $tzName).'"/> ';
                                }
                            }  

                            if(isset($except->reminder)){
                            $soap .='<alarm action="DISPLAY">
                                        <trigger>
                                            <rel neg="1" m="'.$except->reminder.'" related="START"/>
                                        </trigger>
                                    </alarm> ';
                            }

                            //attendees
                            if(isset($except->attendees)) {
                                $attendees = $except->attendees;
                            } elseif(isset($preModAppt->attendees)) {
                                $attendees = $preModAppt->attendees;
                            }
                            if (isset($attendees)) {
                                foreach($attendees as $attendee) {
                                    if (isset($attendee->email)) {
                                        $soap .= '<at a="'.$attendee->email.'" ';
                                        if (isset($attendee->name)) {
                                            $soap .= ' d="'.$attendee->name.'" ';
                                        }

                                        if(isset($attendee->type) && $attendee->type == 2) {
                                            $soap .= 'role="OPT" ';
                                        } else {
                                            $soap .= 'role="REQ" ';
                                        }
                                        $soap .= 'ptst="NE" rsvp="1" />';
                                    }
                                }
                                unset( $attendees );
                                unset( $attendee );
                            }

                            // end <comp> & <inv>
                            $soap .= '</comp></inv>';

                            //subject
                            if (isset($except->subject) && trim($except->subject) != trim($input->subject)) {
                                $soap .= '<su>'.htmlspecialchars($except->subject).'</su>';
                            }

                            $notes = "";
                            $notesType = 1;
                            if ((Request::GetProtocolVersion() >= 12)) {
                                if (isset($except->asbody->data)) {
                                    if (is_resource($except->asbody->data)) {
                                        $notes = stream_get_contents($except->asbody->data);
                                        fclose($except->asbody->data);
                                        $notesType = $except->asbody->type;
                                    } else {
                                        $notes = $except->asbody->data;
                                        $notesType = $except->asbody->type;
                                    }
                                }
                            } else if ((Request::GetProtocolVersion() < 12) && isset($except->body) && ($except->body != "")) {
                                $notes = $except->body;
                            } else if(isset($except->rtf)) {
                                if (class_exists('rtf', false)) {
                                    // start decode RTF if present
                                    $rtf_body = new rtf ();
                                    $rtf_body->loadrtf(base64_decode($except->rtf));
                                    $rtf_body->output("ascii");
                                    $rtf_body->parse();
                                    $notes = w2ui( $rtf_body->out );
                                    unset( $rtf_body );
                                } else {
                                    $notes = "Missing include file z_RTF.php needed to Decode Notes";
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'RTF field could not be handled - '.$notes.' - See notes in zimbra.php' );
                                }
                            }

                            if ($notes <> "") {  // Notes changed
                                //plain text body unless type = 2 passed in.
                                if ($notesType == 1) {                    
                                    $soap .= '<mp ct="text/plain"><content>'."This instance of a recurring appointment has been changed \n\n".htmlspecialchars($notes).'</content></mp>';
                                } else {
                                    $soap .= '<mp ct="text/html"><content>'."This instance of a recurring appointment has been changed &lt;BR&gt;&lt;BR&gt;".htmlspecialchars($notes).'</content></mp>';
                                }
                            } 

                            //close <m>
                            $soap .= '</m>';

                            $soap .= '</CreateAppointmentExceptionRequest>';
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): SOAP ['.$soap.']' );

                            $returnJSON = true;
                            $response = $this->SoapRequest($soap, false, false, $returnJSON);
                            if($response) {
                                $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CreateApptException:' . print_r( $array, true ) );
                                unset($response);
                                if ($id == '') {
                                    $id = $array['Body']['CreateAppointmentExceptionResponse']['calItemId'];
            						unset($array);
                                } else {
                                    // Modifying existing appointment - Cannot apply TAGS to shared items - so don't even try
//                                    if (strrpos($id,':') === false) {
                                    if ('ME' == $idOwner) {
                                        $soap ='<ItemActionRequest xmlns="urn:zimbraMail">
                                                  <action id="'.$id.'" op="update" t="'.$inputtags.'" />
                                                </ItemActionRequest> ';
                                        $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                                        if($actionResponse) {
                                            $actionArray = json_decode($actionResponse, true);
                                            if (!isset($actionArray['Body']['ItemActionResponse']['action']['id']) or !isset($actionArray['Body']['ItemActionResponse']['action']['op'])) {
                                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage APPOINTMENT TAG update failed');
                                            }
                                        }
                                        unset($actionResponse);
                                    }
                                }
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { true }');
//                                return $this->StatMessage($this->_folders[$index]->devid, $id);
                            } else {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'SOAP ['.$soap.']' );
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage APPOINTMENT { false }');
                                return false;
                            }
                        }
                    }

                }

                foreach ($preModAppt->exceptions as $j=>$preModExcept) {
                      
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): preModExcept J ['.$j.'] needs to be deleted ???? - Should never happen as we should have received a "Delete" request for it' );
                }

                return $this->StatMessage($this->_folders[$index]->devid, $id);

                break;

            case 'task':

//                if (isset($input->categories) and (trim($input->categories) != "")) {
/*
                if (isset($input->categories) and is_array($input->categories)) {
                    $taskTagIds = $this->CategoriesToTags($input->categories);
				
                    $tasktags = implode( ",",$taskTagIds);
                    $tagattribute = ' t="'.$tasktags.'" ';

                } else {
                    $tasktags = "";
                    $tagattribute = "";
                }
*/
                $taskOrganizer = $this->_sendAsEmail;


                if($id == '') {
                    $soap = '<CreateTaskRequest xmlns="urn:zimbraMail"><m l="'.$zimbraFolderId.'" '.$inputattribute.' >';
                } else {
                    $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                               <m id="'.$id.'"></m>
                            </GetMsgRequest>';

                    $returnJSON = true;
                    $response = $this->SoapRequest($soap, false, false, $returnJSON);

                    if($response) {
                        $array = json_decode($response, true);
 		    	        unset($response); // We never use it again
                        $item = $array['Body']['GetMsgResponse']['m'][0];
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  print_r( $item, true ) );
                        unset($array);

                        if (isset($item['inv'][0]['comp'][0]['desc'][0]['_content'])) {
                            $originalPlainNotes = $item['inv'][0]['comp'][0]['desc'][0]['_content'];
                        }
                        if (isset($item['inv'][0]['comp'][0]['descHtml'][0]['_content'])) {
                            $originalHtmlNotes = $item['inv'][0]['comp'][0]['descHtml'][0]['_content'];
                        }
                        // Organizer Email
                        if(isset($item['inv'][0]['comp'][0]['or']['a'])) {
                            $taskOrganizer = $item['inv'][0]['comp'][0]['or']['a'];
                        }

                        if (isset($item['l'])) {
                            $originalFolderId = $item['l'];
                        } else {
                            $originalFolderId = $zimbraFolderId;
                        }
                        unset( $item );
                    }
					
                    $soap = '<ModifyTaskRequest xmlns="urn:zimbraMail" id="'.$id.'" comp="0"><m l="'.$originalFolderId.'" >';
//                    $soap = '<ModifyTaskRequest xmlns="urn:zimbraMail" id="'.$id.'" comp="0">';
                }

                $soap .= '<inv><comp ';

                /*  Sensitivity Status
                    [class="PUB|PRI|CON"]
                    PUB => 0 // Default
                    PRI => 1
                    CON => 2
                */
                if (isset($input->sensitivity)) {
                    if($input->sensitivity == 0) {
                        $soap .= 'class="PUB" ';
                    } else if($input->sensitivity == 1) {
                        $soap .= 'class="PRI" ';
                    } else if($input->sensitivity == 2) {
                        $soap .= 'class="CON" ';
                    }
                } else $soap .= 'class="PUB" ';

                if (isset($input->importance)) {
                    if($input->importance == 0) {
                        $soap .= 'priority="9" ';
                    } else if($input->importance == 2) {
                        $soap .= 'priority="1" ';
                    } else {
                        $soap .= 'priority="5" ';
                    }
                } else $soap .= 'priority="5" ';


                // allday
                //if($input->alldayevent == 0) {
                //    $soap .= 'allDay="0" ';
                //} else if($input->alldayevent == 1) {
                //    $soap .= 'allDay="1" ';
                //}

                // subject
                if(isset($input->subject)) {
                    $soap .= 'name="'.htmlspecialchars($input->subject).'" ';
                }

                // location
                if(isset($input->location)) {
                    $soap .= 'loc="'.$input->location.'" ';
                }

                //complete
                if(isset($input->complete) && $input->complete==1) {
                    $soap .= ' status="COMP" percentComplete="100" ';
                }


                // close <comp ... >
                $soap .= '> ';


                // WM and hopefully all non-Nokia phones send the startdate and enddate field
                // as UTC timestamps of 00:00:00 hours on the actual start date, 
                // and 00:00:00 hours on the actual due date. 
                // This is ideal for zimbra that stores task related as dates in YYYYMMDD
                // format with no time of day.
                //
                // They send utcstartdate and utcduedate as the correctly offset time for the 
                // local timezone in which the task was created. 
                //
                // Nokia at this time does not allow entry of a startdate - but seems to leave
                // it intact for tasks pushed out to it that contain one.
                // However, on the Due date, it has real problems. If a task is created on the 
                // Nokia phone, the enddate is sent correctly with the caveat that Nokia sends 
                // 23:59:00 on the due date instead of 00:00:00 which other phnoes send. 
                // On tasks created elsewhere, and subsequently edited on the phone it gets ugly.
                // It seems to do UTC-TZ manipulation on both the duedate and utcduedate fields
                // which results in the task end time creeping forward or backward depending on
                // your timezone offset from UTC.
                // For now - there is no reliable way to handle this situation. So just expect
                // potential problems updating tasks on a Nokia phone.
                // 
                // I have opened a ticket with Nokia to try to get this fixed.


                // startdate if populated is sure to give correct date
                // fallback to utcstartdate with risk that we have wrong TZ set
                if(isset($input->startdate)) {
                    $startdate = $this->Date4Zimbra($input->startdate, "XMLnoZ");
                    $startdate = substr( $startdate, 0, 8);
                    $soap .= '<s d="'.$startdate.'"/> ';
          	    } else if(isset($input->utcstartdate)) {
                    $startdate = $this->Date4Zimbra($input->utcstartdate, $this->_tz);
               	    $startdate = substr( $startdate, 0, 8);
                    $soap .= '<s d="'.$startdate.'"/> ';
              	}
              	
              	// for duedate we need to try to determine the phone type
                // Nokia first
                if (stripos($this->_ua, "MailforExchange") !== false) {
                    // duedate if populated is sure to give correct date
                    // fallback to utcduedate with risk that we have wrong TZ set
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Task DueDate - Nokia' );
                    if(isset($input->duedate)) {
                        $duedate = $this->Date4Zimbra($input->duedate, "XMLnoZ");
                        $duedate = substr( $duedate, 0, 8);
                        $soap .= '<e d="'.$duedate.'"/> ';
                    } else if(isset($input->utcduedate)) {
                        $duedate = $this->Date4Zimbra($input->utcduedate, $this->_tz);
                        $duedate = substr( $duedate, 0, 8);
                        $soap .= '<e d="'.$duedate.'"/> ';
                    }
                } else {
                    // non-Nokia 
                    // duedate if populated is sure to give correct date
                    // fallback to utcduedate with risk that we have wrong TZ set
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'Task DueDate - Non-Nokia' );
                    if(isset($input->duedate)) {
                        $duedate = $this->Date4Zimbra($input->duedate, "XMLnoZ");
                        $duedate = substr( $duedate, 0, 8);
                        $soap .= '<e d="'.$duedate.'"/> ';
                    } else if(isset($input->utcduedate)) {
                        $duedate = $this->Date4Zimbra($input->utcduedate, $this->_tz);
                        $duedate = substr( $duedate, 0, 8);
                        $soap .= '<e d="'.$duedate.'"/> ';
                    }
                }

                //organizer
                $soap .= '<or a="'.$taskOrganizer.'" sentBy="'.$this->_sendAsEmail.'"/> ';

                // CLIENT UID
                if (isset($input->uid)) {
                  $soap .='<xprop name="X-CLIENT-UID" value="'.base64_encode($input->uid).'"/>';
                }

// TODO - Figure out if we should store all the additional info - Reminders/ Date Completed/ etc. that phone could use.
//                //datecompleted
//                if(isset($input->datecompleted)) {
//                  $soap .= '<xprop name="datecompleted" value="'.$this->ParseDate($input->datecompleted, "toXML").'"/> ';
//                }
//                if (isset($input->reminderset) && $input->reminderset == 1) {
//                  $soap .='<alarm action="DISPLAY">
//                              <trigger>
//                                  <rel m="'.$input->remindertime.'" related="START"/>
//                              </trigger>
//                          </alarm> ';
//                  }

                // reminder time
                // TO DO - Find out if need to restrict to 7.0 onwards
                if (isset($input->reminderset) && ($input->reminderset == '1') && (floatval( $this->_zimbraVersion ) > 6.0) ) {
                    if (isset($input->remindertime)) {
                        $remindertime = $this->Date4Zimbra($input->remindertime, "XML") ;
                        $soap .= '<alarm action="DISPLAY"><trigger><abs d="'.$remindertime.'"/></trigger></alarm> ';
                    }
          	    } 

                // end <comp> & <inv>
                $soap .= '</comp></inv>';

                $notes = "";
                $notesType = 1;
                if ((Request::GetProtocolVersion() >= 12)) {
                    if (isset($input->asbody->data)) {
        				if (is_resource($input->asbody->data)) {
                            $notes = stream_get_contents($input->asbody->data);
                            fclose($input->asbody->data);
                            $notesType = $input->asbody->type;
                        } else {
                            $notes = $input->asbody->data;
                            $notesType = $input->asbody->type;
                        }
                    }
                } else if ((Request::GetProtocolVersion() < 12) && isset($input->body) && ($input->body != "")) {
                    $notes = $input->body;
                } else if(isset($input->rtf)) {
                    if (class_exists('rtf', false)) {
                        // start decode RTF if present
                        $rtf_body = new rtf ();
                        $rtf_body->loadrtf(base64_decode($input->rtf));
                        $rtf_body->output("ascii");
                        $rtf_body->parse();
                        $notes = w2ui( $rtf_body->out );
                        unset( $rtf_body );
                    } else {
                        $notes = "Missing include file z_RTF.php needed to Decode Notes";
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'RTF field could not be handled - '.$notes.' - See notes in zimbra.php' );
                    }
                }

                if ($notes <> "") {  // Notes changed
                    //plain text body unless type = 2 passed in.
                    if ($notesType == 1) {                    
                        $soap .= '<mp ct="text/plain"><content>'.htmlspecialchars($notes).'</content></mp>';
                    } else {
                        $soap .= '<mp ct="text/html"><content>'.htmlspecialchars($notes).'</content></mp>';
                    }
                } else {
                    if (isset($originalPlainNotes)) {
                        $soap .= '<mp ct="text/plain"><content>'.$originalPlainNotes.'</content></mp>';
                    } elseif (isset($originalHtmlNotes)) {
                        $soap .= '<mp ct="text/html"><content>'.$originalHtmlNotes.'</content></mp>';
                    }
                }

                //close <m>
                $soap .= '</m>';

                if($id == '') {
                    $soap .= '</CreateTaskRequest>';
                } else {
                    $soap .= '</ModifyTaskRequest>';
                }

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);
                if($response) {
                    $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CreateModifyTask:' . print_r( $array, true ) );
                    if ($id == '') {
                        unset($response);
                        $id = $array['Body']['CreateTaskResponse']['invId'];
                        unset($array);
                    } else {
                        // Modifying existing task - Cannot apply TAGS to shared items - so don't even try
//                        if (strrpos($id,':') === false) {
                        if ('ME' == $idOwner) {
                            $soap ='<ItemActionRequest xmlns="urn:zimbraMail">
                                      <action id="'.$id.'" op="update" t="'.$inputtags.'" />
                                    </ItemActionRequest> ';
                            $returnJSON = true;
                            $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                            if($actionResponse) {
                                $actionArray = json_decode($actionResponse, true);
                                if (!isset($actionArray['Body']['ItemActionResponse']['action']['id']) or !isset($actionArray['Body']['ItemActionResponse']['action']['op'])) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage ' . strtoupper($view) . ' TAG update failed');
                                }
                            }
                            unset($actionResponse);
                        }
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage ' . strtoupper($view) . ' { true }');
                    return $this->StatMessage($this->_folders[$index]->devid, $id);
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage ' . strtoupper($view) . ' { false }');
                    return false;
                }
                break;

            case 'note':

//                if (isset($input->categories) and (trim($input->categories) != "")) {
/*	
                if (isset($input->categories) and is_array($input->categories)) {
                    $taskTagIds = $this->CategoriesToTags($input->categories);
				
                    $tasktags = implode( ",",$taskTagIds);
                    $tagattribute = ' t="'.$tasktags.'" ';

                } else {
                    $tasktags = "";
                    $tagattribute = "";
                }
*/

                if($id == '') {
                    $soap = '<CreateTaskRequest xmlns="urn:zimbraMail"><m l="'.$zimbraFolderId.'" '.$inputattribute.' >';
                } else {
                    $soap ='<GetMsgRequest xmlns="urn:zimbraMail">
                               <m id="'.$id.'"></m>
                            </GetMsgRequest>';

                    $returnJSON = true;
                    $response = $this->SoapRequest($soap, false, false, $returnJSON);

                    if($response) {
                        $array = json_decode($response, true);
 		    	        unset($response); // We never use it again
                        $item = $array['Body']['GetMsgResponse']['m'][0];
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  print_r( $item, true ) );
                        unset($array);

                        if (isset($item['inv'][0]['comp'][0]['desc'][0]['_content'])) {
                            $originalPlainNotes = $item['inv'][0]['comp'][0]['desc'][0]['_content'];
                        }
                        if (isset($item['inv'][0]['comp'][0]['descHtml'][0]['_content'])) {
                            $originalHtmlNotes = $item['inv'][0]['comp'][0]['descHtml'][0]['_content'];
                        }
                        unset( $item );
                    }
					
                    $soap = '<ModifyTaskRequest xmlns="urn:zimbraMail" id="'.$id.'" comp="0"><m l="'.$zimbraFolderId.'" >';
                }

                $soap .= '<inv><comp ';

                //  Sensitivity Status
                $soap .= 'priority="5" ';

                // subject
                if(isset($input->subject)) {
                    $soap .= 'name="'.htmlspecialchars($input->subject).'" ';
                }
                $soap .= ' status="NEED" percentComplete="0" ';

                // close <comp ... >
                $soap .= '> ';

                if(isset($input->lastmodified)) {
                    $lastmodified = $this->Date4ActiveSync($input->lastmodified, "UTC");
                    $lastmodified = $lastmodified . "000";
                    $soap .= ' d="'.$lastmodified.'" ';
              	}
              	
                // end <comp> & <inv>
                $soap .= '</comp></inv>';

                $notes = "";
                $notesType = 1;
                if ((Request::GetProtocolVersion() >= 12)) {
                    if (isset($input->asbody->data)) {
        				if (is_resource($input->asbody->data)) {
                            $notes = stream_get_contents($input->asbody->data);
                            fclose($input->asbody->data);
                            $notesType = $input->asbody->type;
                        } else {
                            $notes = $input->asbody->data;
                            $notesType = $input->asbody->type;
                        }
                    }
                } else if ((Request::GetProtocolVersion() < 12) && isset($input->body) && ($input->body != "")) {
                    $notes = $input->body;
                } else if(isset($input->rtf)) {
                    if (class_exists('rtf', false)) {
                        // start decode RTF if present
                        $rtf_body = new rtf ();
                        $rtf_body->loadrtf(base64_decode($input->rtf));
                        $rtf_body->output("ascii");
                        $rtf_body->parse();
                        $notes = w2ui( $rtf_body->out );
                        unset( $rtf_body );
                    } else {
                        $notes = "Missing include file z_RTF.php needed to Decode Notes";
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'RTF field could not be handled - '.$notes.' - See notes in zimbra.php' );
                    }
                }

                if ($notes <> "") {  // Notes changed
                    //plain text body unless type = 2 passed in.
                    if ($notesType == 1) {                    
                        $soap .= '<mp ct="text/plain"><content>'.htmlspecialchars($notes).'</content></mp>';
                    } else {
                        $soap .= '<mp ct="text/html"><content>'.htmlspecialchars($notes).'</content></mp>';
                    }
                } else {
                    if (isset($originalPlainNotes)) {
                        $soap .= '<mp ct="text/plain"><content>'.$originalPlainNotes.'</content></mp>';
                    } elseif (isset($originalHtmlNotes)) {
                        $soap .= '<mp ct="text/html"><content>'.$originalHtmlNotes.'</content></mp>';
                    }
                }

                //close <m>
                $soap .= '</m>';

                if($id == '') {
                    $soap .= '</CreateTaskRequest>';
                } else {
                    $soap .= '</ModifyTaskRequest>';
                }

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);
                if($response) {
                    $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' .  'CreateModifyTask:' . print_r( $array, true ) );
                    if ($id == '') {
                        unset($response);
                        $id = $array['Body']['CreateTaskResponse']['invId'];
                        unset($array);
                    } else {
                        // Modifying existing task - Cannot apply TAGS to shared items - so don't even try
//                        if (strrpos($id,':') === false) {
                        if ('ME' == $idOwner) {
                            $soap ='<ItemActionRequest xmlns="urn:zimbraMail">
                                      <action id="'.$id.'" op="update" t="'.$inputtags.'" />
                                    </ItemActionRequest> ';
                            $returnJSON = true;
                            $actionResponse = $this->SoapRequest($soap, false, false, $returnJSON);
                            if($actionResponse) {
                                $actionArray = json_decode($actionResponse, true);
                                if (!isset($actionArray['Body']['ItemActionResponse']['action']['id']) or !isset($actionArray['Body']['ItemActionResponse']['action']['op'])) {
                                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'ChangeMessage ' . strtoupper($view) . ' TAG update failed');
                                }
                            }
                            unset($actionResponse);
                        }
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage ' . strtoupper($view) . ' { true }');
                    return $this->StatMessage($this->_folders[$index]->devid, $id);
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage ' . strtoupper($view) . ' { false }');
                    return false;
                }
                break;
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangeMessage(): ' . 'END ChangeMessage { false (invalid folder view) }');
        return false;
    } // end ChangeMessage


    /** CategoriesToTags
     *   If phone item has Categories associated, attach relevant Tags to the item on zimbra
	 *   creating any new Tags that are needed.
     */
    function CategoriesToTags($categories) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->CategoriesToTags(): ' .  'Categories '. print_r( $categories, true ) );
	
        $itemTagCount = count($categories);

        $itemTagIds = array();

        $userTagCount = count($this->_usertags);

        for ($i=0;$i<$itemTagCount;$i++) {
            $tagname = $categories[$i];
                  	
            $newtag = true;
            for ($j=0;$j<$userTagCount;$j++) {
                if ($tagname == $this->_usertags[$j]['name']) {
                    $itemTagIds[] = $this->_usertags[$j]['id'];
                    $newtag = false;
                    break;
                }
            }

            if (($newtag) && (trim($tagname) != "")) {
                // Default to Orange unless "Special Tags"
                $color = 0; // Orange;

                // Create User's New Tags
                $soap ='<CreateTagRequest xmlns="urn:zimbraMail">
                        <tag name="'.$tagname.'" color="'.$color.'" />
                        </CreateTagRequest>	';

                $returnJSON = true;
                $tagresponse = $this->SoapRequest($soap, false, false, $returnJSON);
                if($tagresponse) {
                    $newUserTag = json_decode($tagresponse, true);
                    unset($tagresponse);

                    if (isset($newUserTag['Body']['CreateTagResponse']['tag'][0])) {
                        $newUserTag = $newUserTag['Body']['CreateTagResponse']['tag'][0];

                        // Append the new tag to the user's tags.
                        if (!isset($this->_usertags)) $this->_usertags = array();
                          
                        $this->_usertags[] = $newUserTag;

                        $userTagCount = count($this->_usertags);
                        for ($j=0;$j<$userTagCount;$j++) {
                            if ($tagname == $this->_usertags[$j]['name']) {
                                $itemTagIds[] = $this->_usertags[$j]['id'];
                                break;
                            }
                        }

                    }
                }
                // End Create User's New Tags
            }
                    
        }
	  
        //ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->CategoriesToTags(): ' .  'Tag List ['.$itemTagIds.'] ' );
	    return $itemTagIds;
	  
	} // end CategoriesToTags
	
	
    /* SetMessageFlag should return message stats, analogous to the folder stats (StatFolder). Entries are:
     * 'id'   => Server unique identifier for the message. Again, try to keep this short (under 20 chars)
     * 'flag' => simply '0' for clear, '1' for set
     */
    public function SetMessageFlag($folderid, $id, $flags) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetMessageFlag(): ' . "START SetMessageFlag (folderid: '$folderid'  id: '$id'  flags: '$flags' [Clear-0/'' - Completed-1 - FollowUp-2])");

        $index = $this->GetFolderIndex($folderid);
        $view = $this->_folders[$index]->view;

        if ($this->_localCache) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetMessageFlag(): ' .  'CLEARING CACHE for folder ['.$folderid.']'  );
            unset($this->_cachedMessageLists[$folderid]);
            $this->_cachedMessageLists['changed'] = true;
        }

        if ($view == 'message') {

            if (($flags == '') || ($flags == 0) || ($flags == 1)) {
                // set as "NotFlagged" (clear the flag)
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetMessageFlag(): ' .  'Clearing Flag' );
                $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="!flag"/>
                         </MsgActionRequest>';
            } else {
                // set as "Flagged" (flagged)
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetMessageFlag(): ' .  'Setting Flag' );
                $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="flag"/>
                         </MsgActionRequest>';
            }
            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetMessageFlag(): ' . 'END SetMessageFlag { true }');
                return true;
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SetMessageFlag(): ' . 'END SetMessageFlag { false }');
                return false;
            }
        }

    }  // end SetMessageFlag


    /** MoveMessage
     *
     */
    public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'START MoveMessage { folderid = ' . $folderid. '; id = ' . $id . '; newfolderid = ' . $newfolderid . '}');

        if ($this->_localCache) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' .  'MoveMessage - CLEARING CACHE for folder ['.$folderid.']'  );
            unset($this->_cachedMessageLists[$folderid]);
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' .  'MoveMessage - CLEARING CACHE for folder ['.$newfolderid .']'  );
            unset($this->_cachedMessageLists[$newfolderid ]);
            $this->_cachedMessageLists['changed'] = true;
        }

        // check if the message is in the current syncinterval
        if (!$this->isZimbraObjectInSyncInterval($folderid, $id, $contentParameters))
            throw new StatusException(sprintf("Zimbra->DeleteMessage('%s'): Message is outside the sync interval and so far not deleted.", $id), SYNC_STATUS_OBJECTNOTFOUND);

        $index = $this->GetFolderIndex($folderid);
        $view = $this->_folders[$index]->view;

        $newindex = $this->GetFolderIndex($newfolderid);
        $newview = $this->_folders[$newindex]->view;

        if ($this->GetWasteBasket() == $newfolderid) {
            // Trash folder can accomodate any content
        } else if ($newview != $view) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage { Origin Folder Type ['.$view.'] DOES NOT MATCH Destination ['.$newview.'] }');
            return false;
        }

        // When Zimbra moves an item from a folder owned by one UserID to a folder owned by another UserID
        // it assigns the item a new ID in the recipients mailbox. There is no way to return this updated ID
        // to this function - so if we allow the move to proceed, we end up with an incorrect item ID in the 
        // recipient folder. There is also no way to easily tell the client to refresh both folders if they 
        // are not both Ping'ed folders. Any attempt to perform a further action on the moved item will
        // generate a SOAP Error. SO, THE SAFEST APPROACH IS TO PREVENT MOVING ITEMS BETWEEN ACCOUNTS
        // All linked folders have an id of the form [Folder Type][Owner Identifier]-[Owner's Folder ID]
        // For example, FL0-2. All folders with no '-' in the folderId are owned by the synching user.
        if (strpos($folderid, '-') === false) {
            $owner = 'ME';
        } else {
            $folderOwnerId = explode( '-', $folderid );
            $owner = $folderOwnerId[0];
        }

        if (strpos($newfolderid, '-') === false) {
            $newowner = 'ME';
        } else {
            $folderOwnerId = explode( '-', $newfolderid );
            $newowner = $folderOwnerId[0];
        }
        if ($newowner != $owner) {
/*
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage { PREVENTED FROM MOVING ITEMS BETWEEN ACCOUNTS }');
            $clearCacheList = array();
            $clearCacheList[] = $this->changesSinkFolders[$mods[$i]['id']];
            $this->ClearCache( $clearCacheList );
            throw new StatusException("Zimbra->ChangeMessage(): PREVENTED FROM MOVING ITEMS BETWEEN ACCOUNTS ", SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR);
            return false;
*/
        }

        // OK to proceed - so get the real Zimbra folder id
        $zimbraFolderId = $this->_folders[$newindex]->id;

        switch ($view) {
            case 'message':
                $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                            <action id="' . $id . '" op="move" l="' . $zimbraFolderId . '"/>
                         </MsgActionRequest>';

                $section = 'MsgActionResponse';
                break;
            case 'contact':
                $soap = '<ContactActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="move" l="'.$zimbraFolderId.'"/>
                        </ContactActionRequest>';
                $section = 'ContactActionResponse';
                break;

            case 'appointment':

                $soap = '<ItemActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="move" l="'.$zimbraFolderId.'"/>
                        </ItemActionRequest>';
                $section = 'ItemActionResponse';

                // If moving an appointment to the waste basket - we need to issue a CancelAppointmentRequest instead
                if ($this->GetWasteBasket() == $newfolderid) {
                    if (!$contentParameters) {
                        // For z-push 2 releases 2.0.7 and earlier Content Parameters are not being set before the call to ChangeMessage
                        // Set it here to prevent GetMessage from crashing with the following message
                        // PHP Fatal error:  Call to a member function GetContentClass() on a non-object
                        $contentParameters = new ContentParameters();
                        $contentParameters->SetContentClass('appointment');
                        $bodyPreference = new BodyPreference();
                        $bodyPrefArray = array();
                        $bodyPrefArray[1] = $bodyPreference;

                        $contentParameters->bodypref = $bodyPrefArray;
                    }

                    $preModAppt = $this->GetMessage($folderid, $id, $contentParameters); 
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' .  'GetApptReq Original Appt: ' . print_r( $preModAppt, true ), false );

                    if ($preModAppt->isorganizer == 1) {
                        $invId = $preModAppt->zimbraInvId;

                        $soap  = '<CancelAppointmentRequest xmlns="urn:zimbraMail" id="'.$preModAppt->zimbraInvId.'" comp="'.$preModAppt->zimbraCompNum.'" >';
                        $soap .= '<m>';
                        if (isset($preModAppt->attendees)) {
                            foreach($preModAppt->attendees as $attendee) {
                                if (isset($attendee->email)) {
                                    $soap .= '<e a="'.$attendee->email.'" ';
                                    if (isset($attendee->name)) {
                                        $soap .= ' p="'.$attendee->name.'" ';
                                    }
                                    $soap .= ' t="t" />';
                                }
                            }
                            unset( $attendee );
                        }
                        $soap .= '<su>*Cancelled* ' . htmlspecialchars($preModAppt->subject) . '</su>';

                        $soap .= '<mp ct="text/plain"><content>'.'The following appointment has been cancelled:' . htmlspecialchars($preModAppt->subject) . "\n\n";
                        if (isset($preModAppt->zimbraPlainNotes)) {
                            $soap .= "******************************\n\n" .htmlspecialchars($preModAppt->zimbraPlainNotes);
                        }
                        $soap .= '</content></mp>';
                        $soap .= '</m>';
                        $soap .= '</CancelAppointmentRequest>';
                        $section = 'CancelAppointmentRequest';
                    }
                }
                break;
				
            case 'task':
                $soap = '<ItemActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="move" l="'.$zimbraFolderId.'"/>
                        </ItemActionRequest>';
                $section = 'ItemActionResponse';
                break;
            case 'note':
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage - Move NOT Supported for NOTES { false }');
                return false;
                break;
        }

        if($soap) {
            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);
				
                if (isset($array['Body']['CancelAppointmentResponse'])) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage / CancelAppointmentRequest { true }');
                    return true;
                } elseif (isset($array['Body'][$section]['action']['id'])) {
                    $newid = $array['Body'][$section]['action']['id'];

                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage { new? id='.$newid.' }');
                    return $newid;
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage { false }');
                    return false;
                }
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage { false }');
                return false;
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MoveMessage(): ' . 'END MoveMessage { false ( Error ) }');
        return false;
    } // end MoveMessage


    /** DeleteMessage
     *
     */
    public function DeleteMessage($folderid, $id, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' . 'START DeleteMessage { folderid = ' . $folderid . '; id = ' . $id . '; contentParameters = ' . print_r( $contentParameters, true ) . ' }');


        if ($this->_localCache) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' .  'DeleteMessage - CLEARING CACHE for folder ['.$folderid.']'  );
            unset($this->_cachedMessageLists[$folderid]);
            $this->_cachedMessageLists['changed'] = true;
        }

        // check if the message is in the current syncinterval
        if (!$this->isZimbraObjectInSyncInterval($folderid, $id, $contentParameters))
            throw new StatusException(sprintf("Zimbra->DeleteMessage('%s'): Message is outside the sync interval and so far not deleted.", $id), SYNC_STATUS_OBJECTNOTFOUND);

        $index = $this->GetFolderIndex($folderid);
        $view = $this->_folders[$index]->view;

        switch ($view) {
            case 'message':
                if ($folderid == $this->_wasteID) {
                    $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                                <action id="' . $id . '" op="delete"/>
                            </MsgActionRequest>';
                    $section = 'MsgActionResponse';
                    $op = 'delete';
                } else {
                    $soap = '<MsgActionRequest xmlns="urn:zimbraMail">
                                <action id="' . $id . '" op="trash"/>
                            </MsgActionRequest>';
                    $section = 'MsgActionResponse';
                    $op = 'trash';
                }
                break;

            case 'contact':
                $soap = '<ContactActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="trash"/>
                        </ContactActionRequest>';
                $section = 'ContactActionResponse';
                $op = 'trash';
                break;

            case 'appointment':

                $soap = '<ItemActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="trash"/>
                        </ItemActionRequest>';
                $section = 'ItemActionResponse';
                $op = 'trash';

                if (!$contentParameters) {
                    // For z-push 2 releases 2.0.7 and earlier Content Parameters are not being set before the call to ChangeMessage
                    // Set it here to prevent GetMessage from crashing with the following message
                    // PHP Fatal error:  Call to a member function GetContentClass() on a non-object
                    $contentParameters = new ContentParameters();
                    $contentParameters->SetContentClass('appointment');
                    $bodyPreference = new BodyPreference();
                    $bodyPrefArray = array();
                    $bodyPrefArray[1] = $bodyPreference;

                    $contentParameters->bodypref = $bodyPrefArray;
                }

                $preModAppt = $this->GetMessage($folderid, $id, $contentParameters); 
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' .  'GetApptReq Original Appt: ' . print_r( $preModAppt, true ), false );

                if ($preModAppt->isorganizer == 1) {
                    $invId = $preModAppt->zimbraInvId;

                    $soap  = '<CancelAppointmentRequest xmlns="urn:zimbraMail" id="'.$preModAppt->zimbraInvId.'" comp="'.$preModAppt->zimbraCompNum.'" >';
                    $soap .= '<m>';
                    if (isset($preModAppt->attendees)) {
                        foreach($preModAppt->attendees as $attendee) {
                            if (isset($attendee->email)) {
                                $soap .= '<e a="'.$attendee->email.'" ';
                                if (isset($attendee->name)) {
                                    $soap .= ' p="'.$attendee->name.'" ';
                                }
                                $soap .= ' t="t" />';
                            }
                        }
                        unset( $attendee );
                    }
                    $soap .= '<su>*Cancelled* ' . htmlspecialchars($preModAppt->subject) . '</su>';
                    $soap .= '<mp ct="text/plain"><content>' . 'The following appointment has been cancelled: ' . htmlspecialchars($preModAppt->subject) . "\n\n";
                    if (isset($preModAppt->zimbraPlainNotes)) {
                        $soap .= "******************************\n\n" .htmlspecialchars($preModAppt->zimbraPlainNotes);
                    }
                    $soap .= '</content></mp>';
                    $soap .= '</m>';
                    $soap .= '</CancelAppointmentRequest>';
                    $section = 'CancelAppointmentRequest';
                }

                break;

            case 'task':
            case 'note':
                $soap = '<ItemActionRequest xmlns="urn:zimbraMail">
                            <action id="'.$id.'" op="trash"/>
                        </ItemActionRequest>';
                $section = 'ItemActionResponse';
                $op = 'trash';
                break;
        }

        if($soap) {
            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);

                if (isset($array['Body']['CancelAppointmentResponse'])) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' . 'END DeleteMessage / CancelAppointmentRequest { true }');
                    return true;
                } elseif (isset($array['Body'][$section]['action']['op']) && ($array['Body'][$section]['action']['op'] == $op)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' . 'END DeleteMessage { true }');
                    return true;
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' . 'END DeleteMessage { false }');
                    return false;
                }
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' . 'END DeleteMessage { false }');
                return false;

            }
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->DeleteMessage(): ' . 'END DeleteMessage { false (no soap) }');
        return false;
    } // end DeleteMessage


    /**
     * Checks if a message is in the synchronization interval (window)
     * if a filter (e.g. Sync items two weeks back) or limits this synchronization.
     * These checks only apply to Emails and Appointments only, Contacts, Tasks and Notes do not have time restrictions.
     *
     * @param string     $messageid        the message id to be checked
     *
     * @access private
     * @return boolean
     */
    private function isZimbraObjectInSyncInterval($folderid, $id, $contentParameters) {

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->isZimbraObjectInSyncInterval(): ' . 'START isZimbraObjectInSyncInterval { folderid = ' . $folderid . '; id = ' . $id . '; contentParameters = <hidden> }');

        // if there is no restriciton we do not need to check
        $filtertype = $contentParameters->GetFilterType();
        switch($contentParameters->GetContentClass()) {
            case "Email":
                $cutoffdate = ($filtertype) ? Utils::GetCutOffDate($filtertype) : false;

                $soap = '<GetItemRequest xmlns="urn:zimbraMail">
                             <item id="'.$id.'"  />
                         </GetItemRequest>';
                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);

                if($response) {
                    $array = json_decode($response, true);

                    // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
                    unset($response);

                    if (!isset($array['Body']['GetItemResponse']['m'][0])) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Message not found!", $id));
                        return false;
                    }
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Error searching for Message!", $id));
                    return false;
				}

                $zimbraFolderId = $array['Body']['GetItemResponse']['m'][0]['l'];
                $limit = 500;
                $offset = 0;
                $more = false;

                do {
                    if ($cutoffdate != "0") {
                        $zimbraCutOffDate = strftime("%m/%d/%Y", $cutoffdate-86400);
                        $soap ='<SearchRequest xmlns="urn:zimbraMail" types="message" fetch="'.$id.'" limit="'.$limit.'" offset="'.$offset.'" resultMode="IDS" >
                                    <query>inid:"'.$zimbraFolderId.'" AND after:"' . $zimbraCutOffDate . '"</query>
                                    <locale>en_US</locale>
                                </SearchRequest>';
                    } else {
                        $soap ='<SearchRequest xmlns="urn:zimbraMail" types="message" fetch="'.$id.'" limit="'.$limit.'" offset="'.$offset.'" resultMode="IDS" >
                                    <query>inid:"'.$zimbraFolderId.'"</query>
                                </SearchRequest>';
                    }

                    $returnJSON = true;
                    $response = $this->SoapRequest($soap, false, false, $returnJSON);

                    if($response) {
                        $array = json_decode($response, true);

                        // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
                        unset($response);

                        if (isset($array['Body']['SearchResponse']['hit'])) {
  	                        $items = $array['Body']['SearchResponse']['hit'];
    	                    $total = count($items);
    	                } else $total = 0;

                        for ($i=0;$i<$total;$i++) {
                            if ($id == $items[$i]['id']) {
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Message found in current window!", $id));
                                return true;
                                break;
                            }
                        }

                        // If More Then Limit, Loop Through
                        $more = (isset($array['Body']['SearchResponse']['more']) && $array['Body']['SearchResponse']['more'] == '1');
                        $offset = $offset + $limit;
                    }
                    else {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->isZimbraObjectInSyncInterval(): ' . 'END isZimbraObjectInSyncInterval { ERROR }');
                        return false;
                    }
                } while ($more);

                ZLog::Write(LOGLEVEL_DEBUG,  sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Appointment NOT FOUND in current window!", $id));
                return false;

                break;

            case "Calendar":
                $cutoffdate = ($filtertype) ? Utils::GetCutOffDate($filtertype) : false;

                $soap = '<GetItemRequest xmlns="urn:zimbraMail">
                             <item id="'.$id.'"  />
                         </GetItemRequest>';
                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);

                if($response) {
                    $array = json_decode($response, true);

                    // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
                    unset($response);

                    if (!isset($array['Body']['GetItemResponse']['appt'][0])) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Appointment not found !", $id));

                        return false;
                    }
                }

                $zimbraFolderId = $array['Body']['GetItemResponse']['appt'][0]['l'];
                $limit = 500;
                $offset = 0;
                $more = false;

                do {
                    // maximum 366 days in the future
                    // TO DO - Where did 366 come from ?
                    if ($cutoffdate != "0") {
                        $calExpandInstStart = strval($cutoffdate) ."000";
                        $calExpandInstEnd = strval(time() + (366*24*60*60)) ."000";
                        $soap ='<SearchRequest types="appointment" xmlns="urn:zimbraMail" fetch="'.$id.'" limit="'.$limit.'" offset="'.$offset.'" '.
                                    ' calExpandInstStart="'.$calExpandInstStart.'" calExpandInstEnd="'.$calExpandInstEnd.'"  resultMode="IDS" >
                                    <query>inid:"'.$zimbraFolderId.'" </query>
                                </SearchRequest>';
                    } else {
                        $calExpandInstStart = strval(time() - (366*24*60*60)) ."000";
                        $calExpandInstEnd = strval(time() + (366*24*60*60)) ."000";
                        $soap ='<SearchRequest types="appointment" xmlns="urn:zimbraMail" fetch="'.$id.'" limit="'.$limit.'" offset="'.$offset.'" '.
                                    ' calExpandInstStart="'.$calExpandInstStart.'" calExpandInstEnd="'.$calExpandInstEnd.'" resultMode="IDS" >
                                    <query>inid:"'.$zimbraFolderId.'"</query>
                                </SearchRequest>';
                    }

                    $returnJSON = true;
                    $response = $this->SoapRequest($soap, false, false, $returnJSON);

                    if($response) {
                        $array = json_decode($response, true);

                        // Unset here - as the function can be called recursively below. Best not to carry multiple copies of $response
                        unset($response);

                        if (isset($array['Body']['SearchResponse']['hit'])) {
  	                        $items = $array['Body']['SearchResponse']['hit'];
    	                    $total = count($items);
    	                } else $total = 0;

                        for ($i=0;$i<$total;$i++) {
                            if ($id == $items[$i]['id']) {
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Appointment found in current window!", $id));
                                return true;
                            }
                        }

                        // If More Then Limit, Loop Through
                        $more = (isset($array['Body']['SearchResponse']['more']) && $array['Body']['SearchResponse']['more'] == '1');
                        $offset = $offset + $limit;
                    }
                    else {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->isZimbraObjectInSyncInterval(): ' . 'END isZimbraObjectInSyncInterval { ERROR }');
                        return false;
                    }
                } while ($more);

                ZLog::Write(LOGLEVEL_DEBUG,  sprintf("Zimbra->isZimbraObjectInSyncInterval('%s'): Appointment NOT FOUND in current window!", $id));
                return false;

                break;
            default:
            case "Contacts":
            case "Tasks":
            case "Notes":
            case "Note":
                $cutoffdate = false;
                break;
        }

        if ($cutoffdate === false)
            return true;

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->isZimbraObjectInSyncInterval(): ' . 'END isZimbraObjectInSyncInterval { ERROR }');
        return true;
    }



    /** ResolveRecipients
     *
     */
    function ResolveRecipients($resolveRecipients) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'START ResolveRecipient { resolveRecipients = ' . '<hidden>' . ' }');
//        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'ResolveRecipients ...' .print_r( $resolveRecipients, true ) );

        $zPushOfficialSupport = (class_exists('SyncResolveRecipientsResponse'));
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'zPushOfficialSupport [' . $zPushOfficialSupport . ']' );

        if (!$zPushOfficialSupport && !class_exists('SyncResolveRecipientResponse')) {
            ZLog::Write(LOGLEVEL_WARN, "Class SyncResolveRecipientResponse not available in z-push yet - Cannot resolve recipients !");
            // return a SyncResolveRecipients object so that sync doesn't fail
            $r = new SyncResolveRecipients();
            $r->status = SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR;
            $r->recipient = array();
            return $r;
        }

        if (!$resolveRecipients instanceof SyncResolveRecipients) {
            ZLog::Write(LOGLEVEL_WARN, "Not a valid SyncResolveRecipients object.");
            // return a SyncResolveRecipients object so that sync doesn't fail
            $r = new SyncResolveRecipients();
            $r->status = SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR;
            $r->recipient = array();
            return $r;
        }


        $recipCount = count( $resolveRecipients->to );
        if (isset($resolveRecipients->options)) {
            if ($zPushOfficialSupport) {
                $rrOptions = new SyncResolveRecipientsOptions();
            } else {
                $rrOptions = new SyncRROptions();
            }
            $rrOptions = $resolveRecipients->options;
            if (isset($rrOptions->maxambiguousrecipients) && $rrOptions->maxambiguousrecipients > 0) { 
                $limit = $rrOptions->maxambiguousrecipients;
            } else {
                $limit = 19; // default to 19
            }
            if (isset($rrOptions->availability)) {
                $availability = 'availability';
                if ($zPushOfficialSupport) {
                    $rrAvailability = new SyncResolveRecipientsAvailability();
                } else {
                    $rrAvailability = new SyncRRAvailability();
                }
                $rrAvailability = $rrOptions->availability;

                $starttime = str_replace( "-", "", str_replace( ":", "", $rrAvailability->starttime) );
                $starttime = $this->Date4ActiveSync( $starttime, "UTC" );
                $endtime = str_replace( "-", "", str_replace( ":", "", $rrAvailability->endtime) );
                $endtime = $this->Date4ActiveSync( $endtime, "UTC" );

                $duration = $endtime - $starttime;
                $timeslots = intval( $duration / 1800);
                if (($duration % 1800) != 0) $timeslots += 1;

                if ($timeslots > 32767) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' .  'Availability Request would generate too many timeslots - returning Status=5' );
                    $resolveRecipients->status = SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR;
                    return $resolveRecipients;
                }

            }
            if (isset($rrOptions->certificateretrieval)) {
                $certificateretrieval = $rrOptions->certificateretrieval;
                if (isset($rrOptions->maxcertificates)) {
                    $maxcertificates = $rrOptions->maxcertificates;
                } else {
                    $maxcertificates = 9999;
                }
            }
        }

        $resolveRecipients->status = SYNC_COMMONSTATUS_SUCCESS;
        if ($zPushOfficialSupport) {
            $resolveRecipients->response = array();
        } else {
            $resolveRecipients->recipientresponse = array();
        }

        for ($recip=0;$recip<$recipCount; $recip++) {
		    $recipient = $resolveRecipients->to[$recip];

            if ($zPushOfficialSupport) {
                $recipientResponse = new SyncResolveRecipientsResponse();
            } else {
                $recipientResponse = new SyncResolveRecipientResponse();
            }
            $recipientResponse->to = $recipient;
            $recipientResponse->status = SYNC_COMMONSTATUS_SUCCESS;

            $found = false;

            // First look in the GAL
            $soap = '<SearchGalRequest  type="all"  limit="'.$limit.'"  xmlns="urn:zimbraAccount" >
                         <name>'. $recipient .'</name>
                     </SearchGalRequest>';

            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);

                if ( isset( $array['Body']['SearchGalResponse']['cn'] )) {
                    $galEntry = $array['Body']['SearchGalResponse']['cn'];

                    $recipientResponse->recipientcount = count($galEntry);
                    $recipientResponse->recipient = array();
                    $limit = $limit - count($galEntry);   // Further limit the number of Contacts returned

                    unset($array);


                    for ($i=0;$i<count($galEntry);$i++) {
                        $thisGalEntry = new SyncResolveRecipient();

                        if (isset($galEntry[$i]['_attrs']['fullName'])) {
                            $thisGalEntry->displayname = $galEntry[$i]['_attrs']['fullName'];
                        } elseif (isset($galEntry[$i]['_attrs']['firstName'])) {
                            $thisGalEntry->displayname = $galEntry[$i]['_attrs']['firstName'];
                            if (isset($galEntry[$i]['_attrs']['lastName'])) {
                                $thisGalEntry->displayname .= ' ' . $galEntry[$i]['_attrs']['lastName'];
                            }
                        } elseif (isset($galEntry[$i]['_attrs']['lastName'])) {
                            $thisGalEntry->displayname = $galEntry[$i]['_attrs']['lastName'];
                        } 
                        if (isset($galEntry[$i]['_attrs']['email'])) {
                            $thisGalEntry->emailaddress = $galEntry[$i]['_attrs']['email'];
                        }

                        $thisGalEntry->type = SYNC_RESOLVERECIPIENTS_TYPE_GAL;

                        $recipientResponse->recipient[] = $thisGalEntry;
                        unset($thisGalEntry);
                    }
                    $found = true;
                }

            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'END ResolveRecipients { false (SOAP Error) }');
                $resolveRecipients->status = SYNC_RESOLVERECIPSSTATUS_SERVERERROR;
                return $resolveRecipients;

            }


            // Next, Search Contacts
            $soap = '<SearchRequest  limit="'.$limit.'" types="contact"  xmlns="urn:zimbraMail" >
                         <query>'. $recipient .'</query>
                     </SearchRequest>';

            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' .  'SEARCH RESULTS' . print_r( $array, true ) );

                if ( isset( $array['Body']['SearchResponse']['cn'] )) {
                    $contactEntry = $array['Body']['SearchResponse']['cn'];

                    $recipientResponse->recipientcount = (isset($recipientResponse->recipientcount) ? ($recipientResponse->recipientcount + count($contactEntry)) : count($contactEntry));
                    if (!isset($recipientResponse->recipient)) {
                        $recipientResponse->recipient = array();
                    }
                    unset($array);

                    for ($i=0;$i<count($contactEntry);$i++) {
                        if (isset($contactEntry[$i]['_attrs']['type']) && $contactEntry[$i]['_attrs']['type'] == 'group') {  // Skip GROUPS for now
                            $recipientResponse->recipientcount -= 1;
							continue;
                        }
                        
                        $thisContactEntry = new SyncResolveRecipient();

                        if (isset($contactEntry[$i]['_attrs']['fullName'])) {
                            $thisContactEntry->displayname = $contactEntry[$i]['_attrs']['fullName'];
                        } elseif (isset($contactEntry[$i]['_attrs']['firstName'])) {
                            $thisContactEntry->displayname = $contactEntry[$i]['_attrs']['firstName'];
                            if (isset($contactEntry[$i]['_attrs']['lastName'])) {
                                $thisContactEntry->displayname .= ' ' . $contactEntry[$i]['_attrs']['lastName'];
                            }
                        } elseif (isset($contactEntry[$i]['_attrs']['lastName'])) {
                            $thisContactEntry->displayname = $contactEntry[$i]['_attrs']['lastName'];
                        } 
                        if (isset($contactEntry[$i]['_attrs']['email'])) {
                            $thisContactEntry->emailaddress = $contactEntry[$i]['_attrs']['email'];
                        }

                        $thisContactEntry->type = SYNC_RESOLVERECIPIENTS_TYPE_CONTACT;

                        $recipientResponse->recipient[] = $thisContactEntry;
                        unset($thisContactEntry);
                    }
                    $found = true;
                }

            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'END ResolveRecipients { false (SOAP Error) }');
                $resolveRecipients->status = SYNC_RESOLVERECIPSSTATUS_SERVERERROR;
                return $resolveRecipients;

            }


            if ($found) {
                $recipientResponse->recipientcount = count($recipientResponse->recipient);

                if (isset($availability)) {

                        $possibleRecipientsFound = count($recipientResponse->recipient);
                        $recipientList = "";
                        for ($i = 0; $i < $possibleRecipientsFound; $i++) {
                            $recipientList .= $recipientResponse->recipient[$i]->emailaddress . ",";
							
                            if ($zPushOfficialSupport) {
                                $recipientResponse->recipient[$i]->availability = new SyncResolveRecipientsAvailability();
                            } else {
                                $recipientResponse->recipient[$i]->availability = new SyncRRAvailability();
                            }
                            $recipientResponse->recipient[$i]->availability->status = 163;
                        }
                        $soap = '<GetFreeBusyRequest  s="'.$starttime.'000" e="'.$endtime.'000" name="'.$recipientList.'"  xmlns="urn:zimbraMail" />';

                        $returnJSON = true;
                        $response = $this->SoapRequest($soap, false, false, $returnJSON);
                        if($response) {
                            $array = json_decode($response, true);
                            unset($response);

                            if ( isset( $array['Body']['GetFreeBusyResponse']['usr'][0] )) {
                                $countFB = count($array['Body']['GetFreeBusyResponse']['usr']);
							} else {
                                $countFB = 0;
                            }
							
                            for ($fb = 0; $fb < $countFB; $fb++) {
                                $mergedFreeBusy = str_pad('4', $timeslots, '4' );

                                $userResponse = $array['Body']['GetFreeBusyResponse']['usr'][$fb];
							
                                $fbaSoapKeyResponseKey = array( 'n'=>'4', 'f'=>'0', 't'=>'1', 'b'=>'2', 'u'=>'3' );

                                foreach ($fbaSoapKeyResponseKey as $soapKey=>$responseKey) {
                                    if (isset( $userResponse[$soapKey] )) {
                                        for ($i=0;$i<count($userResponse[$soapKey]);$i++) {
                                            $halfHourStart = $starttime;

                                            $periodStart = substr( $userResponse[$soapKey][$i]['s'], 0, 10);
                                            $periodEnd = substr( $userResponse[$soapKey][$i]['e'], 0, 10);

                                            for ($j=0;$j<=$timeslots;$j++) {
                                                if (($periodStart < $halfHourStart+1800) && ($periodEnd > $halfHourStart)) {
                                                    $mergedFreeBusy[$j] = $responseKey;
                                                }
                                                $halfHourStart += 1800;
                                            }
                                        }
                                    }
                                }

                                for ($i = 0; $i < $possibleRecipientsFound; $i++) {
                                    if ($recipientResponse->recipient[$i]->emailaddress == $userResponse['id']) {
							
                                        $recipientResponse->recipient[$i]->availability->status = 1;
                                        $recipientResponse->recipient[$i]->availability->mergedfreebusy = $mergedFreeBusy;
                                    }
                                }


                                unset( $soapKey );
                                unset( $responseKey );
                            }
                            unset($array);

                        } else {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'END ResolveRecipients { false (SOAP Error) }');
                            $resolveRecipients->status = SYNC_RESOLVERECIPSSTATUS_SERVERERROR;
                            return $resolveRecipients;
                        }
                }

                if (isset($certificateretrieval)) {
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' .  'Resolve Certificate' );

/*
			$result = $backend->resolveRecipient('certificate',$item,array('maxcerts' => $options[SYNC_RESOLVERECIPIENTS_MAXCERTIFICATES],
																		   'maxambigious' => $options[SYNC_RESOLVERECIPIENTS_MAXAMBIGUOUSRECIPIENTS],
																		   )
			$results[$item] = $result[$item];
*/
                    if (isset( $data['maxcerts'] )) {
                        $maxcerts = $data['maxcerts'];
                    } else {
                        $maxcerts = '';
                    }
                    if (isset( $data['maxambigious'] )) {
                        $maxambigious = $data['maxambigious'];
                    } else {
                        $maxambigious = '';
                    }
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' .  'Maxcerts ['.$maxcerts.'] - Maxambigious ['.$maxambigious.']' );

                    $possibleRecipientsFound = count($recipientResponse->recipient);
                    for ($i = 0; $i < $possibleRecipientsFound; $i++) {
                            if ($zPushOfficialSupport) {
                                $recipientResponse->recipient[$i]->certificates = new SyncResolveRecipientsCertificates();
                            } else {
                                $recipientResponse->recipient[$i]->certificates = new SyncRRCertificates();
                            }
                        $recipientResponse->recipient[$i]->certificates->status = SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_NOVALIDCERT;
                    }
                }
            } else {
                $recipientResponse->status = SYNC_RESOLVERECIPSSTATUS_RESPONSE_UNRESOLVEDRECIP;
            }
            if ($zPushOfficialSupport) {
                $resolveRecipients->response[$recip] = $recipientResponse;
            } else {
                $resolveRecipients->recipientresponse[$recip] = $recipientResponse;
            }
            unset($recipientResponse);

        }
		if  (($zPushOfficialSupport && (count($resolveRecipients->response) > 0)) or (!$zPushOfficialSupport && (count($resolveRecipients->recipientresponse) > 0))) { 
            $resolveRecipients->status = SYNC_RESOLVERECIPSSTATUS_SUCCESS;

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'END ResolveRecipients { true }');
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'resolveRecipients RETURN' . print_r( $resolveRecipients, true ), false);
            return $resolveRecipients;
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ResolveRecipients(): ' . 'END ResolveRecipients { false (no soap) }');
        return false;
    } // end ResolveRecipients


    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        if (!$this->notifications) {
            ZLog::Write(LOGLEVEL_DEBUG, "Zimbra->HasChangesSink(): sink is not available");
            return false;
        }

        ZLog::Write(LOGLEVEL_DEBUG, "Zimbra->HasChangesSink(): TRUE");
        return true;
    }

    /**
     * The folder should be considered by the sink.
     * Folders which were not initialized should not result in a notification
     * of IBacken->ChangesSink().
     *
     * @param string        $folderid
     *
     * @access public
     * @return boolean      false if zimbraFolderId can not be found for that folder
     */
    public function ChangesSinkInitialize($folderid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->ChangesSinkInitialize(): folderid '%s'", $folderid));

        $index = $this->GetFolderIndex($folderid);

        $zimbraFolderId = $this->_folders[$index]->id;
        if (!$zimbraFolderId) 
            return false;

        // add zimbraFolderId to the monitored folders
        $this->changesSinkFolders[$zimbraFolderId] = $folderid;

        $zimbraLinkId = $this->_folders[$index]->linkid;
        if ($zimbraLinkId != "") {
            // For shared folders the notified id could be the link - so need to also add zimbraLinkId to the monitored folders
            $this->changesSinkFolders[$zimbraLinkId] = $folderid;
        }

        // check if this store is already monitores, else advise it
        if (!in_array($this->store, $this->changesSinkStores)) {
            $this->changesSinkStores[] = $this->store;
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Zimbra->ChangesSinkInitialize(): advised store '%s'", $this->store));
        }
        return true;
    }


    /**
     * Function ChangesSinkNotify.
     * As there are multiple zimbra calls within each ChangesSink, and we need to look for a notify after each, breaking out this code will keep the code cleaner
     *
     * @param string          $CalledAfter        name of the preceding zimbra request
     * @param array           $notify             notify block returned from zimbra
     * @param array           $notifications      folders that need to be notified (in wbxml return to client)
     * @param array           $clearCacheList     folders that need their cache cleared
     * @param boolean         $needDelay          flag to indicate if we should delay to look for a follow-up notification
     *
     * @access public
     * @return 
     */
    public function ChangesSinkNotify( $CalledAfter, &$notify, &$notifications, &$clearCacheList, &$needDelay) {
    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'START ChangesSinkNotify - CalledAfter '.$CalledAfter.'; ' );

        if (( isset( $notify['created']['link'] )) ||
            ( isset( $notify['created']['folder'] ))) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'New Folder/Link Detected - FolderSync Required ' );
            $this->_clearCacheOnLogoff = true;
            if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                throw new StatusException("Zimbra->ChangesSinkNotify(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
            } else {
                throw new StatusException("Zimbra->ChangesSinkNotify(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
            }
        }


        if (( isset( $notify['created']['appt'] )) ||
            ( isset( $notify['created']['task'] )) ||
            ( isset( $notify['created']['cn'] ))) {
            $needDelay = true;
        }

        $checkForChanges = array( 'folder', 'link' );
        foreach ($checkForChanges as $folderType) {
            if ( isset( $notify['modified'][$folderType] ) ) { 
                $mods = $notify['modified'][$folderType];
                $changes = sizeof( $mods );
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Got a "'.$folderType.'" MODIFIED notification - '.$folderType.'s changed='.$changes );
            } else {
                $mods = array();
                $changes = 0;
            }

            for ($i=0;$i<$changes;$i++) {
                if ( isset( $mods[$i]['name'] ) ) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Folder Name Changed - FolderSync Required ' );
                    $this->_clearCacheOnLogoff = true;
                    if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                        throw new StatusException("Zimbra->ChangesSinkNotify(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                    } else {
                        throw new StatusException("Zimbra->ChangesSinkNotify(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                    }

                } elseif ( isset( $mods[$i]['l'] ) ) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Folder Move/Deletion/Move To Trash - FolderSync Required ' );
                    $this->_clearCacheOnLogoff = true;
                    if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                        throw new StatusException("Zimbra->ChangesSinkNotify(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                    } else {
                        throw new StatusException("Zimbra->ChangesSinkNotify(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                    }

                } elseif (array_key_exists($mods[$i]['id'], $this->changesSinkFolders)) {
                    $notifications[] = $this->changesSinkFolders[$mods[$i]['id']];
                    $clearCacheList[] = $this->changesSinkFolders[$mods[$i]['id']];

                }


                if (array_key_exists( $mods[$i]['id'], $this->_idToIndex )) {
                    if ($this->_folders[$this->_idToIndex[$mods[$i]['id']]]->linkid != "") {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Notification on shared folder ['.$this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid.'] - Need to Force _folders refresh ! ' );
                        // Mark _cacheChangeToken as ForceRefresh to make sure _folders gets refreshed
                        // Changes to linked folders, notified here, will not update the ChangeToken for the current user
                        $this->_cacheChangeToken = "ForceRefresh";
                        $this->_saveCacheOnLogoff = true;
                    }
                    if (isset($this->_virtual['contact']) && in_array($this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid, $this->_virtual['contact'])) {
                        $notifications[] = $this->_primary['contact'];
                        $clearCacheList[] = $this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid;
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Notification on virtual Contact folder ['.$this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid.'] reported on primary ['.$this->_primary['contact'].']' );
                    }elseif (isset($this->_virtual['appointment']) && in_array($this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid, $this->_virtual['appointment'])) {
                        $notifications[] = $this->_primary['appointment'];
                        $clearCacheList[] = $this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid;
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Notification on virtual Calendar folder ['.$this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid.'] reported on primary ['.$this->_primary['appointment'].']' );
                    }elseif (isset($this->_virtual['task']) && in_array($this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid, $this->_virtual['task'])) {
                        $notifications[] = $this->_primary['task'];
                        $clearCacheList[] = $this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid;
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' . 'Notification on virtual Task folder ['.$this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid.'] reported on primary ['.$this->_primary['task'].']' );
                    }elseif (isset($this->_virtual['note']) && in_array($this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid, $this->_virtual['note'])) {
                        $notifications[] = $this->_primary['note'];
                        $clearCacheList[] = $this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid;
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' . 'Notification on virtual Note folder ['.$this->_folders[$this->_idToIndex[$mods[$i]['id']]]->devid.'] reported on primary ['.$this->_primary['note'].']' );
                    }
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'Notification on folder ['.$mods[$i]['id'].'] but it is SmartExcluded / Not found in the Index' );
                }
            }
            unset($mods);                    
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSinkNotify(): ' .  'END ChangesSinkNotify ; ' );
        return;
    }

	
    /**
     * The actual ChangesSink.
     * For max. the $timeout value this method should block and if no changes
     * are available return an empty array.
     * If changes are available a list of folderids is expected.
     *
     * @param int           $timeout        max. amount of seconds to block
     *
     * @access public
     * @return array
     */
    public function ChangesSink($timeout = 60) {
//    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'START ChangesSink timeout='.$timeout.'; ' );

        $notifications = array();
        $clearCacheList = array();
        $needDelay = false;

        // Zimbra takes 3-4 seconds to set query each WaitSet - so if the remaining delay is less than 4 seconds - we should exit to avoid going over the timeout
        if ($timeout > 4) {
            $timeout = $timeout - 4;
        } else {
            sleep($timeout);
            return $notifications;
        }

        $soap = '<CreateWaitSetRequest  defTypes="all" xmlns="urn:zimbraMail" >
                      <add><a id="'.$this->_zimbraId.'" /></add>
                 </CreateWaitSetRequest>';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);

            unset($response);

            // In multi-server setup it is possible to have no error returned - but still not get a fully valid response
            if ( !isset($array['Header']['context']['change']['token']) ) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Session ID ['.$this->_sessionid.'] *Change Token NOT Found in Response* - Planned Zimbra downtime?? - Delay and return' );
                $timeout = $timeout + 4; // Reverse the 4 second reduction
                sleep($timeout);
                return $notifications;
            }

            $this->_pingtokenOne = $array['Header']['context']['change']['token'];
            $this->_waitSetId = $array['Body']['CreateWaitSetResponse']['waitSet'];
            $this->_highestSeqKnown = $array['Body']['CreateWaitSetResponse']['seq'];
        } else {
            if (isset($this->_soapError)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Session ID ['.$this->_sessionid.'] *SOAP ERROR* - Planned Zimbra downtime?? - Delay and return' );
                $timeout = $timeout + 4; // Reverse the 4 second reduction
                sleep($timeout);
                return $notifications;
            }
        }
        $notify = ( isset($array['Header']['context']['notify'][0]) ? $array['Header']['context']['notify'][0] : array() );

        unset($array);

        if (count($notify) > 0) {

            if ( isset( $notify['created']['folder'] ) || isset( $notify['deleted']['folder'] )) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Folder Creation/Deletion - FolderSync Required ' );
                $this->_clearCacheOnLogoff = true;
                if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                } else {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                }
            }
            if ( isset( $notify['created']['link'] ) || isset( $notify['deleted']['link'] )) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Shared Folder Creation/Deletion - FolderSync Required ' );
                $this->_clearCacheOnLogoff = true;
                if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                } else {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                }
            }
            $this->ChangesSinkNotify( 'CreateWaitSet', $notify, $notifications, $clearCacheList, $needDelay);

        } else {

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Session ID ['.$this->_sessionid.'] *Waiting* ['.$this->_waitSetId.'] for ['.$timeout.'] Seconds, with Highest SEQ [' .$this->_highestSeqKnown. '] and Token ['.$this->_pingtokenOne.']' );

            $soap = '<WaitSetRequest  waitSet="'.$this->_waitSetId.'" defTypes="all" seq="'.$this->_highestSeqKnown.'" block="1" timeout="'.$timeout.'" xmlns="urn:zimbraMail" >
                         <update>
                             <a id="'.$this->_zimbraId.'" token="'.$this->_pingtokenOne.'" />
                         </update>   
                     </WaitSetRequest>';



            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);
            } else {
                if (isset($this->_soapError)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Session ID ['.$this->_sessionid.'] *SOAP ERROR* - Planned Zimbra downtime?? - Delay and return' );
                    $timeout = $timeout + 4; // Reverse the 4 second reduction
                    sleep($timeout);
                    return $notifications;
                }
            }
            $notify = ( isset($array['Header']['context']['notify'][0]) ? $array['Header']['context']['notify'][0] : array() );

            unset($array);

            if (count($notify) > 0) {

                if ( isset( $notify['created']['folder'] ) || isset( $notify['deleted']['folder'] )) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Folder Creation/Deletion - FolderSync Required ' );
                    $this->_clearCacheOnLogoff = true;
                    if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                        throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                    } else {
                        throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                    }
                }
                if ( isset( $notify['created']['link'] ) || isset( $notify['deleted']['link'] )) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Shared Folder Creation/Deletion - FolderSync Required ' );
                    $this->_clearCacheOnLogoff = true;
                    if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                        throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                    } else {
                        throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                    }
                }
                $this->ChangesSinkNotify( 'WaitSet', $notify, $notifications, $clearCacheList, $needDelay);
            }
        }


        if ($needDelay ) {
            $microSecs = 500000;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Got a CREATED notification for a APPT/TASK/CONTACT - Delay notification by ['. $microSecs .'] microseconds in case it is a MOVE, then look for another NOTIFY');
            usleep($microSecs);
        }

        $soap = '<DestroyWaitSetRequest  waitSet="'.$this->_waitSetId.'"  xmlns="urn:zimbraMail" />';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);
            unset($response);
        }

        $notify = ( isset($array['Header']['context']['notify'][0]) ? $array['Header']['context']['notify'][0] : array() );

        unset($array);

        if (count($notify) > 0) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Got a secondary notification !');

            if ( isset( $notify['created']['folder'] ) || isset( $notify['deleted']['folder'] )) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Folder Creation/Deletion - FolderSync Required ' );
                $this->_clearCacheOnLogoff = true;
                if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                } else {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                }
            }
            if ( isset( $notify['created']['link'] ) || isset( $notify['deleted']['link'] )) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Shared Folder Creation/Deletion - FolderSync Required ' );
                $this->_clearCacheOnLogoff = true;
                if (defined('SyncCollections::HIERARCHY_CHANGED')) {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
                } else {
                    throw new StatusException("Zimbra->ChangesSink(): HierarchySync required.", SyncCollections::ERROR_WRONG_HIERARCHY);
                }
            }
            $this->ChangesSinkNotify( 'DestroyWaitSet', $notify, $notifications, $clearCacheList, $needDelay);
        }

        if (count($notifications) > 0) {

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Notification received for MONITORED folder(s) ['. implode( ", ", $notifications ) .']' );
            $this->ClearCache( $clearCacheList );

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ChangesSink(): ' .  'Notification of item creation/modification/deletion - Need to Force _folders refresh ! ' );
            // Mark _cacheChangeToken as ForceRefresh to make sure _folders gets refreshed
            // Changes to linked folders, notified here, will not update the ChangeToken for the current user
            $this->_cacheChangeToken = "ForceRefresh";
        }


        return $notifications;
    }


    /**
     * Indicates if the Backend supports folder statistics.
     *
     * @access public
     * @return boolean
     */
    public function HasFolderStats() {
        return true;
    }

    /**
     * Returns a status indication of the folder.
     * If there are changes in the folder, the returned value must change.
     * The returned values are compared with '===' to determine if a folder needs synchronization or not.
     *
     * @param string $store         the store where the folder resides
     * @param string $folderid      the folder id
     *
     * @access public
     * @return string
     */
    public function GetFolderStat($store, $folderid) {
        // As this is not implemented, the value returned will change every hour.
        // This will only be called if HasFolderStats() returns true.

        $stat = false;
        $index = $this->GetFolderIndex($folderid);
        if ($index>=0) {
            $stat = $this->_folders[$index]->stats;  
            $view = $this->_folders[$index]->view;  

            // Z-Push calls GetFolderStat() to compare what it knows to what the server currently has for each folder. In the
            // zimbra backend this is implemented by returning a string containing the Folder name and metadata.
            // For situations (Outlook Contacts for example) where virtual folders are needed, a change to an item in any folder
            // other than the Primary one will get flagged and the stats of it's folder will get updated. But as the z-push server 
            // only knows to query the stats of the Primary so it will never realise it needs to do a refresh on the virtual folder
            // We need to add an indicator to the Primary folder stats to alert about a change on any virtual folder. This has been 
            // implemented by stringing together hashes of the stats from all virtual folders of the primary folder's type, and   
            // adding a hash of the resultant string to the stats returned by the backend for the Primary folder. Any change to the
            // virtual folders should change the hash and cause the change in the virtual folder being synced to the device.

            if ( isset($this->_primary[$view]) && ($this->_folders[$index]->devid == $this->_primary[$view]) ) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderStat(): ' . 'GetFolderStat { folderid = ' . $folderid . '; IS A PRIMARY FOLDER for [' . $view . ']; stat = ' . $this->_folders[$index]->stats .' }');
                if ($this->_deviceMultiFolderSupport[$view] === false) {
                    $hashList = "";
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderStat(): ' . 'GetFolderStat { Multi-Folder Support NOT AVAILABLE for [' . $view . ']; Process Folder list to generate Hash! }');
                    for ($i=0;$i<count($this->_folders);$i++) {
                        if (( 1 == $this->_folders[$i]->virtual ) && ($view == $this->_folders[$i]->view )) {
//                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderStat(): ' . 'GetFolderStat { folderid = ' . $this->_folders[$i]->devid . '; is a VIRTUAL [' . $view . '] folder; }');
                            $hashList .= hash( 'crc32b', $this->_folders[$i]->stats );
                        }
                    }
                    $stat .= '#' . hash( 'crc32b', $hashList );  
                }
            }
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderStat(): ' . 'GetFolderStat { folderid = ' . $folderid . '; Returned stat = ' . $stat .' }');
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetFolderStat(): ' . 'GetFolderStat { folderid = ' . $folderid . ' NOT FOUND; returning stat = false }');
        }
        return $stat;
    }


    /**
     * If folderArray contains an array - clear the cache for each folder in that array
     * If folderArray if false - clear the entire cache for the device
     *
     * @param mixed        $folderArray
     *
     * @access public
     * @return 
     */
    public function ClearCache( $folderArray=false ) {

        if (!$this->_localCache) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->ClearCache(): ' .  'ClearCache called with no _local_Cache available');
            return;
        }

        if (!$folderArray) {
            unset( $this->_cachedMessageLists );
            $this->_cachedMessageLists = array();
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Cache CLEARED for the DEVICE'  );
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Reload cachedMessageList before clearing folder cache'  );
            unset( $this->permanentStorage );
            $this->InitializePermanentStorage();

            $retries = 0;
            while (!($this->permanentStorage instanceof StateObject) && ($retries < 5)) {
                unset($this->permanentStorage);
                $retries += 1;
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Permanent Storage IS NOT A StateObject - Delay & Re-Read it in case of file contention - Retry ' . $retries . '/5 !' );
                $microSecs = 250000; // Quarter-of-a-second
                usleep( $microSecs );
                $this->InitializePermanentStorage();
            }

            if ($this->permanentStorage instanceof StateObject ) {
                $this->_cachedMessageLists = $this->permanentStorage->GetCachedMessageLists();
                if (!isset($this->_cachedMessageLists) or !is_array($this->_cachedMessageLists)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Permanent Storage -> CachedMessageLists is NOT an array - Recreate it !' );
                    $this->_cachedMessageLists = array();
                }
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Permanent Storage IS NOT A StateObject - Recreate it !' );
                $this->permanentStorage = new StateObject();
                $this->_cachedMessageLists = array();
            }

            $folderList = "";
            foreach ($folderArray as $folder) {
                unset($this->_cachedMessageLists[$folder]);
				$folderList .= $folder . ", ";
            }
            unset( $folderArray );
            unset( $folder );

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Cache CLEARED for FOLDER(S) ['.substr($folderList, 0, -2) .']'  );
        }


        unset( $this->_cachedMessageLists['changed'] );
        $this->permanentStorage->SetCachedMessageLists( $this->_cachedMessageLists );
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ClearCache(): ' .  'Save updated cachedMessageList in permanentStorage'  );
        $this->SaveStorages();

    }



    /** SendMailSenderFix
     *   Clean-up/verify/replace the From address sent by the device.
     */
    public function SendMailSenderFix($deviceFrom = false) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'START SendMailSenderFix { $deviceFrom = '.$deviceFrom.'; }');

        if ($deviceFrom && (trim($deviceFrom) != "")) {
            // In case a From header was received from the phone - Check the contents and override if needed.

            // Check if full email address used as user ID on iPxxx device - @domain may be added a second time by the device 
            // This has been observed on iOS7 where Username is initially input with no domain and saved, and is later edited to add a domain 
            // First, check if full email address used as Display Name - "user@domain.com" <user@domain.com>  as this will look like iOS7 double domain issue
            $lastAt = strrpos( $deviceFrom , "@" );
            if (($lastAt !== false) && ($lastAt != 0)) {
                $spaceBeforeAt = strrpos( $deviceFrom , " ", -(strlen($deviceFrom) - $lastAt) ); 
                $spaceBeforeAt = ($spaceBeforeAt === false ? 0 : $spaceBeforeAt );
                $lessBeforeAt = strrpos( $deviceFrom , "<", -(strlen($deviceFrom) - $lastAt) );
                $lessBeforeAt = ($lessBeforeAt === false ? 0 : $lessBeforeAt );
                $addressStart = ($spaceBeforeAt > $lessBeforeAt ? $spaceBeforeAt : $lessBeforeAt );

                $address = substr( $deviceFrom, $addressStart );

                $addressParts = explode( "@", $address);
                if (count($addressParts) == 3) {
                    unset( $addressParts[2] );
                    $deviceFrom = substr($deviceFrom, 0, $addressStart) . implode( "@", $addressParts );
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Extra @domain REMOVED ['.$deviceFrom.']' );
                }
            }

            $parser = new Mail_RFC822();
            $addressArray = $parser->parseAddressList($deviceFrom);
            $phoneEmail = $this->parseAddr($addressArray);
            unset($parser);
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Parsed from email ['.$phoneEmail.']' );
            if (($phoneEmail == $this->_accountName) && ($this->_sendAsEmail != $this->_accountName)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Phone using default email address - but zimbraPrefFromAddress different for the account - Override with zimbraPrefFromAddress');
                $phoneEmail = $this->_sendAsEmail;
            }

            // If SmartFolders Directive or XML file set's Override for Email Address then use it.
            // Enforce valid rules will already have been applied when reading the configuration setting
            // Note - even if ZIMBRA_ENFORCE_VALID_EMAIL is false (or not set) zimbra COS can still prevent
            // the email going out with a non-alias email address. It will replace it with the account detault
            if (isset( $this->_sendAsEmailOverride )) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Using configured sendasemail override Email Address ['.$this->_sendAsEmailOverride.']' );
                $value = $this->_sendAsEmailOverride;
            } else {
                if (!defined('ZIMBRA_ENFORCE_VALID_EMAIL')) {
                    $this->_enforcevalidemail = 'false';
                } else {
                    $this->_enforcevalidemail = ZIMBRA_ENFORCE_VALID_EMAIL;
                }
                if ($this->ToBool($this->_enforcevalidemail) === true) {
                    $goodAddr = false;
                    for ($i=0;$i<count($this->_addresses);$i++) {
                        if (strtolower($phoneEmail) == strtolower($this->_addresses[$i])) {
                            $goodAddr = true;
                            $value = $phoneEmail;
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Valid user email address');
                            break;
                        }
                    }
                    if(!$goodAddr) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Overriding incomplete or invalid user email address ['.$phoneEmail.']');
                        $value = $this->_sendAsEmail;
                    }
                } else {
                    $value = $phoneEmail;
                } 
            } 

            // Use sender preferred name together with provided (or default if provided is invalid) email address.
            if (isset($this->_sendAsNameOverride)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Using configured sendasname override Sender Name ['.$this->_sendAsNameOverride.']' );
                // =?utf-8?B?VmluY2VudCBCcsWhxI1pxIc=?=
//                if (preg_match("/=[?]{1}[A-Za-z0-9\-]+[?]{1}(B|Q)\?[A-Za-z0-9\/\+=]+[?]{1}=/", $this->_sendAsNameOverride) !== false) {
                if ((($prefix = stripos($this->_sendAsNameOverride, '=?')) !== false) && 
                    (($encoding = stripos($this->_sendAsNameOverride, '?q?', $prefix) !== false) || ($encoding = stripos($this->_sendAsNameOverride, '?b?', $prefix) !== false)) && 
                    (($suffix = stripos($this->_sendAsNameOverride, '?=', $prefix)) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Override name already encoded' );
                    $value = '"' . $this->_sendAsNameOverride .'" <'. $value .'>';
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Override name NOT encoded' );
                    $value = '"' . $this->_sendAsNameOverride .'" <'. $value .'>';
                }
            } else {
                // Look in _identities to see if there is a specific From Name to use
                if (isset($this->_identities[$value])) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Setting name from Identities' );
                    $value = '"' . $this->_identities[$value] .'" <'. $value .'>';
                } else {
                    // Otherwise, use account's primary name
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Using account default _sendAsName' );
                    $value = '"' . $this->_sendAsName .'" <'. $value .'>';
                }
            }

        } else {
            // In case no From header, or an empty one, was received from the phone - Create one.

            if (isset( $this->_sendAsEmailOverride )) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Using configured sendasemail override Email Address ['.$this->_sendAsEmailOverride.']' );
                $value = $this->_sendAsEmailOverride;
            } else {
                $found = false;
                if (stripos($this->_username, "@") !== false) {
                    // Device sent login as full email address - look it up
                    if (isset($this->_identities[$this->_username])) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'No/Empty From: header - Setting name from Identities using device login (email address)' );
                        $value = $this->_username;
                        if ($this->_sendAsEmail != $this->_username) {
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'No/Empty From: header - but zimbraPrefFromAddress different for the account - Override with zimbraPrefFromAddress');
                            $value = $this->_sendAsEmail;
                        }
                        $found = true;
                    } 
                } else {
                    // 
                    foreach ($this->_identities as $email=>$name) {
                        if (stripos( $email, $this->_username . "@" ) !== false) {
                            if (stripos( $email, $this->_username . "@" ) == 0) {
                                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'No/Empty From: header - Setting name from Identities using device login' );
                                $value = $email;
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                if (!$found) {
                    $value = $this->_sendAsEmail;
                }
            }
            // Use sender preferred name together with provided (or default if provided is invalid) email address.
            if (isset($this->_sendAsNameOverride)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Using configured sendasname override Sender Name ['.$this->_sendAsNameOverride.']' );
                // =?utf-8?B?VmluY2VudCBCcsWhxI1pxIc=?=
//                if (preg_match("/=[?]{1}[A-Za-z0-9\-]+[?]{1}(B|Q)\?[A-Za-z0-9/\+=]+[?]{1}=/", $this->_sendAsNameOverride) !== false) {
                if ((($prefix = stripos($this->_sendAsNameOverride, '=?')) !== false) && 
                    (($encoding = stripos($this->_sendAsNameOverride, '?q?', $prefix) !== false) || ($encoding = stripos($this->_sendAsNameOverride, '?b?', $prefix) !== false)) && 
                    (($suffix = stripos($this->_sendAsNameOverride, '?=', $prefix)) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Override name already encoded' );
                    $value = '"' . $this->_sendAsNameOverride .'" <'. $value .'>';
                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Override name NOT encoded' );
                    $value = '"' . $this->_sendAsNameOverride .'" <'. $value .'>';
                }
            } else {
                // Look in _identities to see if there is a specific From Name to use
                if (isset($this->_identities[$value])) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Setting name from Identities' );
                    $value = '"' . $this->_identities[$value] .'" <'. $value .'>';
                } else {
                    // Otherwise, use account's primary name
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'Using account default _sendAsName' );
                    $value = '"' . $this->_sendAsName .'" <'. $value .'>';
                }
            }

        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMailSenderFix(): ' . 'END SendMailSenderFix { '.$value.' }');
        return $value;
    } // end SendMailSenderFix


    /** SendMail
     *   Sends a message which is passed as rfc822.
     */
    public function SendMail($syncsm) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'START SendMail { $syncsm = (excluded); }');

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'SyncSm ['.print_r( $syncsm, true ).']' );

/*
class SyncSendMail extends SyncObject {
    public $clientid;
    public $saveinsent;
    public $replacemime;
    public $accountid;
    public $source;
    public $mime;
    public $replyflag;
    public $forwardflag;
*/
/*
class SyncSendMailSource extends SyncObject {
    public $folderid;
    public $itemid;
    public $longid;
    public $instanceid;
*/

		$forward = false;
		$reply = false;
        $saveinsent = false;
        $replaceMIME = false;

        if (isset($syncsm->replyflag)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Reply flag is SET' );
            $reply = $syncsm->source->itemid;
        } elseif (isset($syncsm->forwardflag)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Forward flag is SET' );
            $forward = $syncsm->source->itemid;
		}

        if (isset($syncsm->saveinsent)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'SaveInSent flag is SET' );
            $saveinsent = true;
		}
        if (isset($syncsm->replacemime)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'replaceMIME flag is SET' );
            $replaceMIME = true;
		}

        $rfc822 = $syncsm->mime;

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'RFC822: '. $rfc822 );

        // Set up Mail_Mime object first so as to be able to encode From header if changed. 

        $crlf = "\r\n";
        $text_enc = 'quoted-printable';
        $text_charset = 'utf-8';
        $html_enc = 'quoted-printable';
        $html_charset = 'utf-8';
        $head_enc = 'base64';
        $head_charset = 'utf-8';
        $final_headers = "";
        $attachments = false;

        $final = new Mail_Mime(array('head_encoding'=>$head_enc, 'head_charset'=>$head_charset, 'text_encoding'=>$text_enc, 'text_charset'=>$text_charset, 'html_encoding'=>$html_enc, 'html_charset'=>$html_charset, 'eol'=>$crlf));

        // Set up the Mail_mimeDecode object to break the message into _header and _body

        $mobj = new Mail_mimeDecode($rfc822);

        $headers = explode( chr(13).chr(10), $mobj->_header );

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Headers ['.print_r($mobj->_header, true).']' );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Body ['.print_r($mobj->_body, true).']' );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Headers ['.print_r($headers, true).']' );

        // Break headers into 4 categories.
        // (F) From - We want to fix the sender information before including it into the final message
        // (C) ContentHeaders: - If we will be doing any manipulation of the MIME data - these header(s) will be replaced later
        // (M) MimeVersion: - If we will be doing any manipulation of the MIME data - this header will be replaced later
        // (S) Safe - All other headers we want to pass through to the final message
        $deviceSafeHdrs = "";
        $deviceFrom = "";
        $deviceContentHdrs = "";

        $deviceMimeVersionHdr = "";
        $currentHdr = "S";
        for ($i=0; $i<count($headers); $i++ ) {
            if (strtolower(substr( $headers[$i], 0, 5)) == "from:") {
                $deviceFrom = trim( substr($headers[$i],5) );
                $currentHdr = "F";
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Found a FROM Header at ['.$i.']' );
            } elseif (strtolower(substr( $headers[$i], 0, 13)) == "content-type:") {
                $deviceContentHdrs .= $headers[$i] . chr(13).chr(10);
                $currentHdr = "C";
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Found a Content-Type Header at ['.$i.']' );
            } elseif (strtolower(substr( $headers[$i], 0, 26)) == "content-transfer-encoding:") {
                $deviceContentHdrs .= $headers[$i] . chr(13).chr(10);
                $currentHdr = "C";
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Found a Content-Transfer-Encoding Header at ['.$i.']' );
            } elseif (strtolower(substr( $headers[$i], 0, 13)) == "mime-version:") {
                $deviceMimeVersionHdr = $headers[$i] . chr(13).chr(10);
                $currentHdr = "M";
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Found a Mime-Version Header at ['.$i.']' );
            } elseif (($headers[$i][0] == " ") or ($headers[$i][0] == "\t")) {
                switch ($currentHdr) {
                    case "F":
                        $deviceFrom .= " " . trim( $headers[$i] );
                        break;
                    case "C":
                        $deviceContentHdrs .= $headers[$i] . chr(13).chr(10);
                        break;
                    case "M":
                        $deviceMimeVersionHdr .= $headers[$i] . chr(13).chr(10);
                        break;
                    default:
                        $deviceSafeHdrs .= $headers[$i] . chr(13).chr(10);
                        break;
                }
            } else {
                $deviceSafeHdrs .= $headers[$i] . chr(13).chr(10);
                $currentHdr = "S";
            }
        }

/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'deviceFrom ['.print_r($deviceFrom, true).']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'deviceContentHdrs ['.print_r($deviceContentHdrs, true).']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'deviceMimeVersionHdr ['.print_r($deviceMimeVersionHdr, true).']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'deviceSafeHdrs ['.print_r($deviceSafeHdrs, true).']' );
*/

        $fixedFrom = $this->SendMailSenderFix($deviceFrom);
        $fixedFrom = $final->encodeHeader("From", $fixedFrom, 'utf-8', 'base64');

        // Add back the fixed From header & a Z-Push Zimbra Backend identifier header
        $deviceSafeHdrs .= "From: " . $fixedFrom . chr(13).chr(10);

//        Removed //IGNORE//TRANSLIT is 63.2 as it was causing issues with latest mimeDecode
//        $params = array('decode_headers' => false, 'decode_bodies' => true, 'include_bodies' => true, 'charset' => 'utf-8//IGNORE//TRANSLIT');
        $params = array('decode_headers' => false, 'decode_bodies' => true, 'include_bodies' => true, 'charset' => 'utf-8');
        $message = $mobj->decode($params);
        unset($params);

//        unset($rfc822);

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Message ['.print_r($message, true).']' );


        $body_text = "";
        $body_html = "";
        $htmlInlineImagesOnly = true;
        $textashtml = "";
        $hasCalendar = false;
        $rfc822HasBodyTypes = array( 'plain' => false, 'html' => false, 'calendar' => false, 'ms-tnef' => false, 'signed' => false, 'pkcs7-signature' => false, 'pkcs7-mime' => false );
        $this->GetAllBodyRecursive($message, $body_text, $body_html, $htmlInlineImagesOnly, $textashtml, $rfc822HasBodyTypes, $final, $parent_ctp=false, $parent_cts=false);

/*
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'PLAIN ['.$body_text.']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'HTML ['.$body_html.']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'htmlInlineImagesOnly ['.$htmlInlineImagesOnly.']' );
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'Plain-AS-HTML ['.$textashtml.']' );
*/


        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'rfc822HasBodyTypes ['. print_r($rfc822HasBodyTypes, true).']' );

        // Based on body types in the message - determine if any manipulation of the body can be attempted.
        // If it is a restricted type - then we are done. 
//        if (($rfc822HasBodyTypes['calendar'] || $rfc822HasBodyTypes['ms-tnef'] || $rfc822HasBodyTypes['signed'] || $rfc822HasBodyTypes['pkcs7-signature'] || $rfc822HasBodyTypes['pkcs7-mime'] ) && (!$forward) && (!$reply)) {
        if (((!$forward) && (!$reply)) || ((($forward) || ($reply)) && $replaceMIME)) {

//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'MIME Body cannot be altered - Adding back original Mime-Version and Content-Type headers');
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'MIME Body does not need be altered - Adding back original Mime-Version and Content-Type headers');

            // Not manipulating the MIME body - so add back the original Mime-Version and Content headers
            $final_rfc822 = $deviceSafeHdrs . $deviceMimeVersionHdr . $deviceContentHdrs . chr(13).chr(10) . $mobj->_body;

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'Sending unchanged device originated message body for message - Either not a forward or reply - or is a forward or reply that replaces the MIME message');
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'Sending unchanged device originated message body for Calendar/MS-TNEF encoded/SIGNED or ENCRYPTED message');

        } else {

            // If not a restricted type - then see if we need to manipulate the body (SmartForward/SmartReply)
			
            if ($htmlInlineImagesOnly) {
                $body_html = $textashtml;
            }



//            $body_text = $this->cp1252_to_utf8( $body_text ); // Euro Fix
            $body_text = preg_replace('/^[\n]2,|^[\t\s]*\n+/m',"\n",$body_text);  // Strip out duplicate blank lines
//            $body_html = $this->cp1252_to_utf8( $body_html ); // Euro Fix



            $final_text = "";
            $final_html = "";

            if (($reply || $forward) && (!$replaceMIME)) {
                if ($reply) {
                    $original = $reply;
                } else {
                    $original = $forward;
                }

                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'Grabbing the Original' );

                $folderid = false;
                $needHtml = true;
                $needPlain = true;
                $orig_calendar = "";
                $orig_html = "";
                $orig_text = "";
                $hasBodyTypes = array( 'plain' => false, 'html' => false, 'calendar' => false, 'ms-tnef' => false, 'signed' => false, 'pkcs7-signature' => false, 'pkcs7-mime' => false );
                $msg = array();
                $attachments = array();	

//                  public function GetZimbraMessageBodies($folderid, $id, $needHtml, $needPlain, &$msg, &$plain, &$html, &$calendar, &$hasBodyTypes, &$attachments) {
                $response = $this->GetZimbraMessageBodies($folderid, $original, $needHtml, $needPlain, $msg, $orig_text, $orig_html, $orig_calendar, $hasBodyTypes, $attachments);

                if($response) {

                    $total = count($msg['e']);
                    $from = ''; $to = ''; $cc = ''; $bcc = ''; $replyto = '';
                    for ($i=0;$i<$total;$i++) {

                        // Set $name = in order of preference - Personal Name, Display name, Email address
                        if (isset($msg['e'][$i]['p'])) {
                            $name = $msg['e'][$i]['p'];
                        } else {
                            $name = "";
                        }
                        $addr = $msg['e'][$i]['a'];
                        if (!empty($name)) {
                            $addr = "\"" . $name . "\" &lt;" . $addr . "&gt;";
                        }
                        
                        switch ($msg['e'][$i]['t']) {
                            case 'f':
                                $from .= empty($from) ? $addr : ", " . $addr;
                                break;
                            case 't':
                                $to .= empty($to) ? $addr : ", " . $addr;
                                break;
                            case 'c':
                                $cc .= empty($cc) ? $addr : ", " . $addr;
                                break;
                        }
                    }

                    $date_header = "<B>Sent: </B>" . date( "j F Y G:i", $this->Date4ActiveSync($msg['d'],'UTC') ) . "<BR>";
                    if (isset($msg['su'][0])) {
                        $subject_header = "<B>Subject: </B>" . $msg['su'][0] . "<BR>";
                    } else {
                        $subject_header = "<B>Subject: </B>" . "<BR>";
                    }
                    $from_header = "<B>From: </B>" . $from ."<BR>";
                    $to_header = "<B>To: </B>" . $to . "<BR>";
                    if ($cc != "") {
                        $cc_header = "<B>Cc: </B>" . $cc . "<BR>";
                    } else $cc_header = "";

                } else {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'Soap command failed!' );
                }

                $orig_header_html = "";
                $orig_header_text = "";
  
                $body_text = $body_text . $orig_header_text;
                $body_html = '<SPAN style="font-size: 11pt">' . $body_html . $orig_header_html . '</SPAN>';
			
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'ORIGINAL TEXT: '. $orig_text );
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'ORIGINAL HTML: '. $orig_html );


                $origBodyTagStart = strpos( strtolower($orig_html), '<body' );
	    		if ($origBodyTagStart === false) {
                    // Assume it is a plain text email with no body
	
                    $htmlToOrigBodyStart = "";
                    $htmlFromOrigBodyStart = $orig_html;
                } else {
                    $origBodyTagEnd = strpos( strtolower($orig_html), '>', $origBodyTagStart+1 );

                    $htmlToOrigBodyStart = substr( $orig_html, 0, $origBodyTagEnd+1 );
                    $htmlFromOrigBodyStart = substr( $orig_html, $origBodyTagEnd+1 );
                }

                if ($body_text != "") $final_text = $body_text . $orig_text ;
                if ($body_html != "") $final_html = $htmlToOrigBodyStart . $body_html . $htmlFromOrigBodyStart;

            } else {
                if ($body_text != "") $final_text = $body_text;
                if ($body_html != "") {
                    $final_html = $body_html;
                    if (strpos( strtolower($final_html), '<html' ) === false ) { 
                        // Nokia sends only Text email - need to top and tail it.
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'FINAL HTML HAS NO <html> TAGS - ADD WRAPPER' );
                        $final_html = '<html>'.
                                      '<head>'.
                                      '<meta name="Generator" content="Z-Push">'.
                                      '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
                                      '</head>'.
                                      '<body>'.
                                      str_replace("\n","<BR>",str_replace("\r","", str_replace("\r\n","<BR>",$final_html))).
                                      '</body>'.
                                      '</html>';
                    }
                }
            }
            if ($final_text != "") $final->setTXTBody($final_text);
            if ($final_html != "") $final->setHTMLBody($final_html);

            if (isset($message->ctype_parameters['charset'])) {
                $text_charset = trim($message->ctype_parameters['charset']);
            }

            if (($forward) && (!$replaceMIME)) {
                // If SmartForward, we must re-attach any attachments we got from the original
                // Assuming here we would not want to return all attachments to the sender if replying.
                foreach($attachments as $attachment) {

                    $colons = substr_count( $attachment->attname, ":" );
                    if ($colons == 2) {
                        list($fid, $mid, $pid) = explode(":", $attachment->attname);
                    } else {
                        list($fid, $remoteUser, $remoteId, $pid) = explode(":", $attachment->attname);
                        $mid = $remoteUser .':'. $remoteId;
                    }
                    if ($attachment->attmethod != 6) {
                        $disposition = "attachment";
//                        list($fid, $mid, $pid) = explode(":", $attachment->attname);
                        $final->addAttachment(  $this->GetRawMessage($mid, $pid),
                                                $attachment->contenttype,
                                                $attachment->displayname,
                                                false,
                                                "base64",
                                                $disposition,
                                                "",
                                                "",
                                                $attachment->attoid,
                                                "base64",
                                                "base64", 
                                                "",
                                                $attachment->nameencoding);
                    } else {
                        $disposition = "inline";
//                        list($fid, $mid, $pid) = explode(":", $attachment->attname);

                        $final->addHTMLImage(   $this->GetRawMessage($mid, $pid),
                                                $attachment->contenttype,
                                                $attachment->displayname,
                                                false,
                                                $attachment->attoid);
                    }

                }
                unset( $attachment );
            }
            unset( $attachments );

            $final_body = $final->get();
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'final->txtheaders() =['.$final->txtheaders().']');

            $final_rfc822 = $deviceSafeHdrs . $final->txtheaders(). "\n\n" . $final_body;

        }
//        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' .  'FINAL RFC822=['.$final_rfc822.']');

        unset( $mobj );
        unset( $final );
        unset($rfc822);

        $temp_folder = (substr(STATE_DIR, -1) == '/' ? STATE_DIR : STATE_DIR . "/");
        $temp_file = tempnam($temp_folder, "ZIMBRA_MSG_");
        $handle = fopen($temp_file, "w");
        if ($handle === false) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'Unable to open temporary file ['.$temp_file.']' );
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'END SendMail { false }');
            return false;
        }

        if (false === fwrite($handle, $final_rfc822)) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'Unable to write temporary file ['.$temp_file.']' );
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'END SendMail { false }');
            return false;
        }

        if (false === fclose($handle)) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'Unable to close temporary file ['.$temp_file.']' );
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'END SendMail { false }');
        }
        unset($final_rfc822);

        // Update for PHP 5.5 and later - See https://sourceforge.net/p/zimbrabackend/support-requests/105/
        if (function_exists("curl_file_create")) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'Email uploaded to temporary file ['.$temp_file.'] using new CURLfile object' );
            $cfile = curl_file_create( $temp_file, 'message/rfc822' );
            $array = array('file' => $cfile,
                           'requestId' => 'client_upload_token');
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'Email uploaded to temporary file @['.$temp_file.'] ' );
            $array = array('file' => '@' . $temp_file,
                           'requestId' => 'client_upload_token');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_publicURL . '/service/upload?fmt=raw');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
//        curl_setopt($ch, CURLOPT_USERPWD, $this->_username .":". $this->_password);
        curl_setopt($ch, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN='. $this->_authtoken);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (defined('ZIMBRA_URL_ALLOW_REDIRECT') && ($this->ToBool(ZIMBRA_URL_ALLOW_REDIRECT) === true)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ZIMBRA_URL_ALLOW_REDIRECT is TRUE in config.php - Redirection is ALLOWED' );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $http_header = array();
        $http_header[] = 'X-Forwarded-For: ' . $this->_xFwdForForMailboxLog;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        if (!($response = curl_exec($ch))) {
            if (defined('ZIMBRA_DEBUG')) {
                if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'SOAP Response: '.$response, false);
                }
            }
            $this->_soapError = 'CURL.'.curl_errno($ch);
            $this->error = 'ERROR UPLOADING EMAIL: curl_exec - ('.curl_errno($ch).') '.curl_error($ch);
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . $this->error);
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'END SendMail { false }');
            return false;
        } else {
            if (defined('ZIMBRA_DEBUG')) {
                if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'CURL Response: '.$response, false);
                }
            }
        }
        curl_close($ch);
        if (false === unlink($temp_file)) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'Unable to remove temporary file ['.$temp_file.'] - Please manually remove the file !' );
        }

        $tokens = explode(',',$response);

        if ((count($tokens) != 3) || ($tokens[0] != 200) || (stripos($tokens[1],'client_upload_token') === false)) {
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'CURL Zimbra Upload - Invalid Response: ['.print_r($response,true) .']', false );
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SendMail(): ' . 'END SendMail { false }');
            return false;
        }

        unset($response);
		
        $server_token = trim(trim($tokens[2]), '\'');

        // Replies & Forwards
        $origmsg = '';
        if (isset($reply) && $reply) {
            $origmsg = ' origid="'.$reply.'" rt="r"';
        }
        if (isset($forward) && $forward) {
            $origmsg = ' origid="'.$forward.'" rt="w"';
        }

        // Send Message
        $soap = '<SendMsgRequest xmlns="urn:zimbraMail">
                    <m aid="' . $server_token . '"' . $origmsg . '/>
                 </SendMsgRequest>';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);
            unset($response);

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'END SendMail { MessageID = ' . $array['Body']['SendMsgResponse']['m'][0]['id'] . ' }' );
            return true;
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SendMail(): ' . 'END SendMail { false }');
            return false;
        }
    } // end SendMail


    /** MeetingResponse
     *   Handles the Response to a meeting request sent to the phone by email. Accept/Decline/Tentative are accepted by zimbra
     */
    public function MeetingResponse($requestid, $folderid, $response) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MeetingResponse(): ' . 'START MeetingResponse { requestid = ' . $requestid . '; folderid = ' . $folderid . '; response = ' . $response . ' }');
        switch ($response) {
            case '1':
              $verb = "ACCEPT";
              break;

            case '2':
              $verb = "TENTATIVE";
              break;

            case '3':
              $verb = "DECLINE";
              break;
        }

        // Not sure about the compNum. 
        // email organizer/attendees only if _serverInviteReply is set to true
        // Almost all phones now properly send the invites directly themselves
        $updateOrganizer = ($this->_serverInviteReply ? "1" : "0" );

//        $soap = '<SendInviteReplyRequest xmlns="urn:zimbraMail" id="'.$requestid.'" compNum="0" verb="'.$verb.'" updateOrganizer="'.$updateOrganizer.'"/>';
        $invId = $this->GetInvIDFromMsgID( $requestid );
        $soap = '<SendInviteReplyRequest xmlns="urn:zimbraMail" id="'.$invId.'" compNum="0" verb="'.$verb.'" updateOrganizer="'.$updateOrganizer.'"/>';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);
            unset($response);
            $calendarid = '';
//			debugLog( 'INVITE-REPLY-RESPONSE:' . print_r( $array, true), false );

            $status = ((($verb != "DECLINE") && isset($array['Body']['SendInviteReplyResponse']['calItemId'])) || (($verb == "DECLINE") && isset($array['Body']['SendInviteReplyResponse'])));
            if ($status == true) {
                //ZLog::Write(LOGLEVEL_DEBUG, 'SendInviteReply: STATUS true ' );
                //$calendarid = $array['Body']['SendInviteReplyResponse']['invId']; // Not sure if invId or calItemId more appropriate
                if ($verb != "DECLINE") {
                    $calendarid = $array['Body']['SendInviteReplyResponse']['calItemId'];
                }
            } else {
                $notify = ( isset($array['Header']['context']['notify'][0]) ? $array['Header']['context']['notify'][0] : array() );

                //ZLog::Write(LOGLEVEL_DEBUG, 'SendInviteReply: STATUS ! true ' );
                if (isset($notify['modified']['appt'][0]['inv'][0]['id']) && ($notify['modified']['appt'][0]['inv'][0]['id'] == $requestid)) {

                    if (isset($notify['modified']['appt'][0]['inv'][0]['comp'][0]['calItemId'])) {
                        $status = true;
                        $calendarid = $notify['modified']['appt'][0]['inv'][0]['comp'][0]['calItemId'];
                    }
                } elseif (isset($notify['created']['appt'][0]['inv'][0]['id']) && ($notify['created']['appt'][0]['inv'][0]['id'] == $requestid)) {

                    if (isset($notify['created']['appt'][0]['inv'][0]['comp'][0]['calItemId'])) {
                        $status = true;
                        $calendarid = $notify['created']['appt'][0]['inv'][0]['comp'][0]['calItemId'];
                    }
                }
            }
 
            if ($status == true) {

                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MeetingResponse(): ' . 'END MeetingResponse ['.$verb.'] - calendarID ['.$calendarid.'] { true }');
                return $calendarid;
            }
			debugLog( 'INVITE-REPLY-RESPONSE:' . print_r( $array, true), false );
            unset($array);
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->MeetingResponse(): ' . 'END MeetingResponse { false } - ' . $this->_soapError  );
        return false;
    }


    function getTruncSize($truncation) {
        switch($truncation) {
            case SYNC_TRUNCATION_HEADERS:
                return 0;
            case SYNC_TRUNCATION_512B:
                return 512;
            case SYNC_TRUNCATION_1K:
                return 1024;
            case 3:
                return 2*1024;
            case SYNC_TRUNCATION_5K:
                return 5*1024;
            case 5:
                return 10*1024;
            case 6:
                return 20*1024;
            case 7:
                return 50*1024;
            case 8:
                return 100*1024;
            case SYNC_TRUNCATION_ALL:
                return 1024*1024; // We'll limit to 1MB anyway
            default:
                return 1024; // Default to 1Kb
        }
    }


    // parses address objects back to a simple "," separated string
    function parseAddr($ad) {
        $addr_string = "";
        if (isset($ad) && is_array($ad)) {
            foreach($ad as $addr) {
                if ($addr_string) $addr_string .= ",";
                    $addr_string .= $addr->mailbox . "@" . $addr->host;
            }
            unset( $ad );
            unset( $addr );
        }
        return $addr_string;
    }

    
    public function Settings($settingsObject) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'START Settings' );
//        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'settingsObject = [' . print_r($settingsObject,true) . ']', false );

        if ($settingsObject instanceof SyncUserInformation) {

            $settingsObject->accountid = $this->_username;
//            $settingsObject->accountname = "Test";  TODO - Where does this come from ?
            $settingsObject->userdisplayname = $this->_sendAsName;
            $settingsObject->emailaddresses = $this->_addresses;
            $settingsObject->Status = 1;

            return $settingsObject;

        }
        if ($settingsObject instanceof SyncOOF) {

			$output = new SyncOOF();
			
            if (isset($settingsObject->oofstate)) {
				ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'oofstate is SET');
				
				if ($settingsObject->oofstate != 0) {
					// in case oof should be switched on do it here
					// store somehow your oofmessage in case your system supports. 
					// output["oof"]["status"] = true per default and should be false in case 
					// the oof message could not be set



					$newoofmsg = "";
                    $oofMessages = $settingsObject->oofmessage;

                    for ($i=0;$i<count($oofMessages);$i++) {
                        if (isset($oofMessages[$i]->appliesToInternal)) {
                            $internal = $i;
						    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'Applies to Internal ['. $i .']' );
                        } elseif (isset($oofMessages[$i]->appliesToExternal)) {
                            $external = $i;
						    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'Applies to External ['. $i .']' );
                        } elseif (isset($oofMessages[$i]->appliesToExternalUnknown)) {
                            $externalUnknown = $i;
						    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'Applies to ExternalUnknown ['. $i .']' );
                        }
					}

                    $newoofmsg = isset($oofMessages[$internal]->replymessage) ? $oofMessages[$internal]->replymessage : $newoofmsg;
                    $soap ='<BatchRequest xmlns="urn:zimbra" onerror="stop">';
					$soap .='<ModifyPrefsRequest xmlns="urn:zimbraAccount" requestId="base">
							<pref name="zimbraPrefOutOfOfficeReply">'.$newoofmsg.'</pref>
							<pref name="zimbraPrefOutOfOfficeReplyEnabled">TRUE</pref>
                            <pref name="zimbraPrefExternalSendersType">ALL</pref>';

					if ($settingsObject->oofstate == 1) {
						$soap .= '<pref name="zimbraPrefOutOfOfficeFromDate"></pref>
								  <pref name="zimbraPrefOutOfOfficeUntilDate"></pref>';
					} else {
	// 08/03/11 12:25:42 [11140] START Date4Zimbra { ts = 2011-08-04T14:00:49.000Z; tz = XML }



						$starttime = str_replace( "T", "", $this->Date4Zimbra( $settingsObject->starttime, "XML" ) );
						$endtime = str_replace( "T", "", $this->Date4Zimbra( $settingsObject->endtime, "XML" ) );

						$soap .= '<pref name="zimbraPrefOutOfOfficeFromDate">'.$starttime.'</pref>
								  <pref name="zimbraPrefOutOfOfficeUntilDate">'.$endtime.'</pref>';
					}
					$soap .= '</ModifyPrefsRequest>';

					$newoofmsg = "";
                    $soap .='<ModifyPrefsRequest xmlns="urn:zimbraAccount" requestId="external">';
                    if (($oofMessages[$externalUnknown]->enabled == 1) || ($oofMessages[$external]->enabled == 1)) {

                        if (($oofMessages[$externalUnknown]->enabled == 1) && ($oofMessages[$external]->enabled == 1)) {
                            $newoofmsg = isset($oofMessages[$externalUnknown]->replymessage) ? $oofMessages[$externalUnknown]->replymessage : $newoofmsg;
                            if ($newoofmsg == $oofMessages[$external]->replymessage) {
                                $soap .= '<pref name="zimbraPrefOutOfOfficeExternalReply">'.$newoofmsg.'</pref>';
                            } else {
                                $soap .= '<pref name="zimbraPrefOutOfOfficeExternalReply">'.$oofMessages[$external]->replymessage.'</pref>';
                            }
                        } elseif ($oofMessages[$externalUnknown]->enabled == 1) {
                            $newoofmsg = isset($oofMessages[$externalUnknown]->replymessage) ? $oofMessages[$externalUnknown]->replymessage : $newoofmsg;
                            $soap .= '<pref name="zimbraPrefOutOfOfficeExternalReply">'.$newoofmsg.'</pref>';
                            $soap .= '<pref name="zimbraPrefExternalSendersType">ALLNOTINAB</pref>';
                        } elseif ($oofMessages[$external]->enabled == 1) {
                            $newoofmsg = isset($oofMessages[$external]->replymessage) ? $oofMessages[$external]->replymessage : $newoofmsg;
                            $soap .= '<pref name="zimbraPrefOutOfOfficeExternalReply">'.$newoofmsg.'</pref>';
                        }
                        $soap .= '<pref name="zimbraPrefOutOfOfficeExternalReplyEnabled">TRUE</pref>';
                    } else {
                        $soap .= '<pref name="zimbraPrefOutOfOfficeExternalReplyEnabled">FALSE</pref>';
                    }
					$soap .= '</ModifyPrefsRequest>';
                    $soap .= '</BatchRequest>';

                    $returnJSON = true;
                    $response = $this->SoapRequest($soap, false, false, $returnJSON);
                    if($response) {
                        $array = json_decode($response, true);
						unset($response);
						
						if (isset($array['Body']['BatchResponse']['ModifyPrefsResponse'])) {
                            $settingsObject->Status = 1; 
						} else {
                            $settingsObject->Status = 0;
						}
						unset($array);
							
					} else {
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF:Got No SOAP Response' );
                        $settingsObject->Status = 0;
					}        
			
				} else {
					// in case oof should be switched off do it here

					$soap ='<ModifyPrefsRequest xmlns="urn:zimbraAccount">
							<pref name="zimbraPrefOutOfOfficeReplyEnabled">FALSE</pref>
							</ModifyPrefsRequest>';
										
                    $returnJSON = true;
                    $response = $this->SoapRequest($soap, false, false, $returnJSON);
                    if($response) {
                        $array = json_decode($response, true);
						unset($response);

						if (isset($array['Body']['ModifyPrefsResponse'])) {
                            $settingsObject->Status = 1;
						} else {
                            $settingsObject->Status = 0;
						}
						unset($array);
							
					} else {
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF:Got No SOAP Response' );
                        $settingsObject->Status = 0;
					}        
			
				}
				
			} else {
				ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'oofstate is NOT SET - This is a GET request');
			
                $soap ='<BatchRequest xmlns="urn:zimbra" onerror="stop">';
                $soap .='<GetPrefsRequest xmlns="urn:zimbraAccount" requestId="base">
							<pref name="zimbraPrefOutOfOfficeReplyEnabled"/>
							<pref name="zimbraPrefOutOfOfficeReply"/>
							<pref name="zimbraPrefOutOfOfficeFromDate"/>
							<pref name="zimbraPrefOutOfOfficeUntilDate"/>
							<pref name="zimbraPrefExternalSendersType"/>
						</GetPrefsRequest>';
				$soap .='<GetPrefsRequest xmlns="urn:zimbraAccount" requestId="external">
							<pref name="zimbraPrefOutOfOfficeExternalReplyEnabled"/>
							<pref name="zimbraPrefOutOfOfficeExternalReply"/>
							<pref name="zimbraPrefExternalSendersType"/>
						</GetPrefsRequest>';
                $soap .= '</BatchRequest>';

                $returnJSON = true;
                $response = $this->SoapRequest($soap, false, false, $returnJSON);
                if($response) {
                    $array = json_decode($response, true);

                    if ($array['Body']['BatchResponse']['GetPrefsResponse'][0]['requestId'] == 'base') {
                        $baseResponse = 0;
                        $externalResponse = 1;
                    } else {
                        $baseResponse = 1;
                        $externalResponse = 0;
                    }

					$prefsBase = $array['Body']['BatchResponse']['GetPrefsResponse'][$baseResponse]['_attrs'];
					$prefsExternal = $array['Body']['BatchResponse']['GetPrefsResponse'][$externalResponse]['_attrs'];

					unset($array);

					$oofenabled = false;
					$oofmessageInt = $oofmessageExtKnown = $oofmessageExtUnknown = "None";
						
					if (isset($prefsBase['zimbraPrefOutOfOfficeReplyEnabled']) ) {
						$oofenabled = $this->ToBool( $prefsBase['zimbraPrefOutOfOfficeReplyEnabled'] );
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF Switch Enabled [' . $oofenabled . '] ');
					} 
                    if (isset($prefsBase['zimbraPrefOutOfOfficeReply']) ) {
						$oofmessageInt = $prefsBase['zimbraPrefOutOfOfficeReply'];
						$oofmessageExtKnown = $prefsBase['zimbraPrefOutOfOfficeReply'];
						$oofmessageExtUnknown = $prefsBase['zimbraPrefOutOfOfficeReply'];
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF Message Int [' . $oofmessageInt . '] ');
					} 
					if (isset($prefsBase['zimbraPrefOutOfOfficeFromDate']) ) {
						$ooffrom = $prefsBase['zimbraPrefOutOfOfficeFromDate'];
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF FromDate [' . $ooffrom . '] ');
					} 
                    if (isset($prefsBase['zimbraPrefOutOfOfficeUntilDate']) ) {
						$oofuntil = $prefsBase['zimbraPrefOutOfOfficeUntilDate'];
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF UntilDate [' . $oofuntil . '] ');
					}

					if (isset($prefsExternal['zimbraPrefExternalSendersType']) ) {
						$oofexttype = strtoupper( $prefsExternal['zimbraPrefExternalSendersType'] );
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF External Type [' . $oofexttype . '] ');
					} 
					if (isset($prefsExternal['zimbraPrefOutOfOfficeExternalReplyEnabled']) ) {
						$oofextenabled = strtoupper( $prefsExternal['zimbraPrefOutOfOfficeExternalReplyEnabled'] );
						ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF External Enabled [' . $oofextenabled . '] ');
					} 
                    if (isset($prefsExternal['zimbraPrefOutOfOfficeExternalReplyEnabled']) && ($prefsExternal['zimbraPrefOutOfOfficeExternalReplyEnabled']) == 'TRUE') {
                        if (!isset($oofexttype) || ($oofexttype == 'ALL')) {
                            $oofmessageExtKnown = $prefsExternal['zimbraPrefOutOfOfficeExternalReply'];
                            $oofmessageExtUnknown = $prefsExternal['zimbraPrefOutOfOfficeExternalReply'];
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF Message Ext [' . $oofmessageExtKnown . '] ');
                        } else {
                            $oofmessageExtKnown = $prefsExternal['zimbraPrefOutOfOfficeExternalReply'];
                            $oofmessageExtUnknown = $prefsExternal['zimbraPrefOutOfOfficeExternalReply'];
                            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF Message Ext [' . $oofmessageExtKnown . '] ');
                        }
					} 
					
				} else {
					ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'Got No SOAP Response' );
				}        

				if ((isset($ooffrom)) && (isset($oofuntil))) {
					// return oof messsage and where it should apply here
					$settingsObject->oofstate = 2; // Set it to 2 which implies time-based
		//                if (isset($ooffrom)) $output["oof"]["starttime"] = substr($ooffrom,0,8) ."T". substr($ooffrom,8); //substr($ooffrom,0,8) . "T000000"; 
		//                if (isset($oofuntil)) $output["oof"]["endtime"] = substr($oofuntil,0,8) ."T". substr($oofuntil,8); //substr($oofuntil,0,8) . "T000000";
					$settingsObject->starttime = $this->Date4ActiveSync(substr($ooffrom,0,8) ."T". substr($ooffrom,8), "UTC"); 
					$settingsObject->endtime = $this->Date4ActiveSync(substr($oofuntil,0,8) ."T". substr($oofuntil,8), "UTC");
				} else {
					$settingsObject->oofstate = 1; // Set it to 1 which implies global
				}			

				if ( !$oofenabled ) {
					$settingsObject->oofstate = 0; // Set it to 0 which implies disabled
				}
				if (strtolower($settingsObject->bodytype) == 'html') {
					$bodytype = 'HTML';
				} else {
					$bodytype = 'Text';
				}
				
				unset($settingsObject->bodytype);
				
				ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'OOF State ? [' . $settingsObject->oofstate . '] ');
				// the above 2 elements are common to all 3 oof messages - the elements below change

				$internalOOFMessage = new SyncOOFMessage();
				$internalOOFMessage->appliesToInternal = "";
				$internalOOFMessage->enabled = "1";
				$internalOOFMessage->bodytype = $bodytype;
				$internalOOFMessage->replymessage = $oofmessageInt;
				$settingsObject->oofmessage[] = $internalOOFMessage;

				$extKnownOOFMessage = new SyncOOFMessage();
				$extKnownOOFMessage->appliesToExternal = "";
                if (!(isset($oofextenabled)) 
                     || (isset($oofextenabled) && ($oofextenabled == 'TRUE')) && (isset($oofexttype) && ($oofexttype == 'ALL'))
                     || ((isset($oofextenabled) && ($oofextenabled == 'FALSE')))) {
    				$extKnownOOFMessage->enabled = "1";
                } else {
    				$extKnownOOFMessage->enabled = "0";
                }
				$extKnownOOFMessage->bodytype = $bodytype;
				$extKnownOOFMessage->replymessage = $oofmessageExtKnown;
				$settingsObject->oofmessage[] = $extKnownOOFMessage;

				$extUnknownOOFMessage = new SyncOOFMessage();
				$extUnknownOOFMessage->appliesToExternalUnknown = "";
                if (!(isset($oofextenabled)) 
                     || (isset($oofextenabled) && ($oofextenabled == 'TRUE')) && (isset($oofexttype) && (($oofexttype == 'ALLNOTINAB') || ($oofexttype == 'ALL')))
                     || ((isset($oofextenabled) && ($oofextenabled == 'FALSE')))) {
    				$extUnknownOOFMessage->enabled = "1";
                } else {
    				$extUnknownOOFMessage->enabled = "0";
                }
				$extUnknownOOFMessage->bodytype = $bodytype;
				$extUnknownOOFMessage->replymessage = $oofmessageExtUnknown;
				$settingsObject->oofmessage[] = $extUnknownOOFMessage;

				$settingsObject->Status = 1;
				
//		ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'Final OOF '.print_r( $settingsObject, true ) );
			}
        }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  "Output: " . print_r( $output, true ));		
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Settings(): ' .  'END Settings' );
	return $settingsObject;
    }

    

    /** SoapRequest
     *   Make a SOAP request to Zimbra server returns the XML
     */
//    protected function SoapRequest($body,$header=false,$connecting=false) {
    function SoapRequest($body,$header=false,$connecting=false,$returnJSON=false) {
        if(!$connecting && !$this->_connected) {
            throw new Exception('zimbra.class: soapRequest called without a connection to Zimbra server');
        }

        usleep($this->_soapDelayMicroSeconds); 
        $DosFilterError = false;

        if($header==false) {
            $header = '<context xmlns="urn:zimbra">
                            <authToken>'.$this->_authtoken.'</authToken>
                            <session id="'.$this->_sessionid.'" />';
                            if ($returnJSON == true) {
                                $header .= '<format type="js" />';
                            }
            $header .= '    <userAgent name="'.$this->_ua.$this->_deviceIdForMailboxLog.' devip='.$this->_xFwdForForMailboxLog.' ZPZB" version="'.$GLOBALS['revision'].'" />
                        </context>';
        }

        $soap_message = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                            <soap:Header>'.$header.'</soap:Header>
                            <soap:Body>'.$body.'</soap:Body>
                         </soap:Envelope>';

        $soap_message_debug = preg_replace('/<password>(.*)<\/password>/','<password>**********</password>',$soap_message);

        if (defined('ZIMBRA_DEBUG')) {
            if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP Message: '.$soap_message_debug);
            }
		}

        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $soap_message);

        $DosFilterRetry = true; 
        $DosFilterRetryCount = 2;

        while ($DosFilterRetry == true) {

            $response = curl_exec($this->_curl);

            // Check for 503 Error first - and try to handle it by waiting and representing the request after a second (2 retries - then give up)
            if (($response) && (stripos($response,'html')==1) && (stripos($response,'Error 503 Service Unavailable')!==false)) {
                // NOTE: Not checking for <html> as would have to differentiate between position 0 and false (for not found)
                if (defined('ZIMBRA_DEBUG')) {
                    if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
                    }
                }
                $this->error = $this->ExtractHtmlErrorTitle($response,$returnJSON);
                $words = explode( " ", $this->error);
                $this->_soapError = "HTML." . $words[1];
			    $this->error = 'SOAP FAULT: HTML Error Returned - ' . $this->error;
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error);
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'If using zimbra 8 or later please make sure to whitelist the z-push server IP address(es) in the DoSFilter' );
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'See zimbra wiki for details - http://wiki.zimbra.com/wiki/DoSFilter' );
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'DoSFilter trap - See z-push-error.log - Delaying one second before continuing ...' );
                usleep(1000000); // One second
                $this->_soapDelayMicroSeconds += 40000; // 1/25th of a second
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'DoSFilter trap - Setting/Increasing session SOAP Request Delay to ['.$this->_soapDelayMicroSeconds.'] microseconds' );
                $DosFilterRetryCount -= 1;
                if ($DosFilterRetryCount <= 0) { 
                    $DosFilterRetry = false; 
                } 
            } else {
                $DosFilterRetry = false; 
            }
        }

        if (!$response) {
            if (defined('ZIMBRA_DEBUG')) {
                if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
                }
            }
            $this->_soapError = 'CURL.'.curl_errno($this->_curl);
            $this->error = 'ERROR: curl_exec - ('.curl_errno($this->_curl).') '.curl_error($this->_curl);
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error);
            $response = false;
        }
        elseif (($returnJSON == true) && (strpos($response,'"Body":{"Fault":')!==false)) {
            if (defined('ZIMBRA_DEBUG')) {
                if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
                }
            }
            $this->_soapError = $this->ExtractErrorCode($response,$returnJSON);
            $this->error = 'SOAP FAULT: Error Code   ['.$this->_soapError.'] ';
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error);
            $errorReason = $this->ExtractErrorReason($response,$returnJSON);
            $this->error = 'SOAP FAULT: Error Reason ['.$errorReason.'] ';
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error);
            $response = false;
            if ($this->_soapError == "service.AUTH_EXPIRED") {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'zimbra Auth Token has expired - Throw SYNC_COMMONSTATUS_SERVERERROR to force a new connection/re-auth' );
                throw new StatusException("Zimbra->SoapRequest(): Auth Token Expired - Force client to reconnect.", SYNC_COMMONSTATUS_SERVERERROR);
            } elseif ($this->_soapError == "service.PERM_DENIED") {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'SOAP Message: '.$soap_message_debug);
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
            }

        }
        elseif (strpos($response,'<soap:Body><soap:Fault>')!==false) {
            if (defined('ZIMBRA_DEBUG')) {
                if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
                }
            }
            // Some SOAP faults are not returning JSON under error conditions - Set the returnJSON flag to false
            // if '<soap:Body><soap:Fault>' was found - regardless of what it should have been. This will also 
            // allow the session id to be retrieved properly below too.
            $returnJSON = false;
            $this->_soapError = $this->ExtractErrorCode($response,$returnJSON);
            $this->error = 'SOAP FAULT: Error Code   ['.$this->_soapError.'] ';
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error);
            $errorReason = $this->ExtractErrorReason($response,$returnJSON);
            $this->error = 'SOAP FAULT: Error Reason ['.$errorReason.'] ';
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error);
            $response = false;
            if ($this->_soapError == "service.AUTH_EXPIRED") {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'zimbra Auth Token has expired - Throw SYNC_COMMONSTATUS_SERVERERROR to force a new connection/re-auth' );
                throw new StatusException("Zimbra->SoapRequest(): Auth Token Expired - Force client to reconnect.", SYNC_COMMONSTATUS_SERVERERROR);
            } elseif ($this->_soapError == "service.PERM_DENIED") {
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'SOAP Message: '.$soap_message_debug);
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
            }
        }
        elseif (stripos($response,'html')==1) {
            // NOTE: Not checking for <html> as would have to differentiate between position 0 and false (for not found)
            if (defined('ZIMBRA_DEBUG')) {
                if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP Response: '.$response, false);
                }
            }
            $returnJSON = false;
            $this->error = $this->ExtractHtmlErrorTitle($response,$returnJSON);
            $words = explode( " ", $this->error);
            $this->_soapError = "HTML." . $words[1];
			$this->error = 'SOAP FAULT: HTML Error Returned - ' . $this->error;
            ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . $this->error . ' - Enable ZIMBRA_DEBUG for more details - returning { false }' );
            // If SOAP is not available for any reason HTML Error page returned - Set the returnJSON flag to false
            if ($this->_soapError == 'HTML.503') {
                $DosFilterError = true;
                ZLog::Write(LOGLEVEL_ERROR, 'Zimbra->SoapRequest(): ' . 'DoSFilter trap - Request FAILED after 2 delays ! - returning { false }' );
            }
            $response = false;
        }

        if ($response) {
            $newSessionId =  $this->ExtractSessionID($response,$returnJSON);

            if (!isset($this->_sessionid)) {
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' .  'Session Id - NEW ['.$newSessionId.']' );
                $this->_sessionid = $newSessionId;
            } else if ($newSessionId != $this->_sessionid) {
                if (($newSessionId != "") && ($this->_sessionid != "")) {
                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' .  'Session Id changed - OLD ['.$this->_sessionid.'] -> NEW ['.$newSessionId.']' );
                }
                $this->_sessionid = $newSessionId;
                $this->_sessionIdChanged = true;
            }
        }

        if (defined('ZIMBRA_DEBUG')) {
            if ((ZIMBRA_DEBUG === true) || (stripos(ZIMBRA_DEBUG, $this->_username) !== false)) {
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->SoapRequest(): ' . 'SOAP response: '.$response, false);
            }
        }

        return $response;
    } // end SoapRequest


    /** MakeXMLTree
     *   Turns XML into an array
     */
//    protected function MakeXMLTree($data, $compact=false) {
    function MakeXMLTree($data, $compact=false) {
        // create parser
        $parser = xml_parser_create();
        xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
        xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
        xml_parse_into_struct($parser,$data,$values,$tags);
        xml_parser_free($parser);

        // we store our path here
        $hash_stack = array();

        // this is our target
        $ret = array();
        foreach ($values as $key => $val) {
            if ($compact==false) {
                switch ($val['type']) {
                    case 'open':
                        array_push($hash_stack, $val['tag']);
                        if (isset($val['attributes']))
                            $ret = $this->ComposeArray($ret, $hash_stack, $val['attributes']);
                        else
                            $ret = $this->ComposeArray($ret, $hash_stack);
                    break;

                    case 'close':
                        array_pop($hash_stack);
                    break;

                    case 'complete':
                        array_push($hash_stack, $val['tag']);
                        //$ret = $this->ComposeArray($ret, $hash_stack, $val['value']);
                        if (isset($val['value'])) {
                            $ret = $this->ComposeArray($ret, $hash_stack, $val['value']);
                        } else if (isset($val['attributes'])) {
                            $ret = $this->ComposeArray($ret, $hash_stack, $val['attributes']);
                        } else {
                            $ret = $this->ComposeArray($ret, $hash_stack, '');
                        }
                        array_pop($hash_stack);

                        // handle attributes
                        if (isset($val['attributes']))
                        {
                            foreach($val['attributes'] as $a_k=>$a_v)
                            {
                                $hash_stack[] = $val['tag'].'_attribute_'.$a_k;
                                $ret = $this->ComposeArray($ret, $hash_stack, $a_v);
                                array_pop($hash_stack);
                            }
                        }
                    break;
                }
            } else {
                if ($val['type'] <> 'close') {
                    array_push($ret, $val['tag']);
                    if (isset($val['attributes'])) {
                    	$ret[sizeof($ret)-1] = $val['attributes'];
                        $ret[sizeof($ret)-1]["tag"] = $val['tag'];
                  	} 
                }
            }
        }
        unset($hash_stack);
        unset($key);
        unset($val);
        return $ret;
    } // end MakeXMLTree


    /** &ComposeArray
     *   Function used exclusively by MakeXMLTree to help turn XML into an array
    */
    private function &ComposeArray($array, $elements, $value=array()) {
        // get current element
        $element = array_shift($elements);
        if (!array_key_exists($element, $array)) {
            $array[$element]=array();
        }

        // does the current element refer to a list
        if(sizeof($elements) > 0) {
            $indx = sizeof($array[$element])-1;
            $array[$element][$indx] = &$this->ComposeArray($array[$element][$indx], $elements, $value);
        } else {
            if(array_key_exists($element, $array)) {
                $indx = sizeof($array[$element]);
            } else {
                $indx = 0;
            }
            $array[$element][$indx] = $value;
        }
        return $array;
    } // end ComposeArray


    /** ExtractAuthToken
     *   Get the Auth Token out of the XML
     */
    private function ExtractAuthToken($xml) {
        $authTokenMarker = strpos($xml, "<authToken");
        $authTokenPos = strpos($xml, ">", $authTokenMarker) + 1;
        $authTokenMarker = strpos($xml, "<", $authTokenPos);
        $auth_token = substr($xml, $authTokenPos, $authTokenMarker-$authTokenPos);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ExtractAuthToken(): ' . 'AuthToken ['.$auth_token.']');
        return $auth_token;
    } // end ExtractAuthToken


    /** ExtractSessionID
     *   Get the Session ID out of the XML
     */
    private function ExtractSessionID($xml, $returnJSON=false) {
//"Header":{"context":{"session":{"id":"12717","_content":"12717"},"change":{"token":343697},"_jsns"
        // If curl_error ot SoapFault occurred - $response will be false before calling this function.
        if ($xml == false) {
            $session_id = "";
            return $session_id;
        }
		if ($returnJSON) {
            $bodyMarker = strpos($xml, ',"Body":');
			$context = substr($xml, 0, $bodyMarker) . '}';
            $array = json_decode($context, true);
            if (isset($array['Header']['context']['session']['_content'])) {
                $session_id = $array['Header']['context']['session']['_content'];
            } else $session_id = "";
		} else {
            // Changed "<session id" to "<session" to match for z5 as well as z6
            $sessionMarker = strpos($xml, "<session");
            $sessionPos = strpos($xml, ">", $sessionMarker) + 1;
            $sessionMarker = strpos($xml, "<", $sessionPos);
            $session_id = substr($xml, $sessionPos, $sessionMarker-$sessionPos);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ExtractSessionID(): ' . 'SessionId ['.$session_id.']');
        }
        return $session_id;
    } // end ExtractSessionID


    /** ExtractErrorCode
     *   Get the error code out of the XML
     */
    private function ExtractErrorCode($xml, $returnJSON=false) {
		if ($returnJSON) {
            $array = json_decode($xml, true);
            if (isset($array['Body']['Fault']['Detail']['Error']['Code'])) {
                $errorCode = $array['Body']['Fault']['Detail']['Error']['Code'];
            } 
		} else {
            $errorMarker = strpos($xml, "<Code");
            $errorPos = strpos($xml, ">", $errorMarker) + 1;
            $errorMarker = strpos($xml, "<", $errorPos);
            $errorCode = substr($xml, $errorPos, $errorMarker-$errorPos);
        }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ExtractErrorCode(): ' . 'errorCode ['.$errorCode.']');
        return $errorCode;
    } // end ExtractErrorCode


    /** ExtractErrorReason
     *   Get the error code out of the XML
     */
    private function ExtractErrorReason($xml, $returnJSON=false) {
		if ($returnJSON) {
            $array = json_decode($xml, true);
            if (isset($array['Body']['Fault']['Reason']['Text'])) {
                $errorReason = $array['Body']['Fault']['Reason']['Text'];
            } 
		} else {
            $errorMarker = strpos($xml, "<Text");
            $errorPos = strpos($xml, ">", $errorMarker) + 1;
            $errorMarker = strpos($xml, "<", $errorPos);
            $errorReason = substr($xml, $errorPos, $errorMarker-$errorPos);
        }
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ExtractErrorCode(): ' . 'errorCode ['.$errorCode.']');
        return $errorReason;
    } // end ExtractErrorCode


    /** ExtractHtmlErrorTitle
     *   Get the Html Error Title out of the XML
     */
    private function ExtractHtmlErrorTitle($xml) {
        $errorTitleMarker = strpos($xml, "<title");
        $errorTitlePos = strpos($xml, ">", $errorTitleMarker) + 1;
        $errorTitleMarker = strpos($xml, "<", $errorTitlePos);
        $error_title = substr($xml, $errorTitlePos, $errorTitleMarker-$errorTitlePos);
ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ExtractHtmlErrorTitle(): ' . 'errorTitle ['.$error_title.']');
        return $error_title;
    } // end ExtractHtmlErrorTitle


    /** GetRawMessageStats
     *   Get stats on the mail message (part) 
     */
    function GetRawMessageStats($id, $part="") {
        $ch = curl_init();
        if (!$id) { return null; }
        $url = $this->_publicURL . "/service/content/get?id=" . $id;
        if ($part != "") {
            $url = $url . "&part=" . $part;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
        curl_setopt($ch, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN=' . $this->_authtoken);
        $response = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_close($ch);
        unset( $ch );
        return $curlInfo;
    } // end GetRawMessageStats


    /** GetRawMessage
     *   Get entire mail message (part) in plain text format, not in SOAP (SOAP truncates large messages)
     */
    function GetRawMessage($id, $part="") {
        $ch = curl_init();
        if (!$id) { return null; }
        $url = $this->_publicURL . "/service/content/get?id=" . $id;
        if ($part != "") {
            $url = $url . "&part=" . $part;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
        curl_setopt($ch, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN=' . $this->_authtoken);
        $http_header = array();
        $http_header[] = 'X-Forwarded-For: ' . $this->_xFwdForForMailboxLog;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        $fileContents = curl_exec($ch);
        curl_close($ch);
        return $fileContents;
    } // end GetRawMessage


    /** GetRawMessageStream
     *   Get Stream for entire mail message (part) in plain text format, not in SOAP (SOAP truncates large messages)
     */
    function GetRawMessageStream($id, $part="") {
        $url = $this->_publicURL . "/service/content/get?id=" . $id;
        if ($part != "") {
            $url = $url . "&part=" . $part;
        }
        $opts = array('http' => array(
                          'method' => 'GET',
                          'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n" . 'Cookie: ' .  'ZM_AUTH_TOKEN=' . $this->_authtoken . "\r\n"
                      ),
                      'ssl'=>array(
                          'verify_peer' => $this->_sslVerifyPeer,
                          'verify_peer_name' => (!(0 == $this->_sslVerifyHost))
                      ),
        );

        $context = stream_context_create($opts);

        $stream = fopen($url, 'rb', false, $context);
        return $stream;
    } // end GetRawMessageStream


    /** GetFlags
     *   Extract and decode message flags
     *    (u)nread
     *    (f)lagged
     *    has (a)ttachment
     *    (r)eplied
     *    (s)ent by me
     *    for(w)arded
     *    (d)raft
     *    deleted (x)
     *    (n)otification sent
     *    calendar in(v)ite
     *    urgent (!)
     *    low-priority (?)
     *    s(y)nc folder with external data source
     */
    private function GetFlags($flags) {
        $flag = array();
        $flag["unread"] = 0;
        $flag["read"] = 1;
        $flag["priority"] = 1;
        $flag["external"] = 0;
        $flag["flagged"] = 0;
        $flag["replied"] = 0;
        $flag["forwarded"] = 0;
        $flag["flagRepFwd"] = "nnn";

        $strlen = strlen($flags);
        for ($i=0;$i<$strlen;$i++) {
            $char = substr(strtolower($flags),$i,1);
            switch ($char) {
                case 'f': 
                    $flag["flagged"] = 1; $flag["flagRepFwd"][0] = "F"; break;
                case 'r':
                    $flag["replied"] = 1; $flag["flagRepFwd"][1] = "R"; break;
                case 'w':
                    $flag["forwarded"] = 1; $flag["flagRepFwd"][2] = "W"; break;
                case 'u':
                    $flag["unread"] = 1; 
                    $flag["read"] = 0; 
					break;
                case '!':
                    $flag["priority"] = 2; break;
                case '?':
                    $flag["priority"] = 0; break;
                case 'y':
                    $flag["external"] = 1;
            }
        }
        return $flag;
    }


    /** ConvertItemIdToInvID
     *   Convert Item ID to an Invitation ID (invID).  Needed by ModifyAppointmentRequest.  Nokia cannot handle "-" in Item IDs
     */
    function ConvertItemIdToInvID($id) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ConvertItemIdToInvID(): ' . 'START ConvertItemIdToInvID ('.$id.')');
        $invId = 0;

        if (strrpos($id,':') === false) {
            $soap ='<GetAppointmentRequest id="'.$id.'" xmlns="urn:zimbraMail" />';
        } else {
            $parts = explode(":",$id);
            $soap ='<GetAppointmentRequest id="'.$id.'" xmlns="urn:zimbraMail" />';
        }
        
        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);
            unset($response);

            $invId = $id .'-'. $array['Body']['GetAppointmentResponse']['appt'][0]['inv'][0]['id'];
            unset($array);
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->ConvertItemIdToInvID(): ' . 'END ConvertItemIdToInvID ( invID = ' . $invId . ' )');
        return $invId;
    } // end ConvertItemIdToInvID


    /** GetInvIDFromMsgID
     *   Convert Msg ID to an Invitation ID (invID).  Needed by MeetingResponse.  
     */
    function GetInvIDFromMsgID($id) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetInvIDFromMsgID(): ' . 'START GetInvIDFromMsgID ('.$id.')');
        $invId = 0;

        if (strrpos($id,':') === false) {
            $soap ='<GetItemRequest xmlns="urn:zimbraMail"> <item id="'.$id.'"  /></GetItemRequest>';
        } else {
            $parts = explode(":",$id);
            $soap ='<GetItemRequest xmlns="urn:zimbraMail"> <item id="'.$id.'"  /></GetItemRequest>';
        }
        
        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);
//			debugLog( 'ITEM:' . print_r( $array, true), false );
            unset($response);

            if (isset($array['Body']['GetItemResponse']['appt'][0]['inv'][0]['id'])) {
                $invId = $id .'-'. $array['Body']['GetItemResponse']['appt'][0]['inv'][0]['id'];
            } elseif (isset($array['Body']['GetItemResponse']['m'][0]['inv'][0]['apptId'])) {
                $invId = $array['Body']['GetItemResponse']['m'][0]['inv'][0]['apptId'] .'-'. $id;
            } else {
                $invId = $id;
            }
            unset($array);
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetInvIDFromMsgID(): ' . 'END GetInvIDFromMsgID ( invID = ' . $invId . ' )');
        return $invId;
    } // end ConvertItemIdToInvID


    /** DownloadFromZimbra
     *   Convert download (binary format) from URL to Base64
     */
    function DownloadFromZimbra($url) {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
// TODO: Can't we just use ZM_AUTH_TOKEN like what was done in the function GetRawMessage?
//curl_setopt($ch, CURLOPT_USERPWD, $this->_username . ":" . $this->_password);          // set username / password
        curl_setopt($ch, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN=' . $this->_authtoken);
        $http_header = array();
        $http_header[] = 'X-Forwarded-For: ' . $this->_xFwdForForMailboxLog;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        $fileContents = curl_exec($ch);
        curl_close($ch);
        return base64_encode($fileContents);
    } // end DownloadFromZimbra


    /** UploadToZimbra
     *   Convert Base64 and upload to Zimbra
     */
    function UploadToZimbra($data) {
        // Temporarly save content as file
        $temp_file = tempnam($this->GetTempDir(), 'ZIMBRA_FILE_') . '.png';;
        $handle = fopen($temp_file, "w");
        fwrite($handle, base64_decode($data));
        fclose($handle);
//        rename($temp_file, $temp_file.'.png');
//        $temp_file .= '.png';
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->UploadToZimbra(): ' . 'Filename: ' . $temp_file);

        // Update for PHP 5.5 and later - See https://sourceforge.net/p/zimbrabackend/support-requests/105/
        if (function_exists("curl_file_create")) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->UploadToZimbra(): ' . 'PNG uploaded to temporary file ['.$temp_file.'] using new CURLfile object' );
            $cfile = curl_file_create( $temp_file, 'image/png' );
            $array = array('file' => $cfile,
                           'requestId' => 'client_upload_token');
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->UploadToZimbra(): ' . 'PNG uploaded to temporary file @['.$temp_file.'] ' );
            $array = array('file' => '@' . $temp_file,
                           'requestId' => 'client_upload_token');
        }

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $this->_publicURL . '/service/upload?fmt=raw');
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslVerifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->_sslVerifyHost);
        // TODO: Can't we just use ZM_AUTH_TOKEN like what was done in the function GetRawMessage?
        //curl_setopt($ch, CURLOPT_USERPWD, $this->_username .":". $this->_password);
        curl_setopt($ch, CURLOPT_COOKIE, 'ZM_AUTH_TOKEN='. $this->_authtoken);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (defined('ZIMBRA_URL_ALLOW_REDIRECT') && ($this->ToBool(ZIMBRA_URL_ALLOW_REDIRECT) === true)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Logon(): ' .  'ZIMBRA_URL_ALLOW_REDIRECT is TRUE in config.php - Redirection is ALLOWED' );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
        $http_header = array();
        $http_header[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg, image/png';
        $http_header[] = 'X-Forwarded-For: ' . $this->_xFwdForForMailboxLog;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);

        @unlink($temp_file);
        $tokens = explode(',',$response);
        $server_token = trim(trim($tokens[2]), '\'');
        return $server_token;
    } // end UploadToZimbra


    /** GetTempDir
     *   Get a directory that can be used for a temporary file
     */
    function GetTempDir() {
        // Try to get from environment variable
        if ( !empty($_ENV['TMP']) ) {
            return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) ) {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) ) {
            return realpath( $_ENV['TEMP'] );
        } else {  // Detect by creating a temporary file
            // Try to use system's temporary directory as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file ) {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            } else {
                return false;
            }
        }
    } // end GetTempDir


    /** GetBit
     *   Function takes the decimal number and the Nth bit (1 to 31)
     *   Returns the value of Nth bit from decimal
     */
    protected function GetBit($decimal, $N){
        // Shifting the 1 for N-1 bits
        $constant = 1 << ($N-1);

        // if the bit is set, return 1
        if( $decimal & $constant ){
            return 1;
        }

        // If the bit is not set, return 0
        return 0;
    } // end GetBit


    /** IsBool
     *
     */
    function IsBool($var) {
        if (is_bool($var)) {
            $out = 1;
        } else if (is_int($var)) {
            if ($var == -1 || $var == 0 || $var == 1) {
                $out = 1;
            }
        } else {
            switch ($var) {
                case strtolower($var) == 'true':
                case strtolower($var) == 'false':
                case strtolower($var) == 't':
                case strtolower($var) == 'f':
                case strtolower($var) == 'on':
                case strtolower($var) == 'off':
                case strtolower($var) == 'yes':
                case strtolower($var) == 'no':
                case strtolower($var) == 'y':
                case strtolower($var) == 'n':
                    $out = 1;
                    break;
                default:
                    $out = 0;
            }
        }
        return $out;
    } // end IsBool


    /** ToBool
     *
     */
    public function ToBool($var) {
        if (is_bool($var)) {
            $out = $var;
        } else if (is_int($var)) {
            if ($var == -1 || $var == 1) {
                $out = true;
            } else {
                $out = false;
            }
        } else {
            switch ($var) {
                case strtolower($var) == 'true':
                case strtolower($var) == 't':
                case strtolower($var) == 'on':
                case strtolower($var) == 'yes':
                case strtolower($var) == 'y':
                    $out = true;
                    break;
                default:
                    $out = false;
            }
        }
        return $out;
    } // end ToBool

    
    /** Date4Zimbra
     *
     */
    function Date4Zimbra($ts, $tz, $utc = true, $allDay = false) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Date4Zimbra(): ' . "START Date4Zimbra { ts = " . $ts . "; tz = " . $tz . " }");
        $currTz = date_default_timezone_get();
        date_default_timezone_set($this->_tz);

        if ($tz == "XML") {
            date_default_timezone_set('UTC');
            $date = date('Ymd\THis\Z',$ts);
        } else if ($tz == "UTC") {
            date_default_timezone_set('UTC');
            $date = gmdate('Ymd\THis\Z',$ts);
        } else if ($tz == "XMLnoZ") {
            date_default_timezone_set('UTC');
            $date = date('Ymd\THis',$ts);
        } else if ($allDay === true) {
            date_default_timezone_set($this->_tz);
            $ts = $this->GetDayStartOfTimestamp($ts);
            $date = strftime("%m/%d/%Y", $ts);
        } else if (is_array($tz) === true) {
            date_default_timezone_set($tz['name']);
            $date = date('Ymd\THis',$ts);
        } else {
            date_default_timezone_set($tz);
            $date = date('Ymd\THis',$ts);
        }

        date_default_timezone_set($currTz);
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Date4Zimbra(): ' . "END Date4Zimbra { date = " . $date . " }");
        return $date;
    } // end Date4Zimbra
    
    
    /** Date4ActiveSync
     *
     */
    function Date4ActiveSync($date, $tz, $adjust4Utc = true, $allDay = false) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Date4ActiveSync(): ' . "START Date4ActiveSync { date = " . $date . "; tz = " . $tz . "; adjust4Utc = " . $adjust4Utc . "; allDay = " . $allDay . " }");
        $currTz = date_default_timezone_get();

        $changed = date_default_timezone_set($tz);

        if (!$changed) {
            // Vast majority of received timezones on zimbra will be in PHP Region/City format - Handle those that are not here
            $nonPhpTimezoneFile = str_replace('autodiscover/', '', BASE_PATH) . 'backend/zimbra/zimbraNonPhpTimezones.php';
			if (file_exists($nonPhpTimezoneFile)) {
                $tzLower = strtolower($tz);
                $tzLookupList = array();
                include($nonPhpTimezoneFile);
                if (isset($tzLookupList[$tzLower])) {
                    $changed = date_default_timezone_set($tzLookupList[$tzLower]);
                }
                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Date4ActiveSync(): ' .  'Non-PHP Timezonelist List: "' . $tzLower . '" ' . (($changed) ? " set as " . $tzLookupList[$tzLower] : "NOT FOUND - PLEASE ADD") );
            }
        }

        $isUtc = true;

        /* Possible Date Values
            1275619811 (No TZ)
            1275196862000 (Extra 3 Digits)
            20100827T045959Z
            20100601T003000 + TZ Tag
            April 15, 1980 (No TZ; Use Zimbra Pref) 
        */
        if (preg_match('/([0-9]{13})/i', $date, $matches)) {
            $ts = substr( $matches[1],0,10 );

        } else if (preg_match('/([0-9]{10})/i', $date, $matches)) {
            $ts = $matches[1];

        } else if (preg_match('/([0-9]\.)([0-9]{1,14})E\+([0-9]{1,2})/i', $date, $matches)) {
            $ts = substr( number_format( $date, 0, '.', '' ),0,10 );

        } else if (preg_match('/([0-9]{2,4})([0-9][0-9])([0-9][0-9])T([0-9][0-9])([0-9][0-9])([0-9][0-9])(\.[0-9][0-9])?Z/i', $date, $matches)) {
            if (isset($matches[7]) && substr($matches[7], 1) >= 50) {
                $matches[6]++;
            }
            $ts = gmmktime(intval($matches[4]), intval($matches[5]), intval($matches[6]), intval($matches[2]), intval($matches[3]), intval($matches[1]));

        } else if (preg_match('/([0-9]{2,4})([0-9][0-9])([0-9][0-9])T([0-9][0-9])([0-9][0-9])([0-9][0-9])(\.[0-9][0-9])?/i', $date, $matches)) {
            if (isset($matches[7]) && substr($matches[7], 1) >= 50) {
                $matches[6]++;
            }
            $ts = mktime(intval($matches[4]), intval($matches[5]), intval($matches[6]), intval($matches[2]), intval($matches[3]), intval($matches[1]));

        } else {
            $ts = strtotime($date);
        }

        date_default_timezone_set($currTz);
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->Date4ActiveSync(): ' . "END Date4ActiveSync { ts = " . $ts . " }");
        return $ts;        
    } // end Date4ActiveSync


    /** fixMS
     *
     */
    function fixMS($dateMS) {
        /* Possible dateMS Values
            1275196862000 (Unix date in milliseconds)
            1.275196862E+12 ("floated" date - from json_decode)
        */
        if (preg_match('/([0-9]\.)([0-9]{1,14})E\+([0-9]{1,2})/i', $dateMS, $matches)) {

            $ms = number_format( $dateMS, 0, '.', '' );
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->fixMS(): ' . 'Fixed date ['. $dateMS .'] => ['. $ms .']' );
        } else {
            $ms = $dateMS;
        }
        return $ms;        
    } // end fixMS


    /** GmtDate4ActiveSync
     *
     */
    function GmtDate4ActiveSync($date) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GmtDate4ActiveSync(): ' . "START GmtDate4ActiveSync { date = " . $date . " }");

        /* Possible Date Values
            20100827T045959Z
            20100601T003000 + TZ Tag
            April 15, 1980 (No TZ; Use Zimbra Pref) 
        */
        if (preg_match('/([0-9]{2,4})([0-9][0-9])([0-9][0-9])T([0-9][0-9])([0-9][0-9])([0-9][0-9])(\.[0-9][0-9])?Z/i', $date, $matches)) {
            if (isset($matches[7]) && substr($matches[7], 1) >= 50) {
                $matches[6]++;
            }
            $ts = gmmktime(intval($matches[4]),intval($matches[5]),intval($matches[6]),intval($matches[2]),intval($matches[3]),intval($matches[1]));

        } else if (preg_match('/([0-9]{2,4})([0-9][0-9])([0-9][0-9])T([0-9][0-9])([0-9][0-9])([0-9][0-9])(\.[0-9][0-9])?/i', $date, $matches)) {
            if (isset($matches[7]) && substr($matches[7], 1) >= 50) {
                $matches[6]++;
            }
            $ts = gmmktime(intval($matches[4]),intval($matches[5]),intval($matches[6]),intval($matches[2]),intval($matches[3]),intval($matches[1]));

        } else if (preg_match('/([0-9]{2,4})([0-9][0-9])([0-9][0-9])T([0-9][0-9])([0-9][0-9])([0-9][0-9])/i', $date, $matches)) {
            $ts = gmmktime(intval($matches[4]),intval($matches[5]),intval($matches[6]),intval($matches[2]),intval($matches[3]),intval($matches[1]));

        } else {
            $ts = false;
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GmtDate4ActiveSync(): ' . "END GmtDate4ActiveSync { ts = " . $ts . " }");
        return $ts;        
    } // end GmtDate4ActiveSync



    /* Must be an easier way to do this - but I can't find it right now. 
     *  Create a dummy appointment for now in User's preferred Timezone
     *  Response will contain the TZ object information. Then delete the appointment
     */
    function GetLocalTzObject($folderid, $tz) {
        $now = time();
				
        $start = date('Ymd\THis',$now);
        $end = date('Ymd\THis',$now+1800);  // 30 minute meeting

        $soap = '<CreateAppointmentRequest xmlns="urn:zimbraMail">
                    <m l="'.$folderid.'" d="'.$now.'000">
                    <inv>
                        <comp allDay="0" name="Dummy">
                            <s d="'.$start.'" tz="'.$tz.'"/>
                            <e d="'.$end.'" tz="'.$tz.'"/>
                        </comp>
                        </inv>
                    </m>
                 </CreateAppointmentRequest>';

        $returnJSON = true;
        $response = $this->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);

            unset($response);
			
            $id = $array['Body']['CreateAppointmentResponse']['invId'];
            unset($array);

            $soap = '<GetMsgRequest xmlns="urn:zimbraMail">
                         <m id="'.$id.'">*</m>
                     </GetMsgRequest>';

            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
            if($response) {
                $array = json_decode($response, true);
                unset($response);

                $tzInfo = $array['Body']['GetMsgResponse']['m'][0]['inv'][0]['tz'][0];
                unset($array);

                $tzObject = $this->GetTz($tzInfo);
            }

            $soap = '<ItemActionRequest xmlns="urn:zimbraMail">
                        <action id="'.$id.'" op="trash"/>
                    </ItemActionRequest>';
									
            $returnJSON = true;
            $response = $this->SoapRequest($soap, false, false, $returnJSON);
        }

        if (isset($tzObject)) {
            return $tzObject;
        } else {
            return false;
        }
    } // end GetLocalTzObject


    function GetTz($timezone) {
        /*
        <tz id="America/Chicago" stdoff="-360" dayname="CDT" dayoff="-300" stdname="CST">
          <standard min="0" wkday="1" sec="0" mon="11" hour="2" week="1"/>
          <daylight min="0" wkday="1" sec="0" mon="3" hour="2" week="2"/>
        </tz>
        */
//        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetTz(): ' . print_r( $timezone, true) , false);

        $tzObject["bias"]             = (isset($timezone['stdoff'])) ?  (-1 * $timezone['stdoff']) : 0;
        $tzObject["name"]             = (isset($timezone['id'])) ?  ($timezone['id']) : '';
        $tzObject["stdname"]          = (isset($timezone['stdname'])) ?  $timezone['stdname'] : '';
        $tzObject["dstendyear"]       = 0;
        $tzObject["dstendmonth"]      = (isset($timezone['standard'][0]['mon'])) ?  $timezone['standard'][0]['mon'] : 0;
        $tzObject["dstendday"]        = (isset($timezone['standard'][0]['wkday'])) ?  $timezone['standard'][0]['wkday'] -1 : 0;  
        $tzObject["dstendweek"]       = (isset($timezone['standard'][0]['week'])) ?  $timezone['standard'][0]['week'] : 0;
        $tzObject["dstendhour"]       = (isset($timezone['standard'][0]['hour'])) ?  $timezone['standard'][0]['hour'] : 0;
        $tzObject["dstendminute"]     = (isset($timezone['standard'][0]['min'])) ?  $timezone['standard'][0]['min'] : 0;
        $tzObject["dstendsecond"]     = (isset($timezone['standard'][0]['sec'])) ?  $timezone['standard'][0]['sec'] : 0;
        $tzObject["dstendmillis"]     = 0;
//        $tzObject["stdbias"]          = (isset($timezone['stdoff'])) ?  (-1 * $timezone['stdoff']) : 0;
//        STDBIAS should be 0 as Standard Time will not deviate from BIAS
        $tzObject["stdbias"]          = 0;
        $tzObject["dstname"]          = (isset($timezone['dayname'])) ?  $timezone['dayname'] : '';
        $tzObject["dststartyear"]     = 0;
        $tzObject["dststartmonth"]    = (isset($timezone['daylight'][0]['mon'])) ?  $timezone['daylight'][0]['mon'] : 0;
        $tzObject["dststartday"]      = (isset($timezone['daylight'][0]['wkday'])) ?  $timezone['daylight'][0]['wkday'] -1 : 0;
        $tzObject["dststartweek"]     = (isset($timezone['daylight'][0]['week'])) ?  $timezone['daylight'][0]['week'] : 0;
        $tzObject["dststarthour"]     = (isset($timezone['daylight'][0]['hour'])) ?  $timezone['daylight'][0]['hour'] : 0;
        $tzObject["dststartminute"]   = (isset($timezone['daylight'][0]['min'])) ?  $timezone['daylight'][0]['min'] : 0;
        $tzObject["dststartsecond"]   = (isset($timezone['daylight'][0]['sec'])) ?  $timezone['daylight'][0]['sec'] : 0;
        $tzObject["dststartmillis"]   = 0;
//        $tzObject["dstbias"]          = (isset($timezone['dayoff'])) ?  (-1 * $timezone['dayoff']) : 0;
        $tzObject["dstbias"]          = (isset($timezone['dayoff'])) ? (-1 * ($timezone['dayoff'] + $tzObject["bias"])) : 0;

//		$tzObject["stdbias"] = $tzObject["stdbias"] + (-1 * $tzObject["bias"]);
//		$tzObject["dstbias"] = $tzObject["dstbias"] + (-1 * $tzObject["bias"]);
        
        if ($tzObject["dstendweek"] == -1 ) $tzObject["dstendweek"] = 5;
        if ($tzObject["dststartweek"] == -1 ) $tzObject["dststartweek"] = 5;

        // Make the structure compatible with class.recurrence.php
        $tzObject["timezone"] = $tzObject["bias"];
//        $tzObject["timezonedst"] = $tzObject["dstbias"];
        $tzObject["timezonedst"] = $tzObject["timezone"] + $tzObject["dstbias"];
        
        return $tzObject;
    }


    function GetTzGMT() {
        $tzObject = array("name" => "UTC", "bias" => 0, "stdname" => "UTC", "stdbias" => 0, "dstbias" => 0, "dstendyear" =>0, "dstendmonth" =>0, "dstendday" =>0, "dstendweek" => 0, "dstendhour" => 0, "dstendminute" => 0, "dstendsecond" => 0, "dstendmillis" => 0,
                    "dstname" => "UTC", "dststartyear" =>0, "dststartmonth" =>0, "dststartday" =>0, "dststartweek" => 0, "dststarthour" => 0, "dststartminute" => 0, "dststartsecond" => 0, "dststartmillis" => 0);
        return $tzObject;
    }


    function GetTzSyncBlob($tzObject) {
        $packed = pack("la64vvvvvvvv" . "la64vvvvvvvv" . "l",
                $tzObject["bias"], $tzObject["name"],  $tzObject["dstendyear"], $tzObject["dstendmonth"], $tzObject["dstendday"], $tzObject["dstendweek"], $tzObject["dstendhour"], $tzObject["dstendminute"], $tzObject["dstendsecond"], $tzObject["dstendmillis"],
                $tzObject["stdbias"], $tzObject["name"], $tzObject["dststartyear"], $tzObject["dststartmonth"], $tzObject["dststartday"], $tzObject["dststartweek"], $tzObject["dststarthour"], $tzObject["dststartminute"], $tzObject["dststartsecond"], $tzObject["dststartmillis"],
                $tzObject["dstbias"]);
        return $packed;
    }


    // Unpack timezone info from Sync
    function GetTZFromSyncBlob($data) {
        $tzObject = unpack( "lbias/a64name/vdstendyear/vdstendmonth/vdstendday/vdstendweek/vdstendhour/vdstendminute/vdstendsecond/vdstendmillis/" .
                      "lstdbias/a64name/vdststartyear/vdststartmonth/vdststartday/vdststartweek/vdststarthour/vdststartminute/vdststartsecond/vdststartmillis/" .
                      "ldstbias", $data);

        // Make the structure compatible with class.recurrence.php
        $tzObject["timezone"] = $tzObject["bias"];
//        $tzObject["timezonedst"] = $tzObject["dstbias"];
        $tzObject["timezonedst"] = $tzObject["timezone"] + $tzObject["dstbias"];
        return $tzObject;
    }


    // Returns TRUE if it is the summer and therefore DST is in effect
    function IsDST($localtime, $tz) {
        if( !isset($tz) || !is_array($tz) ||
            !isset($tz["dstbias"]) || $tz["dstbias"] == 0 ||
            !isset($tz["dststartmonth"]) || $tz["dststartmonth"] == 0 ||
            !isset($tz["dstendmonth"]) || $tz["dstendmonth"] == 0)
            return false;

        $year = gmdate("Y", $localtime);
        $start = $this->GetTimestampOfWeek($year, $tz["dststartmonth"], $tz["dststartweek"], $tz["dststartday"], $tz["dststarthour"], $tz["dststartminute"], $tz["dststartsecond"]);
        $end = $this->GetTimestampOfWeek($year, $tz["dstendmonth"], $tz["dstendweek"], $tz["dstendday"], $tz["dstendhour"], $tz["dstendminute"], $tz["dstendsecond"]);

        if($start < $end) {
            // northern hemisphere (july = dst)
            if($localtime >= $start && $localtime < $end)
                $dst = true;
            else
                $dst = false;
        } else {
            // southern hemisphere (january = dst)
            if($localtime >= $end && $localtime < $start)
                $dst = false;
            else
                $dst = true;
        }
        return $dst;
    }


    // Returns the local timestamp for the $week'th $wday of $month in $year at $hour:$minute:$second
    function GetTimestampOfWeek($year, $month, $week, $wday, $hour, $minute, $second) {
        $date = gmmktime($hour, $minute, $second, $month, 1, $year);

        // Find first day in month which matches day of the week
        while(1) {
            $wdaynow = gmdate("w", $date);
            if($wdaynow == $wday)
                break;
            $date += 24 * 60 * 60;
        }

        // Forward $week weeks (may 'overflow' into the next month)
        $date = $date + $week * (24 * 60 * 60 * 7);

        // Reverse 'overflow'. Eg week '10' will always be the last week of the month in which the
        // specified weekday exists
        while(1) {
            $monthnow = gmdate("n", $date) - 1; // gmdate returns 1-12
            if($monthnow > $month)
                $date = $date - (24 * 7 * 60 * 60);
            else
                break;
        }

        return $date;
    }
    
    
    // Normalize the given timestamp to the start of the day
    function GetDayStartOfTimestamp($timestamp) {
        return $timestamp - ($timestamp % (60 * 60 * 24));
    }

};


 
class BackendSearchZimbra implements ISearchProvider {
//    private $connection;
 
    /**
     * Initializes the backend to perform the search
     * Connects to the LDAP server using the values from the configuration
     *
     *
     * @access public
     * @return
     * @throws StatusException
     */
    public function __construct($backend) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->BackendSearchZimbra(): ' .  'BackendSearchZimbra Instantiated' );
        $this->_backend = $backend;
    }

	
    /**
     * Indicates if a search type is supported by this SearchProvider
     * Currently only the type "GAL" (Global Address List) is implemented
     *
     * @param string        $searchtype
     *
     * @access public
     * @return boolean
     */
    public function SupportsType($searchtype) {
        return ((strtoupper($searchtype) == "GAL") || (strtoupper($searchtype) == 'MAILBOX') || (strtoupper($searchtype) == 'DOCUMENTLIBRARY'));
    }
 
    /** GetMailboxSearchResults
     *   Returns array of items which contain contact information
     */
    public function GetMailboxSearchResults($cpo) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'START GetMailboxSearchResults { cpo = ' . print_r( $cpo, true ) . ' }');

        $total = 0;
        $rangeMax = 999;  // Max allowed for Mailbox Search Results

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'Mailbox search ' );

        if ($cpo->GetRebuildResults()) {
            $rebuildresults = true;
        } else {
            $rebuildresults = false;
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'RebuildResults ['.$rebuildresults.']' );

        // if subfolders are required, do a recursive search
        if ($cpo->GetSearchDeepTraversal()) {
            $deeptraversal = true;
        } else {
            $deeptraversal = false;
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'DeepTraversal ['.$deeptraversal.']' );

        if ($cpo->GetSearchRange()) {
            $range = explode("-",$cpo->GetSearchRange());
            if ($range[1] > $rangeMax) {
                throw new StatusException("BackendSearchZimbra->GetMailboxSearchResults(): Range exceeds maximum allowed! ", SYNC_SEARCHSTATUS_STORE_ENDOFRETRANGE);
            }
            $limit = $range[1] - $range[0] + 1;
            $rangeMax = $range[1];
        } else {
            $range = "0-" . $RangeMax; 
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'Range ['.print_r($range, true).']' );

        // default to Email search
        $foldertype = "message";
        $searchFolderClass = $cpo->GetSearchClass();
        if (!empty($searchFolderClass)) {
            switch ($searchFolderClass) {
                case 'Email':
                    $foldertype = "message";
                    break;
                default:
                    // default to Email search
                    $foldertype = "message";
                    break;
            }
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'FolderType ['.$foldertype.']' );

        $searchfreetext = $cpo->GetSearchFreeText();
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'SearchFreeText ['.$searchfreetext.']' );
        if (empty($searchfreetext)) return false;


        $folderid = $cpo->GetSearchFolderId();
        if (!empty($folderid)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'FolderId ['.$folderid.']' );

            $index = $this->_backend->GetFolderIndex($folderid);
            $searchfolderid = $this->_backend->_folders[$index]->id;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'SearchFolderId ['.$searchfolderid.']' );
        }

        // Zimbra searches are only BEFORE and AFTER - not BEFORE/AFTER INCLUDING - SO ADJUST LIMITS BY ONE DAY
        if ($cpo->getSearchDateReceivedGreater()) {
            $startdate = $this->_backend->Date4ActiveSync($cpo->getSearchValueGreater(), "UTC")  - 86400;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'StartDate ['.$startdate.']' );
        }
        if ($cpo->getSearchDateReceivedLess()) {
            $enddate = $this->_backend->Date4ActiveSync($cpo->getSearchValueLess(), "UTC") + 86400;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'EndDate ['.$enddate.']' );
        }


        $soap = '<SearchRequest xmlns="urn:zimbraMail"  limit="'.$limit.'" offset="'.$range[0].'" types="'.$foldertype.'" >
                  <query>';
        if (isset($searchfolderid)) $soap .= 'inid:"' . $searchfolderid . '" ' ;
        if (isset($searchfolderid) && (isset($startdate) || isset($enddate))) $soap .= ' AND ' ;
        if (isset($startdate)) $soap .= ' after:"'.strftime("%m/%d/%Y",$startdate).'" ' ;
        if (isset($startdate) && isset($enddate)) $soap .= ' AND ' ;
        if (isset($enddate)) $soap .= ' before:"'.strftime("%m/%d/%Y",$enddate).'" ' ;
        $soap .= $searchfreetext.'</query>
                  <locale>en_US</locale>
                 </SearchRequest>';

//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'Search Soap ['.$soap.']' );

        $rows = array();

        $returnJSON = true;
        $response = $this->_backend->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' .  'MAILBOX:' . print_r( $array, true ) );

            unset($response);

            $items = $array['Body']['SearchResponse']; 

            $more = $items['more'];
            $total = 0;
            if (isset($items['m'])) {
                $total = count($items['m']);
            }

            for ($i=0; $i<$total; $i++ ) {
                $row = array();
                $email = $items['m'][$i];
                $row["class"] = "Email";
                $deviceFolder = $this->_backend->_folders[$this->_backend->GetFolderIndexZimbraID($email['l'])]->devid;
                $row["longid"] =  $deviceFolder . ":" . $email['id'];
                $row["folderid"] = $deviceFolder;
//ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' . print_r($row,true));
	
                $rows[] = $row;
                unset($row);
            }
            
            unset($array);
        }

        $rows['range'] = "0-" . strval($total - 1 );
        $rows['searchtotal'] = $total;

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetMailboxSearchResults(): ' . 'END GetMailboxSearchResults Mailbox { ' . $total . ' Matches Found }');

        return $rows;

    } // end GetMailboxSearchResults


    /** GetDocumentLibrarySearchResults
     *   Returns array of items which contain contact information
     */
    public function GetDocumentLibrarySearchResults($cpo) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'START GetDocumentLibrarySearchResults { cpo = ' . print_r( $cpo, true ) . ' }');

        $total = 0;
        $rangeMax = 999;  // Max allowed for Document Library Search Results

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'Document Library search ' );

        if ($cpo->GetRebuildResults()) {
            $rebuildresults = true;
        } else {
            $rebuildresults = false;
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'RebuildResults ['.$rebuildresults.']' );

        // if subfolders are required, do a recursive search
        if ($cpo->GetSearchDeepTraversal()) {
            $deeptraversal = true;
        } else {
            $deeptraversal = false;
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'DeepTraversal ['.$deeptraversal.']' );


        if ($cpo->GetSearchRange()) {
            $range = explode("-",$cpo->GetSearchRange());
            if ($range[1] > $rangeMax) {
                throw new StatusException("BackendSearchZimbra->GetDocumentLibrarySearchResults(): Range exceeds maximum allowed! ", SYNC_SEARCHSTATUS_STORE_ENDOFRETRANGE);
            }
            $limit = $range[1] - $range[0] + 1;
            $rangeMax = $range[1];
        } else {
            $range = "0-" . $RangeMax; 
        }
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'Range ['.print_r($range, true).']' );

        // default to Document search
        $foldertype = "document";
        $searchFolderClass = $cpo->GetSearchClass();
        if (!empty($searchFolderClass)) {
            switch ($searchFolderClass) {
                case 'Document':
                    $foldertype = "document";
                    break;
                default:
                    // default to Email search
                    $foldertype = "document";
                    break;
            }
        }

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'FolderType ['.$foldertype.']' );

        $searchequalto = $cpo->GetSearchValueEqualTo();
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'SearchEqualTo ['.$searchequalto.']' );
        if (empty($searchequalto)) return false;

        $folderid = $cpo->GetSearchFolderId();
        if (!empty($folderid)) {
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'FolderId ['.$folderid.']' );

            $index = $this->_backend->GetFolderIndex($folderid);
            $searchfolderid = $this->_backend->_folders[$index]->id;
            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'SearchFolderId ['.$searchfolderid.']' );
        }

        $rows = array();

        $responseRange = "0-0";

        $folder = explode( "\\", $searchequalto);
        $folderParts = count($folder);

//        ZLog::Write(LOGLEVEL_DEBUG, 'EXPLODED SEARCH:' .print_r( $folder, true ) );

        if ((strtoupper($folder[0]) != "ZIMBRA") && (strtoupper($folder[0]) != "Z")) {
            throw new StatusException("BackendSearchZimbra->GetDocumentLibrarySearchResults(): Search base must be ZIMBRA\\ or Z\\", SYNC_SEARCHSTATUS_STORE_BADLINK);
        }

        if ($folderParts == 1) {
            // USER_ROOT search
            //Need dummy Root Folder Metadata returned ?
            $row = new SyncDocumentLibraryDocument();
            $row->longid = '1'; // USER_ROOT
            $row->linkid = $searchequalto; // Original Search
            $row->displayname = $searchequalto;
            $row->isfolder = true;
            $row->creationdate = 0;
            $row->lastmodifieddate = 0;
            $row->ishidden = 0;
            $row->contentlength = 0;  // Root folder metadata does not count towards contents as far as I can tell
            $row->contenttype = '';
	
            $rows[] = $row;
            unset($row);

        } else {
            // Check if we got a valid path to a folder/a file/or an invalid path
            $searchfile = "";
            $searchFolder = array();
            $searchFolder = $folder;
            $searchFolder[0] = ''; // to remove zimbra/ZIMBRA/Zimbra... but leave the "/" at the start

            // First check if entire remaining path is to a folder - in which case we will list it's contents.
            $searchPath = implode( "/", $searchFolder );
            if (isset( $this->_backend->_documentLibrariesPathToIdIndex[$searchPath])) {
                $searchFolderIndex = $this->_backend->_documentLibrariesPathToIdIndex[$searchPath];
                $searchFolderId = $this->_backend->_documentLibraries[$searchFolderIndex]['longid'];
                $ownerid = $this->_backend->_documentLibraries[$searchFolderIndex]['ownerid'];
                ZLog::Write(LOGLEVEL_DEBUG, 'Check for Folder:' .$searchPath. ' - Found folder at Index ['.$searchFolderIndex.'] owned by ['.$ownerid.']' );
                ZLog::Write(LOGLEVEL_DEBUG, 'Need to list folders/files contained within !');
            } else {

                // Next check if all but the last part point to a folder - in which case we will look for just one file - contained in the final part.
                $searchfile = $searchFolder[$folderParts-1];
                unset($searchFolder[$folderParts-1]);  // to remove the last part (possible filename) 
                $searchPath = implode( "/", $searchFolder );

                if (isset( $this->_backend->searchPath[$searchPath])) {
                    $searchFolderIndex = $this->_backend->_documentLibrariesPathToIdIndex[$searchPath];
                    $searchFolderId = $this->_backend->_documentLibraries[$searchFolderIndex]['longid'];
                    $ownerid = $this->_backend->_documentLibraries[$searchFolderIndex]['ownerid'];
                    ZLog::Write(LOGLEVEL_DEBUG, 'Check for Folder:' .$searchPath. ' - Found folder at Index ['.$searchFolderIndex.'] owned by ['.$ownerid.']' );
                    ZLog::Write(LOGLEVEL_DEBUG, 'Need to look for file ['.$searchfile.'] contained within !');
                } else {
                    throw new StatusException("BackendSearchZimbra->GetDocumentLibrarySearchResults(): Search contains a bad folder name! ", SYNC_SEARCHSTATUS_STORE_NOTFOUND);
                }
            }

            if ($searchfile != "") {            // we're looking for  one matching file

			
                $soap = '<SearchRequest xmlns="urn:zimbraMail"  limit="'.$limit.'" offset="'.$range[0].'" types="document" sortBy="subjAsc" >
                          <query> inid:"'.$searchFolderId.'" "'.$searchfile.'" ';
                $soap .= '</query>
                         </SearchRequest>';

//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'Search Soap ['.$soap.']' );

                $returnJSON = true;
                $response = $this->_backend->SoapRequest($soap, false, false, $returnJSON);
			
                if($response) {
                    $array = json_decode($response, true);
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'DOCUMENTS:' . print_r( $array, true ), false );

                    unset($response);

                    $items = $array['Body']['SearchResponse']; 

                    if (!isset($items['doc']) or(count($items['doc']) != 1)) {
                        throw new StatusException("BackendSearchZimbra->GetDocumentLibrarySearchResults(): Search contains a bad file name! ", SYNC_SEARCHSTATUS_STORE_NOTFOUND);
                    }

                    $row = new SyncDocumentLibraryDocument();
                    $document = $items['doc'][0];
                    $row->longid = $document['id'];
                    $row->linkid = $folder[0] . str_replace( "/", "\\", $searchPath) . "\\" . $searchfile; // Original Search root + Path of found folder
                    $row->displayname = $document['name'];
                    $row->isfolder = 0;
                    $row->creationdate = substr($document['cd'], 0, 10);
                    $row->lastmodifieddate = substr($document['md'], 0, 10);
                    $row->ishidden = 0;
                    $row->contentlength = $document['s'];
                    $row->contenttype = $document['ct'];
//                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . print_r($row,true));

                    // Need to add it twice - one for the metadata of the original request - and one for the response
                    $rows[] = $row;
                    $rows[] = $row;
                    unset($row);

                    unset($array);
                    $responseRange = "0-0";
                    $rows['searchtotal'] = 1;
                    $rows['range'] = $responseRange;

                    ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . 'END GetDocumentLibrarySearchResults { Exact File Match Found }');

                    return $rows;

                } else {
                    throw new StatusException("BackendSearchZimbra->GetDocumentLibrarySearchResults(): Search SOAP command failed! ", SYNC_SEARCHSTATUS_STORE_SERVERERROR);
                }
            } else { 

                // We have a folder - store the metadata as the initial return then get the contents
                $more = "";
                $total = 0;
                $responseRange = "0-" . strval($total);

                //Need dummy Root Folder Metadata returned ?
                $row = new SyncDocumentLibraryDocument();
                $row->longid = $this->_backend->_documentLibraries[$searchFolderIndex]['longid']; // Id returned from index
                $row->linkid = $folder[0] . str_replace( "/", "\\", $searchPath); // Original Search root + Path of found folder
                $row->displayname = $searchFolder[count($searchFolder)-1];
                $row->isfolder = true;
                $row->creationdate = 0;
                $row->lastmodifieddate = 0;
                $row->ishidden = 0;
                $row->contentlength = 0;
                $row->contenttype = '';
                $row->zimbralinkid = $this->_backend->_documentLibraries[$searchFolderIndex]['zimbralinkid']; // Id returned from index
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . print_r($row,true));

                $rows[] = $row;
                unset($row);
            }
        }

        // First search the DocumentLibraries for and children of the Search Folder
        foreach ($this->_backend->_documentLibraries as $documentLibrary) {
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): Parentid ' . $documentLibrary['parentid'] . ' - RowLongId ' . $rows[0]->longid );

            if (($documentLibrary['parentid'] == $rows[0]->longid) || ($documentLibrary['parentid'] == $rows[0]->zimbralinkid)) {
                $row = new SyncDocumentLibraryDocument();
                $row->longid = $documentLibrary['longid'];
                $row->linkid = $folder[0] . str_replace( "/", "\\", $documentLibrary['linkid']);
                $row->displayname = $documentLibrary['displayname'];
                $row->isfolder = $documentLibrary['isfolder'];
                $row->creationdate = $documentLibrary['creationdate'];
                $row->lastmodifieddate = $documentLibrary['lastmodifieddate'];
                $row->ishidden = $documentLibrary['ishidden'];
                $row->contentlength = $documentLibrary['contentlength'];
                $row->contenttype = $documentLibrary['contenttype'];
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . print_r($row,true));
	
                $rows[] = $row;
                unset($row);
            }
        }

        if ($folderParts == 1) {
            // USER_ROOT search
            // We are done. The top level contains no documents - only folders. No need to search
            $responseRange = "0-" . strval(count($rows)-2);
            $rows['searchtotal'] = count($rows)-1;
            $rows[0]->contentlength = $rows['searchtotal'];
            $rows['range'] = $responseRange;

            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . 'END GetDocumentLibrarySearchResults { Root Folder Found }');

            return $rows;

        }

        // If we have not returned yet, we have a list of child folders, and need to add a list of files from the search folder 
		
        $soap = '<SearchRequest xmlns="urn:zimbraMail"  limit="'.$limit.'" offset="'.$range[0].'" types="document" sortBy="subjAsc" >
                  <query> inid:"'.$rows[0]->longid.'" ';
        $soap .= '</query>
                 </SearchRequest>';
//                  <locale>en_US</locale>

//        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'Search Soap ['.$soap.']' );

        $returnJSON = true;
        $response = $this->_backend->SoapRequest($soap, false, false, $returnJSON);
			
        if($response) {
            $array = json_decode($response, true);
//            ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' .  'DOCUMENTS:' . print_r( $array, true ), false );

            unset($response);

            $items = $array['Body']['SearchResponse']; 

            $more = $items['more'];
            $total = 0;
            if (isset($items['doc'])) {
                $total = count($items['doc']);
            }

            for ($i=0; $i<$total; $i++ ) {
                $row = new SyncDocumentLibraryDocument();
                $document = $items['doc'][$i];
                $row->longid = $document['id'];
                $row->linkid = $folder[0] . str_replace( "/", "\\", $searchPath). "\\" . $document['name']; // Original Search root + Path of found folder
                $row->displayname = $document['name'];
                $row->isfolder = 0;
                $row->creationdate = substr($document['cd'], 0, 10);
                $row->lastmodifieddate = substr($document['md'], 0, 10);
                $row->ishidden = 0;
                $row->contentlength = $document['s'];
                if (strpos( $document['ct'], 'x-zimbra-doc') !== false) {
                    $row->contenttype = 'text/html';
                } else {
                    $row->contenttype = $document['ct'];
                }
//                ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . print_r($row,true));
	
                $rows[] = $row;
                unset($row);
            }
            
            unset($array);

        } else {
            throw new StatusException("BackendSearchZimbra->GetDocumentLibrarySearchResults(): Search SOAP command failed! ", SYNC_SEARCHSTATUS_STORE_SERVERERROR);
        }

        $responseRange = "0-" . strval(count($rows)-2);
        $rows['searchtotal'] = count($rows)-1;
        $rows[0]->contentlength = $rows['searchtotal'];
        $rows['range'] = $responseRange;

        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetDocumentLibrarySearchResults(): ' . 'END GetDocumentLibrarySearchResults Mailbox { ' . $total . ' Folder/File Matches Found }');

        return $rows;

    } // end GetDocumentLibrarySearchResults


    /** GetGALSearchResults
     *   Returns array of items which contain contact information
	 *   Z-Push 2.4beta1 introduced a third parameter $searchpicture to the function definition.
     */
    public function GetGALSearchResults($searchquery, $searchrange, $searchpicture = false) {
        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetGALSearchResults(): ' .  'START GetGALSearchResults { searchquery = ' . $searchquery . '; searchrange = ' . $searchrange . '; searchpicture = ' . $searchpicture . ' }');

        $total = 0;
        $rangeMax = 99;  // Max allowed for GAL Search Results

        // If (RETURN) key pressed on Samsung client while typing, it adds "<" before and ">, " after the partial name.
        // These should be stripped before attempting a search
        if ((strlen($searchquery) > 0) && ($searchquery[0] == "<")) {
            $searchquery = substr( trim($searchquery), 1 );
        }
        if ((strlen($searchquery) > 2) && (substr($searchquery, -2) == ">,")) {
            $searchquery = substr( $searchquery, 0, strlen($searchquery)-2 );
        }

        $rows = array();
        $rows['searchtotal'] = 0;

        $find = $searchquery;
        if (isset($searchrange)) {
            $range = explode("-",$searchrange);
            if ($range[1] > $rangeMax) {
                throw new StatusException("BackendSearchZimbra->GetGALSearchResults(): Range exceeds maximum allowed! ", SYNC_SEARCHSTATUS_STORE_ENDOFRETRANGE);
            }
            $limit = $range[1] - $range[0] + 1;
            $rangeMax = $range[1];
        } else {
            $range = "0-" . $RangeMax; 
        }

        $rangelimiter = ' limit="'.$limit.'" offset="'.$range[0].'" '; 

        $type = "all";  // Can be set to "account" to not return Locations/Resources
        $soap = '<SearchGalRequest xmlns="urn:zimbraAccount"  type="'.$type.'" '.$rangelimiter.' >
                  <name>' . $find . '</name>
                 </SearchGalRequest>';

        $returnJSON = true;
		$response = $this->_backend->SoapRequest($soap, false, false, $returnJSON);
        if($response) {
            $array = json_decode($response, true);

            unset($response);

            if (isset($array['Body']['SearchGalResponse']['cn'])) {
                $items = $array['Body']['SearchGalResponse']['cn'];
                $total = sizeof($items);
            } else {
                $total = 0;
            }
            unset($array);

            for ($i=0;$i<$total;$i++) {
                $username = ""; //$items[$i]['fileAsStr'];
                $firstname = "";
                $lastname = "";
                $fullname = "";
                $businessphone = "";
                $mobilephone = "";
                $homephone = "";
                $emailaddress = "";
                $company = "";
                $title = "";
                $office = "";
                if (isset($items[$i]['_attrs']['firstName'])) 
                            $firstname = $items[$i]['_attrs']['firstName'];
                if (isset($items[$i]['_attrs']['lastName']))
                            $lastname = $items[$i]['_attrs']['lastName'];
                if (isset($items[$i]['_attrs']['fullName']))
                            $fullname = $items[$i]['_attrs']['fullName'];
                if (isset($items[$i]['_attrs']['email']))
                            $emailaddress = $items[$i]['_attrs']['email'];
                if (isset($items[$i]['_attrs']['company']))
                            $company = $items[$i]['_attrs']['company'];
                if (isset($items[$i]['_attrs']['title']))
                            $title = $items[$i]['_attrs']['title'];
                if (isset($items[$i]['_attrs']['office']))
                            $office = $items[$i]['_attrs']['office'];
                if (isset($items[$i]['_attrs']['homePhone']))
                            $homephone = $items[$i]['_attrs']['homePhone'];
                if (isset($items[$i]['_attrs']['mobilePhone']))
                            $mobilephone = $items[$i]['_attrs']['mobilePhone'];
                if (isset($items[$i]['_attrs']['workPhone']))
                            $businessphone = $items[$i]['_attrs']['workPhone'];

                $rows[] = array(SYNC_GAL_DISPLAYNAME=>$fullname,
								SYNC_GAL_PHONE=>$businessphone,
								SYNC_GAL_MOBILEPHONE=>$mobilephone,
								SYNC_GAL_HOMEPHONE=>$homephone,
								SYNC_GAL_ALIAS=>$username,
								SYNC_GAL_EMAILADDRESS=>$emailaddress,
								SYNC_GAL_COMPANY=>$company,
								SYNC_GAL_TITLE=>$title,
								SYNC_GAL_OFFICE=>$office,
								SYNC_GAL_FIRSTNAME=>$firstname,
								SYNC_GAL_LASTNAME=>$lastname );
            }
            $rows['range'] = "0-" . strval($total - 1 );
            $rows['searchtotal'] = $total;

            unset($items);
        }


        ZLog::Write(LOGLEVEL_DEBUG, 'Zimbra->GetGALSearchResults(): ' . 'END GetGALSearchResults { ' . $total . ' Matches Found }');

        return $rows;
	
    } // end GetGALSearchResults

    /**
    * Terminates a search for a given PID
    *
    * @param int $pid
    *
    * @return boolean
    */
    public function TerminateSearch($pid) {

    /**
      * Disconnects from Search Provider
      *
      * @access public
      * @return boolean
      */
        return false;
    }

    public function Disconnect() {
//         if ($this->connection)
//             close($this->connection);
 
        return true;
    }
 
}
