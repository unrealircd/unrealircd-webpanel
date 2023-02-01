<?php

require_once "../../../common.php";

do_log($rpc->server()->get("736"));

echo "<br><br>";

do_log($rpc->server()->module_list("736"));