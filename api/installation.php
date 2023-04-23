<?php

/** For handling installation BEFORE any backend and user is configured */

require_once "../inc/common.php";

/* only let this happen pre-config */
if (file_exists("../config/config.php"))
		die(json_encode(["error" => "Configuration file exists."]));

if (!isset($_POST) || empty($_POST))
		die(json_encode(["error" => "Incorrect parameters"]));

if ($_POST['method'] == "sql")
{
		$conn = mysqli_connect($_POST['host'], $_POST['user'], $_POST['password'], $_POST['database']);

		// check connection
		if (mysqli_connect_errno())
				die(json_encode(["error" => "Failed to connect to MySQL: " . mysqli_connect_error()]));

		$sql = "SHOW TABLES LIKE '".$conn->real_escape_string($_POST['table_prefix'])."%'"; // SQL query to check if table exists
		$result = $conn->query($sql);
		if ($result->num_rows > 0)
			die(json_encode(["warn" => "Database already has data"]));

		// close connection
		mysqli_close($conn);
		die(json_encode(["success" => "SQL Connection successful"]));

}
