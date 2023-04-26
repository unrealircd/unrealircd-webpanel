<?php

if (!defined('UPATH'))
		die("Access denied");

function connect_to_ircd()
{
	GLOBAL $rpc;
	GLOBAL $config;

	$server = get_active_rpc_server();
	if (!$server)
	{
		Message::Fail("No RPC server configured. Go to Settings - RPC Servers.");
		die;
	}
	$host = $config["unrealircd"][$server]["host"];
	$port = $config["unrealircd"][$server]["port"];
	$rpc_user = $config["unrealircd"][$server]["rpc_user"];
	$rpc_password = $config["unrealircd"][$server]["rpc_password"];
	if (str_starts_with($rpc_password, "secret:"))
		$rpc_password = secret_decrypt($rpc_password);
	$tls_verify = $config["unrealircd"][$server]["tls_verify_cert"];

	if (!$host || !$port || !$rpc_user)
		die("RPC Server is missing credentials");

	if ($rpc_password === null)
	{
		Message::Fail("Your RPC password in the DB was encrypted with a different key than config/config.php contains.<br>\n".
		              "Either restore your previous config/config.php or start with a fresh database.<br>\n");
		die;
	}

	/* Connect now */
	try {
			$rpc = new UnrealIRCd\Connection
			(
				"wss://$host:$port",
				"$rpc_user:$rpc_password",
				["tls_verify" => $tls_verify]
			);
	}
	catch (Exception $e)
	{
		Message::Fail("Unable to connect to UnrealIRCd: ".$e->getMessage() . "<br>".
		              "Verify your connection details in config.php (rpc user, rpc password, host) and ".
		              "verify your UnrealIRCd configuration (listen block with listen::options::rpc and ".
		              "an rpc-user block with the correct IP allowed and the correct username and password).");
		throw $e;
	}

	$user = unreal_get_current_user();
	if ($user)
	{
		/* Set issuer for all the RPC commands */
		$rpc->rpc()->set_issuer($user->username);
	}
}

connect_to_ircd();
