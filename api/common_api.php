<?php
include "../inc/common.php";

if(session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['id']))
	die("Access denied");

// Close the session now, otherwise other pages block
session_write_close();

// Apache w/FPM is shit because it doesn't have flushpackets=on
// or not by default anyway, so we will fill up 4k buffers.
// Yeah, really silly... I know.
$fpm_workaround_needed = false;
if (str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache') &&
    function_exists('fpm_get_status') &&
    is_array(fpm_get_status()))
{
    $fpm_workaround_needed = true;
}

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
function flush_completely()
{
	while (1)
	{
		try {
			$ret = @ob_end_flush();
			if ($ret === false)
				break;
		} catch(Exception $e)
		{
			break;
		}
	}
}

flush_completely();

/* Send server-sent events (SSE) message */
function send_sse($json)
{
	GLOBAL $fpm_workaround_needed;
	$str = "data: ".json_encode($json)."\n\n";
	if ($fpm_workaround_needed)
		$str .= str_repeat(" ", 4096 - ((strlen($str)+1) % 4096))."\n";
	echo $str;
}

function api_log_loop($sources)
{
	GLOBAL $rpc;
	GLOBAL $fpm_workaround_needed;

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
			if ($fpm_workaround_needed)
				echo str_repeat(" ", 4095)."\n";
			else
				echo "\n";
			continue;
		}
		send_sse($res);
	}
}

function api_timer_loop(int $every_msec, string $method, array|null $params = null)
{
	GLOBAL $rpc;

	/* First, execute it immediately */
	$res = $rpc->query($method, $params);
	if (!$res)
		die;
	send_sse($res);
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
	/* - First, execute it immediately */
	$res = $rpc->query($method, $params);
	if (!$res)
		die;
	send_sse($res);
	/* - Then add the timer */
	for(;;)
	{
		$res = $rpc->eventloop();
		if (!$res)
		{
			/* Output at least something every timeout (10) seconds,
			 * otherwise PHP may not
			 * notice when the webclient is gone.
			 */
			if ($fpm_workaround_needed)
				echo str_repeat(" ", 4095)."\n";
			else
				echo "\n";
			continue;
		}
		send_sse($res);
	}
}
