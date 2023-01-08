<?php
define('UPATH', dirname(__FILE__));
require_once "config.php";
require_once UPATH . '/vendor/autoload.php';
require_once "connection.php";
require_once "Classes/class-log.php";
require_once "Classes/class-message.php";
require_once "Classes/class-rpc.php";

$pages = Array("Overview"	=> "overview.php",
               "Users"		=> "users.php",
               "Channels"	=> "channels.php",
               "Server Bans"	=> "tkl.php",
               "Spamfilter"	=> "spamfilter.php",
               "News"		=> "news.php");
