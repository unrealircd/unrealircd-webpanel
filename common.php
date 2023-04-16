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
	    str_ends_with($_SERVER['SCRIPT_FILENAME'],"test_connection.php"))
	{
		return TRUE;
	}
	return FALSE;
}


/* Load config defaults */
$config = Array();
require_once UPATH . "/config/config.defaults.php";

if (!file_exists(UPATH."/config/config.php")) 
exit("The ".UPATH . "/config/config.php file does not exist. Please configure the config.php.sample file and rename it to config.php.");
else if (!file_exists(UPATH."/config/config.php") && file_exists(UPATH."/config.php"))
{
	require_once UPATH . "/config.php";
	require_once UPATH . "/config/compat.php";
} else
if (page_requires_no_config())
{
	/* Allow empty conf */
} else
{
	require_once UPATH . "/config/config.php";
}

if (!get_config("base_url")) die("You need to define the base_url in config/config.php");
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
		"Plugins" => "settings/plugins.php",
	],
	
	"News" => "news.php",
];

if (is_auth_provided())
{
	$pages["Settings"]["Panel Access"] = "settings";

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

/**
 * Returns the given text with html tags for colors and styling
 * @param string $text IRC text
 * @return string HTML text
 * This is a code taken here :
 * https://github.com/h9k/magirc/blob/887596259c9ca1980f07df84d415c6d14f477cc7/lib/magirc/Magirc.class.php#L294
 * it had been retouched a little bit
 * 
 */
function irc2html($text) {
	$lines = explode("\n", mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));
	$out = '';
	$colors = array('#FFFFFF', '#000000', '#00007F', '#009300', '#FF0000', '#7F0000', '#9C009C', '#FC7F00', '#FFFF00', '#00FC00', '#009393', '#00FFFF', '#0000FC', '#FF00FF', '#7F7F7F', '#D2D2D2');

	foreach ($lines as $line) {
		$line = nl2br(mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1'));
		// replace control codes
		$line = preg_replace_callback('/\x03(\d{0,2})(,\d{1,2})?([^\x03\x0F]*)(?:\x03(?!\d))?/', function($matches) use ($colors) {
			$options = '';
			$bgcolor = trim(substr($matches[2], 1));

			if ($bgcolor !== '' && (int) $bgcolor < count($colors)) {
				$options .= 'background-color: ' . $colors[(int) $bgcolor] . '; ';
			}

			$forecolor = trim($matches[1]);

			if ($forecolor !== '' && (int) $forecolor < count($colors)) {
				$options .= 'color: ' . $colors[(int) $forecolor] . ';';
			}

			return '<span style="' . $options . '">' . $matches[3] . '</span>';
		}, $line);

		$line = preg_replace('/\x02([^\x02\x0F]*)(?:\x02)?/', '<strong>$1</strong>', $line);
		$line = preg_replace('/\x1F([^\x1F\x0F]*)(?:\x1F)?/', '<span style="text-decoration: underline;">$1</span>', $line);
		$line = preg_replace('/\x12([^\x12\x0F]*)(?:\x12)?/', '<span style="text-decoration: line-through;">$1</span>', $line);
		$line = preg_replace('/\x16([^\x16\x0F]*)(?:\x16)?/', '<span style="font-style: italic;">$1</span>', $line);
		//$line = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\S+]*(\?\S+)?)?)?)@', "<a href='$1' class='topic'>$1</a>", $line);
		// remove dirt
		$line = preg_replace('/[\x00-\x1F]/', '', $line);
		$line = preg_replace('/[\x7F-\xFF]/', '', $line);
		// append line
		if (!empty($line)) {
			$out .= $line;
		}
	}

	return $out;
}