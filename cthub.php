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

    // Set $server_path to point to root-dir of the web site relative to the cthub script
    // eg: if cthub script is in /php of web-root-dir use: $server_path = "../";
    // eg: if cthub script is in folder/cthub/ and site is in users/d1/ use: $server_path = "../../users/d1/";
    $server_path = '../';

    // Maximum Server Upload File Size in MB
    $max_upload_size = 32;

    // TODO:
    // Split and concat files to upload large files
    // $splitLargeFiles = true;

    // Provide template information wich can be installed on this server
    $install_xml = 'install.xml';

    // File wich stores the admin password
    $pw_file = 'cthubp.php';

    // Log hub activity
    $log_activity = true;

    // name of the log file in the hub directory
    $logfile = 'log.txt';

    //8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918 // Default 'admin' password (SHA256)
    require( $pw_file );
    
    // file compare algo
    $algo = 'md5';

    if( isset( $_POST['algo']) ) {
        $algo = $_POST['algo'];
    }

    define ('SERVER_PATH_LENGTH', strlen($server_path) );

    // CTOptions.sendFileList = true: (recommended)
    function listFilelist ( $spath, $filelist )
    {
         $all = explode( ",", $filelist );
         $tmp = '';
         $L = count($all);

         for($i=0; $i<$L; $i++) {
            $file = $all[$i];
            if( $file && $file != '' && $file != '.' && $file != '..' ) {
                if( file_exists($spath.'/'.$file) ) {
                    if( $algo == 'sha256' ) {
                         $tmp .= '<f url="' . substr($spath, SERVER_PATH_LENGTH) . $file.'" c="' . hash_file("sha256", $spath.'/'.$file) . '"/>
';
                    }else if( $algo == 'sha224' ) {
                         $tmp .= '<f url="' . substr($spath, SERVER_PATH_LENGTH) . $file.'" c="' . hash_file("sha224", $spath.'/'.$file) . '"/>
';
                    }else{
                         $tmp .= '<f url="' . substr($spath, SERVER_PATH_LENGTH) . $file.'" c="' . md5_file($spath.'/'.$file) . '"/>
';
                    }
                }
            }
         }
         return $tmp;
    }
    // CTOptions.sendFileList = false: server searches all files in server_path
    function listDirectory ($spath) {
        $all = opendir($spath);
        $tmp = '';
        while( $file = readdir($all) ) {
            if( $file != '..' && $file != '.') {
                if( is_dir($spath.$file) ) {
                    $tmp .= listDirectory( $spath.$file.'/' );
                }else{
                    if( $algo == 'sha256' ) {
                         $tmp .= '    <f url="' . substr($spath, SERVER_PATH_LENGTH) . $file.'" c="' . hash_file("sha256", $spath.'/'.$file) . '"/>
';
                    }else if( $algo == 'sha224' ) {
                         $tmp .= '    <f url="' . substr($spath, SERVER_PATH_LENGTH) . $file.'" c="' . hash_file("sha224", $spath.'/'.$file) . '"/>
';
                    }else{
                         $tmp .= '    <f url="' . substr($spath, SERVER_PATH_LENGTH) . $file.'" c="' . md5_file($spath.'/'.$file) . '"/>
';
                    }
                }
            }
        }
        return $tmp;
    }
    if( isset($_POST['pwd']) && $_POST['pwd'] == $pwd )
    {
        // change password
        if( isset($_POST['newpwd']) ) {
            // change pwd...
            $text = '<?php $pwd="'.$_POST['newpwd'].'"; ?>';
            file_put_contents($pw_file, $text, LOCK_EX);
            echo 'ok';
            exit();

        }

        // return template install information
        if( isset($_POST['install']) )
        {
            $text = file_get_contents($install_xml);
            echo $text;
        }

        // return file hash infos
        if( isset($_POST['dirinfo']) )
        {
            $text = '<?xml version="1.0" encoding="utf-8" ?>
<fileinfo path="'.$server_path.'" algo="'.$algo.'" maxsize="'.$max_upload_size.'">
'; 
            if( isset($_POST['filelist']) ) {
                // return only the files requested
                $text .= listFilelist($server_path, $_POST['filelist']);
            }else{
                // search and return all files in web directory and sub directories
                $text .= listDirectory($server_path);
            }
            $text .= '</fileinfo>';

            if( $log_activity ) {
                file_put_contents($logfile, 'Send Dir-Info '.$_SERVER['REMOTE_ADDR'] . ' > ' . $_SERVER['HTTP_REFERER'] . "\n", FILE_APPEND | LOCK_EX);
            }
            echo $text;
            exit();
        }

        // receive a file upload
        if( isset($_FILES['fileToUpload']) )
        {
            if( $log_activity ) {
                $text = "Received Upload " . $_FILES["fileToUpload"]["name"] . " path: " . $_POST['path'] . ' from ' . $_SERVER['REMOTE_ADDR'] . ' > ' . $_SERVER['HTTP_REFERER'] ;
            }
            // Receive a file upload. Path relative to this file (cthub.php)
            $target_dir = $server_path;
            if(isset($_POST['path'])) $target_dir .= $_POST['path'];

            // Test if $target_dir exists or create if required ..

            if( ! is_dir($target_dir) ) {

                $pt = explode("/", $_POST['path']);
                if( count($pt) > 1 ) {
                    $pth = '';
                    if( $log_activity ) $text .= " SPLIT-PATH\n";
                    for($i=0; $i<count($pt); $i++) {
                        $pth .= $pt[$i] . "/";
                        if( $log_activity ) $text .= 'Create Directory:' .$pth . "\n";
                        if( !is_dir( $server_path .$pth ) ) {
                            mkdir( $server_path . $pth, 0755 );
                        }
                    }
                }else{
                    if( $log_activity ) $text .= " ROOT-PATH\n";
                    mkdir( $target_dir, 0755 );
                }
            }else{
                if( $log_activity ) $text .= " EXISTS\n";
            }

            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file );
            chmod( $target_file, 0755 );

            if( $log_activity ) {
                file_put_contents($logfile, $text, FILE_APPEND | LOCK_EX);
            }
        }

    }
    else
    {
        echo 'no-pass';
    }
    //if( $debugOutput ) {
    //    echo 'Contemple Hub<br/>=============<br/><br/>Usage: Configure Script:<br/>Set server_path to the web-site root directory<br/>Upload cthub.php to your server<br/>Set path to cthub.php in Contemple config.<br/><br/>Post vars:<br/><b>dirinfo:Boolean</b> If set, hub returns the SHA256 for every file<br/><b>fileupload:String<b>fileToUpload:String</b> The name of file upload file ctrl<br/><b>path:String</b> The path on the server to store the file';
    //}
?>