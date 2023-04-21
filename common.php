<?php
if (version_compare(PHP_VERSION, '8.0.0', '<'))
	die("This webserver is using PHP version ".PHP_VERSION." but we require at least PHP 8.0.0.<br>".
	    "If you already installed PHP8 but are still seeing this error, then it means ".
	    "apache/nginx/.. is loading an older PHP version. Eg. on Debian/Ubuntu you need ".
	    "<code>apt-get install libapache2-mod-php8.2</code> (or a similar version) and ".
	    "<code>apt-get remove libapache2-mod-php7.4</code> (or a similar version). ".
	    "You may also need to choose again the PHP module to load in apache via <code>a2enmod php8.2</code>");

define('UPATH', dirname(__FILE__));

function get_config($setting)
{
	GLOBAL $config;

	$item = $config;
	foreach(explode("::", $setting) as $x)
	{
		if (isset($item[$x]))
			$item = $item[$x];
		else
			return NULL;
	}
	return $item;
}

function page_requires_no_config()
{
	if (str_ends_with($_SERVER['SCRIPT_FILENAME'],"install.php") ||
	    str_ends_with($_SERVER['SCRIPT_FILENAME'],"installation.php"))
	{
		return TRUE;
	}
	return FALSE;
}

function page_requires_no_login()
{
	if (str_ends_with($_SERVER['SCRIPT_FILENAME'],"login/index.php") ||
	    page_requires_no_config())
	{
		return TRUE;
	}
	return FALSE;
}

function read_config_file()
{
	GLOBAL $config;
	GLOBAL $config_transition_unreal_server;

	$config = Array();
	if (!file_exists(UPATH."/config/config.php") && file_exists(UPATH."/config.php"))
	{
		require_once UPATH . "/config.php";
		require_once UPATH . "/config/compat.php";
	}
	if ((@include(UPATH . "/config/config.php")) !== 1)
		return false;
	if (isset($config['unrealircd']))
		$config_transition_unreal_server = true;
	return true;
}

function read_config_db()
{
	GLOBAL $config;

	if (page_requires_no_config())
		return;

	$merge = DbSettings::get();
	/* DB settings overwrite config.php keys: */
	$config = array_merge($config, $merge);
}

function config_is_file_item($name)
{
	if (($name == "plugins") ||
	    ($name == "mysql") ||
	    ($name == "base_url"))
	{
		return true;
	}
	return false;
}

function write_config_file()
{
	GLOBAL $config;

	$file_settings = [];
	foreach($config as $k=>$v)
	{
		if (config_is_file_item($k))
			$file_settings[$k] = $v;
	}

	$cfg_filename = UPATH.'/config/config.php';
	$tmpfile = UPATH.'/config/config.tmp.'.bin2hex(random_bytes(8)).'.php';
	$fd = fopen($tmpfile, "w");
	if (!$fd)
		die("Could not write to temporary config file $tmpfile.<br>We need write permissions on the config/ directory!<br>");

	$str = var_export($file_settings, true);
	if ($str === null)
		die("Error while running write_config_file() -- weird!");
	if (!fwrite($fd, "<?php\n".
		    "/* This config file is written automatically by the UnrealIRCd webpanel.\n".
		    " * You are not really supposed to edit it manually.\n".
		    " */\n".
		    '$config = '.$str.";\n"))
	{
		die("Error writing to config file $tmpfile (on fwrite).<br>");
	}
	if (!fclose($fd))
		die("Error writing to config file $tmpfile (on close).<br>");
	/* Now atomically rename the file */
	if (!rename($tmpfile, $cfg_filename))
		die("Could not write (rename) to file ".$cfg_filename."<br>");
	opcache_invalidate($cfg_filename);

	/* Do not re-read config, as it would reinitialize config
	 * without having the DB settings read. (And it also
	 * serves no purpose)
	 */
	return true;
}

// XXX: handle unsetting of config items :D - explicit unset function ?

function write_config($setting = null)
{
	GLOBAL $config;

	/* Specific request? Easy, write only this setting to the DB (never used for file) */
	if ($setting !== null)
	{
		return DbSettings::set($setting, $config[$setting]);
	}

	/* Otherwise write the whole config.
	 * TODO: avoid writing settings file if unneeded,
	 *       as it is more noisy than db settings.
	 */
	$db_settings = [];

	foreach($config as $k=>$v)
	{
		if (!config_is_file_item($k))
			$db_settings[$k] = $v;
	}

	if (!write_config_file())
		return false;

	foreach($db_settings as $k=>$v)
	{
		$ret = DbSettings::set($k, $v);
		if (!$ret)
			return $ret;
	}

	return true;
}

function get_version()
{
	$fd = @fopen(UPATH."/.git/FETCH_HEAD", "r");
	if ($fd === false)
		return "unknown";
	$line = fgets($fd, 512);
	fclose($fd);
	$commit = substr($line, 0, 8);
	return $commit; /* short git commit id */
}

function upgrade_check()
{
	GLOBAL $config_transition_unreal_server;

	/* Moving of a config.php item to DB: */
	if ($config_transition_unreal_server)
		write_config();

	$version = get_version();
	if (!isset($config['webpanel_version']))
		$config['webpanel_version'] = '';
	if ($version != $config['webpanel_version'])
	{
		$versioninfo = [
			"old_version" => $config['webpanel_version'],
			"new_version" => $version
			];
		Hook::run(HOOKTYPE_UPGRADE, $versioninfo);
		/* And set the new version now that the upgrade is "done" */
		$config['webpanel_version'] = $version;
		write_config("webpanel_version");
	}
}

function panel_start_session($user = false)
{
	if (!isset($_SESSION))
	{
		session_set_cookie_params(86400); // can't set this to session_timeout due to catch-22
		session_start();
	}

	if ($user === false)
	{
		$user = unreal_get_current_user();
		if ($user === false)
			return false;
	}

	$timeout = 3600;
	if (isset($user->user_meta['session_timeout']))
		$timeout = (INT)$user->user_meta['session_timeout'];

	if (!isset($_SESSION['session_timeout']))
		$_SESSION['session_timeout'] = $timeout;

	$_SESSION['last-activity'] = time();
	return true;
}

/* Now read the config, and redirect to install screen if we don't have it */
$config_transition_unreal_server = false;
if (!read_config_file())
{
	if (page_requires_no_config())
	{
		/* Allow empty conf */
	} else
	if (!file_exists(UPATH."/config/config.php") && !file_exists(UPATH."/config.php"))
	{
		header("Location: settings/install.php");
		die();
	}
}

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

/* Do various checks and reading, except during setup step 1. */
if (!page_requires_no_config())
{
	/* Now that plugins are loaded, read config from DB */
	read_config_db();

	/* Check if anything needs upgrading (eg on panel version change) */
	upgrade_check();

	/* And a check... */
	if (!get_config("base_url"))
		die("The base_url was not found in your config. Setup went wrong?");
}

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
		"Plugins" => "settings/plugins.php",
	],
	
	"News" => "news.php",
];

if (!panel_start_session())
{
	if (!page_requires_no_login())
	{
		$current_page = $_SERVER['REQUEST_URI'];
		header("Location: ".get_config("base_url")."login/?redirect=".urlencode($current_page));
		die;
	}
}
if (is_auth_provided())
{
	$pages["Settings"]["Accounts"] = "settings";

	$user = unreal_get_current_user();
	if ($user)
	{
		/* Add logout page, if logged in */
		$pages["Logout"] = "login/?logout=true";
	}
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