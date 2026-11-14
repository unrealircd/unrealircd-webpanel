<?php

define('NO_EVENT_STREAM_HEADER',1);
require_once('common_api.php');

header("Content-type: application/json; charset=utf-8");

if (!$rpc)
	die(json_encode([]));

/* Get the list */
$channels = $rpc->channel()->getAll();

$columns = array_column($channels, 'num_users');
array_multisort($columns, SORT_DESC, $channels);

/* All values are returned raw (not HTML-escaped). The channel list page
 * escapes them for display, and uses the raw name to open the channel.
 * Never navigate or look up by an escaped value: "#a&amp;b" is a
 * different, valid channel name than "#a&b".
 */
$out = [];
foreach($channels as $channel)
{
	$modes = (isset($channel->modes)) ? "+" . explode(" ",$channel->modes)[0] : "<none>";
	$topic = '';
	if (isset($channel->topic))
		$topic = StripControlCharacters($channel->topic);
	$date = explode("T", $channel->creation_time)[0];
	$out[] = [
		"Name" => $channel->name,
		"Users" => $channel->num_users,
		"Modes" => $modes,
		"ModesFull" => isset($channel->modes) ? "+".$channel->modes : "",
		"Topic" => $topic,
		"Created" => $date,
		"CreatedFull" => $channel->creation_time,
	];
}

function custom_sort($a,$b)
{
	return $b["Users"] <=> $a["Users"];
}

usort($out, "custom_sort");

echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE);
