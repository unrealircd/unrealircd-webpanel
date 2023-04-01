<?php

if (!defined('UPATH'))
		die("Access denied");

if (!defined('UNREALIRCD_RPC_USER') ||
		!defined('UNREALIRCD_RPC_PASSWORD') ||
		!defined('UNREALIRCD_HOST') ||
		!defined('UNREALIRCD_PORT')
) die("Unable to find RPC credentials in your config.php");

$tls_verify = (defined('UNREALIRCD_SSL_VERIFY')) ? UNREALIRCD_SSL_VERIFY : true;
$api_login = UNREALIRCD_RPC_USER.":".UNREALIRCD_RPC_PASSWORD;

/* Connect now */
try {
		$rpc = new UnrealIRCd\Connection
		(
			"wss://".UNREALIRCD_HOST.":".UNREALIRCD_PORT,
			$api_login,
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
