<?php
if (version_compare(PHP_VERSION, '8.0.0', '<'))
	die("This webserver is using PHP version ".PHP_VERSION." but we require at least PHP 8.0.0.<br>".
	    "If you already installed PHP8 but are still seeing this error, then it means ".
	    "apache/nginx/.. is loading an older PHP version. Eg. on Debian/Ubuntu you need ".
	    "<code>apt-get install libapache2-mod-php8.2</code> (or a similar version) and ".
	    "<code>apt-get remove libapache2-mod-php7.4</code> (or a similar version). ".
	    "You may also need to choose again the PHP module to load in apache via <code>a2enmod php8.2</code>");

define('UPATH', dirname(__FILE__));
require_once UPATH . "/config.php";
if (!defined('BASE_URL')) die("You need to define BASE_URL in config.php (see config.php.sample for documentation)");
require_once "Classes/class-hook.php";
if (!is_dir(UPATH . "/vendor"))
	die("The vendor/ directory is missing. Most likely the admin forgot to run 'composer install'\n");
require_once UPATH . '/vendor/autoload.php';
require_once UPATH . "/Classes/class-cmodes.php";
require_once UPATH . "/cfg/defines.php";
require_once UPATH . "/misc/strings.php";
require_once UPATH . "/misc/channel-lookup-misc.php";
require_once UPATH . "/misc/user-lookup-misc.php";
require_once UPATH . "/misc/server-lookup-misc.php";
require_once UPATH . "/misc/ip-whois-misc.php";
require_once UPATH . "/Classes/class-log.php";
require_once UPATH . "/Classes/class-message.php";
require_once UPATH . "/Classes/class-rpc.php";
require_once UPATH . "/Classes/class-paneluser.php";
require_once UPATH . "/plugins.php";

$pages = [
	"Overview"     => "",
	"Users"        => "users",
	"Channels"     => "channels",
	"Servers"      => "servers",
	"Server Bans"  => [
		"Server Bans" => "server-bans",
		"Name Bans" => "server-bans/name-bans.php",
		"Ban Exceptions" => "server-bans/ban-exceptions.php"
	],
	"Spamfilter"   => "spamfilter.php",
	"Tools" => [
		"IP WHOIS" => "tools/ip-whois.php",
	],
	"Settings" => [
		"Panel Access" => "settings",
		"Plugins" => "settings/plugins.php",
	],
	
	"News"         => "news.php",
];
$user = unreal_get_current_user();
if ($user)
{
	/* Add logout page, if logged in */
	$pages["Logout"] = "login/?logout=true";
}

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