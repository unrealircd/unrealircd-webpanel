<?php
require_once('common_api.php');

if (!$rpc)
    die();
    
$upgrade = new Upgrade();

if ($upgrade->error)
{
    error_log("Couldn't create dir.");
    return;
}
error_log("Checking for upgrade");
$upgrade->checkForNew();
if (Upgrade::$upgrade_available)
{
    error_log("Upgrade available, downloading and installing");
    if (!$upgrade->downloadUpgradeZip())
        error_log($upgrade->error);
    else if (!$upgrade->extractZip())
        error_log($upgrade->error);
        
    $upgrade->cleanupOldFiles();
    
    if(!$upgrade->extractToWebdir())
        return error_log($upgrade->error);
        
    $upgrade->cleanupDownloadFiles();
    error_log("Upgrade was successful!");
}
else 
    error_log("No upgrade available");