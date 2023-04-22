<?php

include "../inc/defines.php";
session_start();
//timeout after 10 mins of inactivity
if (isset($_SESSION["id"]) && isset($_SESSION["last-activity"]) && time() - $_SESSION["last-activity"] < $_SESSION['session_timeout'])
    die(json_encode(['session' => 'active']));
else
{
    session_destroy();
    die(json_encode(['session' => 'none']));
}