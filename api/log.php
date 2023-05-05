<?php

require_once('common_api.php');

if (!$rpc)
    die();

/* Basically everything ;) */

$response = $rpc->log()->getAll();
if ($response !== false)
{
    /* Only supported in later UnrealIRCd versions */
    $cnt = 0;
    foreach($response as $r)
    {
        $r = (ARRAY)$r;
        $cnt++;
        if (($cnt % 100) != 0)
            $r["sync_option"] = "no_sync";
        send_sse($r);
    }
}

$r = ["sync_option"=>"sync_now"];
send_sse($r);

api_log_loop(["all", "!debug"]);
