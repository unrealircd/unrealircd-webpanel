<?php
if (version_compare(PHP_VERSION, '8.0.0', '<'))
{
	die("This webserver is using PHP version ".PHP_VERSION." but we require at least PHP 8.0.0.<br>".
	    "If you already installed PHP8 but are still seeing this error, then it means ".
	    "apache/nginx/.. is loading an older PHP version. Eg. on Debian/Ubuntu you need ".
	    "<code>apt-get install libapache2-mod-php8.2</code> (or a similar version) and ".
	    "<code>apt-get remove libapache2-mod-php7.4</code> (or a similar version). ".
	    "You may also need to choose again the PHP module to load in apache via <code>a2enmod php8.2</code>");
}

$loaded_extensions = get_loaded_extensions();
if (!in_array("mbstring", $loaded_extensions))
{
	die("The PHP module 'mbstrings' need to be loaded. ".
	    "You need to install the php-mbstring package and restart the webserver.<br>".
	    "If you are on Debian/Ubuntu then run <code>apt-get install php-mbstring</code> ".
	    "and restart your webserver (apache2/nginx/..).");
}

define('UPATH', dirname(__DIR__));

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

function get_current_page_helper($name, $p, &$title)
{
	if (isset($p["script"]))
	{
		if (($p["script"] != '') && str_ends_with($_SERVER['SCRIPT_FILENAME'],$p["script"]))
		{
			// MATCH
			if (isset($p["title"]))
				$title = $p["title"];
			else
				$title = $name;
			return $p;
		}
		return null;
	}
	foreach ($p as $k=>$v)
	{
		$ret = get_current_page_helper($k, $v, $title);
		if ($ret !== null)
			return $ret;
	}
	return null;
}

/** Get current page and title */
function get_current_page(&$title)
{
	GLOBAL $pages;
	foreach ($pages as $k=>$v)
	{
		$ret = get_current_page_helper($k, $v, $title);
		if ($ret !== null)
			return $ret;
	}
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
	/* Upgrade needed? */
	$plugins_modified = false;
	foreach ($config["plugins"] as $k=>$v)
	{
		if ($v == "sql_auth")
		{
			$config["plugins"][$k] = "sql_db";
			$plugins_modified = true;
		} else
		if ($v == "file_auth")
		{
			$config["plugins"][$k] = "file_db";
			$plugins_modified = true;
		}
	}
	if ($plugins_modified)
		write_config_file();

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
	    ($name == "base_url") ||
	    ($name == "secrets"))
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
	if (function_exists('opcache_invalidate'))
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

function generate_secrets()
{
	GLOBAL $config;

	if (!isset($config['secrets']))
		$config['secrets'] = Array();

	if (!isset($config['secrets']['pepper']))
		$config['secrets']['pepper'] = rtrim(base64_encode(random_bytes(16)),'=');

	if (!isset($config['secrets']['key']))
		$config['secrets']['key'] = rtrim(base64_encode(sodium_crypto_aead_xchacha20poly1305_ietf_keygen()),'=');
}

function get_active_rpc_server()
{
	$servers = get_config("unrealircd");
	if (empty($servers))
		return;
	// TODO: make user able to override this - either in user or in session

	foreach ($servers as $displayname=>$e)
	{
		if (isset($e["default"]) && $e["default"])
			return $displayname;
	}
	return null;
}

/* Set a new default RPC server */
function set_default_rpc_server($name)
{
	GLOBAL $config;

	/* Mark all other servers as non-default */
	foreach ($config["unrealircd"] as $n=>$e)
		if ($n != $name)
			$config["unrealircd"][$n]["default"] = false;
	$config["unrealircd"][$name]["default"] = true;
}

/* Ensure at least 1 server is default */
function set_at_least_one_default_rpc_server()
{
	GLOBAL $config;

	$has_default_rpc_server = false;
	foreach ($config["unrealircd"] as $name=>$e)
		if ($e["default"])
			$has_default_rpc_server = true;
	if (!$has_default_rpc_server)
	{
		/* Make first server in the list the default */
		foreach ($config["unrealircd"] as $name=>$e)
		{
			$config["unrealircd"][$name]["default"] = true;
			break;
		}
	}
}

function secret_encrypt(string $text)
{
	GLOBAL $config;

	$key = base64_decode($config['secrets']['key']);
	$nonce = \random_bytes(\SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
	$encrypted_text = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($text, '', $nonce, $key);
	return "secret:".rtrim(base64_encode($nonce),'=').':'.rtrim(base64_encode($encrypted_text),'='); // secret:base64(NONCE):base64(ENCRYPTEDTEXT)
}

function secret_decrypt(string $crypted)
{
	GLOBAL $config;

	$key = base64_decode($config['secrets']['key']);
	$d = explode(":", $crypted);
	if (count($d) != 3)
		return null;
	$nonce = base64_decode($d[1]);
	$ciphertext = base64_decode($d[2]);

	$ret = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($ciphertext, '', $nonce, $key);
	if ($ret === false)
		return null;
	return $ret;
}

function upgrade_check()
{
	GLOBAL $config_transition_unreal_server;
	GLOBAL $config;

	/* Moving of a config.php item to DB: */
	if ($config_transition_unreal_server)
		write_config();

	/* Our own stuff may need upgrading.. */
	/* - generating secrets */
	if (!isset($config['secrets']))
	{
		generate_secrets();
		write_config_file();
	}
	/* - encrypting rpc_password */
	if (isset($config['unrealircd']) &&
	    isset($config['unrealircd']['rpc_password']) &&
	    !str_starts_with($config['unrealircd']['rpc_password'], "secret:"))
	{
		$ret = secret_encrypt($config['unrealircd']['rpc_password']);
		if ($ret !== false)
		{
			$config['unrealircd']['rpc_password'] = $ret;
			write_config('unrealircd');
		}
	}
	/* $config["unrealircd"] should be an array now.. */
	if (isset($config['unrealircd']) && isset($config['unrealircd']['rpc_password']))
	{
		$config["unrealircd"]["default"] = true;
		$config['unrealircd'] = [
			"Primary" => $config['unrealircd']];
		write_config("unrealircd");
	}

	$version = get_version();
	if (!isset($config['webpanel_version']))
		$config['webpanel_version'] = '';
	if ($version != $config['webpanel_version'])
	{
		$versioninfo = [
			"old_version" => $config['webpanel_version'],
			"new_version" => $version
			];
		/* And inform the hook (eg the database backends) */
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

require_once UPATH . "/Classes/class-hook.php";
if (!is_dir(UPATH . "/vendor"))
	die("The vendor/ directory is missing. Most likely the admin forgot to run 'composer install'\n");
require_once UPATH . '/vendor/autoload.php';
require_once UPATH . "/Classes/class-cmodes.php";
require_once UPATH . "/inc/defines.php";
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
	"Overview"     => ["script"=>""],
	"Users"        => ["script"=>"users/index.php"],
	"Channels"     => ["script"=>"channels/index.php"],
	"Servers"      => ["script"=>"servers/index.php"],
	"Server Bans"  => [
		"Server Bans" => ["script" => "server-bans/index.php"],
		"Name Bans" => ["script" => "server-bans/name-bans.php"],
		"Ban Exceptions" => ["script" => "server-bans/ban-exceptions.php"],
	],
	"Spamfilter"   => ["script" => "spamfilter.php"],
	"Logs"   => ["script" => "logs/index.php"],
	"Tools" => [
		"IP WHOIS" => ["script" => "tools/ip-whois.php","no_irc_server_required"=>true],
	],
	"Settings" => [
		"Plugins" => ["script" => "settings/plugins.php","no_irc_server_required"=>true],
		"RPC Servers" => ["script" => "settings/rpc-servers.php","no_irc_server_required"=>true],
	],
	
	"News" => ["script" => "news.php","no_irc_server_required"=>true],
];

if (!panel_start_session())
{
	if (!page_requires_no_login())
	{
		if (!is_auth_provided())
			die("No authentication plugin loaded. You must load either sql_db, file_db, or a similar auth plugin!");
		$current_page = $_SERVER['REQUEST_URI'];
		header("Location: ".get_config("base_url")."login/?redirect=".urlencode($current_page));
		die;
	}
} else {
	$pages["Settings"]["Accounts"] = [
		"script" => "settings/index.php",
		"no_irc_server_required"=>true
	];
	if (current_user_can(PERMISSION_MANAGE_USERS))
	{
		$pages["Settings"]["Role Editor"] = [
			"script"=>"settings/user-role-edit.php",
			"no_irc_server_required"=>true
		];
	}
	$user = unreal_get_current_user();
	if ($user)
	{
		/* Add logout page, if logged in */
		$pages["Logout"] = [
			"script"=>"login/?logout=true",
			"no_irc_server_required"=>true
		];
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

$current_page = get_current_page($current_page_name);
