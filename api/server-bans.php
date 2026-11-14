<?php
define('NO_EVENT_STREAM_HEADER',1);
require_once('common_api.php');

if (!$rpc)
    die(json_encode([]));


$tkls = $rpc->serverban()->getAll();

/* Text values are returned raw (not HTML-escaped): the page escapes them
 * for display, and uses the raw values to fill in the edit form.
 * Only 'Select' is HTML, built here with escaped values.
 */
$out = [];
foreach($tkls as $tkl)
{
    $set_in_config = ((isset($tkl->set_in_config) && $tkl->set_in_config) || ($tkl->set_by == "-config-")) ? true : false;
    $select = '';
    if (!$set_in_config)
        $select = "<input type=\"checkbox\" value=\"" . h(base64_encode($tkl->name).",".base64_encode($tkl->type)) . "\" name=\"tklch[]\">";

    $out[] = [
        "Select" => $select,
        "Mask" => $tkl->name,
        "Type" => $tkl->type_string,
        "Duration" => $tkl->duration_string,
        "Reason" => $tkl->reason,
        "Set By" => $set_in_config ? "" : show_nick_only($tkl->set_by),
        "Config" => $set_in_config,
        "Set On" => $tkl->set_at_string,
        "Expires" => $tkl->expire_at_string,
    ];
}

function custom_sort($a,$b)
{
    return strcmp(strtoupper($a["Mask"]), strtoupper($b["Mask"]));
}

usort($out, "custom_sort");

echo json_encode($out, JSON_INVALID_UTF8_SUBSTITUTE);
