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
		}
	}


	public static function add_footer_info($empty)
	{
		if (!($user = unreal_get_current_user())->id)
			return;

		else {
			echo "<code>Admin Panel v" . WEBPANEL_VERSION . "</code>";
		}
	}

	/* pre-Header hook */
	public static function session_start($n)
	{
		if (!isset($_SESSION))
		{
			session_set_cookie_params(3600);
			session_start();
		}
		if (!isset($_SESSION['id']) || empty($_SESSION))
		{
			$secure = ($_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
			$current_url = "$secure$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$tok = split($_SERVER['SCRIPT_FILENAME'], "/");
			if ($check = security_check() && $tok[count($tok) - 1] !== "error.php") {
				header("Location: " . BASE_URL . "plugins/sql_auth/error.php");
				die();
			}
			header("Location: ".BASE_URL."login/?redirect=".urlencode($current_url));
			die();
		}
		else
		{
			if (!unreal_get_current_user()->id) // user no longer exists
			{
				session_destroy();
				header("Location: ".BASE_URL."login");
				die();
			}
			// you'll be automatically logged out after one hour of inactivity
		}
	}

	/**
	 * Create the tables we'll be using in the SQLdb
	 * @return void
	 */
	public static function create_tables()
	{
		$conn = sqlnew();
		$conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "users (
			user_id int AUTO_INCREMENT NOT NULL,
			user_name VARCHAR(255) NOT NULL,
			user_pass VARCHAR(255) NOT NULL,
			user_email VARCHAR(255),
			user_fname VARCHAR(255),
			user_lname VARCHAR(255),
			user_bio VARCHAR(255),
			created VARCHAR(255),
			PRIMARY KEY (user_id)
		)");

		/**
		 * Patch for beta users
		 * This adds the email column to existing tables without it
		*/
		$columns = $conn->query("SHOW COLUMNS FROM " . SQL_PREFIX . "users");
		$column_names = array();
		$c = $columns->fetchAll();

		foreach($c as $column) {
			$column_names[] = $column['Field'];
		}
		$column_exists = in_array("user_email", $column_names);
		if (!$column_exists) {
			$conn->query("ALTER TABLE " . SQL_PREFIX . "users ADD COLUMN user_email varchar(255)");
		}

		/**
		 * Another patch for beta users
		 * This changes the size of the meta_value so we can store more
		 */
		$columns = $conn->query("SHOW COLUMNS FROM ".SQL_PREFIX."user_meta");
		$c = $columns->fetchAll();
		if (!empty($c))
			$conn->query("ALTER TABLE `".SQL_PREFIX."user_meta` CHANGE `meta_value` `meta_value` VARCHAR(5000) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NULL DEFAULT NULL");


		$conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "user_meta (
			meta_id int AUTO_INCREMENT NOT NULL,
			user_id int NOT NULL,
			meta_key VARCHAR(255) NOT NULL,
			meta_value VARCHAR(255),
			PRIMARY KEY (meta_id)
		)");
		$conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "auth_settings (
			id int AUTO_INCREMENT NOT NULL,
			setting_key VARCHAR(255) NOT NULL,
			setting_value VARCHAR(255),
			PRIMARY KEY (id)
		)");
		$conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "fail2ban (
			id int AUTO_INCREMENT NOT NULL,
			ip VARCHAR(255) NOT NULL,
			count VARCHAR(255),
			PRIMARY KEY (id)
		)");
		new AuthSettings();
	}

	/* We convert $u with a full user as an object ;D*/
	public static function get_user(&$u)
	{
		$id = $u['id'];
		$name = $u['name'];
		$conn = sqlnew();

		if ($id)
		{
			$prep = $conn->prepare("SELECT * FROM " . SQL_PREFIX . "users WHERE user_id = :id LIMIT 1");
			$prep->execute(["id" => strtolower($id)]);
		}
		elseif ($name)
		{
			$prep = $conn->prepare("SELECT * FROM " . SQL_PREFIX . "users WHERE LOWER(user_name) = :name LIMIT 1");
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
			$prep = $conn->prepare("SELECT * FROM " . SQL_PREFIX . "user_meta WHERE user_id = :id");
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
		$query = "SELECT * FROM " . SQL_PREFIX . "user_meta WHERE user_id = :id AND meta_key = :key";
		$stmt = $conn->prepare($query);
		$stmt->execute(["id" => $meta['id'], "key" => $meta['key']]);
		if ($stmt->rowCount()) // it exists, update instead of insert
		{
			$query = "UPDATE " . SQL_PREFIX . "user_meta SET meta_value = :value WHERE user_id = :id AND meta_key = :key";
			$stmt = $conn->prepare($query);
			$stmt->execute($meta);
			if ($stmt->rowCount())
				return true;
			return false;
		}

		else
		{
			$query = "INSERT INTO " . SQL_PREFIX . "user_meta (user_id, meta_key, meta_value) VALUES (:id, :key, :value)";
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
		$query = "DELETE FROM " . SQL_PREFIX . "user_meta WHERE user_id = :id AND meta_key = :key";
		$stmt = $conn->prepare($query);
		$stmt->execute($u['meta']);
		if ($stmt->rowCount())
			return true;
		return false;
	}
	public static function user_create(&$u)
	{
		$username = $u['user_name'];
		$first_name = $u['fname'];
		$last_name = $u['lname'];
		$password = $u['user_pass'];
		$user_bio = $u['user_bio'];
		$user_email = $u['user_email'];
		$conn = sqlnew();
		$prep = $conn->prepare("INSERT INTO " . SQL_PREFIX . "users (user_name, user_pass, user_fname, user_lname, user_bio, user_email, created) VALUES (:name, :pass, :fname, :lname, :user_bio, :user_email, :created)");
		$prep->execute(["name" => $username, "pass" => $password, "fname" => $first_name, "lname" => $last_name, "user_bio" => $user_bio, "user_email" => $user_email, "created" => date("Y-m-d H:i:s")]);
		if ($prep->rowCount())
			$u['success'] = true;
		else
			$u['errmsg'][] = "Could not add user";
	}

	public static function get_user_list(&$list)
	{
		$conn = sqlnew();
		$result = $conn->query("SELECT user_id FROM " . SQL_PREFIX . "users");
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
		$query = "DELETE FROM " . SQL_PREFIX . "users WHERE user_id = :id";
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
	$dnsbl_lookup = DNSBL;

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