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
$upgrade->checkForNew();

if (Upgrade::$upgrade_available)
{
    error_log("Upgrade available, downloading and installing");
    if (!$upgrade->downloadUpgradeZip()
        || !$upgrade->extractZip()
        || !$upgrade->cleanupOldFiles()
        || !$upgrade->extractToWebdir())
        return error_log($upgrade->error);
        
    error_log("Upgrade was successful!");
}