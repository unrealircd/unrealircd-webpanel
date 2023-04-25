<?php
/** Relating to Panel Access: Can add, delete and edit users. Big boss. */
define('PERMISSION_MANAGE_USERS', 'manage_users'); 
/** Relating to Users tab: Can ban users connected to IRC */
define('PERMISSION_BAN_USERS', 'ban_users');
/** Change properties of a user, i.e. vhost, modes and more */
define('PERMISSION_EDIT_USER', 'edit_user');
/** Change properties of a channel, i.e. topic, modes and more */
define('PERMISSION_EDIT_CHANNEL', 'edit_channel'); 
/** Change properties of a user on a channel i.e give/remove voice or ops and more */
define('PERMISSION_EDIT_CHANNEL_USER', 'edit_channel_user'); 
/** Can add manual bans, including G-Lines, Z-Lines and more */
define('PERMISSION_SERVER_BAN_ADD', 'tkl_add'); 
/** Can remove set bans, including G-Lines, Z-Lines and more */
define('PERMISSION_SERVER_BAN_DEL', 'tkl_del');
/** Can add Name Bans (Q-Lines) */
define('PERMISSION_NAME_BAN_ADD', 'nb_add');
/** Can delete Name Bans (Q-Lines) */
define('PERMISSION_NAME_BAN_DEL', 'nb_del'); 
/** Can add ban exceptions (E-Lines) */
define('PERMISSION_BAN_EXCEPTION_ADD', 'be_add'); 
/** Can delete ban exceptions (E-Lines) */
define('PERMISSION_BAN_EXCEPTION_DEL', 'be_del'); 
/** Can add spamfilter entries */
define('PERMISSION_SPAMFILTER_ADD', 'sf_add'); 
/** Can delete spamfilter entries */
define('PERMISSION_SPAMFILTER_DEL', 'sf_del'); 
/** Can rehash servers */
define('PERMISSION_REHASH', 'rhs');
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
		if ($user['object'] === null)
			return; /* no auth module loaded? */
		foreach ($user['object'] as $key => $value)
			$this->$key = $value;
	}

	/**
	 * Verify a user's password
	 * @param string $input
	 * @return bool
	 */
	function password_verify(string $password, bool &$hash_needs_updating = false) : bool
	{
		GLOBAL $config;
		$hash_needs_updating = false;

		if (str_starts_with($this->passhash, "peppered:"))
		{
			/* Argon2 with pepper */
			$password = hash_hmac("sha256", $password, $config['secrets']['pepper']);
			if (password_verify($password, substr($this->passhash,9)))
				return true;
		} else {
			/* Old standard argon2 */
			if (password_verify($password, $this->passhash))
			{
				$hash_needs_updating = true;
				return true;
			}
		}
		return false;
	}

	/**
	 * Generate hash of user's password
	 * @param string $password
	 * @return string
	 */
	public static function password_hash(string $password) : string
	{
		GLOBAL $config;
		$input = hash_hmac("sha256", $password, $config['secrets']['pepper']);
		return "peppered:".password_hash($input, PASSWORD_ARGON2ID);
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

	/** Updates core user info.
	 * CAUTION: Updating a non-existent column will crash
	 * your shit
	 */
	function update_core_info($array)
	{
		$arr = ['info' => $array, 'user' => $this];
		Hook::run(HOOKTYPE_EDIT_USER, $arr);
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
function create_new_user(array &$user) : bool
{
	if (!isset($user['user_name']) || !isset($user['user_pass']))
		throw new Exception("Attempted to add user without specifying user_name or user_pass");

	$user['user_name'] = htmlspecialchars($user['user_name']);
	$user['user_pass'] = PanelUser::password_hash($user['user_pass']);
	$user['fname'] = (isset($user['fname'])) ? htmlspecialchars($user['fname']) : NULL;
	$last['lname'] = (isset($user['lname'])) ? htmlspecialchars($user['lname']) : NULL;
	$user['user_bio'] = (isset($user['user_bio'])) ? htmlspecialchars($user['user_bio']) : NULL;
	$user['email'] = (isset($user['user_email'])) ? htmlspecialchars($user['user_email']) : NULL;

	if (($u = new PanelUser($user['user_name']))->id)
	{
		$user['err'] = "User already exists";
		return false;
	}
	// internal use
	$user['success'] = false;
	$user['errmsg'] = [];
	
	Hook::run(HOOKTYPE_USER_CREATE, $user);
	if (!$user['success'])
		return false;
	
	return true;
}

/**
 * Gets the user object for the current session
 * @return PanelUser|bool
 */
function unreal_get_current_user() : PanelUser|bool
{
	if (isset($_SESSION) && isset($_SESSION['id']))
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
	if (!$user)
		return false;
	return user_can($user, $permission);
}

/**
 * Checks if a user can do something
 * @param string $permission
 * @return bool
 */
function user_can(PanelUser $user, $permission) : bool
{
	global $config;
	if (!$user)
		return false;

	if (isset($user->user_meta['role']))
	{
		if ($user->user_meta['role'] == "Super-Admin")
			return true;

		else if ($user->user_meta['role'] == "Read-Only")
			return false;

		else if (in_array($permission, $config['user_roles'][$user->user_meta['role']]))
			return true;
			
		return false;
	}

	/* compatibility fallback */
	if (isset($user->user_meta['permissions']))
	{
		$perms = unserialize($user->user_meta['permissions']);
		if (in_array($permission, $perms))
			return true;
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
	$arr = ["user" => $user, "info" => &$info, "boolint" => 0];
	Hook::run(HOOKTYPE_USER_DELETE, $arr);
	return $arr["boolint"];
}

function get_panel_user_permission_list()
{
	$list = [
		"Can add/delete/edit Admin Panel users" => PERMISSION_MANAGE_USERS,
		"Can ban/kill IRC users" => PERMISSION_BAN_USERS,
		"Can change properties of a user, i.e. vhost, modes and more" => PERMISSION_EDIT_USER,
		"Can change properties of a channel, i.e. topic, modes and more" => PERMISSION_EDIT_CHANNEL,
		"Can change properties of a user on a channel i.e give/remove voice or ops and more" => PERMISSION_EDIT_CHANNEL_USER,
		"Can add manual bans, including G-Lines, Z-Lines and more" => PERMISSION_SERVER_BAN_ADD,
		"Can remove set bans, including G-Lines, Z-Lines and more" => PERMISSION_SERVER_BAN_DEL,
		"Can forbid usernames and channels" => PERMISSION_NAME_BAN_ADD,
		"Can unforbid usernames and channels" => PERMISSION_NAME_BAN_DEL,
		"Can add server ban exceptions" => PERMISSION_BAN_EXCEPTION_ADD,
		"Can remove server ban exceptions" => PERMISSION_BAN_EXCEPTION_DEL,
		"Can add Spamfilter entries" => PERMISSION_SPAMFILTER_ADD,
		"Can remove Spamfilter entries" => PERMISSION_SPAMFILTER_DEL
	];
	Hook::run(HOOKTYPE_USER_PERMISSION_LIST, $list); // so plugin writers can add their own permissions
	return $list;
}

function generate_panel_user_permission_table($user)
{
	
	$list = get_panel_user_permission_list();
	foreach($list as $desc => $slug)
	{
		$attributes = "";
		$attributes .= (current_user_can(PERMISSION_MANAGE_USERS)) ? "" : "disabled ";
		?>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">
					<input <?php
						$attributes .= (user_can($user, $slug)) ? "checked" : "";
						echo $attributes;
					?> name="permissions[]" value="<?php echo $slug; ?>" type="checkbox">
				</div>
			</div>
			<input type="text" readonly  class="form-control" value="<?php echo "$desc ($slug)"; ?>">
		</div>

		<?php
	}
}

function get_panel_user_roles_list()
{
	/* Defaults */
	$list = [
        "Super-Admin" => get_panel_user_permission_list(), // SuperAdmin can do everything
        "Read-Only" => [], // Read Only can do nothing
	];

	Hook::run(HOOKTYPE_USER_ROLE_LIST, $list);
	return $list;
}

function generate_role_list($list)
{
	$list2 = get_panel_user_permission_list();
	?>
		<h5>Roles List:</h5>
		<div id="permlist">
		<div class="container-xxl" style="max-width: 1430px;">
		<div class="accordion" id="roles_accord">

<?php foreach($list as $role => $slug) {?>
	<div class="card">
		<div class="card-header" id="<?php echo to_slug($role); ?>_heading">
			<div class="btn-header-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_<?php echo to_slug($role); ?>" aria-expanded="true" aria-controls="collapse_<?php echo to_slug($role); ?>">
				<?php echo $role ?>
				
			</div>
		</div>

		<div id="collapse_<?php echo to_slug($role); ?>" class="collapse" aria-labelledby="<?php echo to_slug($role); ?>_heading" data-parent="#roles_accord">
			<div id="results_rpc" class="card-body">
				<form method="post">
				<?php if ($role !== "Super-Admin" && $role !== "Read-Only") { ?>
					<div class="container row mb-2">
						<button id="update_role" name="update_role" value="<?php echo $role ?>" class="btn btn-primary ml-1 mr-2" >Update</button>
						<button id="delete_role" name="del_role_name" value="<?php echo $role ?>" class="btn btn-danger"><i class="fa fa-trash fa-1" aria-hidden="true"></i></button>
					</div>
					
				<?php } ?>
				<div id="<?php echo $role; ?>_input_area"><?php
					foreach($list2 as $desc => $slug)
					{
					$attributes = "";
					$attributes .= ($role == "Super-Admin" || $role == "Read-Only") ? "disabled " : "";

						?>
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<input <?php
										$attributes .= (in_array($slug, $list[$role])) ? "checked" : "";
										echo $attributes;
									?> name="permissions[]" value="<?php echo $slug; ?>" type="checkbox">
								</div>
							</div>
							<input type="text" readonly class="form-control" value="<?php echo "$desc ($slug)"; ?>">
						</div>
				
						<?php
					}
				?>	</div>
				</form>
			</div>
		</div>
	</div>
<?php }?>

		</div></div><br>
			
</div><?php

}
