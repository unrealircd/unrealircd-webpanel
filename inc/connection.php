<?php

if (!defined('UPATH'))
		die("Access denied");

function get_active_rpc_server()
{
	// TODO: make user able to override this - either in user or in session

	foreach (get_config("unrealircd") as $displayname=>$e)
	{
		if (isset($e["default"]) && $e["default"])
			return $displayname;
	}
	return null;
}

function connect_to_ircd()
{
	GLOBAL $rpc;
	GLOBAL $config;

	$server = get_active_rpc_server();
	if (!$server)
		die("No RPC server configured as primary");
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
		die("Your RPC password in the DB was encrypted with a different key than config/config.php contains.<br>\n".
		    "Either restore your previous config/config.php or start with a fresh database.<br>\n");
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
		echo "Unable to connect to UnrealIRCd: ".$e->getMessage() . "<br><br>";
		echo "Verify your connection details in config.php (rpc user, rpc password, host) and ".
		     "verify your UnrealIRCd configuration (listen block with listen::options::rpc and ".
		     "an rpc-user block with the correct IP allowed and the correct username and password).";
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
