<?php

require_once "../inc/common.php";

if (!isset($_POST) || empty($_POST))
		die(json_encode(["error" => "Incorrect parameters"]));

foreach(array("tls_verify","host","port","user","password","edit_existing") as $k)
{
	if (!isset($_POST[$k]))
		die("MISSING: $k");
	${$k} = $_POST[$k];
}

if ($tls_verify == "false")
	$tls_verify = false;
elseif ($tls_verify == "true")
	$tls_verify = true;

if (($edit_existing) && ($password == "****************"))
{
	/* If editing existing and password unchanged,
	 * try to look up existing password.
	 */
	if (isset($config["unrealircd"][$edit_existing]))
	{
		$password = $config["unrealircd"][$edit_existing]["rpc_password"];
		if (str_starts_with($password, "secret:"))
			$password = secret_decrypt($password);
	}
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
		die(json_encode(["error" => "Unable to connect to UnrealIRCd: ".$e->getMessage()]));
}

die(json_encode(["success" => "Successfully connected"]));
