<?php
require_once('common_api.php');

if (!$rpc)
    die();

$upgrade = new Upgrade();

if (Upgrade::$upgrade_available)
{
    error_log("Upgrade available, downloading");
    $upgrade->downloadUpgradeZip();
}