<?php

require_once('common_api.php');

if (!$rpc)
    die();

if (!$_GET || !isset($_GET['search']))
{
    echo json_encode(["error" => "No search query"]);
    die;
}
$search_term = $_GET['search'];
$users = $rpc->user()->getAll();
$chans = $rpc->channel()->getAll(2);
$logs = $rpc->log()->getAll();
$servers = $rpc->server()->getAll();
$server_bans = $rpc->serverban()->getAll();
$excepts = $rpc->serverbanexception()->getAll();
$spamfilter = $rpc->spamfilter()->getAll();
$name_bans = $rpc->nameban()->getAll();

$search_results = [
    "users" => [],
    "channels" => [],
    "channel_bans" => [],
    "channel_invites" => [],
    "channel_excepts" => [],
    "logs" => [],
    "server_bans" => [],
    "excepts" => [],
    "spamfilter" => [],
    "name_bans" => []
];

function strcasestr($haystack, $needle) : bool
{
    $needle = strtolower($needle);
    $haystack = strtolower($haystack);
    $needle = preg_quote($needle, '/');
    $needle = str_replace('\*', '.*', $needle);
    $pattern = '/.*' . $needle . '.*' . '/';

    return preg_match($pattern, $haystack) === 1;
}
foreach ($users as $u)
{
    if (strcasestr($u->name,$search_term))
    {
        $o = (object)[];    
        $o->name = $u->name;
        $o->data = $u->name;
        $o->label = "nick";
        $search_results['users'][] = $o;
    }
    if (strcasestr($u->details,$search_term))
    {
        $o = (object)[];
        $o->name = $u->name;
        $o->data = $u->details;
        $o->label = "userhost";
        $search_results['users'][] = $o;
    }
    if (strcasestr($u->user->realname,$search_term))
    {
        $o = (object)[];
        $o->name = $u->name;
        $o->data = $u->user->realname;
        $o->label = "GECOS";
        $search_results['users'][] = $o;
    }
    if (@strcasestr($u->user->account,$search_term))
    {
        $o = (object)[];
        $o->name = $u->name;
        $o->data = $u->account;
        $o->label = "account";
        $search_results['users'][] = $o;
    }
    if (isset($u->geoip))
    {
        error_log("It's set");
        if (@strcasestr($u->geoip->asn,$search_term))
        {
            $o = (object)[];
            $o->name = $u->name;
            $o->data = $u->geoip->asn;
            $o->label = "ASN";
            $search_results['users'][] = $o;
        }
        if (@strcasestr($u->geoip->asname,$search_term))
        {
            $o = (object)[];
            $o->name = $u->name;
            $o->data = $u->geoip->asname;
            $o->label = "ASN Name";
            $search_results['users'][] = $o;
        }
        if (@strcasestr($u->geoip->country_code,$search_term))
        {
            $o = (object)[];
            $o->name = $u->name;
            $o->data = $u->geoip->country_code;
            $o->label = "Country Code";
            $search_results['users'][] = $o;
        }
    }
    else{
        error_log(json_encode($u));
    }
}
foreach ($chans as $c)
{
    if (strcasestr($c->name,$search_term))
    {
        $c->label = "channel name";
        $search_results['channels'][] = $c;
    }
    if (isset($c->topic) && strcasestr($c->topic,$search_term))
    {
        $c->label = "channel topic";
        $search_results['channels'][] = $c;
    }
    if (isset($c->bans))
    {
        foreach ($c->bans as $i)
        {
            if (!strcasestr($i->name, $search_term))
                continue;

            $new = (object)[];
            $new->name = $c->name;
            $new->topic = $i->name;
            $new->label = "ban (+b)";
            $search_results['channels'][] = $new;
            error_log("$new->label for $i->name");
        }
    }
    if (isset($c->ban_exemptions))
    {
        foreach ($c->ban_exemptions as $i)
        {
            if (!strcasestr($i->name, $search_term))
                continue;

            $new = (object)[];
            $new->name = $c->name;
            $new->topic = $i->name;
            $new->label = "exempt (+e)";
            $search_results['channels'][] = $new;
            error_log("$new->label for $i->name");
        }
    }
    if (isset($c->invite_exceptions))
    {
        foreach ($c->invite_exceptions as $i)
        {
            if (!strcasestr($i->name, $search_term))
                continue;

            $new = (object)[];
            $new->name = $c->name;
            $new->topic = $i->name;
            $new->label = "invite (+I)";
            $search_results['channels'][] = $new;
        }
    }
}
foreach ($logs as $l)
    if (strcasestr($l->msg,$search_term))
        $search_results['logs'][] = $l;

foreach ($servers as $s)
    if (strcasestr($s->name, $search_term))
        $search_results['servers'][] = $s;

foreach ($server_bans as $ban)
{
    
    if (strstr($ban->type,$search_term) || strstr($ban->type_string,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['server_bans'][] = $o;
    }
    elseif (strstr($ban->name,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = "<code>$ban->name</code>";
        $o->data = $ban->reason;
        $search_results['server_bans'][] = $o;
    }

    elseif (strcasestr($ban->reason,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string." reason";
        $o->name = "<code>$ban->name</code>";
        $o->data = $ban->reason;
        $search_results['server_bans'][] = $o;
    }
}

foreach ($excepts as $ban)
{
    if (strstr($ban->type,$search_term) || strstr($ban->type_string,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['excepts'][] = $o;
    }
    elseif (strstr($ban->name,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = "<code>$ban->name</code>";
        $o->data = $ban->reason;
        $search_results['excepts'][] = $o;
    }
    elseif (strcasestr($ban->reason,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string." reason";
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['excepts'][] = $o;
    }
}


foreach ($name_bans as $ban)
{
    if (strstr($ban->type,$search_term) || strstr($ban->type_string,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['name_bans'][] = $o;
    }
    elseif (strstr($ban->name,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = "<code>$ban->name</code>";
        $o->data = $ban->reason;
        $search_results['name_bans'][] = $o;
    }
    elseif (strcasestr($ban->reason,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string." reason";
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['name_bans'][] = $o;
    }
}

foreach ($spamfilter as $ban)
{
    if (strstr($ban->type,$search_term) || strstr($ban->type_string,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['spamfilter'][] = $o;
    }
    elseif (strstr($ban->name,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string;
        $o->name = "<code>$ban->name</code>";
        $o->data = $ban->reason;
        $search_results['spamfilter'][] = $o;
    }
    elseif (strcasestr($ban->reason,$search_term))
    {
        $o = (object)[];
        $o->label = $ban->type_string." reason";
        $o->name = $ban->name;
        $o->data = $ban->reason;
        $search_results['spamfilter'][] = $o;
    }
}



echo json_encode($search_results);