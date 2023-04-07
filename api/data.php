<?php

session_start();
if (!isset($_SESSION['id']))
    die("Access denied");

$cpuUsage = sys_getloadavg();

function convert($size)
 {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
 }

$memUsage = memory_get_usage(true);
$data = array(
    "cpu" => $cpuUsage[0],
    "memory" => convert($memUsage),
);

header('Content-Type: application/json');
echo json_encode($data);
?>