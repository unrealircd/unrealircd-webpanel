<?php

define('NO_EVENT_STREAM_HEADER',1);
require_once('common_api.php');
header("Content-type: application/json; charset=utf-8");
$tkls = $rpc->serverban()->getAll();

$out = [];
foreach($tkls as $tkl)
{
    $set_in_config = ((isset($tkl->set_in_config) && $tkl->set_in_config) || ($tkl->set_by == "-config-")) ? true : false;
    $set_by = $set_in_config ? "<span class=\"badge rounded-pill badge-secondary\">Config</span>" : show_nick_only(htmlspecialchars($tkl->set_by));
    $select = '';
    if (!$set_in_config)
        $select = "<input type=\"checkbox\" value='" . base64_encode($tkl->name).",".base64_encode($tkl->type) . "' name=\"tklch[]\">";

    $out[] = [
        "Select" => $select,
        "Mask" => htmlspecialchars($tkl->name),
        "Type" => $tkl->type_string,
        "Duration" => $tkl->duration_string,
        "Reason" => htmlspecialchars($tkl->reason),
        "Set By" => $set_by,
        "Set On" => $tkl->set_at_string,
        "Expires" => $tkl->expire_at_string,
    ];
}

function custom_sort($a,$b)
{
    return strcmp(strtoupper($a["Mask"]), strtoupper($b["Mask"]));
}

usort($out, "custom_sort");

echo json_encode($out);
