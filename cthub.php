<?php

/*
*   CONTEMPLE PHP Hub Script
*
*   Overview:
*   -
*   - Get Template infos to install on the web-server (install.xml)
*   - Get hash-code info of all website files (dirinfo:bool)
*   - Receive a single file upload (upload:bool,fileToUpload:file,path:string)
*
*   Description:
*   Used by Contemple Website CMS Application
*
*   Usage:
*   Copy the php file 'cthub.php' to the server, optional rename the file and change app config
*   Set server path, max upload file size and template install information below
*/

// enable/disable file uploads on this server
$disbale_upload = false;

// enable/disable changing the server password
$disable_password_change = false;

// Absolute Website Root Path
$host_root = 'https://www.contemple.app/demo/';

// Set $server_path to point to root-dir of the web site relative to the cthub script
// eg: if cthub script is in /php of web-root-dir use: $server_path = "../";
// eg: if cthub script is in folder/cthub/ and site is in users/d1/ use: $server_path = "../../users/d1/";
$server_path = '../';

// Maximum Server Upload File Size in MB
$max_upload_size = 32;

// Install.xml provides template information wich can be installed or updated by this server
$install_xml = 'install.xml';
// For security the file should be moved into a directory above the website root directory:
// $install_xml = "../../../protected-hub/instal.xml";


// Database Content Backups are stored in the sync_dir with every Publish
$sync_dir = 'sync/';
// For security the file should be moved into a directory above the website root directory:
// $sync_dir = "../../../protected-hub/sync/";


// Name of database backups: "data-" -> stores -> data-1.xml, data-2.xml .. files in the sync_dir
$sync_name = 'data-';

// version file contains only one integer with the actual sync version or 0 if not available
$version_file = 'version.txt';
// For security the file should be moved into a directory above the website root directory:
// $version_file = "../../../protected-hub/version.txt";

// File wich stores the admin password
$pw_file = 'pwd.php';
// For security the file should be moved into a directory above the website root directory:
// $pw_file = "../../../protected-hub/pwd.php";

// Log hub activity
$log_activity = true;

// name of the log file in the hub directory
$logfile = 'log.txt';
// For security the file should be moved into a directory above the website root directory:
// $logfile = "../../../protected-hub/log.txt";

// default file compare algo, can be overidden by the app
$algo = 'md5';

require( "hub.php" );


?>