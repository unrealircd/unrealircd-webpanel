<?php

/** For handling installation BEFORE any backend and user is configured */

require_once "../inc/common.php";

/* only let this happen pre-config */
if (file_exists("../config/config.php"))
		die(json_encode(["error" => "Configuration file exists."]));

if (!isset($_POST) || empty($_POST))
		die(json_encode(["error" => "Incorrect parameters"]));
foreach($_POST as $key => $str)
		${$key} = $str;

if ($method == "sql")
{
		$conn = mysqli_connect($host, $user, $password, $database);

		// check connection
		if (mysqli_connect_errno())
				die(json_encode(["error" => "Failed to connect to MySQL: " . mysqli_connect_error()]));

		// close connection
		mysqli_close($conn);
		die(json_encode(["success" => "SQL Connection successful"]));

}
