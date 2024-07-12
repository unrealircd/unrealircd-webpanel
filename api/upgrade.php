<?php
require_once('common_api.php');

if (!$rpc)
    die();
error_log("Stuff");
$upgrade = new Upgrade();
error_log("...");
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
    if (!$upgrade->downloadUpgradeZip()
        || !$upgrade->extractZip()
        || !$upgrade->extractToWebdir()
        )
        return error_log($upgrade->error);
    $upgrade->cleanupOldFiles();
    $upgrade->cleanupDownloadFiles();
    error_log("Upgrade was successful!");
}
else 
    error_log("no upgrade");