<?php

require_once "../inc/common.php";

/* Always JSON, never HTML: the request values must not be able to
 * become markup in the panel's origin.
 */
header("Content-type: application/json; charset=utf-8");
header("X-Content-Type-Options: nosniff");

/* Testing a connection uses (and can send out) the stored RPC password,
 * so only users who can manage the RPC servers may do it.
 */
if (!current_user_can(PERMISSION_MANAGE_USERS))
{
	http_response_code(403);
	die(json_encode(["error" => "Access denied"]));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST) || empty($_POST))
		die(json_encode(["error" => "Incorrect parameters"]));

foreach(array("tls_verify","host","port","user","password","edit_existing") as $k)
{
	if (!isset($_POST[$k]) || !is_string($_POST[$k]))
		die(json_encode(["error" => "Missing parameter: $k"]));
	${$k} = $_POST[$k];
}

if ($tls_verify == "false")
	$tls_verify = false;
elseif ($tls_verify == "true")
	$tls_verify = true;
else
	$tls_verify = false;

if (!is_valid_rpc_host($host))
	die(json_encode(["error" => "Invalid hostname or IP"]));
if (!ctype_digit($port) || ((int)$port < 1) || ((int)$port > 65535))
	die(json_encode(["error" => "Invalid port"]));

if (($edit_existing) && ($password == "****************"))
{
	/* If editing existing and password unchanged, use the stored password.
	 * Only when the host and port are unchanged: otherwise the stored
	 * password would be sent to a new, possibly untrusted, host.
	 */
	if (!isset($config["unrealircd"][$edit_existing]))
		die(json_encode(["error" => "Server not found"]));
	$e = $config["unrealircd"][$edit_existing];
	if (((string)$e["host"] !== $host) || ((string)$e["port"] !== $port))
		die(json_encode(["error" => "The host or port was changed: enter the password again"]));
	$password = $e["rpc_password"];
	if (str_starts_with($password, "secret:"))
		$password = secret_decrypt($password);
	if ($password === null)
		die(json_encode(["error" => "Could not decrypt the stored password"]));
}

try {
		$rpc = new UnrealIRCd\Connection
		(
				"wss://$host:$port",
				"$user:$password",
				["tls_verify" => $tls_verify]
		);
}
catch (Exception $e)
{
		die(json_encode(["error" => "Unable to connect to UnrealIRCd: ".$e->getMessage()], JSON_INVALID_UTF8_SUBSTITUTE));
}

die(json_encode(["success" => "Successfully connected"]));
