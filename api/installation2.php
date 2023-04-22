<?php

require_once "../inc/common.php";

/* only let this happen  */
if (isset($config['unrealircd']) && empty($config['unrealircd']['host']))
		die(json_encode(["error" => "Already configured."]));

if (!isset($_GET) || empty($_GET))
		die(json_encode(["error" => "Incorrect parameters"]));

foreach($_GET as $key => $str)
		${$key} = $str;

if ($method == "rpc")
{
		if (isset($tls_verify))
		{
				if ($tls_verify == "false")
						$tls_verify = false;
				elseif ($tls_verify == "true")
						$tls_verify = true;
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
}
