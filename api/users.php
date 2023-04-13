<?php

require_once "../common.php";
require_once "../connection.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id']))
    die("Access denied");

if (!isset($_GET) || empty($_GET))
{
    if ($list = $rpc->user()->getAll())
        echo json_encode($list);
    else
        echo json_encode(["error" => "No users found"]);
    die();
}
elseif (isset($_GET['lookup']))
{
    if ($user = $rpc->user()->get($_GET['lookup']))
        echo json_encode($user);
    else
        echo json_encode(["error" => "User not found"]);
    die();
}

else // we're filtering
{
    if (!($list = $rpc->user()->getAll()))
    {
        echo json_encode(["error" => "No users found"]);
        die();
    }

    $return_list = [];
    
    if (isset($_GET['nick']) && !empty($_GET['nick']) && $nick = strtolower($_GET['nick']))
    {
        foreach ($list as $user)
        {
            if (strstr(strtolower($user->name), $nick))
                $return_list[] = $user;
        }
    }
    if (isset($_GET['hostname']) && !empty($_GET['hostname']) && $nick = strtolower($_GET['hostname']))
    {
        foreach ($list as $user)
        {
            if (strstr(strtolower($user->name), $nick))
                $return_list[] = $user;
        }
    }
    echo json_encode($return_list);
    
}