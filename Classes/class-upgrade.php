<?php

class Upgrade
{
    public $web_dir;
    public $download_dir;
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
        $this->download_dir = $this->web_dir.'downloads';
        
        Upgrade::$upgrade_available = false;
        $this->checkForNew();
        if ($this->error)
            error_log($this->error);
        else
        {    
            if (Upgrade::$upgrade_available)
                error_log("Upgrade available! Version ".Upgrade::$latest_version);
            else
                error_log("No upgrade available");
        }
    }
    
    /** Checks for a new upgrade */
    function checkForNew()
    {
        global $config;
        read_config_db();
        if (time() - $config['upgrade']['last_check'] < 300) // only check every 15 mins
            return $config['upgrade']['latest_version'] > WEBPANEL_VERSION ? true : false;
            
        // Define the API URL to check for updates
        $apiUrl = "https://api.github.com/repos/unrealircd/unrealircd-webpanel/releases"; // Replace with your API URL
        $response = file_get_contents($apiUrl, false, stream_context_create(["http" => ["method" => "GET", "header" => "User-agent: UnrealIRCd Webpanel"]]));
        if ($response === false)
        {
            $this->error = "Couldn't check github.";
            return false;
        }
        $data = json_decode($response, true);
        $latest = $data[count($data) - 1];
        $config['upgrade']['latest_version'] = $latest['tag_name'];
        $config['upgrade']['last_check'] = time();
        $config['upgrade']['download_link'] = $latest['zipball_url'];
        write_config('upgrade');
        Upgrade::$upgrade_available = (float)$latest['tag_name'] > WEBPANEL_VERSION ? true : false;
    }
    
    function downloadUpgradeZip()
    {
        $ch = curl_init(get_config('upgrade::download_link'));
        $fp = fopen($this->download_dir."/unrealircd-webpanel-upgrade.zip", 'w+');
    
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: UnrealIRCd Webpanel',
        ]);
        $success = curl_exec($ch);
    
        curl_close($ch);
        fclose($fp);
    
        return $success;
    }
}

 // Define the URL to download the update
$downloadUrl = "https://example.com/api/download_update"; // Replace with your download URL
        
// Define the URL to download the update
$downloadUrl = "https://example.com/api/download_update"; // Replace with your download URL

// Define the directory where the update will be extracted
$webDir = __DIR__; // Current directory

// Function to check for updates
function checkForUpdate($url) {
    $response = file_get_contents($url);
    if ($response === FALSE) {
        die("Error checking for updates.");
    }
    $data = json_decode($response, true);
    return $data['update_available'] ?? false;
}

// Function to download the update
function downloadUpdate($url, $savePath) {
    $ch = curl_init($url);
    $fp = fopen($savePath, 'w+');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $success = curl_exec($ch);

    curl_close($ch);
    fclose($fp);

    return $success;
}

// Function to extract the zip file
function extractZip($zipPath, $extractTo) {
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        return true;
    } else {
        return false;
    }
}
/**
// Check for updates
if (checkForUpdate($apiUrl)) {
    $tempZipFile = $webDir . '/update.zip';

    // Download the update
    if (downloadUpdate($downloadUrl, $tempZipFile)) {
        // Extract the update
        if (extractZip($tempZipFile, $webDir)) {
            echo "Update applied successfully.";
        } else {
            echo "Failed to extract the update.";
        }
        // Clean up the temporary zip file
        unlink($tempZipFile);
    } else {
        echo "Failed to download the update.";
    }
} else {
    echo "No update available.";
}
*/
