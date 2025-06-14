<?php

if (!defined('UPATH'))
	    die(__('access_denied'));

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
		Message::Fail(__('rpc_serverconfigured_fail'));
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
		die(__('rpc_serverconfigured_credentials'));
	}

	if ($rpc_password === null)
	{
		if ($is_api_page)
			return;
		Message::Fail(__('rpc_serverconfigured_config'));
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
		Message::Fail(sprintf(
    __('rpc_serverconfigured_nounrealircd'),
    htmlspecialchars($e->getMessage())
		));
		throw $e;
	}
}

connect_to_ircd();
