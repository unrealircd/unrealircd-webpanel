<?php

require_once "../common.php";

/* only let this happen pre-config */
if (file_exists("../config/config.php"))
		die(json_encode(["error" => "Configuration file exists."]));
$method;

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
elseif ($method == "sql")
{
		$conn = mysqli_connect($host, $user, $password, $database);

		// check connection
		if (mysqli_connect_errno())
				die(json_encode(["error" => "Failed to connect to MySQL: " . mysqli_connect_error()]));

		// close connection
		mysqli_close($conn);
		die(json_encode(["success" => "SQL Connection successful"]));

}