<?php
/**
 * RPC Functionality for UnrealIRCd Admin Webpanel
 * License: GPLv3 or later
 * Author: ValwareIRC
 * GitHub URI: ValwareIRC/unrealircd-webpanel
 * 2023
 */

if (!defined('UPATH'))
	die("Access denied");

require UPATH . '/vendor/autoload.php';

use UnrealIRCd\Connection;

class RPC_List
{
	static $user = [];
	static $channel = [];
	static $tkl = [];
	static $spamfilter = [];

	static $opercount = 0;
	static $services_count = 0;
	static $most_populated_channel = NULL;
	static $channel_pop_count = 0;
}

function rpc_pop_lists()
{
	GLOBAL $rpc;

	/* Get the user list */
	$ret = $rpc->query("user.list");
	// TODO: error checking

	foreach($ret->list as $r)
	{
		RPC_List::$user[] = $r;
		if (strpos($r->user->modes,"o") !== false && strpos($r->user->modes,"S") == false)
			RPC_List::$opercount++;
		elseif (strpos($r->user->modes,"S") !== false)
			RPC_List::$services_count++;
	}

	/* Get the channels list */
	$ret = $rpc->query("channel.list");
	foreach($ret->list as $r)
	{
		RPC_List::$channel[] = $r;
		if ($r->num_users > RPC_List::$channel_pop_count)
		{
			RPC_List::$channel_pop_count = $r->num_users;
			RPC_List::$most_populated_channel = $r->name;
		}
	}

	/* Get the tkl list */
	$ret = $rpc->query("server_ban.list");
	foreach($ret->list as $r)
		RPC_List::$tkl[] = $r;

	/* Get the spamfilter list */
	$ret = $rpc->query("spamfilter.list");
	foreach($ret->list as $r)
		RPC_List::$spamfilter[] = $r;

}


/** RPC TKL Add */
function rpc_tkl_add($name, $type, $expiry, $reason) : bool
{
	GLOBAL $rpc;

	$params = ["name" => $name, "type" => $type, "reason" => $reason, "duration_string" => $expiry];
	$result = $rpc->query("server_ban.add", $params);
	if ($result->error)
	{
		$msg = "The $type could not be added: $name - ".$result->error->message . " (" . $result->error->code . ")";
		Message::Fail($msg);
		return false;
	}
	return true;
}


/** RPC TKL Delete */
function rpc_tkl_del($name, $type) : bool
{
	GLOBAL $rpc;

	$params = ["name" => $name, "type" => $type];
	$result = $rpc->query("server_ban.del", $params);
	if ($result->error)
	{
		$msg = "The $type could not be deleted: $name - ".$result->error->message . " (" . $result->error->code . ")";
		Message::Fail($msg);
		return false;
	}
	return true;
}

/** RPC Spamfilter Delete
 * 
 */
function rpc_sf_del($name, $mtype, $targets, $action) : bool
{
	GLOBAL $rpc;

	$params = ["name" => $name, "match_type" => $mtype, "spamfilter_targets" => $targets, "ban_action" => $action, "set_by" => "YoMama"];
	$result = $rpc->query("spamfilter.del", $params);
	if ($result->error)
	{
		$msg = "The spamfilter entry could not be deleted: $name - ".$result['error']['message'] . " (" . $result['error']['code'] . ")";
		Message::Fail($msg);
		return false;
	}
	else
	{
		$r = $result->tkl;
		Message::Success("Deleted spamfilter entry: ".$r->name." [type: ".$r->match_type."] [targets: ".$r->spamfilter_targets. "] [action: ".$r->ban_action."] [reason: ".$r->reason."] [set by: ".$r->set_by."]");
	}
	return true;
}

/** Convert the duration_string */
function rpc_convert_duration_string($str)
{
	$tok = explode("w", $str);
	$weeks = $tok[0];
	$tok = explode("d", $tok[1]);
	$days = $tok[0];
	$tok = explode("h", $tok[1]);
	$hours = $tok[0];
	return "$weeks weeks, $days days and $hours hours";
	
}