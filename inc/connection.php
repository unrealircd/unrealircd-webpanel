<?php

if (!defined('UPATH'))
		die("Access denied");

function connect_to_ircd()
{
	GLOBAL $rpc;
	GLOBAL $config;

	$is_api_page = str_contains($_SERVER['SCRIPT_FILENAME'], "/api/") ? true : false;

	$options = []; /* options that we pass to new UnrealIRCd\Connection */

	$rpc = null; /* Initialize, mostly for API page failures */

	$server = get_active_rpc_server();
	if (!$server)
	{
		if ($is_api_page)
			return;
		Message::Fail("No RPC server configured. Go to Settings - RPC Servers.");
		die;
	}
	$host = $config["unrealircd"][$server]["host"];
	$port = $config["unrealircd"][$server]["port"];
	$rpc_user = $config["unrealircd"][$server]["rpc_user"];
	$rpc_password = $config["unrealircd"][$server]["rpc_password"];
	if (str_starts_with($rpc_password, "secret:"))
		$rpc_password = secret_decrypt($rpc_password);
	if (isset($config["unrealircd"][$server]["tls_verify_cert"]))
		$options["tls_verify"] = $config["unrealircd"][$server]["tls_verify_cert"];

	if (!$host || !$port || !$rpc_user)
	{
		if ($is_api_page)
			return;
		die("RPC Server is missing credentials");
	}

	if ($rpc_password === null)
	{
		if ($is_api_page)
			return;
		Message::Fail("Your RPC password in the DB was encrypted with a different key than config/config.php contains.<br>\n".
		              "Either restore your previous config/config.php or start with a fresh database.<br>\n");
		die;
	}

	$user = unreal_get_current_user();
	if ($user)
	{
		/* Set issuer for all the RPC commands */
		$options['issuer'] = $user->username;
	}

	/* Connect now */
	try {
			$rpc = new UnrealIRCd\Connection
			(
				"wss://$host:$port",
				"$rpc_user:$rpc_password",
				$options
			);
	}
	catch (Exception $e)
	{
		if ($is_api_page)
			return;
		Message::Fail("Unable to connect to UnrealIRCd: ".$e->getMessage() . "<br>".
		              "Verify that the connection details from Settings - RPC Servers match the ones in UnrealIRCd ".
		              "and that UnrealIRCd is up and running");
		throw $e;
	}
}

connect_to_ircd();
