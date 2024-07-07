<?php
require_once "class-message.php";


/** Check for plugins and load them.
 * 
 * This expects your plugin folder to be located in `plugins/` and that the directory name,
 * constructor file name and class name are identical.
 * For example:
 * You must have a file structure like this: plugins/myplugin/myplugin.php
 * Which contains a class like this:
 * ```
 * class myplugin {
 *	  $name = "My plugin";
 *	  $author = "Joe Bloggs";
 *	  $version "1.0";
 *	  $desc = "This is my plugin and it does stuff";
 *	  
 *	  // rest of code here...
 * }
 * ```
 * Your plugin class must be constructable and contain the following public variables:
 * $name	The name or title of your plugin.
 * $author  The name of the author
 * $version The version of the plugin
 * $description	A short description of the plugin
*/
class Plugins
{
	static $list = [];

	static function load($modname)
	{
		$plugin = new Plugin($modname);
		if ($plugin->error)
		{
			Message::Fail("Warning: Plugin \"$modname\" failed to load: $plugin->error");
		}
		else
		{
			self::$list[] = $plugin;
		}
	}
	static function plugin_exists($name, $version = NULL)
	{
		foreach(self::$list as $p)
			if (!strcmp($p->name,$name) && (!$version || ($version >= $p->version)))
				return true;

		return false;
	}

}

class Plugin
{
	public $name;
	public $author;
	public $version;
	public $description;
	public $handle;
	public $email;

	public $error = NULL;
	function __construct($handle)
	{
		if (!is_dir(UPATH."/plugins/$handle"))
			$this->error = "Plugin directory \"".UPATH."/plugins/$handle\" doesn't exist";

		else if (!is_file(UPATH."/plugins/$handle/$handle.php"))
			$this->error = "Plugin file \"".UPATH."/plugins/$handle/$handle.php\" doesn't exist";

		else
		{
			require_once UPATH."/plugins/$handle/$handle.php";

			if (!class_exists($handle))
				$this->error = "Class \"$handle\" doesn't exist";

			else
			{
				$plugin = new $handle();
			
				if (!isset($plugin->name))
					$this->error = "Plugin name not defined";
				elseif (!isset($plugin->author))
					$this->error = "Plugin author not defined";
				elseif (!isset($plugin->version))
					$this->error = "Plugin version not defined";
				elseif (!isset($plugin->description))
					$this->error = "Plugin description not defined";
				elseif (!isset($plugin->email))
					$this->error = "Plugin email not defined";
				else
				{
					$this->handle = $handle;
					$this->name = $plugin->name;
					$this->author = $plugin->author;
					$this->version = $plugin->version;
					$this->description = $plugin->description;
					$this->email = $plugin->email;
				}
			}
		}
	}
}

if (get_config("plugins"))
{
	foreach(get_config("plugins") as $plugin)
		Plugins::load($plugin);
}

/* Requires the plugin */
function require_plugin($name, $version)
{
	if (!Plugins::plugin_exists($name,$version))
		die("Missing plugin: $name v$version");
}

/* I'm not a fan of globals */
class AuthModLoaded
{
	public static $status = 0;
}

function is_auth_provided()
{
	return AuthModLoaded::$status;
}
