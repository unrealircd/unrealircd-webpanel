<?php

require_once "../inc/session.php";
header('Content-Type: application/json');

panel_session_begin();
if (panel_session_uid() === NULL)
    die(json_encode(["error" => "Access denied"]));
session_write_close();

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