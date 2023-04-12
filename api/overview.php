<?php

session_start();
if (!isset($_SESSION["id"]))
    die("{\"error\": \"Access denied\"}");

include "../common.php";

// Close the session now, otherwise other pages block too long
session_write_close();

include "../connection.php";

header("Content-type: application/json");

$stats = $rpc->query("stats.get", []);
echo json_encode($stats);
