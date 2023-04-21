<?php

/* Hook Definitions
* 
* Hooks let you do things in your plugin, like add nav items for your
* own pages, add extra cards to the overview and more (to come)
*/
/** HOOKTYPE_NAVBAR
 * 
 * @param array $pages
 * Receives an array of pages. For example:
 * $pages = ["Overview" => ""];
 * 
 * So when you call this hook, you must refer to the
 * parameter by reference. For example:
 * Hook::func(HOOKTYPE_NAVBAR, 'add_navbar_item');
 * 
 * function add_navbar_item(&$pages) // remember the & to use by reference
 * { insert_hacks_here(); }
 */
define('HOOKTYPE_NAVBAR', 100); 

/** HOOKTYPE_PRE_HEADER
 * 
 * This doesn't receive anything, however you must still specify an
 * parameter for your hook function, because it's referring to memory. Sorry =]
 * 
 * Currently this is only used by the "sql_auth" plugin by Valware in order to
 * redirect users immediately to the login page.
 * 
 * Putting HTML in this hook is not a good idea.
 */
define('HOOKTYPE_PRE_HEADER', 101);

/** HOOKTYPE_PRE_OVERVIEW_CARD
 * 
 * @param object $stats
 * 
 * This is called before the initial cards have loaded in the overview.
 * This lets you add your own HTML or whatever you like on the overview,
 * new cards, whatever.
 * 
 * The parameter is an object containing stats used in the overview.
 * See "index.php" to see how it's used.
 * 
 */
define('HOOKTYPE_PRE_OVERVIEW_CARD', 102);

/** HOOKTYPE_OVERVIEW_CARD
 * 
 * @param object $stats
 * 
 * This is called after the initial cards have loaded in the overview.
 * This lets you add your own HTML or whatever you like on the overview,
 * new cards, whatever.
 * 
 * The parameter is an object containing stats used in the overview.
 * See "index.php" to see how it's used.
 * 
*/
define('HOOKTYPE_OVERVIEW_CARD', 103);

/** HOOKTYPE_NOTIFICATION
 * 
 * @param array $notification
 * The array should contain:
 *
 * "name" - The name of the recipient
 * "message" - The notification message
 *
 * This does not do anything special by itself. It simply allows plugins
 * to be able to use it with regards to notification sending.
 * This is not run at any place, but should be run from your plugin.
 *
 * This hook is simple in design and only contains two elements in attempt
 * to make it work cross-plugin. That is, if you have implemented your own
 * notificiation system, you will be able to do whatever you like such as
 * display a navbar list of notifications or send important emails by running
 * this hook.
 * 
*/
define('HOOKTYPE_NOTIFICATION', 104);


/** HOOKTYPE_PRE_FOOTER
 * @param array $empty - Doesn't do anything
 * 
 * This runs inside the footer body before anything else.
 */
define('HOOKTYPE_PRE_FOOTER', 105);

/** HOOKTYPE_FOOTER
 * @param array $empty - Doesn't do anything
 * 
 * This runs inside the footer body after everything else.
 */
define('HOOKTYPE_FOOTER', 106);

/** HOOKTYPE_USER_LOOKUP
 * @param array $user [name, id]
 */
define('HOOKTYPE_USER_LOOKUP', 107);

/** HOOKTYPE_USERMETA_ADD
 * @param array $meta [[id, key, value], (object)PanelUser]
 */
define('HOOKTYPE_USERMETA_ADD', 108);

/** HOOKTYPE_USERMETA_ADD
 * @param array $meta [id, key, value]
 */
define('HOOKTYPE_USERMETA_DEL', 109);

/** HOOKTYPE_USERMETA_ADD
 * @param array $meta [id, key, value]
 */
define('HOOKTYPE_USERMETA_GET', 110);

/** HOOKTYPE_USER_CREATE
 * @param array $userinfo []
 */
define('HOOKTYPE_USER_CREATE', 111);

define('HOOKTYPE_GET_USER_LIST', 112);

define('HOOKTYPE_USER_DELETE', 113);

define('HOOKTYPE_USER_LOGIN', 114);

define('HOOKTYPE_USER_LOGIN_FAIL', 115);

define('HOOKTYPE_USER_PERMISSION_LIST', 116);

define('HOOKTYPE_EDIT_USER', 117);

define('HOOKTYPE_USER_ROLE_LIST', 118);

define('HOOKTYPE_EDIT_ROLE', 119);

define('HOOKTYPE_AUTH_MOD', 200);

/** An upgrade has been detected.
 * @param array	$versioninfo[]
 * Array contains: "old_version" and "new_version"
 */
define('HOOKTYPE_UPGRADE', 201);

/** 
 *  Class for "Hook"
 * This is the main function which gets called whenever you want to use a Hook.
 * 
 * Example:
 * Calling the Hook using a function:
 * Hook::func(HOOKTYPE_NAVBAR, 'bob');
 * 
 * This Hook references the function 'bob', and will run this
 * function bob
 * {
 * 	echo "We rehashed!";
 * }
 * 
 * Example 2:
 * Calling the Hook using an initialized object class method:
 * Hook::func(HOOKTYPE_NAVBAR, [$this, 'method']);
 * 
 * Example 3:
 * Calling the Hook using a static class method:
 * Hook::func(HOOKTYPE_NAVBAR, 'classname::method');
 * 
 */
class Hook {

	/** A static list of Hooks and their associated functions */
	private static $actions = [];

	/** Runs a Hook.
	 * The parameter for $Hook should be a "HOOKTYPE_" as defined in hook.php
	 * @param string $Hook The define or string name of the Hook. For example, HOOKTYPE_REHASH.
	 * @param array &$args The array of information you are sending along in the Hook, so that other functions may see and modify things.
	 * @return void Does not return anything.
	 * 
	 */
	public static function run($Hook, &$args = array())
	{
		if (!empty(self::$actions[$Hook]))
			foreach (self::$actions[$Hook] as &$f)
				$f($args);
			
	}

	/** Calls a Hook
	 * @param string $Hook The define or string name of the Hook. For example, HOOKTYPE_REHASH.
	 * @param string|Closure $function This is a string reference to a Closure function or a class method.
	 * @return void Does not return anything.
	 */
	public static function func($Hook, $function)
	{
		self::$actions[$Hook][] = $function;
	}

	/** Deletes a Hook
	 * @param string $Hook The Hook from which we are removing a function reference.
	 * @param string $function The name of the function that we are removing.
	 * @return void Does not reuturn anything.
	 */
	public static function del($Hook, $function)
	{
		for ($i = 0; isset(self::$actions[$Hook][$i]); $i++)
			if (self::$actions[$Hook][$i] == $function)
				array_splice(self::$actions[$Hook],$i);
	}
}
