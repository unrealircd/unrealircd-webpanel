<?php

class Upgrade
{
    public $web_dir;
    private $temp_dir;
    private static $temp_extracted_dir;
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
        if (file_exists($temp_dir)) {
            deleteDirectoryContents($temp_dir);
            rmdir($temp_dir);
        }
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
        $last_check = &$config['upgrade']['last_check'] ?? 0;
        if (isset($last_check) && time() - $last_check < 6) // only check every 15 mins
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
        $latest = $data[0];
        $config['upgrade']['latest_version'] = $latest['tag_name'];
        $last_check = time();
        $config['upgrade']['download_link'] = $latest['zipball_url'];
        write_config('upgrade');
        error_log($latest['tag_name'] ." ". WEBPANEL_VERSION);
        Upgrade::$upgrade_available = version_compare($latest['tag_name'], WEBPANEL_VERSION, ">") ? true : false;
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
            self::$temp_extracted_dir = findOnlyDirectory($this->temp_dir);
            error_log(self::$temp_extracted_dir);
            return true;
        } else {
            return false;
        }
    }
    function cleanupOldFiles()
    {
        foreach ($this->compareAndGetFilesToDelete() as $file)
        {
            unlink("$this->web_dir$file");
            error_log("Deleting: $file");   
        }
    }    
    function compareAndGetFilesToDelete() : array
    {
        $currentFiles = $this->listFiles($this->web_dir);
        $updateFiles = $this->listFiles(self::$temp_extracted_dir);
        $filesToDelete = array_diff($currentFiles, $updateFiles);
        $filesToActuallyDelete = [];
        error_log("Comparing... Files to delete:");
        foreach ($filesToDelete as $file)
        {
            // skip the relevant directories
            if (str_starts_with($file, "panel_upgrade/")
             || str_starts_with($file, "vendor/")
             || str_starts_with($file, "config/")
             || str_starts_with($file, "data/")
             || str_starts_with($file, "plugins/"))
                continue;
            $filesToActuallyDelete[] = $file;
        }
        return $filesToActuallyDelete;
    }
    
    function extractToWebdir()
    {
        recurse_copy(self::$temp_extracted_dir, $this->web_dir);
    }
    
    /**
     * Cleans up the extracted update files
     * @return void
     */
    function cleanupDownloadFiles()
    {
        $ex_dir = self::$temp_extracted_dir ?? findOnlyDirectory($this->temp_dir);
        deleteDirectoryContents($ex_dir);
        rmdir($ex_dir);
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


function deleteDirectoryContents($dir) {
    error_log("Deleting directory contents at $dir");
    if (!is_dir($dir)) {
        echo "The provided path is not a directory.";
        return false;
    }

    // Open the directory
    $handle = opendir($dir);
    if ($handle === false) {
        echo "Failed to open the directory.";
        return false;
    }

    // Loop through the directory contents
    while (($item = readdir($handle)) !== false) {
        // Skip the special entries "." and ".."
        if ($item == "." || $item == "..") {
            continue;
        }

        $itemPath = $dir."/".$item;

        // If the item is a directory, recursively delete its contents
        if (is_dir($itemPath)) {
            deleteDirectoryContents($itemPath);
            // Remove the empty directory
            rmdir($itemPath);
        } else {
            // If the item is a file, delete it
            unlink($itemPath);
        }
    }

    // Close the directory handle
    closedir($handle);

    return true;
}

function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) )
        if (( $file != '.' ) && ( $file != '..' ))
        {
            if ( is_dir($src . '/' . $file) )
                recurse_copy($src . '/' . $file, $dst . '/' . $file);

            else
                copy($src . '/' . $file, $dst . '/' . $file);
        }
        

    closedir($dir);
}
