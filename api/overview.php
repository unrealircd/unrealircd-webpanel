<?php
require_once('common_api.php');

if (!$rpc)
    die();

api_timer_loop(1000, "stats.get");
