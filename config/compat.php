<?php

/* Compatibility layer for old config -> new config */

/* Base url */
if (defined('BASE_URL'))
	$config["base_url"] = BASE_URL;

/* UnrealIRCd settings */
if (defined('UNREALIRCD_RPC_USER'))
	$config["unrealircd"]["rpc_user"] = UNREALIRCD_RPC_USER;
if (defined('UNREALIRCD_RPC_PASSWORD'))
	$config["unrealircd"]["rpc_password"] = UNREALIRCD_RPC_PASSWORD;
if (defined('UNREALIRCD_HOST'))
	$config["unrealircd"]["host"] = UNREALIRCD_HOST;
if (defined('UNREALIRCD_PORT'))
	$config["unrealircd"]["port"] = UNREALIRCD_PORT;
if (defined('UNREALIRCD_SSL_VERIFY'))
	$config["unrealircd"]["tls_verify_cert"] = UNREALIRCD_SSL_VERIFY;

/* Debug */
if (defined('UNREALIRCD_DEBUG'))
	$config["debug"] = UNREALIRCD_DEBUG;

/* Plugins */
if (defined('PLUGINS'))
	$config["plugins"] = PLUGINS;

/* SQL settings */
if (defined('SQL_IP'))
	$config["mysql"]["host"] = SQL_IP;
if (defined('SQL_DATABASE'))
	$config["mysql"]["database"] = SQL_DATABASE;
if (defined('SQL_USERNAME'))
	$config["mysql"]["username"] = SQL_USERNAME;
if (defined('SQL_PASSWORD'))
	$config["mysql"]["password"] = SQL_PASSWORD;
if (defined('SQL_PREFIX'))
	$config["mysql"]["table_prefix"] = SQL_PREFIX;

// TODO: blacklist thingy and email thingy
if (defined('DNSBL'))
	$config["dnsbl"] = DNSBL;
