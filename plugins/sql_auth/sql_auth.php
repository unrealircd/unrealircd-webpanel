<?php

require_once "SQL/sql.php";
require_once "SQL/settings.php";

class sql_auth
{
	public $name = "SQLAuth";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "Provides a User Auth and Management Panel with an SQL backend";
	public $email = "v.a.pond@outlook.com";

	function __construct()
	{
		self::create_tables();
		Hook::func(HOOKTYPE_PRE_HEADER, 'sql_auth::session_start');
		Hook::func(HOOKTYPE_FOOTER, 'sql_auth::add_footer_info');
		Hook::func(HOOKTYPE_USER_LOOKUP, 'sql_auth::get_user');
		Hook::func(HOOKTYPE_USERMETA_ADD, 'sql_auth::add_usermeta');
		Hook::func(HOOKTYPE_USERMETA_DEL, 'sql_auth::del_usermeta');
		Hook::func(HOOKTYPE_USERMETA_GET, 'sql_auth::get_usermeta');
		Hook::func(HOOKTYPE_USER_CREATE, 'sql_auth::user_create');
		Hook::func(HOOKTYPE_GET_USER_LIST, 'sql_auth::get_user_list');
		Hook::func(HOOKTYPE_USER_DELETE, 'sql_auth::user_delete');
		Hook::func(HOOKTYPE_EDIT_USER, 'sql_auth::edit_core');
		Hook::func(HOOKTYPE_PRE_OVERVIEW_CARD, 'sql_auth::add_pre_overview_card');
		AuthModLoaded::$status = 1;

		if (defined('SQL_DEFAULT_USER')) // we've got a default account
		{
			$lkup = new PanelUser(SQL_DEFAULT_USER['username']);

			if (!$lkup->id) // doesn't exist, add it with full privileges
			{
				$user = [];
				$user['user_name'] = SQL_DEFAULT_USER['username'];
				$user['user_pass'] = SQL_DEFAULT_USER['password'];
				$user['err'] = "";
				create_new_user($user);
			}
			$lkup = new PanelUser(SQL_DEFAULT_USER['username']);
			if (!user_can($lkup, PERMISSION_MANAGE_USERS))
				$lkup->add_permission(PERMISSION_MANAGE_USERS);
		}
	}


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
		if (defined('SQL_DEFAULT_USER'))
			Message::Fail("Warning: SQL_DEFAULT_USER is set in config.php. You should remove that item now, as it is only used during installation.");
	}

	/* pre-Header hook */
	public static function session_start($n)
	{
		$current_page = $_SERVER['REQUEST_URI'];
		if (str_ends_with($current_page,"setup.php"))
			return;

		if (!isset($_SESSION))
		{
			session_set_cookie_params(3600);
			session_start();
		}
		if (!isset($_SESSION['id']) || empty($_SESSION))
		{
			
			$tok = split($_SERVER['SCRIPT_FILENAME'], "/");
			if ($check = security_check() && $tok[count($tok) - 1] !== "error.php") {
				header("Location: " . get_config("base_url") . "plugins/sql_auth/error.php");
				die();
			}
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

	/**
	 * Create the tables we'll be using in the SQLdb
	 * @return void
	 */
	public static function create_tables()
	{
		$script = $_SERVER['SCRIPT_FILENAME'];
		if (str_ends_with($script,"setup.php"))
			return;
		$conn = sqlnew();
		$stmt = $conn->query("SHOW TABLES LIKE '".get_config("mysql::table_prefix")."%'");
		if ($stmt->rowCount() < 4)
		{
			header("Location: ".get_config("base_url")."plugins/sql_auth/setup.php");
			die();
		}
	}

	/* We convert $u with a full user as an object ;D*/
	public static function get_user(&$u)
	{
		$id = $u['id'];
		$name = $u['name'];
		$conn = sqlnew();

		if ($id)
		{
			$prep = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "users WHERE user_id = :id LIMIT 1");
			$prep->execute(["id" => strtolower($id)]);
		}
		elseif ($name)
		{
			$prep = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "users WHERE LOWER(user_name) = :name LIMIT 1");
			$prep->execute(["name" => strtolower($name)]);
		}
		$data = NULL;
		$obj = (object) [];
		if ($prep)
			$data = $prep->fetchAll();
		if (isset($data[0]) && $data = $data[0])
		{
			$obj->id = $data['user_id'];
			$obj->username = $data['user_name'];
			$obj->passhash = $data['user_pass'];
			$obj->first_name = $data['user_fname'] ?? NULL;
			$obj->last_name = $data['user_lname'] ?? NULL;
			$obj->created = $data['created'];
			$obj->bio = $data['user_bio'];
			$obj->email = $data['user_email'];
			$obj->user_meta = (new PanelUser_Meta($obj->id))->list;
		}
		$u['object'] = $obj;
	}

	public static function get_usermeta(&$u)
	{
		$list = &$u['meta'];
		$id = $u['id'];
		$conn = sqlnew();
		if (isset($id))
		{
			$prep = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "user_meta WHERE user_id = :id");
			$prep->execute(["id" => $id]);
		}
		foreach ($prep->fetchAll() as $row)
		{
			$list[$row['meta_key']] = $row['meta_value'];
		}
	}

	public static function add_usermeta(&$meta)
	{
		$meta = $meta['meta'];
		$conn = sqlnew();
		/* check if it exists first, update it if it does */
		$query = "SELECT * FROM " . get_config("mysql::table_prefix") . "user_meta WHERE user_id = :id AND meta_key = :key";
		$stmt = $conn->prepare($query);
		$stmt->execute(["id" => $meta['id'], "key" => $meta['key']]);
		if ($stmt->rowCount()) // it exists, update instead of insert
		{
			$query = "UPDATE " . get_config("mysql::table_prefix") . "user_meta SET meta_value = :value WHERE user_id = :id AND meta_key = :key";
			$stmt = $conn->prepare($query);
			$stmt->execute($meta);
			if ($stmt->rowCount())
				return true;
			return false;
		}

		else
		{
			$query = "INSERT INTO " . get_config("mysql::table_prefix") . "user_meta (user_id, meta_key, meta_value) VALUES (:id, :key, :value)";
			$stmt = $conn->prepare($query);
			$stmt->execute($meta);
			if ($stmt->rowCount())
				return true;
			return false;
		}
	}
	public static function del_usermeta(&$u)
	{
		$conn = sqlnew();
		$query = "DELETE FROM " . get_config("mysql::table_prefix") . "user_meta WHERE user_id = :id AND meta_key = :key";
		$stmt = $conn->prepare($query);
		$stmt->execute($u['meta']);
		if ($stmt->rowCount())
			return true;
		return false;
	}
	public static function user_create(&$u)
	{
		$username = $u['user_name'];
		$first_name = $u['fname'] ?? NULL;
		$last_name = $u['lname'] ?? NULL;
		$password = $u['user_pass'] ?? NULL;
		$user_bio = $u['user_bio'] ?? NULL;
		$user_email = $u['user_email'] ?? NULL;
		$conn = sqlnew();
		$prep = $conn->prepare("INSERT INTO " . get_config("mysql::table_prefix") . "users (user_name, user_pass, user_fname, user_lname, user_bio, user_email, created) VALUES (:name, :pass, :fname, :lname, :user_bio, :user_email, :created)");
		$prep->execute(["name" => $username, "pass" => $password, "fname" => $first_name, "lname" => $last_name, "user_bio" => $user_bio, "user_email" => $user_email, "created" => date("Y-m-d H:i:s")]);
		if ($prep->rowCount())
			$u['success'] = true;
		else
			$u['errmsg'][] = "Could not add user";
	}

	public static function get_user_list(&$list)
	{
		$conn = sqlnew();
		$result = $conn->query("SELECT user_id FROM " . get_config("mysql::table_prefix") . "users");
		if (!$result) // impossible
		{
			die("Something went wrong.");
		}
		$userlist = [];
		while($row =  $result->fetch())
		{
			$userlist[] = new PanelUser(NULL, $row['user_id']);
		}
		if (!empty($userlist))
			$list = $userlist;
		
	}
	public static function user_delete(&$u)
	{
		$user = $u['user'];
		$query = "DELETE FROM " . get_config("mysql::table_prefix") . "users WHERE user_id = :id";
		$conn = sqlnew();
		$stmt = $conn->prepare($query);
		$stmt->execute(["id" => $user->id]);
		$deleted = $stmt->rowCount();
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
		$conn = sqlnew();
		$user = $arr['user'];
		$info = $arr['info'];
		foreach($info as $key => $val)
		{
			$value = NULL;
			if (!$val || !strlen($val) || BadPtr($val))
				continue;
			if (!strcmp($key,"update_fname") && $val != $user->first_name)
			{
				$value = "user_fname";
				$valuestr = "first name";
			}
			elseif (!strcmp($key,"update_lname") && $val != $user->last_name)
			{
				$value = "user_lname";
				$valuestr = "last name";
			}
			elseif (!strcmp($key,"update_bio") && $val != $user->bio)
			{
				$value = "user_bio";
				$valuestr = "bio";
			}
			elseif (!strcmp($key,"update_pass") || !strcmp($key,"update_pass_conf"))
			{
				$value = "user_pass";
				$valuestr = "password";
			}
			elseif(!strcmp($key,"update_email") && $val != $user->email)
			{
				$value = "user_email";
				$valuestr = "email address";
			}
			
			if (!$value)
				continue;
			$query = "UPDATE " . get_config("mysql::table_prefix") . "users SET $value=:value WHERE user_id = :id";
			$stmt = $conn->prepare($query);
			$stmt->execute(["value" => $val, "id" => $user->id]);

			if (!$stmt->rowCount() && $stmt->errorInfo()[0] != "00000")
				Message::Fail("Could not update $valuestr for $user->username: ".$stmt->errorInfo()[0]." (CODE: ".$stmt->errorCode().")");

			else
				Message::Success("Successfully updated the $valuestr for $user->username");
		}
	}
}


function security_check()
{
	$ip = $_SERVER['REMOTE_ADDR'];
	if (dnsbl_check($ip))
		return true;

	else if (fail2ban_check($ip))
	{

	}
}

function dnsbl_check($ip)
{
	$dnsbl_lookup = config_get("dnsbl");
	if (!$dnsbl_lookup)
		return;

	// clear variable just in case
	$listed = NULL;

	// if the IP was not given because you're an idiot, stop processing
	if (!$ip) { return; }
	
	// get the first two segments of the IPv4	
	$because = split($ip, ".");   // why you
	$you = $because[1]; 		  // gotta play
	$want = $because[2];		 // that song
	$to = $you.".".$want.".";	// so loud?
	
	// exempt local connections because sometimes they get a false positive
	if ($to == "192.168." || $to == "127.0.") { return NULL; }
	
	// you spin my IP right round, right round, to check the records baby, right round-round-round
	$reverse_ip = glue(array_reverse(split($ip, ".")), ".");
	
	// checkem
	foreach ($dnsbl_lookup as $host) {
		
		//if it was listed
		if (checkdnsrr($reverse_ip . "." . $host . ".", "A")) {
			
			//take note
			$listed = $host;
		}
	}

	// if it was safe, return NOTHING
	if (!$listed) {
		return NULL;
	}
	
	// else, you guessed it, return where it was listed
	else {
		return $listed;
	}
}

function fail2ban_check($ip)
{

}