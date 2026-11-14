<?php

define('NO_EVENT_STREAM_HEADER',1);
require_once('common_api.php');

if (!$rpc)
	die(json_encode([]));

/* Get the user list */
$users = $rpc->user()->getAll();

/* The display columns contain HTML, so every value from IRC in them is
 * escaped here. The *_raw fields are raw values for use with textContent.
 */
$out = [];
foreach($users as $user)
{
	$isBot = (strpos($user->user->modes, "B") !== false) ? ' <span class="badge rounded-pill badge-dark">Bot</span>' : "";
	$nick = h($user->name).$isBot;

	$country = isset($user->geoip->country_code) ? '<img src="https://flagcdn.com/48x36/'.h(strtolower($user->geoip->country_code)).'.png" width="20" height="15"> '.h($user->geoip->country_code) : "";

	if ($user->hostname == $user->ip)
			$hostip = $user->ip;
	else if ($user->ip == null)
			$hostip = $user->hostname;
	else
			$hostip = $user->hostname . " (".$user->ip.")";
	$hostip = h($hostip);

	$account = (isset($user->user->account)) ? "<a href=\"".h(get_config("base_url")."users/?account=".urlencode($user->user->account))."\">".h($user->user->account)."</a>" : '<span class="badge rounded-pill badge-primary">None</span>';
	$oper = (isset($user->user->operlogin)) ? h($user->user->operlogin)." <span class=\"badge rounded-pill badge-secondary\">".h($user->user->operclass ?? "")."</span>" : "";
	if (!strlen($oper))
			$oper = (strpos($user->user->modes, "S") !== false) ? '<span class="badge rounded-pill badge-warning">Services Bot</span>' : "";
	$servername = h($user->user->servername);
	$reputation = h($user->user->reputation);

	$nick = "<a href=\"details.php?nick=".h(urlencode($user->id))."\">$nick</a>";

	$out[] = [
		"Select" => "<input type=\"checkbox\" value=\"" . h(base64_encode($user->id))."\" name=\"userch[]\">", /* yeah ridiculous to have here in this file and the feed ;) */
		"Nick" => $nick,
		"Country" => $country,
		"Host/IP" => $hostip,
		"Account" => $account,
		"Oper" => $oper,
		"Connected to" => $servername,
		"Reputation" => $reputation,
		"nick_raw" => $user->name,
		"host_raw" => $user->hostname,
		"ip_raw" => $user->ip,
	];
}

function custom_sort($a,$b)
{
	return strcmp(strtoupper($a["nick_raw"]), strtoupper($b["nick_raw"]));
}

usort($out, "custom_sort");

echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE);
