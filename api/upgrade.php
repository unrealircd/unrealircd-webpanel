<?php
require_once('common_api.php');

// Updating application code requires explicit administrative permission.
if (!current_user_can(PERMISSION_MANAGE_PLUGINS))
{
    http_response_code(403);
    die('Access denied');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST')
{
    header('Allow: POST');
    http_response_code(405);
    die('POST required');
}

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
        return error_log($upgrade->error);
    
    else if (!$upgrade->extractZip())
        return error_log($upgrade->error);
        
    $upgrade->cleanupOldFiles();
    
    if(!$upgrade->extractToWebdir())
        return error_log($upgrade->error);
        
    $upgrade->cleanupDownloadFiles();
    error_log("Upgrade was successful!");
}
else 
    error_log("No upgrade available");