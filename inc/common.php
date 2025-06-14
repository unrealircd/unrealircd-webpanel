<?php
require_once "languages.php";
if (ini_get('output_buffering') == true) {
	ini_set('output_buffering', 'off');
}
if (ini_get('zlib.output_compression') == true) {
	ini_set('zlib.output_compression', 'off');
}

function check_requirements()
{
	if (version_compare(PHP_VERSION, '8.0.0', '<'))
	{
	die(sprintf(__('requirements_php_version'), PHP_VERSION));

	}

	$loaded_extensions = get_loaded_extensions();
	$required_extensions = ["mbstring", "sodium"];
	$missing_extensions = [];
	foreach ($required_extensions as $mod)
		if (!in_array($mod, $loaded_extensions))
			$missing_extensions[] = $mod;

	if (count($missing_extensions) > 0)
	{
		$text = "<html>" . __('requirements_extensions_missing_title') . "<br>\n<ul>\n";
		$cmd = 'apt-get install';
		foreach($missing_extensions as $mod)
		{
			$text .= "<li>$mod</li>\n";
			$cmd .= " php-$mod";
		}
		$text .= "</ul>\n";
		$text .= sprintf(__('requirements_extensions_missing_cmd'), $cmd);
		die($text);
	}
}

check_requirements(); /* very early !! */

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
		die(sprintf(__('config_write_error'), htmlspecialchars($tmpfile)));

	$str = var_export($file_settings, true);
	if ($str === null)
		die(__('requirements_write_config_weird'));
	if (!fwrite($fd, "<?php\n".
			"/* " . __('requirements_config_file_notice_1') . "\n".
			" * " . __('requirements_config_file_notice_2') . "\n".
			" */\n".
			'$config = '.$str.";\n"))
	{
		die(sprintf(__('requirements_write_config_fwrite'), htmlspecialchars($tmpfile)));
	}
	if (!fclose($fd))
		 die(sprintf(__('requirements_write_config_fclose'), htmlspecialchars($tmpfile)));
	/* Now atomically rename the file */
	if (!rename($tmpfile, $cfg_filename))
		die(sprintf(__('requirements_write_config_rename_error'), htmlspecialchars($cfg_filename)));
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
	 *	   as it is more noisy than db settings.
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
    die(__('die_vendor'));
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
require_once UPATH . "/Classes/class-notes.php";
require_once UPATH . "/Classes/class-plugins.php";
require_once UPATH . "/Classes/class-upgrade.php";

/* Do various checks and reading, except during setup step 1. */
if (!page_requires_no_config())
{
	/* Now that plugins are loaded, read config from DB */
	read_config_db();

	/* Check if anything needs upgrading (eg on panel version change) */
	upgrade_check();

	/* And a check... */
	if (!get_config("base_url"))
		die(__('other_base_url'));
}

$pages = [
	__('menu_overview') => ["script" => ""],
	__('menu_users') => ["script"=>"users/index.php"],
	__('menu_channels') => ["script"=>"channels/index.php"],
	__('menu_servers') => ["script"=>"servers/index.php"],
	__('a_menu_servers_bans')  => [
		__('menu_server_ban') => ["script" => "server-bans/index.php"],
		__('menu_name_bans') => ["script" => "server-bans/name-bans.php"],
		__('menu_ban_exceptions') => ["script" => "server-bans/ban-exceptions.php"],
	],
	__('menu_spamfilter') => ["script" => "server-bans/spamfilter.php"],
	__('menu_logs')   => ["script" => "logs/index.php"],
	__('a_menu_tools') => [
		__('menu_ip_whois') => ["script" => "tools/ip-whois.php","no_irc_server_required"=>true],
	],
	__('a_menu_settings') => [
		__('menu_general_settings') => ["script" => "settings/general.php"],
		__('menu_rpc_servers') => ["script" => "settings/rpc-servers.php","no_irc_server_required"=>true],
	],
];


if (!panel_start_session())
{
	if (!page_requires_no_login())
	{
		if (!is_auth_provided())
			die(__('other_auth_provided'));
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
	if (current_user_can(PERMISSION_MANAGE_PLUGINS))
	{
		$pages["Settings"]["Plugins"] = ["script" => "settings/plugins.php"];
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


global $rightClickMenu;
$rightClickMenu = [
	[
		"text" => "Copy",
		"onclick" => "copy_to_clipboard(window.getSelection().toString())",
		"icon" => "fa-clipboard"
	],
	[
		"text" => "Paste",
		"onclick" => "paste_from_clipboard()",
		"icon" => "fa-paint-brush",
	],
];

// register our menu
Hook::run(HOOKTYPE_RIGHTCLICK_MENU, $rightClickMenu);
