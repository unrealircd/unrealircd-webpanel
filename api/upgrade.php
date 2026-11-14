<?php
define('NO_EVENT_STREAM_HEADER', 1);
require_once('common_api.php');

/* Upgrading replaces the panel's code: only for users who can manage
 * plugins, and only with a POST (which needs the CSRF token).
 */
if (!current_user_can(PERMISSION_MANAGE_PLUGINS))
{
    http_response_code(403);
    die(json_encode(["error" => "Access denied"]));
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST')
{
    http_response_code(405);
    die(json_encode(["error" => "Use POST"]));
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