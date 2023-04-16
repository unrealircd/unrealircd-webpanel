<?php

class file_auth
{
	public $name = "FileAuth";
	public $author = "Syzop and Valware";
	public $version = "1.0";
	public $description = "Provides a User Auth using a simple file backend";
	public $email = "syzop@vulnscan.org";

	function __construct()
	{
		Hook::func(HOOKTYPE_PRE_HEADER, 'file_auth::session_start');
		Hook::func(HOOKTYPE_FOOTER, 'file_auth::add_footer_info');
		Hook::func(HOOKTYPE_USER_LOOKUP, 'file_auth::get_user');
		Hook::func(HOOKTYPE_USERMETA_ADD, 'file_auth::add_usermeta');
		Hook::func(HOOKTYPE_USERMETA_DEL, 'file_auth::del_usermeta');
		Hook::func(HOOKTYPE_USERMETA_GET, 'file_auth::get_usermeta');
		Hook::func(HOOKTYPE_USER_CREATE, 'file_auth::user_create');
		Hook::func(HOOKTYPE_GET_USER_LIST, 'file_auth::get_user_list');
		Hook::func(HOOKTYPE_USER_DELETE, 'file_auth::user_delete');
		Hook::func(HOOKTYPE_EDIT_USER, 'file_auth::edit_core');
		Hook::func(HOOKTYPE_PRE_OVERVIEW_CARD, 'file_auth::add_pre_overview_card');
		AuthModLoaded::$status = 1;

		file_auth::read_db();

		if (defined('DEFAULT_USER')) // we've got a default account
		{
			$lkup = new PanelUser(DEFAULT_USER['username']);

			if (!$lkup->id) // doesn't exist, add it with full privileges
			{
				$user = [];
				$user['user_name'] = DEFAULT_USER['username'];
				$user['user_pass'] = DEFAULT_USER['password'];
				$user['err'] = "";
				create_new_user($user);
			}
			$lkup = new PanelUser(DEFAULT_USER['username']);
			if (!user_can($lkup, PERMISSION_MANAGE_USERS))
				$lkup->add_permission(PERMISSION_MANAGE_USERS);
		}
	}

	// duplicate code
	public static function add_footer_info($empty)
	{
		if (!($user = unreal_get_current_user()))
			return;

		else {
			echo "<code>Admin Panel v" . WEBPANEL_VERSION . "</code>";
		}
	}

	public static function add_pre_overview_card($empty)
	{
		if (defined('DEFAULT_USER'))
			Message::Fail("Warning: DEFAULT_USER is set in config.php. You should remove that item now, as it is only used during installation.");
	}

	/* pre-Header hook */
	// duplicate code
	public static function session_start($n)
	{
		$current_page = $_SERVER['REQUEST_URI'];
		if (!isset($_SESSION))
		{
			session_set_cookie_params(3600);
			session_start();
		}
		if (!isset($_SESSION['id']) || empty($_SESSION))
		{
			header("Location: ".get_config("base_url")."login/?redirect=".urlencode($current_page));
			die();
		}
		else
		{
			if (!unreal_get_current_user()) // user no longer exists
			{
				session_destroy();
				header("Location: ".get_config("base_url")."login");
				die();
			}
			// you'll be automatically logged out after one hour of inactivity
			$_SESSION['last-activity'] = time();

		}
	}

	public static function get_user_helper($item)
	{
		$obj = (object) [];
		$obj->id = $item["id"];
		$obj->username = $item["username"];
		$obj->passhash = $item["password"];
		$obj->first_name = $item["first_name"];
		$obj->last_name = $item["last_name"];
		$obj->created = $item["created"];
		$obj->bio = $item["bio"];
		$obj->email = $item["email"];
		$obj->user_meta = (new PanelUser_Meta($obj->id))->list;
		return $obj;
	}

	public static function uid_to_username($id)
	{
		GLOBAL $db;
		foreach($db["users"] as $user=>$details)
			if ($details["id"] === $id)
				return $details["username"];
		return null;
	}

	/* We convert $u with a full user as an object ;D*/
	public static function get_user(&$u)
	{
		GLOBAL $db;

		$id = $u['id'];
		$name = $u['name'];

		$obj = (object) [];
		if ($id)
		{
			foreach($db["users"] as $user=>$details)
				if ($details["id"] === $id)
					$obj = file_auth::get_user_helper($details);
		}
		if (isset($db["users"][$name]))
		{
			$obj = file_auth::get_user_helper($db["users"][$name]);
		}
		$u['object'] = $obj;
		return $obj;
	}

	public static function get_usermeta(&$u)
	{
		GLOBAL $db;

		$uid = $u['id'];

		$username = file_auth::uid_to_username($uid);
		if (!$username)
			die("User not found: $uid\n"); // return false; /* User does not exist */

		$u['meta'] = $db["users"][$username]['meta'];
	}

	public static function add_usermeta(&$meta)
	{
		GLOBAL $db;

		$meta = $meta['meta'];
		$uid = $meta['id'];
		$key = $meta['key'];
		$value = $meta['value'];

		file_auth::read_db();
		$username = file_auth::uid_to_username($uid);
		if (!$username)
			return false; /* User does not exist */

		/* And update */
		$db["users"][$username]["meta"][$key] = $value;
		file_auth::write_db();
		return true;
	}

	public static function del_usermeta(&$u)
	{
		$uid = $meta['id'];
		$key = $meta['key'];

		file_auth::read_db();
		$username = file_auth::uid_to_username($uid);
		if (!$username)
			return false; /* User does not exist */

		/* And delete */
		unset($db["users"][$username]["meta"][$key]);

		file_auth::write_db();
		return true;
	}

	public static function read_db()
	{
		GLOBAL $db;
		$db_filename = UPATH.'/data/database.php';
		@include($db_filename);
		/* Add at least the general arrays: */
		if (!isset($db["users"]))
			$db["users"] = [];
		/* Initialize more if we ever add more... */
	}

	public static function write_db($force = false)
	{
		GLOBAL $db;
		/* Refuse to write empty db (or nearly empty) */
		if (empty($db) || empty($db["users"]) && !$force)
			return;

		$db_filename = UPATH.'/data/database.php';
		$tmpfile = UPATH.'/data/database.tmp.'.bin2hex(random_bytes(8)).'.php'; // hmm todo optional location? :D
		$fd = fopen($tmpfile, "w");
		if (!$fd)
			die("Could not write to temporary database file $tmpfile.<br>We need write permissions on the data/ directory!<br>");

		$str = var_export($db, true);
		if ($str === null)
			die("Error while running write_db() -- weird!");
		if (!fwrite($fd, "<?php\n".
		            "/* This database file is written automatically by the UnrealIRCd webpanel.\n".
		            " * You are not really supposed to edit it manually.\n".
		            " */\n".
		            '$db = '.var_export($db, true).";\n"))
		{
			die("Error writing to database file $tmpfile (on fwrite).<br>");
		}
		if (!fclose($fd))
			die("Error writing to database file $tmpfile (on close).<br>");
		/* Now atomically rename the file */
		if (!rename($tmpfile, $db_filename))
			die("Could not write (rename) to file ".$db_filename."<br>");
		opcache_invalidate($db_filename);
	}

	public static function user_create(&$u)
	{
		GLOBAL $db;

		$username = $u['user_name'];
		$first_name = $u['fname'] ?? NULL;
		$last_name = $u['lname'] ?? NULL;
		$password = $u['user_pass'] ?? NULL;
		$user_bio = $u['user_bio'] ?? NULL;
		$user_email = $u['user_email'] ?? NULL;
		$created = date("Y-m-d H:i:s");
		$id = random_int(1000000,99999999);

		file_auth::read_db();

		if (isset($db["users"][$username]))
		{
			$u['errmsg'][] = "Could not add user: user already exists";
			return;
		}

		$db["users"][$username] = [
			"id" => $id,
			"username" => $username,
			"first_name" => $first_name,
			"last_name" => $last_name,
			"password" => $password,
			"bio" => $user_bio,
			"email" => $user_email,
			"created" => $created,
			"meta" => [],
			];

		file_auth::write_db();
		$u['success'] = true;
	}

	public static function get_user_list(&$list)
	{
		GLOBAL $db;

		$userlist = [];
		foreach($db["users"] as $user=>$details)
		{
			$userlist[] = new PanelUser(NULL, $details['id']);
		}
		if (!empty($userlist))
			$list = $userlist;
		
	}

	public static function user_delete(&$u)
	{
		GLOBAL $db;

		file_auth::read_db();
		$user = $u['user'];
		$username = $user->username;
		$deleted = false;
		if (isset($db["users"][$username]))
		{
			unset($db["users"][$username]);
			$deleted = true;
		}
		file_auth::write_db(true);

		if ($deleted)
		{
			$u['info'][] = "Successfully deleted user \"$user->username\"";
			$u['boolint'] =  1;
		} else {
			$u['info'][] = "Unknown error";
			$u['boolint'] = 0;
		}
	}

	public static function edit_core($arr)
	{
		GLOBAL $db;

		$user = $arr['user'];
		$username = $user->username;
		$info = $arr['info'];

		file_auth::read_db();

		foreach($info as $key => $val)
		{
			$keyname = NULL;
			if (!$val || !strlen($val) || BadPtr($val))
				continue;
			if (!strcmp($key,"update_fname") && $val != $user->first_name)
			{
				$keyname = "first_name";
				$property_name = "first name";
			}
			elseif (!strcmp($key,"update_lname") && $val != $user->last_name)
			{
				$keyname = "last_name";
				$property_name = "last name";
			}
			elseif (!strcmp($key,"update_bio") && $val != $user->bio)
			{
				$keyname = "bio";
				$property_name = "bio";
			}
			elseif (!strcmp($key,"update_pass") || !strcmp($key,"update_pass_conf"))
			{
				$keyname = "password";
				$property_name = "password";
			}
			elseif(!strcmp($key,"update_email") && $val != $user->email)
			{
				$keyname = "email";
				$property_name = "email address";
			}

			if (!$keyname)
				continue;

			if (isset($db["users"][$username]))
			{
				$db["users"][$username][$keyname] = $val;
				Message::Success("Successfully updated the $property_name for $user->username");
			} else {
				Message::Fail("Could not update $property_name for $user->username: ".$stmt->errorInfo()[0]." (CODE: ".$stmt->errorCode().")");
			}
		}

		file_auth::write_db(true);
	}
}
