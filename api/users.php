<?php

define('NO_EVENT_STREAM_HEADER',1);
require_once('common_api.php');
header("Content-type: application/json; charset=utf-8");

/* Get the user list */
$users = $rpc->user()->getAll();

$out = [];
foreach($users as $user)
{
	// base64_encode($user->id)

	$isBot = (strpos($user->user->modes, "B") !== false) ? ' <span class="badge rounded-pill badge-dark">Bot</span>' : "";
	$nick = htmlspecialchars($user->name).$isBot;

	$country = isset($user->geoip->country_code) ? '<img src="https://flagcdn.com/48x36/'.htmlspecialchars(strtolower($user->geoip->country_code)).'.png" width="20" height="15"> '.htmlspecialchars($user->geoip->country_code) : "";

	if ($user->hostname == $user->ip)
			$hostip = $user->ip;
	else if ($user->ip == null)
			$hostip = $user->hostname;
	else
			$hostip = $user->hostname . " (".$user->ip.")";
	$hostip = htmlspecialchars($hostip);
	
	$account = (isset($user->user->account)) ? "<a href=\"".get_config("base_url")."users/?account=".$user->user->account."\">".htmlspecialchars($user->user->account)."</a>" : '<span class="badge rounded-pill badge-primary">None</span>';
	$modes = (isset($user->user->modes)) ? "+" . $user->user->modes : "<none>";
	$oper = (isset($user->user->operlogin)) ? $user->user->operlogin." <span class=\"badge rounded-pill badge-secondary\">".$user->user->operclass."</span>" : "";
	if (!strlen($oper))
			$oper = (strpos($user->user->modes, "S") !== false) ? '<span class="badge rounded-pill badge-warning">Services Bot</span>' : "";
	$servername = $user->user->servername;
	$reputation = $user->user->reputation;

	$nick = "<a href=\"details.php?nick=".$user->id."\">$nick</a>";

	$out[] = [
		"Select" => "<input type=\"checkbox\" label='selectall' onClick=\"toggle_user(this)\" />", /* yeah ridiculous to have here in this file and the feed ;) */
		"Nick" => $nick,
		"Country" => $country,
		"Host/IP" => $hostip,
		"Account" => $account,
		"Usermodes" => $modes,
		"Oper" => $oper,
		"Connected to" => $servername,
		"Reputation" => $reputation,
	];
}

function custom_sort($a,$b)
{
	return strcmp(strtoupper($a["Nick"]), strtoupper($b["Nick"]));
}

usort($out, "custom_sort");

echo json_encode($out);
