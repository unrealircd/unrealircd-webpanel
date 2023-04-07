<?php

session_start();
if (!isset($_SESSION["id"]))
    die("{\"error\": \"Access denied\"}");

include "../common.php";
include "../connection.php";

$stats = $rpc->query("stats.get", []);
echo json_encode($stats);
