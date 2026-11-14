<?php
/* Set RPC server */

require_once "../inc/common.php";

header("Content-type: application/json; charset=utf-8");

/* This changes the default server for everyone */
if (!current_user_can(PERMISSION_MANAGE_USERS))
{
	http_response_code(403);
	die(json_encode(["error" => "Access denied"]));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['server']) || !is_string($_POST['server']))
	die(json_encode(["error" => "No server selected"]));

$server = $_POST['server'];
if (!isset($config['unrealircd']) || !isset($config['unrealircd'][$server]))
	die(json_encode(["error" => "Server not found"]));

set_default_rpc_server($server);
write_config("unrealircd");

die(json_encode(true));
