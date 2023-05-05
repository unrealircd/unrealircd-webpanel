<?php

require_once('common_api.php');

if (!$rpc)
    die();

/* Filter - almost everything... */
$log_list = ["all", "!debug"];
/* Add these as well, they are not logged by default
 * in the memory log either. See
 * https://github.com/unrealircd/unrealircd/commit/45342c2d33968178cd07a12cd6fdc4e65b604134
 * Added here separately because we may want to make
 * this an option...
 */
array_push($log_list,
    "!join.LOCAL_CLIENT_JOIN",
    "!join.REMOTE_CLIENT_JOIN",
    "!part.LOCAL_CLIENT_PART",
    "!part.REMOTE_CLIENT_PART",
    "!kick.LOCAL_CLIENT_KICK",
    "!kick.REMOTE_CLIENT_KICK",
);

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

api_log_loop($log_list);
