<?php

/** For handling installation BEFORE any backend and user is configured */

define('PANEL_PAGE_INSTALLER', 1);
require_once "../inc/common.php";

header("Content-type: application/json; charset=utf-8");

/* only let this happen pre-config (also refuses with only the old root config.php) */
if (panel_is_configured())
		die(json_encode(["error" => "Configuration file exists."]));

/* A request without a valid CSRF token arrives here with an empty $_POST */
if ($panel_csrf_failed)
		die(json_encode(["error" => "The setup page has expired, please reload it."]));

if (!isset($_POST) || empty($_POST))
		die(json_encode(["error" => "Incorrect parameters"]));

foreach (["method", "host", "user", "password", "database", "table_prefix"] as $k)
	if (!isset($_POST[$k]) || !is_string($_POST[$k]))
		die(json_encode(["error" => "Incorrect parameters"]));

if ($_POST['method'] == "sql")
{
	$conn = false;
	try {
		$conn = mysqli_connect($_POST['host'], $_POST['user'], $_POST['password'], $_POST['database']);
	} catch(Exception $e)
	{
	}
	// check connection
	if (!$conn || mysqli_connect_errno())
			die(json_encode(["error" => "Failed to connect to MySQL: " . mysqli_connect_error()], JSON_INVALID_UTF8_SUBSTITUTE));

	$sql = "SHOW TABLES LIKE '".$conn->real_escape_string($_POST['table_prefix'])."%'"; // SQL query to check if table exists
	$result = $conn->query($sql);
	if ($result->num_rows > 0)
		die(json_encode(["warn" => "Database already has data"]));

	// close connection
	mysqli_close($conn);
	die(json_encode(["success" => "SQL Connection successful"]));

}
