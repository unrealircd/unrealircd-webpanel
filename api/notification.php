<?php
include "../common.php";
include "../connection.php";


if (!isset($_SESSION['id']))
    die("Access denied");

// Close the session now, otherwise other pages block
session_write_close();

// Set a valid header so browsers pick it up correctly.
//header('Content-type: text/html; charset=utf-8');
header("Content-type: application/json");

// Explicitly disable caching so Varnish and other upstreams won't cache.
header("Cache-Control: no-cache, must-revalidate");

// Setting this header instructs Nginx to disable fastcgi_buffering and disable
// gzip for this request.
header('X-Accel-Buffering: no');

// No time limit
set_time_limit(0);

// Send content immediately
ob_implicit_flush(1);

// Eh.. yeah...
ob_end_flush();

// If we use fastcgi, then finish the request now (UNTESTED)
if (function_exists('fastcgi_finish_request'))
    fastcgi_finish_request();


$sources = (isset($_GET['s']) && !empty($_GET['s'])) ? split($_GET['s'],",") : ["!debug","all"];
$rpc->log()->subscribe($sources);
for(;;)
{
    $res = $rpc->eventloop();
    if (!$res)
        continue;
    echo json_encode($res)."\n";
}
