<?php
define('UPATH', dirname(__FILE__));
require_once "config.php";
if (!defined('BASE_URL')) die("You need to define BASE_URL in config.php (see config.php.sample for documentation)");
require_once "Classes/class-hook.php";
if (!is_dir(UPATH . "/vendor"))
	die("The vendor/ directory is missing. Most likely the admin forgot to run 'composer install'\n");
require_once UPATH . '/vendor/autoload.php';
require_once "connection.php";
require_once "misc/strings.php";
require_once "misc/user-lookup-misc.php";
require_once "Classes/class-log.php";
require_once "Classes/class-message.php";
require_once "Classes/class-rpc.php";
require_once "plugins.php";

$pages = Array("Overview"	=> "index.php",
			   "Users"		=> "users/index.php",
			   "Channels"	=> "channels.php",
			   "Server Bans"	=> "tkl.php",
			   "Spamfilter"	=> "spamfilter.php",
			   "News"		=> "news.php");


Hook::run(HOOKTYPE_NAVBAR, $pages);

/* Example to add new menu item:
 * 
 * class MyPlugin
 * {
 * 
 *	  function __construct()
 *	  {
 *		  Hook::func(HOOKTYPE_NAVBAR, [$this, 'add_menu'])
 *	  }
 * 
 *	  function add_menu(&$pages) // this should pass by reference (using the & prefix)
 *	  {
 *		  $page_name = "My New Page";
 *		  $page_link = "link/to/page.php";
 *		  $pages[$page_name] = $page_link;
 *	  }
 * }
*/