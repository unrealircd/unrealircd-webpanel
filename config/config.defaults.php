<?php

/**
 * The configuration file for your admin panel.
 * 
 */

if (!defined('UPATH'))
	die("Access denied");

/**
 * The base URL, how this panel can be accessed.
 * This would be '/' if you installed in the web root,
 * or something like '/webpanel/' if you go to http://x.y.z/webpanel
 * IMPORTANT: needs a trailing slash!
*/
$config["base_url"] = '/unrealircd-webpanel/';

/**
 *  The RPC User name as defined in your unrealircd.conf
 *  https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Configuring_UnrealIRCd
*/
$config["unrealircd"]["rpc_user"] = 'adminpanel';

/**
 *  The RPC User password as defined in your unrealircd.conf
*/
$config["unrealircd"]["rpc_password"] = 'securepassword';

/** 
 * The host IP or name of your RPC server
*/
$config["unrealircd"]["host"] = '127.0.0.1';

/**
 * The port of your RPC server as defined in your unrealircd.conf
*/
$config["unrealircd"]["port"] = '8600';

/** 
 * You should set this to true, if your RPC server is not on your local host
*/
$config["unrealircd"]["tls_verify_cert"] = false;

/**
 * You should only need this, if you're developing something.
*/
$config["debug"] = false;

/* No plugins loaded by default */

/* No SQL config by default, except for default table prefix: */
$config["mysql"]["table_prefix"] = "unreal_";

// TODO: mailer defaults ?
