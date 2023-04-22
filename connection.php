<?php

if (!defined('UPATH'))
		die("Access denied");

function connect_to_ircd()
{
	GLOBAL $rpc;

	$host = get_config("unrealircd::host");
	$port = get_config("unrealircd::port");
	$rpc_user = get_config("unrealircd::rpc_user");
	$rpc_password = get_config("unrealircd::rpc_password");
	if (str_starts_with($rpc_password, "secret:"))
		$rpc_password = secret_decrypt($rpc_password);

	if (!$host || !$port || !$rpc_user || !$rpc_password)
		die("Unable to find RPC credentials in your config.php");

	$tls_verify = get_config("unrealircd::tls_verify_cert");

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
