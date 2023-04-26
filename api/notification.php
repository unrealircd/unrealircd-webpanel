<?php

require_once('common_api.php');

if (!$rpc)
    die();

api_log_loop(["!debug", "warn", "error", "link"]);
