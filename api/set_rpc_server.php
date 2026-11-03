<?php
/* Set RPC server */

require_once "../inc/common.php";

if (!current_user_can(PERMISSION_MANAGE_USERS))
{
	http_response_code(403);
	die('Access denied');
}

if (!isset($_POST['server']))
	die("ERROR: No server selected");

$server = $_POST['server'];
if (!isset($config['unrealircd']) || !isset($config['unrealircd'][$server]))
	die("ERROR: Server not found");

set_default_rpc_server($server);
write_config("unrealircd");

die(json_encode(true));
