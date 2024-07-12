<?php

class Upgrade
{
    public $web_dir;
    private $temp_dir;
    private $temp_extracted_dir;
    public static $upgrade_available;
    public static $last_check;
    public $error;
    public static $latest_version;
    function __construct()
    {
        global $config;
        read_config_db();
        if (!get_config('upgrade'))
        {
            $config['upgrade'] =[];
            write_config('upgrade');
        }
        
        $tok = split(__DIR__, '/');
        unset($tok[count($tok) - 1]);
        $this->web_dir = implode('/',$tok).'/';
        
        /** prepare the temp directory */
        $temp_dir = $this->web_dir."panel_upgrade";
        $temp_dir .= ($temp_dir[strlen($temp_dir) - 1] != '/') ? "/" : "";
        array_map('unlink', array_filter((array) glob("$temp_dir/*.*")));
        array_map('rmdir', array_filter((array) glob("$temp_dir/*")));
        if (file_exists($temp_dir)) rmdir($temp_dir);
        $mkdir = mkdir($temp_dir, 0755, true);

        $this->temp_dir = $mkdir ? $temp_dir : NULL;
        $this->error = $mkdir ? NULL : "Could not create directory: $temp_dir";
        Upgrade::$upgrade_available = false;
        if ($this->error)
            error_log($this->error);
    }
    
    /** Checks for a new upgrade */
    function checkForNew()
    {
        global $config;
        read_config_db();
        $last_check = &$config['upgrade']['last_check'];
        if (isset($last_check) && time() - $last_check < 300) // only check every 15 mins
        {
            error_log("Skipping upgrade check, checked ".time() - $last_check." seconds ago");
            return false;
        }
        error_log(time()." - ".$last_check." = ".time()-$last_check);
        $apiUrl = "https://api.github.com/repos/unrealircd/unrealircd-webpanel/releases"; 
        $response = file_get_contents($apiUrl, false, stream_context_create(
                ["http" => ["method" => "GET", "header" => "User-agent: UnrealIRCd Webpanel"]]
            ));
            
        if ($response === false)
        {
            $this->error = "Couldn't check github.";
            return false;
        }
        $data = json_decode($response, true);
        $latest = $data[count($data) - 1];
        $config['upgrade']['latest_version'] = $latest['tag_name'];
        $last_check = time();
        $config['upgrade']['download_link'] = $latest['zipball_url'];
        write_config('upgrade');
        Upgrade::$upgrade_available = (float)$latest['tag_name'] > WEBPANEL_VERSION ? true : false;
    }
    
    function downloadUpgradeZip()
    {
        $ch = curl_init(get_config('upgrade::download_link'));
        $fp = fopen($this->temp_dir."unrealircd-webpanel-upgrade.zip", 'w+');
    
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: UnrealIRCd Webpanel',
        ]);
        $success = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($code == "403" || $code == "404")
        {
            $this->error ="Unable to download";
        }
        curl_close($ch);
        fclose($fp);
    
        return $success;
    }
    function extractZip() {
        $zip = new ZipArchive;
        if ($zip->open($this->temp_dir."unrealircd-webpanel-upgrade.zip") === true)
        {
            $zip->extractTo("$this->temp_dir");
            $zip->close();
            unlink($this->temp_dir."unrealircd-webpanel-upgrade.zip");
            $this->temp_extracted_dir = findOnlyDirectory($this->temp_dir);
            error_log($this->temp_extracted_dir);
            return true;
        } else {
            return false;
        }
    }
    function cleanupOldFiles()
    {
        $currentFiles = $this->listFiles($this->web_dir);
        $updateFiles = $this->listFiles($this->temp_extracted_dir);
    
        $filesToDelete = array_diff($currentFiles, $updateFiles);
        error_log("Comparing... Files to delete:");
        foreach ($filesToDelete as $file)
        {
            error_log($file);
            //unlink("$file");
        }

    }
    
    function extractToWebdir()
    {
        $zip = new ZipArchive;
        if ($zip->open($this->temp_dir."unrealircd-webpanel-upgrade.zip") === true)
        {
            $extracted = $zip->extractTo(str_replace("//","/",get_config('base_url')));
            $zip->close();
            if (!$extracted)
            {
                error_log("Cannot extract to web directory. Permission denied.");
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Cleans up the extracted update files
     * @return void
     */
    function cleanupDownloadFiles()
    {
        array_map('unlink', array_filter((array) glob("$this->temp_dir/*.*")));
        array_map('rmdir', array_filter((array) glob("$this->temp_dir/*")));
    }
    
    function listFiles($dir) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file)
        {
            if ($file->isFile())
            {
                $f = substr($file->getPathname(), strlen($dir));
                if ($f[0] == "/") $f = substr($f,1);
                error_log("$dir => $f");
                
                
                
                
                $files[] = $f;
            }
        }
        return $files;
    }
}


function findOnlyDirectory($topDir) {
    // Ensure the directory exists and is indeed a directory
    if (!is_dir($topDir)) {
        die("The specified path is not a directory.");
    }

    // Open the directory
    $dirHandle = opendir($topDir);
    if ($dirHandle === false) {
        die("Unable to open directory.");
    }

    $directories = [];
    
    // Read through the directory contents
    while (($entry = readdir($dirHandle)) !== false) {
        $fullPath = $topDir . DIRECTORY_SEPARATOR . $entry;
        // Check if the entry is a directory and not . or ..
        if (is_dir($fullPath) && $entry !== '.' && $entry !== '..') {
            $directories[] = $fullPath;
        }
    }

    // Close the directory handle
    closedir($dirHandle);

    // Check if there is exactly one directory
    if (count($directories) === 1) {
        return $directories[0];
    } elseif (count($directories) === 0) {
        return "No directories found after extracting. Possibly missing php-zip extention. Aborting upgrade.";
    } else {
        return "Multiple directories found. Previous cleanup was unsuccessful for some reason, maybe a permissions error? Aborting upgrade.";
    }
}