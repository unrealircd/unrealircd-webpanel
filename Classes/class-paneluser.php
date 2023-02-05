<?php

define('PERMISSION_MANAGE_USERS', 'manage_users'); /** Relating to Panel Access: Can add, delete and edit users. Big boss. */
define('PERMISSION_BAN_USERS', 'ban_users'); /** Relating to Users tab: Can ban users connected to IRC */
define('PERMISSION_EDIT_USER', 'edit_user'); /** Change properties of a user, i.e. vhost, modes and more */
define('PERMISSION_EDIT_CHANNEL', 'edit_channel'); /** Change properties of a channel, i.e. topic, modes and more */
define('PERMISSION_EDIT_CHANNEL_USER', 'edit_channel_user'); /** Change properties of a user on a channel i.e give/remove voice or ops and more */
define('PERMISSION_SERVER_BAN_ADD', 'tkl_add'); /** Can add manual bans, including G-Lines, Z-Lines and more */
define('PERMISSION_SERVER_BAN_DEL', 'tkl_del'); /** Can remove set bans, including G-Lines, Z-Lines and more */
define('PERMISSION_NAME_BAN_ADD', 'nb_add'); /** Can add Name Bans (Q-Lines) */
define('PERMISSION_NAME_BAN_DEL', 'nb_del'); /** Can delete Name Bans (Q-Lines) */
define('PERMISSION_BAN_EXCEPTION_ADD', 'be_add'); /** Can add ban exceptions (E-Lines) */
define('PERMISSION_BAN_EXCEPTION_DEL', 'be_del'); /** Can delete ban exceptions (E-Lines) */
define('PERMISSION_SPAMFILTER_ADD', 'sf_add'); /** Can add spamfilter entries */
define('PERMISSION_SPAMFILTER_DEL', 'sf_del'); /** Can delete spamfilter entries */
/**
 * PanelUser
 * This is the User class for the SQL_Auth plugin
 */
class PanelUser
{
	public $id = NULL;
	public $username = NULL;
	private $passhash = NULL;
	public $first_name = NULL;
	public $last_name = NULL;
	public $created = NULL;
	public $user_meta = [];
	public $bio = NULL;
	public $email = NULL;

	/**
	 * Find a user in the database by name or ID
	 * @param string $name
	 * @param mixed $id
	 */
	function __construct(string $name = NULL, int $id = NULL)
	{
		$user["name"] = $name;
		$user["id"] = $id;
		$user["object"] = NULL;

		Hook::run(HOOKTYPE_USER_LOOKUP, $user);
		foreach ($user['object'] as $key => $value)
			$this->$key = $value;
	}

	/**
	 * Verify a user's password
	 * @param string $input
	 * @return bool
	 */
	function password_verify(string $input) : bool
	{
		if (password_verify($input, $this->passhash))
			return true;
		return false;
	}

	/**
	 * Add user meta data
	 * @param string $key
	 * @param string $value
	 */
	function add_meta(string $key, string $value)
	{
		
		if (!$key || !$value)
			return false;

		$meta = [
			"id" => $this->id,
			"key" => $key,
			"value" => $value
		];

		$array['meta'] = $meta;
		$array['user'] = $this;
		Hook::run(HOOKTYPE_USERMETA_ADD, $array);
		
	}

	/**
	 * Delete user meta data by key
	 * @param string $key
	 */
	function delete_meta(string $key)
	{
		if (!$key )
			return false;

		$meta = [
			"id" => $this->id,
			"key" => $key,
		];
		Hook::run(HOOKTYPE_USERMETA_DEL, $meta);

	}

	/** PERMISSIONS */

	function add_permission($permission)
	{
		$meta = (isset($this->user_meta['permissions'])) ? unserialize($this->user_meta['permissions']) : [];
		if (!in_array($permission,$meta))
			$meta[] = $permission;
		$this->add_meta("permissions", serialize($meta)); // updet de dettabess
		$this->user_meta['permissions'] = serialize($meta); // put it back in our object in case it still needs to be used
	}
	function delete_permission($permission)
	{
		$meta = (isset($this->user_meta['permissions'])) ? unserialize($this->user_meta['permissions']) : [];
		foreach($meta as $key => $value)
		{
			if (!strcmp($permission, $value))
				unset($meta[$key]);
		}
		$this->add_meta("permissions", serialize($meta));
		$this->user_meta['permissions'] = serialize($meta);
	}

}


/**
 * This class looks up and returns any user meta.
 * This is used by PanelUser, so you won't need to 
 * call it separately from PanelUser.
 */
class PanelUser_Meta
{
	public $list = [];
	function __construct($id)
	{
		$array = [];
		$arr["id"] = $id;
		$arr['meta'] = &$array;
		Hook::run(HOOKTYPE_USERMETA_GET, $arr);
		do_log($array);
		$this->list = $arr['meta'];
		
	}
}

/**
 * Array of user
 * 
 * Required:
 * user_name
 * user_pass
 * 
 * Optional:
 * user_fname
 * user_lname
 * 
 * @param array $user
 * @throws Exception
 * @return bool
 */
function create_new_user(array $user) : bool
{
	if (!isset($user['user_name']) || !isset($user['user_pass']))
		throw new Exception("Attempted to add user without specifying user_name or user_pass");

	$username = $user['user_name'];
	$password = password_hash($user['user_pass'], PASSWORD_ARGON2ID);
	$first_name = (isset($user['fname'])) ? $user['fname'] : NULL;
	$last_name = (isset($user['lname'])) ? $user['lname'] : NULL;
	$user_bio = (isset($user['user_bio'])) ? $user['user_bio'] : NULL;
	

	$conn = sqlnew();
	$prep = $conn->prepare("INSERT INTO " . SQL_PREFIX . "users (user_name, user_pass, user_fname, user_lname, user_bio, created) VALUES (:name, :pass, :fname, :lname, :user_bio, :created)");
	$prep->execute(["name" => $username, "pass" => $password, "fname" => $first_name, "lname" => $last_name, "user_bio" => $user_bio, "created" => date("Y-m-d H:i:s")]);
	
	return true;
}

/**
 * Gets the user object for the current session
 * @return PanelUser|bool
 */
function unreal_get_current_user() : PanelUser|bool
{
	if (!isset($_SESSION))
	{
		session_set_cookie_params(3600);
		session_start();
	}
	if (isset($_SESSION['id']))
	{
		$user = new PanelUser(NULL, $_SESSION['id']);
		if ($user->id)
			return $user;
	}
	return false;
}

/**
 * Checks if a user can do something
 * @param string $permission
 * @return bool
 */
function current_user_can($permission) : bool
{
	$user = unreal_get_current_user();
	do_log($user);
	if (!$user)
		return false;
	do_log($user);
	if (isset($user->user_meta['permissions']))
	{
		$perms = unserialize($user->user_meta['permissions']);
		if (in_array($permission, $perms))
		{
			return true;
		}
	}
	return false;
}

/**
 * Delete a user and related meta
 * @param int $id The ID of the user in the SQL database.
 * @param array $info This will fill with a response.
 * @return int
 * 
 * Return values:
 *  1   The user was successfully deleted.
 *  0   The user was not found
 *  -1  The admin does not have permission to delete users [TODO]
 */
function delete_user(int $id, &$info = []) : int
{
	$user = new PanelUser(NULL, $id);
	if (!$user->id) {
		$info[] = "Could not find user";
		return 0;
	}
	$query = "DELETE FROM " . SQL_PREFIX . "users WHERE user_id = :id";
	$conn = sqlnew();
	$stmt = $conn->prepare($query);
	$stmt->execute(["id" => $user->id]);
	$deleted = $stmt->rowCount();
	if ($user->id)
	{
		$info[] = "Successfully deleted user \"$user->username\"";
		return 1;
	}
	$info[] = "Unknown error";
	return 0;
}

