<?php
include "../common.php";

if (!isset($_SESSION['id']))
	die("Access denied");

// Close the session now, otherwise other pages block
session_write_close();

// Only now make the connection (this can take a short while)
include "../connection.php";

// Server Side Events
header('Content-Type: text/event-stream');

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

/* Send server-sent events (SSE) message */
function send_sse($json)
{
	echo "data: ".json_encode($json)."\n\n";
}

function api_log_loop($sources)
{
	GLOBAL $rpc;

	$rpc->log()->subscribe($sources);
	if ($rpc->error)
	{
		echo $rpc->error;
		die;
	}

	for(;;)
	{
		$res = $rpc->eventloop();
		if (!$res)
			continue;
		send_sse($res);
	}
}

function api_timer_loop(int $every_msec, string $method, array|null $params = null)
{
	GLOBAL $rpc;

	$rpc->rpc()->add_timer("timer", $every_msec, $method, $params);
	if ($rpc->error)
	{
		echo $rpc->error;
		die;
	}

	for(;;)
	{
		$res = $rpc->eventloop();
		if (!$res)
			continue;
		send_sse($res);
	}
}
