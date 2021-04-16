<?php
/***********************************************
* File      :   config.php
* Project   :   Z-Push - tools - GAB sync
* Descr     :   Configuration file.
*
* Created   :   28.01.2016
*
* Copyright 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
* ************************************************/

// The field to be hashed that is unique and never changes
// in the entire lifetime of the GAB entry.
define('HASHFIELD', 'account');
define('AMOUNT_OF_CHUNKS', 10);

// SyncWorker implementation to be used
define('SYNCWORKER', 'Kopano');

// Unique id to find a contact from the GAB (value to be supplied by -u on the command line)
// Zarafa supports: 'account' and 'smtpAddress' (email)
define('UNIQUEID', 'account');

// Server connection settings
// Depending on your setup, it might be advisable to change the lines below to one defined with your
// default socket location.
// Normally "default:" points to the default setting ("file:///var/run/kopano/server.sock")
// Examples: define("SERVER", "default:");
//           define("SERVER", "http://localhost:236/kopano");
//           define("SERVER", "https://localhost:237/kopano");
//           define("SERVER", "file:///var/run/kopano/server.sock");
// If you are using ZCP >= 7.2.0, set it to the zarafa location, e.g.
//           define("SERVER", "http://localhost:236/zarafa");
//           define("SERVER", "https://localhost:237/zarafa");
//           define("SERVER", "file:///var/run/zarafad/server.sock");
// For ZCP versions prior to 7.2.0 the socket location is different (http(s) sockets are the same):
//           define("SERVER", "file:///var/run/zarafa");

define('SERVER', 'default:');

define('USERNAME', 'SYSTEM');
define('PASSWORD', '');
define('CERTIFICATE', null);
define('CERTIFICATE_PASSWORD', null);

// Store where the hidden folder is located.
// For the public folder, use SYSTEM
// to use another store, use the same as USERNAME
// or another store where USERNAME has full access to.
define('HIDDEN_FOLDERSTORE', 'SYSTEM');

/// Do not change (unless you know exactly what you do)
define('HIDDEN_FOLDERNAME', 'Z-Push-KOE-GAB');

// Types of the objects to sync to GAB.
define('GAB_SYNC_USER', 1);
define('GAB_SYNC_CONTACT', 2);
define('GAB_SYNC_GROUP', 4);
define('GAB_SYNC_ROOM', 8);
define('GAB_SYNC_EQUIPMENT', 16);

define('GAB_SYNC_ALL', GAB_SYNC_USER | GAB_SYNC_CONTACT | GAB_SYNC_GROUP | GAB_SYNC_ROOM | GAB_SYNC_EQUIPMENT);

// Set which items from GAB should be synced.
// Default value is GAB_SYNC_ALL which syncs all items.
// In order to sync only some specific types combine them with "|", e.g.
// to sync only users and groups use:
// define('GAB_SYNC_TYPES', GAB_SYNC_USER | GAB_SYNC_CONTACT);
// In order to exclude specific types combine "& ~TYPE", e.g.
// to sync all types except rooms and equipments use:
// define('GAB_SYNC_TYPES', GAB_SYNC_ALL & ~GAB_SYNC_ROOM & ~GAB_SYNC_EQUIPMENT);
define('GAB_SYNC_TYPES', GAB_SYNC_ALL);

// Whether to hide the group Everyone in the synced GAB.
define('GAB_SYNC_HIDE_EVERYONE', false);