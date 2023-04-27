<?php

require_once('common_api.php');

if (!$rpc)
    die();

/* Basically everything ;) */

$response = $rpc->log()->getAll();
if ($response !== false)
{
    /* Only supported in later UnrealIRCd versions */
    foreach($response as $r)
        send_sse($r);
}

api_log_loop(["all", "!debug"]);
