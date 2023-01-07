<?php

if (!defined('UPATH'))
        die("Access denied");

if (!defined('UNREALIRCD_RPC_USER') ||
        !defined('UNREALIRCD_RPC_PASSWORD') ||
        !defined('UNREALIRCD_HOST') ||
        !defined('UNREALIRCD_PORT')
) die("Unable to find RPC credentials in your config.php");

$tls_verify = (defined('UNREALIRCD_SSL_VERIFY')) ? UNREALIRCD_SSL_VERIFY : true;
$api_login = UNREALIRCD_RPC_USER.":".UNREALIRCD_RPC_PASSWORD;

/* Connect now */
try {
        $rpc = new UnrealIRCd\Connection("wss://".UNREALIRCD_HOST.":".UNREALIRCD_PORT,
                                         $api_login,
                                         Array("tls_verify"=>$tls_verify));
} catch (Exception $e) {
        die("Unable to connect to UnreaIRCd<br>");
}
