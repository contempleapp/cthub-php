<?php
    
    $hub_version = "1.2";

    if( !isset($disable_upload)) $disable_upload = false;
    if( !isset($disable_password_change)) $disable_password_change = false;
    if( !isset($server_path)) $server_path = "../";
    if( !isset($install_xml)) $install_xml = 'install.xml';
    if( !isset($sync_dir)) $sync_dir = "sync/";
    if( !isset($sync_name)) $sync_name = "data-";
    if( !isset($version_file)) $version_file = "version.txt";
    if( !isset($sync_name)) $sync_name = "data-";
    if( !isset($pw_file)) $pw_file = "pwd.php";
    if( !isset($log_activity)) $log_activity = true;
    if( !isset($logfile)) $logfile = 'log.txt';
    if( !isset($algo)) $algo = 'md5';
    
    require( $pw_file );
    
    if( isset($_POST['algo']) ) {
        $algo = $_POST['algo'];
    }
    
    // TODO:
    // Split and concat files to upload large files
    // $splitLargeFiles = true;

    define ('SERVER_PATH_LENGTH', strlen($server_path) );

    function listFilelist ( $spath, $filelist )
    {
         $all = explode( ",", $filelist );
         $tmp = '';
         $L = count($all);

         for($i=0; $i<$L; $i++)
         {
            $file = $all[$i];
           
            if( $file && $file != '' && $file != '.' && $file != '..' )
            {
                if( strpos($file, "../") === false ) {
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
         }
         return $tmp;
    }

    // Hub Actions
   if( isset($_POST['pwd']) && $_POST['pwd'] == $pwd )
   {
        $action = '';
        if( isset($_POST['action']) )
        {
            /* 
            * Deprecated POST vars:
            * install, lookup, update, dirinfo, src
            * hubversion, content, latest, versions
            * 
            * New: 
            * $_POST['action'] = 'install';
            * $_POST['action'] = 'dirinfo';
            * etc.
            */
            $action = $_POST['action'];
        }

        // change password
        if( isset($_POST['newpwd']) ) {
            if( !$disable_password_change ) {
                // change pwd...
                $text = '<?php $pwd="'.$_POST['newpwd'].'"; ?>';
                file_put_contents($pw_file, $text, LOCK_EX);
                echo 'ok';
            }else{
                echo 'disabled';
            }
            exit;
        }


        // receive a file upload
        if( isset($_FILES['fileToUpload']) && !$disable_upload )
        {
            if( strpos($_POST['path'], "../") === false && substr($_POST['path'],0,1) != '/' ) {

                if( $log_activity ) {
                    $text = "Received Upload " . $_FILES["fileToUpload"]["name"] . " path: " . $_POST['path'] . ' from ' . $_SERVER['REMOTE_ADDR'] . ' > ' . $_SERVER['HTTP_REFERER'] ;
                }
                
                if( $_POST['path'] == 'cthub/sync/' && ($_FILES["fileToUpload"]["name"] == "sync.xml" || $_FILES["fileToUpload"]["name"] == "stat.xml") )
                {
                    $syv = 0;
                    
                    if( !is_dir($sync_dir) ) {
                        mkdir( $sync_dir, 0755 );
                    }
                    
                    if( $log_activity ) $text .= ' Sync-' . $syv . ' ';
                    
                    if( file_exists($sync_dir.$version_file) )
                    {
                        $systr = file_get_contents($sync_dir . $version_file);
                        if( $systr ) {
                            $syv = intval( $systr );
                        }
                    }
                   
                    if( $_FILES["fileToUpload"]["name"] == "sync.xml" )
                    {
                        // increment sync_version:
                        $syv++;
                        
                        // write new sync verion to txt file
                        file_put_contents($sync_dir.$version_file, $syv, LOCK_EX);
                    
                        $target_file = $sync_dir . $sync_name . $syv . '.xml' ;
                    }
                    else
                    {
                        $target_file = $sync_dir . $sync_name . $syv . '-patch.xml' ;
                    }
                    if( $log_activity ) $text .= ' Write content : ' . $target_dir;
                    
                    move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file );
                    chmod( $target_file, 0755 );
                    
                }
                else if( $_POST['path'] == 'cthub/sync/' )
                {
                    
                    $target_file = $sync_dir . basename($_FILES["fileToUpload"]["name"]);
                    move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file );
                    chmod( $target_file, 0755 );
                    
                }
                else
                {
                    // Receive a file upload
                    $target_dir = $server_path;

                    if(isset($_POST['path'])) $target_dir .= $_POST['path'];

                    // Test if $target_dir exists or create if required ..

                    if( ! is_dir($target_dir) ) {

                        $pt = explode("/", $_POST['path']);
                        if( count($pt) > 1 ) {
                            $pth = '';
                            if( $log_activity ) $text .= " SPLIT-PATH\n";
                            for($i=0; $i<count($pt); $i++)
                            {
                                if( $pt[$i] != ".." )
                                {
                                    $pth .= $pt[$i] . "/";
                                    if( $log_activity ) $text .= 'Create Directory:' .$pth . "\n";
                                    if( !is_dir( $server_path .$pth ) ) {
                                        mkdir( $server_path . $pth, 0755 );
                                    }
                                }
                            }
                        }else{
                            if( $log_activity ) $text .= " ROOT-PATH\n";
                            mkdir( $target_dir, 0755 );
                        }
                    }
                    else
                    {
                        if( $log_activity ) $text .= " EXISTS\n";
                    }

                    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                    move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file );
                    chmod( $target_file, 0755 );
                }
                
                if( $log_activity ) {
                    file_put_contents($logfile, $text, FILE_APPEND | LOCK_EX);
                }
            }else{
                 if( $log_activity ) {
                    file_put_contents($logfile, "CRITICAL ERROR: Absolute File Path or Parenting not allowed", FILE_APPEND | LOCK_EX);
                }
            }
        }

        // return template install information
        if( $action == 'install' || isset($_POST['install']) )
        {
            if( file_exists($install_xml) )
            {
                // search template-name in install.xml and return a zip file
                $xml = simplexml_load_file($install_xml) or die("Error: Cannot create xml object");
                header('Content-type: text/xml');

                echo '<?xml version="1.0" encoding="utf-8"?>
<ct><templates>
';
                foreach( $xml->templates->template as $template )
                {
                    if( $template['name'] && $template['uploadScript'] ) {
                        
                        if( strpos( $template['src'], "://" ) == false ) {
                            $src = $template['name'];
                        }else{
                            $src = $template['src'];
                        }
                        
                    echo '  <template name="' . $template['name'] . '" src="' . $src .
                         '" version="' . ( isset($template['version']) ? $template['version'] : "0.0.0" ) . 
                         '" homeAreaName="' . (isset($template['homeAreaName']) ? $template['homeAreaName'] : "") . 
                         '" uploadScript="' . $template['uploadScript'] . 
                         '" date="' . ( isset($template['date']) ? $template['date'] : "" ) . '" />
';
                    }
                }
                
              echo '</templates></ct>';
            }
            exit;
        }

         // return file exists information of website files
        if ( $action == 'lookup' || isset($_POST['lookup']) && isset($_POST['file']) )
        {
            if( strpos($_POST['file'], "../") == false )
            {
                 if ( file_exists( $server_path . $_POST['file'] ) )
                 {
                     echo $host_root . $_POST['file'];
                     exit;
                 } 
            }
            
            echo "not-found";
            exit;
        }
        
        // return template update information
        if ( ($action == 'update' || isset($_POST['update'])) && isset($_POST['name']) )
        {
            if ( file_exists($install_xml) )
            {
                // search template-name in install.xml and return a zip file
                $xml = simplexml_load_file($install_xml) or die("Error: Cannot create xml object");
               
                //header('Content-type: text/xml');

                echo '<?xml version="1.0" encoding="utf-8"?>
<ct>
';
                foreach( $xml->templates->template as $template )
                {
                    if( $template['name'] == $_POST['name'] )
                    {
                    
                        if( strpos( $template['src'], "://" ) == false ) {
                            $src = $template['name'];
                        }else{
                            $src = $template['src'];
                        }
                        
                echo '  <update type="'.(isset($template['patchtype']) ? $template['patchtype'] : 'template') . 
                            '" name="'  . $template['name'] . 
                            '" src="' . $src . 
                            '" version="' . ( isset($template['patchversion']) ? $template['patchversion'] : (isset($template['version']) ? $template['version'] : "0.0.0") ) . 
                            '" date="' . ( isset($template['patchdate']) ? $template['patchdate'] : (isset($template['date']) ? $template['date'] : "") ) . 
                        '" />
';
                        break;
                    }
                }
                
              echo '</ct>
';
            }
            exit;
        }

        // return file hash infos
        if( $action == 'dirinfo' || isset($_POST['dirinfo']) )
        {
            if( !$disable_upload ) {
               // header('Content-type: text/xml');
                $text = '<?xml version="1.0" encoding="utf-8" ?>
        <fileinfo path="'.$server_path.'" algo="'.$algo.'" maxsize="'.$max_upload_size.'">
        '; 
                $text .= listFilelist($server_path, $_POST['filelist']);
                $text .= '</fileinfo>';

                if( $log_activity ) {
                    file_put_contents($logfile, 'Send Dir-Info '.$_SERVER['REMOTE_ADDR'] . ' > ' . $_SERVER['HTTP_REFERER'] . "\n", FILE_APPEND | LOCK_EX);
                }
                echo $text;
            }else{
                echo 'disabled';
            }
            exit;
        }

        // get theme zip file from theme name
        if( ($action == 'src' || isset($_POST['src'])) && isset($_POST['name']) )
        {
           if (file_exists($install_xml) )
            {
                // search template-name in install.xml and return a zip file
                $xml = simplexml_load_file($install_xml) or die("Error: Cannot create xml object");
            
                foreach( $xml->templates->template as $template )
                {
                    if( $template['name'] == $_POST['name'] )
                    {
                        if( isset($template['version']) ) {
                            $v = $template['version'];
                        }else{
                            $v = '0.0.0';
                        } 

                        if( isset($_POST['patch']) && isset($template['update']) )
                        {
                            $f = $template['update'];
                            if( isset($template['patchversion']) ) {
                                $v = $template['patchversion'];
                            }
                        }
                        else
                        {
                            $f = $template['src'];
                        }

                        $file_name = substr( basename($f), 0, -4) . "-" . $v . ".zip";

                        if( $log_activity ) {
                            file_put_contents($logfile, 'Send Theme '.$_SERVER['REMOTE_ADDR'] . ' > ' . $_SERVER['HTTP_REFERER'] . ": " . $file_name. "\n", FILE_APPEND | LOCK_EX);
                        }

                        header("Content-Type: application/zip");
                        header("Content-Disposition: attachment; filename=$file_name");
                        header("Content-Length: " . filesize($f));
                        readfile($f);

                        break;
                    }
                }   
            }
            exit;
        }
       
        // Get hub version
        if( $action == 'hubversion' || isset($_POST['hubversion']) )
        {
            echo $hub_version;
            exit;
        }
       
        // Get latest sync content (xml content)
        if( $action == 'content' || isset($_POST['content']) )
        {
            if( isset($_POST['version']) )
            {
                $systr = $_POST['version'];
            }
            else
            {
                // Get latest version
                $systr = "1";
                
                if( file_exists($sync_dir . $version_file) ) {
                    $systr = file_get_contents($sync_dir . $version_file);
                }
            }
            
            $syfile = $sync_dir . $sync_name . $systr . '.xml';
            
            if( file_exists($syfile) ) {
                $text = file_get_contents( $syfile );
                echo $text;
            }else{
                echo 'not-found';
            }
            exit;
        }
       
        // Get latest content version (integer)
        if( $action == 'latest' || isset($_POST['latest']) )
        {
            if( file_exists($sync_dir.$version_file) ) {
                $systr = file_get_contents($sync_dir.$version_file);
                if( $systr ) {
                    echo $systr;
                    exit;
                }
            }
            echo "0";
            exit;  
        }
       
        // Get available content versions as comma separated integer list
        if( $action == 'versions' || isset($_POST['versions']) )
        {
            if( is_dir($sync_dir) )
            {
                // loop through files in sync_dir
                $all = opendir($sync_dir);
                $tmp = '';
                $len = strlen($sync_name);
                $tmp2 = "";
                
                while( $file = readdir($all) ) {
                    if( $file != '..' && $file != '.' && $file != $version_file && !is_dir($sync_dir.$file) )
                    {
                        $tmp2 = substr( $file, $len, -4 );
                        if( strlen($tmp2) < 6 ) { // Ignore Patch Files
                            $tmp .= $tmp2 . ",";
                        }
                    }
                }
                echo $tmp;
            }
            echo "";
            exit;  
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