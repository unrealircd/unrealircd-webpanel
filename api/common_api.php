<?php
include "../inc/common.php";

if(session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['id']))
	die("Access denied");

// Close the session now, otherwise other pages block
session_write_close();

// Only now make the connection (this can take a short while)
include "../inc/connection.php";

// Server Side Events
if (!defined('NO_EVENT_STREAM_HEADER'))
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

// Flush and stop output buffering (eg fastcgi w/NGINX)
while (@ob_end_flush());

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
		{
			/* Output at least something every timeout (10) seconds,
			 * otherwise PHP may not
			 * notice when the webclient is gone.
			 */
			echo "\n";
			continue;
		}
		send_sse($res);
	}
}

function api_timer_loop(int $every_msec, string $method, array|null $params = null)
{
	GLOBAL $rpc;

	$rpc->rpc()->add_timer("timer", $every_msec, $method, $params);
	if ($rpc->error)
	{
		/* Have to resort to old style: client-side timer */
		while(1)
		{
			$res = $rpc->query($method, $params);
			if (!$res)
				die;
			send_sse($res);
			usleep($every_msec * 1000);
		}
	}

	/* New style: use server-side timers */
	for(;;)
	{
		$res = $rpc->eventloop();
		if (!$res)
		{
			/* Output at least something every timeout (10) seconds,
			 * otherwise PHP may not
			 * notice when the webclient is gone.
			 */
			echo "\n";
			continue;
		}
		send_sse($res);
	}
}
