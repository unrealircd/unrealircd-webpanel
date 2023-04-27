<?php

require_once('common_api.php');

if (!$rpc)
    die();

/* Basically everything ;) */
api_log_loop(["all", "!debug"]);
