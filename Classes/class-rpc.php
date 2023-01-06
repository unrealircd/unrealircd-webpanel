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
use UnrealIRCd\User;
use UnrealIRCd\Channel;

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
	$ret = $rpc->user()->getAll();
	// TODO: error checking

	foreach($ret as $r)
	{
		RPC_List::$user[] = $r;
		if (strpos($r->user->modes,"o") !== false && strpos($r->user->modes,"S") == false)
			RPC_List::$opercount++;
		elseif (strpos($r->user->modes,"S") !== false)
			RPC_List::$services_count++;
	}

	/* Get the channels list */
	$ret = $rpc->channel()->getAll();
	foreach($ret as $r)
	{
		RPC_List::$channel[] = $r;
		if ($r->num_users > RPC_List::$channel_pop_count)
		{
			RPC_List::$channel_pop_count = $r->num_users;
			RPC_List::$most_populated_channel = $r->name;
		}
	}

	/* Get the tkl list */
	$ret = $rpc->serverban()->getAll();
	foreach($ret as $r)
		RPC_List::$tkl[] = $r;

	/* Get the spamfilter list */
	$ret = $rpc->spamfilter()->getAll();
	foreach($ret as $r)
		RPC_List::$spamfilter[] = $r;

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