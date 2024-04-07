<?php

define('NO_EVENT_STREAM_HEADER',1);
require_once('common_api.php');

if (!$rpc)
	die(json_encode([]));

/* Get the list */
$channels = $rpc->channel()->getAll();

$columns = array_column($channels, 'num_users');
array_multisort($columns, SORT_DESC, $channels);

$out = [];
foreach($channels as $channel)
{
	$modes = (isset($channel->modes)) ? "+" . explode(" ",$channel->modes)[0] : "<none>";
	$topic = '';
	if (isset($channel->topic))
		$topic = htmlentities(StripControlCharacters($channel->topic), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 | ENT_DISALLOWED);
	$date = explode("T", $channel->creation_time)[0];
	$out[] = [
		"Name" => htmlspecialchars($channel->name),
		"Users" => $channel->num_users,
		"Modes" => "<span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"+".htmlspecialchars($channel->modes)."\">$modes</span>",
		"Topic" => $topic,
		"Created" => "<span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"".$channel->creation_time."\">$date</span>",
	];
}

function custom_sort($a,$b)
{
	return $b["Users"] <=> $a["Users"];
}

usort($out, "custom_sort");

echo json_encode($out);
