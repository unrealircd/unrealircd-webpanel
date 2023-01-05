<?php

/**
 * The configuration file for your admin panel.
 * 
 */


 if (!defined('UPATH'))
    die("Access denied");


/**
 *  The RPC User name as defined in your unrealircd.conf
*/
define( 'UNREALIRCD_RPC_USER', 'apiuser' );

/**
 *  The RPC User password as defined in your unrealircd.conf
*/
define( 'UNREALIRCD_RPC_PASSWORD', 'securepassword' );

/** 
 * The host IP or name of your RPC server
*/
define( 'UNREALIRCD_HOST', '127.0.0.1' );

/**
 * The port of your RPC server as defined in your unrealircd.conf
*/
define( 'UNREALIRCD_PORT', '8000' );

/** 
 * You should set this to true if your RPC server is not on our local host
*/
define( 'UNREALIRCD_SSL_VERIFY', false );

/**
 * You should only need this if you're developing something.
*/
define( 'UNREALIRCD_DEBUG', true );